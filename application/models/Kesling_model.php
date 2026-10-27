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
            foreach ($standards as $s) {
                $sid = (int)$s['id'];
                $pid = (int)$p['id'];
                $p['thresholds'][$sid] = isset($thrByStd[$sid][$pid]) ? $thrByStd[$sid][$pid]['threshold_display'] : NULL;
            }
        }
        unset($p);
        return ['standards' => $standards, 'parameters' => $params];
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

        $recordsTotal = $this->db->count_all_results('env_orders', FALSE);

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
        $this->db->select('o.*, st.name AS sample_type_name, s.code AS standard_code, s.name AS standard_name, s.regulation_ref');
        $this->db->where('o.id', (int)$id);
        return $this->db->get()->row_array();
    }

    public function create_order($data) {
        $this->db->insert('env_orders', $data);
        return $this->db->insert_id();
    }

    public function update_order($id, $data) {
        return $this->db->where('id', (int)$id)->update('env_orders', $data);
    }

    public function delete_order($id) {
        return $this->db->where('id', (int)$id)->delete('env_orders');
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
