<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pendaftaran_model extends CI_Model {

    protected $table = 'pendaftaran';
    protected $table_detail = 'pendaftaran_detail';
    protected $table_log = 'pendaftaran_log';

    public function __construct() {
        parent::__construct();
    }

    private function _log($action, $message) {
        log_message('info', sprintf("[PENDAFTARAN-MODEL] ACTION=%s | MSG=%s", $action, $message));
    }

    /**
     * Server-side DataTables fetcher for Pendaftaran & Order LIS
     */
    public function get_datatables($params) {
        $start = isset($params['start']) ? (int)$params['start'] : 0;
        $length = isset($params['length']) ? (int)$params['length'] : 10;
        $search = isset($params['search']) ? trim($params['search']) : '';
        $tipePasien = isset($params['tipe_pasien']) ? trim($params['tipe_pasien']) : 'all';
        $statusBayar = isset($params['status_bayar']) ? trim($params['status_bayar']) : 'all';
        $statusOrder = isset($params['status_order']) ? trim($params['status_order']) : 'all';
        $tanggal = isset($params['tanggal']) ? trim($params['tanggal']) : '';

        // Query total
        $totalRecords = $this->db->count_all($this->table);

        // Build filtered query
        $this->db->from($this->table);

        if (!empty($tipePasien) && $tipePasien !== 'all') {
            $this->db->where('tipe_pasien', $tipePasien);
        }

        if (!empty($statusBayar) && $statusBayar !== 'all') {
            $this->db->where('status_pembayaran', $statusBayar);
        }

        if (!empty($statusOrder) && $statusOrder !== 'all') {
            $this->db->where('status_order', $statusOrder);
        }

        if (!empty($tanggal)) {
            $this->db->where('tanggal_pendaftaran', $tanggal);
        }

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('nomor_registrasi', $search);
            $this->db->or_like('nomor_billing', $search);
            $this->db->or_like('barcode_sampel', $search);
            $this->db->or_like('nama_pasien', $search);
            $this->db->or_like('nik_pasien', $search);
            $this->db->or_like('nomor_rm', $search);
            $this->db->or_like('instansi_pengirim', $search);
            $this->db->or_like('dokter_pengirim', $search);
            $this->db->or_like('telepon', $search);
            $this->db->group_end();
        }

        // Count filtered records
        $filteredRecords = $this->db->count_all_results('', false);

        // Sorting & pagination
        $this->db->order_by('id', 'DESC');
        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        $query = $this->db->get();
        $rows = $query->result_array();

        // Attach item count to each row
        foreach ($rows as &$row) {
            $row['total_items'] = $this->db->where('pendaftaran_id', $row['id'])->count_all_results($this->table_detail);
        }

        return [
            'total' => $totalRecords,
            'filtered' => $filteredRecords,
            'data' => $rows
        ];
    }

    /**
     * Get single registration with full details & logs
     */
    public function get_by_id($id) {
        $this->db->select('p.*, 
            ab.nama_bayar as akun_bayar_nama, ab.kode_bayar, ab.kode_rekening as akun_bayar_coa, ab.tipe_transaksi as akun_bayar_tipe, ab.nama_bank as akun_bayar_bank, ab.nomor_rekening_bank as akun_bayar_norek, ab.atas_nama as akun_bayar_atas_nama,
            ap.nama_piutang as akun_piutang_nama, ap.kode_piutang, ap.kode_rekening as akun_piutang_coa, ap.kategori_penjamin as akun_piutang_kategori, ap.jatuh_tempo_hari as akun_piutang_tempo, ap.kontak_person as akun_piutang_kontak');
        $this->db->from($this->table . ' p');
        $this->db->join('akun_bayar ab', 'ab.id = p.akun_bayar_id', 'left');
        $this->db->join('akun_piutang ap', 'ap.id = p.akun_piutang_id', 'left');
        $this->db->where('p.id', (int)$id);
        $pendaftaran = $this->db->get()->row_array();

        if (!$pendaftaran) {
            return null;
        }

        // Ambil item rincian uji lab
        $this->db->from($this->table_detail);
        $this->db->where('pendaftaran_id', (int)$id);
        $this->db->order_by('id', 'ASC');
        $pendaftaran['items'] = $this->db->get()->result_array();

        // Ambil audit trail logs
        $this->db->from($this->table_log);
        $this->db->where('pendaftaran_id', (int)$id);
        $this->db->order_by('id', 'DESC');
        $pendaftaran['logs'] = $this->db->get()->result_array();

        // Info klien jika ada
        if (!empty($pendaftaran['client_id'])) {
            $pendaftaran['client'] = $this->db->get_where('clients', ['id' => (int)$pendaftaran['client_id']])->row_array();
        } else {
            $pendaftaran['client'] = null;
        }

        return $pendaftaran;
    }

    /**
     * Generate Nomor Registrasi Otomatis (REG-YYYYMMDD-XXXX)
     */
    public function generate_reg_number() {
        $today = date('Ymd');
        $prefix = 'REG-' . $today . '-';

        $this->db->select('nomor_registrasi');
        $this->db->like('nomor_registrasi', $prefix, 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $last = $this->db->get($this->table)->row_array();

        $nextSeq = 1;
        if ($last && !empty($last['nomor_registrasi'])) {
            $parts = explode('-', $last['nomor_registrasi']);
            if (isset($parts[2]) && is_numeric($parts[2])) {
                $nextSeq = (int)$parts[2] + 1;
            }
        }

        return $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate Nomor Billing ID Otomatis (BIL-YYYYMMDD-XXXX)
     */
    public function generate_billing_number() {
        $today = date('Ymd');
        $prefix = 'BIL-' . $today . '-';

        $this->db->select('nomor_billing');
        $this->db->like('nomor_billing', $prefix, 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $last = $this->db->get($this->table)->row_array();

        $nextSeq = 1;
        if ($last && !empty($last['nomor_billing'])) {
            $parts = explode('-', $last['nomor_billing']);
            if (isset($parts[2]) && is_numeric($parts[2])) {
                $nextSeq = (int)$parts[2] + 1;
            }
        }

        return $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate Barcode Sampel Otomatis (SMP-YYMMDD-XXXX)
     */
    public function generate_sample_barcode() {
        $shortDate = date('ymd');
        $prefix = 'SMP-' . $shortDate . '-';

        $this->db->select('barcode_sampel');
        $this->db->like('barcode_sampel', $prefix, 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $last = $this->db->get($this->table)->row_array();

        $nextSeq = 1;
        if ($last && !empty($last['barcode_sampel'])) {
            $parts = explode('-', $last['barcode_sampel']);
            if (isset($parts[2]) && is_numeric($parts[2])) {
                $nextSeq = (int)$parts[2] + 1;
            }
        }

        return $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate Kuitansi Kasir Otomatis (KWT-YYYYMMDD-XXXX)
     */
    public function generate_kuitansi_number() {
        $today = date('Ymd');
        $prefix = 'KWT-' . $today . '-';

        $this->db->select('nomor_kuitansi');
        $this->db->like('nomor_kuitansi', $prefix, 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $last = $this->db->get($this->table)->row_array();

        $nextSeq = 1;
        if ($last && !empty($last['nomor_kuitansi'])) {
            $parts = explode('-', $last['nomor_kuitansi']);
            if (isset($parts[2]) && is_numeric($parts[2])) {
                $nextSeq = (int)$parts[2] + 1;
            }
        }

        return $prefix . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Simpan Pendaftaran & Order LIS Baru (Alur 1)
     */
    public function save_pendaftaran($data, $items, $userId = null, $userName = 'Petugas') {
        $this->db->trans_start();

        // 1. Pastikan Kode Otomatis Terisi
        if (empty($data['nomor_registrasi'])) {
            $data['nomor_registrasi'] = $this->generate_reg_number();
        }
        if (empty($data['nomor_billing'])) {
            $data['nomor_billing'] = $this->generate_billing_number();
        }
        if (empty($data['barcode_sampel'])) {
            $data['barcode_sampel'] = $this->generate_sample_barcode();
        }

        $data['tanggal_pendaftaran'] = !empty($data['tanggal_pendaftaran']) ? $data['tanggal_pendaftaran'] : date('Y-m-d');
        $data['jam_pendaftaran'] = !empty($data['jam_pendaftaran']) ? $data['jam_pendaftaran'] : date('H:i:s');
        $data['created_by'] = $userId;

        // 2. Insert ke tabel pendaftaran
        $this->db->insert($this->table, $data);
        $pendaftaranId = $this->db->insert_id();

        // 3. Insert items pemeriksaan
        if (!empty($items) && is_array($items)) {
            foreach ($items as $item) {
                $itemType = isset($item['tipe_item']) ? $item['tipe_item'] : 'parameter';
                $itemRow = [
                    'pendaftaran_id' => $pendaftaranId,
                    'tipe_item' => $itemType,
                    'item_id' => (int)($item['item_id'] ?? 0),
                    'kode_item' => $item['kode_item'] ?? '-',
                    'nama_item' => $item['nama_item'] ?? '-',
                    'kategori_lab' => $item['kategori_lab'] ?? 'Laboratorium',
                    'metode_uji' => $item['metode_uji'] ?? '-',
                    'satuan' => $item['satuan'] ?? '-',
                    'nilai_rujukan' => $item['nilai_rujukan'] ?? '-',
                    'jasa_manajemen' => (float)($item['jasa_manajemen'] ?? 0),
                    'jasa_dokter' => (float)($item['jasa_dokter'] ?? 0),
                    'jasa_petugas' => (float)($item['jasa_petugas'] ?? 0),
                    'kso' => (float)($item['kso'] ?? 0),
                    'bhp' => (float)($item['bhp'] ?? 0),
                    'tarif' => (float)($item['tarif'] ?? 0),
                    'status_pengujian' => 'pending'
                ];
                $this->db->insert($this->table_detail, $itemRow);

                // Jika item adalah template/paket pemeriksaan, otomatis simpan juga sub-parameter ujinya
                if ($itemType === 'template') {
                    $templateId = (int)($item['item_id'] ?? 0);
                    $this->db->select('p.id, p.parameter_code, p.parameter_name, p.unit, c.category_name, m.method_name');
                    $this->db->from('lab_template_items ti');
                    $this->db->join('lab_parameters p', 'ti.parameter_id = p.id');
                    $this->db->join('lab_categories c', 'p.category_id = c.id', 'left');
                    $this->db->join('lab_methods m', 'p.method_id = m.id', 'left');
                    $this->db->where('ti.template_id', $templateId);
                    $this->db->order_by('ti.sort_order', 'ASC');
                    $subParams = $this->db->get()->result_array();

                    foreach ($subParams as $sp) {
                        $refRange = '-';
                        $rr = $this->db->where('parameter_id', $sp['id'])->get('lab_reference_ranges')->row_array();
                        if ($rr) {
                            $refRange = !empty($rr['reference_display']) ? $rr['reference_display'] : 
                                       (($rr['min_val'] !== null && $rr['max_val'] !== null) ? $rr['min_val'] . ' - ' . $rr['max_val'] : ($rr['text_normal'] ?? '-'));
                        }

                        $subRow = [
                            'pendaftaran_id' => $pendaftaranId,
                            'tipe_item' => 'parameter',
                            'item_id' => (int)$sp['id'],
                            'kode_item' => $sp['parameter_code'],
                            'nama_item' => $sp['parameter_name'],
                            'kategori_lab' => $sp['category_name'] ?: 'Laboratorium',
                            'metode_uji' => $sp['method_name'] ?: '-',
                            'satuan' => $sp['unit'] ?: '-',
                            'nilai_rujukan' => $refRange,
                            'jasa_manajemen' => 0,
                            'jasa_dokter' => 0,
                            'jasa_petugas' => 0,
                            'kso' => 0,
                            'bhp' => 0,
                            'tarif' => 0,
                            'status_pengujian' => 'pending'
                        ];
                        $this->db->insert($this->table_detail, $subRow);
                    }
                }
            }
        }

        // 4. Catat Log Audit Trail
        $logDesc = sprintf(
            "Registrasi Baru: %s (%s). Tipe: %s. Jalur Bayar: %s. Total Biaya: Rp %s",
            $data['nomor_registrasi'],
            $data['nama_pasien'],
            strtoupper(str_replace('_', ' ', $data['tipe_pasien'])),
            strtoupper(str_replace('_', ' ', $data['alur_pembayaran'])),
            number_format($data['total_biaya'], 0, ',', '.')
        );

        $this->db->insert($this->table_log, [
            'pendaftaran_id' => $pendaftaranId,
            'action' => 'REGISTRASI_ORDER',
            'description' => $logDesc,
            'user_id' => $userId,
            'user_name' => $userName
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            $this->_log('SAVE_ERROR', 'Gagal menyimpan transaksi pendaftaran.');
            return false;
        }

        $this->_log('SAVE_SUCCESS', sprintf("Berhasil simpan pendaftaran ID=%d, NoReg=%s", $pendaftaranId, $data['nomor_registrasi']));
        return $pendaftaranId;
    }

    /**
     * Dapatkan detail pendaftaran beserta hasil uji lab per parameter
     */
    public function get_details_with_results($id) {
        $details = $this->db->where('pendaftaran_id', (int)$id)
            ->order_by('kategori_lab', 'ASC')
            ->order_by('id', 'ASC')
            ->get($this->table_detail)
            ->result_array();

        // Cek apakah ada paket template yang belum ter-expand sub-parameternya
        $hasParameters = false;
        $templateRows = [];
        foreach ($details as $d) {
            if ($d['tipe_item'] === 'parameter') {
                $hasParameters = true;
            } else if ($d['tipe_item'] === 'template') {
                $templateRows[] = $d;
            }
        }

        // Jika hanya ada template tanpa parameter, ekspansi sekarang dari master template
        if (!$hasParameters && !empty($templateRows)) {
            foreach ($templateRows as $tRow) {
                $templateId = (int)$tRow['item_id'];
                $this->db->select('p.id, p.parameter_code, p.parameter_name, p.unit, c.category_name, m.method_name');
                $this->db->from('lab_template_items ti');
                $this->db->join('lab_parameters p', 'ti.parameter_id = p.id');
                $this->db->join('lab_categories c', 'p.category_id = c.id', 'left');
                $this->db->join('lab_methods m', 'p.method_id = m.id', 'left');
                $this->db->where('ti.template_id', $templateId);
                $this->db->order_by('ti.sort_order', 'ASC');
                $subParams = $this->db->get()->result_array();

                foreach ($subParams as $sp) {
                    $refRange = '-';
                    $rr = $this->db->where('parameter_id', $sp['id'])->get('lab_reference_ranges')->row_array();
                    if ($rr) {
                        $refRange = !empty($rr['reference_display']) ? $rr['reference_display'] : 
                                   (($rr['min_val'] !== null && $rr['max_val'] !== null) ? $rr['min_val'] . ' - ' . $rr['max_val'] : ($rr['text_normal'] ?? '-'));
                    }

                    $subRow = [
                        'pendaftaran_id' => (int)$id,
                        'tipe_item' => 'parameter',
                        'item_id' => (int)$sp['id'],
                        'kode_item' => $sp['parameter_code'],
                        'nama_item' => $sp['parameter_name'],
                        'kategori_lab' => $sp['category_name'] ?: 'Laboratorium',
                        'metode_uji' => $sp['method_name'] ?: '-',
                        'satuan' => $sp['unit'] ?: '-',
                        'nilai_rujukan' => $refRange,
                        'jasa_manajemen' => 0,
                        'jasa_dokter' => 0,
                        'jasa_petugas' => 0,
                        'kso' => 0,
                        'bhp' => 0,
                        'tarif' => 0,
                        'status_pengujian' => 'pending'
                    ];
                    $this->db->insert($this->table_detail, $subRow);
                }
            }

            // Ambil ulang data details
            $details = $this->db->where('pendaftaran_id', (int)$id)
                ->order_by('kategori_lab', 'ASC')
                ->order_by('id', 'ASC')
                ->get($this->table_detail)
                ->result_array();
        }

        // Jika terdapat item parameter, saring untuk form input / lembar hasil
        $hasParamItems = false;
        foreach ($details as $d) {
            if ($d['tipe_item'] === 'parameter') {
                $hasParamItems = true;
                break;
            }
        }

        if ($hasParamItems) {
            return array_values(array_filter($details, function($d) {
                return $d['tipe_item'] === 'parameter';
            }));
        }

        return $details;
    }

    /**
     * Alias untuk get_details_with_results
     */
    public function get_hasil_lab($id) {
        return $this->get_details_with_results($id);
    }

    /**
     * Simpan / Perbarui Hasil Pemeriksaan Laboratorium
     */
    public function save_lab_results($pendaftaranId, $results, $metadata, $userId = null, $userName = 'Analis Lab') {
        $pendaftaran = $this->db->get_where($this->table, ['id' => (int)$pendaftaranId])->row_array();
        if (!$pendaftaran) {
            return ['status' => false, 'message' => 'Data pendaftaran tidak ditemukan.'];
        }

        $this->db->trans_start();

        $statusPengujian = ($metadata['status_order'] === 'completed') ? 'completed' : (($metadata['status_order'] === 'validasi') ? 'validasi' : 'processing');
        $now = date('Y-m-d H:i:s');

        if (!empty($results) && is_array($results)) {
            foreach ($results as $res) {
                $detailId = (int)($res['id'] ?? ($res['detail_id'] ?? 0));
                if ($detailId <= 0) continue;

                $rawFlag = strtolower(trim($res['hasil_flag'] ?? ($res['flag_kritis'] ?? 'normal')));
                // Normalisasi flag input (normal/low/high/critical atau Normal/L/H/L*/H*)
                if ($rawFlag === 'l' || $rawFlag === 'low') {
                    $flag = 'low';
                } elseif ($rawFlag === 'h' || $rawFlag === 'high') {
                    $flag = 'high';
                } elseif ($rawFlag === 'l*' || $rawFlag === 'h*' || $rawFlag === 'critical' || $rawFlag === 'kritis') {
                    $flag = 'critical';
                } else {
                    $flag = 'normal';
                }

                $hasilNilai = trim($res['hasil_nilai'] ?? ($res['hasil'] ?? ''));
                $catatan = trim($res['catatan_hasil'] ?? ($res['catatan_pemeriksaan'] ?? ''));

                $this->db->where('id', $detailId);
                $this->db->where('pendaftaran_id', (int)$pendaftaranId);
                $this->db->update($this->table_detail, [
                    'hasil_nilai' => $hasilNilai,
                    'hasil_flag' => $flag,
                    'catatan_hasil' => $catatan,
                    'status_pengujian' => $statusPengujian,
                    'analis_id' => $userId,
                    'waktu_input_hasil' => $now
                ]);
            }
        }

        // Update header pendaftaran
        $newStatusOrder = in_array($metadata['status_order'] ?? '', ['processing', 'validasi', 'completed']) ? $metadata['status_order'] : 'validasi';
        $updateHeader = [
            'dokter_pj' => trim($metadata['dokter_pj'] ?? ''),
            'analis_nama' => trim($metadata['analis_nama'] ?? $userName),
            'kesimpulan_saran' => trim($metadata['kesimpulan_saran'] ?? ''),
            'status_order' => $newStatusOrder,
            'updated_at' => $now
        ];

        if ($newStatusOrder === 'completed') {
            $updateHeader['waktu_hasil_selesai'] = $now;
        }

        $this->db->where('id', (int)$pendaftaranId)->update($this->table, $updateHeader);

        // Audit Trail Log
        $logDesc = sprintf(
            "Hasil Lab Disimpan (%s). Dokter PJ: %s | Analis: %s | Status Order: %s",
            strtoupper($newStatusOrder),
            $updateHeader['dokter_pj'] ?: '-',
            $updateHeader['analis_nama'] ?: '-',
            strtoupper($newStatusOrder)
        );

        $this->db->insert($this->table_log, [
            'pendaftaran_id' => (int)$pendaftaranId,
            'action' => ($newStatusOrder === 'completed') ? 'VALIDASI_HASIL_SELESAI' : 'INPUT_HASIL_LAB',
            'description' => $logDesc,
            'user_id' => $userId,
            'user_name' => $userName
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['status' => false, 'message' => 'Gagal menyimpan hasil laboratorium ke database.'];
        }

        $msg = 'Hasil pemeriksaan laboratorium tersimpan sebagai Draft / Sedang Dikerjakan (Processing).';
        if ($newStatusOrder === 'completed') {
            $msg = 'Hasil pemeriksaan laboratorium berhasil divalidasi dan berstatus Selesai (Completed).';
        } elseif ($newStatusOrder === 'validasi') {
            $msg = 'Hasil laboratorium berhasil disimpan dan dikirim ke antrean Validasi & Otorisasi Dokter Sp.PK.';
        }

        return [
            'status' => true,
            'message' => $msg,
            'status_order' => $newStatusOrder,
            'waktu_selesai' => $now
        ];
    }

    /**
     * Validasi Pembayaran Kasir (Alur Kasir Terintegrasi Akun Bayar Kas/Bank -> PAID)
     */
    public function validasi_pembayaran_kasir($id, $paymentData, $userId = null, $userName = 'Kasir') {
        $pendaftaran = $this->db->get_where($this->table, ['id' => (int)$id])->row_array();
        if (!$pendaftaran) {
            return ['status' => false, 'message' => 'Data pendaftaran tidak ditemukan.'];
        }

        $nominalBayar = (float)($paymentData['nominal_bayar'] ?? 0);
        $totalBiaya = (float)$pendaftaran['total_biaya'];

        $akunBayarId = !empty($paymentData['akun_bayar_id']) ? (int)$paymentData['akun_bayar_id'] : null;
        $metodeStr = $paymentData['metode_pembayaran'] ?? 'Cash';

        if ($akunBayarId) {
            $ab = $this->db->get_where('akun_bayar', ['id' => $akunBayarId])->row_array();
            if ($ab) {
                $metodeStr = $ab['nama_bayar'] . ' (' . $ab['kode_rekening'] . ')';
            }
        }

        if ($nominalBayar < $totalBiaya && (stripos($metodeStr, 'tunai') !== false || stripos($metodeStr, 'cash') !== false)) {
            return ['status' => false, 'message' => 'Nominal pembayaran tunai kurang dari total tagihan.'];
        }

        $nominalKembali = max(0, $nominalBayar - $totalBiaya);
        $nomorKuitansi = $this->generate_kuitansi_number();

        $updateData = [
            'status_pembayaran' => 'PAID',
            'akun_bayar_id' => $akunBayarId,
            'metode_pembayaran' => $metodeStr,
            'waktu_bayar' => date('Y-m-d H:i:s'),
            'nominal_bayar' => $nominalBayar,
            'nominal_kembali' => $nominalKembali,
            'kasir_id' => $userId,
            'nomor_kuitansi' => $nomorKuitansi
        ];

        // Jika status_order masih registered, otomatis siap antrian sampling
        if ($pendaftaran['status_order'] === 'registered') {
            $updateData['status_order'] = 'sampling';
        }

        $this->db->trans_start();

        $this->db->where('id', (int)$id)->update($this->table, $updateData);

        // Catat Log
        $logDesc = sprintf(
            "Pembayaran Lunas Kasir (PAID). Channel: %s | Akun Bayar ID: %s | Bayar: Rp %s | Kembali: Rp %s | No. Kuitansi: %s",
            $updateData['metode_pembayaran'],
            $akunBayarId ?: '-',
            number_format($nominalBayar, 0, ',', '.'),
            number_format($nominalKembali, 0, ',', '.'),
            $nomorKuitansi
        );

        $this->db->insert($this->table_log, [
            'pendaftaran_id' => (int)$id,
            'action' => 'VALIDASI_PEMBAYARAN_KASIR',
            'description' => $logDesc,
            'user_id' => $userId,
            'user_name' => $userName
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['status' => false, 'message' => 'Gagal memproses validasi pembayaran kasir.'];
        }

        return [
            'status' => true,
            'message' => 'Pembayaran berhasil divalidasi ke Kas BLUD. Kuitansi terbit dan sampel siap diuji.',
            'nomor_kuitansi' => $nomorKuitansi,
            'barcode_sampel' => $pendaftaran['barcode_sampel']
        ];
    }

    /**
     * Validasi Piutang / PKS Mitra (Alur Piutang Terintegrasi Akun Piutang Penjamin -> GUARANTEED)
     */
    public function validasi_penjaminan_pks($id, $guaranteeData, $userId = null, $userName = 'Verifikator') {
        $pendaftaran = $this->db->get_where($this->table, ['id' => (int)$id])->row_array();
        if (!$pendaftaran) {
            return ['status' => false, 'message' => 'Data pendaftaran tidak ditemukan.'];
        }

        $akunPiutangId = !empty($guaranteeData['akun_piutang_id']) ? (int)$guaranteeData['akun_piutang_id'] : null;
        $nomorPks = trim($guaranteeData['nomor_pks_spk'] ?? '');
        $instansiPenjamin = trim($guaranteeData['instansi_penjamin'] ?? '');
        $metodeStr = 'SPK / PKS Mitra';

        if ($akunPiutangId) {
            $ap = $this->db->get_where('akun_piutang', ['id' => $akunPiutangId])->row_array();
            if ($ap) {
                if (empty($instansiPenjamin)) {
                    $instansiPenjamin = $ap['nama_piutang'];
                }
                $metodeStr = 'Piutang: ' . $ap['nama_piutang'] . ' (' . $ap['kode_rekening'] . ')';
            }
        }

        if (empty($nomorPks) || empty($instansiPenjamin)) {
            return ['status' => false, 'message' => 'Nomor PKS/SPK/Jaminan dan Instansi Penjamin wajib diisi.'];
        }

        $updateData = [
            'status_pembayaran' => 'GUARANTEED',
            'akun_piutang_id' => $akunPiutangId,
            'metode_pembayaran' => $metodeStr,
            'waktu_bayar' => date('Y-m-d H:i:s'),
            'nomor_pks_spk' => $nomorPks,
            'instansi_penjamin' => $instansiPenjamin,
            'pic_penjamin' => $guaranteeData['pic_penjamin'] ?? '',
            'catatan_penjamin' => $guaranteeData['catatan_penjamin'] ?? '',
            'status_penagihan' => 'belum_ditagih'
        ];

        if ($pendaftaran['status_order'] === 'registered') {
            $updateData['status_order'] = 'sampling';
        }

        $this->db->trans_start();

        $this->db->where('id', (int)$id)->update($this->table, $updateData);

        // Catat Log
        $logDesc = sprintf(
            "Verifikasi Jaminan Piutang Mitra (GUARANTEED). Penjamin: %s | Akun Piutang ID: %s | No. PKS/SPK: %s | PIC: %s",
            $instansiPenjamin,
            $akunPiutangId ?: '-',
            $nomorPks,
            $updateData['pic_penjamin']
        );

        $this->db->insert($this->table_log, [
            'pendaftaran_id' => (int)$id,
            'action' => 'VERIFIKASI_PKS_MITRA',
            'description' => $logDesc,
            'user_id' => $userId,
            'user_name' => $userName
        ]);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            return ['status' => false, 'message' => 'Gagal memproses verifikasi jaminan piutang.'];
        }

        return [
            'status' => true,
            'message' => 'Jaminan Penjamin / Piutang Mitra terverifikasi (GUARANTEED). Sampel siap diuji.',
            'barcode_sampel' => $pendaftaran['barcode_sampel']
        ];
    }

    /**
     * Update Status Order & Sampling (e.g. Sampling selesai -> Processing)
     */
    public function update_status_order($id, $statusOrder, $catatan = '', $userId = null, $userName = 'Petugas') {
        $pendaftaran = $this->db->get_where($this->table, ['id' => (int)$id])->row_array();
        if (!$pendaftaran) {
            return false;
        }

        $updateData = ['status_order' => $statusOrder];
        if (in_array($statusOrder, ['sampling', 'processing']) && empty($pendaftaran['waktu_sampling'])) {
            $updateData['waktu_sampling'] = date('Y-m-d H:i:s');
            $updateData['petugas_sampling'] = $userName;
        }

        $this->db->trans_start();

        $this->db->where('id', (int)$id)->update($this->table, $updateData);

        // Log audit
        $this->db->insert($this->table_log, [
            'pendaftaran_id' => (int)$id,
            'action' => 'UPDATE_STATUS_ORDER',
            'description' => sprintf("Status order diubah menjadi '%s'. Catatan: %s", strtoupper($statusOrder), $catatan ?: '-'),
            'user_id' => $userId,
            'user_name' => $userName
        ]);

        $this->db->trans_complete();
        return $this->db->trans_status() !== FALSE;
    }

    /**
     * Batalkan Pendaftaran
     */
    public function batal_pendaftaran($id, $alasan = '', $userId = null, $userName = 'Admin') {
        $pendaftaran = $this->db->get_where($this->table, ['id' => (int)$id])->row_array();
        if (!$pendaftaran) {
            return false;
        }

        $this->db->trans_start();

        $this->db->where('id', (int)$id)->update($this->table, [
            'status_pembayaran' => 'CANCELLED',
            'status_order' => 'cancelled',
            'catatan' => $pendaftaran['catatan'] . ' [DIBATALKAN: ' . $alasan . ']'
        ]);

        $this->db->insert($this->table_log, [
            'pendaftaran_id' => (int)$id,
            'action' => 'PEMBATALAN_REGISTRASI',
            'description' => sprintf("Pendaftaran dibatalkan. Alasan: %s", $alasan ?: 'Permintaan Pemohon'),
            'user_id' => $userId,
            'user_name' => $userName
        ]);

        $this->db->trans_complete();
        return $this->db->trans_status() !== FALSE;
    }

    /**
     * Hitung Metrik Ringkasan Pendaftaran
     */
    public function get_summary_stats() {
        $today = date('Y-m-d');

        // Total hari ini
        $totalHariIni = $this->db->where('tanggal_pendaftaran', $today)->count_all_results($this->table);

        // Pasien Mandiri
        $mandiri = $this->db->where('tipe_pasien', 'mandiri')->count_all_results($this->table);

        // Rujukan Faskes
        $rujukan = $this->db->where('tipe_pasien', 'rujukan_faskes')->count_all_results($this->table);

        // Sampel Lingkungan / Perusahaan
        $perusahaan = $this->db->where('tipe_pasien', 'perusahaan_lingkungan')->count_all_results($this->table);

        // Menunggu Bayar (UNPAID)
        $unpaid = $this->db->where('status_pembayaran', 'UNPAID')->count_all_results($this->table);

        // Lunas Kasir (PAID)
        $paid = $this->db->where('status_pembayaran', 'PAID')->count_all_results($this->table);

        // Dijamin Mitra PKS (GUARANTEED)
        $guaranteed = $this->db->where('status_pembayaran', 'GUARANTEED')->count_all_results($this->table);

        // Siap / Sedang Sampling
        $sampling = $this->db->where_in('status_order', ['sampling', 'processing'])->count_all_results($this->table);

        // Total Pendapatan Kasir Hari Ini
        $this->db->select_sum('nominal_bayar');
        $this->db->where('status_pembayaran', 'PAID');
        $this->db->where('DATE(waktu_bayar)', $today);
        $sumPaid = $this->db->get($this->table)->row_array();
        $pendapatanHariIni = (float)($sumPaid['nominal_bayar'] ?? 0);

        return [
            'total_hari_ini' => $totalHariIni,
            'mandiri' => $mandiri,
            'rujukan' => $rujukan,
            'perusahaan' => $perusahaan,
            'unpaid' => $unpaid,
            'paid' => $paid,
            'guaranteed' => $guaranteed,
            'sampling' => $sampling,
            'pendapatan_hari_ini' => $pendapatanHariIni
        ];
    }

    /**
     * Quick search pasien/klien untuk autocomplete pendaftaran
     */
    public function search_clients($query, $type = null) {
        $this->db->from('clients');
        $this->db->where("(`status` = 'active' OR `status` IS NULL)", NULL, FALSE);

        if (!empty($type) && in_array($type, ['perorangan', 'perusahaan'])) {
            $this->db->where('client_type', $type);
        }

        if (!empty($query)) {
            $this->db->group_start();
            $this->db->like('name', $query);
            $this->db->or_like('client_code', $query);
            $this->db->or_like('identity_number', $query);
            $this->db->or_like('phone', $query);
            $this->db->or_like('pic_name', $query);
            $this->db->or_like('address', $query);
            $this->db->group_end();
        }

        $this->db->order_by('name', 'ASC');
        $this->db->limit(20);
        return $this->db->get()->result_array();
    }

    /**
     * Dapatkan daftar tarif aktif (Parameter & Paket Template) sesuai Kelompok Tarif
     */
    public function get_available_tariffs($tariffGroup = 'umum') {
        // 1. Parameter Satuan dengan Tarif
        $this->db->select('p.id, p.category_id, p.parameter_code, p.parameter_name, p.unit, c.category_name, m.method_name, 
                           t.jasa_manajemen, t.jasa_dokter, t.jasa_petugas, t.kso, t.bhp, t.total_tarif');
        $this->db->from('lab_parameters p');
        $this->db->join('lab_categories c', 'p.category_id = c.id', 'left');
        $this->db->join('lab_methods m', 'p.method_id = m.id', 'left');
        $this->db->join('lab_tariffs t', "t.item_type = 'parameter' AND t.item_id = p.id AND t.tariff_group = '{$tariffGroup}'", 'left');
        $this->db->where('p.is_active', 1);
        $this->db->order_by('c.sort_order', 'ASC');
        $this->db->order_by('p.parameter_name', 'ASC');
        $params = $this->db->get()->result_array();

        // 2. Template / Paket Lab dengan Tarif
        $this->db->select('tpl.id, tpl.template_code, tpl.template_name, tpl.description, c.category_name,
                           t.jasa_manajemen, t.jasa_dokter, t.jasa_petugas, t.kso, t.bhp, t.total_tarif');
        $this->db->from('lab_templates tpl');
        $this->db->join('lab_categories c', 'tpl.category_id = c.id', 'left');
        $this->db->join('lab_tariffs t', "t.item_type = 'template' AND t.item_id = tpl.id AND t.tariff_group = '{$tariffGroup}'", 'left');
        $this->db->where('tpl.is_active', 1);
        $this->db->order_by('tpl.template_name', 'ASC');
        $templates = $this->db->get()->result_array();

        // Ambil item parameter dalam tiap template
        foreach ($templates as &$tpl) {
            $this->db->select('p.id, p.parameter_code, p.parameter_name, p.unit');
            $this->db->from('lab_template_items ti');
            $this->db->join('lab_parameters p', 'ti.parameter_id = p.id');
            $this->db->where('ti.template_id', $tpl['id']);
            $tpl['items'] = $this->db->get()->result_array();
        }

        return [
            'parameters' => $params,
            'templates' => $templates
        ];
    }
}
