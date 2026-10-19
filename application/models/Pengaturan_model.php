<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pengaturan_model extends CI_Model {

    protected $table = 'pengaturan';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Ambil semua pengaturan dalam bentuk array key => value
     */
    public function get_all_key_value() {
        $rows = $this->db->get($this->table)->result_array();
        $settings = [];
        foreach ($rows as $r) {
            $settings[$r['setting_key']] = $r['setting_value'];
        }
        return $settings;
    }

    /**
     * Ambil pengaturan berdasarkan group (aplikasi, ai, bridging)
     * Mengembalikan associative array key => value
     */
    public function get_by_group($group) {
        $rows = $this->db->where('setting_group', $group)
                         ->order_by('sort_order', 'ASC')
                         ->get($this->table)
                         ->result_array();
        $settings = [];
        foreach ($rows as $r) {
            $settings[$r['setting_key']] = $r['setting_value'];
        }
        return $settings;
    }

    /**
     * Ambil detail metadata pengaturan berdasarkan group
     */
    public function get_raw_by_group($group) {
        return $this->db->where('setting_group', $group)
                        ->order_by('sort_order', 'ASC')
                        ->get($this->table)
                        ->result_array();
    }

    /**
     * Ambil 1 nilai setting
     */
    public function get($key, $default = null) {
        $row = $this->db->where('setting_key', $key)->get($this->table)->row_array();
        return ($row !== null) ? $row['setting_value'] : $default;
    }

    /**
     * Simpan / perbarui 1 setting
     */
    public function set($key, $value, $group = 'aplikasi', $label = null, $type = 'text') {
        $exists = $this->db->where('setting_key', $key)->count_all_results($this->table);
        $now = date('Y-m-d H:i:s');
        if ($exists > 0) {
            return $this->db->where('setting_key', $key)->update($this->table, [
                'setting_value' => $value,
                'updated_at'    => $now
            ]);
        } else {
            return $this->db->insert($this->table, [
                'setting_group' => $group,
                'setting_key'   => $key,
                'setting_value' => $value,
                'setting_type'  => $type,
                'label'         => $label ?: ucwords(str_replace('_', ' ', $key)),
                'created_at'    => $now,
                'updated_at'    => $now
            ]);
        }
    }

    /**
     * Simpan kumpulan setting dari form POST
     */
    public function save_batch_settings($data, $group = 'aplikasi') {
        $now = date('Y-m-d H:i:s');
        $this->db->trans_start();

        foreach ($data as $key => $val) {
            // Cek apakah key terdaftar dalam tabel pengaturan
            $exists = $this->db->where('setting_key', $key)->count_all_results($this->table);
            if ($exists > 0) {
                $this->db->where('setting_key', $key)->update($this->table, [
                    'setting_value' => is_array($val) ? json_encode($val) : (string)$val,
                    'updated_at'    => $now
                ]);
            } else {
                $this->db->insert($this->table, [
                    'setting_group' => $group,
                    'setting_key'   => $key,
                    'setting_value' => is_array($val) ? json_encode($val) : (string)$val,
                    'setting_type'  => 'text',
                    'label'         => ucwords(str_replace('_', ' ', $key)),
                    'created_at'    => $now,
                    'updated_at'    => $now
                ]);
            }
        }

        $this->db->trans_complete();
        return $this->db->trans_status();
    }
}
