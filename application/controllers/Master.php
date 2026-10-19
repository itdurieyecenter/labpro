<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Master extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->rbac_lib->require_login();
        $this->load->model('lab_model');
        $this->load->model('rbac_model');
    }

    // =========================================================================
    // 1. MASTER DOKTER PENGIRIM
    // =========================================================================
    public function dokter() {
        $this->rbac_lib->require_access('master/dokter', 'view');

        $data['title'] = 'Master Dokter Pengirim - Labkesda Riau';
        $data['active_menu'] = 'master/dokter';
        $data['user'] = $this->rbac_lib->user();
        $data['menu_tree'] = $this->rbac_lib->get_menu_tree();
        $data['can_create'] = $this->rbac_lib->has_access('master/dokter', 'create');
        $data['can_edit'] = $this->rbac_lib->has_access('master/dokter', 'edit');

        $data['dokters'] = $this->db->order_by('nama_dokter', 'ASC')->get('master_dokter')->result_array();

        $this->load->view('layout/header', $data);
        $this->load->view('layout/sidebar', $data);
        $this->load->view('layout/topbar', $data);
        $this->load->view('master/dokter', $data);
        $this->load->view('layout/footer', $data);
    }

    public function save_dokter() {
        $this->rbac_lib->require_access('master/dokter', 'create');

        $id = (int)$this->input->post('id', TRUE);
        $nama = trim($this->input->post('nama_dokter', TRUE));
        $spesialisasi = trim($this->input->post('spesialisasi', TRUE)) ?: 'Dokter Umum';
        $faskes = trim($this->input->post('faskes_asal', TRUE));
        $sip = trim($this->input->post('sip_nip', TRUE));
        $telepon = trim($this->input->post('telepon', TRUE));
        $email = trim($this->input->post('email', TRUE));

        if (empty($nama)) {
            return $this->output->set_status_header(400)->set_output(json_encode(['status' => 'error', 'message' => 'Nama dokter wajib diisi.']));
        }

        $payload = [
            'nama_dokter' => $nama,
            'spesialisasi' => $spesialisasi,
            'faskes_asal' => $faskes,
            'sip_nip' => $sip,
            'telepon' => $telepon,
            'email' => $email,
            'is_active' => 1
        ];

        if ($id > 0) {
            $this->db->where('id', $id)->update('master_dokter', $payload);
            $msg = 'Data dokter berhasil diperbarui.';
        } else {
            $this->db->insert('master_dokter', $payload);
            $msg = 'Dokter pengirim baru berhasil didaftarkan.';
        }

        return $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'success', 'message' => $msg]));
    }

    public function delete_dokter($id) {
        $this->rbac_lib->require_access('master/dokter', 'delete');
        $this->db->where('id', (int)$id)->delete('master_dokter');
        return $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'success', 'message' => 'Dokter berhasil dihapus.']));
    }

    // =========================================================================
    // 2. MASTER NILAI RUJUKAN & NILAI KRITIS (PANIC VALUES)
    // =========================================================================
    public function nilai_rujukan() {
        $this->rbac_lib->require_access('master/nilai_rujukan', 'view');

        $data['title'] = 'Nilai Rujukan & Nilai Kritis Lab - Labkesda Riau';
        $data['active_menu'] = 'master/nilai_rujukan';
        $data['user'] = $this->rbac_lib->user();
        $data['menu_tree'] = $this->rbac_lib->get_menu_tree();
        $data['can_create'] = $this->rbac_lib->has_access('master/nilai_rujukan', 'create');
        $data['can_edit'] = $this->rbac_lib->has_access('master/nilai_rujukan', 'edit');

        // Parameter beserta range rujukannya
        $this->db->select('rr.*, p.parameter_code, p.parameter_name, p.unit, c.category_name, m.method_name');
        $this->db->from('lab_reference_ranges rr');
        $this->db->join('lab_parameters p', 'p.id = rr.parameter_id');
        $this->db->join('lab_categories c', 'c.id = p.category_id', 'left');
        $this->db->join('lab_methods m', 'm.id = p.method_id', 'left');
        $this->db->order_by('c.sort_order', 'ASC');
        $this->db->order_by('p.parameter_name', 'ASC');
        $data['ranges'] = $this->db->get()->result_array();

        $data['parameters'] = $this->db->where('is_active', 1)->order_by('parameter_name', 'ASC')->get('lab_parameters')->result_array();

        $this->load->view('layout/header', $data);
        $this->load->view('layout/sidebar', $data);
        $this->load->view('layout/topbar', $data);
        $this->load->view('master/nilai_rujukan', $data);
        $this->load->view('layout/footer', $data);
    }

    public function save_nilai_rujukan() {
        $this->rbac_lib->require_access('master/nilai_rujukan', 'create');

        $id = (int)$this->input->post('id', TRUE);
        $parameterId = (int)$this->input->post('parameter_id', TRUE);
        $gender = strtoupper(trim($this->input->post('gender', TRUE) ?: 'ALL'));
        if (!in_array($gender, ['ALL', 'L', 'P'])) {
            $gender = 'ALL';
        }
        $ageClass = strtolower(trim($this->input->post('age_classification', TRUE) ?: 'dewasa'));
        $validAges = ['semua_umur', 'neonatus', 'bayi', 'anak', 'remaja', 'dewasa', 'lansia', 'hamil'];
        if (!in_array($ageClass, $validAges)) {
            $ageClass = 'semua_umur';
        }
        $ageLabel = trim($this->input->post('age_label', TRUE)) ?: ucfirst($ageClass);
        $minVal = $this->input->post('min_val', TRUE) !== '' ? (float)$this->input->post('min_val', TRUE) : null;
        $maxVal = $this->input->post('max_val', TRUE) !== '' ? (float)$this->input->post('max_val', TRUE) : null;
        $criticalLow = $this->input->post('critical_low', TRUE) !== '' ? (float)$this->input->post('critical_low', TRUE) : null;
        $criticalHigh = $this->input->post('critical_high', TRUE) !== '' ? (float)$this->input->post('critical_high', TRUE) : null;
        $display = trim($this->input->post('reference_display', TRUE));
        $textNormal = trim($this->input->post('text_normal', TRUE));
        $notes = trim($this->input->post('notes', TRUE));

        if ($parameterId <= 0) {
            return $this->output->set_status_header(400)->set_output(json_encode(['status' => 'error', 'message' => 'Pilih parameter uji terlebih dahulu.']));
        }

        if (empty($display)) {
            if ($minVal !== null && $maxVal !== null) {
                $display = $minVal . ' - ' . $maxVal;
            } elseif (!empty($textNormal)) {
                $display = $textNormal;
            }
        }

        $payload = [
            'parameter_id' => $parameterId,
            'gender' => $gender,
            'age_classification' => $ageClass,
            'age_label' => $ageLabel,
            'min_val' => $minVal,
            'max_val' => $maxVal,
            'critical_low' => $criticalLow,
            'critical_high' => $criticalHigh,
            'reference_display' => $display,
            'text_normal' => !empty($textNormal) ? $textNormal : null,
            'notes' => !empty($notes) ? $notes : null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($id > 0) {
            $this->db->where('id', $id)->update('lab_reference_ranges', $payload);
            $msg = 'Nilai rujukan & rentang kritis berhasil diperbarui.';
        } else {
            $payload['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('lab_reference_ranges', $payload);
            $msg = 'Nilai rujukan baru berhasil ditambahkan.';
        }

        return $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'success', 'message' => $msg]));
    }

    public function delete_nilai_rujukan($id) {
        $this->rbac_lib->require_access('master/nilai_rujukan', 'delete');
        $this->db->where('id', (int)$id)->delete('lab_reference_ranges');
        return $this->output->set_content_type('application/json')->set_output(json_encode(['status' => 'success', 'message' => 'Nilai rujukan berhasil dihapus.']));
    }
}
