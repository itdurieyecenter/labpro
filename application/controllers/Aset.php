<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Modul Aset & Inventaris Laboratorium (KIB-B Peralatan dan Mesin).
 *
 * Mengelola pencatatan BMD, riwayat kalibrasi instrumen sesuai ISO/IEC 17025,
 * kartu pemeliharaan berkala, serta rekam jejak mutasi dan penghapusan aset.
 */
class Aset extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->rbac_lib->require_login();
        $this->load->model('aset_model');
        $this->load->library('form_validation');
    }

    private function _log($action, $message, $level = 'info') {
        log_message($level, sprintf("[ASET-CTRL] USER=%s | ACTION=%s | MSG=%s", $this->rbac_lib->user('username'), $action, $message));
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

    private function _referensi() {
        return [
            'golongan_ref' => $this->aset_model->get_referensi('golongan_bmd'),
            'ruangan_ref' => $this->aset_model->get_referensi('ruangan'),
            'sumber_dana_ref' => $this->aset_model->get_referensi('sumber_dana'),
            'lembaga_ref' => $this->aset_model->get_referensi('lembaga_kalibrasi'),
        ];
    }

    // =====================================================================
    // MONITORING
    // =====================================================================

    public function index() {
        $this->rbac_lib->require_access('aset', 'view');

        $data = $this->_base_data('Monitoring Aset & Inventaris', 'aset');
        $data['ringkasan'] = $this->aset_model->ringkasan();
        $data['kalibrasi_perhatian'] = $this->aset_model->get_kalibrasi_perhatian();
        $data['pemeliharaan_jatuh_tempo'] = $this->aset_model->get_pemeliharaan_jatuh_tempo();
        $data['ruangan_ref'] = $this->aset_model->get_referensi('ruangan');

        $this->_render('aset/index', $data);
    }

    // =====================================================================
    // MASTER ASET (KIB-B)
    // =====================================================================

    public function barang() {
        $this->rbac_lib->require_access('aset/barang', 'view');

        $data = $this->_base_data('Daftar Aset KIB-B Peralatan & Mesin', 'aset/barang');
        $data = array_merge($data, $this->_referensi());
        $data['pegawai_options'] = $this->aset_model->get_pegawai_options();
        $data['ringkasan'] = $this->aset_model->ringkasan();

        $this->_render('aset/barang', $data);
    }

    public function fetch() {
        $this->rbac_lib->require_access('aset/barang', 'view');

        $params = [
            'draw' => $this->input->get('draw', TRUE),
            'start' => $this->input->get('start', TRUE),
            'length' => $this->input->get('length', TRUE),
            'search' => ['value' => $this->input->get('search[value]', TRUE)],
            'golongan' => $this->input->get('golongan', TRUE) ?: 'all',
            'lokasi_ruangan' => $this->input->get('lokasi_ruangan', TRUE) ?: 'all',
            'kondisi' => $this->input->get('kondisi', TRUE) ?: 'all',
            'status' => $this->input->get('status', TRUE) ?: 'all',
            'status_kalibrasi' => $this->input->get('status_kalibrasi', TRUE) ?: 'all',
        ];

        $result = $this->aset_model->get_datatable($params);

        return $this->_json([
            'status' => 'success',
            'draw' => $result['draw'],
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
            'data' => $result['data'],
        ]);
    }

    public function get($id) {
        $this->rbac_lib->require_access('aset/barang', 'view');

        $row = $this->aset_model->get_by_id((int)$id);
        if (!$row) {
            return $this->_error('Aset tidak ditemukan.', 404);
        }
        return $this->_json(['status' => 'success', 'data' => $row]);
    }

    public function next_nup() {
        $this->rbac_lib->require_access('aset/barang', 'view');

        $kode = trim($this->input->get('kode_bmd', TRUE));
        if ($kode === '') {
            return $this->_error('Kode BMD belum diisi.');
        }

        $nup = $this->aset_model->get_next_nup($kode);
        return $this->_json([
            'status' => 'success',
            'data' => ['nup' => $nup, 'nomor_register' => $this->aset_model->susun_nomor_register($kode, $nup)],
        ]);
    }

    public function save() {
        $id = $this->input->post('id');
        $isEdit = !empty($id);

        if (!$this->rbac_lib->has_access('aset/barang', $isEdit ? 'edit' : 'create')) {
            return $this->_error('Anda tidak memiliki hak akses untuk ' . ($isEdit ? 'mengubah' : 'menambah') . ' data aset.', 403);
        }

        $this->form_validation->set_rules('kode_bmd', 'Kode BMD', 'required|trim|max_length[50]');
        $this->form_validation->set_rules('nama_aset', 'Nama Aset', 'required|trim|max_length[200]');
        $this->form_validation->set_rules('golongan', 'Golongan BMD', 'required|trim');
        $this->form_validation->set_rules('kondisi', 'Kondisi', 'required|trim');

        if ($this->form_validation->run() === FALSE) {
            return $this->_error(validation_errors(' ', ' '));
        }

        $result = $this->aset_model->save_aset([
            'kode_bmd' => $this->input->post('kode_bmd', TRUE),
            'nup' => $this->input->post('nup'),
            'golongan' => $this->input->post('golongan', TRUE),
            'nama_aset' => $this->input->post('nama_aset', TRUE),
            'merk_tipe' => $this->input->post('merk_tipe', TRUE),
            'nomor_seri' => $this->input->post('nomor_seri', TRUE),
            'ukuran_spesifikasi' => $this->input->post('ukuran_spesifikasi', TRUE),
            'bahan' => $this->input->post('bahan', TRUE),
            'tanggal_perolehan' => $this->input->post('tanggal_perolehan', TRUE),
            'cara_perolehan' => $this->input->post('cara_perolehan', TRUE),
            'nomor_dokumen_perolehan' => $this->input->post('nomor_dokumen_perolehan', TRUE),
            'nilai_perolehan' => $this->input->post('nilai_perolehan'),
            'masa_manfaat_tahun' => $this->input->post('masa_manfaat_tahun'),
            'lokasi_ruangan' => $this->input->post('lokasi_ruangan', TRUE),
            'penanggung_jawab_id' => $this->input->post('penanggung_jawab_id'),
            'kondisi' => $this->input->post('kondisi', TRUE),
            'wajib_kalibrasi' => $this->input->post('wajib_kalibrasi') === null ? 0 : (int)$this->input->post('wajib_kalibrasi'),
            'interval_kalibrasi_bulan' => $this->input->post('interval_kalibrasi_bulan'),
            'wajib_pemeliharaan' => $this->input->post('wajib_pemeliharaan') === null ? 0 : (int)$this->input->post('wajib_pemeliharaan'),
            'interval_pemeliharaan_bulan' => $this->input->post('interval_pemeliharaan_bulan'),
            'keterangan' => $this->input->post('keterangan', TRUE),
        ], $isEdit ? (int)$id : null, $this->rbac_lib->user('id'), $this->rbac_lib->user('full_name'));

        if (!$result['status']) {
            return $this->_error($result['message']);
        }

        $this->_log($isEdit ? 'ASET_UPDATE' : 'ASET_CREATE', "ID={$result['id']}");

        return $this->_json([
            'status' => 'success',
            'message' => 'Data aset berhasil ' . ($isEdit ? 'diperbarui' : 'ditambahkan') . '.',
            'data' => $result['data'],
        ]);
    }

    public function hapus($id) {
        if (!$this->rbac_lib->has_access('aset/barang', 'delete')) {
            return $this->_error('Anda tidak memiliki hak akses untuk menghapus aset.', 403);
        }

        $result = $this->aset_model->hapus_aset((int)$id);
        if (!$result['status']) {
            return $this->_error($result['message']);
        }

        $this->_log('ASET_DELETE', "ID={$id}");
        return $this->_json(['status' => 'success', 'message' => $result['message']]);
    }

    // =====================================================================
    // KALIBRASI
    // =====================================================================

    public function kalibrasi() {
        $this->rbac_lib->require_access('aset/kalibrasi', 'view');

        $data = $this->_base_data('Kalibrasi Instrumen Laboratorium', 'aset/kalibrasi');
        $data['perhatian'] = $this->aset_model->get_kalibrasi_perhatian();
        $data['aset_options'] = $this->aset_model->get_aset_options();
        $data['lembaga_ref'] = $this->aset_model->get_referensi('lembaga_kalibrasi');
        $data['ringkasan'] = $this->aset_model->ringkasan();

        $this->_render('aset/kalibrasi', $data);
    }

    public function simpan_kalibrasi() {
        if (!$this->rbac_lib->has_access('aset/kalibrasi', 'create')) {
            return $this->_error('Anda tidak memiliki hak akses untuk mencatat kalibrasi.', 403);
        }

        $this->form_validation->set_rules('aset_id', 'Aset', 'required|integer');
        $this->form_validation->set_rules('tanggal_kalibrasi', 'Tanggal Kalibrasi', 'required');
        $this->form_validation->set_rules('lembaga_kalibrasi', 'Lembaga Kalibrasi', 'required|trim');
        $this->form_validation->set_rules('hasil', 'Hasil Kalibrasi', 'required|trim');

        if ($this->form_validation->run() === FALSE) {
            return $this->_error(validation_errors(' ', ' '));
        }

        $result = $this->aset_model->simpan_kalibrasi([
            'aset_id' => (int)$this->input->post('aset_id'),
            'tanggal_kalibrasi' => $this->input->post('tanggal_kalibrasi', TRUE),
            'masa_berlaku_sampai' => $this->input->post('masa_berlaku_sampai', TRUE),
            'lembaga_kalibrasi' => $this->input->post('lembaga_kalibrasi', TRUE),
            'nomor_sertifikat' => $this->input->post('nomor_sertifikat', TRUE),
            'terakreditasi_kan' => $this->input->post('terakreditasi_kan') === null ? 0 : 1,
            'hasil' => $this->input->post('hasil', TRUE),
            'nilai_koreksi' => $this->input->post('nilai_koreksi', TRUE),
            'biaya' => $this->input->post('biaya'),
            'catatan' => $this->input->post('catatan', TRUE),
        ], $this->rbac_lib->user('full_name'));

        if (!$result['status']) {
            return $this->_error($result['message']);
        }

        $this->_log('KALIBRASI', "ID={$result['id']}");
        return $this->_json(['status' => 'success', 'message' => $result['message']]);
    }

    public function fetch_kalibrasi() {
        $this->rbac_lib->require_access('aset/kalibrasi', 'view');
        return $this->_json(['status' => 'success', 'data' => $this->aset_model->get_kalibrasi_perhatian()]);
    }

    // =====================================================================
    // PEMELIHARAAN
    // =====================================================================

    public function pemeliharaan() {
        $this->rbac_lib->require_access('aset/pemeliharaan', 'view');

        $data = $this->_base_data('Kartu Pemeliharaan Aset', 'aset/pemeliharaan');
        $data['aset_options'] = $this->aset_model->get_aset_options();
        $data['jatuh_tempo'] = $this->aset_model->get_pemeliharaan_jatuh_tempo();
        $data['ringkasan'] = $this->aset_model->ringkasan();

        $this->_render('aset/pemeliharaan', $data);
    }

    public function fetch_pemeliharaan() {
        $this->rbac_lib->require_access('aset/pemeliharaan', 'view');

        $result = $this->aset_model->get_pemeliharaan_datatable([
            'draw' => $this->input->get('draw', TRUE),
            'start' => $this->input->get('start', TRUE),
            'length' => $this->input->get('length', TRUE),
            'search' => ['value' => $this->input->get('search[value]', TRUE)],
            'status' => $this->input->get('status', TRUE) ?: 'all',
            'jenis' => $this->input->get('jenis', TRUE) ?: 'all',
        ]);

        return $this->_json([
            'status' => 'success',
            'draw' => $result['draw'],
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
            'data' => $result['data'],
        ]);
    }

    public function simpan_pemeliharaan() {
        $id = $this->input->post('id');
        $isEdit = !empty($id);

        if (!$this->rbac_lib->has_access('aset/pemeliharaan', $isEdit ? 'edit' : 'create')) {
            return $this->_error('Anda tidak memiliki hak akses untuk mencatat pemeliharaan.', 403);
        }

        $this->form_validation->set_rules('aset_id', 'Aset', 'required|integer');
        $this->form_validation->set_rules('jenis', 'Jenis Pemeliharaan', 'required|trim');
        $this->form_validation->set_rules('status', 'Status', 'required|trim');

        if ($this->form_validation->run() === FALSE) {
            return $this->_error(validation_errors(' ', ' '));
        }

        $result = $this->aset_model->simpan_pemeliharaan([
            'aset_id' => (int)$this->input->post('aset_id'),
            'jenis' => $this->input->post('jenis', TRUE),
            'tanggal_rencana' => $this->input->post('tanggal_rencana', TRUE),
            'tanggal_realisasi' => $this->input->post('tanggal_realisasi', TRUE),
            'pelaksana' => $this->input->post('pelaksana', TRUE) ?: 'internal',
            'nama_pelaksana' => $this->input->post('nama_pelaksana', TRUE),
            'uraian_pekerjaan' => $this->input->post('uraian_pekerjaan', TRUE),
            'suku_cadang' => $this->input->post('suku_cadang', TRUE),
            'biaya' => $this->input->post('biaya'),
            'hasil' => $this->input->post('hasil', TRUE),
            'status' => $this->input->post('status', TRUE),
            'catatan' => $this->input->post('catatan', TRUE),
        ], $isEdit ? (int)$id : null, $this->rbac_lib->user('full_name'));

        if (!$result['status']) {
            return $this->_error($result['message']);
        }

        $this->_log($isEdit ? 'PEMELIHARAAN_UPDATE' : 'PEMELIHARAAN_CREATE', "ID={$result['id']}");
        return $this->_json(['status' => 'success', 'message' => $result['message']]);
    }

    // =====================================================================
    // MUTASI & PENGHAPUSAN
    // =====================================================================

    public function mutasi() {
        $this->rbac_lib->require_access('aset/mutasi', 'view');

        $data = $this->_base_data('Mutasi & Penghapusan Aset', 'aset/mutasi');
        $data['aset_options'] = $this->aset_model->get_aset_options();
        $data['ruangan_ref'] = $this->aset_model->get_referensi('ruangan');
        $data['ringkasan'] = $this->aset_model->ringkasan();

        $this->_render('aset/mutasi', $data);
    }

    public function fetch_mutasi() {
        $this->rbac_lib->require_access('aset/mutasi', 'view');

        $result = $this->aset_model->get_mutasi_datatable([
            'draw' => $this->input->get('draw', TRUE),
            'start' => $this->input->get('start', TRUE),
            'length' => $this->input->get('length', TRUE),
            'search' => ['value' => $this->input->get('search[value]', TRUE)],
            'tipe' => $this->input->get('tipe', TRUE) ?: 'all',
        ]);

        return $this->_json([
            'status' => 'success',
            'draw' => $result['draw'],
            'recordsTotal' => $result['recordsTotal'],
            'recordsFiltered' => $result['recordsFiltered'],
            'data' => $result['data'],
        ]);
    }

    public function proses_mutasi() {
        if (!$this->rbac_lib->has_access('aset/mutasi', 'create')) {
            return $this->_error('Anda tidak memiliki hak akses untuk memproses mutasi aset.', 403);
        }

        $this->form_validation->set_rules('aset_id', 'Aset', 'required|integer');
        $this->form_validation->set_rules('tipe', 'Jenis Mutasi', 'required|trim');
        $this->form_validation->set_rules('tanggal', 'Tanggal', 'required');

        if ($this->form_validation->run() === FALSE) {
            return $this->_error(validation_errors(' ', ' '));
        }

        $result = $this->aset_model->proses_mutasi([
            'aset_id' => (int)$this->input->post('aset_id'),
            'tipe' => $this->input->post('tipe', TRUE),
            'tanggal' => $this->input->post('tanggal', TRUE),
            'ke_ruangan' => $this->input->post('ke_ruangan', TRUE),
            'ke_kondisi' => $this->input->post('ke_kondisi', TRUE),
            'pihak_terkait' => $this->input->post('pihak_terkait', TRUE),
            'nomor_dokumen' => $this->input->post('nomor_dokumen', TRUE),
            'keterangan' => $this->input->post('keterangan', TRUE),
        ], $this->rbac_lib->user('id'), $this->rbac_lib->user('full_name'));

        if (!$result['status']) {
            return $this->_error($result['message']);
        }

        $this->_log('MUTASI', $this->input->post('tipe'));
        return $this->_json(['status' => 'success', 'message' => $result['message']]);
    }

    /**
     * Cetak Kartu Inventaris Barang B untuk satu aset.
     */
    public function cetak_kib($id) {
        $this->rbac_lib->require_access('aset/barang', 'view');

        $row = $this->aset_model->get_by_id((int)$id);
        if (!$row) {
            show_error('Aset tidak ditemukan.', 404, 'Data Tidak Ditemukan');
            return;
        }

        $data['aset'] = $row;
        $data['kop'] = kop_surat_data();
        $data['title'] = 'KIB-B ' . $row['nomor_register'];

        $this->load->view('aset/cetak_kib', $data);
    }
}
