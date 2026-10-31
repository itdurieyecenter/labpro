<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Laporan extends CI_Controller {

    const MAX_ROWS_TABEL = 300;
    const MAX_ROWS_CETAK = 5000;

    public function __construct() {
        parent::__construct();
        $this->rbac_lib->require_login();
        $this->load->model('pendaftaran_model');
        $this->load->model('rekening_model');
        $this->load->model('rbac_model');
    }

    private function _log($action, $message) {
        log_message('info', sprintf("[LAPORAN] USER=%s | ACTION=%s | MSG=%s", $this->rbac_lib->user('username'), $action, $message));
    }

    /**
     * Halaman Rekapitulasi Pelayanan & Billing BLUD LIS PRO
     */
    public function index() {
        $this->rbac_lib->require_access('laporan', 'view');

        $startDate = $this->input->get('start_date', TRUE) ?: date('Y-m-01');
        $endDate = $this->input->get('end_date', TRUE) ?: date('Y-m-d');
        $tipePasien = $this->input->get('tipe_pasien', TRUE) ?: 'all';

        $data['title'] = 'Laporan & Rekapitulasi LIS & Billing BLUD - Labkesda Riau';
        $data['active_menu'] = 'laporan';
        $data['user'] = $this->rbac_lib->user();
        $data['menu_tree'] = $this->rbac_lib->get_menu_tree();
        $data['start_date'] = $startDate;
        $data['end_date'] = $endDate;
        $data['tipe_pasien'] = $tipePasien;

        // 1. Rekapitulasi kunjungan: dihitung di SQL (tidak memuat seluruh baris ke memori PHP)
        $this->db->select("COUNT(*) AS n, COALESCE(SUM(total_biaya),0) AS omset,
            COALESCE(SUM(CASE WHEN status_pembayaran='PAID' THEN total_biaya END),0) AS paid,
            COALESCE(SUM(CASE WHEN status_pembayaran='GUARANTEED' THEN total_biaya END),0) AS piutang,
            SUM(tipe_pasien='mandiri') AS c_mandiri, SUM(tipe_pasien='rujukan_faskes') AS c_rujukan, SUM(tipe_pasien='perusahaan_lingkungan') AS c_perusahaan", false);
        $this->db->from('pendaftaran');
        $this->db->where('tanggal_pendaftaran >=', $startDate);
        $this->db->where('tanggal_pendaftaran <=', $endDate);
        if ($tipePasien !== 'all') {
            $this->db->where('tipe_pasien', $tipePasien);
        }
        $agg = $this->db->get()->row_array();

        $data['tot_registrasi'] = (int)$agg['n'];
        $data['tot_omset'] = (float)$agg['omset'];
        $data['tot_paid'] = (float)$agg['paid'];
        $data['tot_piutang'] = (float)$agg['piutang'];
        $data['tipe_counts'] = [
            'mandiri' => (int)$agg['c_mandiri'],
            'rujukan_faskes' => (int)$agg['c_rujukan'],
            'perusahaan_lingkungan' => (int)$agg['c_perusahaan'],
        ];

        // Tabel rincian dibatasi agar halaman/memori tetap ringan; rekap lengkap ada di kartu & tombol cetak
        $this->db->from('pendaftaran');
        $this->db->where('tanggal_pendaftaran >=', $startDate);
        $this->db->where('tanggal_pendaftaran <=', $endDate);
        if ($tipePasien !== 'all') {
            $this->db->where('tipe_pasien', $tipePasien);
        }
        $data['registrations'] = $this->db->order_by('tanggal_pendaftaran', 'DESC')->order_by('id', 'DESC')->limit(self::MAX_ROWS_TABEL)->get()->result_array();

        // 2. Rekapitulasi Pembayaran per Akun Bayar Kas/Bank
        $data['rekap_kasir'] = $this->db->select('COALESCE(ab.nama_bayar, p.metode_pembayaran, "Kas Tunai") as akun_nama, COUNT(p.id) as jumlah_trx, SUM(p.total_biaya) as total_nominal')
            ->from('pendaftaran p')
            ->join('akun_bayar ab', 'ab.id = p.akun_bayar_id', 'left')
            ->where('p.tanggal_pendaftaran >=', $startDate)
            ->where('p.tanggal_pendaftaran <=', $endDate)
            ->where('p.status_pembayaran', 'PAID')
            ->group_by('akun_nama')
            ->get()
            ->result_array();

        // 3. Rekapitulasi Piutang per Penjamin / Mitra
        $data['rekap_piutang'] = $this->db->select('COALESCE(p.instansi_penjamin, "Mitra PKS") as mitra_nama, p.nomor_pks_spk, COUNT(p.id) as jumlah_pasien, SUM(p.total_biaya) as total_piutang')
            ->from('pendaftaran p')
            ->where('p.tanggal_pendaftaran >=', $startDate)
            ->where('p.tanggal_pendaftaran <=', $endDate)
            ->where('p.status_pembayaran', 'GUARANTEED')
            ->group_by('mitra_nama, p.nomor_pks_spk')
            ->get()
            ->result_array();

        // 4. Rekapitulasi 10 Parameter Uji Terbanyak
        $data['top_params'] = $this->db->select('pd.nama_item, pd.kategori_lab, COUNT(pd.id) as frekuensi, SUM(pd.tarif) as total_tarif')
            ->from('pendaftaran_detail pd')
            ->join('pendaftaran p', 'p.id = pd.pendaftaran_id')
            ->where('p.tanggal_pendaftaran >=', $startDate)
            ->where('p.tanggal_pendaftaran <=', $endDate)
            ->where('pd.tipe_item', 'parameter')
            ->group_by('pd.nama_item, pd.kategori_lab')
            ->order_by('frekuensi', 'DESC')
            ->limit(10)
            ->get()
            ->result_array();

        $this->_log('VIEW', sprintf("Laporan diakses range %s s.d %s", $startDate, $endDate));

        $this->load->view('layout/header', $data);
        $this->load->view('layout/sidebar', $data);
        $this->load->view('layout/topbar', $data);
        $this->load->view('laporan/index', $data);
        $this->load->view('layout/footer', $data);
    }

    /**
     * Cetak Lembar Resmi Rekapitulasi Pelayanan LIS & Billing BLUD
     */
    public function cetak() {
        $this->rbac_lib->require_access('laporan', 'view');

        $startDate = $this->input->get('start_date', TRUE) ?: date('Y-m-01');
        $endDate = $this->input->get('end_date', TRUE) ?: date('Y-m-d');
        $tipePasien = $this->input->get('tipe_pasien', TRUE) ?: 'all';

        $this->db->select("COALESCE(SUM(total_biaya),0) AS omset,
            COALESCE(SUM(CASE WHEN status_pembayaran='PAID' THEN total_biaya END),0) AS paid,
            COALESCE(SUM(CASE WHEN status_pembayaran='GUARANTEED' THEN total_biaya END),0) AS piutang", false);
        $this->db->from('pendaftaran');
        $this->db->where('tanggal_pendaftaran >=', $startDate);
        $this->db->where('tanggal_pendaftaran <=', $endDate);
        if ($tipePasien !== 'all') {
            $this->db->where('tipe_pasien', $tipePasien);
        }
        $agg = $this->db->get()->row_array();
        $data['tot_omset'] = (float)$agg['omset'];
        $data['tot_paid'] = (float)$agg['paid'];
        $data['tot_piutang'] = (float)$agg['piutang'];
        $data['start_date'] = $startDate;
        $data['end_date'] = $endDate;

        // Baris diambil bertahap (keyset, 1000/baris-chunk) supaya cetak ratusan ribu baris tidak menghabiskan RAM
        $db = $this->db;
        $data['max_rows_cetak'] = self::MAX_ROWS_CETAK;
        $db->where('tanggal_pendaftaran >=', $startDate)->where('tanggal_pendaftaran <=', $endDate);
        if ($tipePasien !== 'all') {
            $db->where('tipe_pasien', $tipePasien);
        }
        $data['tot_registrasi'] = (int)$db->count_all_results('pendaftaran');
        $data['reg_rows'] = (function () use ($db, $startDate, $endDate, $tipePasien) {
            $lastDate = null;
            $lastId = 0;
            $sent = 0;
            while ($sent < self::MAX_ROWS_CETAK) {
                $db->from('pendaftaran');
                $db->where('tanggal_pendaftaran >=', $startDate);
                $db->where('tanggal_pendaftaran <=', $endDate);
                if ($tipePasien !== 'all') {
                    $db->where('tipe_pasien', $tipePasien);
                }
                if ($lastDate !== null) {
                    $db->group_start()->where('tanggal_pendaftaran >', $lastDate)
                        ->or_group_start()->where('tanggal_pendaftaran', $lastDate)->where('id >', $lastId)->group_end()
                        ->group_end();
                }
                $rows = $db->order_by('tanggal_pendaftaran', 'ASC')->order_by('id', 'ASC')->limit(1000)->get()->result_array();
                if (!$rows) {
                    return;
                }
                foreach ($rows as $r) {
                    if ($sent++ >= self::MAX_ROWS_CETAK) {
                        return;
                    }
                    yield $r;
                }
                $last = end($rows);
                $lastDate = $last['tanggal_pendaftaran'];
                $lastId = (int)$last['id'];
                unset($rows);
            }
        })();

        $this->load->view('laporan/cetak', $data);
    }
}
