<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pegawai extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->rbac_lib->require_login();
        $this->load->model('pegawai_model');
        $this->load->library('form_validation');
    }

    private function _log($action, $message, $level = 'info') {
        log_message($level, sprintf("[PEGAWAI-CTRL] USER=%s | ACTION=%s | MSG=%s", $this->rbac_lib->user('username'), $action, $message));
    }

    public function index() {
        $this->rbac_lib->require_access('pegawai', 'view');

        $data['title'] = 'Master Data Pegawai & Nakes ASN - UPT Laboratorium Kesehatan Provinsi Riau';
        $data['active_menu'] = 'pegawai';
        $data['user'] = $this->rbac_lib->user();
        $data['menu_tree'] = $this->rbac_lib->get_menu_tree();
        $data['can_create'] = $this->rbac_lib->has_access('pegawai', 'create');
        $data['can_edit'] = $this->rbac_lib->has_access('pegawai', 'edit');
        $data['can_delete'] = $this->rbac_lib->has_access('pegawai', 'delete');

        $data['pangkat_ref'] = $this->pegawai_model->get_pangkat_reference();
        $data['seksi_ref'] = $this->pegawai_model->get_seksi_reference();
        $data['form_ref'] = $this->pegawai_model->get_all_form_references();
        $data['summary'] = $this->pegawai_model->count_summary();

        $this->load->view('layout/header', $data);
        $this->load->view('layout/sidebar', $data);
        $this->load->view('layout/topbar', $data);
        $this->load->view('pegawai/index', $data);
        $this->load->view('layout/footer', $data);
    }

    public function fetch() {
        $this->rbac_lib->require_access('pegawai', 'view');

        $params = [
            'draw' => $this->input->get('draw', TRUE),
            'start' => $this->input->get('start', TRUE),
            'length' => $this->input->get('length', TRUE),
            'search' => [
                'value' => $this->input->get('search[value]', TRUE)
            ],
            'status_pegawai' => $this->input->get('status_pegawai', TRUE) ?: 'all',
            'seksi_ruangan' => $this->input->get('seksi_ruangan', TRUE) ?: 'all',
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

        $result = $this->pegawai_model->get_datatable($params);

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
        $this->rbac_lib->require_access('pegawai', 'view');
        $pegawai = $this->pegawai_model->get_by_id((int)$id);

        if (!$pegawai) {
            return $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Data pegawai tidak ditemukan dalam sistem.'
                ]));
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'data' => $pegawai
            ]));
    }

    public function summary() {
        $this->rbac_lib->require_access('pegawai', 'view');
        $summary = $this->pegawai_model->count_summary();

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'data' => $summary
            ]));
    }

    public function save() {
        $id = $this->input->post('id');
        $isEdit = !empty($id);

        $actionPerm = $isEdit ? 'edit' : 'create';
        if (!$this->rbac_lib->has_access('pegawai', $actionPerm)) {
            return $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki hak akses untuk ' . ($isEdit ? 'mengubah' : 'menambah') . ' data pegawai.'
                ]));
        }

        // Normalisasi NIP (hilangkan spasi atau tanda hubung)
        $rawNip = trim($this->input->post('nip', TRUE));
        $cleanNip = preg_replace('/[^0-9]/', '', $rawNip);
        $_POST['nip'] = $cleanNip;

        $this->form_validation->set_rules('nama_lengkap', 'Nama Lengkap Pegawai', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('nip', 'Nomor Induk Pegawai (NIP)', 'required|trim|numeric|max_length[18]');
        $this->form_validation->set_rules('telepon', 'Nomor HP / WhatsApp', 'trim|max_length[30]');
        $this->form_validation->set_rules('status_pegawai', 'Status Kepegawaian', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('nama_jabatan', 'Nama Jabatan', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('seksi_ruangan', 'Seksi / Penempatan Ruangan', 'required|trim|max_length[150]');

        if ($this->form_validation->run() === FALSE) {
            return $this->output
                ->set_status_header(422)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => validation_errors(' ', ' ')
                ]));
        }

        $nip = $cleanNip;

        // Cek duplikasi NIP
        $existing = $this->pegawai_model->get_by_nip($nip);
        if ($existing && (!$isEdit || (int)$existing['id'] !== (int)$id)) {
            return $this->output
                ->set_status_header(422)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'NIP ' . $nip . ' sudah terdaftar atas nama ' . $existing['nama_lengkap'] . '.'
                ]));
        }

        // Sinkronisasi nama pangkat dari kode golongan jika kosong
        $pangkatRef = $this->pegawai_model->get_pangkat_reference();
        $kodeGol = trim($this->input->post('kode_golongan', TRUE));
        $namaPangkat = trim($this->input->post('nama_pangkat', TRUE));
        if (empty($namaPangkat) && isset($pangkatRef[$kodeGol])) {
            $namaPangkat = $pangkatRef[$kodeGol];
        }

        $data = [
            'nip' => $nip,
            'nik' => trim($this->input->post('nik', TRUE)) ?: null,
            'gelar_depan' => trim($this->input->post('gelar_depan', TRUE)) ?: null,
            'nama_lengkap' => trim($this->input->post('nama_lengkap', TRUE)),
            'gelar_belakang' => trim($this->input->post('gelar_belakang', TRUE)) ?: null,
            'tempat_lahir' => trim($this->input->post('tempat_lahir', TRUE)) ?: null,
            'tanggal_lahir' => $this->input->post('tanggal_lahir', TRUE) ?: null,
            'jenis_kelamin' => $this->input->post('jenis_kelamin', TRUE) ?: 'L',
            'agama' => $this->input->post('agama', TRUE) ?: 'Islam',
            'status_pernikahan' => $this->input->post('status_pernikahan', TRUE) ?: 'Belum Kawin',
            'golongan_darah' => $this->input->post('golongan_darah', TRUE) ?: null,

            'status_pegawai' => $this->input->post('status_pegawai', TRUE) ?: 'PNS',
            'kedudukan_pegawai' => $this->input->post('kedudukan_pegawai', TRUE) ?: 'Aktif',
            'nomor_sk_cpns' => trim($this->input->post('nomor_sk_cpns', TRUE)) ?: null,
            'tmt_cpns' => $this->input->post('tmt_cpns', TRUE) ?: null,
            'nomor_sk_pns' => trim($this->input->post('nomor_sk_pns', TRUE)) ?: null,
            'tmt_pns' => $this->input->post('tmt_pns', TRUE) ?: null,
            'mkg_tahun' => (int)$this->input->post('mkg_tahun'),
            'mkg_bulan' => (int)$this->input->post('mkg_bulan'),

            'kode_golongan' => $kodeGol ?: null,
            'nama_pangkat' => $namaPangkat ?: null,
            'tmt_golongan' => $this->input->post('tmt_golongan', TRUE) ?: null,
            'nomor_sk_kp' => trim($this->input->post('nomor_sk_kp', TRUE)) ?: null,
            'tmt_kgb_terakhir' => $this->input->post('tmt_kgb_terakhir', TRUE) ?: null,
            'tmt_kgb_berikutnya' => $this->input->post('tmt_kgb_berikutnya', TRUE) ?: null,

            'jenis_jabatan' => $this->input->post('jenis_jabatan', TRUE) ?: 'Fungsional Tertentu (JFT)',
            'nama_jabatan' => trim($this->input->post('nama_jabatan', TRUE)),
            'jenjang_jabatan' => trim($this->input->post('jenjang_jabatan', TRUE)) ?: null,
            'tmt_jabatan' => $this->input->post('tmt_jabatan', TRUE) ?: null,
            'kelas_jabatan' => (int)$this->input->post('kelas_jabatan') ?: 7,
            'unit_kerja_induk' => trim($this->input->post('unit_kerja_induk', TRUE)) ?: 'Dinas Kesehatan Provinsi Riau',
            'satuan_kerja' => trim($this->input->post('satuan_kerja', TRUE)) ?: 'UPT Laboratorium Kesehatan Provinsi Riau',
            'seksi_ruangan' => trim($this->input->post('seksi_ruangan', TRUE)) ?: 'Instalasi Laboratorium Klinik',

            'nomor_str' => trim($this->input->post('nomor_str', TRUE)) ?: null,
            'tgl_terbit_str' => $this->input->post('tgl_terbit_str', TRUE) ?: null,
            'masa_berlaku_str' => trim($this->input->post('masa_berlaku_str', TRUE)) ?: 'Seumur Hidup',
            'nomor_sip' => trim($this->input->post('nomor_sip', TRUE)) ?: null,
            'masa_berlaku_sip' => $this->input->post('masa_berlaku_sip', TRUE) ?: null,
            'sertifikasi_khusus' => trim($this->input->post('sertifikasi_khusus', TRUE)) ?: null,

            'tingkat_pendidikan' => $this->input->post('tingkat_pendidikan', TRUE) ?: 'D-III',
            'jurusan' => trim($this->input->post('jurusan', TRUE)) ?: null,
            'nama_institusi' => trim($this->input->post('nama_institusi', TRUE)) ?: null,
            'tahun_lulus' => $this->input->post('tahun_lulus') ? (int)$this->input->post('tahun_lulus') : null,
            'nomor_ijazah' => trim($this->input->post('nomor_ijazah', TRUE)) ?: null,

            'npwp' => trim($this->input->post('npwp', TRUE)) ?: null,
            'no_bpjs_kesehatan' => trim($this->input->post('no_bpjs_kesehatan', TRUE)) ?: null,
            'no_taspen' => trim($this->input->post('no_taspen', TRUE)) ?: null,
            'no_karpeg' => trim($this->input->post('no_karpeg', TRUE)) ?: null,
            'no_karis_karsu' => trim($this->input->post('no_karis_karsu', TRUE)) ?: null,
            'bank_payroll' => trim($this->input->post('bank_payroll', TRUE)) ?: 'Bank Riau Kepri Syariah',
            'nomor_rekening' => trim($this->input->post('nomor_rekening', TRUE)) ?: null,
            'email' => trim($this->input->post('email', TRUE)) ?: null,
            'telepon' => trim($this->input->post('telepon', TRUE)),
            'alamat' => trim($this->input->post('alamat', TRUE)) ?: null
        ];

        if ($isEdit) {
            $this->pegawai_model->update((int)$id, $data);
            $savedId = (int)$id;
            $msg = 'Data pegawai ' . $data['nama_lengkap'] . ' berhasil diperbarui!';
            $this->_log('UPDATE_SUCCESS', "ID={$savedId} | NIP={$nip}");
        } else {
            $savedId = $this->pegawai_model->insert($data);
            $msg = 'Data pegawai baru ' . $data['nama_lengkap'] . ' (NIP: ' . $nip . ') berhasil ditambahkan ke database!';
            $this->_log('CREATE_SUCCESS', "ID={$savedId} | NIP={$nip}");
        }

        $savedRow = $this->pegawai_model->get_by_id($savedId);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'message' => $msg,
                'data' => $savedRow
            ]));
    }

    public function delete($id) {
        if (!$this->rbac_lib->has_access('pegawai', 'delete')) {
            return $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki hak akses untuk menghapus data pegawai.'
                ]));
        }

        $row = $this->pegawai_model->get_by_id((int)$id);
        if (!$row) {
            return $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Pegawai tidak ditemukan.'
                ]));
        }

        $this->pegawai_model->delete((int)$id);
        $this->_log('DELETE_SUCCESS', "ID={$id} | Nama={$row['nama_lengkap']}");

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'message' => 'Data pegawai ' . $row['nama_lengkap'] . ' berhasil dihapus dari sistem.'
            ]));
    }

    /**
     * AJAX: Ambil daftar data referensi per kategori
     */
    public function fetch_referensi() {
        $this->rbac_lib->require_access('pegawai', 'view');
        $kategori = $this->input->get('kategori', TRUE) ?: 'all';
        $data = $this->pegawai_model->get_referensi($kategori);
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'kategori' => $kategori,
                'data' => $data
            ]));
    }

    /**
     * AJAX: Ambil semua referensi 6 kategori sekaligus
     */
    public function get_all_referensi() {
        $this->rbac_lib->require_access('pegawai', 'view');
        $data = $this->pegawai_model->get_all_form_references();
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'references' => $data
            ]));
    }

    /**
     * AJAX: Ambil 1 item referensi untuk edit
     */
    public function get_referensi_item($id) {
        $this->rbac_lib->require_access('pegawai', 'view');
        $item = $this->pegawai_model->get_referensi_by_id((int)$id);
        if (!$item) {
            return $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Data referensi tidak ditemukan.']));
        }
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => 'success', 'data' => $item]));
    }

    /**
     * AJAX: Generate Kode Referensi Otomatis mengikuti pola sistem
     * "User cukup buat sekali saja, selebihnya program mengikuti"
     */
    public function generate_ref_code() {
        $this->rbac_lib->require_access('pegawai', 'view');
        $kategori = trim($this->input->get('kategori', TRUE));
        $nama = trim($this->input->get('nama', TRUE));

        if (empty($kategori)) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Kategori wajib disertakan.']));
        }

        $code = $this->pegawai_model->generate_reference_code($kategori, $nama);
        $nextSort = $this->pegawai_model->get_next_ref_sort_order($kategori);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'kategori' => $kategori,
                'code' => $code,
                'next_sort' => $nextSort
            ]));
    }

    /**
     * AJAX: Generate NIP / Kode Pegawai Otomatis untuk Non-ASN / Kontrak / Honorer
     */
    public function generate_pegawai_nip() {
        $this->rbac_lib->require_access('pegawai', 'view');
        $status = trim($this->input->get('status', TRUE)) ?: 'Non-ASN / BLUD';
        $tglLahir = trim($this->input->get('tanggal_lahir', TRUE));
        $jk = trim($this->input->get('jenis_kelamin', TRUE)) ?: 'L';

        $nip = $this->pegawai_model->generate_pegawai_nip($status, $tglLahir, $jk);
        $nipFormatted = substr($nip, 0, 8) . ' ' . substr($nip, 8, 6) . ' ' . substr($nip, 14, 1) . ' ' . substr($nip, 15, 3);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'nip' => $nip,
                'nip_formatted' => $nipFormatted,
                'status_pegawai' => $status
            ]));
    }

    /**
     * AJAX: Simpan (Tambah / Edit) Referensi Dinamis
     */
    public function save_referensi() {
        if (!$this->rbac_lib->has_access('pegawai', 'create') && !$this->rbac_lib->has_access('pegawai', 'edit')) {
            return $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Akses ditolak.']));
        }

        $id = $this->input->post('id', TRUE);
        $kategori = trim($this->input->post('kategori', TRUE));
        $nama = trim($this->input->post('nama', TRUE));
        $kode = trim($this->input->post('kode', TRUE));
        $keterangan = trim($this->input->post('keterangan', TRUE));
        $sortOrder = $this->input->post('sort_order', TRUE);

        if (empty($kategori) || empty($nama)) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'Kategori dan Nama Referensi wajib diisi.'
                ]));
        }

        // Jika kode kosong, program otomatis menghasilkan kode mengikuti pola kategori
        if (empty($kode)) {
            $kode = $this->pegawai_model->generate_reference_code($kategori, $nama);
        }

        // Jika sort order kosong, otomatis urutan berikutnya
        if (empty($sortOrder) || !is_numeric($sortOrder) || (int)$sortOrder <= 0) {
            $sortOrder = $this->pegawai_model->get_next_ref_sort_order($kategori);
        }

        $payload = [
            'kategori' => $kategori,
            'kode' => $kode ?: null,
            'nama' => $nama,
            'keterangan' => $keterangan ?: null,
            'sort_order' => (int)$sortOrder,
            'is_active' => 1
        ];

        $savedId = $this->pegawai_model->save_referensi($payload, $id ?: null);
        $allRefs = $this->pegawai_model->get_all_form_references();

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'message' => $id ? "Referensi \"{$nama}\" berhasil diperbarui." : "Referensi baru \"{$nama}\" berhasil ditambahkan.",
                'saved_id' => $savedId,
                'saved_item' => array_merge($payload, ['id' => $savedId]),
                'references' => $allRefs
            ]));
    }

    /**
     * AJAX: Hapus Referensi Dinamis
     */
    public function delete_referensi($id) {
        if (!$this->rbac_lib->has_access('pegawai', 'delete')) {
            return $this->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses hapus.']));
        }

        $item = $this->pegawai_model->get_referensi_by_id((int)$id);
        if (!$item) {
            return $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan.']));
        }

        $this->pegawai_model->delete_referensi((int)$id);
        $allRefs = $this->pegawai_model->get_all_form_references();

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'message' => "Referensi \"{$item['nama']}\" berhasil dihapus dari sistem.",
                'references' => $allRefs
            ]));
    }
}
