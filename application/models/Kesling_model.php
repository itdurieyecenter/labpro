<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model Modul Kesehatan Lingkungan (Pilot: Air Minum).
 *
 * Mengelola master baku mutu multi-standar, order pengujian sampel lingkungan
 * (FPPS, koordinat, personel sampling), hasil uji, dan evaluasi lewat-ambang.
 * Status lewat-ambang DIHITUNG dari baku mutu standar terpilih, tidak disimpan.
 */
class Kesling_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    // =====================================================================
    // MASTER: tipe sampel, standar, parameter, baku mutu
    // =====================================================================

    public function get_sample_types($activeOnly = FALSE) {
        if ($activeOnly) $this->db->where('is_active', 1);
        return $this->db->order_by('sort_order', 'ASC')->get('env_sample_types')->result_array();
    }

    public function get_standards($sampleTypeId = NULL, $activeOnly = FALSE) {
        if ($sampleTypeId) $this->db->where('sample_type_id', (int)$sampleTypeId);
        if ($activeOnly) $this->db->where('is_active', 1);
        return $this->db->order_by('sort_order', 'ASC')->get('env_standards')->result_array();
    }

    public function get_standard($id) {
        return $this->db->where('id', (int)$id)->get('env_standards')->row_array();
    }

    public function get_parameters($sampleTypeId, $activeOnly = TRUE) {
        $this->db->where('sample_type_id', (int)$sampleTypeId);
        if ($activeOnly) $this->db->where('is_active', 1);
        return $this->db->order_by('sort_order', 'ASC')->get('env_parameters')->result_array();
    }

    /**
     * Ambang baku mutu untuk sebuah standar, di-index per parameter_id.
     */
    public function get_thresholds_by_standard($standardId) {
        $rows = $this->db->where('standard_id', (int)$standardId)->get('env_thresholds')->result_array();
        $map = [];
        foreach ($rows as $r) {
            $map[(int)$r['parameter_id']] = $r;
        }
        return $map;
    }

    /**
     * Matriks baku mutu untuk halaman master: setiap parameter dengan ambang
     * di tiap standar (untuk tipe sampel tertentu).
     */
    public function get_baku_mutu_matrix($sampleTypeId) {
        $params = $this->get_parameters($sampleTypeId, FALSE);
        $standards = $this->get_standards($sampleTypeId, FALSE);
        $thrByStd = [];
        foreach ($standards as $s) {
            $thrByStd[(int)$s['id']] = $this->get_thresholds_by_standard($s['id']);
        }
        foreach ($params as &$p) {
            $p['thresholds'] = [];
            $p['threshold_rows'] = [];
            foreach ($standards as $s) {
                $sid = (int)$s['id'];
                $pid = (int)$p['id'];
                $row = isset($thrByStd[$sid][$pid]) ? $thrByStd[$sid][$pid] : NULL;
                $p['thresholds'][$sid] = $row ? $row['threshold_display'] : NULL;
                $p['threshold_rows'][$sid] = $row;
            }
        }
        unset($p);
        return ['standards' => $standards, 'parameters' => $params];
    }

    public function get_parameter($id) {
        return $this->db->where('id', (int)$id)->get('env_parameters')->row_array();
    }

    public function save_parameter($data, $id = 0) {
        $id = (int)$id;
        if ($id > 0) {
            $this->db->where('id', $id)->update('env_parameters', $data);
            return $id;
        }
        $this->db->insert('env_parameters', $data);
        return (int)$this->db->insert_id();
    }

    public function delete_parameter($id) {
        return $this->db->where('id', (int)$id)->delete('env_parameters');
    }

    public function save_standard($data, $id = 0) {
        $id = (int)$id;
        if ($id > 0) {
            $this->db->where('id', $id)->update('env_standards', $data);
            return $id;
        }
        $this->db->insert('env_standards', $data);
        return (int)$this->db->insert_id();
    }

    public function delete_standard($id) {
        return $this->db->where('id', (int)$id)->delete('env_standards');
    }

    public function get_threshold($standardId, $parameterId) {
        return $this->db
            ->where('standard_id', (int)$standardId)
            ->where('parameter_id', (int)$parameterId)
            ->get('env_thresholds')
            ->row_array();
    }

    public function save_threshold($standardId, $parameterId, $data) {
        $existing = $this->get_threshold($standardId, $parameterId);
        if ($existing) {
            return $this->db->where('id', (int)$existing['id'])->update('env_thresholds', $data);
        }
        $data['standard_id'] = (int)$standardId;
        $data['parameter_id'] = (int)$parameterId;
        return $this->db->insert('env_thresholds', $data);
    }

    public function delete_threshold($standardId, $parameterId) {
        return $this->db
            ->where('standard_id', (int)$standardId)
            ->where('parameter_id', (int)$parameterId)
            ->delete('env_thresholds');
    }

    /**
     * Token HMAC-SHA256 16 karakter hex untuk QR verifikasi publik LHU Kesling.
     */
    public function verification_token($orderId, $orderNumber) {
        $secret = (string)($this->config->item('encryption_key') ?: 'lispro-default-key');
        $msg = 'kesling|' . (int)$orderId . '|' . trim((string)$orderNumber);
        return substr(hash_hmac('sha256', $msg, $secret), 0, 16);
    }

    public function get_minimal_for_verify($orderId) {
        return $this->get_order((int)$orderId);
    }

    // =====================================================================
    // EVALUASI LEWAT-AMBANG (dipakai saat entri hasil & cetak LHU)
    // =====================================================================

    /**
     * Normalisasi hasil uji jadi angka pembanding. Menerima "<0,0005", "42", "0,09".
     * Mengembalikan float atau NULL bila tak bisa dinumerikkan.
     */
    public function to_numeric($value) {
        if ($value === NULL) return NULL;
        $v = trim((string)$value);
        if ($v === '') return NULL;
        // Buang penanda < atau > (mis. "<0,0005" dianggap 0,0005 utk perbandingan konservatif)
        $v = ltrim($v, "<>≤≥ ");
        $v = str_replace(['.', ' '], ['', ''], $v); // buang pemisah ribuan titik & spasi
        $v = str_replace(',', '.', $v);             // koma desimal -> titik
        if (!is_numeric($v)) return NULL;
        return (float)$v;
    }

    /**
     * Status hasil terhadap ambang: 'exceed' | 'ok' | 'na'.
     * $threshold = baris env_thresholds; $resultValue = teks hasil; $resultNumeric opsional.
     */
    public function evaluate($threshold, $resultValue, $resultNumeric = NULL) {
        if (!$threshold) return 'na';
        $op = $threshold['operator'];

        if ($op === 'none') return 'na';

        if ($op === 'text') {
            if ($resultValue === NULL || trim((string)$resultValue) === '') return 'na';
            $exp = mb_strtolower(trim((string)$threshold['text_expected']));
            $got = mb_strtolower(trim((string)$resultValue));
            return ($exp !== '' && $got === $exp) ? 'ok' : 'exceed';
        }

        $num = ($resultNumeric !== NULL) ? (float)$resultNumeric : $this->to_numeric($resultValue);
        if ($num === NULL) return 'na';

        $min = ($threshold['min_val'] !== NULL) ? (float)$threshold['min_val'] : NULL;
        $max = ($threshold['max_val'] !== NULL) ? (float)$threshold['max_val'] : NULL;

        switch ($op) {
            case 'max':   return ($max !== NULL && $num > $max) ? 'exceed' : 'ok';
            case 'min':   return ($min !== NULL && $num < $min) ? 'exceed' : 'ok';
            case 'range': return (($min !== NULL && $num < $min) || ($max !== NULL && $num > $max)) ? 'exceed' : 'ok';
            case 'equal': return ($max !== NULL && $num == $max) ? 'ok' : 'exceed';
        }
        return 'na';
    }

    // =====================================================================
    // ORDER
    // =====================================================================

    private $roman = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII'];

    /**
     * Nomor urut order tahun berjalan (dipakai utk LHU & FPPS).
     */
    private function _next_seq() {
        $year = date('Y');
        $like = '%/LKL-PR/%/' . $year;
        $this->db->like('order_number', '/' . $year, 'before'); // longgar; fallback hitung tahun ini
        $this->db->where('YEAR(created_at)', $year);
        $count = $this->db->count_all_results('env_orders');
        return $count + 1;
    }

    public function generate_numbers() {
        $seq = $this->_next_seq();
        $seqStr = str_pad($seq, 4, '0', STR_PAD_LEFT);
        $rm = $this->roman[(int)date('n')];
        $year = date('Y');
        return [
            'order_number' => sprintf('%s/LHU/LKL-PR/%s/%s', $seqStr, $rm, $year),
            'fpps_number'  => sprintf('%s/FPPS/LKL-PR/%s/%s', $seqStr, $rm, $year),
            'lab_number'   => $seqStr,
        ];
    }

    public function get_orders_datatable($params) {
        $start  = isset($params['start']) ? (int)$params['start'] : 0;
        $length = isset($params['length']) ? (int)$params['length'] : 10;
        $search = isset($params['search']['value']) ? trim($params['search']['value']) : '';
        $status = isset($params['status']) ? $params['status'] : 'all';

        $this->db->from('env_orders o');
        $this->db->join('env_sample_types st', 'st.id = o.sample_type_id', 'left');
        $this->db->join('env_standards s', 's.id = o.standard_id', 'left');
        $this->db->select('o.*, st.name AS sample_type_name, s.code AS standard_code, s.name AS standard_name');

        $recordsTotal = $this->db->count_all_results('', FALSE);

        if ($status !== 'all' && $status !== '') {
            $this->db->where('o.status', $status);
        }
        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('o.order_number', $search);
            $this->db->or_like('o.fpps_number', $search);
            $this->db->or_like('o.customer_name', $search);
            $this->db->or_like('o.sample_description', $search);
            $this->db->group_end();
        }
        $recordsFiltered = $this->db->count_all_results('', FALSE);

        $this->db->order_by('o.id', 'DESC');
        if ($length > 0) $this->db->limit($length, $start);
        $rows = $this->db->get()->result_array();

        return [
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ];
    }

    public function get_order($id) {
        $this->db->from('env_orders o');
        $this->db->join('env_sample_types st', 'st.id = o.sample_type_id', 'left');
        $this->db->join('env_standards s', 's.id = o.standard_id', 'left');
        $this->db->join('pendaftaran pd', 'pd.id = o.pendaftaran_id', 'left');
        $this->db->select('o.*, st.name AS sample_type_name, s.code AS standard_code, s.name AS standard_name, s.regulation_ref,
            pd.status_pembayaran AS tagihan_status, pd.nomor_registrasi AS tagihan_reg');
        $this->db->where('o.id', (int)$id);
        return $this->db->get()->row_array();
    }

    public function create_order($data) {
        $this->db->insert('env_orders', $data);
        return $this->db->insert_id();
    }

    public function update_order($id, $data) {
        $ok = $this->db->where('id', (int)$id)->update('env_orders', $data);
        if (isset($data['status'])) {
            $this->sync_pendaftaran_status($id);
        }
        return $ok;
    }

    /**
     * Samakan status_order pendaftaran (tagihan) dengan status order Kesling,
     * supaya daftar Pendaftaran Uji Lingkungan menampilkan progres yang benar.
     */
    public function sync_pendaftaran_status($orderId) {
        $o = $this->db->select('status, pendaftaran_id')->get_where('env_orders', ['id' => (int)$orderId])->row_array();
        if (!$o || empty($o['pendaftaran_id'])) return;
        $map = ['registered' => 'registered', 'sampling' => 'sampling', 'testing' => 'processing',
                'validasi' => 'validasi', 'completed' => 'completed', 'cancelled' => 'cancelled'];
        if (!isset($map[$o['status']])) return;
        $upd = ['status_order' => $map[$o['status']], 'updated_at' => date('Y-m-d H:i:s')];
        if ($o['status'] === 'completed') $upd['waktu_hasil_selesai'] = date('Y-m-d H:i:s');
        $this->db->where('id', (int)$o['pendaftaran_id'])->where('status_pembayaran !=', 'CANCELLED')->update('pendaftaran', $upd);
    }

    public function delete_order($id) {
        return $this->db->where('id', (int)$id)->delete('env_orders');
    }

    /**
     * Buat order Kesling dari pendaftaran kategori 'lingkungan' (form Pendaftaran).
     * Tagihan sudah ada di pendaftaran, jadi order langsung ditautkan ke sana.
     * Jenis sampel di luar tipe Kesling (mis. Makanan/Minuman) tidak dibuatkan order.
     */
    public function create_from_pendaftaran($reg, $userId = null, array $items = []) {
        if (!$this->db->table_exists('env_orders')) {
            return ['status' => 'unavailable'];
        }
        if ($this->db->where('pendaftaran_id', (int)$reg['id'])->count_all_results('env_orders') > 0) {
            return ['status' => 'exists'];
        }

        $codeBySample = [
            'Air Minum' => 'AIR_MINUM',
            'Air Bersih / Higiene Sanitasi' => 'AIR_BERSIH',
            'Air Limbah' => 'AIR_LIMBAH',
            'Badan Air (Sungai / Danau)' => 'BADAN_AIR',
            'Udara' => 'UDARA',
            'Makanan / Minuman' => 'MAKANAN',
        ];
        $code = $codeBySample[$reg['jenis_sampel']] ?? null;
        $type = $code ? $this->db->get_where('env_sample_types', ['code' => $code])->row_array() : null;
        if (!$type) {
            return ['status' => 'no_type'];
        }

        $names = [];
        foreach ($items as $it) {
            $itemType = $it['tipe_item'] ?? '';
            if ($itemType === 'env_parameter' && !empty($it['nama_item'])) {
                $names[] = $it['nama_item'];
            } elseif ($itemType === 'env_package' && !empty($it['nama_item'])) {
                $inner = !empty($it['param_names']) && is_array($it['param_names']) ? ' [' . implode(', ', $it['param_names']) . ']' : '';
                $names[] = 'Paket ' . $it['nama_item'] . $inner;
            }
        }
        $notes = trim(($reg['diagnosa_klinis'] ?? '') . ($names ? "\nParameter dipesan (" . count($names) . "): " . implode(', ', $names) : ''));

        // Standar acuan default = baku mutu aktif pertama untuk tipe sampel ini (bisa diganti di Order Lingkungan)
        $std = $this->db->select('id')->where('sample_type_id', (int)$type['id'])->where('is_active', 1)
            ->order_by('sort_order', 'ASC')->order_by('id', 'ASC')->limit(1)->get('env_standards')->row_array();

        $numbers = $this->generate_numbers();
        $this->create_order([
            'order_number' => $numbers['order_number'],
            'fpps_number' => $numbers['fpps_number'],
            'lab_number' => $numbers['lab_number'],
            'sample_type_id' => (int)$type['id'],
            'standard_id' => $std ? (int)$std['id'] : null,
            'customer_name' => $reg['instansi_pengirim'] ?: $reg['nama_pasien'],
            'customer_address' => $reg['alamat'],
            'contact_phone' => $reg['telepon'],
            'sample_description' => $reg['nama_pasien'],
            'sample_location' => $reg['alamat'],
            'status' => 'registered',
            'notes' => $notes !== '' ? $notes : null,
            'biaya_pengujian' => (float)$reg['total_biaya'],
            'pendaftaran_id' => (int)$reg['id'],
            'created_by' => $userId,
        ]);

        return ['status' => 'created', 'order_number' => $numbers['order_number']];
    }

    /**
     * Terbitkan / selaraskan tagihan order lingkungan di tabel pendaftaran
     * (kategori 'lingkungan') supaya tampil di Antrian Kasir & Closing Harian.
     * Tagihan yang sudah PAID/GUARANTEED tidak diubah.
     */
    public function sync_tagihan($orderId, $userId = null) {
        $this->load->model('pendaftaran_model');
        $o = $this->get_order((int)$orderId);
        if (!$o) {
            return ['status' => 'none'];
        }

        $biaya = (float)$o['biaya_pengujian'];
        $linked = !empty($o['pendaftaran_id']);
        $nama = $o['sample_description'] ?: ($o['sample_type_name'] . ' - ' . $o['customer_name']);

        if ($linked) {
            if ($o['tagihan_status'] !== 'UNPAID') {
                return ['status' => 'locked', 'reg' => $o['tagihan_reg']];
            }
            if ($biaya <= 0) {
                $this->pendaftaran_model->batal_pendaftaran($o['pendaftaran_id'], 'Biaya order lingkungan dikosongkan', $userId, 'Sistem');
                $this->db->where('id', (int)$orderId)->update('env_orders', ['pendaftaran_id' => null]);
                return ['status' => 'cancelled'];
            }
            $this->db->where('id', (int)$o['pendaftaran_id'])->update('pendaftaran', [
                'nama_pasien' => $nama,
                'instansi_pengirim' => $o['customer_name'],
                'telepon' => $o['contact_phone'] ?: '-',
                'jenis_sampel' => $o['sample_type_name'],
                'subtotal_tarif' => $biaya,
                'total_biaya' => $biaya,
            ]);
            // Rincian per parameter (dari form Pendaftaran) dibiarkan; hanya tagihan satu-baris yang disesuaikan
            if ($this->db->where('pendaftaran_id', (int)$o['pendaftaran_id'])->count_all_results('pendaftaran_detail') === 1) {
                $this->db->where('pendaftaran_id', (int)$o['pendaftaran_id'])->update('pendaftaran_detail', ['tarif' => $biaya]);
            }
            return ['status' => 'updated', 'reg' => $o['tagihan_reg']];
        }

        if ($biaya <= 0) {
            return ['status' => 'none'];
        }

        $this->db->trans_start();
        $reg = $this->pendaftaran_model->generate_reg_number();
        $this->db->insert('pendaftaran', [
            'nomor_registrasi' => $reg,
            'nomor_billing' => $this->pendaftaran_model->generate_billing_number(),
            'barcode_sampel' => $this->pendaftaran_model->generate_sample_barcode(),
            'client_id' => null,
            'tipe_pasien' => 'perusahaan_lingkungan',
            'tanggal_pendaftaran' => date('Y-m-d'),
            'jam_pendaftaran' => date('H:i:s'),
            'nama_pasien' => $nama,
            'telepon' => $o['contact_phone'] ?: '-',
            'alamat' => $o['customer_address'],
            'instansi_pengirim' => $o['customer_name'],
            'nomor_surat_pengantar' => $o['order_number'],
            'kategori_pemeriksaan' => 'lingkungan',
            'jenis_sampel' => $o['sample_type_name'],
            'alur_pembayaran' => 'mandiri_langsung',
            'kelompok_tarif' => 'perusahaan',
            'subtotal_tarif' => $biaya,
            'total_biaya' => $biaya,
            'status_pembayaran' => 'UNPAID',
            'status_order' => 'registered',
            'catatan' => 'Tagihan order lingkungan ' . $o['order_number'],
            'created_by' => $userId,
        ]);
        $pid = $this->db->insert_id();
        $this->db->insert('pendaftaran_detail', [
            'pendaftaran_id' => $pid,
            'tipe_item' => 'parameter',
            'item_id' => 0,
            'kode_item' => $o['order_number'],
            'nama_item' => 'Pengujian ' . $o['sample_type_name'] . ' (' . $o['order_number'] . ')',
            'kategori_lab' => 'Kesehatan Lingkungan',
            'tarif' => $biaya,
            'status_pengujian' => 'pending',
        ]);
        $this->db->where('id', (int)$orderId)->update('env_orders', ['pendaftaran_id' => $pid]);
        $this->db->trans_complete();

        return $this->db->trans_status() === FALSE ? ['status' => 'error'] : ['status' => 'created', 'reg' => $reg];
    }

    // =====================================================================
    // HASIL
    // =====================================================================

    public function get_results($orderId) {
        $rows = $this->db->where('order_id', (int)$orderId)->get('env_order_results')->result_array();
        $map = [];
        foreach ($rows as $r) {
            $map[(int)$r['parameter_id']] = $r;
        }
        return $map;
    }

    /**
     * Simpan hasil (upsert per parameter). $results = [parameter_id => ['value'=>..,'method'=>..,'notes'=>..]]
     */
    public function save_results($orderId, $results) {
        $orderId = (int)$orderId;
        foreach ($results as $paramId => $r) {
            $paramId = (int)$paramId;
            $value = isset($r['value']) ? trim($r['value']) : '';
            $existing = $this->db->where('order_id', $orderId)->where('parameter_id', $paramId)->get('env_order_results')->row_array();

            if ($value === '' && !isset($r['method']) && !isset($r['notes'])) {
                // kosong & tak ada metadata -> hapus bila ada
                if ($existing) $this->db->where('id', $existing['id'])->delete('env_order_results');
                continue;
            }

            $payload = [
                'result_value'   => $value !== '' ? $value : NULL,
                'result_numeric' => $value !== '' ? $this->to_numeric($value) : NULL,
                'method_used'    => isset($r['method']) ? trim($r['method']) : NULL,
                'notes'          => isset($r['notes']) ? trim($r['notes']) : NULL,
            ];
            if ($existing) {
                $this->db->where('id', $existing['id'])->update('env_order_results', $payload);
            } else {
                $payload['order_id'] = $orderId;
                $payload['parameter_id'] = $paramId;
                $this->db->insert('env_order_results', $payload);
            }
        }
        return TRUE;
    }

    /**
     * Rakit data lengkap untuk cetak LHU / entri hasil: parameter (urut, dikelompokkan
     * sub-kategori) digabung ambang standar terpilih, hasil, dan status evaluasi.
     */
    public function assemble_for_report($orderId) {
        $order = $this->get_order($orderId);
        if (!$order) return NULL;

        $params = $this->get_parameters($order['sample_type_id'], TRUE);
        $thr = $order['standard_id'] ? $this->get_thresholds_by_standard($order['standard_id']) : [];
        $results = $this->get_results($orderId);

        $grouped = [];
        $summary = ['total' => 0, 'exceed' => 0, 'ok' => 0, 'na' => 0];
        foreach ($params as $p) {
            $pid = (int)$p['id'];
            $t = isset($thr[$pid]) ? $thr[$pid] : NULL;
            // Parameter yang tidak punya ambang di standar terpilih tidak ditampilkan di LHU
            if (!$t) continue;

            $res = isset($results[$pid]) ? $results[$pid] : NULL;
            $resultValue = $res ? $res['result_value'] : NULL;
            $resultNumeric = $res ? $res['result_numeric'] : NULL;
            $status = $this->evaluate($t, $resultValue, $resultNumeric);

            $row = [
                'parameter_id' => $pid,
                'param_name'   => $p['param_name'],
                'param_name_en'=> $p['param_name_en'],
                'is_accredited'=> (int)$p['is_accredited'],
                'unit'         => !empty($t['unit_override']) ? $t['unit_override'] : $p['unit'],
                'method'       => $res && !empty($res['method_used']) ? $res['method_used']
                                    : (!empty($t['method_override']) ? $t['method_override'] : $p['method_default']),
                'threshold'    => $t['threshold_display'],
                'result_value' => $resultValue,
                'notes'        => $res ? $res['notes'] : NULL,
                'status'       => $status,
                // field mesin ambang utk evaluasi live di sisi klien (entri hasil)
                'op'           => $t['operator'],
                'min'          => $t['min_val'],
                'max'          => $t['max_val'],
                'text_expected'=> $t['text_expected'],
            ];
            $grouped[$p['sub_category']][] = $row;

            $summary['total']++;
            $summary[$status === 'exceed' ? 'exceed' : ($status === 'ok' ? 'ok' : 'na')]++;
        }

        return ['order' => $order, 'grouped' => $grouped, 'summary' => $summary];
    }

    public function stats() {
        return [
            'total'      => (int)$this->db->count_all('env_orders'),
            'registered' => (int)$this->db->where('status', 'registered')->count_all_results('env_orders'),
            'testing'    => (int)$this->db->where_in('status', ['sampling','testing','validasi'])->count_all_results('env_orders'),
            'completed'  => (int)$this->db->where('status', 'completed')->count_all_results('env_orders'),
        ];
    }
}
