<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('rbac_model');
    }

    public function index() {
        if ($this->rbac_lib->is_logged_in()) {
            redirect('dashboard');
        } else {
            redirect('auth/login');
        }
    }

    public function login() {
        if ($this->rbac_lib->is_logged_in()) {
            redirect('dashboard');
        }

        $this->form_validation->set_rules('username', 'Username', 'trim|required');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');

        if ($this->form_validation->run() === TRUE) {
            $username = $this->input->post('username', TRUE);
            $password = $this->input->post('password');

            $user = $this->rbac_model->authenticate($username, $password);

            if ($user) {
                $this->session->set_userdata([
                    'logged_in' => TRUE,
                    'user' => $user
                ]);
                $this->rbac_model->update_last_login($user['id']);
                $this->session->set_flashdata('success', 'Selamat datang kembali, ' . $user['full_name'] . '!');
                redirect('dashboard');
            } else {
                $this->session->set_flashdata('error', 'Username atau password salah, atau akun dinonaktifkan.');
                redirect('auth/login');
            }
        }

        $data['title'] = 'Login - LIS PRO Laboratory Information System';
        $this->load->view('auth/login', $data);
    }

    public function logout() {
        $this->session->unset_userdata('logged_in');
        $this->session->unset_userdata('user');
        $this->session->sess_destroy();
        redirect('auth/login');
    }

    public function quick_login($username) {
        $user = $this->rbac_model->authenticate($username, 'password123');
        if ($user) {
            $this->session->set_userdata([
                'logged_in' => TRUE,
                'user' => $user
            ]);
            $this->rbac_model->update_last_login($user['id']);
            $this->session->set_flashdata('success', 'Berhasil beralih role ke: ' . $user['role_display_name']);
        }
        redirect('dashboard');
    }
}
