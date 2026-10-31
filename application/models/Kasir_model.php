<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kasir_model extends CI_Model {

    protected $table = 'pendaftaran';
    protected $table_closing = 'kasir_closing';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Daftar tagihan untuk antrian kasir.
     * $f: status (UNPAID|PAID|GUARANTEED|all), tanggal, tipe_pasien, search
     */
    public function get_antrian($f = []) {
        $status = $f['status'] ?? 'UNPAID';
        $tanggal = trim($f['tanggal'] ?? '');
        $tipe = $f['tipe_pasien'] ?? 'all';
        $search = trim($f['search'] ?? '');

        $this->db->select('p.id, p.nomor_registrasi, p.nomor_billing, p.nama_pasien, p.tipe_pasien, p.instansi_pengirim,
            p.tanggal_pendaftaran, p.jam_pendaftaran, p.total_biaya, p.status_pembayaran, p.metode_pembayaran,
            p.waktu_bayar, p.nomor_kuitansi, p.instansi_penjamin, p.closing_id, p.alur_pembayaran, p.kategori_pemeriksaan, p.nomor_surat_pengantar');
        $this->db->from($this->table . ' p');

        if ($status !== 'all') {
            $this->db->where('p.status_pembayaran', $status);
        } else {
            $this->db->where('p.status_pembayaran !=', 'CANCELLED');
        }
        if ($tanggal !== '') {
            $this->db->where('p.tanggal_pendaftaran', $tanggal);
        }
        if ($tipe !== 'all' && $tipe !== '') {
            $this->db->where('p.tipe_pasien', $tipe);
        }
        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('p.nomor_registrasi', $search);
            $this->db->or_like('p.nomor_billing', $search);
            $this->db->or_like('p.nama_pasien', $search);
            $this->db->or_like('p.instansi_pengirim', $search);
            $this->db->or_like('p.nomor_kuitansi', $search);
            $this->db->group_end();
        }

        $this->db->order_by('p.tanggal_pendaftaran', 'ASC');
        $this->db->order_by('p.jam_pendaftaran', 'ASC');
        $this->db->limit(500);
        return $this->db->get()->result_array();
    }

    /**
     * Ringkasan kartu antrian kasir (UNPAID semua tanggal, PAID & piutang hari ini).
     */
    public function get_summary($kasirId) {
        $today = date('Y-m-d');
        $awal = $today . ' 00:00:00';
        $akhir = date('Y-m-d', strtotime('+1 day')) . ' 00:00:00';

        $unpaid = $this->db->select('COUNT(*) c, COALESCE(SUM(total_biaya),0) s', false)
            ->where('status_pembayaran', 'UNPAID')->get($this->table)->row_array();

        $paid = $this->db->select('COUNT(*) c, COALESCE(SUM(total_biaya),0) s', false)
            ->where('status_pembayaran', 'PAID')
            ->where('waktu_bayar >=', $awal)->where('waktu_bayar <', $akhir)
            ->where('kasir_id', (int)$kasirId)
            ->get($this->table)->row_array();

        $piutang = $this->db->select('COUNT(*) c, COALESCE(SUM(total_biaya),0) s', false)
            ->where('status_pembayaran', 'GUARANTEED')
            ->where('waktu_bayar >=', $awal)->where('waktu_bayar <', $akhir)
            ->get($this->table)->row_array();

        $belumClosing = $this->db->where('status_pembayaran', 'PAID')
            ->where('kasir_id', (int)$kasirId)
            ->where('closing_id IS NULL', null, false)
            ->where('waktu_bayar <', $awal)
            ->count_all_results($this->table);

        return [
            'unpaid_count' => (int)$unpaid['c'],
            'unpaid_total' => (float)$unpaid['s'],
            'paid_count' => (int)$paid['c'],
            'paid_total' => (float)$paid['s'],
            'piutang_count' => (int)$piutang['c'],
            'piutang_total' => (float)$piutang['s'],
            'belum_closing_lampau' => (int)$belumClosing,
        ];
    }

    /**
     * Transaksi PAID milik kasir pada tanggal tertentu yang belum di-closing.
     */
    protected function _open_transactions($tanggal, $kasirId) {
        $this->db->select('p.id, p.nomor_registrasi, p.nomor_kuitansi, p.nama_pasien, p.instansi_pengirim, p.total_biaya,
            p.metode_pembayaran, p.waktu_bayar, p.akun_bayar_id,
            COALESCE(ab.tipe_transaksi, \'cash\') AS tipe_transaksi,
            COALESCE(ab.nama_bayar, p.metode_pembayaran) AS nama_akun', false);
        $this->db->from($this->table . ' p');
        $this->db->join('akun_bayar ab', 'ab.id = p.akun_bayar_id', 'left');
        $this->db->where('p.status_pembayaran', 'PAID');
        $this->db->where('p.kasir_id', (int)$kasirId);
        $this->db->where('p.closing_id IS NULL', null, false);
        $this->db->where('p.waktu_bayar >=', $tanggal . ' 00:00:00');
        $this->db->where('p.waktu_bayar <', date('Y-m-d', strtotime($tanggal . ' +1 day')) . ' 00:00:00');
        $this->db->order_by('p.waktu_bayar', 'ASC');
        return $this->db->get()->result_array();
    }

    /**
     * Rekap transaksi untuk preview closing: total, tunai/non-tunai, dan rincian per akun bayar.
     */
    public function closing_preview($tanggal, $kasirId) {
        $rows = $this->_open_transactions($tanggal, $kasirId);

        $total = 0.0;
        $tunai = 0.0;
        $perAkun = [];
        foreach ($rows as $r) {
            $nilai = (float)$r['total_biaya'];
            $total += $nilai;
            if ($r['tipe_transaksi'] === 'cash') {
                $tunai += $nilai;
            }
            $key = $r['nama_akun'] ?: 'Tanpa Akun';
            if (!isset($perAkun[$key])) {
                $perAkun[$key] = ['akun' => $key, 'tipe' => $r['tipe_transaksi'], 'jumlah' => 0, 'total' => 0.0];
            }
            $perAkun[$key]['jumlah']++;
            $perAkun[$key]['total'] += $nilai;
        }

        $piutang = $this->db->select('COUNT(*) c, COALESCE(SUM(total_biaya),0) s', false)
            ->where('status_pembayaran', 'GUARANTEED')
            ->where('waktu_bayar >=', $tanggal . ' 00:00:00')
            ->where('waktu_bayar <', date('Y-m-d', strtotime($tanggal . ' +1 day')) . ' 00:00:00')
            ->get($this->table)->row_array();

        $sudahClosing = $this->db->where('tanggal', $tanggal)
            ->where('kasir_id', (int)$kasirId)
            ->count_all_results($this->table_closing);

        return [
            'rows' => $rows,
            'jumlah' => count($rows),
            'total' => $total,
            'tunai' => $tunai,
            'non_tunai' => $total - $tunai,
            'per_akun' => array_values($perAkun),
            'piutang_count' => (int)$piutang['c'],
            'piutang_total' => (float)$piutang['s'],
            'sudah_closing' => (int)$sudahClosing,
        ];
    }

    public function generate_closing_number($tanggal) {
        $prefix = 'CLS-' . date('Ymd', strtotime($tanggal)) . '-';

        $last = $this->db->select('nomor_closing')
            ->like('nomor_closing', $prefix, 'after')
            ->order_by('id', 'DESC')->limit(1)
            ->get($this->table_closing)->row_array();

        $seq = 1;
        if ($last) {
            $parts = explode('-', $last['nomor_closing']);
            if (isset($parts[2]) && is_numeric($parts[2])) {
                $seq = (int)$parts[2] + 1;
            }
        }
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Tutup kasir: bekukan transaksi PAID yang belum di-closing pada tanggal tsb.
     */
    public function do_closing($tanggal, $kasirId, $kasirNama, $uangFisik, $catatan = '') {
        $this->db->trans_start();

        $preview = $this->closing_preview($tanggal, $kasirId);
        if ($preview['jumlah'] === 0) {
            $this->db->trans_complete();
            return ['status' => false, 'message' => 'Tidak ada transaksi lunas yang belum di-closing pada tanggal ini.'];
        }

        $selisih = $uangFisik - $preview['tunai'];
        $nomor = $this->generate_closing_number($tanggal);

        $this->db->insert($this->table_closing, [
            'nomor_closing' => $nomor,
            'tanggal' => $tanggal,
            'kasir_id' => (int)$kasirId,
            'kasir_nama' => $kasirNama,
            'jumlah_transaksi' => $preview['jumlah'],
            'total_pendapatan' => $preview['total'],
            'total_tunai' => $preview['tunai'],
            'total_non_tunai' => $preview['non_tunai'],
            'uang_fisik_tunai' => $uangFisik,
            'selisih_tunai' => $selisih,
            'rincian_akun' => json_encode($preview['per_akun']),
            'catatan' => $catatan,
        ]);
        $closingId = $this->db->insert_id();

        $ids = array_column($preview['rows'], 'id');
        $this->db->where_in('id', $ids)->where('closing_id IS NULL', null, false)
            ->update($this->table, ['closing_id' => $closingId]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['status' => false, 'message' => 'Gagal menyimpan closing kasir.'];
        }

        return [
            'status' => true,
            'message' => 'Closing kasir ' . $nomor . ' berhasil disimpan.',
            'closing_id' => $closingId,
            'nomor_closing' => $nomor,
        ];
    }

    public function get_closing_history($kasirId = null, $limit = 30) {
        if ($kasirId !== null) {
            $this->db->where('kasir_id', (int)$kasirId);
        }
        return $this->db->order_by('tanggal', 'DESC')->order_by('id', 'DESC')
            ->limit($limit)->get($this->table_closing)->result_array();
    }

    public function get_closing($id) {
        $c = $this->db->get_where($this->table_closing, ['id' => (int)$id])->row_array();
        if (!$c) {
            return null;
        }
        $c['rincian'] = json_decode($c['rincian_akun'] ?? '[]', true) ?: [];
        $c['transaksi'] = $this->db->select('p.nomor_registrasi, p.nomor_kuitansi, p.nama_pasien, p.instansi_pengirim, p.total_biaya, p.metode_pembayaran, p.waktu_bayar')
            ->from($this->table . ' p')
            ->where('p.closing_id', (int)$id)
            ->order_by('p.waktu_bayar', 'ASC')
            ->get()->result_array();
        return $c;
    }
}
