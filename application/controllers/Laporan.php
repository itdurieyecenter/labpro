<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Laporan extends CI_Controller {

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

        // Base Query Range
        $this->db->from('pendaftaran');
        $this->db->where('tanggal_pendaftaran >=', $startDate);
        $this->db->where('tanggal_pendaftaran <=', $endDate);
        if ($tipePasien !== 'all') {
            $this->db->where('tipe_pasien', $tipePasien);
        }
        $data['registrations'] = $this->db->order_by('tanggal_pendaftaran', 'DESC')->get()->result_array();

        // 1. Rekapitulasi Kunjungan
        $totRegistrasi = count($data['registrations']);
        $totOmset = 0;
        $totPaid = 0;
        $totPiutang = 0;
        $tipeCounts = ['mandiri' => 0, 'rujukan_faskes' => 0, 'perusahaan_lingkungan' => 0];

        foreach ($data['registrations'] as $reg) {
            $totOmset += (float)$reg['total_biaya'];
            if ($reg['status_pembayaran'] === 'PAID') {
                $totPaid += (float)$reg['total_biaya'];
            } elseif ($reg['status_pembayaran'] === 'GUARANTEED') {
                $totPiutang += (float)$reg['total_biaya'];
            }
            if (isset($tipeCounts[$reg['tipe_pasien']])) {
                $tipeCounts[$reg['tipe_pasien']]++;
            }
        }

        $data['tot_registrasi'] = $totRegistrasi;
        $data['tot_omset'] = $totOmset;
        $data['tot_paid'] = $totPaid;
        $data['tot_piutang'] = $totPiutang;
        $data['tipe_counts'] = $tipeCounts;

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

        $this->db->from('pendaftaran');
        $this->db->where('tanggal_pendaftaran >=', $startDate);
        $this->db->where('tanggal_pendaftaran <=', $endDate);
        if ($tipePasien !== 'all') {
            $this->db->where('tipe_pasien', $tipePasien);
        }
        $data['registrations'] = $this->db->order_by('tanggal_pendaftaran', 'ASC')->get()->result_array();
        $data['start_date'] = $startDate;
        $data['end_date'] = $endDate;

        $totOmset = 0; $totPaid = 0; $totPiutang = 0;
        foreach ($data['registrations'] as $r) {
            $totOmset += (float)$r['total_biaya'];
            if ($r['status_pembayaran'] === 'PAID') $totPaid += (float)$r['total_biaya'];
            elseif ($r['status_pembayaran'] === 'GUARANTEED') $totPiutang += (float)$r['total_biaya'];
        }

        $data['tot_omset'] = $totOmset;
        $data['tot_paid'] = $totPaid;
        $data['tot_piutang'] = $totPiutang;

        $this->load->view('laporan/cetak', $data);
    }
}
