<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modul Gudang & Stock Opname Reagen / BHP.
 *
 * Mengelola master barang, penerimaan berbasis batch, pengeluaran dengan
 * antrian FIFO/FEFO, kartu stok, peringatan kedaluwarsa dan titik pemesanan
 * ulang, serta penyesuaian persediaan melalui stock opname.
 */
class Gudang extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->rbac_lib->require_login();
        $this->load->model('gudang_model');
        $this->load->library('form_validation');
    }

    private function _log($action, $message, $level = 'info') {
        log_message($level, sprintf("[GUDANG-CTRL] USER=%s | ACTION=%s | MSG=%s", $this->rbac_lib->user('username'), $action, $message));
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

    /** Daftar barang aktif untuk pilihan pada form. */
    private function _opsi_barang() {
        return $this->db->select('id, kode_barang, nama_barang, satuan, kategori, wajib_batch, stok_minimum')
                        ->where('is_active', 1)
                        ->order_by('nama_barang', 'ASC')
                        ->get('gudang_barang')
                        ->result_array();
    }

    // =====================================================================
    // MONITORING STOK
    // =====================================================================

    public function index() {
        $this->rbac_lib->require_access('gudang', 'view');

        $data = $this->_base_data('Monitoring Stok Gudang', 'gudang');
        $data['ringkasan'] = $this->gudang_model->ringkasan();
        $data['stok_kritis'] = $this->gudang_model->get_stok_kritis();
        $data['kedaluwarsa'] = $this->gudang_model->get_batch_kedaluwarsa();
        $data['mutasi_terakhir'] = $this->gudang_model->get_mutasi_terakhir(10);

        $this->_render('gudang/index', $data);
    }

    // =====================================================================
    // MASTER BARANG
    // =====================================================================

    public function barang() {
        $this->rbac_lib->require_access('gudang/barang', 'view');

        $data = $this->_base_data('Master Barang, Reagen & BHP', 'gudang/barang');
        $data['kategori_ref'] = $this->gudang_model->get_referensi('kategori_barang');
        $data['satuan_ref'] = $this->gudang_model->get_referensi('satuan');
        $data['lokasi_ref'] = $this->gudang_model->get_referensi('lokasi_simpan');
        $data['ringkasan'] = $this->gudang_model->ringkasan();

        $this->_render('gudang/barang', $data);
    }

    public function fetch_barang() {
        $this->rbac_lib->require_access('gudang/barang', 'view');

        $params = [
            'draw' => $this->input->get('draw', TRUE),
            'start' => $this->input->get('start', TRUE),
            'length' => $this->input->get('length', TRUE),
            'search' => ['value' => $this->input->get('search[value]', TRUE)],
            'kategori' => $this->input->get('kategori', TRUE) ?: 'all',
            'status_stok' => $this->input->get('status_stok', TRUE) ?: 'all',
            'is_active' => $this->input->get('is_active', TRUE) ?: 'all',
        ];

        $result = $this->gudang_model->get_barang_datatable($params);

        return $this->_json([
            'status' => 'success',
            'draw' => $result['draw'],
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
            'data' => $result['data'],
        ]);
    }

    public function get_barang($id) {
        $this->rbac_lib->require_access('gudang/barang', 'view');

        $row = $this->gudang_model->get_barang_by_id((int)$id);
        if (!$row) {
            return $this->_error('Barang tidak ditemukan.', 404);
        }
        return $this->_json(['status' => 'success', 'data' => $row]);
    }

    public function save_barang() {
        $id = $this->input->post('id');
        $isEdit = !empty($id);

        if (!$this->rbac_lib->has_access('gudang/barang', $isEdit ? 'edit' : 'create')) {
            return $this->_error('Anda tidak memiliki hak akses untuk ' . ($isEdit ? 'mengubah' : 'menambah') . ' master barang.', 403);
        }

        $this->form_validation->set_rules('nama_barang', 'Nama Barang', 'required|trim|max_length[200]');
        $this->form_validation->set_rules('kategori', 'Kategori', 'required|trim');
        $this->form_validation->set_rules('satuan', 'Satuan', 'required|trim|max_length[30]');

        if ($this->form_validation->run() === FALSE) {
            return $this->_error(validation_errors(' ', ' '));
        }

        $savedId = $this->gudang_model->save_barang([
            'kode_barang' => $this->input->post('kode_barang', TRUE),
            'nama_barang' => $this->input->post('nama_barang', TRUE),
            'kategori' => $this->input->post('kategori', TRUE),
            'satuan' => $this->input->post('satuan', TRUE),
            'merk' => $this->input->post('merk', TRUE),
            'kemasan' => $this->input->post('kemasan', TRUE),
            'suhu_penyimpanan' => $this->input->post('suhu_penyimpanan', TRUE),
            'lokasi_simpan' => $this->input->post('lokasi_simpan', TRUE),
            'stok_minimum' => $this->input->post('stok_minimum'),
            'stok_maksimum' => $this->input->post('stok_maksimum'),
            'wajib_batch' => $this->input->post('wajib_batch') === null ? 1 : (int)$this->input->post('wajib_batch'),
            'keterangan' => $this->input->post('keterangan', TRUE),
            'is_active' => $this->input->post('is_active') === null ? 1 : (int)$this->input->post('is_active'),
        ], $isEdit ? (int)$id : null);

        $this->_log($isEdit ? 'BARANG_UPDATE' : 'BARANG_CREATE', "ID={$savedId}");

        return $this->_json([
            'status' => 'success',
            'message' => 'Data barang berhasil ' . ($isEdit ? 'diperbarui' : 'ditambahkan') . '.',
            'data' => $this->gudang_model->get_barang_by_id($savedId),
        ]);
    }

    public function hapus_barang($id) {
        if (!$this->rbac_lib->has_access('gudang/barang', 'delete')) {
            return $this->_error('Anda tidak memiliki hak akses untuk menghapus barang.', 403);
        }

        $result = $this->gudang_model->hapus_barang((int)$id);
        if (!$result['status']) {
            return $this->_error($result['message']);
        }

        $this->_log('BARANG_DELETE', "ID={$id}");
        return $this->_json(['status' => 'success', 'message' => $result['message']]);
    }

    // =====================================================================
    // PENERIMAAN BARANG
    // =====================================================================

    public function penerimaan() {
        $this->rbac_lib->require_access('gudang/penerimaan', 'view');

        $data = $this->_base_data('Penerimaan Barang & Reagen', 'gudang/penerimaan');
        $data['barang_options'] = $this->_opsi_barang();
        $data['pemasok_ref'] = $this->gudang_model->get_referensi('pemasok');
        $data['riwayat'] = $this->gudang_model->get_mutasi_terakhir(25);

        $this->_render('gudang/penerimaan', $data);
    }

    public function simpan_penerimaan() {
        if (!$this->rbac_lib->has_access('gudang/penerimaan', 'create')) {
            return $this->_error('Anda tidak memiliki hak akses untuk mencatat penerimaan barang.', 403);
        }

        $this->form_validation->set_rules('barang_id', 'Barang', 'required|integer');
        $this->form_validation->set_rules('jumlah', 'Jumlah', 'required|numeric');
        $this->form_validation->set_rules('tanggal_terima', 'Tanggal Terima', 'required');

        if ($this->form_validation->run() === FALSE) {
            return $this->_error(validation_errors(' ', ' '));
        }

        $result = $this->gudang_model->terima_barang([
            'barang_id' => (int)$this->input->post('barang_id'),
            'jumlah' => $this->input->post('jumlah'),
            'nomor_batch' => $this->input->post('nomor_batch', TRUE),
            'tanggal_terima' => $this->input->post('tanggal_terima', TRUE),
            'tanggal_kedaluwarsa' => $this->input->post('tanggal_kedaluwarsa', TRUE),
            'harga_satuan' => $this->input->post('harga_satuan'),
            'pemasok' => $this->input->post('pemasok', TRUE),
            'nomor_dokumen' => $this->input->post('nomor_dokumen', TRUE),
            'keterangan' => $this->input->post('keterangan', TRUE),
        ], $this->rbac_lib->user('id'), $this->rbac_lib->user('full_name'));

        if (!$result['status']) {
            return $this->_error($result['message']);
        }

        $this->_log('PENERIMAAN', $result['nomor']);
        return $this->_json(['status' => 'success', 'message' => $result['message'], 'data' => $result]);
    }

    // =====================================================================
    // PEMAKAIAN BARANG
    // =====================================================================

    public function pemakaian() {
        $this->rbac_lib->require_access('gudang/pemakaian', 'view');

        $data = $this->_base_data('Pemakaian & Pengeluaran Barang', 'gudang/pemakaian');
        $data['barang_options'] = $this->_opsi_barang();
        $data['config_gudang'] = $this->gudang_model->get_config();
        $data['riwayat'] = $this->gudang_model->get_mutasi_terakhir(25);

        $this->_render('gudang/pemakaian', $data);
    }

    /**
     * Perlihatkan batch mana saja yang akan terpakai sebelum disimpan.
     */
    public function simulasi() {
        $this->rbac_lib->require_access('gudang/pemakaian', 'view');

        $barangId = (int)$this->input->get('barang_id', TRUE);
        $jumlah = (float)$this->input->get('jumlah', TRUE);

        if ($barangId <= 0) {
            return $this->_error('Barang belum dipilih.');
        }
        if ($jumlah <= 0) {
            return $this->_error('Jumlah harus lebih besar dari nol.');
        }

        return $this->_json([
            'status' => 'success',
            'data' => $this->gudang_model->simulasi_keluar($barangId, $jumlah),
        ]);
    }

    public function simpan_pemakaian() {
        if (!$this->rbac_lib->has_access('gudang/pemakaian', 'create')) {
            return $this->_error('Anda tidak memiliki hak akses untuk mencatat pengeluaran barang.', 403);
        }

        $this->form_validation->set_rules('barang_id', 'Barang', 'required|integer');
        $this->form_validation->set_rules('jumlah', 'Jumlah', 'required|numeric');
        $this->form_validation->set_rules('tanggal', 'Tanggal', 'required');

        if ($this->form_validation->run() === FALSE) {
            return $this->_error(validation_errors(' ', ' '));
        }

        $tipe = $this->input->post('tipe', TRUE);
        if (!in_array($tipe, ['keluar', 'rusak', 'kedaluwarsa'], true)) {
            $tipe = 'keluar';
        }

        $result = $this->gudang_model->keluarkan_barang([
            'barang_id' => (int)$this->input->post('barang_id'),
            'jumlah' => $this->input->post('jumlah'),
            'tanggal' => $this->input->post('tanggal', TRUE),
            'tujuan' => $this->input->post('tujuan', TRUE),
            'keterangan' => $this->input->post('keterangan', TRUE),
        ], $this->rbac_lib->user('id'), $this->rbac_lib->user('full_name'), $tipe);

        if (!$result['status']) {
            return $this->_error($result['message']);
        }

        $this->_log('PEMAKAIAN', $result['nomor']);
        return $this->_json(['status' => 'success', 'message' => $result['message'], 'data' => $result]);
    }

    // =====================================================================
    // KARTU STOK
    // =====================================================================

    public function kartu_stok() {
        $this->rbac_lib->require_access('gudang/kartu_stok', 'view');

        $data = $this->_base_data('Kartu Stok Digital', 'gudang/kartu_stok');
        $data['barang_options'] = $this->_opsi_barang();
        $data['barang_id'] = (int)($this->input->get('barang_id', TRUE) ?: 0);
        $data['mulai'] = $this->input->get('mulai', TRUE) ?: date('Y-m-01');
        $data['selesai'] = $this->input->get('selesai', TRUE) ?: date('Y-m-t');

        $this->_render('gudang/kartu_stok', $data);
    }

    public function fetch_kartu() {
        $this->rbac_lib->require_access('gudang/kartu_stok', 'view');

        $barangId = (int)$this->input->get('barang_id', TRUE);
        if ($barangId <= 0) {
            return $this->_error('Barang belum dipilih.');
        }

        $mulai = $this->input->get('mulai', TRUE) ?: null;
        $selesai = $this->input->get('selesai', TRUE) ?: null;

        if ($mulai && $selesai && strtotime($selesai) < strtotime($mulai)) {
            return $this->_error('Tanggal akhir lebih awal dari tanggal mulai.');
        }

        return $this->_json([
            'status' => 'success',
            'data' => [
                'barang' => $this->gudang_model->get_barang_by_id($barangId),
                'mutasi' => $this->gudang_model->get_kartu_stok($barangId, $mulai, $selesai),
                'ringkasan' => $this->gudang_model->ringkasan_kartu($barangId, $mulai, $selesai),
            ],
        ]);
    }

    // =====================================================================
    // STOCK OPNAME
    // =====================================================================

    public function opname() {
        $this->rbac_lib->require_access('gudang/opname', 'view');

        $data = $this->_base_data('Stock Opname Gudang', 'gudang/opname');
        $data['daftar'] = $this->gudang_model->get_opname_list();
        $data['aktif'] = null;

        foreach ($data['daftar'] as $o) {
            if ($o['status'] === 'draft') {
                $data['aktif'] = $this->gudang_model->get_opname_by_id((int)$o['id']);
                break;
            }
        }

        $this->_render('gudang/opname', $data);
    }

    public function buat_opname() {
        if (!$this->rbac_lib->has_access('gudang/opname', 'create')) {
            return $this->_error('Anda tidak memiliki hak akses untuk membuka sesi opname.', 403);
        }

        $result = $this->gudang_model->buat_opname([
            'tanggal' => $this->input->post('tanggal', TRUE),
            'petugas_nama' => $this->input->post('petugas_nama', TRUE),
            'catatan' => $this->input->post('catatan', TRUE),
        ], $this->rbac_lib->user('id'), $this->rbac_lib->user('full_name'));

        if (!$result['status']) {
            return $this->_error($result['message']);
        }

        $this->_log('OPNAME_CREATE', $result['nomor']);
        return $this->_json(['status' => 'success', 'message' => $result['message'], 'data' => $result]);
    }

    public function get_opname($id) {
        $this->rbac_lib->require_access('gudang/opname', 'view');

        $row = $this->gudang_model->get_opname_by_id((int)$id);
        if (!$row) {
            return $this->_error('Sesi opname tidak ditemukan.', 404);
        }
        return $this->_json(['status' => 'success', 'data' => $row]);
    }

    public function simpan_hitungan() {
        if (!$this->rbac_lib->has_access('gudang/opname', 'edit')) {
            return $this->_error('Anda tidak memiliki hak akses untuk mengisi hasil opname.', 403);
        }

        $result = $this->gudang_model->simpan_hitungan(
            (int)$this->input->post('detail_id'),
            $this->input->post('stok_fisik'),
            $this->input->post('keterangan', TRUE)
        );

        if (!$result['status']) {
            return $this->_error($result['message']);
        }

        return $this->_json(['status' => 'success', 'data' => $result['data']]);
    }

    public function selesaikan_opname($id) {
        if (!$this->rbac_lib->has_access('gudang/opname', 'edit')) {
            return $this->_error('Anda tidak memiliki hak akses untuk menutup sesi opname.', 403);
        }

        $result = $this->gudang_model->selesaikan_opname(
            (int)$id,
            $this->rbac_lib->user('id'),
            $this->rbac_lib->user('full_name')
        );

        if (!$result['status']) {
            return $this->_error($result['message']);
        }

        $this->_log('OPNAME_FINISH', "ID={$id}");
        return $this->_json(['status' => 'success', 'message' => $result['message']]);
    }

    public function batalkan_opname($id) {
        if (!$this->rbac_lib->has_access('gudang/opname', 'delete')) {
            return $this->_error('Anda tidak memiliki hak akses untuk membatalkan sesi opname.', 403);
        }

        $result = $this->gudang_model->batalkan_opname((int)$id);
        if (!$result['status']) {
            return $this->_error($result['message']);
        }

        $this->_log('OPNAME_CANCEL', "ID={$id}");
        return $this->_json(['status' => 'success', 'message' => $result['message']]);
    }
}
