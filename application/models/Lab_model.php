<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Lab_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    private function _log($action, $message) {
        log_message('info', sprintf("[LAB-MODEL] ACTION=%s | MSG=%s", $action, $message));
    }

    // ==========================================
    // 1. KATEGORI LAB
    // ==========================================
    public function get_categories() {
        return $this->db->where('is_active', 1)->order_by('sort_order', 'ASC')->get('lab_categories')->result_array();
    }

    // ==========================================
    // 2. METODE PEMERIKSAAN
    // ==========================================
    public function get_all_methods() {
        return $this->db->where('is_active', 1)->order_by('method_name', 'ASC')->get('lab_methods')->result_array();
    }

    public function get_methods_datatable($params = []) {
        $draw = isset($params['draw']) ? (int)$params['draw'] : 1;
        $start = isset($params['start']) ? (int)$params['start'] : 0;
        $length = isset($params['length']) ? (int)$params['length'] : 10;
        $search = isset($params['search']['value']) ? $params['search']['value'] : '';

        $recordsTotal = $this->db->count_all('lab_methods');

        $this->db->from('lab_methods');
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('method_code', $search);
            $this->db->or_like('method_name', $search);
            $this->db->or_like('standard_code', $search);
            $this->db->or_like('instrument_name', $search);
            $this->db->group_end();
        }
        $recordsFiltered = $this->db->count_all_results();

        $this->db->from('lab_methods');
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('method_code', $search);
            $this->db->or_like('method_name', $search);
            $this->db->or_like('standard_code', $search);
            $this->db->or_like('instrument_name', $search);
            $this->db->group_end();
        }

        $orderColumns = [0 => 'id', 1 => 'method_code', 2 => 'method_name', 3 => 'standard_code', 4 => 'instrument_name', 5 => 'is_active'];
        $orderColIdx = isset($params['order'][0]['column']) ? (int)$params['order'][0]['column'] : 1;
        $orderDir = isset($params['order'][0]['dir']) ? $params['order'][0]['dir'] : 'ASC';
        $orderCol = isset($orderColumns[$orderColIdx]) ? $orderColumns[$orderColIdx] : 'method_name';

        $this->db->order_by($orderCol, $orderDir);
        $this->db->limit($length, $start);
        $data = $this->db->get()->result_array();

        $no = $start + 1;
        foreach ($data as &$row) {
            $row['no_urut'] = $no++;
        }
        unset($row);

        return [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ];
    }

    public function get_method_by_id($id) {
        return $this->db->get_where('lab_methods', ['id' => (int)$id])->row_array();
    }

    public function insert_method($data) {
        $this->db->insert('lab_methods', $data);
        $id = $this->db->insert_id();
        $this->_log('INSERT_METHOD', "ID={$id} | Nama={$data['method_name']}");
        return $id;
    }

    public function update_method($id, $data) {
        $this->db->where('id', (int)$id)->update('lab_methods', $data);
        $this->_log('UPDATE_METHOD', "ID={$id}");
        return true;
    }

    public function delete_method($id) {
        $this->db->where('id', (int)$id)->delete('lab_methods');
        $this->_log('DELETE_METHOD', "ID={$id}");
        return true;
    }

    // ==========================================
    // 3. PARAMETER & TARIF SATUAN
    // ==========================================
    public function get_parameters_datatable($params = []) {
        $draw = isset($params['draw']) ? (int)$params['draw'] : 1;
        $start = isset($params['start']) ? (int)$params['start'] : 0;
        $length = isset($params['length']) ? (int)$params['length'] : 10;
        $search = isset($params['search']['value']) ? $params['search']['value'] : '';
        $category_id = isset($params['category_id']) && $params['category_id'] !== '' ? (int)$params['category_id'] : null;
        $tariff_group = isset($params['tariff_group']) && !empty($params['tariff_group']) ? $params['tariff_group'] : 'umum';

        $recordsTotal = $this->db->count_all('lab_parameters');

        // Filtered Count
        $this->db->from('lab_parameters p');
        $this->db->join('lab_categories c', 'c.id = p.category_id', 'left');
        $this->db->join('lab_methods m', 'm.id = p.method_id', 'left');
        if ($category_id !== null) {
            $this->db->where('p.category_id', $category_id);
        }
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('p.parameter_code', $search);
            $this->db->or_like('p.parameter_name', $search);
            $this->db->or_like('c.category_name', $search);
            $this->db->or_like('m.method_name', $search);
            $this->db->group_end();
        }
        $recordsFiltered = $this->db->count_all_results();

        // Get Data
        $this->db->select("
            p.*,
            c.category_name,
            c.category_code,
            m.method_name,
            m.standard_code,
            COALESCE(t.jasa_manajemen, 0) as jasa_manajemen,
            COALESCE(t.jasa_dokter, 0) as jasa_dokter,
            COALESCE(t.jasa_petugas, 0) as jasa_petugas,
            COALESCE(t.kso, 0) as kso,
            COALESCE(t.bhp, 0) as bhp,
            COALESCE(t.total_tarif, 0) as total_tarif,
            (SELECT COUNT(*) FROM lab_reference_ranges r WHERE r.parameter_id = p.id) as ref_range_count,
            (SELECT GROUP_CONCAT(CONCAT(age_label, ': ', reference_display) SEPARATOR '<br>') FROM lab_reference_ranges r WHERE r.parameter_id = p.id LIMIT 3) as ref_display_summary
        ");
        $this->db->from('lab_parameters p');
        $this->db->join('lab_categories c', 'c.id = p.category_id', 'left');
        $this->db->join('lab_methods m', 'm.id = p.method_id', 'left');
        $this->db->join("lab_tariffs t", "t.item_id = p.id AND t.item_type = 'parameter' AND t.tariff_group = '{$tariff_group}'", 'left');

        if ($category_id !== null) {
            $this->db->where('p.category_id', $category_id);
        }
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('p.parameter_code', $search);
            $this->db->or_like('p.parameter_name', $search);
            $this->db->or_like('c.category_name', $search);
            $this->db->or_like('m.method_name', $search);
            $this->db->group_end();
        }

        $orderColumns = [
            0 => 'p.id',
            1 => 'p.parameter_code',
            2 => 'p.parameter_name',
            3 => 'c.category_name',
            4 => 'p.unit',
            5 => 'm.method_name',
            6 => 't.jasa_manajemen',
            7 => 't.jasa_dokter',
            8 => 't.jasa_petugas',
            9 => 't.kso',
            10 => 't.bhp',
            11 => 't.total_tarif',
            12 => 'p.id'
        ];
        $orderColIdx = isset($params['order'][0]['column']) ? (int)$params['order'][0]['column'] : 1;
        $orderDir = isset($params['order'][0]['dir']) ? $params['order'][0]['dir'] : 'ASC';
        $orderCol = isset($orderColumns[$orderColIdx]) ? $orderColumns[$orderColIdx] : 'p.sort_order';

        $this->db->order_by($orderCol, $orderDir);
        $this->db->limit($length, $start);
        $data = $this->db->get()->result_array();

        $no = $start + 1;
        foreach ($data as &$row) {
            $row['no_urut'] = $no++;
        }
        unset($row);

        return [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ];
    }

    public function get_parameter_by_id($id, $tariff_group = 'umum') {
        $this->db->select("
            p.*,
            c.category_name,
            m.method_name,
            COALESCE(t.jasa_manajemen, 0) as jasa_manajemen,
            COALESCE(t.jasa_dokter, 0) as jasa_dokter,
            COALESCE(t.jasa_petugas, 0) as jasa_petugas,
            COALESCE(t.kso, 0) as kso,
            COALESCE(t.bhp, 0) as bhp,
            COALESCE(t.total_tarif, 0) as total_tarif
        ");
        $this->db->from('lab_parameters p');
        $this->db->join('lab_categories c', 'c.id = p.category_id', 'left');
        $this->db->join('lab_methods m', 'm.id = p.method_id', 'left');
        $this->db->join("lab_tariffs t", "t.item_id = p.id AND t.item_type = 'parameter' AND t.tariff_group = '{$tariff_group}'", 'left');
        $this->db->where('p.id', (int)$id);
        $param = $this->db->get()->row_array();

        if ($param) {
            $param['reference_ranges'] = $this->get_reference_ranges_by_param($id);
        }

        return $param;
    }

    public function save_parameter($param_data, $tariff_data, $id = null) {
        $this->db->trans_start();

        if ($id) {
            $this->db->where('id', (int)$id)->update('lab_parameters', $param_data);
            $param_id = (int)$id;
            $this->_log('UPDATE_PARAMETER', "ID={$param_id} | Nama={$param_data['parameter_name']}");
        } else {
            $this->db->insert('lab_parameters', $param_data);
            $param_id = $this->db->insert_id();
            $this->_log('INSERT_PARAMETER', "ID={$param_id} | Nama={$param_data['parameter_name']}");
        }

        // Simpan / update tarif
        $tariff_group = isset($tariff_data['tariff_group']) ? $tariff_data['tariff_group'] : 'umum';
        $jasa_manajemen = (float)($tariff_data['jasa_manajemen'] ?? 0);
        $jasa_dokter = (float)($tariff_data['jasa_dokter'] ?? 0);
        $jasa_petugas = (float)($tariff_data['jasa_petugas'] ?? 0);
        $kso = (float)($tariff_data['kso'] ?? 0);
        $bhp = (float)($tariff_data['bhp'] ?? 0);
        $total_tarif = $jasa_manajemen + $jasa_dokter + $jasa_petugas + $kso + $bhp;

        $tData = [
            'item_type' => 'parameter',
            'item_id' => $param_id,
            'tariff_group' => $tariff_group,
            'jasa_manajemen' => $jasa_manajemen,
            'jasa_dokter' => $jasa_dokter,
            'jasa_petugas' => $jasa_petugas,
            'kso' => $kso,
            'bhp' => $bhp,
            'total_tarif' => $total_tarif,
            'notes' => $tariff_data['notes'] ?? null
        ];

        $existing = $this->db->get_where('lab_tariffs', [
            'item_type' => 'parameter',
            'item_id' => $param_id,
            'tariff_group' => $tariff_group
        ])->row_array();

        if ($existing) {
            $this->db->where('id', $existing['id'])->update('lab_tariffs', $tData);
        } else {
            $this->db->insert('lab_tariffs', $tData);
        }

        $this->db->trans_complete();
        return $this->db->trans_status() ? $param_id : false;
    }

    public function delete_parameter($id) {
        $this->db->trans_start();
        $this->db->where('item_type', 'parameter')->where('item_id', (int)$id)->delete('lab_tariffs');
        $this->db->where('parameter_id', (int)$id)->delete('lab_reference_ranges');
        $this->db->where('id', (int)$id)->delete('lab_parameters');
        $this->db->trans_complete();
        $this->_log('DELETE_PARAMETER', "ID={$id}");
        return $this->db->trans_status();
    }

    // ==========================================
    // 4. NILAI RUJUKAN & KLASIFIKASI UMUR
    // ==========================================
    public function get_reference_ranges_by_param($parameter_id) {
        return $this->db->where('parameter_id', (int)$parameter_id)
            ->order_by('gender', 'ASC')
            ->order_by('age_min_days', 'ASC')
            ->get('lab_reference_ranges')
            ->result_array();
    }

    public function save_reference_range($data, $id = null) {
        if ($id) {
            $this->db->where('id', (int)$id)->update('lab_reference_ranges', $data);
            $this->_log('UPDATE_REFERENCE_RANGE', "ID={$id}");
            return $id;
        } else {
            $this->db->insert('lab_reference_ranges', $data);
            $new_id = $this->db->insert_id();
            $this->_log('INSERT_REFERENCE_RANGE', "ID={$new_id}");
            return $new_id;
        }
    }

    public function delete_reference_range($id) {
        $this->db->where('id', (int)$id)->delete('lab_reference_ranges');
        $this->_log('DELETE_REFERENCE_RANGE', "ID={$id}");
        return true;
    }

    // ==========================================
    // 5. TEMPLATE / PAKET LABORATORIUM
    // ==========================================
    public function get_all_templates() {
        return $this->db->where('is_active', 1)->order_by('sort_order', 'ASC')->get('lab_templates')->result_array();
    }

    public function get_templates_datatable($params = []) {
        $draw = isset($params['draw']) ? (int)$params['draw'] : 1;
        $start = isset($params['start']) ? (int)$params['start'] : 0;
        $length = isset($params['length']) ? (int)$params['length'] : 10;
        $search = isset($params['search']['value']) ? $params['search']['value'] : '';
        $category_id = isset($params['category_id']) && $params['category_id'] !== '' ? (int)$params['category_id'] : null;
        $tariff_group = isset($params['tariff_group']) && !empty($params['tariff_group']) ? $params['tariff_group'] : 'umum';

        $recordsTotal = $this->db->count_all('lab_templates');

        $this->db->from('lab_templates tpl');
        $this->db->join('lab_categories c', 'c.id = tpl.category_id', 'left');
        if ($category_id !== null) {
            $this->db->where('tpl.category_id', $category_id);
        }
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('tpl.template_code', $search);
            $this->db->or_like('tpl.template_name', $search);
            $this->db->or_like('c.category_name', $search);
            $this->db->group_end();
        }
        $recordsFiltered = $this->db->count_all_results();

        $this->db->select("
            tpl.*,
            c.category_name,
            c.category_code,
            COALESCE(t.jasa_manajemen, 0) as jasa_manajemen,
            COALESCE(t.jasa_dokter, 0) as jasa_dokter,
            COALESCE(t.jasa_petugas, 0) as jasa_petugas,
            COALESCE(t.kso, 0) as kso,
            COALESCE(t.bhp, 0) as bhp,
            COALESCE(t.total_tarif, 0) as total_tarif,
            (SELECT COUNT(*) FROM lab_template_items ti WHERE ti.template_id = tpl.id) as item_count,
            (SELECT GROUP_CONCAT(p.parameter_name ORDER BY ti.sort_order SEPARATOR ', ') 
             FROM lab_template_items ti 
             JOIN lab_parameters p ON p.id = ti.parameter_id 
             WHERE ti.template_id = tpl.id) as item_names
        ");
        $this->db->from('lab_templates tpl');
        $this->db->join('lab_categories c', 'c.id = tpl.category_id', 'left');
        $this->db->join("lab_tariffs t", "t.item_id = tpl.id AND t.item_type = 'template' AND t.tariff_group = '{$tariff_group}'", 'left');

        if ($category_id !== null) {
            $this->db->where('tpl.category_id', $category_id);
        }
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('tpl.template_code', $search);
            $this->db->or_like('tpl.template_name', $search);
            $this->db->or_like('c.category_name', $search);
            $this->db->group_end();
        }

        $orderColumns = [
            0 => 'tpl.id',
            1 => 'tpl.template_code',
            2 => 'tpl.template_name',
            3 => 'c.category_name',
            4 => 'item_count',
            5 => 't.jasa_manajemen',
            6 => 't.jasa_dokter',
            7 => 't.jasa_petugas',
            8 => 't.kso',
            9 => 't.bhp',
            10 => 't.total_tarif',
            11 => 'tpl.is_active'
        ];
        $orderColIdx = isset($params['order'][0]['column']) ? (int)$params['order'][0]['column'] : 1;
        $orderDir = isset($params['order'][0]['dir']) ? $params['order'][0]['dir'] : 'ASC';
        $orderCol = isset($orderColumns[$orderColIdx]) ? $orderColumns[$orderColIdx] : 'tpl.sort_order';

        $this->db->order_by($orderCol, $orderDir);
        $this->db->limit($length, $start);
        $data = $this->db->get()->result_array();

        $no = $start + 1;
        foreach ($data as &$row) {
            $row['no_urut'] = $no++;
        }
        unset($row);

        return [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ];
    }

    public function get_template_by_id($id, $tariff_group = 'umum') {
        $this->db->select("
            tpl.*,
            c.category_name,
            COALESCE(t.jasa_manajemen, 0) as jasa_manajemen,
            COALESCE(t.jasa_dokter, 0) as jasa_dokter,
            COALESCE(t.jasa_petugas, 0) as jasa_petugas,
            COALESCE(t.kso, 0) as kso,
            COALESCE(t.bhp, 0) as bhp,
            COALESCE(t.total_tarif, 0) as total_tarif
        ");
        $this->db->from('lab_templates tpl');
        $this->db->join('lab_categories c', 'c.id = tpl.category_id', 'left');
        $this->db->join("lab_tariffs t", "t.item_id = tpl.id AND t.item_type = 'template' AND t.tariff_group = '{$tariff_group}'", 'left');
        $this->db->where('tpl.id', (int)$id);
        $template = $this->db->get()->row_array();

        if ($template) {
            $this->db->select("ti.*, p.parameter_code, p.parameter_name, p.unit, m.method_name");
            $this->db->from('lab_template_items ti');
            $this->db->join('lab_parameters p', 'p.id = ti.parameter_id');
            $this->db->join('lab_methods m', 'm.id = p.method_id', 'left');
            $this->db->where('ti.template_id', (int)$id);
            $this->db->order_by('ti.sort_order', 'ASC');
            $template['items'] = $this->db->get()->result_array();
        }

        return $template;
    }

    public function save_template($tpl_data, $tariff_data, $item_ids = [], $id = null) {
        $this->db->trans_start();

        if ($id) {
            $this->db->where('id', (int)$id)->update('lab_templates', $tpl_data);
            $template_id = (int)$id;
            $this->_log('UPDATE_TEMPLATE', "ID={$template_id} | Nama={$tpl_data['template_name']}");
        } else {
            $this->db->insert('lab_templates', $tpl_data);
            $template_id = $this->db->insert_id();
            $this->_log('INSERT_TEMPLATE', "ID={$template_id} | Nama={$tpl_data['template_name']}");
        }

        // Update items dalam template
        $this->db->where('template_id', $template_id)->delete('lab_template_items');
        if (!empty($item_ids) && is_array($item_ids)) {
            $order = 1;
            foreach ($item_ids as $pid) {
                $pid = (int)$pid;
                if ($pid > 0) {
                    $this->db->insert('lab_template_items', [
                        'template_id' => $template_id,
                        'parameter_id' => $pid,
                        'sort_order' => $order++
                    ]);
                }
            }
        }

        // Simpan / update tarif paket
        $tariff_group = isset($tariff_data['tariff_group']) ? $tariff_data['tariff_group'] : 'umum';
        $jasa_manajemen = (float)($tariff_data['jasa_manajemen'] ?? 0);
        $jasa_dokter = (float)($tariff_data['jasa_dokter'] ?? 0);
        $jasa_petugas = (float)($tariff_data['jasa_petugas'] ?? 0);
        $kso = (float)($tariff_data['kso'] ?? 0);
        $bhp = (float)($tariff_data['bhp'] ?? 0);
        $total_tarif = $jasa_manajemen + $jasa_dokter + $jasa_petugas + $kso + $bhp;

        $tData = [
            'item_type' => 'template',
            'item_id' => $template_id,
            'tariff_group' => $tariff_group,
            'jasa_manajemen' => $jasa_manajemen,
            'jasa_dokter' => $jasa_dokter,
            'jasa_petugas' => $jasa_petugas,
            'kso' => $kso,
            'bhp' => $bhp,
            'total_tarif' => $total_tarif,
            'notes' => $tariff_data['notes'] ?? null
        ];

        $existing = $this->db->get_where('lab_tariffs', [
            'item_type' => 'template',
            'item_id' => $template_id,
            'tariff_group' => $tariff_group
        ])->row_array();

        if ($existing) {
            $this->db->where('id', $existing['id'])->update('lab_tariffs', $tData);
        } else {
            $this->db->insert('lab_tariffs', $tData);
        }

        $this->db->trans_complete();
        return $this->db->trans_status() ? $template_id : false;
    }

    public function delete_template($id) {
        $this->db->trans_start();
        $this->db->where('item_type', 'template')->where('item_id', (int)$id)->delete('lab_tariffs');
        $this->db->where('template_id', (int)$id)->delete('lab_template_items');
        $this->db->where('id', (int)$id)->delete('lab_templates');
        $this->db->trans_complete();
        $this->_log('DELETE_TEMPLATE', "ID={$id}");
        return $this->db->trans_status();
    }

    // ==========================================
    // 6. SUMMARY STATISTIK MASTER LAB
    // ==========================================
    public function get_summary_counts() {
        return [
            'total_parameter' => $this->db->count_all('lab_parameters'),
            'total_template' => $this->db->count_all('lab_templates'),
            'total_metode' => $this->db->count_all('lab_methods'),
            'total_kategori' => $this->db->count_all('lab_categories')
        ];
    }

    // ==========================================
    // 7. GENERATOR KODE OTOMATIS (SMART SYSTEM CODE)
    // ==========================================

    /**
     * Menghasilkan kode parameter baru secara otomatis berdasarkan kategori
     * Contoh: HEM-023, URI-001, KIM-001, IMM-001, KUL-001, PAR-001
     */
    public function generate_parameter_code($category_id = null) {
        $prefix = 'PRM';
        if (!empty($category_id)) {
            $cat = $this->db->get_where('lab_categories', ['id' => (int)$category_id])->row_array();
            if ($cat) {
                $cCode = strtoupper($cat['category_code']);
                if (strpos($cCode, 'HEMATO') !== false) {
                    $prefix = 'HEM';
                } elseif (strpos($cCode, 'URIN') !== false) {
                    $prefix = 'URI';
                } elseif (strpos($cCode, 'KLINIK') !== false || strpos($cCode, 'KIM') !== false) {
                    $prefix = 'KIM';
                } elseif (strpos($cCode, 'PARASIT') !== false) {
                    $prefix = 'PAR';
                } elseif (strpos($cCode, 'IMUN') !== false || strpos($cCode, 'IMMUN') !== false) {
                    $prefix = 'IMM';
                } elseif (strpos($cCode, 'KULTUR') !== false || strpos($cCode, 'MIKRO') !== false) {
                    $prefix = 'KUL';
                } else {
                    $clean = preg_replace('/[^A-Z]/', '', $cCode);
                    $prefix = !empty($clean) ? substr($clean, 0, 3) : 'PRM';
                }
            }
        }

        // Cari nomor urut tertinggi untuk prefix ini
        $query = $this->db->query("
            SELECT parameter_code 
            FROM lab_parameters 
            WHERE parameter_code LIKE " . $this->db->escape($prefix . '-%') . "
            ORDER BY id DESC
        ");
        $existingCodes = $query->result_array();

        $maxNum = 0;
        foreach ($existingCodes as $row) {
            $parts = explode('-', $row['parameter_code']);
            if (isset($parts[1]) && is_numeric($parts[1])) {
                $num = (int)$parts[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        // Jika belum ada yang pakai format PREFIX-XXX, hitung total parameter pada kategori ini sebagai base
        if ($maxNum === 0 && !empty($category_id)) {
            $catCount = $this->db->where('category_id', (int)$category_id)->count_all_results('lab_parameters');
            $maxNum = $catCount;
        }

        $nextNum = $maxNum + 1;
        $code = sprintf('%s-%03d', $prefix, $nextNum);

        // Pastikan tidak ada tabrakan kode yang sudah terdaftar
        while ($this->db->where('parameter_code', $code)->count_all_results('lab_parameters') > 0) {
            $nextNum++;
            $code = sprintf('%s-%03d', $prefix, $nextNum);
        }

        $this->_log('GENERATE_PARAM_CODE', "Cat={$category_id} | Prefix={$prefix} | Code={$code}");
        return $code;
    }

    /**
     * Menghasilkan kode template/paket baru secara otomatis
     * Format SIMRS: PKT-[Kategori]-[Nomor]
     * Contoh: PKT-HEM-01, PKT-URI-02, PKT-MCU-01
     */
    public function generate_template_code($category_id = null) {
        $prefix = 'PKT-MCU';
        if (!empty($category_id)) {
            $cat = $this->db->get_where('lab_categories', ['id' => (int)$category_id])->row_array();
            if ($cat) {
                $cCode = strtoupper($cat['category_code']);
                if (strpos($cCode, 'HEMATO') !== false) {
                    $prefix = 'PKT-HEM';
                } elseif (strpos($cCode, 'URIN') !== false) {
                    $prefix = 'PKT-URI';
                } elseif (strpos($cCode, 'KLINIK') !== false || strpos($cCode, 'KIM') !== false) {
                    $prefix = 'PKT-KIM';
                } elseif (strpos($cCode, 'PARASIT') !== false) {
                    $prefix = 'PKT-PAR';
                } elseif (strpos($cCode, 'IMUN') !== false || strpos($cCode, 'IMMUN') !== false) {
                    $prefix = 'PKT-IMM';
                } elseif (strpos($cCode, 'KULTUR') !== false || strpos($cCode, 'MIKRO') !== false) {
                    $prefix = 'PKT-KUL';
                } else {
                    $clean = preg_replace('/[^A-Z]/', '', $cCode);
                    $sub = !empty($clean) ? substr($clean, 0, 3) : 'LAB';
                    $prefix = 'PKT-' . $sub;
                }
            }
        }

        $query = $this->db->query("
            SELECT template_code 
            FROM lab_templates 
            WHERE template_code LIKE " . $this->db->escape($prefix . '-%') . "
            ORDER BY id DESC
        ");
        $existingCodes = $query->result_array();

        $maxNum = 0;
        foreach ($existingCodes as $row) {
            $parts = explode('-', $row['template_code']);
            $lastPart = end($parts);
            if (is_numeric($lastPart)) {
                $num = (int)$lastPart;
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        $nextNum = $maxNum + 1;
        $code = sprintf('%s-%02d', $prefix, $nextNum);

        while ($this->db->where('template_code', $code)->count_all_results('lab_templates') > 0) {
            $nextNum++;
            $code = sprintf('%s-%02d', $prefix, $nextNum);
        }

        $this->_log('GENERATE_TPL_CODE', "Cat={$category_id} | Prefix={$prefix} | Code={$code}");
        return $code;
    }

    /**
     * Menghasilkan kode metode pemeriksaan baru secara otomatis
     * Format: MET-XXX
     * Contoh: MET-010, MET-011
     */
    public function generate_method_code() {
        $prefix = 'MET';
        $query = $this->db->query("
            SELECT method_code 
            FROM lab_methods 
            WHERE method_code LIKE " . $this->db->escape($prefix . '-%') . "
            ORDER BY id DESC
        ");
        $existingCodes = $query->result_array();

        $maxNum = 0;
        foreach ($existingCodes as $row) {
            $parts = explode('-', $row['method_code']);
            $lastPart = end($parts);
            if (is_numeric($lastPart)) {
                $num = (int)$lastPart;
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        if ($maxNum === 0) {
            $totalMethods = $this->db->count_all('lab_methods');
            $maxNum = $totalMethods;
        }

        $nextNum = $maxNum + 1;
        $code = sprintf('%s-%03d', $prefix, $nextNum);

        while ($this->db->where('method_code', $code)->count_all_results('lab_methods') > 0) {
            $nextNum++;
            $code = sprintf('%s-%03d', $prefix, $nextNum);
        }

        $this->_log('GENERATE_METHOD_CODE', "Code={$code}");
        return $code;
    }

    /**
     * Menghasilkan kode standar SOP default sesuai acuan Labkes Provinsi Riau
     * Format: IKM/5.4.XX/LKL-PR
     */
    public function generate_standard_sop_code() {
        $query = $this->db->query("
            SELECT standard_code 
            FROM lab_methods 
            WHERE standard_code LIKE 'IKM/5.4.%/LKL-PR'
            ORDER BY id DESC
        ");
        $existingCodes = $query->result_array();

        $maxNum = 40;
        foreach ($existingCodes as $row) {
            if (preg_match('#IKM/5\.4\.(\d+)/LKL-PR#', $row['standard_code'], $m)) {
                $num = (int)$m[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        $nextNum = $maxNum + 1;
        return sprintf('IKM/5.4.%02d/LKL-PR', $nextNum);
    }
}
