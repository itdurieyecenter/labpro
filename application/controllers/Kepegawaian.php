<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modul Kepegawaian - Manajemen Cuti ASN
 *
 * Input dilakukan operator Tata Usaha atas nama pegawai, lalu diteruskan ke
 * alur persetujuan berjenjang yang dikonfigurasi pada menu Setting Approval.
 */
class Kepegawaian extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->rbac_lib->require_login();
        $this->load->model('pegawai_model');
        $this->load->model('cuti_model');
        $this->load->model('kepegawaian_model');
        $this->load->library('form_validation');
    }

    private function _log($action, $message, $level = 'info') {
        log_message($level, sprintf("[CUTI-CTRL] USER=%s | ACTION=%s | MSG=%s", $this->rbac_lib->user('username'), $action, $message));
    }

    private function _json($payload, $statusCode = 200) {
        if ($statusCode !== 200) {
            $this->output->set_status_header($statusCode);
        }
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
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

    public function index() {
        redirect('kepegawaian/pengajuan_cuti');
    }

    // =====================================================================
    // SETTING HIRARKI APPROVAL
    // =====================================================================

    public function approval_setting() {
        $this->rbac_lib->require_access('kepegawaian/approval_setting', 'view');

        $data = $this->_base_data('Setting Alur Approval Cuti ASN', 'kepegawaian/approval_setting');
        $data['rules'] = $this->cuti_model->get_rules();
        $data['pegawai_options'] = $this->cuti_model->get_pegawai_options();
        $data['next_urutan'] = $this->cuti_model->get_next_urutan();

        $this->_render('kepegawaian/approval_setting', $data);
    }

    public function rule_fetch() {
        $this->rbac_lib->require_access('kepegawaian/approval_setting', 'view');
        return $this->_json(['status' => 'success', 'data' => $this->cuti_model->get_rules()]);
    }

    public function rule_get($id) {
        $this->rbac_lib->require_access('kepegawaian/approval_setting', 'view');
        $rule = $this->cuti_model->get_rule_by_id((int)$id);

        if (!$rule) {
            return $this->_error('Aturan approval tidak ditemukan.', 404);
        }
        return $this->_json(['status' => 'success', 'data' => $rule]);
    }

    public function rule_save() {
        $id = $this->input->post('id');
        $isEdit = !empty($id);

        if (!$this->rbac_lib->has_access('kepegawaian/approval_setting', $isEdit ? 'edit' : 'create')) {
            return $this->_error('Anda tidak memiliki hak akses untuk mengubah hirarki approval cuti.', 403);
        }

        $this->form_validation->set_rules('pegawai_id', 'Pegawai Pemberi Persetujuan', 'required|integer');
        $this->form_validation->set_rules('jabatan_approval', 'Peran dalam Alur Approval', 'required|trim|max_length[150]');

        if ($this->form_validation->run() === FALSE) {
            return $this->_error(validation_errors(' ', ' '));
        }

        $urutan = (int)$this->input->post('urutan');
        if ($urutan <= 0) {
            $urutan = $this->cuti_model->get_next_urutan();
        }

        $savedId = $this->cuti_model->save_rule([
            'urutan' => $urutan,
            'pegawai_id' => (int)$this->input->post('pegawai_id'),
            'jabatan_approval' => $this->input->post('jabatan_approval', TRUE),
            'keterangan' => $this->input->post('keterangan', TRUE) ?: null,
            'is_required' => (int)$this->input->post('is_required'),
            'is_active' => $this->input->post('is_active') === null ? 1 : (int)$this->input->post('is_active'),
        ], $isEdit ? (int)$id : null);

        $this->_log($isEdit ? 'RULE_UPDATE' : 'RULE_CREATE', "ID={$savedId}");

        return $this->_json([
            'status' => 'success',
            'message' => 'Tahap approval cuti berhasil ' . ($isEdit ? 'diperbarui' : 'ditambahkan') . '.',
            'data' => $this->cuti_model->get_rule_by_id($savedId),
        ]);
    }

    public function rule_delete($id) {
        if (!$this->rbac_lib->has_access('kepegawaian/approval_setting', 'delete')) {
            return $this->_error('Anda tidak memiliki hak akses untuk menghapus tahap approval.', 403);
        }

        $rule = $this->cuti_model->get_rule_by_id((int)$id);
        if (!$rule) {
            return $this->_error('Aturan approval tidak ditemukan.', 404);
        }

        $this->cuti_model->delete_rule((int)$id);
        $this->_log('RULE_DELETE', "ID={$id} | Jabatan={$rule['jabatan_approval']}");

        return $this->_json([
            'status' => 'success',
            'message' => 'Tahap approval "' . $rule['jabatan_approval'] . '" dihapus.',
        ]);
    }

    public function rule_move() {
        if (!$this->rbac_lib->has_access('kepegawaian/approval_setting', 'edit')) {
            return $this->_error('Anda tidak memiliki hak akses untuk mengubah urutan approval.', 403);
        }

        $id = (int)$this->input->post('id');
        $direction = $this->input->post('direction') === 'down' ? 'down' : 'up';

        if (!$this->cuti_model->move_order($id, $direction)) {
            return $this->_error('Tahap ini sudah berada di posisi ' . ($direction === 'up' ? 'teratas' : 'terbawah') . '.');
        }

        return $this->_json([
            'status' => 'success',
            'message' => 'Urutan tahap approval diperbarui.',
            'data' => $this->cuti_model->get_rules(),
        ]);
    }

    // =====================================================================
    // PENGAJUAN CUTI
    // =====================================================================

    public function pengajuan_cuti() {
        $this->rbac_lib->require_access('kepegawaian/pengajuan_cuti', 'view');

        $data = $this->_base_data('Pengajuan Permohonan Cuti ASN', 'kepegawaian/pengajuan_cuti');
        $data['pegawai_options'] = $this->cuti_model->get_pegawai_options();
        $data['jenis_cuti'] = $this->cuti_model->get_jenis(true);
        $data['config_cuti'] = $this->cuti_model->get_config();
        $data['summary'] = $this->cuti_model->count_summary();
        $data['tahun_list'] = $this->cuti_model->get_tahun_tersedia();
        $data['jumlah_tahap_approval'] = count($this->cuti_model->get_rules(true));

        $this->_render('kepegawaian/pengajuan_cuti', $data);
    }

    public function fetch() {
        $this->rbac_lib->require_access('kepegawaian/pengajuan_cuti', 'view');

        $params = [
            'draw' => $this->input->get('draw', TRUE),
            'start' => $this->input->get('start', TRUE),
            'length' => $this->input->get('length', TRUE),
            'search' => ['value' => $this->input->get('search[value]', TRUE)],
            'status' => $this->input->get('status', TRUE) ?: 'all',
            'tahun' => $this->input->get('tahun', TRUE) ?: 'all',
            'jenis_cuti_id' => $this->input->get('jenis_cuti_id', TRUE) ?: 'all',
            'pegawai_id' => $this->input->get('pegawai_id', TRUE) ?: 'all',
            'order' => [],
        ];

        $orderIndex = 0;
        while ($this->input->get('order[' . $orderIndex . '][column]', TRUE) !== NULL) {
            $params['order'][] = [
                'column' => $this->input->get('order[' . $orderIndex . '][column]', TRUE),
                'dir' => $this->input->get('order[' . $orderIndex . '][dir]', TRUE),
            ];
            $orderIndex++;
        }

        $result = $this->cuti_model->get_datatable($params);

        return $this->_json([
            'status' => 'success',
            'draw' => $result['draw'],
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
            'data' => $result['data'],
        ]);
    }

    public function get($id) {
        $this->rbac_lib->require_access('kepegawaian/pengajuan_cuti', 'view');

        $row = $this->cuti_model->get_by_id((int)$id);
        if (!$row) {
            return $this->_error('Pengajuan cuti tidak ditemukan.', 404);
        }

        $row['log'] = $this->cuti_model->get_log((int)$id);
        return $this->_json(['status' => 'success', 'data' => $row]);
    }

    /**
     * Hitung hari kerja dan sisa saldo secara langsung, dipakai form untuk
     * menampilkan dampak pengajuan sebelum disimpan.
     */
    public function hitung_hari() {
        $this->rbac_lib->require_access('kepegawaian/pengajuan_cuti', 'view');

        $mulai = $this->input->get('tanggal_mulai', TRUE);
        $selesai = $this->input->get('tanggal_selesai', TRUE);
        $pegawaiId = (int)$this->input->get('pegawai_id', TRUE);
        $jenisId = (int)$this->input->get('jenis_cuti_id', TRUE);
        $tahun = (int)($this->input->get('tahun_hak', TRUE) ?: date('Y'));

        if (empty($mulai) || empty($selesai)) {
            return $this->_error('Tanggal mulai dan selesai wajib diisi.');
        }

        $jumlah = $this->cuti_model->hitung_hari($mulai, $selesai);
        $response = [
            'jumlah_hari' => $jumlah,
            'hari_libur' => $this->cuti_model->get_hari_libur($mulai, $selesai),
            'mengurangi_saldo' => false,
            'alokasi' => null,
            'saldo' => null,
            'peringatan' => null,
        ];

        if ($pegawaiId > 0) {
            $response['saldo'] = $this->cuti_model->get_saldo($pegawaiId, $tahun);

            $jenis = $jenisId > 0 ? $this->cuti_model->get_jenis_by_id($jenisId) : null;
            if ($jenis && (int)$jenis['mengurangi_saldo'] === 1) {
                $response['mengurangi_saldo'] = true;
                $cek = $this->cuti_model->alokasi_pemakaian($pegawaiId, $tahun, $jumlah);
                $response['alokasi'] = $cek['alokasi'];
                if (!$cek['status']) {
                    $response['peringatan'] = $cek['message'];
                }
            }
        }

        return $this->_json(['status' => 'success', 'data' => $response]);
    }

    public function saldo() {
        $this->rbac_lib->require_access('kepegawaian/pengajuan_cuti', 'view');

        $pegawaiId = (int)$this->input->get('pegawai_id', TRUE);
        if ($pegawaiId <= 0) {
            return $this->_error('Pegawai belum dipilih.');
        }

        $tahun = (int)($this->input->get('tahun', TRUE) ?: date('Y'));

        return $this->_json([
            'status' => 'success',
            'data' => $this->cuti_model->get_saldo($pegawaiId, $tahun),
        ]);
    }

    public function save() {
        $id = $this->input->post('id');
        $isEdit = !empty($id);

        if (!$this->rbac_lib->has_access('kepegawaian/pengajuan_cuti', $isEdit ? 'edit' : 'create')) {
            return $this->_error('Anda tidak memiliki hak akses untuk ' . ($isEdit ? 'mengubah' : 'membuat') . ' pengajuan cuti.', 403);
        }

        $this->form_validation->set_rules('pegawai_id', 'Pegawai', 'required|integer');
        $this->form_validation->set_rules('jenis_cuti_id', 'Jenis Cuti', 'required|integer');
        $this->form_validation->set_rules('tanggal_mulai', 'Tanggal Mulai', 'required');
        $this->form_validation->set_rules('tanggal_selesai', 'Tanggal Selesai', 'required');
        $this->form_validation->set_rules('alasan', 'Alasan Cuti', 'required|trim|max_length[500]');

        if ($this->form_validation->run() === FALSE) {
            return $this->_error(validation_errors(' ', ' '));
        }

        $payload = [
            'pegawai_id' => (int)$this->input->post('pegawai_id'),
            'jenis_cuti_id' => (int)$this->input->post('jenis_cuti_id'),
            'tahun_hak' => (int)($this->input->post('tahun_hak') ?: date('Y', strtotime($this->input->post('tanggal_mulai')))),
            'tanggal_mulai' => $this->input->post('tanggal_mulai', TRUE),
            'tanggal_selesai' => $this->input->post('tanggal_selesai', TRUE),
            'alasan' => $this->input->post('alasan', TRUE),
            'alamat_selama_cuti' => $this->input->post('alamat_selama_cuti', TRUE) ?: null,
            'telepon_selama_cuti' => $this->input->post('telepon_selama_cuti', TRUE) ?: null,
            'user_id' => $this->rbac_lib->user('id'),
            'user_nama' => $this->rbac_lib->user('full_name'),
        ];

        // Lampiran opsional; berkas lama dipertahankan bila tidak diunggah ulang.
        if (!empty($_FILES['lampiran']['name'])) {
            $upload = $this->_upload_lampiran();
            if ($upload['status'] === false) {
                return $this->_error($upload['message']);
            }
            $payload['lampiran'] = $upload['path'];
        }

        $result = $this->cuti_model->save_pengajuan($payload, $isEdit ? (int)$id : null);

        if (!$result['status']) {
            return $this->_error($result['message']);
        }

        $this->_log($isEdit ? 'UPDATE' : 'CREATE', "ID={$result['id']}");

        return $this->_json([
            'status' => 'success',
            'message' => 'Pengajuan cuti berhasil disimpan sebagai draft. Klik "Kirim ke Approval" untuk meneruskan.',
            'data' => $result['data'],
        ]);
    }

    private function _upload_lampiran() {
        $uploadDir = FCPATH . 'uploads/cuti/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $config['upload_path'] = $uploadDir;
        $config['allowed_types'] = 'pdf|jpg|jpeg|png';
        $config['max_size'] = 4096; // 4MB
        $config['file_ext_tolower'] = TRUE;
        $config['file_name'] = 'cuti_' . time();
        $config['overwrite'] = FALSE;

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('lampiran')) {
            return ['status' => false, 'message' => strip_tags($this->upload->display_errors())];
        }

        $uploadData = $this->upload->data();
        return ['status' => true, 'path' => 'uploads/cuti/' . $uploadData['file_name']];
    }

    public function ajukan($id) {
        if (!$this->rbac_lib->has_access('kepegawaian/pengajuan_cuti', 'edit')) {
            return $this->_error('Anda tidak memiliki hak akses untuk mengirim pengajuan cuti.', 403);
        }

        $result = $this->cuti_model->ajukan(
            (int)$id,
            $this->rbac_lib->user('id'),
            $this->rbac_lib->user('full_name')
        );

        if (!$result['status']) {
            return $this->_error($result['message']);
        }

        $this->_log('SUBMIT', "ID={$id}");
        return $this->_json(['status' => 'success', 'message' => $result['message']]);
    }

    public function batalkan($id) {
        if (!$this->rbac_lib->has_access('kepegawaian/pengajuan_cuti', 'edit')) {
            return $this->_error('Anda tidak memiliki hak akses untuk membatalkan pengajuan cuti.', 403);
        }

        $result = $this->cuti_model->batalkan(
            (int)$id,
            $this->input->post('alasan', TRUE),
            $this->rbac_lib->user('id'),
            $this->rbac_lib->user('full_name')
        );

        if (!$result['status']) {
            return $this->_error($result['message']);
        }

        $this->_log('CANCEL', "ID={$id}");
        return $this->_json(['status' => 'success', 'message' => $result['message']]);
    }

    public function hapus($id) {
        if (!$this->rbac_lib->has_access('kepegawaian/pengajuan_cuti', 'delete')) {
            return $this->_error('Anda tidak memiliki hak akses untuk menghapus pengajuan cuti.', 403);
        }

        $result = $this->cuti_model->hapus_pengajuan((int)$id);
        if (!$result['status']) {
            return $this->_error($result['message']);
        }

        $this->_log('DELETE', "ID={$id}");
        return $this->_json(['status' => 'success', 'message' => $result['message']]);
    }

    // =====================================================================
    // APPROVAL CUTI
    // =====================================================================

    public function approval_cuti() {
        $this->rbac_lib->require_access('kepegawaian/approval_cuti', 'view');

        $data = $this->_base_data('Verifikasi & Persetujuan Cuti ASN', 'kepegawaian/approval_cuti');
        $data['antrean'] = $this->cuti_model->get_antrean_approval();
        $data['pegawai_options'] = $this->cuti_model->get_pegawai_options();
        $data['summary'] = $this->cuti_model->count_summary();
        $data['rules'] = $this->cuti_model->get_rules(true);

        $this->_render('kepegawaian/approval_cuti', $data);
    }

    public function antrean() {
        $this->rbac_lib->require_access('kepegawaian/approval_cuti', 'view');

        $rows = $this->cuti_model->get_antrean_approval([
            'approver_id' => $this->input->get('approver_id', TRUE) ?: 'all',
            'search' => $this->input->get('search', TRUE),
        ]);

        return $this->_json(['status' => 'success', 'data' => $rows]);
    }

    public function proses_approval() {
        if (!$this->rbac_lib->has_access('kepegawaian/approval_cuti', 'edit')) {
            return $this->_error('Anda tidak memiliki hak akses untuk memproses persetujuan cuti.', 403);
        }

        $cutiId = (int)$this->input->post('cuti_id');
        $urutan = (int)$this->input->post('urutan');
        $keputusan = $this->input->post('keputusan') === 'reject' ? 'reject' : 'approve';
        $catatan = $this->input->post('catatan', TRUE);

        if ($keputusan === 'reject' && empty($catatan)) {
            return $this->_error('Alasan penolakan wajib diisi.');
        }

        $result = $this->cuti_model->proses_approval(
            $cutiId,
            $urutan,
            $keputusan,
            $catatan,
            $this->rbac_lib->user('id'),
            $this->rbac_lib->user('full_name')
        );

        if (!$result['status']) {
            return $this->_error($result['message']);
        }

        $this->_log('APPROVAL', "ID={$cutiId} | STEP={$urutan} | {$keputusan}");

        return $this->_json([
            'status' => 'success',
            'message' => $result['message'],
            'final' => $result['final'] ?? null,
        ]);
    }

    // =====================================================================
    // LAPORAN & CETAK
    // =====================================================================

    public function laporan_cuti() {
        $this->rbac_lib->require_access('kepegawaian/laporan_cuti', 'view');

        $tahun = (int)($this->input->get('tahun', TRUE) ?: date('Y'));

        $data = $this->_base_data('Laporan & Rekapitulasi Cuti Pegawai', 'kepegawaian/laporan_cuti');
        $data['tahun'] = $tahun;
        $data['tahun_list'] = $this->cuti_model->get_tahun_tersedia();
        $data['rekap'] = $this->cuti_model->get_rekap_saldo($tahun);
        $data['summary'] = $this->cuti_model->count_summary($tahun);
        $data['seksi_ref'] = $this->pegawai_model->get_seksi_reference();
        $data['config_cuti'] = $this->cuti_model->get_config();

        $this->_render('kepegawaian/laporan_cuti', $data);
    }

    public function rekap() {
        $this->rbac_lib->require_access('kepegawaian/laporan_cuti', 'view');

        $rows = $this->cuti_model->get_rekap_saldo(
            (int)($this->input->get('tahun', TRUE) ?: date('Y')),
            [
                'seksi_ruangan' => $this->input->get('seksi_ruangan', TRUE) ?: 'all',
                'status_pegawai' => $this->input->get('status_pegawai', TRUE) ?: 'all',
                'search' => $this->input->get('search', TRUE),
            ]
        );

        return $this->_json(['status' => 'success', 'data' => $rows]);
    }

    public function simpan_saldo() {
        if (!$this->rbac_lib->has_access('kepegawaian/laporan_cuti', 'edit')) {
            return $this->_error('Anda tidak memiliki hak akses untuk menyesuaikan saldo cuti.', 403);
        }

        $pegawaiId = (int)$this->input->post('pegawai_id');
        $tahun = (int)($this->input->post('tahun') ?: date('Y'));

        if ($pegawaiId <= 0) {
            return $this->_error('Pegawai tidak valid.');
        }

        $saldo = $this->cuti_model->save_saldo($pegawaiId, $tahun, [
            'hak_tahun_berjalan' => (int)$this->input->post('hak_tahun_berjalan'),
            'saldo_n1' => (int)$this->input->post('saldo_n1'),
            'saldo_n2' => (int)$this->input->post('saldo_n2'),
            'penyesuaian' => (int)$this->input->post('penyesuaian'),
            'catatan' => $this->input->post('catatan', TRUE) ?: null,
        ]);

        $this->_log('SALDO_UPDATE', "PEGAWAI={$pegawaiId} | TAHUN={$tahun}");

        return $this->_json([
            'status' => 'success',
            'message' => 'Saldo hak cuti berhasil disesuaikan.',
            'data' => $saldo,
        ]);
    }

    // =====================================================================
    // KREDENSIAL STR & SIP
    // =====================================================================

    public function kredensial() {
        $this->rbac_lib->require_access('kepegawaian/kredensial', 'view');

        $data = $this->_base_data('Pemantauan Kredensial STR & SIP', 'kepegawaian/kredensial');
        $data['kredensial'] = $this->kepegawaian_model->get_kredensial();
        $data['ringkasan'] = $this->kepegawaian_model->ringkasan_kredensial();
        $data['config_kredensial'] = $this->kepegawaian_model->get_config();
        $data['seksi_ref'] = $this->pegawai_model->get_seksi_reference();

        $this->_render('kepegawaian/kredensial', $data);
    }

    public function fetch_kredensial() {
        $this->rbac_lib->require_access('kepegawaian/kredensial', 'view');

        $rows = $this->kepegawaian_model->get_kredensial([
            'seksi_ruangan' => $this->input->get('seksi_ruangan', TRUE) ?: 'all',
            'status' => $this->input->get('status', TRUE) ?: 'all',
            'search' => $this->input->get('search', TRUE),
        ]);

        return $this->_json(['status' => 'success', 'data' => $rows]);
    }

    // =====================================================================
    // BEBAN KERJA PEGAWAI
    // =====================================================================

    public function beban_kerja() {
        $this->rbac_lib->require_access('kepegawaian/beban_kerja', 'view');

        $mulai = $this->input->get('mulai', TRUE) ?: date('Y-m-01');
        $selesai = $this->input->get('selesai', TRUE) ?: date('Y-m-t');

        $data = $this->_base_data('Monitoring Beban Kerja Pegawai', 'kepegawaian/beban_kerja');
        $data['mulai'] = $mulai;
        $data['selesai'] = $selesai;
        $data['beban'] = $this->kepegawaian_model->get_beban_kerja($mulai, $selesai);
        $data['seksi_ref'] = $this->pegawai_model->get_seksi_reference();

        $this->_render('kepegawaian/beban_kerja', $data);
    }

    public function fetch_beban_kerja() {
        $this->rbac_lib->require_access('kepegawaian/beban_kerja', 'view');

        $mulai = $this->input->get('mulai', TRUE) ?: date('Y-m-01');
        $selesai = $this->input->get('selesai', TRUE) ?: date('Y-m-t');

        if (strtotime($selesai) < strtotime($mulai)) {
            return $this->_error('Tanggal akhir periode lebih awal dari tanggal mulai.');
        }

        $hasil = $this->kepegawaian_model->get_beban_kerja($mulai, $selesai, [
            'seksi_ruangan' => $this->input->get('seksi_ruangan', TRUE) ?: 'all',
            'search' => $this->input->get('search', TRUE),
        ]);

        return $this->_json(['status' => 'success', 'data' => $hasil]);
    }

    // =====================================================================
    // AUDIT TRAIL
    // =====================================================================

    public function audit_trail() {
        $this->rbac_lib->require_access('kepegawaian/audit_trail', 'view');

        $data = $this->_base_data('Audit Trail Aktivitas Pengguna', 'kepegawaian/audit_trail');
        $data['ringkasan'] = $this->kepegawaian_model->ringkasan_audit();
        $data['modul_list'] = $this->kepegawaian_model->get_audit_modul();
        $data['user_list'] = $this->kepegawaian_model->get_user_options();
        $data['retensi_hari'] = (int)get_setting('audit_retensi_hari', 365);

        $this->_render('kepegawaian/audit_trail', $data);
    }

    public function fetch_audit() {
        $this->rbac_lib->require_access('kepegawaian/audit_trail', 'view');

        $params = [
            'draw' => $this->input->get('draw', TRUE),
            'start' => $this->input->get('start', TRUE),
            'length' => $this->input->get('length', TRUE),
            'search' => ['value' => $this->input->get('search[value]', TRUE)],
            'modul' => $this->input->get('modul', TRUE) ?: 'all',
            'user_id' => $this->input->get('user_id', TRUE) ?: 'all',
            'tanggal_mulai' => $this->input->get('tanggal_mulai', TRUE),
            'tanggal_selesai' => $this->input->get('tanggal_selesai', TRUE),
        ];

        $result = $this->kepegawaian_model->get_audit_datatable($params);

        return $this->_json([
            'status' => 'success',
            'draw' => $result['draw'],
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
            'data' => $result['data'],
        ]);
    }

    /**
     * Pangkas catatan audit yang sudah melewati masa retensi.
     */
    public function pangkas_audit() {
        if (!$this->rbac_lib->has_access('kepegawaian/audit_trail', 'delete')) {
            return $this->_error('Anda tidak memiliki hak akses untuk memangkas log audit.', 403);
        }

        $this->load->library('audit_lib');
        $jumlah = $this->audit_lib->pangkas_retensi();

        $this->_log('AUDIT_PRUNE', "Dihapus={$jumlah}");

        return $this->_json([
            'status' => 'success',
            'message' => $jumlah > 0
                ? $jumlah . ' catatan audit yang melewati masa retensi telah dihapus.'
                : 'Tidak ada catatan audit yang melewati masa retensi.',
        ]);
    }

    /**
     * Cetak surat izin cuti untuk pengajuan yang sudah disetujui penuh.
     */
    public function cetak_surat($id) {
        $this->rbac_lib->require_access('kepegawaian/laporan_cuti', 'view');

        $row = $this->cuti_model->get_by_id((int)$id);
        if (!$row) {
            show_error('Pengajuan cuti tidak ditemukan.', 404, 'Data Tidak Ditemukan');
            return;
        }
        if ($row['status'] !== 'disetujui') {
            show_error('Surat izin cuti hanya dapat dicetak untuk pengajuan yang telah disetujui seluruh tahap.', 403, 'Belum Disetujui');
            return;
        }

        $data['cuti'] = $row;
        $data['saldo'] = $row['saldo'];
        $data['kop'] = kop_surat_data();
        $data['title'] = 'Surat Izin Cuti - ' . $row['nomor_pengajuan'];

        $this->load->view('kepegawaian/cetak_surat_cuti', $data);
    }
}
