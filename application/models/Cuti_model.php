<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model Manajemen Cuti ASN
 *
 * Alur approval berjenjang mengikuti pola Approval_model (modul validasi hasil lab):
 * aturan disimpan di setting_approval_cuti, lalu disalin menjadi rantai per
 * pengajuan di cuti_approval saat pengajuan dikirim.
 *
 * Perhitungan saldo mengacu PP 11/2017 & Perka BKN 24/2017, dengan batas
 * yang dapat diubah lewat tabel pengaturan (grup 'cuti').
 */
class Cuti_model extends CI_Model {

    protected $table_jenis = 'cuti_jenis';
    protected $table_libur = 'cuti_hari_libur';
    protected $table_saldo = 'cuti_saldo';
    protected $table_rules = 'setting_approval_cuti';
    protected $table_pengajuan = 'cuti_pengajuan';
    protected $table_approval = 'cuti_approval';
    protected $table_log = 'cuti_log';
    protected $table_pegawai = 'pegawai';

    public function __construct() {
        parent::__construct();
    }

    // =====================================================================
    // KONFIGURASI & UTILITAS
    // =====================================================================

    /**
     * Parameter aturan cuti dari tabel pengaturan, bukan konstanta hardcode.
     */
    public function get_config() {
        return [
            'hak_tahunan'      => (int)get_setting('cuti_hak_tahunan', 12),
            'max_pakai_n1'     => (int)get_setting('cuti_max_pakai_n1', 6),
            'max_pakai_n2'     => (int)get_setting('cuti_max_pakai_n2', 6),
            'max_total'        => (int)get_setting('cuti_max_total_setahun', 24),
            'hitung_hari_kerja'=> (int)get_setting('cuti_hitung_hari_kerja', 1) === 1,
            'prefix_nomor'     => get_setting('cuti_prefix_nomor', 'CT'),
            'format_surat'     => get_setting('cuti_format_surat', '800/{nomor}/LKL-PR/{romawi}/{tahun}'),
        ];
    }

    public function format_nama_pegawai($p) {
        if (!$p) return '-';
        $nama = trim($p['nama_lengkap'] ?? '');
        $gelarDepan = trim($p['gelar_depan'] ?? '');
        $gelarBelakang = trim($p['gelar_belakang'] ?? '');

        $result = '';
        if (!empty($gelarDepan)) {
            $result .= $gelarDepan . ' ';
        }
        $result .= $nama;
        if (!empty($gelarBelakang)) {
            $result .= ', ' . $gelarBelakang;
        }
        return $result;
    }

    public function bulan_romawi($bulan) {
        $map = [1 => 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        return $map[(int)$bulan] ?? 'I';
    }

    /**
     * Daftar tanggal libur aktif dalam rentang, sebagai map Y-m-d => keterangan.
     */
    public function get_hari_libur($mulai, $selesai) {
        $rows = $this->db->select('tanggal, keterangan')
                         ->where('is_active', 1)
                         ->where('tanggal >=', $mulai)
                         ->where('tanggal <=', $selesai)
                         ->get($this->table_libur)
                         ->result_array();

        $map = [];
        foreach ($rows as $r) {
            $map[$r['tanggal']] = $r['keterangan'];
        }
        return $map;
    }

    /**
     * Hitung jumlah hari cuti. Bila opsi hari kerja aktif, Sabtu/Minggu dan
     * hari libur nasional tidak ikut dihitung.
     */
    public function hitung_hari($mulai, $selesai, $hariKerjaSaja = null) {
        if (empty($mulai) || empty($selesai)) return 0;

        $start = new DateTime($mulai);
        $end = new DateTime($selesai);
        if ($end < $start) return 0;

        if ($hariKerjaSaja === null) {
            $cfg = $this->get_config();
            $hariKerjaSaja = $cfg['hitung_hari_kerja'];
        }

        $libur = $hariKerjaSaja ? $this->get_hari_libur($start->format('Y-m-d'), $end->format('Y-m-d')) : [];

        $jumlah = 0;
        $cursor = clone $start;
        while ($cursor <= $end) {
            $tgl = $cursor->format('Y-m-d');
            $isWeekend = in_array($cursor->format('N'), ['6', '7'], true);

            if (!$hariKerjaSaja || (!$isWeekend && !isset($libur[$tgl]))) {
                $jumlah++;
            }
            $cursor->modify('+1 day');
        }

        return $jumlah;
    }

    // =====================================================================
    // MASTER JENIS CUTI
    // =====================================================================

    public function get_jenis($activeOnly = true) {
        if ($activeOnly) {
            $this->db->where('is_active', 1);
        }
        return $this->db->order_by('sort_order', 'ASC')->get($this->table_jenis)->result_array();
    }

    public function get_jenis_by_id($id) {
        return $this->db->where('id', (int)$id)->get($this->table_jenis)->row_array();
    }

    // =====================================================================
    // SALDO HAK CUTI TAHUNAN
    // =====================================================================

    /**
     * Ambil saldo pegawai untuk satu tahun. Bila belum ada, baris dibuat
     * otomatis dengan membawa sisa hak dari dua tahun sebelumnya.
     */
    public function get_saldo($pegawaiId, $tahun = null) {
        $pegawaiId = (int)$pegawaiId;
        $tahun = (int)($tahun ?: date('Y'));
        $cfg = $this->get_config();

        $row = $this->db->where('pegawai_id', $pegawaiId)
                        ->where('tahun', $tahun)
                        ->get($this->table_saldo)
                        ->row_array();

        if (!$row) {
            $prev1 = $this->db->where('pegawai_id', $pegawaiId)->where('tahun', $tahun - 1)
                              ->get($this->table_saldo)->row_array();
            $prev2 = $this->db->where('pegawai_id', $pegawaiId)->where('tahun', $tahun - 2)
                              ->get($this->table_saldo)->row_array();

            $data = [
                'pegawai_id' => $pegawaiId,
                'tahun' => $tahun,
                'hak_tahun_berjalan' => $cfg['hak_tahunan'],
                'saldo_n1' => $prev1 ? max(0, (int)$prev1['hak_tahun_berjalan'] + (int)$prev1['penyesuaian'] - (int)$prev1['terpakai_n']) : 0,
                'saldo_n2' => $prev2 ? max(0, (int)$prev2['hak_tahun_berjalan'] + (int)$prev2['penyesuaian'] - (int)$prev2['terpakai_n']) : 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            $this->db->insert($this->table_saldo, $data);
            $row = $this->db->where('pegawai_id', $pegawaiId)->where('tahun', $tahun)
                            ->get($this->table_saldo)->row_array();
        }

        return $this->_hitung_sisa($row, $cfg);
    }

    /**
     * Lengkapi baris saldo dengan sisa hak yang benar-benar boleh dipakai,
     * setelah dibatasi plafon N-1 / N-2 dan plafon total setahun.
     */
    private function _hitung_sisa($row, $cfg = null) {
        if (!$row) return null;
        $cfg = $cfg ?: $this->get_config();

        $hakN = (int)$row['hak_tahun_berjalan'] + (int)$row['penyesuaian'];

        $plafonN2 = min((int)$row['saldo_n2'], $cfg['max_pakai_n2']);
        $plafonN1 = min((int)$row['saldo_n1'], $cfg['max_pakai_n1']);

        $row['sisa_n2'] = max(0, $plafonN2 - (int)$row['terpakai_n2']);
        $row['sisa_n1'] = max(0, $plafonN1 - (int)$row['terpakai_n1']);
        $row['sisa_n']  = max(0, $hakN - (int)$row['terpakai_n']);

        $row['plafon_n2'] = $plafonN2;
        $row['plafon_n1'] = $plafonN1;
        $row['hak_efektif_n'] = $hakN;

        $row['total_terpakai'] = (int)$row['terpakai_n'] + (int)$row['terpakai_n1'] + (int)$row['terpakai_n2'];
        $sisaPlafonTotal = max(0, $cfg['max_total'] - $row['total_terpakai']);

        // Sisa yang benar-benar dapat diambil tidak boleh melampaui plafon total setahun.
        $row['total_tersedia'] = min($row['sisa_n2'] + $row['sisa_n1'] + $row['sisa_n'], $sisaPlafonTotal);
        $row['sisa_plafon_total'] = $sisaPlafonTotal;

        return $row;
    }

    /**
     * Bagi kebutuhan hari cuti ke sumber saldo: N-2 lebih dulu karena paling
     * cepat hangus, lalu N-1, terakhir hak tahun berjalan.
     */
    public function alokasi_pemakaian($pegawaiId, $tahun, $jumlahHari) {
        $saldo = $this->get_saldo($pegawaiId, $tahun);
        $butuh = (int)$jumlahHari;

        $alokasi = ['pakai_n2' => 0, 'pakai_n1' => 0, 'pakai_n' => 0];

        if ($butuh <= 0) {
            return ['status' => true, 'alokasi' => $alokasi, 'saldo' => $saldo];
        }

        if ($butuh > (int)$saldo['total_tersedia']) {
            return [
                'status' => false,
                'message' => sprintf(
                    'Sisa hak cuti tidak mencukupi. Dibutuhkan %d hari kerja, tersedia %d hari (N-2: %d, N-1: %d, tahun berjalan: %d).',
                    $butuh, $saldo['total_tersedia'], $saldo['sisa_n2'], $saldo['sisa_n1'], $saldo['sisa_n']
                ),
                'alokasi' => $alokasi,
                'saldo' => $saldo
            ];
        }

        foreach (['n2', 'n1', 'n'] as $sumber) {
            if ($butuh <= 0) break;
            $ambil = min($butuh, (int)$saldo['sisa_' . $sumber]);
            $alokasi['pakai_' . $sumber] = $ambil;
            $butuh -= $ambil;
        }

        return ['status' => true, 'alokasi' => $alokasi, 'saldo' => $saldo];
    }

    /**
     * Terapkan atau kembalikan pemakaian saldo. $arah = 1 memotong, -1 memulihkan.
     */
    public function terapkan_pemakaian($pegawaiId, $tahun, $alokasi, $arah = 1) {
        $this->get_saldo($pegawaiId, $tahun); // pastikan baris ada

        $this->db->set('terpakai_n2', 'GREATEST(0, terpakai_n2 + ' . ((int)$alokasi['pakai_n2'] * $arah) . ')', FALSE);
        $this->db->set('terpakai_n1', 'GREATEST(0, terpakai_n1 + ' . ((int)$alokasi['pakai_n1'] * $arah) . ')', FALSE);
        $this->db->set('terpakai_n',  'GREATEST(0, terpakai_n + '  . ((int)$alokasi['pakai_n']  * $arah) . ')', FALSE);
        $this->db->set('updated_at', date('Y-m-d H:i:s'));
        $this->db->where('pegawai_id', (int)$pegawaiId)->where('tahun', (int)$tahun);
        $this->db->update($this->table_saldo);

        return true;
    }

    public function save_saldo($pegawaiId, $tahun, $data) {
        $this->get_saldo($pegawaiId, $tahun);

        $allowed = ['hak_tahun_berjalan', 'saldo_n1', 'saldo_n2', 'penyesuaian', 'catatan'];
        $payload = array_intersect_key($data, array_flip($allowed));
        $payload['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('pegawai_id', (int)$pegawaiId)->where('tahun', (int)$tahun)
                 ->update($this->table_saldo, $payload);

        return $this->get_saldo($pegawaiId, $tahun);
    }

    /**
     * Rekap saldo seluruh pegawai pada satu tahun, untuk laporan.
     */
    public function get_rekap_saldo($tahun = null, $filter = []) {
        $tahun = (int)($tahun ?: date('Y'));
        $cfg = $this->get_config();

        // Kolom saldo diambil eksplisit dengan nilai bawaan; memakai s.* membuat
        // kolom NULL hasil LEFT JOIN menimpa p.id pada pegawai yang belum bersaldo.
        $this->db->select("p.id AS pegawai_id, p.nip, p.nama_lengkap, p.gelar_depan, p.gelar_belakang,
                           p.nama_jabatan, p.seksi_ruangan, p.status_pegawai,
                           {$tahun} AS tahun,
                           COALESCE(s.hak_tahun_berjalan, {$cfg['hak_tahunan']}) AS hak_tahun_berjalan,
                           COALESCE(s.saldo_n1, 0) AS saldo_n1,
                           COALESCE(s.saldo_n2, 0) AS saldo_n2,
                           COALESCE(s.terpakai_n, 0) AS terpakai_n,
                           COALESCE(s.terpakai_n1, 0) AS terpakai_n1,
                           COALESCE(s.terpakai_n2, 0) AS terpakai_n2,
                           COALESCE(s.penyesuaian, 0) AS penyesuaian,
                           s.catatan", FALSE);
        $this->db->from($this->table_pegawai . ' p');
        $this->db->join($this->table_saldo . ' s', 's.pegawai_id = p.id AND s.tahun = ' . $tahun, 'left');

        if (!empty($filter['seksi_ruangan']) && $filter['seksi_ruangan'] !== 'all') {
            $this->db->where('p.seksi_ruangan', $filter['seksi_ruangan']);
        }
        if (!empty($filter['status_pegawai']) && $filter['status_pegawai'] !== 'all') {
            $this->db->where('p.status_pegawai', $filter['status_pegawai']);
        }
        if (!empty($filter['search'])) {
            $this->db->group_start()
                     ->like('p.nama_lengkap', $filter['search'])
                     ->or_like('p.nip', $filter['search'])
                     ->group_end();
        }

        $rows = $this->db->order_by('p.nama_lengkap', 'ASC')->get()->result_array();

        foreach ($rows as &$r) {
            $r['nama_formatted'] = $this->format_nama_pegawai($r);
            $r = $this->_hitung_sisa($r, $cfg);
        }

        return $rows;
    }

    // =====================================================================
    // ATURAN APPROVAL BERJENJANG
    // =====================================================================

    public function get_rules($activeOnly = false) {
        $this->db->select('r.*, p.nip, p.nama_lengkap, p.gelar_depan, p.gelar_belakang,
                           p.nama_jabatan AS pegawai_jabatan, p.seksi_ruangan, p.foto,
                           p.tanda_tangan AS pegawai_tanda_tangan');
        $this->db->from($this->table_rules . ' r');
        $this->db->join($this->table_pegawai . ' p', 'p.id = r.pegawai_id', 'left');

        if ($activeOnly) {
            $this->db->where('r.is_active', 1);
        }

        $this->db->order_by('r.urutan', 'ASC');
        $this->db->order_by('r.id', 'ASC');
        $rows = $this->db->get()->result_array();

        foreach ($rows as &$r) {
            $r['nama_formatted'] = $this->format_nama_pegawai($r);
        }

        return $rows;
    }

    public function get_rule_by_id($id) {
        $this->db->select('r.*, p.nip, p.nama_lengkap, p.gelar_depan, p.gelar_belakang,
                           p.nama_jabatan AS pegawai_jabatan, p.seksi_ruangan,
                           p.tanda_tangan AS pegawai_tanda_tangan');
        $this->db->from($this->table_rules . ' r');
        $this->db->join($this->table_pegawai . ' p', 'p.id = r.pegawai_id', 'left');
        $this->db->where('r.id', (int)$id);
        $row = $this->db->get()->row_array();

        if ($row) {
            $row['nama_formatted'] = $this->format_nama_pegawai($row);
        }
        return $row;
    }

    public function get_pegawai_options() {
        $rows = $this->db->select('id, nip, nama_lengkap, gelar_depan, gelar_belakang, nama_jabatan, seksi_ruangan, foto, tanda_tangan')
                         ->order_by('nama_lengkap', 'ASC')
                         ->get($this->table_pegawai)
                         ->result_array();

        foreach ($rows as &$p) {
            $p['nama_formatted'] = $this->format_nama_pegawai($p);
        }
        return $rows;
    }

    public function get_next_urutan() {
        $max = $this->db->select_max('urutan')->get($this->table_rules)->row_array();
        return (int)($max['urutan'] ?? 0) + 1;
    }

    public function save_rule($data, $id = null) {
        $payload = [
            'urutan' => (int)$data['urutan'],
            'pegawai_id' => (int)$data['pegawai_id'],
            'jabatan_approval' => trim($data['jabatan_approval']),
            'keterangan' => $data['keterangan'] ?? null,
            'is_required' => isset($data['is_required']) ? (int)$data['is_required'] : 1,
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 1,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($id) {
            $this->db->where('id', (int)$id)->update($this->table_rules, $payload);
            return (int)$id;
        }

        $payload['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table_rules, $payload);
        return (int)$this->db->insert_id();
    }

    public function delete_rule($id) {
        $this->db->where('id', (int)$id)->delete($this->table_rules);
        $this->_rapikan_urutan();
        return true;
    }

    /**
     * Geser urutan satu tahap approval ke atas atau ke bawah.
     */
    public function move_order($id, $direction = 'up') {
        $current = $this->db->where('id', (int)$id)->get($this->table_rules)->row_array();
        if (!$current) {
            return false;
        }

        $operator = ($direction === 'up') ? '<' : '>';
        $sort = ($direction === 'up') ? 'DESC' : 'ASC';

        $neighbour = $this->db->where('urutan ' . $operator, (int)$current['urutan'])
                              ->order_by('urutan', $sort)
                              ->limit(1)
                              ->get($this->table_rules)
                              ->row_array();

        if (!$neighbour) {
            return false;
        }

        $this->db->where('id', $current['id'])->update($this->table_rules, ['urutan' => (int)$neighbour['urutan']]);
        $this->db->where('id', $neighbour['id'])->update($this->table_rules, ['urutan' => (int)$current['urutan']]);

        return true;
    }

    private function _rapikan_urutan() {
        $rows = $this->db->order_by('urutan', 'ASC')->order_by('id', 'ASC')->get($this->table_rules)->result_array();
        $urutan = 1;
        foreach ($rows as $r) {
            if ((int)$r['urutan'] !== $urutan) {
                $this->db->where('id', $r['id'])->update($this->table_rules, ['urutan' => $urutan]);
            }
            $urutan++;
        }
    }

    // =====================================================================
    // PENGAJUAN CUTI
    // =====================================================================

    public function generate_nomor() {
        $cfg = $this->get_config();
        $tahun = date('Y');
        $romawi = $this->bulan_romawi(date('n'));

        $last = $this->db->select('nomor_pengajuan')
                         ->like('nomor_pengajuan', $cfg['prefix_nomor'] . '/', 'after')
                         ->like('nomor_pengajuan', '/' . $tahun, 'before')
                         ->order_by('id', 'DESC')
                         ->limit(1)
                         ->get($this->table_pengajuan)
                         ->row_array();

        $urut = 1;
        if ($last && preg_match('#/(\d+)/#', $last['nomor_pengajuan'], $m)) {
            $urut = (int)$m[1] + 1;
        }

        return sprintf('%s/%04d/%s/%s', $cfg['prefix_nomor'], $urut, $romawi, $tahun);
    }

    public function generate_nomor_surat() {
        $cfg = $this->get_config();
        $tahun = date('Y');

        $jumlah = $this->db->where('status', 'disetujui')
                           ->where('YEAR(waktu_selesai_approval)', $tahun)
                           ->count_all_results($this->table_pengajuan);

        return str_replace(
            ['{nomor}', '{romawi}', '{tahun}'],
            [sprintf('%03d', $jumlah + 1), $this->bulan_romawi(date('n')), $tahun],
            $cfg['format_surat']
        );
    }

    public function get_by_id($id) {
        $row = $this->db->where('id', (int)$id)->get($this->table_pengajuan)->row_array();
        if (!$row) return null;

        $row['approvals'] = $this->get_approvals((int)$id);
        $row['saldo'] = $this->get_saldo((int)$row['pegawai_id'], (int)$row['tahun_hak']);
        return $row;
    }

    public function get_datatable($params) {
        $draw = (int)($params['draw'] ?? 1);
        $start = (int)($params['start'] ?? 0);
        $length = (int)($params['length'] ?? 10);
        $search = trim($params['search']['value'] ?? '');

        $columns = ['nomor_pengajuan', 'pegawai_nama', 'jenis_cuti_nama', 'tanggal_mulai', 'jumlah_hari', 'status'];

        $recordsTotal = $this->db->count_all_results($this->table_pengajuan);

        $this->db->from($this->table_pengajuan);
        $this->_apply_filter($params, $search);
        $recordsFiltered = $this->db->count_all_results('', FALSE);

        if (!empty($params['order'])) {
            foreach ($params['order'] as $o) {
                $idx = (int)($o['column'] ?? 0);
                if (isset($columns[$idx])) {
                    $dir = (strtolower($o['dir'] ?? 'asc') === 'desc') ? 'DESC' : 'ASC';
                    $this->db->order_by($columns[$idx], $dir);
                }
            }
        } else {
            $this->db->order_by('id', 'DESC');
        }

        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        $rows = $this->db->get()->result_array();

        foreach ($rows as &$r) {
            $r['approvals'] = $this->get_approvals((int)$r['id']);
            $r['progress'] = $this->ringkas_progress($r['approvals']);
        }

        return [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ];
    }

    private function _apply_filter($params, $search) {
        if ($search !== '') {
            $this->db->group_start()
                     ->like('nomor_pengajuan', $search)
                     ->or_like('pegawai_nama', $search)
                     ->or_like('pegawai_nip', $search)
                     ->or_like('jenis_cuti_nama', $search)
                     ->group_end();
        }

        if (!empty($params['status']) && $params['status'] !== 'all') {
            $this->db->where('status', $params['status']);
        }
        if (!empty($params['tahun']) && $params['tahun'] !== 'all') {
            $this->db->where('tahun_hak', (int)$params['tahun']);
        }
        if (!empty($params['jenis_cuti_id']) && $params['jenis_cuti_id'] !== 'all') {
            $this->db->where('jenis_cuti_id', (int)$params['jenis_cuti_id']);
        }
        if (!empty($params['pegawai_id']) && $params['pegawai_id'] !== 'all') {
            $this->db->where('pegawai_id', (int)$params['pegawai_id']);
        }
    }

    public function ringkas_progress($approvals) {
        $total = count($approvals);
        $disetujui = 0;
        foreach ($approvals as $a) {
            if ($a['status'] === 'approved') $disetujui++;
        }
        return ['total' => $total, 'disetujui' => $disetujui];
    }

    /**
     * Simpan pengajuan baru atau perbarui draft. Snapshot data pegawai dan
     * jenis cuti diambil ulang setiap simpan selama masih berstatus draft.
     */
    public function save_pengajuan($data, $id = null) {
        $pegawai = $this->db->where('id', (int)$data['pegawai_id'])->get($this->table_pegawai)->row_array();
        if (!$pegawai) {
            return ['status' => false, 'message' => 'Data pegawai tidak ditemukan.'];
        }

        $jenis = $this->get_jenis_by_id((int)$data['jenis_cuti_id']);
        if (!$jenis) {
            return ['status' => false, 'message' => 'Jenis cuti tidak dikenali.'];
        }

        $jumlahHari = $this->hitung_hari($data['tanggal_mulai'], $data['tanggal_selesai']);
        if ($jumlahHari <= 0) {
            return ['status' => false, 'message' => 'Rentang tanggal cuti tidak valid atau seluruhnya jatuh pada hari libur.'];
        }

        if ((int)$jenis['max_hari'] > 0 && $jumlahHari > (int)$jenis['max_hari']) {
            return ['status' => false, 'message' => sprintf('%s dibatasi maksimal %d hari, pengajuan ini %d hari.', $jenis['nama'], (int)$jenis['max_hari'], $jumlahHari)];
        }

        $tahunHak = (int)($data['tahun_hak'] ?? date('Y', strtotime($data['tanggal_mulai'])));

        // Hanya cuti tahunan yang membebani saldo N/N-1/N-2.
        $alokasi = ['pakai_n2' => 0, 'pakai_n1' => 0, 'pakai_n' => 0];
        if ((int)$jenis['mengurangi_saldo'] === 1) {
            $cek = $this->alokasi_pemakaian((int)$data['pegawai_id'], $tahunHak, $jumlahHari);
            if (!$cek['status']) {
                return ['status' => false, 'message' => $cek['message']];
            }
            $alokasi = $cek['alokasi'];
        }

        $bentrok = $this->cek_bentrok((int)$data['pegawai_id'], $data['tanggal_mulai'], $data['tanggal_selesai'], $id);
        if ($bentrok) {
            return ['status' => false, 'message' => sprintf('Pegawai sudah memiliki pengajuan cuti %s pada %s s/d %s.', $bentrok['nomor_pengajuan'], $bentrok['tanggal_mulai'], $bentrok['tanggal_selesai'])];
        }

        $payload = [
            'pegawai_id' => (int)$pegawai['id'],
            'pegawai_nama' => $this->format_nama_pegawai($pegawai),
            'pegawai_nip' => $pegawai['nip'],
            'pegawai_jabatan' => $pegawai['nama_jabatan'],
            'pegawai_pangkat' => $pegawai['nama_pangkat'],
            'pegawai_golongan' => $pegawai['kode_golongan'],
            'pegawai_seksi' => $pegawai['seksi_ruangan'],
            'pegawai_mkg_tahun' => (int)$pegawai['mkg_tahun'],
            'jenis_cuti_id' => (int)$jenis['id'],
            'jenis_cuti_kode' => $jenis['kode'],
            'jenis_cuti_nama' => $jenis['nama'],
            'tahun_hak' => $tahunHak,
            'tanggal_mulai' => $data['tanggal_mulai'],
            'tanggal_selesai' => $data['tanggal_selesai'],
            'jumlah_hari' => $jumlahHari,
            'pakai_n2' => $alokasi['pakai_n2'],
            'pakai_n1' => $alokasi['pakai_n1'],
            'pakai_n' => $alokasi['pakai_n'],
            'alasan' => trim($data['alasan']),
            'alamat_selama_cuti' => $data['alamat_selama_cuti'] ?? null,
            'telepon_selama_cuti' => $data['telepon_selama_cuti'] ?? null,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (array_key_exists('lampiran', $data) && $data['lampiran'] !== null) {
            $payload['lampiran'] = $data['lampiran'];
        }

        if ($id) {
            $existing = $this->db->where('id', (int)$id)->get($this->table_pengajuan)->row_array();
            if (!$existing) {
                return ['status' => false, 'message' => 'Pengajuan tidak ditemukan.'];
            }
            if ($existing['status'] !== 'draft') {
                return ['status' => false, 'message' => 'Pengajuan yang sudah dikirim tidak dapat diubah. Batalkan terlebih dahulu.'];
            }

            $this->db->where('id', (int)$id)->update($this->table_pengajuan, $payload);
            $savedId = (int)$id;
            $this->tulis_log($savedId, 'UPDATE_DRAFT', 'Draft pengajuan cuti diperbarui', 'draft', 'draft');
        } else {
            $payload['nomor_pengajuan'] = $this->generate_nomor();
            $payload['status'] = 'draft';
            $payload['diinput_oleh_user_id'] = (int)($data['user_id'] ?? 0) ?: null;
            $payload['diinput_oleh_nama'] = $data['user_nama'] ?? null;
            $payload['created_at'] = date('Y-m-d H:i:s');

            $this->db->insert($this->table_pengajuan, $payload);
            $savedId = (int)$this->db->insert_id();
            $this->tulis_log($savedId, 'CREATE', 'Draft pengajuan cuti dibuat', null, 'draft');
        }

        return ['status' => true, 'id' => $savedId, 'data' => $this->get_by_id($savedId)];
    }

    /**
     * Cuti yang masih hidup (draft/diajukan/proses/disetujui) tidak boleh
     * tumpang tindih tanggalnya untuk pegawai yang sama.
     */
    public function cek_bentrok($pegawaiId, $mulai, $selesai, $excludeId = null) {
        $this->db->where('pegawai_id', (int)$pegawaiId)
                 ->where_in('status', ['draft', 'diajukan', 'proses', 'disetujui'])
                 ->where('tanggal_mulai <=', $selesai)
                 ->where('tanggal_selesai >=', $mulai);

        if ($excludeId) {
            $this->db->where('id !=', (int)$excludeId);
        }

        return $this->db->limit(1)->get($this->table_pengajuan)->row_array();
    }

    /**
     * Kirim draft ke alur approval. Saldo dipotong di sini agar hak cuti
     * langsung terpesan dan tidak dipakai ganda oleh pengajuan lain.
     */
    public function ajukan($id, $userId = null, $userNama = 'Petugas') {
        $row = $this->db->where('id', (int)$id)->get($this->table_pengajuan)->row_array();
        if (!$row) {
            return ['status' => false, 'message' => 'Pengajuan tidak ditemukan.'];
        }
        if ($row['status'] !== 'draft') {
            return ['status' => false, 'message' => 'Hanya pengajuan berstatus draft yang dapat dikirim.'];
        }

        $jenis = $this->get_jenis_by_id((int)$row['jenis_cuti_id']);
        if ($jenis && (int)$jenis['wajib_lampiran'] === 1 && empty($row['lampiran'])) {
            return ['status' => false, 'message' => $jenis['nama'] . ' wajib melampirkan surat keterangan pendukung.'];
        }

        // Saldo dihitung ulang di sini: antara penyimpanan draft dan pengiriman,
        // permohonan lain bisa saja sudah memakai hak cuti yang sama.
        $alokasi = ['pakai_n2' => (int)$row['pakai_n2'], 'pakai_n1' => (int)$row['pakai_n1'], 'pakai_n' => (int)$row['pakai_n']];

        if ($jenis && (int)$jenis['mengurangi_saldo'] === 1) {
            $cek = $this->alokasi_pemakaian((int)$row['pegawai_id'], (int)$row['tahun_hak'], (int)$row['jumlah_hari']);
            if (!$cek['status']) {
                return ['status' => false, 'message' => $cek['message']];
            }
            $alokasi = $cek['alokasi'];
        }

        if (!$this->init_approval((int)$id)) {
            return ['status' => false, 'message' => 'Hirarki approval cuti belum dikonfigurasi. Atur terlebih dahulu di menu Setting Approval.'];
        }

        if ($jenis && (int)$jenis['mengurangi_saldo'] === 1) {
            $this->terapkan_pemakaian((int)$row['pegawai_id'], (int)$row['tahun_hak'], $alokasi, 1);
        }

        $this->db->where('id', (int)$id)->update($this->table_pengajuan, [
            'status' => 'diajukan',
            'pakai_n2' => $alokasi['pakai_n2'],
            'pakai_n1' => $alokasi['pakai_n1'],
            'pakai_n' => $alokasi['pakai_n'],
            'waktu_pengajuan' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->tulis_log((int)$id, 'SUBMIT', 'Pengajuan dikirim ke alur approval', 'draft', 'diajukan', $userId, $userNama);

        return ['status' => true, 'message' => 'Pengajuan cuti ' . $row['nomor_pengajuan'] . ' berhasil dikirim ke alur persetujuan.'];
    }

    /**
     * Batalkan pengajuan dan kembalikan saldo bila sudah sempat dipotong.
     */
    public function batalkan($id, $alasan = null, $userId = null, $userNama = 'Petugas') {
        $row = $this->db->where('id', (int)$id)->get($this->table_pengajuan)->row_array();
        if (!$row) {
            return ['status' => false, 'message' => 'Pengajuan tidak ditemukan.'];
        }
        if (in_array($row['status'], ['dibatalkan', 'ditolak'], true)) {
            return ['status' => false, 'message' => 'Pengajuan ini sudah tidak aktif.'];
        }

        $this->_kembalikan_saldo($row);

        $this->db->where('id', (int)$id)->update($this->table_pengajuan, [
            'status' => 'dibatalkan',
            'catatan_penolakan' => $alasan,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->tulis_log((int)$id, 'CANCEL', $alasan ?: 'Pengajuan dibatalkan', $row['status'], 'dibatalkan', $userId, $userNama);

        return ['status' => true, 'message' => 'Pengajuan cuti ' . $row['nomor_pengajuan'] . ' dibatalkan.'];
    }

    private function _kembalikan_saldo($row) {
        if (!in_array($row['status'], ['diajukan', 'proses', 'disetujui'], true)) {
            return; // draft belum pernah memotong saldo
        }

        $jenis = $this->get_jenis_by_id((int)$row['jenis_cuti_id']);
        if (!$jenis || (int)$jenis['mengurangi_saldo'] !== 1) {
            return;
        }

        $this->terapkan_pemakaian((int)$row['pegawai_id'], (int)$row['tahun_hak'], [
            'pakai_n2' => (int)$row['pakai_n2'],
            'pakai_n1' => (int)$row['pakai_n1'],
            'pakai_n'  => (int)$row['pakai_n'],
        ], -1);
    }

    public function hapus_pengajuan($id) {
        $row = $this->db->where('id', (int)$id)->get($this->table_pengajuan)->row_array();
        if (!$row) {
            return ['status' => false, 'message' => 'Pengajuan tidak ditemukan.'];
        }
        if ($row['status'] !== 'draft') {
            return ['status' => false, 'message' => 'Hanya draft yang dapat dihapus. Gunakan pembatalan untuk pengajuan yang sudah dikirim.'];
        }

        $this->db->where('id', (int)$id)->delete($this->table_pengajuan);
        return ['status' => true, 'message' => 'Draft pengajuan ' . $row['nomor_pengajuan'] . ' dihapus.'];
    }

    // =====================================================================
    // RANTAI APPROVAL
    // =====================================================================

    public function init_approval($cutiId) {
        $cutiId = (int)$cutiId;

        $existing = $this->db->where('cuti_id', $cutiId)->count_all_results($this->table_approval);
        if ($existing > 0) {
            return true;
        }

        $rules = $this->get_rules(true);
        if (empty($rules)) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        $batch = [];
        $isFirst = true;

        foreach ($rules as $r) {
            $batch[] = [
                'cuti_id' => $cutiId,
                'rule_id' => (int)$r['id'],
                'urutan' => (int)$r['urutan'],
                'pegawai_id' => (int)$r['pegawai_id'],
                'pegawai_nama' => $r['nama_formatted'],
                'pegawai_nip' => $r['nip'],
                'pegawai_jabatan' => $r['pegawai_jabatan'],
                'jabatan_approval' => $r['jabatan_approval'],
                'status' => $isFirst ? 'pending' : 'waiting',
                'tanda_tangan' => $r['pegawai_tanda_tangan'] ?: null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $isFirst = false;
        }

        $this->db->insert_batch($this->table_approval, $batch);
        return true;
    }

    public function get_approvals($cutiId) {
        return $this->db->where('cuti_id', (int)$cutiId)
                        ->order_by('urutan', 'ASC')
                        ->get($this->table_approval)
                        ->result_array();
    }

    /**
     * Antrean approval: pengajuan yang tahap aktifnya menunggu keputusan.
     */
    public function get_antrean_approval($filter = []) {
        $this->db->select('c.*, a.id AS approval_id, a.urutan AS step_urutan,
                           a.pegawai_id AS approver_id, a.pegawai_nama AS approver_nama,
                           a.jabatan_approval, a.status AS step_status');
        $this->db->from($this->table_pengajuan . ' c');
        $this->db->join($this->table_approval . ' a', 'a.cuti_id = c.id AND a.status = "pending"', 'inner');
        $this->db->where_in('c.status', ['diajukan', 'proses']);

        if (!empty($filter['approver_id']) && $filter['approver_id'] !== 'all') {
            $this->db->where('a.pegawai_id', (int)$filter['approver_id']);
        }
        if (!empty($filter['search'])) {
            $this->db->group_start()
                     ->like('c.nomor_pengajuan', $filter['search'])
                     ->or_like('c.pegawai_nama', $filter['search'])
                     ->group_end();
        }

        $rows = $this->db->order_by('c.waktu_pengajuan', 'ASC')->get()->result_array();

        foreach ($rows as &$r) {
            $r['approvals'] = $this->get_approvals((int)$r['id']);
            $r['progress'] = $this->ringkas_progress($r['approvals']);
        }

        return $rows;
    }

    /**
     * Setujui atau tolak satu tahap. Tahap hanya dapat diproses bila seluruh
     * tahap sebelumnya sudah disetujui.
     */
    public function proses_approval($cutiId, $stepUrutan, $keputusan, $catatan = null, $userId = null, $userNama = 'Petugas') {
        $cutiId = (int)$cutiId;
        $stepUrutan = (int)$stepUrutan;

        $cuti = $this->db->where('id', $cutiId)->get($this->table_pengajuan)->row_array();
        if (!$cuti) {
            return ['status' => false, 'message' => 'Pengajuan tidak ditemukan.'];
        }
        if (!in_array($cuti['status'], ['diajukan', 'proses'], true)) {
            return ['status' => false, 'message' => 'Pengajuan ini sudah tidak dalam proses persetujuan.'];
        }

        $approvals = $this->get_approvals($cutiId);
        if (empty($approvals)) {
            return ['status' => false, 'message' => 'Rantai approval belum terbentuk.'];
        }

        $target = null;
        $belumLengkap = false;
        foreach ($approvals as $a) {
            if ((int)$a['urutan'] < $stepUrutan && $a['status'] !== 'approved') {
                $belumLengkap = true;
            }
            if ((int)$a['urutan'] === $stepUrutan) {
                $target = $a;
            }
        }

        if (!$target) {
            return ['status' => false, 'message' => 'Tahap approval tidak ditemukan.'];
        }
        if ($belumLengkap) {
            return ['status' => false, 'message' => 'Tahap sebelumnya belum disetujui.'];
        }
        if ($target['status'] === 'approved') {
            return ['status' => false, 'message' => 'Tahap ini sudah disetujui sebelumnya.'];
        }

        $now = date('Y-m-d H:i:s');
        $statusStep = ($keputusan === 'approve') ? 'approved' : 'rejected';

        $this->db->where('id', (int)$target['id'])->update($this->table_approval, [
            'status' => $statusStep,
            'catatan' => $catatan,
            'approved_by_user_id' => $userId,
            'approved_by_user_name' => $userNama,
            'waktu_approval' => $now,
            'ip_address' => $this->input->ip_address(),
            'updated_at' => $now,
        ]);

        if ($statusStep === 'rejected') {
            $this->_kembalikan_saldo($cuti);
            $this->db->where('id', $cutiId)->update($this->table_pengajuan, [
                'status' => 'ditolak',
                'catatan_penolakan' => $catatan,
                'waktu_selesai_approval' => $now,
                'updated_at' => $now,
            ]);
            $this->tulis_log($cutiId, 'REJECT', 'Ditolak pada tahap ' . $stepUrutan . ': ' . $catatan, $cuti['status'], 'ditolak', $userId, $userNama);

            return ['status' => true, 'message' => 'Pengajuan cuti ditolak. Saldo hak cuti dikembalikan.', 'final' => 'ditolak'];
        }

        // Aktifkan tahap berikutnya, atau selesaikan bila ini tahap terakhir.
        $next = null;
        foreach ($approvals as $a) {
            if ((int)$a['urutan'] > $stepUrutan) {
                $next = $a;
                break;
            }
        }

        if ($next) {
            $this->db->where('id', (int)$next['id'])->update($this->table_approval, [
                'status' => 'pending',
                'updated_at' => $now,
            ]);
            $this->db->where('id', $cutiId)->update($this->table_pengajuan, [
                'status' => 'proses',
                'updated_at' => $now,
            ]);
            $this->tulis_log($cutiId, 'APPROVE_STEP', 'Tahap ' . $stepUrutan . ' disetujui', $cuti['status'], 'proses', $userId, $userNama);

            return ['status' => true, 'message' => 'Tahap ' . $stepUrutan . ' disetujui. Diteruskan ke ' . $next['jabatan_approval'] . '.', 'final' => null];
        }

        $this->db->where('id', $cutiId)->update($this->table_pengajuan, [
            'status' => 'disetujui',
            'nomor_surat_izin' => $cuti['nomor_surat_izin'] ?: $this->generate_nomor_surat(),
            'waktu_selesai_approval' => $now,
            'updated_at' => $now,
        ]);
        $this->tulis_log($cutiId, 'APPROVE_FINAL', 'Seluruh tahap approval selesai', $cuti['status'], 'disetujui', $userId, $userNama);

        return ['status' => true, 'message' => 'Pengajuan cuti disetujui sepenuhnya. Surat izin cuti siap dicetak.', 'final' => 'disetujui'];
    }

    // =====================================================================
    // LOG & RINGKASAN
    // =====================================================================

    public function tulis_log($cutiId, $aksi, $keterangan = null, $sebelum = null, $sesudah = null, $userId = null, $userNama = null) {
        $this->db->insert($this->table_log, [
            'cuti_id' => (int)$cutiId,
            'aksi' => $aksi,
            'keterangan' => $keterangan,
            'status_sebelum' => $sebelum,
            'status_sesudah' => $sesudah,
            'user_id' => $userId,
            'user_nama' => $userNama,
            'ip_address' => $this->input->ip_address(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function get_log($cutiId) {
        return $this->db->where('cuti_id', (int)$cutiId)
                        ->order_by('id', 'DESC')
                        ->get($this->table_log)
                        ->result_array();
    }

    public function count_summary($tahun = null) {
        $tahun = (int)($tahun ?: date('Y'));

        $rows = $this->db->select('status, COUNT(*) AS jumlah, COALESCE(SUM(jumlah_hari), 0) AS total_hari')
                         ->where('tahun_hak', $tahun)
                         ->group_by('status')
                         ->get($this->table_pengajuan)
                         ->result_array();

        $summary = [
            'tahun' => $tahun,
            'draft' => 0, 'diajukan' => 0, 'proses' => 0,
            'disetujui' => 0, 'ditolak' => 0, 'dibatalkan' => 0,
            'total' => 0, 'total_hari_disetujui' => 0,
        ];

        foreach ($rows as $r) {
            $summary[$r['status']] = (int)$r['jumlah'];
            $summary['total'] += (int)$r['jumlah'];
            if ($r['status'] === 'disetujui') {
                $summary['total_hari_disetujui'] = (int)$r['total_hari'];
            }
        }

        $summary['menunggu_approval'] = $summary['diajukan'] + $summary['proses'];

        return $summary;
    }

    public function get_tahun_tersedia() {
        $rows = $this->db->select('DISTINCT tahun_hak AS tahun', FALSE)
                         ->order_by('tahun_hak', 'DESC')
                         ->get($this->table_pengajuan)
                         ->result_array();

        $tahun = array_map(function ($r) { return (int)$r['tahun']; }, $rows);

        $sekarang = (int)date('Y');
        if (!in_array($sekarang, $tahun, true)) {
            array_unshift($tahun, $sekarang);
        }

        return $tahun;
    }
}
