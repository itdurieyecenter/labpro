<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tarif extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->rbac_lib->require_login();
        $this->load->model('lab_model');
    }

    private function _log($action, $message, $level = 'info') {
        $user = $this->rbac_lib->user();
        log_message($level, sprintf(
            "[TARIF-CRUD] ACTION=%s | USER_ID=%s | USER=%s | ROLE=%s | MSG=%s",
            $action,
            $user['id'] ?? '-',
            $user['username'] ?? '-',
            $user['role_name'] ?? '-',
            $message
        ));
    }

    // ==========================================
    // 1. VIEWS UTAMA
    // ==========================================
    public function index() {
        $this->rbac_lib->require_access('tarif', 'view');

        $data['title'] = 'Master Tarif & Parameter Laboratorium - LIS PRO';
        $data['active_menu'] = 'tarif';
        $data['user'] = $this->rbac_lib->user();
        $data['menu_tree'] = $this->rbac_lib->get_menu_tree();
        $data['can_create'] = $this->rbac_lib->has_access('tarif', 'create');
        $data['can_edit'] = $this->rbac_lib->has_access('tarif', 'edit');
        $data['can_delete'] = $this->rbac_lib->has_access('tarif', 'delete');
        $data['active_tab'] = 'parameter';

        $data['categories'] = $this->lab_model->get_categories();
        $data['methods'] = $this->lab_model->get_all_methods();

        $this->_log('VIEW_PAGE', 'Halaman Master Tarif & Parameter diakses');

        $this->load->view('layout/header', $data);
        $this->load->view('layout/sidebar', $data);
        $this->load->view('layout/topbar', $data);
        $this->load->view('tarif/index', $data);
        $this->load->view('layout/footer', $data);
    }

    public function template() {
        $this->rbac_lib->require_access('tarif/template', 'view');

        $data['title'] = 'Master Template & Paket Laboratorium - LIS PRO';
        $data['active_menu'] = 'tarif/template';
        $data['user'] = $this->rbac_lib->user();
        $data['menu_tree'] = $this->rbac_lib->get_menu_tree();
        $data['can_create'] = $this->rbac_lib->has_access('tarif/template', 'create');
        $data['can_edit'] = $this->rbac_lib->has_access('tarif/template', 'edit');
        $data['can_delete'] = $this->rbac_lib->has_access('tarif/template', 'delete');
        $data['active_tab'] = 'template';

        $data['categories'] = $this->lab_model->get_categories();
        $data['methods'] = $this->lab_model->get_all_methods();

        $this->_log('VIEW_PAGE', 'Halaman Master Template Lab diakses');

        $this->load->view('layout/header', $data);
        $this->load->view('layout/sidebar', $data);
        $this->load->view('layout/topbar', $data);
        $this->load->view('tarif/index', $data);
        $this->load->view('layout/footer', $data);
    }

    public function metode() {
        $this->rbac_lib->require_access('tarif/metode', 'view');

        $data['title'] = 'Master Metode Pemeriksaan Laboratorium - LIS PRO';
        $data['active_menu'] = 'tarif/metode';
        $data['user'] = $this->rbac_lib->user();
        $data['menu_tree'] = $this->rbac_lib->get_menu_tree();
        $data['can_create'] = $this->rbac_lib->has_access('tarif/metode', 'create');
        $data['can_edit'] = $this->rbac_lib->has_access('tarif/metode', 'edit');
        $data['can_delete'] = $this->rbac_lib->has_access('tarif/metode', 'delete');
        $data['active_tab'] = 'metode';

        $data['categories'] = $this->lab_model->get_categories();
        $data['methods'] = $this->lab_model->get_all_methods();

        $this->_log('VIEW_PAGE', 'Halaman Master Metode Lab diakses');

        $this->load->view('layout/header', $data);
        $this->load->view('layout/sidebar', $data);
        $this->load->view('layout/topbar', $data);
        $this->load->view('tarif/index', $data);
        $this->load->view('layout/footer', $data);
    }

    // ==========================================
    // 2. AJAX PARAMETER & TARIF SATUAN
    // ==========================================
    public function fetch_parameters() {
        $this->rbac_lib->require_access('tarif', 'view');

        $params = [
            'draw' => $this->input->get('draw', TRUE),
            'start' => $this->input->get('start', TRUE),
            'length' => $this->input->get('length', TRUE),
            'search' => [
                'value' => $this->input->get('search[value]', TRUE)
            ],
            'category_id' => $this->input->get('category_id', TRUE),
            'tariff_group' => $this->input->get('tariff_group', TRUE) ?: 'umum',
            'order' => []
        ];

        $orderIndex = 0;
        while ($this->input->get('order[' . $orderIndex . '][column]', TRUE) !== NULL) {
            $params['order'][] = [
                'column' => $this->input->get('order[' . $orderIndex . '][column]', TRUE),
                'dir' => $this->input->get('order[' . $orderIndex . '][dir]', TRUE)
            ];
            $orderIndex++;
        }

        $result = $this->lab_model->get_parameters_datatable($params);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'draw' => $result['draw'],
                'recordsTotal' => $result['recordsTotal'],
                'recordsFiltered' => $result['recordsFiltered'],
                'data' => $result['data']
            ]));
    }

    public function get_parameter($id) {
        $this->rbac_lib->require_access('tarif', 'view');
        $tariff_group = $this->input->get('tariff_group', TRUE) ?: 'umum';

        $param = $this->lab_model->get_parameter_by_id((int)$id, $tariff_group);
        if (!$param) {
            return $this->output->set_status_header(404)->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'Parameter lab tidak ditemukan.']));
        }

        return $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'success', 'data' => $param]));
    }

    public function save_parameter() {
        $id = $this->input->post('id');
        $isEdit = !empty($id);

        $permAction = $isEdit ? 'edit' : 'create';
        if (!$this->rbac_lib->has_access('tarif', $permAction)) {
            return $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'Tidak memiliki hak akses.']));
        }

        $this->form_validation->set_rules('category_id', 'Kategori Lab', 'required|numeric');
        $this->form_validation->set_rules('parameter_code', 'Kode Parameter', 'trim|max_length[50]');
        $this->form_validation->set_rules('parameter_name', 'Nama Parameter', 'required|trim|max_length[150]');

        if ($this->form_validation->run() === FALSE) {
            return $this->output->set_status_header(422)->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => validation_errors(' ', ' ')]));
        }

        $param_code = strtoupper(trim($this->input->post('parameter_code', TRUE)));
        if (empty($param_code)) {
            $param_code = $this->lab_model->generate_parameter_code($this->input->post('category_id'));
        }

        $param_data = [
            'category_id' => (int)$this->input->post('category_id'),
            'method_id' => $this->input->post('method_id') ? (int)$this->input->post('method_id') : null,
            'parameter_code' => $param_code,
            'parameter_name' => trim($this->input->post('parameter_name', TRUE)),
            'unit' => trim($this->input->post('unit', TRUE)) ?: null,
            'data_type' => $this->input->post('data_type', TRUE) ?: 'numeric',
            'options_list' => $this->input->post('options_list', TRUE) ?: null,
            'sort_order' => (int)$this->input->post('sort_order'),
            'is_active' => (int)($this->input->post('is_active') ?? 1)
        ];

        $tariff_data = [
            'tariff_group' => $this->input->post('tariff_group', TRUE) ?: 'umum',
            'jasa_manajemen' => (float)$this->input->post('jasa_manajemen'),
            'jasa_dokter' => (float)$this->input->post('jasa_dokter'),
            'jasa_petugas' => (float)$this->input->post('jasa_petugas'),
            'kso' => (float)$this->input->post('kso'),
            'bhp' => (float)$this->input->post('bhp'),
            'notes' => $this->input->post('notes', TRUE) ?: null
        ];

        $saved_id = $this->lab_model->save_parameter($param_data, $tariff_data, $isEdit ? (int)$id : null);

        if (!$saved_id) {
            return $this->output->set_status_header(500)->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data parameter.']));
        }

        return $this->output->set_content_type('application/json')->set_output(json_encode([
            'status' => 'success',
            'message' => 'Parameter ' . $param_data['parameter_name'] . ' berhasil disimpan!',
            'data_id' => $saved_id
        ]));
    }

    public function delete_parameter($id) {
        if (!$this->rbac_lib->has_access('tarif', 'delete')) {
            return $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'Tidak memiliki hak akses.']));
        }

        $this->lab_model->delete_parameter((int)$id);
        return $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'success', 'message' => 'Parameter lab berhasil dihapus.']));
    }

    // ==========================================
    // 3. AJAX NILAI RUJUKAN & KLASIFIKASI UMUR
    // ==========================================
    public function get_reference_ranges($param_id) {
        $this->rbac_lib->require_access('tarif', 'view');
        $ranges = $this->lab_model->get_reference_ranges_by_param((int)$param_id);
        return $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'success', 'data' => $ranges]));
    }

    public function save_reference_range() {
        if (!$this->rbac_lib->has_access('tarif', 'create') && !$this->rbac_lib->has_access('tarif', 'edit')) {
            return $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'Tidak memiliki hak akses.']));
        }

        $id = $this->input->post('id');
        $parameter_id = (int)$this->input->post('parameter_id');

        if (!$parameter_id) {
            return $this->output->set_status_header(422)->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'Parameter ID tidak valid.']));
        }

        $data = [
            'parameter_id' => $parameter_id,
            'gender' => $this->input->post('gender', TRUE) ?: 'ALL',
            'age_classification' => $this->input->post('age_classification', TRUE) ?: 'semua_umur',
            'age_min_days' => (int)$this->input->post('age_min_days'),
            'age_max_days' => (int)($this->input->post('age_max_days') ?: 36500),
            'age_label' => trim($this->input->post('age_label', TRUE)) ?: 'Semua Umur',
            'min_val' => $this->input->post('min_val') !== '' ? (float)$this->input->post('min_val') : null,
            'max_val' => $this->input->post('max_val') !== '' ? (float)$this->input->post('max_val') : null,
            'text_normal' => $this->input->post('text_normal', TRUE) ?: null,
            'critical_low' => $this->input->post('critical_low') !== '' ? (float)$this->input->post('critical_low') : null,
            'critical_high' => $this->input->post('critical_high') !== '' ? (float)$this->input->post('critical_high') : null,
            'reference_display' => trim($this->input->post('reference_display', TRUE)),
            'notes' => $this->input->post('notes', TRUE) ?: null
        ];

        if (empty($data['reference_display'])) {
            if ($data['min_val'] !== null && $data['max_val'] !== null) {
                $data['reference_display'] = $data['min_val'] . ' - ' . $data['max_val'];
            } elseif (!empty($data['text_normal'])) {
                $data['reference_display'] = $data['text_normal'];
            } else {
                $data['reference_display'] = '-';
            }
        }

        $res_id = $this->lab_model->save_reference_range($data, !empty($id) ? (int)$id : null);
        return $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'success', 'message' => 'Nilai rujukan berhasil disimpan!', 'data_id' => $res_id]));
    }

    public function delete_reference_range($id) {
        if (!$this->rbac_lib->has_access('tarif', 'delete')) {
            return $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'Tidak memiliki hak akses.']));
        }

        $this->lab_model->delete_reference_range((int)$id);
        return $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'success', 'message' => 'Nilai rujukan dihapus.']));
    }

    // ==========================================
    // 4. AJAX TEMPLATE / PAKET LABORATORIUM
    // ==========================================
    public function fetch_templates() {
        $this->rbac_lib->require_access('tarif/template', 'view');

        $params = [
            'draw' => $this->input->get('draw', TRUE),
            'start' => $this->input->get('start', TRUE),
            'length' => $this->input->get('length', TRUE),
            'search' => [
                'value' => $this->input->get('search[value]', TRUE)
            ],
            'category_id' => $this->input->get('category_id', TRUE),
            'tariff_group' => $this->input->get('tariff_group', TRUE) ?: 'umum',
            'order' => []
        ];

        $orderIndex = 0;
        while ($this->input->get('order[' . $orderIndex . '][column]', TRUE) !== NULL) {
            $params['order'][] = [
                'column' => $this->input->get('order[' . $orderIndex . '][column]', TRUE),
                'dir' => $this->input->get('order[' . $orderIndex . '][dir]', TRUE)
            ];
            $orderIndex++;
        }

        $result = $this->lab_model->get_templates_datatable($params);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'draw' => $result['draw'],
                'recordsTotal' => $result['recordsTotal'],
                'recordsFiltered' => $result['recordsFiltered'],
                'data' => $result['data']
            ]));
    }

    public function get_template($id) {
        $this->rbac_lib->require_access('tarif/template', 'view');
        $tariff_group = $this->input->get('tariff_group', TRUE) ?: 'umum';

        $tpl = $this->lab_model->get_template_by_id((int)$id, $tariff_group);
        if (!$tpl) {
            return $this->output->set_status_header(404)->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'Template lab tidak ditemukan.']));
        }

        // Parse structured metadata in description if present
        $tpl['service_type'] = 'all';
        $tpl['tat_duration'] = '';
        $tpl['patient_preparation'] = '';

        if (!empty($tpl['description']) && substr($tpl['description'], 0, 1) === '{') {
            $meta = json_decode($tpl['description'], true);
            if (is_array($meta)) {
                $tpl['service_type'] = $meta['layanan'] ?? 'all';
                $tpl['tat_duration'] = $meta['tat'] ?? '';
                $tpl['patient_preparation'] = $meta['persiapan'] ?? '';
                $tpl['description'] = $meta['deskripsi'] ?? '';
            }
        }

        return $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'success', 'data' => $tpl]));
    }

    public function save_template() {
        $id = $this->input->post('id');
        $isEdit = !empty($id);

        $permAction = $isEdit ? 'edit' : 'create';
        if (!$this->rbac_lib->has_access('tarif/template', $permAction)) {
            return $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'Tidak memiliki hak akses.']));
        }

        $this->form_validation->set_rules('category_id', 'Kategori Lab', 'required|numeric');
        $this->form_validation->set_rules('template_code', 'Kode Template/Paket', 'trim|max_length[50]');
        $this->form_validation->set_rules('template_name', 'Nama Template/Paket', 'required|trim|max_length[150]');

        if ($this->form_validation->run() === FALSE) {
            return $this->output->set_status_header(422)->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => validation_errors(' ', ' ')]));
        }

        $desc = trim($this->input->post('description', TRUE));
        $service_type = trim($this->input->post('service_type', TRUE));
        $tat = trim($this->input->post('tat_duration', TRUE));
        $prep = trim($this->input->post('patient_preparation', TRUE));

        // Format metadata SIMRS dalam description
        $meta = [
            'layanan' => !empty($service_type) ? $service_type : 'all',
            'tat' => $tat,
            'persiapan' => $prep,
            'deskripsi' => $desc
        ];

        $tpl_code = strtoupper(trim($this->input->post('template_code', TRUE)));
        if (empty($tpl_code)) {
            $tpl_code = $this->lab_model->generate_template_code($this->input->post('category_id'));
        }

        $tpl_data = [
            'category_id' => (int)$this->input->post('category_id'),
            'template_code' => $tpl_code,
            'template_name' => trim($this->input->post('template_name', TRUE)),
            'description' => json_encode($meta, JSON_UNESCAPED_UNICODE),
            'is_active' => (int)($this->input->post('is_active') ?? 1),
            'sort_order' => (int)$this->input->post('sort_order')
        ];

        $tariff_data = [
            'tariff_group' => $this->input->post('tariff_group', TRUE) ?: 'umum',
            'jasa_manajemen' => (float)$this->input->post('jasa_manajemen'),
            'jasa_dokter' => (float)$this->input->post('jasa_dokter'),
            'jasa_petugas' => (float)$this->input->post('jasa_petugas'),
            'kso' => (float)$this->input->post('kso'),
            'bhp' => (float)$this->input->post('bhp'),
            'notes' => $this->input->post('notes', TRUE) ?: null
        ];

        $item_ids = $this->input->post('parameter_ids');
        if (!is_array($item_ids)) {
            $item_ids = [];
        }

        $saved_id = $this->lab_model->save_template($tpl_data, $tariff_data, $item_ids, $isEdit ? (int)$id : null);

        if (!$saved_id) {
            return $this->output->set_status_header(500)->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'Gagal menyimpan template lab.']));
        }

        return $this->output->set_content_type('application/json')->set_output(json_encode([
            'status' => 'success',
            'message' => 'Template ' . $tpl_data['template_name'] . ' berhasil disimpan!',
            'data_id' => $saved_id
        ]));
    }

    public function delete_template($id) {
        if (!$this->rbac_lib->has_access('tarif/template', 'delete')) {
            return $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'Tidak memiliki hak akses.']));
        }

        $this->lab_model->delete_template((int)$id);
        return $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'success', 'message' => 'Template lab berhasil dihapus.']));
    }

    // ==========================================
    // 5. AJAX METODE PEMERIKSAAN
    // ==========================================
    public function fetch_methods() {
        $this->rbac_lib->require_access('tarif/metode', 'view');

        $params = [
            'draw' => $this->input->get('draw', TRUE),
            'start' => $this->input->get('start', TRUE),
            'length' => $this->input->get('length', TRUE),
            'search' => [
                'value' => $this->input->get('search[value]', TRUE)
            ],
            'order' => []
        ];

        $orderIndex = 0;
        while ($this->input->get('order[' . $orderIndex . '][column]', TRUE) !== NULL) {
            $params['order'][] = [
                'column' => $this->input->get('order[' . $orderIndex . '][column]', TRUE),
                'dir' => $this->input->get('order[' . $orderIndex . '][dir]', TRUE)
            ];
            $orderIndex++;
        }

        $result = $this->lab_model->get_methods_datatable($params);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'draw' => $result['draw'],
                'recordsTotal' => $result['recordsTotal'],
                'recordsFiltered' => $result['recordsFiltered'],
                'data' => $result['data']
            ]));
    }

    public function get_method($id) {
        $this->rbac_lib->require_access('tarif/metode', 'view');
        $method = $this->lab_model->get_method_by_id((int)$id);
        if (!$method) {
            return $this->output->set_status_header(404)->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'Metode tidak ditemukan.']));
        }
        return $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'success', 'data' => $method]));
    }

    public function save_method() {
        $id = $this->input->post('id');
        $isEdit = !empty($id);

        $permAction = $isEdit ? 'edit' : 'create';
        if (!$this->rbac_lib->has_access('tarif/metode', $permAction)) {
            return $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'Tidak memiliki hak akses.']));
        }

        $this->form_validation->set_rules('method_code', 'Kode Metode', 'trim|max_length[50]');
        $this->form_validation->set_rules('method_name', 'Nama Metode', 'required|trim|max_length[150]');

        if ($this->form_validation->run() === FALSE) {
            return $this->output->set_status_header(422)->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => validation_errors(' ', ' ')]));
        }

        $method_code = strtoupper(trim($this->input->post('method_code', TRUE)));
        if (empty($method_code)) {
            $method_code = $this->lab_model->generate_method_code();
        }

        $std_code = trim($this->input->post('standard_code', TRUE));
        if (empty($std_code)) {
            $std_code = $this->lab_model->generate_standard_sop_code();
        }

        $data = [
            'method_code' => $method_code,
            'method_name' => trim($this->input->post('method_name', TRUE)),
            'standard_code' => $std_code ?: null,
            'instrument_name' => trim($this->input->post('instrument_name', TRUE)) ?: null,
            'description' => $this->input->post('description', TRUE) ?: null,
            'is_active' => (int)($this->input->post('is_active') ?? 1)
        ];

        if ($isEdit) {
            $this->lab_model->update_method((int)$id, $data);
            $msg = 'Metode pemeriksaan berhasil diperbarui.';
        } else {
            $this->lab_model->insert_method($data);
            $msg = 'Metode pemeriksaan baru berhasil ditambahkan.';
        }

        return $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'success', 'message' => $msg]));
    }

    public function delete_method($id) {
        if (!$this->rbac_lib->has_access('tarif/metode', 'delete')) {
            return $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(['status' => 'error', 'message' => 'Tidak memiliki hak akses.']));
        }

        $this->lab_model->delete_method((int)$id);
        return $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'success', 'message' => 'Metode pemeriksaan berhasil dihapus.']));
    }

    // ==========================================
    // 6. HELPER & SUMMARY STATISTIK
    // ==========================================
    public function get_parameters_list() {
        $this->rbac_lib->require_access('tarif', 'view');
        $cat_id = $this->input->get('category_id');
        $tariff_group = $this->input->get('tariff_group') ?: 'umum';

        $this->db->select('p.id, p.category_id, p.parameter_code, p.parameter_name, p.unit, p.data_type, c.category_name, COALESCE(t.total_tarif, 0) as standard_tariff');
        $this->db->from('lab_parameters p');
        $this->db->join('lab_categories c', 'c.id = p.category_id', 'left');
        $this->db->join("lab_tariffs t", "t.item_id = p.id AND t.item_type = 'parameter' AND t.tariff_group = " . $this->db->escape($tariff_group), 'left');
        $this->db->where('p.is_active', 1);

        if (!empty($cat_id) && $cat_id !== 'all') {
            $this->db->where('p.category_id', (int)$cat_id);
        }

        $this->db->order_by('c.sort_order', 'ASC');
        $this->db->order_by('p.sort_order', 'ASC');
        $params = $this->db->get()->result_array();

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'data' => $params
            ]));
    }

    public function summary() {
        $this->rbac_lib->require_access('tarif', 'view');
        $counts = $this->lab_model->get_summary_counts();
        return $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'success', 'data' => $counts]));
    }

    // ==========================================
    // 7. ENDPOINT GENERATE KODE OTOMATIS (AJAX)
    // ==========================================
    public function get_next_parameter_code($category_id = null) {
        $this->rbac_lib->require_access('tarif', 'view');
        if (empty($category_id)) {
            $category_id = $this->input->get('category_id');
        }
        $code = $this->lab_model->generate_parameter_code($category_id);
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'code' => $code
            ]));
    }

    public function get_next_template_code($category_id = null) {
        $this->rbac_lib->require_access('tarif/template', 'view');
        if (empty($category_id)) {
            $category_id = $this->input->get('category_id');
        }
        $code = $this->lab_model->generate_template_code($category_id);
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'code' => $code
            ]));
    }

    public function get_next_method_code() {
        $this->rbac_lib->require_access('tarif/metode', 'view');
        $code = $this->lab_model->generate_method_code();
        $sop = $this->lab_model->generate_standard_sop_code();
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'code' => $code,
                'standard_code' => $sop
            ]));
    }
}
