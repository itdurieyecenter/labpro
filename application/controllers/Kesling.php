<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modul Kesehatan Lingkungan (Pilot: Air Minum).
 *
 * Order pengujian sampel lingkungan (FPPS, koordinat, personel sampling),
 * entri hasil dengan evaluasi lewat-ambang terhadap baku mutu multi-standar,
 * cetak Laporan Hasil Uji (LHU) bilingual, dan master baku mutu.
 */
class Kesling extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->rbac_lib->require_login();
        $this->load->model('kesling_model');
        $this->load->library('form_validation');
    }

    private function _log($action, $message, $level = 'info') {
        log_message($level, sprintf("[KESLING-CTRL] USER=%s | ACTION=%s | MSG=%s", $this->rbac_lib->user('username'), $action, $message));
    }

    private function _json($payload, $statusCode = 200) {
        if ($statusCode !== 200) $this->output->set_status_header($statusCode);
        return $this->output->set_content_type('application/json')->set_output(json_encode($payload));
    }

    private function _error($message, $statusCode = 422) {
        return $this->_json(['status' => 'error', 'message' => $message], $statusCode);
    }

    private function _base_data($title, $activeMenu) {
        $data['title'] = $title . ' - UPT Laboratorium Kesehatan Provinsi Riau';
        $data['active_menu'] = $activeMenu;
        $data['user'] = $this->rbac_lib->user();
        $data['menu_tree'] = $this->rbac_lib->get_menu_tree();
        $data['can_create'] = $this->rbac_lib->has_access($activeMenu, 'create');
        $data['can_edit'] = $this->rbac_lib->has_access($activeMenu, 'edit');
        $data['can_delete'] = $this->rbac_lib->has_access($activeMenu, 'delete');
        return $data;
    }

    private function _render($view, $data) {
        $this->load->view('layout/header', $data);
        $this->load->view('layout/sidebar', $data);
        $this->load->view('layout/topbar', $data);
        $this->load->view($view, $data);
        $this->load->view('layout/footer', $data);
    }

    // =====================================================================
    // ORDER LINGKUNGAN
    // =====================================================================

    public function index() {
        $this->rbac_lib->require_access('kesling', 'view');

        $data = $this->_base_data('Order Pengujian Lingkungan', 'kesling');
        $data['stats'] = $this->kesling_model->stats();
        $data['sample_types'] = $this->kesling_model->get_sample_types(TRUE);
        $data['standards'] = $this->kesling_model->get_standards(NULL, TRUE);

        $this->_render('kesling/index', $data);
    }

    public function fetch() {
        $this->rbac_lib->require_access('kesling', 'view');

        $params = [
            'draw'   => (int)$this->input->get('draw', TRUE),
            'start'  => $this->input->get('start', TRUE),
            'length' => $this->input->get('length', TRUE),
            'search' => ['value' => $this->input->get('search[value]', TRUE)],
            'status' => $this->input->get('status', TRUE) ?: 'all',
        ];
        $result = $this->kesling_model->get_orders_datatable($params);

        return $this->_json([
            'status' => 'success',
            'draw' => $params['draw'],
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
            'data' => $result['data'],
        ]);
    }

    public function get($id) {
        $this->rbac_lib->require_access('kesling', 'view');
        $row = $this->kesling_model->get_order((int)$id);
        if (!$row) return $this->_error('Order tidak ditemukan.', 404);
        return $this->_json(['status' => 'success', 'data' => $row]);
    }

    public function get_numbers() {
        $this->rbac_lib->require_access('kesling', 'create');
        return $this->_json(['status' => 'success', 'data' => $this->kesling_model->generate_numbers()]);
    }

    public function save_order() {
        $id = (int)$this->input->post('id', TRUE);
        $this->rbac_lib->require_access('kesling', $id ? 'edit' : 'create');

        $this->form_validation->set_rules('customer_name', 'Nama Customer', 'required|trim');
        $this->form_validation->set_rules('sample_type_id', 'Tipe Sampel', 'required|integer');
        if ($this->form_validation->run() === FALSE) {
            return $this->_error(strip_tags(validation_errors()) ?: 'Data tidak valid.');
        }

        $samplingAt = $this->input->post('sampling_at', TRUE);
        $receivedAt = $this->input->post('received_at', TRUE);
        $payload = [
            'fpps_number'        => $this->input->post('fpps_number', TRUE) ?: NULL,
            'lab_number'         => $this->input->post('lab_number', TRUE) ?: NULL,
            'sample_type_id'     => (int)$this->input->post('sample_type_id', TRUE),
            'standard_id'        => $this->input->post('standard_id', TRUE) ?: NULL,
            'customer_name'      => $this->input->post('customer_name', TRUE),
            'customer_address'   => $this->input->post('customer_address', TRUE) ?: NULL,
            'contact_person'     => $this->input->post('contact_person', TRUE) ?: NULL,
            'contact_phone'      => $this->input->post('contact_phone', TRUE) ?: NULL,
            'sample_description' => $this->input->post('sample_description', TRUE) ?: NULL,
            'sample_location'    => $this->input->post('sample_location', TRUE) ?: NULL,
            'latitude'           => $this->input->post('latitude', TRUE) !== '' ? $this->input->post('latitude', TRUE) : NULL,
            'longitude'          => $this->input->post('longitude', TRUE) !== '' ? $this->input->post('longitude', TRUE) : NULL,
            'sampling_by'        => $this->input->post('sampling_by', TRUE) ?: NULL,
            'sampling_at'        => $samplingAt ?: NULL,
            'received_by'        => $this->input->post('received_by', TRUE) ?: NULL,
            'received_at'        => $receivedAt ?: NULL,
            'analysis_start'     => $this->input->post('analysis_start', TRUE) ?: NULL,
            'analysis_end'       => $this->input->post('analysis_end', TRUE) ?: NULL,
            'status'             => $this->input->post('status', TRUE) ?: 'registered',
            'notes'              => $this->input->post('notes', TRUE) ?: NULL,
        ];

        if ($id) {
            if (!$this->kesling_model->get_order($id)) return $this->_error('Order tidak ditemukan.', 404);
            $this->kesling_model->update_order($id, $payload);
            $this->_log('UPDATE_ORDER', 'Order lingkungan ID=' . $id . ' diperbarui.');
            return $this->_json(['status' => 'success', 'message' => 'Order lingkungan berhasil diperbarui.', 'id' => $id]);
        }

        // Nomor otomatis bila belum diisi
        $numbers = $this->kesling_model->generate_numbers();
        $payload['order_number'] = $numbers['order_number'];
        if (empty($payload['fpps_number'])) $payload['fpps_number'] = $numbers['fpps_number'];
        if (empty($payload['lab_number'])) $payload['lab_number'] = $numbers['lab_number'];
        $user = $this->rbac_lib->user();
        $payload['created_by'] = isset($user['id']) ? (int)$user['id'] : NULL;

        $newId = $this->kesling_model->create_order($payload);
        $this->_log('CREATE_ORDER', 'Order lingkungan baru ID=' . $newId . ' (' . $payload['order_number'] . ').');
        return $this->_json(['status' => 'success', 'message' => 'Order lingkungan berhasil dibuat. No: ' . $payload['order_number'], 'id' => $newId]);
    }

    public function delete_order($id) {
        $this->rbac_lib->require_access('kesling', 'delete');
        $id = (int)$id;
        if (!$this->kesling_model->get_order($id)) return $this->_error('Order tidak ditemukan.', 404);
        $this->kesling_model->delete_order($id);
        $this->_log('DELETE_ORDER', 'Order lingkungan ID=' . $id . ' dihapus.');
        return $this->_json(['status' => 'success', 'message' => 'Order lingkungan berhasil dihapus.']);
    }

    // =====================================================================
    // ENTRI HASIL
    // =====================================================================

    public function hasil($id) {
        $this->rbac_lib->require_access('kesling', 'view');
        $id = (int)$id;

        $order = $this->kesling_model->get_order($id);
        if (!$order) { show_404(); return; }

        $data = $this->_base_data('Entri Hasil Uji Lingkungan', 'kesling');
        $data['order'] = $order;
        $data['sample_types'] = $this->kesling_model->get_sample_types(TRUE);
        $data['standards'] = $this->kesling_model->get_standards($order['sample_type_id'], TRUE);
        $data['assembled'] = $this->kesling_model->assemble_for_report($id);

        $this->_render('kesling/hasil', $data);
    }

    public function save_hasil() {
        $id = (int)$this->input->post('order_id', TRUE);
        $this->rbac_lib->require_access('kesling', 'edit');

        $order = $this->kesling_model->get_order($id);
        if (!$order) return $this->_error('Order tidak ditemukan.', 404);

        $results = $this->input->post('results', TRUE); // [param_id => ['value'=>..,'method'=>..,'notes'=>..]]
        if (!is_array($results)) $results = [];
        $this->kesling_model->save_results($id, $results);

        // Opsi perubahan status (mis. testing -> validasi/completed)
        $newStatus = $this->input->post('status', TRUE);
        if ($newStatus) {
            $this->kesling_model->update_order($id, ['status' => $newStatus]);
        }

        $this->_log('SAVE_HASIL', 'Hasil uji order lingkungan ID=' . $id . ' disimpan.');
        return $this->_json(['status' => 'success', 'message' => 'Hasil uji berhasil disimpan.']);
    }

    // =====================================================================
    // CETAK LHU
    // =====================================================================

    public function cetak_lhu($id) {
        $this->rbac_lib->require_access('kesling', 'view');
        $id = (int)$id;

        $assembled = $this->kesling_model->assemble_for_report($id);
        if (!$assembled) { show_404(); return; }

        $data['assembled'] = $assembled;
        $data['profile'] = app_profile();
        $this->load->view('kesling/cetak_lhu', $data);
    }

    // =====================================================================
    // MASTER BAKU MUTU
    // =====================================================================

    public function baku_mutu() {
        $this->rbac_lib->require_access('kesling/baku_mutu', 'view');

        $sampleTypeId = (int)($this->input->get('tipe', TRUE) ?: 1);
        $data = $this->_base_data('Baku Mutu & Standar Lingkungan', 'kesling/baku_mutu');
        $data['sample_types'] = $this->kesling_model->get_sample_types(FALSE);
        $data['selected_type'] = $sampleTypeId;
        $data['matrix'] = $this->kesling_model->get_baku_mutu_matrix($sampleTypeId);

        $this->_render('kesling/baku_mutu', $data);
    }
}
