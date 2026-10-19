<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Client extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->rbac_lib->require_login();
        $this->load->model('client_model');
    }

    private function _log($action, $message, $level = 'info') {
        $user = $this->rbac_lib->user();
        $logMsg = sprintf(
            "[CLIENT-CRUD] ACTION=%s | USER_ID=%s | USER=%s | ROLE=%s | MSG=%s",
            $action,
            isset($user['id']) ? $user['id'] : '-',
            isset($user['username']) ? $user['username'] : '-',
            isset($user['role_name']) ? $user['role_name'] : '-',
            $message
        );
        log_message($level, $logMsg);
    }

    public function index() {
        $this->rbac_lib->require_access('client', 'view');

        $data['title'] = 'Master Klien (Pasien & Perusahaan) - LIS PRO';
        $data['active_menu'] = 'client';
        $data['user'] = $this->rbac_lib->user();
        $data['menu_tree'] = $this->rbac_lib->get_menu_tree();
        $data['can_create'] = $this->rbac_lib->has_access('client', 'create');
        $data['can_edit'] = $this->rbac_lib->has_access('client', 'edit');
        $data['can_delete'] = $this->rbac_lib->has_access('client', 'delete');

        $this->_log('VIEW_PAGE', 'Halaman Master Klien diakses');

        $this->load->view('layout/header', $data);
        $this->load->view('layout/sidebar', $data);
        $this->load->view('layout/topbar', $data);
        $this->load->view('client/index', $data);
        $this->load->view('layout/footer', $data);
    }

    public function fetch() {
        $this->rbac_lib->require_access('client', 'view');

        $params = [
            'draw' => $this->input->get('draw', TRUE),
            'start' => $this->input->get('start', TRUE),
            'length' => $this->input->get('length', TRUE),
            'search' => [
                'value' => $this->input->get('search[value]', TRUE),
                'regex' => $this->input->get('search[regex]', TRUE)
            ],
            'order' => [],
            'columns' => [],
            'client_type' => $this->input->get('client_type', TRUE),
            'status' => $this->input->get('status', TRUE)
        ];

        // Parse order columns from DataTables
        $orderIndex = 0;
        while ($this->input->get('order[' . $orderIndex . '][column]', TRUE) !== NULL) {
            $params['order'][] = [
                'column' => $this->input->get('order[' . $orderIndex . '][column]', TRUE),
                'dir' => $this->input->get('order[' . $orderIndex . '][dir]', TRUE)
            ];
            $orderIndex++;
        }

        // Parse columns definition from DataTables
        $colIndex = 0;
        while ($this->input->get('columns[' . $colIndex . '][data]', TRUE) !== NULL) {
            $params['columns'][] = [
                'data' => $this->input->get('columns[' . $colIndex . '][data]', TRUE),
                'name' => $this->input->get('columns[' . $colIndex . '][name]', TRUE),
                'orderable' => $this->input->get('columns[' . $colIndex . '][orderable]', TRUE),
                'searchable' => $this->input->get('columns[' . $colIndex . '][searchable]', TRUE),
                'search' => [
                    'value' => $this->input->get('columns[' . $colIndex . '][search][value]', TRUE),
                    'regex' => $this->input->get('columns[' . $colIndex . '][search][regex]', TRUE)
                ]
            ];
            $colIndex++;
        }

        $this->_log('FETCH_DATATABLE', sprintf("draw=%d | start=%d | length=%d | search=%s | type=%s | status=%s",
            $params['draw'] ?? '-', $params['start'] ?? '-', $params['length'] ?? '-',
            $params['search']['value'] ?? '-', $params['client_type'] ?? 'all', $params['status'] ?? 'all'));

        // Ringkasan statistik (untuk kartu statistik)
        if ($this->input->get('summary', TRUE) === '1') {
            $summary = $this->client_model->count_summary_by_filter($params['client_type'] ?? null, $params['status'] ?? null);
            $this->_log('FETCH_SUMMARY', sprintf("type=%s | status=%s | total=%d", $params['client_type'] ?? 'all', $params['status'] ?? 'all', $summary['total']));
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'success',
                    'summary' => $summary
                ]));
        }

        $result = $this->client_model->get_all_datatable($params);

        $this->_log('FETCH_DATATABLE_RESULT', sprintf("draw=%d | recordsTotal=%d | recordsFiltered=%d | returned=%d",
            $result['draw'], $result['recordsTotal'], $result['recordsFiltered'], count($result['data'])));

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

    public function get($id) {
        $this->rbac_lib->require_access('client', 'view');

        $this->_log('READ_DETAIL', sprintf("Meminta data klien ID=%d", (int)$id));

        $client = $this->client_model->get_by_id((int)$id);
        if (!$client) {
            $this->_log('READ_DETAIL_FAILED', sprintf("Klien ID=%d tidak ditemukan", (int)$id), 'error');
            return $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Data klien tidak ditemukan.']));
        }

        $this->_log('READ_DETAIL_SUCCESS', sprintf("Data klien ID=%d | Kode=%s | Nama=%s", $client['id'], $client['client_code'], $client['name']));

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => 'success', 'data' => $client]));
    }

    public function get_next_code($type = 'perorangan') {
        $this->rbac_lib->require_access('client', 'view');
        $type = in_array($type, ['perusahaan', 'perorangan']) ? $type : 'perorangan';

        $this->_log('GENERATE_CODE', sprintf("Tipe=%s", $type));

        $code = $this->client_model->generate_code($type);

        $this->_log('GENERATE_CODE_RESULT', sprintf("Kode dihasilkan: %s untuk tipe=%s", $code, $type));

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => 'success', 'code' => $code]));
    }

    public function save() {
        $id = $this->input->post('id');
        $isEdit = !empty($id);

        $requiredPermission = $isEdit ? 'edit' : 'create';
        if (!$this->rbac_lib->has_access('client', $requiredPermission)) {
            $this->_log('SAVE_FORBIDDEN', sprintf("Akses ditolak untuk %s klien", $isEdit ? 'edit' : 'create'), 'error');
            return $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses untuk ' . ($isEdit ? 'mengubah' : 'menambah') . ' data klien.']));
        }

        $this->form_validation->set_rules('client_type', 'Tipe Klien', 'required|in_list[perorangan,perusahaan]');
        $this->form_validation->set_rules('name', 'Nama Klien', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('phone', 'Nomor Telepon/HP', 'required|trim|max_length[30]');

        $this->_log('SAVE_REQUEST', sprintf("Methode=%s | ID=%s | Input: %s", $isEdit ? 'UPDATE' : 'CREATE', $id ?? 'baru', json_encode($this->input->post(NULL, TRUE))));

        if ($this->form_validation->run() === FALSE) {
            $errors = validation_errors(' ', ' ');
            $this->_log('SAVE_VALIDATION_ERROR', sprintf("Validasi gagal: %s", $errors), 'error');
            return $this->output
                ->set_status_header(422)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => $errors
                ]));
        }

        $client_type = $this->input->post('client_type', TRUE);
        $client_code = $this->input->post('client_code', TRUE);

        if (empty($client_code)) {
            $client_code = $this->client_model->generate_code($client_type);
            $this->_log('SAVE_GENERATE_CODE', sprintf("Kode otomatis dihasilkan: %s", $client_code));
        }

        $existing = $this->client_model->get_by_code($client_code);
        if ($existing && (!$isEdit || (int)$existing['id'] !== (int)$id)) {
            $this->_log('SAVE_DUPLICATE_CODE', sprintf("Kode %s sudah digunakan oleh ID=%d", $client_code, $existing['id']), 'error');
            return $this->output
                ->set_status_header(422)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Kode klien ' . $client_code . ' sudah digunakan oleh data lain.'
                ]));
        }

        $data = [
            'client_type' => $client_type,
            'client_code' => $client_code,
            'name' => $this->input->post('name', TRUE),
            'identity_number' => $this->input->post('identity_number', TRUE) ?: null,
            'gender' => $this->input->post('gender', TRUE) ?: null,
            'birth_date' => $this->input->post('birth_date', TRUE) ?: null,
            'blood_type' => $this->input->post('blood_type', TRUE) ?: null,
            'pic_name' => $this->input->post('pic_name', TRUE) ?: null,
            'pic_phone' => $this->input->post('pic_phone', TRUE) ?: null,
            'phone' => $this->input->post('phone', TRUE),
            'email' => $this->input->post('email', TRUE) ?: null,
            'address' => $this->input->post('address', TRUE) ?: null,
            'contract_discount' => (float)$this->input->post('contract_discount'),
            'payment_term' => $this->input->post('payment_term', TRUE) ?: 'Tunai',
            'status' => $this->input->post('status', TRUE) === 'inactive' ? 'inactive' : 'active',
            'notes' => $this->input->post('notes', TRUE) ?: null,
        ];

        if ($client_type === 'perusahaan') {
            $data['gender'] = null;
            $data['birth_date'] = null;
            $data['blood_type'] = null;
        } else {
            $data['pic_name'] = null;
            $data['pic_phone'] = null;
            $data['contract_discount'] = 0.00;
        }

        if ($isEdit) {
            $this->client_model->update((int)$id, $data);
            $savedId = (int)$id;
            $msg = 'Data klien ' . $data['name'] . ' berhasil diperbarui.';
            $this->_log('UPDATE_SUCCESS', sprintf("ID=%d | Kode=%s | Nama=%s | Tipe=%s | Data=%s", $savedId, $client_code, $data['name'], $client_type, json_encode($data)));
        } else {
            $savedId = $this->client_model->insert($data);
            $msg = 'Klien baru ' . $data['name'] . ' (' . $client_code . ') berhasil ditambahkan.';
            $this->_log('CREATE_SUCCESS', sprintf("ID=%d | Kode=%s | Nama=%s | Tipe=%s | Data=%s", $savedId, $client_code, $data['name'], $client_type, json_encode($data)));
        }

        $savedClient = $this->client_model->get_by_id($savedId);

        $this->_log('SAVE_COMPLETE', sprintf("Operasi %s selesai. ID=%d | Kode=%s", $isEdit ? 'UPDATE' : 'CREATE', $savedId, $client_code));

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'message' => $msg,
                'data' => $savedClient
            ]));
    }

    public function delete($id) {
        if (!$this->rbac_lib->has_access('client', 'delete')) {
            $this->_log('DELETE_FORBIDDEN', sprintf("Akses delete ditolak untuk ID=%d", (int)$id), 'error');
            return $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses untuk menghapus klien ini.']));
        }

        $client = $this->client_model->get_by_id((int)$id);
        if (!$client) {
            $this->_log('DELETE_NOT_FOUND', sprintf("Klien ID=%d tidak ditemukan untuk dihapus", (int)$id), 'error');
            return $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Data klien tidak ditemukan.']));
        }

        $this->_log('DELETE_REQUEST', sprintf("Menghapus ID=%d | Kode=%s | Nama=%s", $client['id'], $client['client_code'], $client['name']));

        $this->client_model->delete((int)$id);

        $this->_log('DELETE_SUCCESS', sprintf("ID=%d | Kode=%s | Nama=%s telah dihapus", $client['id'], $client['client_code'], $client['name']));

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'message' => 'Data klien ' . $client['name'] . ' (' . $client['client_code'] . ') berhasil dihapus.'
            ]));
    }
}
