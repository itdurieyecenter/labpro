<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pendaftaran extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->rbac_lib->require_login();
        $this->load->model('pendaftaran_model');
        $this->load->model('client_model');
        $this->load->model('rekening_model');
        $this->load->model('pengaturan_model');
    }

    private function _log($action, $message, $level = 'info') {
        $user = $this->rbac_lib->user();
        $logMsg = sprintf(
            "[PENDAFTARAN-LIS] ACTION=%s | USER_ID=%s | USER=%s | ROLE=%s | MSG=%s",
            $action,
            isset($user['id']) ? $user['id'] : '-',
            isset($user['username']) ? $user['username'] : '-',
            isset($user['role_name']) ? $user['role_name'] : '-',
            $message
        );
        log_message($level, $logMsg);
    }

    /**
     * Halaman Utama Pendaftaran & Order LIS
     */
    public function index() {
        $this->rbac_lib->require_access('pendaftaran', 'view');

        $data['title'] = 'Pendaftaran & Order Pemeriksaan LIS - Labkesda Riau';
        $data['active_menu'] = 'pendaftaran';
        $data['user'] = $this->rbac_lib->user();
        $data['menu_tree'] = $this->rbac_lib->get_menu_tree();
        
        $data['can_create'] = $this->rbac_lib->has_access('pendaftaran', 'create');
        $data['can_edit'] = $this->rbac_lib->has_access('pendaftaran', 'edit');
        $data['can_delete'] = $this->rbac_lib->has_access('pendaftaran', 'delete');

        // Ringkasan metrik statistik
        $data['summary'] = $this->pendaftaran_model->get_summary_stats();

        // Kategori pemeriksaan lab untuk filter & form
        $data['categories'] = $this->db->where('is_active', 1)->order_by('sort_order', 'ASC')->get('lab_categories')->result_array();

        // Akun Bayar Kasir (COA Kas & Bank) & Akun Piutang Mitra (COA Piutang)
        $data['akun_bayar_list'] = $this->rekening_model->get_all_akun_bayar(['is_active' => 1]);
        $data['akun_piutang_list'] = $this->rekening_model->get_all_akun_piutang(['is_active' => 1]);
        $data['default_dokter_pj'] = get_setting('dokter_penanggung_jawab', 'dr. Hj. Siti Rahmah, Sp.PK, M.Kes');

        $this->_log('VIEW_INDEX', 'Halaman Pendaftaran & Order LIS diakses.');

        $this->load->view('layout/header', $data);
        $this->load->view('layout/sidebar', $data);
        $this->load->view('layout/topbar', $data);
        $this->load->view('pendaftaran/index', $data);
        $this->load->view('layout/footer', $data);
    }

    /**
     * Antrian Sampling & Pengambilan Spesimen (Alur 3: PENGUJIAN SAMPEL)
     */
    public function antrian_sampling() {
        $this->rbac_lib->require_access('pendaftaran', 'view');

        $data['title'] = 'Antrian Sampling & Spesimen Lab - Labkesda Riau';
        $data['active_menu'] = 'pendaftaran/antrian_sampling';
        $data['user'] = $this->rbac_lib->user();
        $data['menu_tree'] = $this->rbac_lib->get_menu_tree();
        
        $data['can_create'] = $this->rbac_lib->has_access('pendaftaran', 'create');
        $data['can_edit'] = $this->rbac_lib->has_access('pendaftaran', 'edit');
        $data['can_delete'] = $this->rbac_lib->has_access('pendaftaran', 'delete');

        $data['summary'] = $this->pendaftaran_model->get_summary_stats();
        $data['categories'] = $this->db->where('is_active', 1)->order_by('sort_order', 'ASC')->get('lab_categories')->result_array();
        $data['akun_bayar_list'] = $this->rekening_model->get_all_akun_bayar(['is_active' => 1]);
        $data['akun_piutang_list'] = $this->rekening_model->get_all_akun_piutang(['is_active' => 1]);
        $data['default_dokter_pj'] = get_setting('dokter_penanggung_jawab', 'dr. Hj. Siti Rahmah, Sp.PK, M.Kes');
        $data['is_sampling_queue'] = true;

        $this->_log('VIEW_SAMPLING', 'Antrian Sampling Lab diakses.');

        $this->load->view('layout/header', $data);
        $this->load->view('layout/sidebar', $data);
        $this->load->view('layout/topbar', $data);
        $this->load->view('pendaftaran/index', $data);
        $this->load->view('layout/footer', $data);
    }

    /**
     * AJAX DataTables Server-Side Fetcher
     */
    public function fetch() {
        $this->rbac_lib->require_access('pendaftaran', 'view');

        $params = [
            'start' => $this->input->get('start', TRUE),
            'length' => $this->input->get('length', TRUE),
            'search' => $this->input->get('search[value]', TRUE),
            'tipe_pasien' => $this->input->get('tipe_pasien', TRUE),
            'status_bayar' => $this->input->get('status_bayar', TRUE),
            'status_order' => $this->input->get('status_order', TRUE),
            'tanggal' => $this->input->get('tanggal', TRUE)
        ];

        $result = $this->pendaftaran_model->get_datatables($params);

        // Tambah no_urut
        $start = isset($params['start']) ? (int)$params['start'] : 0;
        foreach ($result['data'] as $idx => &$row) {
            $row['no_urut'] = $start + $idx + 1;
            $row['tanggal_format'] = date('d/m/Y', strtotime($row['tanggal_pendaftaran'])) . ' ' . substr($row['jam_pendaftaran'], 0, 5);
            $row['total_format'] = 'Rp ' . number_format($row['total_biaya'], 0, ',', '.');
        }

        $response = [
            'draw' => (int)$this->input->get('draw', TRUE),
            'recordsTotal' => $result['total'],
            'recordsFiltered' => $result['filtered'],
            'data' => $result['data']
        ];

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    /**
     * AJAX Ambil Detail Registrasi & Order
     */
    public function get($id) {
        $this->rbac_lib->require_access('pendaftaran', 'view');

        $data = $this->pendaftaran_model->get_by_id($id);
        if (!$data) {
            return $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Data pendaftaran tidak ditemukan.']));
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => 'success', 'data' => $data]));
    }

    /**
     * AJAX Ambil Nomor Registrasi, Billing & Barcode Otomatis
     */
    public function get_next_codes() {
        $this->rbac_lib->require_access('pendaftaran', 'view');

        $codes = [
            'nomor_registrasi' => $this->pendaftaran_model->generate_reg_number(),
            'nomor_billing' => $this->pendaftaran_model->generate_billing_number(),
            'barcode_sampel' => $this->pendaftaran_model->generate_sample_barcode(),
            'tanggal' => date('Y-m-d'),
            'jam' => date('H:i')
        ];

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => 'success', 'codes' => $codes]));
    }

    /**
     * AJAX Autocomplete Pencarian Pasien / Klien
     */
    public function search_clients() {
        $query = $this->input->get('q', TRUE);
        $type = $this->input->get('type', TRUE);

        $results = $this->pendaftaran_model->search_clients($query, $type);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => 'success', 'data' => $results]));
    }

    /**
     * AJAX Tambah Klien Cepat Langsung dari Modal Pendaftaran
     */
    public function save_quick_client() {
        $this->rbac_lib->require_access('pendaftaran', 'create');
        $this->load->model('client_model');

        $clientType = $this->input->post('client_type', TRUE) ?: 'perorangan';
        $name = trim($this->input->post('name', TRUE));
        $phone = trim($this->input->post('phone', TRUE));

        if (empty($name)) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Nama Klien / Pasien / Perusahaan wajib diisi.']));
        }

        if (empty($phone)) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Nomor Telepon / WhatsApp wajib diisi.']));
        }

        $clientCode = $this->client_model->generate_code($clientType);

        $data = [
            'client_type' => $clientType,
            'client_code' => $clientCode,
            'name' => $name,
            'identity_number' => ($this->input->post('identity_number', TRUE) ?: ($this->input->post('identity_number_corp', TRUE) ?: null)),
            'gender' => $this->input->post('gender', TRUE) ?: null,
            'birth_date' => $this->input->post('birth_date', TRUE) ?: null,
            'blood_type' => $this->input->post('blood_type', TRUE) ?: null,
            'pic_name' => $this->input->post('pic_name', TRUE) ?: null,
            'pic_phone' => $this->input->post('pic_phone', TRUE) ?: null,
            'phone' => $phone,
            'email' => $this->input->post('email', TRUE) ?: null,
            'address' => $this->input->post('address', TRUE) ?: null,
            'contract_discount' => (float)$this->input->post('contract_discount'),
            'payment_term' => $this->input->post('payment_term', TRUE) ?: 'Tunai',
            'status' => 'active',
            'notes' => $this->input->post('notes', TRUE) ?: null,
        ];

        if ($clientType === 'perusahaan') {
            $data['gender'] = null;
            $data['birth_date'] = null;
            $data['blood_type'] = null;
        } else {
            $data['pic_name'] = null;
            $data['pic_phone'] = null;
            $data['contract_discount'] = 0.00;
        }

        $savedId = $this->client_model->insert($data);
        $client = $this->client_model->get_by_id($savedId);

        $this->_log('CREATE_QUICK_CLIENT', sprintf("ID=%d | Kode=%s | Nama=%s | Tipe=%s", $savedId, $clientCode, $name, $clientType));

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'message' => 'Klien ' . $name . ' (' . $clientCode . ') berhasil didaftarkan.',
                'data' => $client
            ]));
    }

    /**
     * AJAX Dapatkan Tarif & Template Lab Berdasarkan Kelompok Tarif
     */
    public function get_tariffs() {
        $tariffGroup = $this->input->get('tariff_group', TRUE) ?: 'umum';
        if (!in_array($tariffGroup, ['umum', 'perusahaan', 'bpjs', 'asuransi'])) {
            $tariffGroup = 'umum';
        }

        $tariffs = $this->pendaftaran_model->get_available_tariffs($tariffGroup);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => 'success', 'data' => $tariffs]));
    }

    /**
     * AJAX Simpan Pendaftaran & Order Baru (Alur 1)
     */
    public function save() {
        $this->rbac_lib->require_access('pendaftaran', 'create');

        $user = $this->rbac_lib->user();
        $userId = isset($user['id']) ? (int)$user['id'] : null;
        $userName = isset($user['full_name']) ? $user['full_name'] : ($user['username'] ?? 'Petugas');

        // Validasi input wajib
        $namaPasien = trim($this->input->post('nama_pasien', TRUE));
        $tipePasien = trim($this->input->post('tipe_pasien', TRUE));
        $telepon = trim($this->input->post('telepon', TRUE));
        $alurPembayaran = trim($this->input->post('alur_pembayaran', TRUE));
        $itemsJson = $this->input->post('items_json', FALSE);

        if (empty($namaPasien)) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Nama Pasien / Subjek Uji wajib diisi.']));
        }

        if (empty($telepon)) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Nomor HP / WhatsApp wajib diisi untuk e-Hasil.']));
        }

        $items = json_decode($itemsJson, true);
        if (empty($items) || !is_array($items)) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Pilih minimal 1 parameter uji atau paket pemeriksaan lab.']));
        }

        // Kalkulasi rincian keuangan
        $subtotal = 0;
        $totManajemen = 0;
        $totDokter = 0;
        $totPetugas = 0;
        $totKso = 0;
        $totBhp = 0;

        foreach ($items as $it) {
            $t = (float)($it['tarif'] ?? 0);
            $subtotal += $t;
            $totManajemen += (float)($it['jasa_manajemen'] ?? 0);
            $totDokter += (float)($it['jasa_dokter'] ?? 0);
            $totPetugas += (float)($it['jasa_petugas'] ?? 0);
            $totKso += (float)($it['kso'] ?? 0);
            $totBhp += (float)($it['bhp'] ?? 0);
        }

        $diskonPersen = (float)$this->input->post('diskon_persen', TRUE);
        $diskonNominal = ($subtotal * $diskonPersen) / 100;
        $totalBiaya = max(0, $subtotal - $diskonNominal);

        // Status awal pembayaran tergantung alur yang dipilih saat registrasi
        $statusPembayaran = 'UNPAID';
        $statusOrder = 'registered';
        $metodePembayaran = null;
        $waktuBayar = null;
        $nominalBayar = 0;
        $nominalKembali = 0;
        $nomorKuitansi = null;
        $nomorPks = null;
        $instansiPenjamin = null;
        $picPenjamin = null;

        // Jika alur piutang PKS dan data SPK langsung diisi lengkap
        if ($alurPembayaran === 'piutang_pks') {
            $nomorPks = trim($this->input->post('nomor_pks_spk', TRUE));
            $instansiPenjamin = trim($this->input->post('instansi_penjamin', TRUE));
            $picPenjamin = trim($this->input->post('pic_penjamin', TRUE));

            if (!empty($nomorPks) && !empty($instansiPenjamin)) {
                $statusPembayaran = 'GUARANTEED';
                $statusOrder = 'sampling'; // Langsung aktif barcode sampel
                $metodePembayaran = 'SPK / PKS Mitra';
                $waktuBayar = date('Y-m-d H:i:s');
            }
        }

        $data = [
            'nomor_registrasi' => $this->input->post('nomor_registrasi', TRUE),
            'nomor_billing' => $this->input->post('nomor_billing', TRUE),
            'barcode_sampel' => $this->input->post('barcode_sampel', TRUE),
            'client_id' => $this->input->post('client_id', TRUE) ? (int)$this->input->post('client_id', TRUE) : null,
            'tipe_pasien' => in_array($tipePasien, ['mandiri', 'rujukan_faskes', 'perusahaan_lingkungan']) ? $tipePasien : 'mandiri',
            'tanggal_pendaftaran' => $this->input->post('tanggal_pendaftaran', TRUE) ?: date('Y-m-d'),
            'jam_pendaftaran' => $this->input->post('jam_pendaftaran', TRUE) ?: date('H:i:s'),
            'nama_pasien' => $namaPasien,
            'nik_pasien' => $this->input->post('nik_pasien', TRUE) ?: null,
            'nomor_rm' => $this->input->post('nomor_rm', TRUE) ?: null,
            'jenis_kelamin' => $this->input->post('jenis_kelamin', TRUE) ?: null,
            'tanggal_lahir' => $this->input->post('tanggal_lahir', TRUE) ?: null,
            'usia_tahun' => $this->input->post('usia_tahun', TRUE) ? (int)$this->input->post('usia_tahun', TRUE) : null,
            'usia_bulan' => $this->input->post('usia_bulan', TRUE) ? (int)$this->input->post('usia_bulan', TRUE) : null,
            'telepon' => $telepon,
            'email' => $this->input->post('email', TRUE) ?: null,
            'alamat' => $this->input->post('alamat', TRUE) ?: null,
            'kota_kabupaten' => $this->input->post('kota_kabupaten', TRUE) ?: 'Kota Pekanbaru',
            
            'instansi_pengirim' => $this->input->post('instansi_pengirim', TRUE) ?: null,
            'dokter_pengirim' => $this->input->post('dokter_pengirim', TRUE) ?: null,
            'nomor_surat_pengantar' => $this->input->post('nomor_surat_pengantar', TRUE) ?: null,
            'kategori_pemeriksaan' => $this->input->post('kategori_pemeriksaan', TRUE) ?: 'klinis',
            'jenis_sampel' => $this->input->post('jenis_sampel', TRUE) ?: 'Darah Vena / Serum',
            'diagnosa_klinis' => $this->input->post('diagnosa_klinis', TRUE) ?: null,
            
            'alur_pembayaran' => $alurPembayaran ?: 'mandiri_langsung',
            'kelompok_tarif' => $this->input->post('kelompok_tarif', TRUE) ?: 'umum',
            'subtotal_tarif' => $subtotal,
            'diskon_persen' => $diskonPersen,
            'diskon_nominal' => $diskonNominal,
            'total_biaya' => $totalBiaya,
            'total_jasa_manajemen' => $totManajemen,
            'total_jasa_dokter' => $totDokter,
            'total_jasa_petugas' => $totPetugas,
            'total_kso' => $totKso,
            'total_bhp' => $totBhp,
            
            'status_pembayaran' => $statusPembayaran,
            'metode_pembayaran' => $metodePembayaran,
            'waktu_bayar' => $waktuBayar,
            'nominal_bayar' => $nominalBayar,
            'nominal_kembali' => $nominalKembali,
            'nomor_kuitansi' => $nomorKuitansi,
            
            'nomor_pks_spk' => $nomorPks,
            'instansi_penjamin' => $instansiPenjamin,
            'pic_penjamin' => $picPenjamin,
            'catatan_penjamin' => $this->input->post('catatan_penjamin', TRUE) ?: null,
            'status_penagihan' => 'belum_ditagih',
            
            'status_order' => $statusOrder,
            'catatan' => $this->input->post('catatan', TRUE) ?: null
        ];

        $insertId = $this->pendaftaran_model->save_pendaftaran($data, $items, $userId, $userName);

        if (!$insertId) {
            return $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Gagal menyimpan transaksi pendaftaran ke database.']));
        }

        $regData = $this->pendaftaran_model->get_by_id($insertId);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'message' => 'Pendaftaran & Order LIS berhasil diterbitkan.',
                'id' => $insertId,
                'data' => $regData
            ]));
    }

    /**
     * AJAX Validasi Pembayaran Kasir (Alur Kasir Terintegrasi Akun Bayar Kas/Bank & Akun Piutang)
     */
    public function validasi_kasir() {
        $this->rbac_lib->require_access('pendaftaran', 'edit');

        $id = (int)$this->input->post('id', TRUE);
        $jenisTransaksi = $this->input->post('jenis_transaksi', TRUE) ?: 'bayar';

        $user = $this->rbac_lib->user();
        $userId = isset($user['id']) ? (int)$user['id'] : null;
        $userName = isset($user['full_name']) ? $user['full_name'] : ($user['username'] ?? 'Kasir');

        if ($jenisTransaksi === 'piutang') {
            $akunPiutangId = (int)$this->input->post('akun_piutang_id', TRUE);
            $nomorPks = trim($this->input->post('nomor_pks_spk', TRUE));
            $instansi = trim($this->input->post('instansi_penjamin', TRUE));
            $pic = trim($this->input->post('pic_penjamin', TRUE));
            $catatan = trim($this->input->post('catatan_penjamin', TRUE));

            $result = $this->pendaftaran_model->validasi_penjaminan_pks($id, [
                'akun_piutang_id' => $akunPiutangId,
                'nomor_pks_spk' => $nomorPks,
                'instansi_penjamin' => $instansi,
                'pic_penjamin' => $pic,
                'catatan_penjamin' => $catatan
            ], $userId, $userName);
        } else {
            $akunBayarId = (int)$this->input->post('akun_bayar_id', TRUE);
            $metode = $this->input->post('metode_pembayaran', TRUE) ?: 'Cash';
            $nominalBayar = (float)$this->input->post('nominal_bayar', TRUE);

            $result = $this->pendaftaran_model->validasi_pembayaran_kasir($id, [
                'akun_bayar_id' => $akunBayarId,
                'metode_pembayaran' => $metode,
                'nominal_bayar' => $nominalBayar
            ], $userId, $userName);
        }

        if (!$result['status']) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode($result));
        }

        $this->_log('KASIR_PAID', sprintf("Validasi kasir ID=%d | Jenis=%s", $id, $jenisTransaksi));

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }

    /**
     * AJAX Verifikasi Jaminan Piutang PKS / SPK (Alur 2: Piutang / PKS -> GUARANTEED)
     */
    public function validasi_pks() {
        $this->rbac_lib->require_access('pendaftaran', 'edit');

        $id = (int)$this->input->post('id', TRUE);
        $akunPiutangId = (int)$this->input->post('akun_piutang_id', TRUE);
        $nomorPks = trim($this->input->post('nomor_pks_spk', TRUE));
        $instansi = trim($this->input->post('instansi_penjamin', TRUE));
        $pic = trim($this->input->post('pic_penjamin', TRUE));
        $catatan = trim($this->input->post('catatan_penjamin', TRUE));

        $user = $this->rbac_lib->user();
        $userId = isset($user['id']) ? (int)$user['id'] : null;
        $userName = isset($user['full_name']) ? $user['full_name'] : ($user['username'] ?? 'Verifikator');

        $result = $this->pendaftaran_model->validasi_penjaminan_pks($id, [
            'akun_piutang_id' => $akunPiutangId,
            'nomor_pks_spk' => $nomorPks,
            'instansi_penjamin' => $instansi,
            'pic_penjamin' => $pic,
            'catatan_penjamin' => $catatan
        ], $userId, $userName);

        if (!$result['status']) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode($result));
        }

        $this->_log('VERIF_PKS_GUARANTEED', sprintf("Verifikasi SPK ID=%d | NoPKS=%s", $id, $nomorPks));

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($result));
    }

    /**
     * AJAX Ambil Data Hasil Pemeriksaan Lab untuk Form Input Hasil
     */
    public function get_hasil_lab($id) {
        $this->rbac_lib->require_access('pendaftaran', 'view');

        $reg = $this->pendaftaran_model->get_by_id($id);
        if (!$reg) {
            return $this->output
                ->set_status_header(404)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Data pendaftaran tidak ditemukan.']));
        }

        $items = $this->pendaftaran_model->get_details_with_results($id);
        $user = $this->rbac_lib->user();

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'data' => [
                    'pendaftaran' => $reg,
                    'items' => $items,
                    'default_dokter' => !empty($reg['dokter_pj']) ? $reg['dokter_pj'] : get_setting('dokter_penanggung_jawab', 'dr. Hj. Siti Rahmah, Sp.PK, M.Kes'),
                    'default_analis' => !empty($reg['analis_nama']) ? $reg['analis_nama'] : (isset($user['full_name']) ? $user['full_name'] : ($user['username'] ?? 'Petugas Analis')),
                    'kesimpulan_saran' => $reg['kesimpulan_saran'] ?? ''
                ]
            ]));
    }

    /**
     * AJAX Simpan / Validasi Hasil Pemeriksaan Lab
     */
    public function save_hasil_lab() {
        $this->rbac_lib->require_access('pendaftaran', 'edit');

        $id = (int)$this->input->post('id', TRUE);
        $resultsJson = $this->input->post('results_json', FALSE);
        $results = json_decode($resultsJson, true);

        if ($id <= 0) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'ID Pendaftaran tidak valid.']));
        }

        if (empty($results) || !is_array($results)) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Rincian hasil pemeriksaan tidak boleh kosong.']));
        }

        $user = $this->rbac_lib->user();
        $userId = isset($user['id']) ? (int)$user['id'] : null;
        $userName = isset($user['full_name']) ? $user['full_name'] : ($user['username'] ?? 'Petugas Analis');

        $metadata = [
            'dokter_pj' => $this->input->post('dokter_pj', TRUE),
            'analis_nama' => $this->input->post('analis_nama', TRUE) ?: $userName,
            'kesimpulan_saran' => $this->input->post('kesimpulan_saran', TRUE),
            'status_order' => $this->input->post('status_order', TRUE) ?: 'processing'
        ];

        $res = $this->pendaftaran_model->save_lab_results($id, $results, $metadata, $userId, $userName);

        if (!$res['status']) {
            return $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode($res));
        }

        $this->_log('SAVE_HASIL_LAB', sprintf("Input hasil lab ID=%d | Status=%s", $id, $metadata['status_order']));

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($res));
    }

    /**
     * AJAX Update Status Order & Sampling (Alur 3)
     */
    public function update_status() {
        $this->rbac_lib->require_access('pendaftaran', 'edit');

        $id = (int)$this->input->post('id', TRUE);
        $newStatus = trim($this->input->post('status_order', TRUE));
        $catatan = trim($this->input->post('catatan', TRUE));

        $user = $this->rbac_lib->user();
        $userId = isset($user['id']) ? (int)$user['id'] : null;
        $userName = isset($user['full_name']) ? $user['full_name'] : ($user['username'] ?? 'Petugas');

        if (!in_array($newStatus, ['registered', 'sampling', 'processing', 'validasi', 'completed', 'cancelled'])) {
            return $this->output
                ->set_status_header(400)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Status order tidak valid.']));
        }

        $ok = $this->pendaftaran_model->update_status_order($id, $newStatus, $catatan, $userId, $userName);
        if (!$ok) {
            return $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Gagal mengubah status order.']));
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => 'success', 'message' => 'Status order LIS berhasil diperbarui.']));
    }

    /**
     * AJAX Batalkan Pendaftaran
     */
    public function batal() {
        $this->rbac_lib->require_access('pendaftaran', 'delete');

        $id = (int)$this->input->post('id', TRUE);
        $alasan = trim($this->input->post('alasan', TRUE));

        $user = $this->rbac_lib->user();
        $userId = isset($user['id']) ? (int)$user['id'] : null;
        $userName = isset($user['full_name']) ? $user['full_name'] : ($user['username'] ?? 'Admin');

        $ok = $this->pendaftaran_model->batal_pendaftaran($id, $alasan, $userId, $userName);
        if (!$ok) {
            return $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Gagal membatalkan pendaftaran.']));
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => 'success', 'message' => 'Pendaftaran LIS telah dibatalkan.']));
    }

    /**
     * AJAX Ambil Summary Terkini
     */
    public function summary() {
        $summary = $this->pendaftaran_model->get_summary_stats();
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['status' => 'success', 'summary' => $summary]));
    }

    /**
     * CETAK KUITANSI BILLING RESMI BLUD (Standar SAK / SAP Pemprov Riau)
     */
    public function cetak_kuitansi($id) {
        $this->rbac_lib->require_access('pendaftaran', 'view');

        $data['reg'] = $this->pendaftaran_model->get_by_id($id);
        if (!$data['reg']) {
            show_404();
        }

        $this->load->view('pendaftaran/cetak_kuitansi', $data);
    }

    /**
     * CETAK LABEL BARCODE TABUNG SAMPEL LAB
     */
    public function cetak_barcode($id) {
        $this->rbac_lib->require_access('pendaftaran', 'view');

        $data['reg'] = $this->pendaftaran_model->get_by_id($id);
        if (!$data['reg']) {
            show_404();
        }
        $data['setting'] = app_profile();

        $this->load->view('pendaftaran/cetak_barcode', $data);
    }

    /**
     * CETAK FORMULIR PERMINTAAN PEMERIKSAAN LAB (ORDER SHEET)
     */
    public function cetak_order($id) {
        $this->rbac_lib->require_access('pendaftaran', 'view');

        $data['reg'] = $this->pendaftaran_model->get_by_id($id);
        if (!$data['reg']) {
            show_404();
        }

        $this->load->view('pendaftaran/cetak_order', $data);
    }

    /**
     * CETAK LEMBAR RESMI HASIL PEMERIKSAAN LABORATORIUM (STANDAR KAN ISO 17025)
     */
    public function cetak_hasil($id) {
        $this->rbac_lib->require_access('pendaftaran', 'view');

        $data['reg'] = $this->pendaftaran_model->get_by_id($id);
        if (!$data['reg']) {
            show_404();
        }

        $this->load->model('approval_model');
        $keabsahan = $this->approval_model->check_keabsahan($id);
        $data['keabsahan'] = $keabsahan;
        $data['approvals'] = $this->approval_model->get_order_approvals($id);

        // Jika hasil BELUM SAH dan sistem dikonfigurasi 'block' (Blokir total)
        if (!$keabsahan['is_sah'] && $keabsahan['mode_belum_lengkap'] === 'block') {
            $data['title'] = 'Akses Ditolak - Hasil Lab Belum Sah';
            $this->load->view('pendaftaran/hasil_belum_sah', $data);
            return;
        }

        $data['items'] = $this->pendaftaran_model->get_details_with_results($id);
        $data['profile'] = app_profile();

        // Kelompokkan item hasil berdasarkan Kategori Lab
        $grouped = [];
        foreach ($data['items'] as $item) {
            $cat = !empty($item['kategori_lab']) ? $item['kategori_lab'] : 'Pemeriksaan Umum';
            if (!isset($grouped[$cat])) {
                $grouped[$cat] = [];
            }
            $grouped[$cat][] = $item;
        }
        $data['grouped_items'] = $grouped;

        $this->load->view('pendaftaran/cetak_hasil', $data);
    }
}
