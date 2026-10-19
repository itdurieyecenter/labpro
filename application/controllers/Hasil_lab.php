<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hasil_lab extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->rbac_lib->require_login();
        $this->load->model('pendaftaran_model');
        $this->load->model('lab_model');
        $this->load->model('rbac_model');
    }

    private function _log($action, $message) {
        log_message('info', sprintf("[HASIL_LAB] USER=%s | ACTION=%s | MSG=%s", $this->rbac_lib->user('username'), $action, $message));
    }

    /**
     * Halaman Utama Modul Input Hasil Laboratorium (Khusus Analis Kesehatan / ATLM)
     */
    public function index() {
        $this->rbac_lib->require_access('hasil_lab', 'view');

        $data['title'] = 'Input & Pengerjaan Hasil Lab - Labkesda Riau';
        $data['active_menu'] = 'hasil_lab';
        $data['user'] = $this->rbac_lib->user();
        $data['menu_tree'] = $this->rbac_lib->get_menu_tree();
        
        $data['can_create'] = $this->rbac_lib->has_access('hasil_lab', 'create');
        $data['can_edit'] = $this->rbac_lib->has_access('hasil_lab', 'edit');

        // Summary khusus analis
        $today = date('Y-m-d');
        $data['stats'] = [
            'total_processing' => $this->db->where_in('status_order', ['sampling', 'processing', 'validasi'])->count_all_results('pendaftaran'),
            'belum_diinput' => $this->db->where_in('status_order', ['sampling', 'processing'])->count_all_results('pendaftaran'),
            'siap_validasi' => $this->db->where('status_order', 'validasi')->count_all_results('pendaftaran'),
            'selesai_hari_ini' => $this->db->where('status_order', 'completed')->where('DATE(waktu_hasil_selesai)', $today)->count_all_results('pendaftaran'),
            'kritis_count' => $this->db->where('hasil_flag', 'critical')->count_all_results('pendaftaran_detail')
        ];

        $data['categories'] = $this->db->where('is_active', 1)->order_by('sort_order', 'ASC')->get('lab_categories')->result_array();
        $data['default_analis'] = $this->rbac_lib->user('full_name') ?: 'Analis Lab';
        $data['default_dokter_pj'] = get_setting('dokter_penanggung_jawab', 'dr. Hj. Siti Rahmah, Sp.PK, M.Kes');

        $this->_log('VIEW', 'Halaman Input Hasil Laboratorium diakses');

        $this->load->view('layout/header', $data);
        $this->load->view('layout/sidebar', $data);
        $this->load->view('layout/topbar', $data);
        $this->load->view('hasil_lab/index', $data);
        $this->load->view('layout/footer', $data);
    }

    /**
     * AJAX DataTables Fetcher untuk Analis Lab
     */
    public function fetch() {
        $this->rbac_lib->require_access('hasil_lab', 'view');

        $draw = (int)$this->input->get('draw');
        $start = (int)$this->input->get('start');
        $length = (int)$this->input->get('length');
        $search = $this->input->get('search')['value'] ?? '';

        $statusOrder = $this->input->get('status_order', TRUE) ?: 'processing';
        $kategori = $this->input->get('kategori', TRUE);
        $tanggal = $this->input->get('tanggal', TRUE);

        // Query Dasar: Spesimen yang sudah masuk lab (status sampling, processing, validasi, atau completed)
        $this->db->from('pendaftaran');
        if ($statusOrder === 'processing') {
            $this->db->where_in('status_order', ['sampling', 'processing']);
        } elseif ($statusOrder === 'validasi') {
            $this->db->where('status_order', 'validasi');
        } elseif ($statusOrder === 'completed') {
            $this->db->where('status_order', 'completed');
        } elseif ($statusOrder === 'all') {
            $this->db->where_in('status_order', ['sampling', 'processing', 'validasi', 'completed']);
        } else {
            $this->db->where('status_order', $statusOrder);
        }

        if (!empty($tanggal)) {
            $this->db->where('tanggal_pendaftaran', $tanggal);
        }

        if (!empty($kategori) && $kategori !== 'all') {
            $this->db->where('kategori_pemeriksaan', $kategori);
        }

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('nomor_registrasi', $search);
            $this->db->or_like('barcode_sampel', $search);
            $this->db->or_like('nama_pasien', $search);
            $this->db->or_like('nomor_rm', $search);
            $this->db->or_like('dokter_pengirim', $search);
            $this->db->or_like('instansi_pengirim', $search);
            $this->db->group_end();
        }

        $recordsFiltered = $this->db->count_all_results('', FALSE);

        $this->db->order_by('id', 'DESC');
        if ($length > 0) {
            $this->db->limit($length, $start);
        }
        $query = $this->db->get();
        $rows = $query->result_array();

        $data = [];
        $no = $start + 1;

        foreach ($rows as $r) {
            // Ambil rincian ringkas parameter uji
            $details = $this->db->select('nama_item, hasil_nilai, satuan, hasil_flag, status_pengujian')
                ->where('pendaftaran_id', (int)$r['id'])
                ->get('pendaftaran_detail')
                ->result_array();

            $totalItems = count($details);
            $filledItems = 0;
            $hasPanic = false;
            $paramNames = [];

            foreach ($details as $dt) {
                if ($dt['hasil_nilai'] !== null && $dt['hasil_nilai'] !== '') {
                    $filledItems++;
                }
                if ($dt['hasil_flag'] === 'critical') {
                    $hasPanic = true;
                }
                if (count($paramNames) < 3) {
                    $paramNames[] = $dt['nama_item'];
                }
            }

            $moreCount = $totalItems - count($paramNames);
            $paramSummary = implode(', ', $paramNames) . ($moreCount > 0 ? " (+{$moreCount} lainnya)" : '');

            $r['no_urut'] = $no++;
            $r['total_items'] = $totalItems;
            $r['filled_items'] = $filledItems;
            $r['has_panic'] = $hasPanic;
            $r['param_summary'] = $paramSummary;
            $r['tanggal_format'] = date('d/m/Y', strtotime($r['tanggal_pendaftaran']));
            
            $data[] = $r;
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'draw' => $draw,
                'recordsTotal' => $recordsFiltered,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data
            ]));
    }

    /**
     * Dapatkan Form Parameter & Hasil Lab untuk Pengerjaan Analis
     */
    public function get_hasil($id) {
        $this->rbac_lib->require_access('hasil_lab', 'view');
        $reg = $this->pendaftaran_model->get_by_id($id);
        if (!$reg) {
            return $this->output->set_status_header(404)->set_output(json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']));
        }

        $items = $this->pendaftaran_model->get_hasil_lab($id);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'data' => [
                    'pendaftaran' => $reg,
                    'items' => $items
                ]
            ]));
    }

    /**
     * Simpan Hasil Pemeriksaan Lab dari Analis
     */
    public function save_hasil() {
        $this->rbac_lib->require_access('hasil_lab', 'edit');

        $id = (int)$this->input->post('id', TRUE);
        $statusOrder = $this->input->post('status_order', TRUE) ?: 'validasi';
        $analisNama = trim($this->input->post('analis_nama', TRUE)) ?: ($this->rbac_lib->user('full_name') ?: 'Analis Lab');
        $dokterPj = trim($this->input->post('dokter_pj', TRUE)) ?: get_setting('dokter_penanggung_jawab', 'dr. Hj. Siti Rahmah, Sp.PK, M.Kes');
        $catatanHasil = trim($this->input->post('catatan_hasil', TRUE));
        $kesimpulan = trim($this->input->post('kesimpulan_klinis', TRUE));

        $results = $this->input->post('results'); // array of [detail_id, hasil, flag_kritis, catatan_pemeriksaan]

        if (empty($results) || !is_array($results)) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Tidak ada parameter uji yang diinput.']));
        }

        $user = $this->rbac_lib->user();
        $userId = isset($user['id']) ? (int)$user['id'] : null;
        $userName = isset($user['full_name']) ? $user['full_name'] : 'Analis Lab';

        $metadata = [
            'status_order' => $statusOrder,
            'analis_nama' => $analisNama,
            'dokter_pj' => $dokterPj,
            'catatan_hasil' => $catatanHasil,
            'kesimpulan_saran' => $kesimpulan
        ];

        $res = $this->pendaftaran_model->save_lab_results($id, $results, $metadata, $userId, $userName);

        if (!$res['status']) {
            return $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode($res));
        }

        $this->_log('SAVE_HASIL', sprintf("Order ID=%d disimpan oleh Analis %s | Status=%s", $id, $analisNama, $statusOrder));

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($res));
    }
}
