<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->rbac_lib->require_login();
        $this->load->model('rbac_model');
        $this->load->model('dashboard_model');
    }

    /**
     * Dashboard eksekutif: tren kunjungan, utilisasi pemeriksaan, TAT,
     * performa pendapatan, dan peringatan stok/kalibrasi.
     *
     * Rentang tanggal diambil dari query string supaya tautan dashboard
     * dengan periode tertentu bisa dibagikan dan di-bookmark.
     */
    public function index() {
        $cfg = $this->dashboard_model->get_config();

        $mulai = $this->input->get('mulai', TRUE);
        $selesai = $this->input->get('selesai', TRUE);

        if (!$mulai || !strtotime($mulai)) {
            $mulai = date('Y-m-d', strtotime('-' . ($cfg['periode_default'] - 1) . ' days'));
        }
        if (!$selesai || !strtotime($selesai)) {
            $selesai = date('Y-m-d');
        }

        $data['title'] = 'Dashboard Eksekutif - LIS PRO';
        $data['active_menu'] = 'dashboard';
        $data['user'] = $this->rbac_lib->user();
        $data['menu_tree'] = $this->rbac_lib->get_menu_tree();
        $data['all_roles'] = $this->rbac_model->get_all_roles();

        $data['cfg'] = $cfg;
        $data['mulai'] = $mulai;
        $data['selesai'] = $selesai;
        $data['kpi'] = $this->dashboard_model->kpi($mulai, $selesai);
        $data['tren_kunjungan'] = $this->dashboard_model->tren_kunjungan($mulai, $selesai);
        $data['komposisi_kategori'] = $this->dashboard_model->komposisi_kategori($mulai, $selesai);
        $data['top_pemeriksaan'] = $this->dashboard_model->top_pemeriksaan($mulai, $selesai);
        $data['hasil_abnormal'] = $this->dashboard_model->hasil_abnormal($mulai, $selesai);
        $data['tat_tahap'] = $this->dashboard_model->tat_per_tahap($mulai, $selesai);
        $data['tat_kategori'] = $this->dashboard_model->tat_per_kategori($mulai, $selesai);
        $data['tren_pendapatan'] = $this->dashboard_model->tren_pendapatan();
        $data['pendapatan_tipe'] = $this->dashboard_model->pendapatan_per_tipe($mulai, $selesai);
        $data['distribusi_status'] = $this->dashboard_model->distribusi_status($mulai, $selesai);
        $data['order_tertunda'] = $this->dashboard_model->order_tertunda();
        $data['peringatan'] = $this->dashboard_model->peringatan();

        $this->load->view('layout/header', $data);
        $this->load->view('layout/sidebar', $data);
        $this->load->view('layout/topbar', $data);
        $this->load->view('dashboard/index', $data);
        $this->load->view('layout/footer', $data);
    }

    public function roles() {
        $this->rbac_lib->require_access('roles', 'view');

        $data['title'] = 'Manajemen Hak Akses (RBAC) - LIS PRO';
        $data['active_menu'] = 'roles';
        $data['user'] = $this->rbac_lib->user();
        $data['menu_tree'] = $this->rbac_lib->get_menu_tree();
        $data['roles'] = $this->rbac_model->get_all_roles();

        $this->db->select('m.id as menu_id, m.menu_name, m.menu_url, m.parent_id, rp.role_id, rp.can_view, rp.can_create, rp.can_edit, rp.can_delete');
        $this->db->from('menus m');
        $this->db->join('role_permissions rp', 'rp.menu_id = m.id', 'left');
        $this->db->order_by('m.id', 'ASC');
        $data['permissions_raw'] = $this->db->get()->result_array();

        $this->load->view('layout/header', $data);
        $this->load->view('layout/sidebar', $data);
        $this->load->view('layout/topbar', $data);
        $this->load->view('dashboard/roles', $data);
        $this->load->view('layout/footer', $data);
    }
}
