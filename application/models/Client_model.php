<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Client_model extends CI_Model {

    protected $table = 'clients';

    public function __construct() {
        parent::__construct();
    }

    private function _db_log($action, $message) {
        log_message('info', sprintf("[CLIENT-MODEL] ACTION=%s | TABLE=%s | MSG=%s", $action, $this->table, $message));
    }

    public function get_all($client_type = null, $search = null, $status = null) {
        $this->db->from($this->table);

        if (!empty($client_type) && in_array($client_type, ['perorangan', 'perusahaan'])) {
            $this->db->where('client_type', $client_type);
        }

        if (!empty($status) && in_array($status, ['active', 'inactive'])) {
            $this->db->where('status', $status);
        }

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('client_code', $search);
            $this->db->or_like('name', $search);
            $this->db->or_like('identity_number', $search);
            $this->db->or_like('phone', $search);
            $this->db->or_like('pic_name', $search);
            $this->db->or_like('email', $search);
            $this->db->group_end();
        }

        $this->db->order_by('id', 'DESC');
        $result = $this->db->get()->result_array();

        $this->_db_log('READ_LIST', sprintf("Filter: type=%s, search=%s, status=%s | Hasil: %d baris", $client_type ?? 'all', $search ?? '-', $status ?? '-', count($result)));

        return $result;
    }

    public function get_by_id($id) {
        $result = $this->db->get_where($this->table, ['id' => (int)$id])->row_array();

        if ($result) {
            $this->_db_log('READ_BY_ID', sprintf("ID=%d | Kode=%s | Nama=%s", $result['id'], $result['client_code'], $result['name']));
        } else {
            $this->_db_log('READ_BY_ID_NOT_FOUND', sprintf("ID=%d tidak ditemukan", (int)$id));
        }

        return $result;
    }

    public function get_by_code($code) {
        $result = $this->db->get_where($this->table, ['client_code' => $code])->row_array();

        if ($result) {
            $this->_db_log('READ_BY_CODE', sprintf("Kode=%s | ID=%d | Nama=%s", $code, $result['id'], $result['name']));
        } else {
            $this->_db_log('READ_BY_CODE_NOT_FOUND', sprintf("Kode=%s tidak ditemukan", $code));
        }

        return $result;
    }

    public function insert($data) {
        $this->_db_log('INSERT_BEFORE', sprintf("Data=%s", json_encode($data)));
        $this->db->insert($this->table, $data);
        $insertId = $this->db->insert_id();
        $this->_db_log('INSERT_SUCCESS', sprintf("ID=%d | Kode=%s | Nama=%s", $insertId, $data['client_code'] ?? '-', $data['name'] ?? '-'));
        return $insertId;
    }

    public function update($id, $data) {
        $this->_db_log('UPDATE_BEFORE', sprintf("ID=%d | Data=%s", (int)$id, json_encode($data)));
        $this->db->where('id', (int)$id);
        $affected = $this->db->update($this->table, $data);
        $this->_db_log('UPDATE_SUCCESS', sprintf("ID=%d | Baris terpengaruh=%d | Kode=%s | Nama=%s", (int)$id, $affected, $data['client_code'] ?? '-', $data['name'] ?? '-'));
        return $affected;
    }

    public function delete($id) {
        $client = $this->db->get_where($this->table, ['id' => (int)$id])->row_array();
        if ($client) {
            $this->_db_log('DELETE_BEFORE', sprintf("ID=%d | Kode=%s | Nama=%s", $client['id'], $client['client_code'], $client['name']));
        } else {
            $this->_db_log('DELETE_NOT_FOUND', sprintf("ID=%d tidak ditemukan", (int)$id));
        }

        $this->db->where('id', (int)$id);
        $affected = $this->db->delete($this->table);

        if ($affected > 0) {
            $this->_db_log('DELETE_SUCCESS', sprintf("ID=%d | Kode=%s | Nama=%s telah dihapus", (int)$id, $client['client_code'] ?? '-', $client['name'] ?? '-'));
        }

        return $affected;
    }

    public function generate_code($client_type) {
        $prefix = ($client_type === 'perusahaan') ? 'CORP-' : 'PSN-';
        $year = date('Y');
        $fullPrefix = $prefix . $year . '-';

        $this->db->select('client_code');
        $this->db->like('client_code', $fullPrefix, 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $lastRow = $this->db->get($this->table)->row_array();

        if ($lastRow) {
            $lastNum = (int)substr($lastRow['client_code'], -4);
            $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }

        $code = $fullPrefix . $nextNum;

        $this->_db_log('GENERATE_CODE', sprintf("Tipe=%s | Kode terakhir=%s | Kode baru=%s", $client_type, $lastRow['client_code'] ?? '-', $code));

        return $code;
    }

    public function count_summary() {
        $total = $this->db->count_all($this->table);

        $this->db->where('client_type', 'perorangan');
        $perorangan = $this->db->count_all_results($this->table);

        $this->db->where('client_type', 'perusahaan');
        $perusahaan = $this->db->count_all_results($this->table);

        $this->db->where('status', 'active');
        $active = $this->db->count_all_results($this->table);

        $this->_db_log('COUNT_SUMMARY', sprintf("Total=%d | Perorangan=%d | Perusahaan=%d | Aktif=%d", $total, $perorangan, $perusahaan, $active));

        return [
            'total' => $total,
            'perorangan' => $perorangan,
            'perusahaan' => $perusahaan,
            'active' => $active
        ];
    }

    /**
     * DataTables Server-Side Processing
     * Returns: draw, recordsTotal, recordsFiltered, data[]
     */
    public function get_all_datatable($params = []) {
        $draw = isset($params['draw']) ? (int)$params['draw'] : 1;
        $start = isset($params['start']) ? (int)$params['start'] : 0;
        $length = isset($params['length']) ? (int)$params['length'] : 10;
        $search = isset($params['search']['value']) ? $params['search']['value'] : '';
        $client_type = isset($params['client_type']) ? $params['client_type'] : null;
        $status = isset($params['status']) ? $params['status'] : null;

        // Order mapping: DataTables column index -> DB column
        $orderColumns = [
            0 => 'no_urut', // nomor urut (computed, not sortable)
            1 => 'client_code',
            2 => 'name',
            3 => 'client_type',
            4 => 'phone',
            5 => 'status',
            6 => 'id', // aksi (not sortable)
        ];

        $orderColumnIndex = isset($params['order'][0]['column']) ? (int)$params['order'][0]['column'] : 1;
        $orderDir = isset($params['order'][0]['dir']) ? $params['order'][0]['dir'] : 'desc';
        $orderColumn = isset($orderColumns[$orderColumnIndex]) ? $orderColumns[$orderColumnIndex] : 'client_code';

        // Total records (no filter)
        $this->db->from($this->table);
        $recordsTotal = $this->db->count_all_results();

        // Filtered records (with client_type, status, search)
        $this->db->from($this->table);

        if (!empty($client_type) && in_array($client_type, ['perorangan', 'perusahaan'])) {
            $this->db->where('client_type', $client_type);
        }

        if (!empty($status) && in_array($status, ['active', 'inactive'])) {
            $this->db->where('status', $status);
        }

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('client_code', $search);
            $this->db->or_like('name', $search);
            $this->db->or_like('identity_number', $search);
            $this->db->or_like('phone', $search);
            $this->db->or_like('pic_name', $search);
            $this->db->or_like('email', $search);
            $this->db->group_end();
        }

        $recordsFiltered = $this->db->count_all_results();

        // Get data with pagination and ordering
        $this->db->from($this->table);

        if (!empty($client_type) && in_array($client_type, ['perorangan', 'perusahaan'])) {
            $this->db->where('client_type', $client_type);
        }

        if (!empty($status) && in_array($status, ['active', 'inactive'])) {
            $this->db->where('status', $status);
        }

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('client_code', $search);
            $this->db->or_like('name', $search);
            $this->db->or_like('identity_number', $search);
            $this->db->or_like('phone', $search);
            $this->db->or_like('pic_name', $search);
            $this->db->or_like('email', $search);
            $this->db->group_end();
        }

        // Order by: skip nomor_urut (0) and aksi (6) as they are not real columns
        if ($orderColumn === 'no_urut' || $orderColumn === 'id') {
            $this->db->order_by('id', $orderDir);
        } else {
            $this->db->order_by($orderColumn, $orderDir);
        }

        $this->db->limit($length, $start);
        $data = $this->db->get()->result_array();

        // Add nomor urut (row number) starting from start + 1
        $no = $start + 1;
        foreach ($data as &$row) {
            $row['no_urut'] = $no++;
        }
        unset($row);

        $this->_db_log('READ_DATATABLE', sprintf("draw=%d | start=%d | length=%d | search=%s | type=%s | status=%s | total=%d | filtered=%d | returned=%d",
            $draw, $start, $length, $search ?? '-', $client_type ?? 'all', $status ?? 'all', $recordsTotal, $recordsFiltered, count($data)));

        return [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ];
    }

    public function count_summary_by_filter($client_type = null, $status = null) {
        $this->db->from($this->table);

        if (!empty($client_type) && in_array($client_type, ['perorangan', 'perusahaan'])) {
            $this->db->where('client_type', $client_type);
        }

        if (!empty($status) && in_array($status, ['active', 'inactive'])) {
            $this->db->where('status', $status);
        }

        $total = $this->db->count_all_results();

        $this->db->from($this->table);

        if (!empty($client_type) && in_array($client_type, ['perorangan', 'perusahaan'])) {
            $this->db->where('client_type', $client_type);
        }

        if (!empty($status) && in_array($status, ['active', 'inactive'])) {
            $this->db->where('status', $status);
        }

        $this->db->where('client_type', 'perorangan');
        $perorangan = $this->db->count_all_results();

        $this->db->from($this->table);

        if (!empty($client_type) && in_array($client_type, ['perorangan', 'perusahaan'])) {
            $this->db->where('client_type', $client_type);
        }

        if (!empty($status) && in_array($status, ['active', 'inactive'])) {
            $this->db->where('status', $status);
        }

        $this->db->where('client_type', 'perusahaan');
        $perusahaan = $this->db->count_all_results();

        $this->db->from($this->table);

        if (!empty($client_type) && in_array($client_type, ['perorangan', 'perusahaan'])) {
            $this->db->where('client_type', $client_type);
        }

        if (!empty($status) && in_array($status, ['active', 'inactive'])) {
            $this->db->where('status', $status);
        }

        $this->db->where('status', 'active');
        $active = $this->db->count_all_results();

        $this->_db_log('COUNT_SUMMARY_BY_FILTER', sprintf("type=%s | status=%s | total=%d | perorangan=%d | perusahaan=%d | aktif=%d",
            $client_type ?? 'all', $status ?? 'all', $total, $perorangan, $perusahaan, $active));

        return [
            'total' => $total,
            'perorangan' => $perorangan,
            'perusahaan' => $perusahaan,
            'active' => $active
        ];
    }
}
