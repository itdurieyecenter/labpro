<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rbac_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function authenticate($username, $password) {
        $this->db->select('users.*, roles.role_name, roles.display_name as role_display_name');
        $this->db->from('users');
        $this->db->join('roles', 'roles.id = users.role_id');
        $this->db->where('users.username', $username);
        $this->db->where('users.is_active', 1);
        $query = $this->db->get();

        if ($query->num_rows() === 1) {
            $user = $query->row_array();
            if (password_verify($password, $user['password'])) {
                unset($user['password']);
                return $user;
            }
        }
        return false;
    }

    public function update_last_login($user_id) {
        $this->db->where('id', $user_id);
        $this->db->update('users', ['last_login' => date('Y-m-d H:i:s')]);
    }

    public function get_user_by_id($user_id) {
        $this->db->select('users.*, roles.role_name, roles.display_name as role_display_name');
        $this->db->from('users');
        $this->db->join('roles', 'roles.id = users.role_id');
        $this->db->where('users.id', $user_id);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function get_menus_by_role($role_id) {
        $this->db->select('m.*, rp.can_view, rp.can_create, rp.can_edit, rp.can_delete');
        $this->db->from('menus m');
        $this->db->join('role_permissions rp', 'rp.menu_id = m.id AND rp.role_id = ' . (int)$role_id, 'left');
        $this->db->where('m.is_active', 1);
        $this->db->where('rp.can_view', 1);
        $this->db->order_by('m.parent_id', 'ASC');
        $this->db->order_by('m.sort_order', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_all_roles() {
        return $this->db->get('roles')->result_array();
    }

    public function check_permission($role_id, $menu_url, $action = 'view') {
        $field = 'can_view';
        if ($action === 'create') {
            $field = 'can_create';
        } elseif ($action === 'edit') {
            $field = 'can_edit';
        } elseif ($action === 'delete') {
            $field = 'can_delete';
        }

        $this->db->select('rp.' . $field);
        $this->db->from('role_permissions rp');
        $this->db->join('menus m', 'm.id = rp.menu_id');
        $this->db->where('rp.role_id', (int)$role_id);
        $this->db->where('m.menu_url', $menu_url);
        $this->db->where('m.is_active', 1);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            return (int)$row[$field] === 1;
        }

        return false;
    }

    public function get_all_menus() {
        $this->db->select('m.*, p.menu_name as parent_name');
        $this->db->from('menus m');
        $this->db->join('menus p', 'p.id = m.parent_id', 'left');
        $this->db->order_by('COALESCE(m.parent_id, m.id) ASC, (m.parent_id IS NOT NULL) ASC, m.sort_order ASC', '', FALSE);
        return $this->db->get()->result_array();
    }

    public function get_root_menus() {
        return $this->db->where('parent_id IS NULL', null, false)
                        ->order_by('sort_order', 'ASC')
                        ->get('menus')
                        ->result_array();
    }

    public function get_menu_by_id($id) {
        return $this->db->where('id', (int)$id)->get('menus')->row_array();
    }

    public function save_menu($data, $id = null) {
        if (!empty($id)) {
            $this->db->where('id', (int)$id);
            $this->db->update('menus', $data);
            return (int)$id;
        } else {
            $this->db->insert('menus', $data);
            $newId = $this->db->insert_id();

            // Berikan hak akses penuh untuk Administrator (Role ID 1) secara otomatis
            $this->db->query("INSERT INTO role_permissions (role_id, menu_id, can_view, can_create, can_edit, can_delete)
                              VALUES (1, $newId, 1, 1, 1, 1)
                              ON DUPLICATE KEY UPDATE can_view=1, can_create=1, can_edit=1, can_delete=1");
            return $newId;
        }
    }

    public function delete_menu($id) {
        // Hapus child menu terlebih dahulu jika ada
        $this->db->where('parent_id', (int)$id)->delete('menus');
        // Hapus role permissions
        $this->db->where('menu_id', (int)$id)->delete('role_permissions');
        // Hapus menu utama
        return $this->db->where('id', (int)$id)->delete('menus');
    }

    public function toggle_menu_status($id) {
        $menu = $this->get_menu_by_id($id);
        if (!$menu) {
            return false;
        }
        $newStatus = (int)$menu['is_active'] === 1 ? 0 : 1;
        $this->db->where('id', (int)$id)->update('menus', ['is_active' => $newStatus]);
        return $newStatus;
    }
}
