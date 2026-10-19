<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rekening extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->rbac_lib->require_login();
        $this->load->model('rekening_model');
    }

    private function _json($data) {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    // =========================================================================
    // 1. MAIN VIEWS & TAB ROUTING
    // =========================================================================
    public function index($tab = null) {
        if (!$tab) {
            $tab = $this->input->get('tab') ?: 'coa';
        }

        // Map tab to active menu identifier
        $tab_menu_map = [
            'coa'     => 'rekening/coa',
            'tahun'   => 'rekening/tahun',
            'bayar'   => 'rekening/akun_bayar',
            'piutang' => 'rekening/akun_piutang'
        ];

        $active_menu = $tab_menu_map[$tab] ?? 'rekening/coa';
        $this->rbac_lib->require_access($active_menu, 'view');

        $data['title']       = 'Akun Rekening & Bagan Akun Standar (COA) - LIS PRO';
        $data['active_menu'] = $active_menu;
        $data['active_tab']  = $tab;
        $data['user']        = $this->rbac_lib->user();
        $data['menu_tree']   = $this->rbac_lib->get_menu_tree();

        // RBAC Permissions
        $data['can_create']  = $this->rbac_lib->has_access($active_menu, 'create');
        $data['can_edit']    = $this->rbac_lib->has_access($active_menu, 'edit');
        $data['can_delete']  = $this->rbac_lib->has_access($active_menu, 'delete');

        // Master Data & Dropdowns
        $data['summary']        = $this->rekening_model->get_summary();
        $data['kategori_list']  = $this->rekening_model->get_all_kategori();
        $data['coas']           = $this->rekening_model->get_all_coa();
        $data['coa_all_drop']   = $this->rekening_model->get_coa_dropdown('all');
        $data['coa_kas_drop']   = $this->rekening_model->get_coa_dropdown('kas_bank');
        $data['coa_piut_drop']  = $this->rekening_model->get_coa_dropdown('piutang');
        $data['coa_pend_drop']  = $this->rekening_model->get_coa_dropdown('pendapatan');
        $data['tahun_list']     = $this->rekening_model->get_distinct_tahun();
        $data['curr_tahun']     = (int)date('Y');
        $data['rekening_tahun'] = $this->rekening_model->get_all_rekening_tahun($data['curr_tahun']);
        $data['akun_bayar']     = $this->rekening_model->get_all_akun_bayar();
        $data['akun_piutang']   = $this->rekening_model->get_all_akun_piutang();

        $this->load->view('layout/header', $data);
        $this->load->view('layout/sidebar', $data);
        $this->load->view('layout/topbar', $data);
        $this->load->view('rekening/index', $data);
        $this->load->view('layout/footer', $data);
    }

    public function coa() {
        $this->index('coa');
    }

    public function tahun() {
        $this->index('tahun');
    }

    public function akun_bayar() {
        $this->index('bayar');
    }

    public function akun_piutang() {
        $this->index('piutang');
    }

    // =========================================================================
    // 2. SUMMARY STATS AJAX
    // =========================================================================
    public function get_summary_ajax() {
        $summary = $this->rekening_model->get_summary();
        $this->_json(['status' => 'success', 'data' => $summary]);
    }

    // =========================================================================
    // 2B. MASTER KATEGORI AKUN (PSAK) AJAX ENDPOINTS
    // =========================================================================
    public function get_kategori_list_ajax() {
        $data = $this->rekening_model->get_all_kategori();
        $this->_json(['status' => 'success', 'data' => $data]);
    }

    public function get_kategori_detail_ajax($id) {
        $data = $this->rekening_model->get_kategori_by_id($id);
        if (!$data) {
            $this->_json(['status' => 'error', 'message' => 'Kategori akun tidak ditemukan.']);
            return;
        }
        $this->_json(['status' => 'success', 'data' => $data]);
    }

    public function save_kategori_ajax() {
        if ($this->input->method() !== 'post') {
            $this->_json(['status' => 'error', 'message' => 'Invalid request method']);
            return;
        }

        $id = $this->input->post('id');
        $action = empty($id) ? 'create' : 'edit';
        if (!$this->rbac_lib->has_access('rekening/coa', $action)) {
            $this->_json(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses untuk aksi ini.']);
            return;
        }

        $kode_kategori = trim($this->input->post('kode_kategori'));
        $nama_kategori = trim($this->input->post('nama_kategori'));
        $posisi_normal = $this->input->post('posisi_normal') ?: 'D';
        $deskripsi     = trim($this->input->post('deskripsi'));
        $urutan        = (int)$this->input->post('urutan') ?: 1;
        $is_active     = $this->input->post('is_active') ? 1 : 0;

        if (empty($kode_kategori) || empty($nama_kategori)) {
            $this->_json(['status' => 'error', 'message' => 'Kode kategori dan nama kategori wajib diisi.']);
            return;
        }

        // Cek duplikasi kode kategori
        $existing = $this->rekening_model->get_kategori_by_kode($kode_kategori);
        if ($existing && (empty($id) || $existing['id'] != $id)) {
            $this->_json(['status' => 'error', 'message' => "Kode Kategori '{$kode_kategori}' sudah digunakan."]);
            return;
        }

        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($nama_kategori)));

        $payload = [
            'kode_kategori' => $kode_kategori,
            'nama_kategori' => strtoupper($nama_kategori),
            'slug'          => $slug,
            'posisi_normal' => $posisi_normal,
            'deskripsi'     => $deskripsi,
            'urutan'        => $urutan,
            'is_active'     => $is_active
        ];

        if (!empty($id)) {
            $this->rekening_model->update_kategori($id, $payload);
            $msg = 'Kategori Akun berhasil diperbarui.';
        } else {
            $id = $this->rekening_model->insert_kategori($payload);
            $msg = 'Kategori Akun baru berhasil ditambahkan.';
        }

        $kategori_list = $this->rekening_model->get_all_kategori();

        $this->_json([
            'status' => 'success',
            'message' => $msg,
            'id' => $id,
            'kategori' => array_merge($payload, ['id' => $id]),
            'kategori_list' => $kategori_list
        ]);
    }

    public function delete_kategori_ajax($id) {
        if ($this->input->method() !== 'post') {
            $this->_json(['status' => 'error', 'message' => 'Invalid request method']);
            return;
        }

        if (!$this->rbac_lib->has_access('rekening/coa', 'delete')) {
            $this->_json(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses untuk menghapus kategori.']);
            return;
        }

        $result = $this->rekening_model->delete_kategori($id);
        if (is_array($result) && !$result['status']) {
            $this->_json(['status' => 'error', 'message' => $result['message']]);
            return;
        }

        $kategori_list = $this->rekening_model->get_all_kategori();
        $this->_json([
            'status' => 'success',
            'message' => 'Kategori Akun berhasil dihapus.',
            'kategori_list' => $kategori_list
        ]);
    }

    public function toggle_kategori_ajax($id) {
        if ($this->input->method() !== 'post') {
            $this->_json(['status' => 'error', 'message' => 'Invalid request method']);
            return;
        }

        if (!$this->rbac_lib->has_access('rekening/coa', 'edit')) {
            $this->_json(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses untuk mengubah status.']);
            return;
        }

        $new_status = $this->rekening_model->toggle_kategori_status($id);
        if ($new_status === false) {
            $this->_json(['status' => 'error', 'message' => 'Kategori Akun tidak ditemukan.']);
            return;
        }

        $this->_json([
            'status' => 'success',
            'message' => 'Status kategori berhasil diperbarui.',
            'is_active' => $new_status
        ]);
    }

    public function suggest_child_code_ajax() {
        $parent_kode = trim($this->input->get('parent_kode'));
        if (empty($parent_kode)) {
            $this->_json(['status' => 'error', 'message' => 'Parent kode tidak diberikan.']);
            return;
        }

        $parent = $this->rekening_model->get_coa_by_kode($parent_kode);
        if (!$parent) {
            $this->_json(['status' => 'error', 'message' => 'Akun induk tidak ditemukan.']);
            return;
        }

        $suggested_code = $this->rekening_model->suggest_next_child_code($parent_kode);

        $this->_json([
            'status' => 'success',
            'parent' => $parent,
            'suggested_code' => $suggested_code,
            'next_level' => (int)$parent['level'] + 1,
            'posisi_normal' => $parent['posisi_normal'],
            'kategori' => $parent['kategori'],
            'kategori_id' => $parent['kategori_id'] ?? null,
            'is_kas_bank' => (int)$parent['is_kas_bank'],
            'is_piutang' => (int)$parent['is_piutang'],
            'is_pendapatan' => (int)$parent['is_pendapatan']
        ]);
    }

    // =========================================================================
    // 3. COA (CHART OF ACCOUNTS) AJAX ENDPOINTS
    // =========================================================================
    public function get_coa_list_ajax() {
        $filters = [
            'kategori'  => $this->input->get('kategori'),
            'level'     => $this->input->get('level'),
            'is_active' => $this->input->get('is_active'),
            'search'    => $this->input->get('search')
        ];
        $data = $this->rekening_model->get_all_coa($filters);
        $this->_json(['status' => 'success', 'data' => $data]);
    }

    public function get_coa_detail_ajax($id) {
        $data = $this->rekening_model->get_coa_by_id($id);
        if (!$data) {
            $this->_json(['status' => 'error', 'message' => 'Akun COA tidak ditemukan.']);
            return;
        }
        $this->_json(['status' => 'success', 'data' => $data]);
    }

    public function save_coa_ajax() {
        if ($this->input->method() !== 'post') {
            $this->_json(['status' => 'error', 'message' => 'Invalid request method']);
            return;
        }

        $id = $this->input->post('id');
        $action = empty($id) ? 'create' : 'edit';

        if (!$this->rbac_lib->has_access('rekening/coa', $action)) {
            $this->_json(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses untuk aksi ini.']);
            return;
        }

        $kode_rekening = trim($this->input->post('kode_rekening'));
        $nama_rekening = trim($this->input->post('nama_rekening'));
        $kategori      = $this->input->post('kategori');
        $kategori_id   = $this->input->post('kategori_id') ?: null;
        $posisi_normal = $this->input->post('posisi_normal') ?: 'D';
        $level         = (int)$this->input->post('level') ?: 1;
        $parent_kode   = trim($this->input->post('parent_kode')) ?: null;
        $is_kas_bank   = $this->input->post('is_kas_bank') ? 1 : 0;
        $is_piutang    = $this->input->post('is_piutang') ? 1 : 0;
        $is_pendapatan = $this->input->post('is_pendapatan') ? 1 : 0;
        $is_active     = $this->input->post('is_active') ? 1 : 0;
        $keterangan    = trim($this->input->post('keterangan'));

        // Jika parent dipilih, otomatis sinkronkan kategori dan level
        if (!empty($parent_kode)) {
            $parent = $this->rekening_model->get_coa_by_kode($parent_kode);
            if ($parent) {
                $kategori = $parent['kategori'];
                $kategori_id = $parent['kategori_id'] ?? $kategori_id;
                $level = (int)$parent['level'] + 1;
            }
        } elseif (!empty($kategori_id) && empty($kategori)) {
            $kat = $this->rekening_model->get_kategori_by_id($kategori_id);
            if ($kat) $kategori = $kat['slug'];
        }

        if (empty($kode_rekening) || empty($nama_rekening) || empty($kategori)) {
            $this->_json(['status' => 'error', 'message' => 'Kode rekening, nama rekening, dan kategori wajib diisi.']);
            return;
        }

        // Cek duplikasi kode
        $existing = $this->rekening_model->get_coa_by_kode($kode_rekening);
        if ($existing && (empty($id) || $existing['id'] != $id)) {
            $this->_json(['status' => 'error', 'message' => "Kode Rekening '{$kode_rekening}' sudah digunakan oleh akun lain."]);
            return;
        }

        $payload = [
            'kode_rekening' => $kode_rekening,
            'nama_rekening' => $nama_rekening,
            'kategori'      => $kategori,
            'kategori_id'   => $kategori_id,
            'posisi_normal' => $posisi_normal,
            'level'         => $level,
            'parent_kode'   => $parent_kode,
            'is_kas_bank'   => $is_kas_bank,
            'is_piutang'    => $is_piutang,
            'is_pendapatan' => $is_pendapatan,
            'is_active'     => $is_active,
            'keterangan'    => $keterangan
        ];

        if (!empty($id)) {
            $this->rekening_model->update_coa($id, $payload);
            $msg = 'Akun Rekening berhasil diperbarui.';
        } else {
            $id = $this->rekening_model->insert_coa($payload);
            $msg = 'Akun Rekening baru berhasil ditambahkan.';
        }

        $this->_json(['status' => 'success', 'message' => $msg, 'id' => $id]);
    }

    public function delete_coa_ajax($id) {
        if ($this->input->method() !== 'post') {
            $this->_json(['status' => 'error', 'message' => 'Invalid request method']);
            return;
        }

        if (!$this->rbac_lib->has_access('rekening/coa', 'delete')) {
            $this->_json(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses untuk menghapus COA.']);
            return;
        }

        $result = $this->rekening_model->delete_coa($id);
        if (is_array($result) && !$result['status']) {
            $this->_json(['status' => 'error', 'message' => $result['message']]);
            return;
        }

        $this->_json(['status' => 'success', 'message' => 'Akun Rekening berhasil dihapus.']);
    }

    public function toggle_coa_ajax($id) {
        if ($this->input->method() !== 'post') {
            $this->_json(['status' => 'error', 'message' => 'Invalid request method']);
            return;
        }

        if (!$this->rbac_lib->has_access('rekening/coa', 'edit')) {
            $this->_json(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses untuk mengubah status.']);
            return;
        }

        $new_status = $this->rekening_model->toggle_coa_status($id);
        $this->_json([
            'status'     => 'success',
            'message'    => 'Status akun rekening berhasil diperbarui.',
            'new_status' => $new_status
        ]);
    }

    // =========================================================================
    // 4. REKENING TAHUN AJAX ENDPOINTS
    // =========================================================================
    public function get_tahun_list_ajax() {
        $tahun  = $this->input->get('tahun');
        $status = $this->input->get('status');
        $data   = $this->rekening_model->get_all_rekening_tahun($tahun, $status);
        $this->_json(['status' => 'success', 'data' => $data]);
    }

    public function get_tahun_detail_ajax($id) {
        $data = $this->rekening_model->get_rekening_tahun_by_id($id);
        if (!$data) {
            $this->_json(['status' => 'error', 'message' => 'Data rekening tahun tidak ditemukan.']);
            return;
        }
        $this->_json(['status' => 'success', 'data' => $data]);
    }

    public function save_tahun_ajax() {
        if ($this->input->method() !== 'post') {
            $this->_json(['status' => 'error', 'message' => 'Invalid request method']);
            return;
        }

        $id = $this->input->post('id');
        $action = empty($id) ? 'create' : 'edit';

        if (!$this->rbac_lib->has_access('rekening/tahun', $action)) {
            $this->_json(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses untuk aksi ini.']);
            return;
        }

        $tahun           = (int)$this->input->post('tahun');
        $kode_rekening   = trim($this->input->post('kode_rekening'));
        $saldo_awal      = (float)str_replace(['.', ','], ['', '.'], $this->input->post('saldo_awal') ?: 0);
        $target_anggaran = (float)str_replace(['.', ','], ['', '.'], $this->input->post('target_anggaran') ?: 0);
        $realisasi_debet = (float)str_replace(['.', ','], ['', '.'], $this->input->post('realisasi_debet') ?: 0);
        $realisasi_kredit= (float)str_replace(['.', ','], ['', '.'], $this->input->post('realisasi_kredit') ?: 0);
        $status          = $this->input->post('status') ?: 'aktif';
        $keterangan      = trim($this->input->post('keterangan'));

        if (empty($tahun) || empty($kode_rekening)) {
            $this->_json(['status' => 'error', 'message' => 'Tahun fiskal dan kode rekening COA wajib dipilih.']);
            return;
        }

        // Cek duplikasi tahun + kode
        $check = $this->db->get_where('rekening_tahun', ['tahun' => $tahun, 'kode_rekening' => $kode_rekening])->row_array();
        if ($check && (empty($id) || $check['id'] != $id)) {
            $this->_json(['status' => 'error', 'message' => "Akun {$kode_rekening} sudah terdaftar pada tahun fiskal {$tahun}."]);
            return;
        }

        $payload = [
            'tahun'            => $tahun,
            'kode_rekening'    => $kode_rekening,
            'saldo_awal'       => $saldo_awal,
            'target_anggaran'  => $target_anggaran,
            'realisasi_debet'  => $realisasi_debet,
            'realisasi_kredit' => $realisasi_kredit,
            'status'           => $status,
            'keterangan'       => $keterangan
        ];
        if (!empty($id)) {
            $payload['id'] = $id;
        }

        $saved_id = $this->rekening_model->save_rekening_tahun($payload);
        $msg = empty($id) ? 'Anggaran & Saldo Awal berhasil ditambahkan.' : 'Data rekening tahun berhasil diperbarui.';

        $this->_json(['status' => 'success', 'message' => $msg, 'id' => $saved_id]);
    }

    public function delete_tahun_ajax($id) {
        if ($this->input->method() !== 'post') {
            $this->_json(['status' => 'error', 'message' => 'Invalid request method']);
            return;
        }

        if (!$this->rbac_lib->has_access('rekening/tahun', 'delete')) {
            $this->_json(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses untuk menghapus data.']);
            return;
        }

        $result = $this->rekening_model->delete_rekening_tahun($id);
        if (is_array($result) && !$result['status']) {
            $this->_json(['status' => 'error', 'message' => $result['message']]);
            return;
        }

        $this->_json(['status' => 'success', 'message' => 'Data rekening tahun berhasil dihapus.']);
    }

    public function toggle_tahun_status_ajax($id) {
        if ($this->input->method() !== 'post') {
            $this->_json(['status' => 'error', 'message' => 'Invalid request method']);
            return;
        }

        if (!$this->rbac_lib->has_access('rekening/tahun', 'edit')) {
            $this->_json(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses untuk mengubah status tahun buku.']);
            return;
        }

        $new_status = $this->rekening_model->toggle_status_tahun($id);
        $this->_json([
            'status'     => 'success',
            'message'    => 'Status tahun buku berhasil diubah ke: ' . strtoupper($new_status),
            'new_status' => $new_status
        ]);
    }

    // =========================================================================
    // 5. AKUN BAYAR AJAX ENDPOINTS
    // =========================================================================
    public function get_bayar_list_ajax() {
        $filters = [
            'tipe'      => $this->input->get('tipe'),
            'is_active' => $this->input->get('is_active'),
            'search'    => $this->input->get('search')
        ];
        $data = $this->rekening_model->get_all_akun_bayar($filters);
        $this->_json(['status' => 'success', 'data' => $data]);
    }

    public function get_bayar_detail_ajax($id) {
        $data = $this->rekening_model->get_akun_bayar_by_id($id);
        if (!$data) {
            $this->_json(['status' => 'error', 'message' => 'Akun bayar tidak ditemukan.']);
            return;
        }
        $this->_json(['status' => 'success', 'data' => $data]);
    }

    public function save_bayar_ajax() {
        if ($this->input->method() !== 'post') {
            $this->_json(['status' => 'error', 'message' => 'Invalid request method']);
            return;
        }

        $id = $this->input->post('id');
        $action = empty($id) ? 'create' : 'edit';

        if (!$this->rbac_lib->has_access('rekening/akun_bayar', $action)) {
            $this->_json(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses untuk aksi ini.']);
            return;
        }

        $kode_bayar         = strtoupper(trim($this->input->post('kode_bayar')));
        $nama_bayar         = trim($this->input->post('nama_bayar'));
        $kode_rekening      = trim($this->input->post('kode_rekening'));
        $tipe_transaksi     = $this->input->post('tipe_transaksi') ?: 'cash';
        $nama_bank          = trim($this->input->post('nama_bank'));
        $nomor_rekening_bank= trim($this->input->post('nomor_rekening_bank'));
        $atas_nama          = trim($this->input->post('atas_nama'));
        $biaya_admin        = (float)str_replace(['.', ','], ['', '.'], $this->input->post('biaya_admin') ?: 0);
        $urutan             = (int)$this->input->post('urutan') ?: 1;
        $is_active          = $this->input->post('is_active') ? 1 : 0;

        if (empty($kode_bayar) || empty($nama_bayar) || empty($kode_rekening)) {
            $this->_json(['status' => 'error', 'message' => 'Kode bayar, nama channel, dan pemetaan akun COA wajib diisi.']);
            return;
        }

        // Cek duplikasi kode bayar
        $check = $this->db->get_where('akun_bayar', ['kode_bayar' => $kode_bayar])->row_array();
        if ($check && (empty($id) || $check['id'] != $id)) {
            $this->_json(['status' => 'error', 'message' => "Kode bayar '{$kode_bayar}' sudah digunakan."]);
            return;
        }

        $payload = [
            'kode_bayar'          => $kode_bayar,
            'nama_bayar'          => $nama_bayar,
            'kode_rekening'       => $kode_rekening,
            'tipe_transaksi'      => $tipe_transaksi,
            'nama_bank'           => $nama_bank,
            'nomor_rekening_bank' => $nomor_rekening_bank,
            'atas_nama'           => $atas_nama,
            'biaya_admin'         => $biaya_admin,
            'urutan'              => $urutan,
            'is_active'           => $is_active
        ];

        if (!empty($id)) {
            $this->rekening_model->update_akun_bayar($id, $payload);
            $msg = 'Akun bayar kasir berhasil diperbarui.';
        } else {
            $id = $this->rekening_model->insert_akun_bayar($payload);
            $msg = 'Akun bayar kasir baru berhasil ditambahkan.';
        }

        $this->_json(['status' => 'success', 'message' => $msg, 'id' => $id]);
    }

    public function delete_bayar_ajax($id) {
        if ($this->input->method() !== 'post') {
            $this->_json(['status' => 'error', 'message' => 'Invalid request method']);
            return;
        }

        if (!$this->rbac_lib->has_access('rekening/akun_bayar', 'delete')) {
            $this->_json(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses untuk menghapus data.']);
            return;
        }

        $this->rekening_model->delete_akun_bayar($id);
        $this->_json(['status' => 'success', 'message' => 'Akun bayar kasir berhasil dihapus.']);
    }

    public function toggle_bayar_ajax($id) {
        if ($this->input->method() !== 'post') {
            $this->_json(['status' => 'error', 'message' => 'Invalid request method']);
            return;
        }

        if (!$this->rbac_lib->has_access('rekening/akun_bayar', 'edit')) {
            $this->_json(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses untuk mengubah status.']);
            return;
        }

        $new_status = $this->rekening_model->toggle_akun_bayar_status($id);
        $this->_json([
            'status'     => 'success',
            'message'    => 'Status channel bayar berhasil diperbarui.',
            'new_status' => $new_status
        ]);
    }

    // =========================================================================
    // 6. AKUN PIUTANG AJAX ENDPOINTS
    // =========================================================================
    public function get_piutang_list_ajax() {
        $filters = [
            'penjamin'  => $this->input->get('penjamin'),
            'is_active' => $this->input->get('is_active'),
            'search'    => $this->input->get('search')
        ];
        $data = $this->rekening_model->get_all_akun_piutang($filters);
        $this->_json(['status' => 'success', 'data' => $data]);
    }

    public function get_piutang_detail_ajax($id) {
        $data = $this->rekening_model->get_akun_piutang_by_id($id);
        if (!$data) {
            $this->_json(['status' => 'error', 'message' => 'Akun piutang tidak ditemukan.']);
            return;
        }
        $this->_json(['status' => 'success', 'data' => $data]);
    }

    public function save_piutang_ajax() {
        if ($this->input->method() !== 'post') {
            $this->_json(['status' => 'error', 'message' => 'Invalid request method']);
            return;
        }

        $id = $this->input->post('id');
        $action = empty($id) ? 'create' : 'edit';

        if (!$this->rbac_lib->has_access('rekening/akun_piutang', $action)) {
            $this->_json(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses untuk aksi ini.']);
            return;
        }

        $kode_piutang      = strtoupper(trim($this->input->post('kode_piutang')));
        $nama_piutang      = trim($this->input->post('nama_piutang'));
        $kode_rekening     = trim($this->input->post('kode_rekening'));
        $kategori_penjamin = $this->input->post('kategori_penjamin') ?: 'bpjs';
        $jatuh_tempo_hari  = (int)$this->input->post('jatuh_tempo_hari') ?: 30;
        $kontak_person     = trim($this->input->post('kontak_person'));
        $no_telepon        = trim($this->input->post('no_telepon'));
        $email             = trim($this->input->post('email'));
        $alamat            = trim($this->input->post('alamat'));
        $is_active         = $this->input->post('is_active') ? 1 : 0;

        if (empty($kode_piutang) || empty($nama_piutang) || empty($kode_rekening)) {
            $this->_json(['status' => 'error', 'message' => 'Kode piutang, nama mitra penjamin, dan akun COA wajib diisi.']);
            return;
        }

        // Cek duplikasi kode
        $check = $this->db->get_where('akun_piutang', ['kode_piutang' => $kode_piutang])->row_array();
        if ($check && (empty($id) || $check['id'] != $id)) {
            $this->_json(['status' => 'error', 'message' => "Kode piutang '{$kode_piutang}' sudah digunakan."]);
            return;
        }

        $payload = [
            'kode_piutang'      => $kode_piutang,
            'nama_piutang'      => $nama_piutang,
            'kode_rekening'     => $kode_rekening,
            'kategori_penjamin' => $kategori_penjamin,
            'jatuh_tempo_hari'  => $jatuh_tempo_hari,
            'kontak_person'     => $kontak_person,
            'no_telepon'        => $no_telepon,
            'email'             => $email,
            'alamat'            => $alamat,
            'is_active'         => $is_active
        ];

        if (!empty($id)) {
            $this->rekening_model->update_akun_piutang($id, $payload);
            $msg = 'Akun piutang penjamin berhasil diperbarui.';
        } else {
            $id = $this->rekening_model->insert_akun_piutang($payload);
            $msg = 'Akun piutang penjamin baru berhasil ditambahkan.';
        }

        $this->_json(['status' => 'success', 'message' => $msg, 'id' => $id]);
    }

    public function delete_piutang_ajax($id) {
        if ($this->input->method() !== 'post') {
            $this->_json(['status' => 'error', 'message' => 'Invalid request method']);
            return;
        }

        if (!$this->rbac_lib->has_access('rekening/akun_piutang', 'delete')) {
            $this->_json(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses untuk menghapus data.']);
            return;
        }

        $this->rekening_model->delete_akun_piutang($id);
        $this->_json(['status' => 'success', 'message' => 'Akun piutang penjamin berhasil dihapus.']);
    }

    public function toggle_piutang_ajax($id) {
        if ($this->input->method() !== 'post') {
            $this->_json(['status' => 'error', 'message' => 'Invalid request method']);
            return;
        }

        if (!$this->rbac_lib->has_access('rekening/akun_piutang', 'edit')) {
            $this->_json(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses untuk mengubah status.']);
            return;
        }

        $new_status = $this->rekening_model->toggle_akun_piutang_status($id);
        $this->_json([
            'status'     => 'success',
            'message'    => 'Status akun piutang berhasil diperbarui.',
            'new_status' => $new_status
        ]);
    }
}
