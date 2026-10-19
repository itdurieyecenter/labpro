<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->rbac_lib->require_login();
        $this->load->model('rbac_model');
    }

    public function index() {
        $data['title'] = 'Dashboard Utama - LIS PRO';
        $data['active_menu'] = 'dashboard';
        $data['user'] = $this->rbac_lib->user();
        $data['menu_tree'] = $this->rbac_lib->get_menu_tree();
        $data['all_roles'] = $this->rbac_model->get_all_roles();
        
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
