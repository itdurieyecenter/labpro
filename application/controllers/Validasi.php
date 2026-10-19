<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Validasi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->rbac_lib->require_login();
        $this->load->model('pendaftaran_model');
        $this->load->model('lab_model');
        $this->load->model('rbac_model');
        $this->load->model('approval_model');
    }

    private function _log($action, $message) {
        log_message('info', sprintf("[VALIDASI_SPPK] USER=%s | ACTION=%s | MSG=%s", $this->rbac_lib->user('username'), $action, $message));
    }

    /**
     * Halaman Utama Validasi & Otorisasi Dokter Spesialis Patologi Klinik (Sp.PK)
     */
    public function index() {
        $this->rbac_lib->require_access('validasi', 'view');

        $data['title'] = 'Validasi & Otorisasi Dokter Sp.PK - Labkesda Riau';
        $data['active_menu'] = 'validasi';
        $data['user'] = $this->rbac_lib->user();
        $data['menu_tree'] = $this->rbac_lib->get_menu_tree();
        
        $data['can_create'] = $this->rbac_lib->has_access('validasi', 'create');
        $data['can_edit'] = $this->rbac_lib->has_access('validasi', 'edit');

        // Statistik otorisasi
        $today = date('Y-m-d');
        $data['stats'] = [
            'siap_validasi' => $this->db->where('status_order', 'validasi')->count_all_results('pendaftaran'),
            'tervalidasi_hari_ini' => $this->db->where('status_order', 'completed')->where('DATE(waktu_hasil_selesai)', $today)->count_all_results('pendaftaran'),
            'total_completed' => $this->db->where('status_order', 'completed')->count_all_results('pendaftaran'),
            'kritis_alert' => $this->db->where('hasil_flag', 'critical')->count_all_results('pendaftaran_detail')
        ];

        $data['dokter_sp_pk'] = $this->rbac_lib->user('full_name') ?: get_setting('dokter_penanggung_jawab', 'dr. Hj. Siti Rahmah, Sp.PK, M.Kes');

        $this->_log('VIEW', 'Menu Validasi & Otorisasi diakses');

        $this->load->view('layout/header', $data);
        $this->load->view('layout/sidebar', $data);
        $this->load->view('layout/topbar', $data);
        $this->load->view('validasi/index', $data);
        $this->load->view('layout/footer', $data);
    }

    /**
     * AJAX Fetcher Order Siap Validasi
     */
    public function fetch() {
        $this->rbac_lib->require_access('validasi', 'view');

        $draw = (int)$this->input->get('draw');
        $start = (int)$this->input->get('start');
        $length = (int)$this->input->get('length');
        $search = $this->input->get('search')['value'] ?? '';

        $filterStatus = $this->input->get('filter_status', TRUE) ?: 'siap'; // siap / completed / all
        $tanggal = $this->input->get('tanggal', TRUE);

        $this->db->from('pendaftaran');

        if ($filterStatus === 'siap') {
            $this->db->where('status_order', 'validasi');
        } elseif ($filterStatus === 'completed') {
            $this->db->where('status_order', 'completed');
        } else {
            $this->db->where_in('status_order', ['validasi', 'completed']);
        }

        if (!empty($tanggal)) {
            $this->db->where('tanggal_pendaftaran', $tanggal);
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
            $details = $this->db->select('nama_item, hasil_nilai, satuan, nilai_rujukan, hasil_flag')
                ->where('pendaftaran_id', (int)$r['id'])
                ->get('pendaftaran_detail')
                ->result_array();

            $totalItems = count($details);
            $filledItems = 0;
            $hasPanic = false;
            $flagsSummary = [];

            foreach ($details as $dt) {
                if ($dt['hasil_nilai'] !== null && $dt['hasil_nilai'] !== '') {
                    $filledItems++;
                }
                if ($dt['hasil_flag'] === 'critical') {
                    $hasPanic = true;
                    $flagsSummary[] = $dt['nama_item'] . ' (CRITICAL)';
                }
            }

            // Ambil progress approval berjenjang
            $approvals = $this->approval_model->get_order_approvals($r['id']);
            $totalSteps = count($approvals);
            $approvedSteps = 0;
            $currentPendingRole = '-';

            foreach ($approvals as $ap) {
                if ($ap['status'] === 'approved') {
                    $approvedSteps++;
                } elseif ($ap['status'] === 'pending' && $currentPendingRole === '-') {
                    $currentPendingRole = $ap['jabatan_approval'];
                }
            }

            $r['no_urut'] = $no++;
            $r['total_items'] = $totalItems;
            $r['filled_items'] = $filledItems;
            $r['has_panic'] = $hasPanic;
            $r['panic_items'] = implode(', ', $flagsSummary);
            $r['total_approval_steps'] = $totalSteps;
            $r['approved_steps'] = $approvedSteps;
            $r['current_pending_role'] = $currentPendingRole;
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
     * Dapatkan Lembar Lengkap Hasil Uji untuk Otorisasi Dokter & Approval Berjenjang
     */
    public function get_detail_validasi($id) {
        $this->rbac_lib->require_access('validasi', 'view');

        $reg = $this->pendaftaran_model->get_by_id($id);
        if (!$reg) {
            return $this->output->set_status_header(404)->set_output(json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']));
        }

        $items = $this->pendaftaran_model->get_hasil_lab($id);
        $approvals = $this->approval_model->get_order_approvals($id);
        $keabsahan = $this->approval_model->check_keabsahan($id);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'data' => [
                    'pendaftaran' => $reg,
                    'items' => $items,
                    'approvals' => $approvals,
                    'keabsahan' => $keabsahan
                ]
            ]));
    }

    /**
     * Proses Otorisasi Klinis Resmi oleh Dokter Sp.PK
     */
    public function otorisasi() {
        $this->rbac_lib->require_access('validasi', 'edit');

        $id = (int)$this->input->post('id', TRUE);
        $dokterPj = trim($this->input->post('dokter_pj', TRUE)) ?: ($this->rbac_lib->user('full_name') ?: 'Dokter Sp.PK');
        $kesimpulan = trim($this->input->post('kesimpulan_klinis', TRUE));
        $catatan = trim($this->input->post('catatan_hasil', TRUE));

        $user = $this->rbac_lib->user();
        $userId = isset($user['id']) ? (int)$user['id'] : null;
        $userName = isset($user['full_name']) ? $user['full_name'] : 'Dokter Sp.PK';

        $pendaftaran = $this->pendaftaran_model->get_by_id($id);
        if (!$pendaftaran) {
            return $this->output->set_status_header(404)->set_output(json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']));
        }

        $now = date('Y-m-d H:i:s');

        $this->db->trans_start();

        // Update status pendaftaran jadi completed
        $this->db->where('id', $id)->update('pendaftaran', [
            'status_order' => 'completed',
            'dokter_pj' => $dokterPj,
            'kesimpulan_saran' => $kesimpulan,
            'catatan' => $catatan,
            'waktu_hasil_selesai' => $now,
            'updated_at' => $now
        ]);

        // Audit Trail Log
        $this->db->insert('pendaftaran_log', [
            'pendaftaran_id' => $id,
            'action' => 'VALIDASI_OTORISASI_SPPK',
            'description' => sprintf("Otorisasi & Validasi Klinis oleh Dokter %s. Status Order: COMPLETED.", $dokterPj),
            'user_id' => $userId,
            'user_name' => $userName
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Gagal menyimpan otorisasi ke database.']));
        }

        $this->_log('OTORISASI', sprintf("Order ID=%d divalidasi oleh Dokter %s", $id, $dokterPj));

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'message' => 'Otorisasi klinis resmi berhasil. Hasil pemeriksaan kini berstatus Selesai (Completed) dan siap dicetak.',
                'waktu_selesai' => $now
            ]));
    }

    /**
     * AJAX Approve Tingkat Tertentu dalam Rantai Approval Berjenjang
     */
    public function approve_step() {
        $this->rbac_lib->require_access('validasi', 'edit');

        $pendaftaranId = (int)$this->input->post('pendaftaran_id', TRUE);
        $stepUrutan = (int)$this->input->post('urutan', TRUE);
        $catatan = trim($this->input->post('catatan', TRUE));
        $signatureBase64 = $this->input->post('signature_base64');

        $user = $this->rbac_lib->user();
        $userId = isset($user['id']) ? (int)$user['id'] : null;
        $userName = isset($user['full_name']) ? $user['full_name'] : 'Petugas';

        $tandaTangan = null;
        if (!empty($signatureBase64) && strpos($signatureBase64, 'data:image') === 0) {
            $uploadDir = FCPATH . 'uploads/signatures/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $dataParts = explode(',', $signatureBase64);
            $imgData = base64_decode($dataParts[1] ?? '');
            if (!empty($imgData)) {
                $filename = 'appr_' . $pendaftaranId . '_step' . $stepUrutan . '_' . time() . '.png';
                file_put_contents($uploadDir . $filename, $imgData);
                $tandaTangan = 'uploads/signatures/' . $filename;
            }
        } elseif (!empty($_FILES['tanda_tangan_file']['name'])) {
            $uploadDir = FCPATH . 'uploads/signatures/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $config['upload_path']   = $uploadDir;
            $config['allowed_types'] = 'png|jpg|jpeg';
            $config['max_size']      = 2048;
            $config['file_name']     = 'appr_' . $pendaftaranId . '_step' . $stepUrutan . '_' . time();
            $this->load->library('upload', $config);
            if ($this->upload->do_upload('tanda_tangan_file')) {
                $uploadData = $this->upload->data();
                $tandaTangan = 'uploads/signatures/' . $uploadData['file_name'];
            }
        }

        $approvalData = [
            'catatan' => $catatan
        ];
        if ($tandaTangan !== null) {
            $approvalData['tanda_tangan'] = $tandaTangan;
        }

        // Simpan kesimpulan saran / ekspertise klinis jika ada
        $kesimpulan = trim($this->input->post('kesimpulan_klinis', TRUE));
        if (!empty($kesimpulan)) {
            $this->db->where('id', $pendaftaranId)->update('pendaftaran', [
                'kesimpulan_saran' => $kesimpulan,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        $res = $this->approval_model->approve_step($pendaftaranId, $stepUrutan, $approvalData, $userId, $userName);

        if (!$res['status']) {
            return $this->output->set_status_header(400)->set_output(json_encode($res));
        }

        return $this->output->set_content_type('application/json')->set_output(json_encode($res));
    }

    /**
     * Simpan Ekspertise Klinis & Catatan Teknis tanpa mengubah status order
     */
    public function save_catatan_klinis() {
        $this->rbac_lib->require_access('validasi', 'edit');

        $pendaftaranId = (int)$this->input->post('id', TRUE);
        $kesimpulan = trim($this->input->post('kesimpulan_klinis', TRUE));
        $catatanTeknis = trim($this->input->post('catatan_hasil', TRUE));
        $dokterPj = trim($this->input->post('dokter_pj', TRUE));

        $pendaftaran = $this->pendaftaran_model->get_by_id($pendaftaranId);
        if (!$pendaftaran) {
            return $this->output->set_status_header(404)->set_output(json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']));
        }

        $update = [
            'kesimpulan_saran' => $kesimpulan,
            'catatan' => $catatanTeknis,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        if (!empty($dokterPj)) {
            $update['dokter_pj'] = $dokterPj;
        }

        $this->db->where('id', $pendaftaranId)->update('pendaftaran', $update);

        return $this->output->set_content_type('application/json')->set_output(json_encode([
            'status' => 'success',
            'message' => 'Ekspertise klinis dan catatan lab berhasil diperbarui.'
        ]));
    }
}
