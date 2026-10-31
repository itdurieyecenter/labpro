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
            'biaya_pengujian'    => max(0, (float)$this->input->post('biaya_pengujian', TRUE)),
        ];
        $userNow = $this->rbac_lib->user();
        $userIdNow = isset($userNow['id']) ? (int)$userNow['id'] : NULL;

        if ($id) {
            $existing = $this->kesling_model->get_order($id);
            if (!$existing) return $this->_error('Order tidak ditemukan.', 404);
            // Status validasi/selesai hanya dikelola oleh alur verifikasi berjenjang
            if (in_array($existing['status'], ['validasi', 'completed'], true)) {
                $payload['status'] = $existing['status'];
            } elseif (!in_array($payload['status'], ['registered', 'sampling', 'testing', 'cancelled'], true)) {
                $payload['status'] = $existing['status'];
            }
            if (in_array($payload['status'], ['sampling', 'testing'], true)
                && !empty($existing['pendaftaran_id']) && $existing['tagihan_status'] === 'UNPAID') {
                return $this->_error('Tagihan ' . $existing['tagihan_reg'] . ' belum dibayar/dijamin di kasir. Order belum dapat diproses ke lab.', 409);
            }
            // Biaya terkunci setelah tagihan diproses kasir (PAID / GUARANTEED)
            if (!empty($existing['pendaftaran_id']) && $existing['tagihan_status'] !== 'UNPAID') {
                $payload['biaya_pengujian'] = (float)$existing['biaya_pengujian'];
            }
            $this->kesling_model->update_order($id, $payload);
            $tagihan = $this->kesling_model->sync_tagihan($id, $userIdNow);
            $this->_log('UPDATE_ORDER', 'Order lingkungan ID=' . $id . ' diperbarui.');
            $msg = 'Order lingkungan berhasil diperbarui.';
            if ($tagihan['status'] === 'locked') $msg .= ' Biaya tidak diubah karena tagihan sudah diproses kasir.';
            return $this->_json(['status' => 'success', 'message' => $msg, 'id' => $id]);
        }

        $payload['status'] = 'registered';

        // Nomor otomatis bila belum diisi
        $numbers = $this->kesling_model->generate_numbers();
        $payload['order_number'] = $numbers['order_number'];
        if (empty($payload['fpps_number'])) $payload['fpps_number'] = $numbers['fpps_number'];
        if (empty($payload['lab_number'])) $payload['lab_number'] = $numbers['lab_number'];
        $user = $this->rbac_lib->user();
        $payload['created_by'] = isset($user['id']) ? (int)$user['id'] : NULL;

        $newId = $this->kesling_model->create_order($payload);
        $tagihan = $this->kesling_model->sync_tagihan($newId, $userIdNow);
        $this->_log('CREATE_ORDER', 'Order lingkungan baru ID=' . $newId . ' (' . $payload['order_number'] . ').');
        $msg = 'Order lingkungan berhasil dibuat. No: ' . $payload['order_number'];
        if ($tagihan['status'] === 'created') $msg .= ' | Tagihan ' . $tagihan['reg'] . ' masuk Antrian Kasir.';
        return $this->_json(['status' => 'success', 'message' => $msg, 'id' => $newId]);
    }

    public function delete_order($id) {
        $this->rbac_lib->require_access('kesling', 'delete');
        $id = (int)$id;
        $order = $this->kesling_model->get_order($id);
        if (!$order) return $this->_error('Order tidak ditemukan.', 404);
        if (!empty($order['pendaftaran_id'])) {
            if ($order['tagihan_status'] !== 'UNPAID') {
                return $this->_error('Order tidak dapat dihapus karena tagihan ' . $order['tagihan_reg'] . ' sudah diproses kasir (' . $order['tagihan_status'] . ').', 409);
            }
            $this->load->model('pendaftaran_model');
            $this->pendaftaran_model->batal_pendaftaran($order['pendaftaran_id'], 'Order lingkungan ' . $order['order_number'] . ' dihapus');
        }
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
        if ($order['status'] === 'completed') {
            return $this->_error('LHU sudah final (seluruh verifikasi lengkap) dan terkunci. Hasil tidak dapat diubah.', 409);
        }
        if ($order['status'] === 'cancelled') return $this->_error('Order sudah dibatalkan.', 409);
        if (!empty($order['pendaftaran_id']) && $order['tagihan_status'] === 'UNPAID') {
            return $this->_error('Tagihan ' . $order['tagihan_reg'] . ' belum dibayar/dijamin di kasir. Hasil belum dapat diinput.', 409);
        }

        $results = $this->input->post('results', TRUE); // [param_id => ['value'=>..,'method'=>..,'notes'=>..]]
        if (!is_array($results)) $results = [];

        $newStatus = $this->input->post('status', TRUE);
        if (!in_array($newStatus, ['testing', 'validasi'], true)) $newStatus = 'testing';

        $this->load->model('approval_model');
        $msgExtra = '';
        // Hasil direvisi saat verifikasi berjalan -> tanda tangan dibatalkan
        if ($this->approval_model->env_count_approved($id) > 0) {
            $this->approval_model->env_reset($id);
            $msgExtra = ' Tanda tangan verifikasi sebelumnya dibatalkan karena hasil direvisi.';
            if ($newStatus !== 'validasi') $newStatus = 'testing';
        }

        $this->kesling_model->save_results($id, $results);

        if ($newStatus === 'validasi') {
            $assembled = $this->kesling_model->assemble_for_report($id);
            $kosong = 0;
            if ($assembled) {
                foreach ($assembled['grouped'] as $rows) {
                    foreach ($rows as $r) {
                        if ($r['result_value'] === null || $r['result_value'] === '') $kosong++;
                    }
                }
            }
            if (!$assembled || $assembled['summary']['total'] === 0) {
                return $this->_error('Order belum punya parameter/baku mutu untuk dievaluasi. Pilih standar acuan terlebih dahulu.', 409);
            }
            if ($kosong > 0) {
                $this->kesling_model->update_order($id, ['status' => 'testing']);
                return $this->_error('Masih ada ' . $kosong . ' parameter tanpa hasil. Lengkapi dulu sebelum dikirim ke verifikasi. (Hasil yang sudah diisi tersimpan.)', 409);
            }
        }
        $this->kesling_model->update_order($id, ['status' => $newStatus]);

        $this->_log('SAVE_HASIL', 'Hasil uji order lingkungan ID=' . $id . ' disimpan (status ' . $newStatus . ').');
        $msg = $newStatus === 'validasi'
            ? 'Hasil lengkap dan dikirim ke Verifikasi Hasil Lingkungan (berjenjang).'
            : 'Hasil uji berhasil disimpan.';
        return $this->_json(['status' => 'success', 'message' => $msg . $msgExtra]);
    }

    // =====================================================================
    // VERIFIKASI BERJENJANG HASIL LINGKUNGAN
    // =====================================================================

    public function verifikasi() {
        $this->rbac_lib->require_access('kesling/verifikasi', 'view');
        $data = $this->_base_data('Verifikasi Hasil Lingkungan', 'kesling/verifikasi');
        $this->_render('kesling/verifikasi', $data);
    }

    public function verifikasi_fetch() {
        $this->rbac_lib->require_access('kesling/verifikasi', 'view');
        $this->load->model('approval_model');
        $filter = $this->input->get('filter', TRUE) ?: 'validasi';
        $this->db->select('o.id, o.order_number, o.customer_name, o.status, o.received_at, st.name AS sample_type_name')
            ->from('env_orders o')->join('env_sample_types st', 'st.id = o.sample_type_id', 'left');
        if ($filter === 'completed') $this->db->where('o.status', 'completed');
        elseif ($filter === 'all') $this->db->where_in('o.status', ['validasi', 'completed']);
        else $this->db->where('o.status', 'validasi');
        $rows = $this->db->order_by('o.id', 'DESC')->limit(300)->get()->result_array();
        foreach ($rows as &$r) {
            $r['steps'] = $this->approval_model->env_get($r['id']);
            foreach ($r['steps'] as &$st) { unset($st['tanda_tangan']); $st['punya_ttd'] = true; }
            unset($st);
            $r['signed'] = count(array_filter($r['steps'], function ($x) { return $x['status'] === 'approved'; }));
        }
        return $this->_json(['status' => 'success', 'data' => $rows, 'can_edit' => $this->rbac_lib->has_access('kesling/verifikasi', 'edit')]);
    }

    public function approve_step() {
        $this->rbac_lib->require_access('kesling/verifikasi', 'edit');
        $this->load->model('approval_model');
        $user = $this->rbac_lib->user();
        $res = $this->approval_model->env_approve_step(
            (int)$this->input->post('order_id', TRUE),
            (int)$this->input->post('urutan', TRUE),
            ['catatan' => trim((string)$this->input->post('catatan', TRUE))],
            $user['id'] ?? null,
            $user['full_name'] ?? 'Petugas'
        );
        $this->_log('APPROVE_STEP', $res['message']);
        return $this->_json($res, $res['status'] ? 200 : 409);
    }

    public function tolak() {
        $this->rbac_lib->require_access('kesling/verifikasi', 'edit');
        $id = (int)$this->input->post('order_id', TRUE);
        $alasan = trim((string)$this->input->post('alasan', TRUE));
        $order = $this->kesling_model->get_order($id);
        if (!$order) return $this->_error('Order tidak ditemukan.', 404);
        if ($order['status'] !== 'validasi') return $this->_error('Hanya order berstatus Validasi yang dapat dikembalikan.', 409);
        if ($alasan === '') return $this->_error('Alasan pengembalian wajib diisi.', 400);
        $this->load->model('approval_model');
        $this->approval_model->env_reset($id);
        $this->kesling_model->update_order($id, [
            'status' => 'testing',
            'notes' => trim(($order['notes'] ? $order['notes'] . "\n" : '') . '[Dikembalikan verifikator ' . date('d/m/Y H:i') . '] ' . $alasan),
        ]);
        $this->_log('TOLAK', 'Order ' . $order['order_number'] . ' dikembalikan: ' . $alasan);
        return $this->_json(['status' => 'success', 'message' => 'Order dikembalikan ke analis untuk revisi.']);
    }

    // =====================================================================
    // CETAK LHU & BARCODE SAMPEL
    // =====================================================================

    public function cetak_lhu($id) {
        $this->rbac_lib->require_access('kesling', 'view');
        $id = (int)$id;

        $assembled = $this->kesling_model->assemble_for_report($id);
        if (!$assembled) { show_404(); return; }

        $order = $assembled['order'];
        $this->load->model('approval_model');
        $kab = $this->approval_model->env_check_keabsahan($id);
        if ($order['status'] !== 'completed' || !$kab['is_sah']) {
            $this->output->set_status_header(403);
            $data['order'] = $order;
            $data['kab'] = $kab;
            $data['approvals'] = $this->approval_model->env_get($id);
            $this->load->view('kesling/lhu_belum_sah', $data);
            return;
        }
        $data['approvals'] = $this->approval_model->env_get($id);
        $token = $this->kesling_model->verification_token($order['id'], $order['order_number']);
        $data['assembled'] = $assembled;
        $data['profile'] = app_profile();
        $data['verify_url'] = base_url('verifikasi/kesling/' . (int)$order['id'] . '/' . $token);
        $this->load->view('kesling/cetak_lhu', $data);
    }

    public function cetak_barcode($id) {
        $this->rbac_lib->require_access('kesling', 'view');
        $id = (int)$id;

        $order = $this->kesling_model->get_order($id);
        if (!$order) { show_404(); return; }

        $data['order'] = $order;
        $data['profile'] = app_profile();
        $this->load->view('kesling/cetak_barcode', $data);
    }

    // =====================================================================
    // MASTER BAKU MUTU (Matriks + CRUD Standar, Parameter, & Ambang Batas)
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

    public function get_standard($id) {
        $this->rbac_lib->require_access('kesling/baku_mutu', 'view');
        $row = $this->kesling_model->get_standard((int)$id);
        if (!$row) return $this->_error('Standar tidak ditemukan.', 404);
        return $this->_json(['status' => 'success', 'data' => $row]);
    }

    public function save_standard() {
        $id = (int)$this->input->post('id', TRUE);
        $this->rbac_lib->require_access('kesling/baku_mutu', $id ? 'edit' : 'create');

        $this->form_validation->set_rules('sample_type_id', 'Tipe Sampel', 'required|integer');
        $this->form_validation->set_rules('code', 'Kode Standar', 'required|trim|max_length[40]');
        $this->form_validation->set_rules('name', 'Nama Standar', 'required|trim|max_length[180]');
        if ($this->form_validation->run() === FALSE) {
            return $this->_error(strip_tags(validation_errors()) ?: 'Data standar tidak valid.');
        }

        $code = strtoupper(preg_replace('/[^A-Za-z0-9_\-]/', '_', trim($this->input->post('code', TRUE))));
        $dup = $this->db->where('code', $code)->where('id !=', $id)->get('env_standards')->row_array();
        if ($dup) return $this->_error('Kode standar "' . $code . '" sudah digunakan.');

        $payload = [
            'sample_type_id' => (int)$this->input->post('sample_type_id', TRUE),
            'code'           => $code,
            'name'           => trim($this->input->post('name', TRUE)),
            'regulation_ref' => trim($this->input->post('regulation_ref', TRUE)) ?: NULL,
            'description'    => trim($this->input->post('description', TRUE)) ?: NULL,
            'sort_order'     => (int)($this->input->post('sort_order', TRUE) ?: 1),
            'is_active'      => $this->input->post('is_active') !== NULL ? (int)$this->input->post('is_active', TRUE) : 1,
        ];

        $savedId = $this->kesling_model->save_standard($payload, $id);
        $this->_log('SAVE_STANDARD', 'Standar baku mutu ID=' . $savedId . ' (' . $code . ') disimpan.');
        return $this->_json(['status' => 'success', 'message' => 'Standar baku mutu berhasil disimpan.', 'id' => $savedId]);
    }

    public function delete_standard($id) {
        $this->rbac_lib->require_access('kesling/baku_mutu', 'delete');
        $id = (int)$id;
        if (!$this->kesling_model->get_standard($id)) return $this->_error('Standar tidak ditemukan.', 404);
        $this->kesling_model->delete_standard($id);
        $this->_log('DELETE_STANDARD', 'Standar baku mutu ID=' . $id . ' dihapus.');
        return $this->_json(['status' => 'success', 'message' => 'Standar baku mutu berhasil dihapus.']);
    }

    public function get_parameter($id) {
        $this->rbac_lib->require_access('kesling/baku_mutu', 'view');
        $row = $this->kesling_model->get_parameter((int)$id);
        if (!$row) return $this->_error('Parameter tidak ditemukan.', 404);
        return $this->_json(['status' => 'success', 'data' => $row]);
    }

    public function save_parameter() {
        $id = (int)$this->input->post('id', TRUE);
        $this->rbac_lib->require_access('kesling/baku_mutu', $id ? 'edit' : 'create');

        $this->form_validation->set_rules('sample_type_id', 'Tipe Sampel', 'required|integer');
        $this->form_validation->set_rules('param_code', 'Kode Parameter', 'required|trim|max_length[40]');
        $this->form_validation->set_rules('param_name', 'Nama Parameter', 'required|trim|max_length[150]');
        if ($this->form_validation->run() === FALSE) {
            return $this->_error(strip_tags(validation_errors()) ?: 'Data parameter tidak valid.');
        }

        $code = strtoupper(preg_replace('/[^A-Za-z0-9_\-]/', '-', trim($this->input->post('param_code', TRUE))));
        $dup = $this->db->where('param_code', $code)->where('id !=', $id)->get('env_parameters')->row_array();
        if ($dup) return $this->_error('Kode parameter "' . $code . '" sudah digunakan.');

        $subCat = $this->input->post('sub_category', TRUE);
        if (!in_array($subCat, ['fisika', 'kimia', 'mikrobiologi', 'lainnya'], TRUE)) $subCat = 'kimia';
        $dataType = $this->input->post('data_type', TRUE) === 'text' ? 'text' : 'numeric';

        $payload = [
            'sample_type_id' => (int)$this->input->post('sample_type_id', TRUE),
            'sub_category'   => $subCat,
            'param_code'     => $code,
            'param_name'     => trim($this->input->post('param_name', TRUE)),
            'param_name_en'  => trim($this->input->post('param_name_en', TRUE)) ?: NULL,
            'unit'           => trim($this->input->post('unit', TRUE)) ?: '-',
            'method_default' => trim($this->input->post('method_default', TRUE)) ?: NULL,
            'data_type'      => $dataType,
            'is_accredited'  => $this->input->post('is_accredited') ? 1 : 0,
            'sort_order'     => (int)($this->input->post('sort_order', TRUE) ?: 10),
            'is_active'      => $this->input->post('is_active') !== NULL ? (int)$this->input->post('is_active', TRUE) : 1,
        ];

        $savedId = $this->kesling_model->save_parameter($payload, $id);
        $this->_log('SAVE_PARAMETER', 'Parameter lingkungan ID=' . $savedId . ' (' . $code . ') disimpan.');
        return $this->_json(['status' => 'success', 'message' => 'Parameter uji berhasil disimpan.', 'id' => $savedId]);
    }

    public function delete_parameter($id) {
        $this->rbac_lib->require_access('kesling/baku_mutu', 'delete');
        $id = (int)$id;
        if (!$this->kesling_model->get_parameter($id)) return $this->_error('Parameter tidak ditemukan.', 404);
        $this->kesling_model->delete_parameter($id);
        $this->_log('DELETE_PARAMETER', 'Parameter lingkungan ID=' . $id . ' dihapus.');
        return $this->_json(['status' => 'success', 'message' => 'Parameter uji berhasil dihapus.']);
    }

    public function save_threshold() {
        $this->rbac_lib->require_access('kesling/baku_mutu', 'edit');

        $standardId  = (int)$this->input->post('standard_id', TRUE);
        $parameterId = (int)$this->input->post('parameter_id', TRUE);
        if (!$standardId || !$parameterId) return $this->_error('Standar dan parameter wajib dipilih.');

        $display = trim((string)$this->input->post('threshold_display', TRUE));
        if ($display === '') {
            // Bila dikosongkan, hapus nilai rujukan pada standar tersebut
            $this->kesling_model->delete_threshold($standardId, $parameterId);
            $this->_log('DELETE_THRESHOLD', sprintf('Ambang std=%d param=%d dihapus.', $standardId, $parameterId));
            return $this->_json(['status' => 'success', 'message' => 'Nilai rujukan pada standar ini telah dikosongkan.']);
        }

        $op = $this->input->post('operator', TRUE);
        if (!in_array($op, ['max', 'min', 'range', 'equal', 'text', 'none'], TRUE)) $op = 'max';

        $minRaw = trim((string)$this->input->post('min_val', TRUE));
        $maxRaw = trim((string)$this->input->post('max_val', TRUE));

        $payload = [
            'threshold_display' => $display,
            'operator'          => $op,
            'min_val'           => $minRaw !== '' ? (float)str_replace(',', '.', $minRaw) : NULL,
            'max_val'           => $maxRaw !== '' ? (float)str_replace(',', '.', $maxRaw) : NULL,
            'text_expected'     => trim((string)$this->input->post('text_expected', TRUE)) ?: NULL,
            'unit_override'     => trim((string)$this->input->post('unit_override', TRUE)) ?: NULL,
            'method_override'   => trim((string)$this->input->post('method_override', TRUE)) ?: NULL,
        ];

        $this->kesling_model->save_threshold($standardId, $parameterId, $payload);
        $this->_log('SAVE_THRESHOLD', sprintf('Ambang std=%d param=%d disimpan (%s).', $standardId, $parameterId, $display));
        return $this->_json(['status' => 'success', 'message' => 'Nilai rujukan berhasil disimpan.']);
    }
}
