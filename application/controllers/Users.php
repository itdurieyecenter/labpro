<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->rbac_lib->require_login();
        $this->rbac_lib->require_access('users', 'view');
        $this->load->model('rbac_model');
    }

    public function index() {
        $data['title'] = 'Manajemen Pengguna & Staf Laboratorium';
        $data['active_menu'] = 'users';
        $data['user'] = $this->rbac_lib->user();
        $data['menu_tree'] = $this->rbac_lib->get_menu_tree();

        $this->db->select('u.*, r.role_name, r.display_name as role_display_name');
        $this->db->from('users u');
        $this->db->join('roles r', 'r.id = u.role_id');
        $this->db->order_by('u.id', 'ASC');
        $data['users_list'] = $this->db->get()->result_array();

        $data['roles'] = $this->rbac_model->get_all_roles();

        $this->load->view('layout/header', $data);
        $this->load->view('layout/sidebar', $data);
        $this->load->view('layout/topbar', $data);
        $this->load->view('users/index', $data);
        $this->load->view('layout/footer', $data);
    }

    public function save_user() {
        $id = (int)$this->input->post('id', TRUE);
        $action = $id > 0 ? 'edit' : 'create';
        $this->rbac_lib->require_access('users', $action);

        $roleId = (int)$this->input->post('role_id', TRUE);
        $username = strtolower(trim($this->input->post('username', TRUE)));
        $fullName = trim($this->input->post('full_name', TRUE));
        $email = trim($this->input->post('email', TRUE));
        $phone = trim($this->input->post('phone', TRUE));
        $password = $this->input->post('password');
        $isActive = $this->input->post('is_active') !== null ? (int)$this->input->post('is_active') : 1;

        if (empty($username) || empty($fullName) || $roleId <= 0) {
            return $this->output->set_status_header(400)->set_output(json_encode([
                'status' => 'error',
                'message' => 'Username, Nama Lengkap, dan Peran (Role) wajib diisi.'
            ]));
        }

        // Cek duplikasi username
        $this->db->where('username', $username);
        if ($id > 0) {
            $this->db->where('id !=', $id);
        }
        $existing = $this->db->get('users')->row_array();
        if ($existing) {
            return $this->output->set_status_header(400)->set_output(json_encode([
                'status' => 'error',
                'message' => 'Username "' . $username . '" sudah digunakan oleh pengguna lain.'
            ]));
        }

        $payload = [
            'role_id' => $roleId,
            'username' => $username,
            'full_name' => $fullName,
            'email' => !empty($email) ? $email : null,
            'phone' => !empty($phone) ? $phone : null,
            'is_active' => $isActive,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($id > 0) {
            if (!empty($password)) {
                $payload['password'] = password_hash($password, PASSWORD_BCRYPT);
            }
            $this->db->where('id', $id)->update('users', $payload);
            $msg = 'Data pengguna ' . $fullName . ' berhasil diperbarui.';
        } else {
            if (empty($password)) {
                $password = 'password123'; // Default password jika kosong
            }
            $payload['password'] = password_hash($password, PASSWORD_BCRYPT);
            $payload['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('users', $payload);
            $msg = 'Pengguna baru ' . $fullName . ' berhasil ditambahkan.';
        }

        return $this->output->set_content_type('application/json')->set_output(json_encode([
            'status' => 'success',
            'message' => $msg
        ]));
    }

    public function toggle_status($id) {
        $this->rbac_lib->require_access('users', 'edit');
        $id = (int)$id;

        $currentUser = $this->rbac_lib->user();
        if ($currentUser['id'] == $id) {
            return $this->output->set_status_header(400)->set_output(json_encode([
                'status' => 'error',
                'message' => 'Anda tidak dapat menonaktifkan akun Anda sendiri saat sedang aktif.'
            ]));
        }

        $user = $this->db->where('id', $id)->get('users')->row_array();
        if (!$user) {
            return $this->output->set_status_header(404)->set_output(json_encode([
                'status' => 'error',
                'message' => 'Pengguna tidak ditemukan.'
            ]));
        }

        $newStatus = $user['is_active'] == 1 ? 0 : 1;
        $this->db->where('id', $id)->update('users', [
            'is_active' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return $this->output->set_content_type('application/json')->set_output(json_encode([
            'status' => 'success',
            'message' => 'Status pengguna ' . $user['username'] . ' berhasil diubah menjadi ' . ($newStatus == 1 ? 'Aktif' : 'Nonaktif') . '.',
            'new_status' => $newStatus
        ]));
    }

    public function reset_password() {
        $this->rbac_lib->require_access('users', 'edit');

        $id = (int)$this->input->post('id', TRUE);
        $newPass = $this->input->post('new_password');

        if (empty($newPass)) {
            $newPass = 'password123';
        }

        $this->db->where('id', $id)->update('users', [
            'password' => password_hash($newPass, PASSWORD_BCRYPT),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return $this->output->set_content_type('application/json')->set_output(json_encode([
            'status' => 'success',
            'message' => 'Kata sandi berhasil direset.'
        ]));
    }

    public function delete_user($id) {
        $this->rbac_lib->require_access('users', 'delete');
        $id = (int)$id;

        $currentUser = $this->rbac_lib->user();
        if ($currentUser['id'] == $id || $id === 1) {
            return $this->output->set_status_header(400)->set_output(json_encode([
                'status' => 'error',
                'message' => 'Akun Administrator utama atau akun yang sedang digunakan tidak dapat dihapus.'
            ]));
        }

        $this->db->where('id', $id)->delete('users');

        return $this->output->set_content_type('application/json')->set_output(json_encode([
            'status' => 'success',
            'message' => 'Pengguna berhasil dihapus dari sistem.'
        ]));
    }
}
