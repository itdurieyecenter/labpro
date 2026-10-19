<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Menu extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->rbac_lib->require_login();
        $this->load->model('rbac_model');
    }

    public function index() {
        // Hanya user dengan izin atau admin yang dapat mengelola menu
        $data['title'] = 'Manajemen Menu Navigasi - LIS PRO';
        $data['active_menu'] = 'menu';
        $data['user'] = $this->rbac_lib->user();
        $data['menu_tree'] = $this->rbac_lib->get_menu_tree();
        
        $data['all_menus'] = $this->rbac_model->get_all_menus();
        $data['root_menus'] = $this->rbac_model->get_root_menus();

        // Statistik ringkas menu
        $total = count($data['all_menus']);
        $rootCount = count($data['root_menus']);
        $subCount = $total - $rootCount;
        $activeCount = 0;
        foreach ($data['all_menus'] as $m) {
            if ((int)$m['is_active'] === 1) {
                $activeCount++;
            }
        }

        $data['stats'] = [
            'total' => $total,
            'root' => $rootCount,
            'submenu' => $subCount,
            'active' => $activeCount
        ];

        $this->load->view('layout/header', $data);
        $this->load->view('layout/sidebar', $data);
        $this->load->view('layout/topbar', $data);
        $this->load->view('menu/index', $data);
        $this->load->view('layout/footer', $data);
    }

    public function fetch() {
        $menus = $this->rbac_model->get_all_menus();
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'data' => $menus
            ]));
    }

    public function get($id) {
        $menu = $this->rbac_model->get_menu_by_id((int)$id);
        if (!$menu) {
            return $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Menu tidak ditemukan.'
                ]));
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'data' => $menu
            ]));
    }

    public function save() {
        // Cek hak admin
        if ((int)$this->rbac_lib->role_id() !== 1) {
            return $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Hanya Administrator yang berwenang mengelola menu sistem.'
                ]));
        }

        $this->form_validation->set_rules('menu_name', 'Nama Menu', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('menu_url', 'URL Rute Menu', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('menu_icon', 'Ikon Menu', 'required|trim|max_length[50]');

        if ($this->form_validation->run() === FALSE) {
            return $this->output
                ->set_status_header(422)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => validation_errors(' ', ' ')
                ]));
        }

        $id = $this->input->post('id');
        $parentId = $this->input->post('parent_id');
        if (empty($parentId) || $parentId === '0' || $parentId === 'root') {
            $parentId = null;
        } else {
            $parentId = (int)$parentId;
        }

        $data = [
            'parent_id' => $parentId,
            'menu_name' => trim($this->input->post('menu_name', TRUE)),
            'menu_url' => trim($this->input->post('menu_url', TRUE)),
            'menu_icon' => trim($this->input->post('menu_icon', TRUE)) ?: 'circle',
            'sort_order' => (int)$this->input->post('sort_order'),
            'is_active' => (int)($this->input->post('is_active') ? 1 : 0)
        ];

        $savedId = $this->rbac_model->save_menu($data, !empty($id) ? (int)$id : null);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'message' => 'Menu "' . $data['menu_name'] . '" berhasil disimpan!',
                'data_id' => $savedId
            ]));
    }

    public function delete($id) {
        if ((int)$this->rbac_lib->role_id() !== 1) {
            return $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Hanya Administrator yang berwenang menghapus menu.'
                ]));
        }

        $menu = $this->rbac_model->get_menu_by_id((int)$id);
        if (!$menu) {
            return $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Menu tidak ditemukan.'
                ]));
        }

        $this->rbac_model->delete_menu((int)$id);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'message' => 'Menu "' . $menu['menu_name'] . '" dan seluruh submenunya berhasil dihapus.'
            ]));
    }

    public function toggle_status($id) {
        if ((int)$this->rbac_lib->role_id() !== 1) {
            return $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Akses ditolak.'
                ]));
        }

        $newStatus = $this->rbac_model->toggle_menu_status((int)$id);
        if ($newStatus === false) {
            return $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Menu tidak ditemukan.'
                ]));
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'message' => 'Status menu berhasil diubah.',
                'new_status' => $newStatus
            ]));
    }
}
