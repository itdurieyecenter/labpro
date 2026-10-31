<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kasir extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->rbac_lib->require_login();
        $this->load->model('kasir_model');
        $this->load->model('pendaftaran_model');
        $this->load->model('rekening_model');
    }

    private function _log($action, $message) {
        $user = $this->rbac_lib->user();
        log_message('info', sprintf(
            "[KASIR] ACTION=%s | USER_ID=%s | USER=%s | MSG=%s",
            $action,
            isset($user['id']) ? $user['id'] : '-',
            isset($user['username']) ? $user['username'] : '-',
            $message
        ));
    }

    private function _layout($view, $data) {
        $data['user'] = $this->rbac_lib->user();
        $data['menu_tree'] = $this->rbac_lib->get_menu_tree();

        $this->load->view('layout/header', $data);
        $this->load->view('layout/sidebar', $data);
        $this->load->view('layout/topbar', $data);
        $this->load->view($view, $data);
        $this->load->view('layout/footer', $data);
    }

    private function _json($payload, $code = 200) {
        return $this->output
            ->set_status_header($code)
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }

    private function _valid_date($d) {
        $dt = DateTime::createFromFormat('Y-m-d', (string)$d);
        return $dt && $dt->format('Y-m-d') === $d;
    }

    /**
     * Antrian Kasir: tagihan pasien/perusahaan yang terdaftar dan siap dibayar
     */
    public function index() {
        $this->rbac_lib->require_access('kasir', 'view');

        $status = $this->input->get('status', TRUE) ?: 'UNPAID';
        if (!in_array($status, ['UNPAID', 'PAID', 'GUARANTEED', 'all'], true)) {
            $status = 'UNPAID';
        }
        $tanggal = $this->input->get('tanggal', TRUE);
        $tanggal = ($tanggal !== null && $tanggal !== '' && $this->_valid_date($tanggal)) ? $tanggal : '';
        $tipe = $this->input->get('tipe_pasien', TRUE) ?: 'all';
        $search = trim((string)$this->input->get('q', TRUE));

        $user = $this->rbac_lib->user();

        $data['title'] = 'Kasir - Antrian Pembayaran - Labkesda Riau';
        $data['active_menu'] = 'kasir';
        $data['can_edit'] = $this->rbac_lib->has_access('kasir', 'edit');
        $data['status'] = $status;
        $data['tanggal'] = $tanggal;
        $data['tipe_pasien'] = $tipe;
        $data['search'] = $search;
        $data['summary'] = $this->kasir_model->get_summary($user['id'] ?? 0);
        $data['rows'] = $this->kasir_model->get_antrian([
            'status' => $status,
            'tanggal' => $tanggal,
            'tipe_pasien' => $tipe,
            'search' => $search,
        ]);
        $data['akun_bayar_list'] = $this->rekening_model->get_all_akun_bayar(['is_active' => 1]);
        $data['akun_piutang_list'] = $this->rekening_model->get_all_akun_piutang(['is_active' => 1]);

        $this->_log('VIEW_ANTRIAN', 'Antrian kasir diakses. Status=' . $status);
        $this->_layout('kasir/index', $data);
    }

    /**
     * AJAX proses pembayaran (tunai/non-tunai -> PAID) atau penjaminan piutang (-> GUARANTEED)
     */
    public function proses() {
        $this->rbac_lib->require_access('kasir', 'edit');

        $id = (int)$this->input->post('id', TRUE);
        $jenis = $this->input->post('jenis_transaksi', TRUE) ?: 'bayar';

        $reg = $this->db->select('status_pembayaran')->get_where('pendaftaran', ['id' => $id])->row_array();
        if (!$reg) {
            return $this->_json(['status' => false, 'message' => 'Data pendaftaran tidak ditemukan.'], 404);
        }
        if ($reg['status_pembayaran'] !== 'UNPAID') {
            return $this->_json(['status' => false, 'message' => 'Tagihan ini sudah diproses (status: ' . $reg['status_pembayaran'] . ').'], 409);
        }

        $user = $this->rbac_lib->user();
        $userId = isset($user['id']) ? (int)$user['id'] : null;
        $userName = isset($user['full_name']) ? $user['full_name'] : ($user['username'] ?? 'Kasir');

        if ($jenis === 'piutang') {
            $result = $this->pendaftaran_model->validasi_penjaminan_pks($id, [
                'akun_piutang_id' => (int)$this->input->post('akun_piutang_id', TRUE),
                'nomor_pks_spk' => trim($this->input->post('nomor_pks_spk', TRUE)),
                'instansi_penjamin' => trim($this->input->post('instansi_penjamin', TRUE)),
                'pic_penjamin' => trim($this->input->post('pic_penjamin', TRUE)),
                'catatan_penjamin' => trim($this->input->post('catatan_penjamin', TRUE)),
            ], $userId, $userName);
        } else {
            $result = $this->pendaftaran_model->validasi_pembayaran_kasir($id, [
                'akun_bayar_id' => (int)$this->input->post('akun_bayar_id', TRUE),
                'metode_pembayaran' => $this->input->post('metode_pembayaran', TRUE) ?: 'Cash',
                'nominal_bayar' => (float)$this->input->post('nominal_bayar', TRUE),
            ], $userId, $userName);
        }

        if (empty($result['status'])) {
            return $this->_json($result, 400);
        }

        $this->_log('PROSES_' . strtoupper($jenis), sprintf('ID=%d', $id));
        return $this->_json($result);
    }

    /**
     * Cetak kuitansi (izin cukup modul kasir)
     */
    public function cetak_kuitansi($id) {
        $this->rbac_lib->require_access('kasir', 'view');

        $data['reg'] = $this->pendaftaran_model->get_by_id($id);
        if (!$data['reg']) {
            show_404();
        }
        $this->load->view('pendaftaran/cetak_kuitansi', $data);
    }

    /**
     * Closing Pendapatan Harian
     */
    public function closing() {
        $this->rbac_lib->require_access('kasir/closing', 'view');

        $tanggal = $this->input->get('tanggal', TRUE);
        if (!$tanggal || !$this->_valid_date($tanggal)) {
            $tanggal = date('Y-m-d');
        }
        $user = $this->rbac_lib->user();
        $kasirId = (int)($user['id'] ?? 0);

        $data['title'] = 'Closing Kasir Harian - Labkesda Riau';
        $data['active_menu'] = 'kasir/closing';
        $data['can_create'] = $this->rbac_lib->has_access('kasir/closing', 'create');
        $data['tanggal'] = $tanggal;
        $data['preview'] = $this->kasir_model->closing_preview($tanggal, $kasirId);
        $data['history'] = $this->kasir_model->get_closing_history($this->rbac_lib->role_id() == 1 ? null : $kasirId);

        $this->_log('VIEW_CLOSING', 'Closing kasir diakses. Tanggal=' . $tanggal);
        $this->_layout('kasir/closing', $data);
    }

    /**
     * AJAX simpan closing harian
     */
    public function simpan_closing() {
        $this->rbac_lib->require_access('kasir/closing', 'create');

        $tanggal = $this->input->post('tanggal', TRUE);
        if (!$tanggal || !$this->_valid_date($tanggal)) {
            return $this->_json(['status' => false, 'message' => 'Tanggal closing tidak valid.'], 400);
        }
        if ($tanggal > date('Y-m-d')) {
            return $this->_json(['status' => false, 'message' => 'Tanggal closing tidak boleh di masa depan.'], 400);
        }

        $uangFisikRaw = $this->input->post('uang_fisik_tunai', TRUE);
        if ($uangFisikRaw === null || $uangFisikRaw === '' || !is_numeric($uangFisikRaw) || (float)$uangFisikRaw < 0) {
            return $this->_json(['status' => false, 'message' => 'Uang tunai fisik wajib diisi (minimal 0).'], 400);
        }

        $user = $this->rbac_lib->user();
        $kasirId = (int)($user['id'] ?? 0);
        $kasirNama = isset($user['full_name']) ? $user['full_name'] : ($user['username'] ?? 'Kasir');

        $result = $this->kasir_model->do_closing(
            $tanggal, $kasirId, $kasirNama, (float)$uangFisikRaw,
            trim((string)$this->input->post('catatan', TRUE))
        );

        if (empty($result['status'])) {
            return $this->_json($result, 400);
        }

        $this->_log('SIMPAN_CLOSING', sprintf('%s | Tanggal=%s', $result['nomor_closing'], $tanggal));
        return $this->_json($result);
    }

    /**
     * Cetak lembar closing kasir
     */
    public function cetak_closing($id) {
        $this->rbac_lib->require_access('kasir/closing', 'view');

        $closing = $this->kasir_model->get_closing((int)$id);
        if (!$closing) {
            show_404();
        }
        if ($this->rbac_lib->role_id() != 1 && (int)$closing['kasir_id'] !== (int)$this->rbac_lib->user('id')) {
            show_error('Akses ditolak. Closing ini milik kasir lain.', 403, '403 Forbidden');
        }

        $data['c'] = $closing;
        $data['instansi'] = get_setting('nama_instansi', 'UPT Laboratorium Kesehatan Provinsi Riau');
        $this->load->view('kasir/cetak_closing', $data);
    }
}
