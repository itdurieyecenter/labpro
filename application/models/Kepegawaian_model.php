<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model pendukung modul Kepegawaian di luar manajemen cuti:
 * pemantauan masa berlaku kredensial, monitoring beban kerja, dan
 * pembacaan audit trail.
 */
class Kepegawaian_model extends CI_Model {

    protected $table_pegawai = 'pegawai';
    protected $table_detail = 'pendaftaran_detail';
    protected $table_approval = 'pendaftaran_approval';
    protected $table_cuti = 'cuti_pengajuan';
    protected $table_audit = 'activity_log';

    public function __construct() {
        parent::__construct();
        $this->load->model('cuti_model');
    }

    // =====================================================================
    // KREDENSIAL STR & SIP
    // =====================================================================

    public function get_config() {
        return [
            'peringatan_hari' => (int)get_setting('kredensial_peringatan_hari', 90),
            'kritis_hari' => (int)get_setting('kredensial_kritis_hari', 30),
            'target_harian' => (int)get_setting('beban_target_harian', 20),
        ];
    }

    /**
     * Tafsirkan nilai masa berlaku yang mungkin berupa tanggal atau teks.
     *
     * Sejak UU No. 17 Tahun 2023 STR tenaga kesehatan berlaku seumur hidup,
     * sehingga kolom masa_berlaku_str kerap berisi teks "Seumur Hidup".
     * Nilai semacam itu diperlakukan sebagai tidak pernah kedaluwarsa.
     */
    public function tafsir_masa_berlaku($nilai, $cfg = null) {
        $cfg = $cfg ?: $this->get_config();

        $nilai = trim((string)$nilai);
        if ($nilai === '' || strtolower($nilai) === 'null') {
            return ['status' => 'kosong', 'label' => 'Belum diisi', 'tanggal' => null, 'sisa_hari' => null];
        }

        if (preg_match('/seumur\s*hidup|tidak\s*terbatas|permanen/i', $nilai)) {
            return ['status' => 'seumur_hidup', 'label' => 'Seumur Hidup', 'tanggal' => null, 'sisa_hari' => null];
        }

        $ts = strtotime($nilai);
        if ($ts === false || $ts <= 0) {
            // Nilai bebas yang tidak dapat ditafsirkan, tampilkan apa adanya.
            return ['status' => 'tidak_terbaca', 'label' => $nilai, 'tanggal' => null, 'sisa_hari' => null];
        }

        $tanggal = date('Y-m-d', $ts);
        $sisa = (int)floor((strtotime($tanggal) - strtotime(date('Y-m-d'))) / 86400);

        if ($sisa < 0) {
            $status = 'kedaluwarsa';
        } elseif ($sisa <= $cfg['kritis_hari']) {
            $status = 'kritis';
        } elseif ($sisa <= $cfg['peringatan_hari']) {
            $status = 'peringatan';
        } else {
            $status = 'aman';
        }

        return ['status' => $status, 'label' => $tanggal, 'tanggal' => $tanggal, 'sisa_hari' => $sisa];
    }

    /**
     * Daftar kredensial seluruh pegawai beserta status masa berlakunya.
     */
    public function get_kredensial($filter = []) {
        $cfg = $this->get_config();

        $this->db->select('id, nip, nama_lengkap, gelar_depan, gelar_belakang, nama_jabatan,
                           seksi_ruangan, status_pegawai, tingkat_pendidikan, jurusan,
                           nomor_str, tgl_terbit_str, masa_berlaku_str,
                           nomor_sip, masa_berlaku_sip, sertifikasi_khusus');
        $this->db->from($this->table_pegawai);

        if (!empty($filter['seksi_ruangan']) && $filter['seksi_ruangan'] !== 'all') {
            $this->db->where('seksi_ruangan', $filter['seksi_ruangan']);
        }
        if (!empty($filter['search'])) {
            $this->db->group_start()
                     ->like('nama_lengkap', $filter['search'])
                     ->or_like('nip', $filter['search'])
                     ->or_like('nomor_str', $filter['search'])
                     ->or_like('nomor_sip', $filter['search'])
                     ->group_end();
        }

        $rows = $this->db->order_by('nama_lengkap', 'ASC')->get()->result_array();

        $prioritas = ['kedaluwarsa' => 0, 'kritis' => 1, 'peringatan' => 2, 'kosong' => 3, 'tidak_terbaca' => 4, 'aman' => 5, 'seumur_hidup' => 6];

        foreach ($rows as &$r) {
            $r['nama_formatted'] = $this->cuti_model->format_nama_pegawai($r);
            $r['str'] = $this->tafsir_masa_berlaku($r['masa_berlaku_str'], $cfg);
            $r['sip'] = $this->tafsir_masa_berlaku($r['masa_berlaku_sip'], $cfg);

            // Nomor kosong membuat kredensial dianggap belum lengkap walau
            // kolom masa berlakunya terisi.
            if (trim((string)$r['nomor_str']) === '') $r['str'] = ['status' => 'kosong', 'label' => 'Belum diisi', 'tanggal' => null, 'sisa_hari' => null];
            if (trim((string)$r['nomor_sip']) === '') $r['sip'] = ['status' => 'kosong', 'label' => 'Belum diisi', 'tanggal' => null, 'sisa_hari' => null];

            $skorStr = $prioritas[$r['str']['status']] ?? 9;
            $skorSip = $prioritas[$r['sip']['status']] ?? 9;
            $r['status_terburuk'] = $skorStr <= $skorSip ? $r['str']['status'] : $r['sip']['status'];
            $r['_skor'] = min($skorStr, $skorSip);
        }
        unset($r);

        if (!empty($filter['status']) && $filter['status'] !== 'all') {
            $rows = array_values(array_filter($rows, function ($r) use ($filter) {
                return $r['str']['status'] === $filter['status'] || $r['sip']['status'] === $filter['status'];
            }));
        }

        // Yang paling mendesak tampil lebih dulu.
        usort($rows, function ($a, $b) {
            return $a['_skor'] <=> $b['_skor'] ?: strcmp($a['nama_lengkap'], $b['nama_lengkap']);
        });

        return $rows;
    }

    public function ringkasan_kredensial() {
        $rows = $this->get_kredensial();

        $ringkas = ['total' => count($rows), 'kedaluwarsa' => 0, 'kritis' => 0, 'peringatan' => 0, 'kosong' => 0, 'aman' => 0];

        foreach ($rows as $r) {
            foreach (['str', 'sip'] as $jenis) {
                $s = $r[$jenis]['status'];
                if (isset($ringkas[$s])) {
                    $ringkas[$s]++;
                } elseif ($s === 'seumur_hidup') {
                    $ringkas['aman']++;
                }
            }
        }

        $ringkas['perlu_tindakan'] = $ringkas['kedaluwarsa'] + $ringkas['kritis'];

        return $ringkas;
    }

    // =====================================================================
    // BEBAN KERJA PEGAWAI
    // =====================================================================

    /**
     * Rekap beban kerja per pegawai pada satu rentang tanggal.
     *
     * Dua sumber dipakai: jumlah item pemeriksaan yang hasilnya diinput
     * (pendaftaran_detail.analis_id) dan jumlah pengesahan yang dilakukan
     * (pendaftaran_approval). Hari cuti yang disetujui mengurangi hari kerja
     * efektif sehingga rata-rata harian tetap adil.
     */
    public function get_beban_kerja($mulai, $selesai, $filter = []) {
        $cfg = $this->get_config();

        $this->db->select('id, nip, nama_lengkap, gelar_depan, gelar_belakang, nama_jabatan, seksi_ruangan, status_pegawai');
        $this->db->from($this->table_pegawai);

        if (!empty($filter['seksi_ruangan']) && $filter['seksi_ruangan'] !== 'all') {
            $this->db->where('seksi_ruangan', $filter['seksi_ruangan']);
        }
        if (!empty($filter['search'])) {
            $this->db->group_start()
                     ->like('nama_lengkap', $filter['search'])
                     ->or_like('nip', $filter['search'])
                     ->group_end();
        }

        $pegawai = $this->db->order_by('nama_lengkap', 'ASC')->get()->result_array();

        $hariKerja = $this->cuti_model->hitung_hari($mulai, $selesai, true);

        foreach ($pegawai as &$p) {
            $id = (int)$p['id'];

            $p['nama_formatted'] = $this->cuti_model->format_nama_pegawai($p);

            $p['jml_pemeriksaan'] = (int)$this->db->where('analis_id', $id)
                                                  ->where('waktu_input_hasil >=', $mulai . ' 00:00:00')
                                                  ->where('waktu_input_hasil <=', $selesai . ' 23:59:59')
                                                  ->count_all_results($this->table_detail);

            $p['jml_validasi'] = (int)$this->db->where('pegawai_id', $id)
                                               ->where('status', 'approved')
                                               ->where('waktu_approval >=', $mulai . ' 00:00:00')
                                               ->where('waktu_approval <=', $selesai . ' 23:59:59')
                                               ->count_all_results($this->table_approval);

            $cuti = $this->db->select('COALESCE(SUM(jumlah_hari), 0) AS hari', FALSE)
                             ->where('pegawai_id', $id)
                             ->where('status', 'disetujui')
                             ->where('tanggal_mulai <=', $selesai)
                             ->where('tanggal_selesai >=', $mulai)
                             ->get($this->table_cuti)
                             ->row_array();

            $p['hari_cuti'] = (int)($cuti['hari'] ?? 0);
            $p['hari_kerja_efektif'] = max(0, $hariKerja - $p['hari_cuti']);

            $p['total_aktivitas'] = $p['jml_pemeriksaan'] + $p['jml_validasi'];
            $p['rata_harian'] = $p['hari_kerja_efektif'] > 0
                ? round($p['jml_pemeriksaan'] / $p['hari_kerja_efektif'], 1)
                : 0;

            $p['target_harian'] = $cfg['target_harian'];
            $p['capaian_persen'] = $cfg['target_harian'] > 0
                ? (int)round(($p['rata_harian'] / $cfg['target_harian']) * 100)
                : 0;
        }
        unset($p);

        usort($pegawai, function ($a, $b) {
            return $b['total_aktivitas'] <=> $a['total_aktivitas'];
        });

        return [
            'periode' => ['mulai' => $mulai, 'selesai' => $selesai, 'hari_kerja' => $hariKerja],
            'target_harian' => $cfg['target_harian'],
            'data' => $pegawai,
        ];
    }

    // =====================================================================
    // AUDIT TRAIL
    // =====================================================================

    public function get_audit_datatable($params) {
        $draw = (int)($params['draw'] ?? 1);
        $start = (int)($params['start'] ?? 0);
        $length = (int)($params['length'] ?? 25);
        $search = trim($params['search']['value'] ?? '');

        $recordsTotal = $this->db->count_all_results($this->table_audit);

        $this->db->from($this->table_audit);
        $this->_filter_audit($params, $search);
        $recordsFiltered = $this->db->count_all_results('', FALSE);

        $this->db->order_by('id', 'DESC');
        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        return [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $this->db->get()->result_array(),
        ];
    }

    private function _filter_audit($params, $search) {
        if ($search !== '') {
            $this->db->group_start()
                     ->like('user_nama', $search)
                     ->or_like('modul', $search)
                     ->or_like('aksi', $search)
                     ->or_like('deskripsi', $search)
                     ->or_like('ip_address', $search)
                     ->group_end();
        }

        if (!empty($params['modul']) && $params['modul'] !== 'all') {
            $this->db->where('modul', $params['modul']);
        }
        if (!empty($params['user_id']) && $params['user_id'] !== 'all') {
            $this->db->where('user_id', (int)$params['user_id']);
        }
        if (!empty($params['tanggal_mulai'])) {
            $this->db->where('created_at >=', $params['tanggal_mulai'] . ' 00:00:00');
        }
        if (!empty($params['tanggal_selesai'])) {
            $this->db->where('created_at <=', $params['tanggal_selesai'] . ' 23:59:59');
        }
    }

    public function get_audit_modul() {
        $rows = $this->db->select('modul, COUNT(*) AS jumlah')
                         ->group_by('modul')
                         ->order_by('modul', 'ASC')
                         ->get($this->table_audit)
                         ->result_array();
        return $rows;
    }

    public function ringkasan_audit() {
        $hariIni = $this->db->where('created_at >=', date('Y-m-d') . ' 00:00:00')
                            ->count_all_results($this->table_audit);

        $mingguIni = $this->db->where('created_at >=', date('Y-m-d', strtotime('-7 days')) . ' 00:00:00')
                              ->count_all_results($this->table_audit);

        $penggunaAktif = $this->db->select('COUNT(DISTINCT user_id) AS jml', FALSE)
                                  ->where('created_at >=', date('Y-m-d', strtotime('-7 days')) . ' 00:00:00')
                                  ->get($this->table_audit)
                                  ->row_array();

        return [
            'total' => $this->db->count_all_results($this->table_audit),
            'hari_ini' => $hariIni,
            'minggu_ini' => $mingguIni,
            'pengguna_aktif' => (int)($penggunaAktif['jml'] ?? 0),
        ];
    }

    public function get_user_options() {
        return $this->db->select('id, username, full_name')
                        ->order_by('full_name', 'ASC')
                        ->get('users')
                        ->result_array();
    }
}
