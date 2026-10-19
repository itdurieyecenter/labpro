<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Errors_handler extends CI_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function not_found() {
        $this->output->set_status_header(404);

        $data['title'] = '404 - Modul / Halaman Tidak Ditemukan - LIS PRO';
        $data['active_menu'] = '';
        $data['requested_uri'] = $this->uri->uri_string();

        if ($this->rbac_lib->is_logged_in()) {
            $data['user'] = $this->rbac_lib->user();
            $data['menu_tree'] = $this->rbac_lib->get_menu_tree();

            $this->load->view('layout/header', $data);
            $this->load->view('layout/sidebar', $data);
            $this->load->view('layout/topbar', $data);
            $this->load->view('errors/error_404_simrs', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $this->load->view('errors/error_404_guest', $data);
        }
    }
}
