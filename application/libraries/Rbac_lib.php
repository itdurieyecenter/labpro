<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rbac_lib {

    protected $CI;

    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model('rbac_model');
    }

    public function is_logged_in() {
        return (bool)$this->CI->session->userdata('logged_in');
    }

    public function require_login() {
        if (!$this->is_logged_in()) {
            $this->CI->session->set_flashdata('error', 'Silakan login terlebih dahulu untuk mengakses sistem.');
            redirect('login');
        }
    }

    public function user($key = null) {
        $user = $this->CI->session->userdata('user');
        if ($key !== null) {
            return isset($user[$key]) ? $user[$key] : null;
        }
        return $user;
    }

    public function role_id() {
        return $this->user('role_id');
    }

    public function role_name() {
        return $this->user('role_name');
    }

    public function has_access($menu_url, $action = 'view') {
        if (!$this->is_logged_in()) {
            return false;
        }

        $role_id = $this->role_id();
        if ((int)$role_id === 1) {
            return true;
        }

        return $this->CI->rbac_model->check_permission($role_id, $menu_url, $action);
    }

    public function require_access($menu_url, $action = 'view') {
        $this->require_login();
        if (!$this->has_access($menu_url, $action)) {
            show_error('Akses ditolak. Anda tidak memiliki izin untuk modul ini.', 403, '403 Forbidden - RBAC');
        }
    }

    public function get_menu_tree() {
        if (!$this->is_logged_in()) {
            return [];
        }

        $role_id = $this->role_id();
        $rawMenus = $this->CI->rbac_model->get_menus_by_role($role_id);

        $tree = [];
        $submenus = [];

        foreach ($rawMenus as $menu) {
            if ($menu['parent_id'] === null) {
                $menu['children'] = [];
                $tree[$menu['id']] = $menu;
            } else {
                $submenus[] = $menu;
            }
        }

        foreach ($submenus as $sub) {
            if (isset($tree[$sub['parent_id']])) {
                $tree[$sub['parent_id']]['children'][] = $sub;
            }
        }

        return array_values($tree);
    }
}
