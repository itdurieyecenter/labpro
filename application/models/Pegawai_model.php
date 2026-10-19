<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pegawai_model extends CI_Model {

    protected $table = 'pegawai';

    public function __construct() {
        parent::__construct();
    }

    private function _log($action, $message) {
        log_message('info', sprintf("[PEGAWAI-MODEL] ACTION=%s | MSG=%s", $action, $message));
    }

    public function get_datatable($params = []) {
        $draw = isset($params['draw']) ? (int)$params['draw'] : 1;
        $start = isset($params['start']) ? (int)$params['start'] : 0;
        $length = isset($params['length']) ? (int)$params['length'] : 10;
        $search = isset($params['search']['value']) ? trim($params['search']['value']) : '';
        $statusPegawai = isset($params['status_pegawai']) ? $params['status_pegawai'] : '';
        $seksiRuangan = isset($params['seksi_ruangan']) ? $params['seksi_ruangan'] : '';

        // Total count
        $recordsTotal = $this->db->count_all($this->table);

        // Filtered count query
        $this->db->from($this->table);

        if (!empty($statusPegawai) && $statusPegawai !== 'all') {
            $this->db->where('status_pegawai', $statusPegawai);
        }

        if (!empty($seksiRuangan) && $seksiRuangan !== 'all') {
            $this->db->where('seksi_ruangan', $seksiRuangan);
        }

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('nip', $search);
            $this->db->or_like('nik', $search);
            $this->db->or_like('nama_lengkap', $search);
            $this->db->or_like('nama_jabatan', $search);
            $this->db->or_like('nama_pangkat', $search);
            $this->db->or_like('kode_golongan', $search);
            $this->db->or_like('nomor_str', $search);
            $this->db->or_like('nomor_sip', $search);
            $this->db->or_like('telepon', $search);
            $this->db->group_end();
        }

        $recordsFiltered = $this->db->count_all_results();

        // Data query
        $this->db->from($this->table);

        if (!empty($statusPegawai) && $statusPegawai !== 'all') {
            $this->db->where('status_pegawai', $statusPegawai);
        }

        if (!empty($seksiRuangan) && $seksiRuangan !== 'all') {
            $this->db->where('seksi_ruangan', $seksiRuangan);
        }

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('nip', $search);
            $this->db->or_like('nik', $search);
            $this->db->or_like('nama_lengkap', $search);
            $this->db->or_like('nama_jabatan', $search);
            $this->db->or_like('nama_pangkat', $search);
            $this->db->or_like('kode_golongan', $search);
            $this->db->or_like('nomor_str', $search);
            $this->db->or_like('nomor_sip', $search);
            $this->db->or_like('telepon', $search);
            $this->db->group_end();
        }

        // Sorting mapping
        $orderColumns = [
            0 => 'id',
            1 => 'nip',
            2 => 'nama_lengkap',
            3 => 'kode_golongan',
            4 => 'nama_jabatan',
            5 => 'status_pegawai',
            6 => 'nomor_str'
        ];

        $orderColIdx = isset($params['order'][0]['column']) ? (int)$params['order'][0]['column'] : 1;
        $orderDir = isset($params['order'][0]['dir']) ? $params['order'][0]['dir'] : 'ASC';
        $orderCol = isset($orderColumns[$orderColIdx]) ? $orderColumns[$orderColIdx] : 'nip';

        $this->db->order_by($orderCol, $orderDir);
        $this->db->limit($length, $start);
        $data = $this->db->get()->result_array();

        $no = $start + 1;
        foreach ($data as &$row) {
            $row['no_urut'] = $no++;
            // Format full name with gelar
            $front = !empty($row['gelar_depan']) ? trim($row['gelar_depan']) . ' ' : '';
            $back = !empty($row['gelar_belakang']) ? ', ' . trim($row['gelar_belakang']) : '';
            $row['nama_bergelar'] = $front . $row['nama_lengkap'] . $back;
            
            // Format NIP 18 digit display
            if (strlen($row['nip']) === 18) {
                $row['nip_formatted'] = substr($row['nip'], 0, 8) . ' ' . substr($row['nip'], 8, 6) . ' ' . substr($row['nip'], 14, 1) . ' ' . substr($row['nip'], 15, 3);
            } else {
                $row['nip_formatted'] = $row['nip'];
            }
        }
        unset($row);

        return [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ];
    }

    public function get_by_id($id) {
        $row = $this->db->get_where($this->table, ['id' => (int)$id])->row_array();
        if ($row) {
            $front = !empty($row['gelar_depan']) ? trim($row['gelar_depan']) . ' ' : '';
            $back = !empty($row['gelar_belakang']) ? ', ' . trim($row['gelar_belakang']) : '';
            $row['nama_bergelar'] = $front . $row['nama_lengkap'] . $back;
            
            if (strlen($row['nip']) === 18) {
                $row['nip_formatted'] = substr($row['nip'], 0, 8) . ' ' . substr($row['nip'], 8, 6) . ' ' . substr($row['nip'], 14, 1) . ' ' . substr($row['nip'], 15, 3);
            } else {
                $row['nip_formatted'] = $row['nip'];
            }
        }
        return $row;
    }

    public function get_by_nip($nip) {
        return $this->db->get_where($this->table, ['nip' => trim($nip)])->row_array();
    }

    public function insert($data) {
        $this->db->insert($this->table, $data);
        $insertId = $this->db->insert_id();
        $this->_log('INSERT', "ID={$insertId} | NIP={$data['nip']} | Nama={$data['nama_lengkap']}");
        return $insertId;
    }

    public function update($id, $data) {
        $this->db->where('id', (int)$id)->update($this->table, $data);
        $this->_log('UPDATE', "ID={$id} | NIP={$data['nip']}");
        return true;
    }

    public function delete($id) {
        $this->db->where('id', (int)$id)->delete($this->table);
        $this->_log('DELETE', "ID={$id}");
        return true;
    }

    public function count_summary() {
        $total = $this->db->count_all($this->table);

        $pns = $this->db->where('status_pegawai', 'PNS')->count_all_results($this->table);
        $cpns = $this->db->where('status_pegawai', 'CPNS')->count_all_results($this->table);
        $pppk = $this->db->where('status_pegawai', 'PPPK')->count_all_results($this->table);
        $nonAsn = $this->db->where('status_pegawai', 'Non-ASN / BLUD')->count_all_results($this->table);

        // Tenaga medis / nakes dengan STR terdaftar
        $strAktif = $this->db->where('nomor_str IS NOT NULL', null, false)->where('nomor_str !=', '')->count_all_results($this->table);

        return [
            'total' => $total,
            'pns' => $pns,
            'cpns' => $cpns,
            'asn_total' => $pns + $cpns,
            'pppk' => $pppk,
            'non_asn' => $nonAsn,
            'str_aktif' => $strAktif
        ];
    }

    /**
     * Referensi Golongan Ruang BKN & Pangkat Resmi
     */
    public function get_pangkat_reference() {
        $rows = $this->get_referensi('golongan_ruang');
        if (!empty($rows)) {
            $ref = [];
            foreach ($rows as $r) {
                $ref[$r['kode']] = $r['nama'];
            }
            return $ref;
        }

        return [
            'I/a' => 'Juru Muda',
            'I/b' => 'Juru Muda Tingkat I',
            'I/c' => 'Juru',
            'I/d' => 'Juru Tingkat I',
            'II/a' => 'Pengatur Muda',
            'II/b' => 'Pengatur Muda Tingkat I',
            'II/c' => 'Pengatur',
            'II/d' => 'Pengatur Tingkat I',
            'III/a' => 'Penata Muda',
            'III/b' => 'Penata Muda Tingkat I',
            'III/c' => 'Penata',
            'III/d' => 'Penata Tingkat I',
            'IV/a' => 'Pembina',
            'IV/b' => 'Pembina Tingkat I',
            'IV/c' => 'Pembina Utama Muda',
            'IV/d' => 'Pembina Utama Madya',
            'IV/e' => 'Pembina Utama',
            'IX' => 'Golongan IX (PPPK D-IV / S-1)',
            'X' => 'Golongan X (PPPK S-1 Profesi)',
            'VII' => 'Golongan VII (PPPK D-III)'
        ];
    }

    /**
     * Referensi Seksi & Instalasi UPT Labkesda Riau
     */
    public function get_seksi_reference() {
        $rows = $this->get_referensi('seksi_ruangan');
        if (!empty($rows)) {
            $ref = [];
            foreach ($rows as $r) {
                $key = !empty($r['kode']) ? $r['kode'] : $r['nama'];
                $ref[$key] = !empty($r['keterangan']) ? $r['keterangan'] : $r['nama'];
            }
            return $ref;
        }

        return [
            'Pimpinan & Tata Usaha' => 'Subbag Tata Usaha & Kepegawaian',
            'Instalasi Laboratorium Klinik' => 'Instalasi Laboratorium Klinik Terpadu',
            'Instalasi Hematologi & Urinalisa' => 'Instalasi Hematologi, Urinalisa & Cairan Tubuh',
            'Instalasi Kimia Klinik' => 'Instalasi Kimia Klinik & Imunologi',
            'Instalasi Mikrobiologi & PCR' => 'Instalasi Mikrobiologi, Kultur & Biomolekuler/PCR',
            'Instalasi Kimia Air & Makanan Minuman' => 'Instalasi Kimia Lingkungan, Air & Makanan Minuman (Kesmas)',
            'Instalasi Flebotomi & Registrasi Sampel' => 'Instalasi Pengambilan Sampel (Flebotomi) & Loket Penerimaan',
            'Mutu, K3 & Akreditasi Lab' => 'Instalasi Pengendalian Mutu (ISO 15189/17025) & K3'
        ];
    }

    /**
     * Ambil data referensi dinamis per kategori atau semua
     */
    public function get_referensi($kategori = null) {
        $this->db->from('pegawai_referensi');
        if (!empty($kategori) && $kategori !== 'all') {
            $this->db->where('kategori', $kategori);
        }
        $this->db->where('is_active', 1);
        $this->db->order_by('sort_order', 'ASC');
        $this->db->order_by('id', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_referensi_by_id($id) {
        return $this->db->where('id', (int)$id)->get('pegawai_referensi')->row_array();
    }

    /**
     * Hitung nomor urut berikutnya untuk kategori referensi
     */
    public function get_next_ref_sort_order($kategori) {
        $row = $this->db->select_max('sort_order')->where('kategori', $kategori)->get('pegawai_referensi')->row();
        return ($row && $row->sort_order) ? ((int)$row->sort_order + 1) : 1;
    }

    /**
     * Cek apakah kode referensi sudah terdaftar dalam kategori tertentu
     */
    public function is_ref_code_exists($kategori, $code) {
        return $this->db->where('kategori', $kategori)
            ->where('kode', trim($code))
            ->count_all_results('pegawai_referensi') > 0;
    }

    /**
     * SMART SYSTEM CODE GENERATOR
     * "User cukup buat sekali saja, selebihnya program mengikuti"
     * 1. Mengikuti pola prefix & urutan yang pernah dibuat oleh user.
     * 2. Menghasilkan akronim cerdas atau nomor urut otomatis jika kode tidak diisi.
     */
    public function generate_reference_code($kategori, $nama = '') {
        $kategori = trim($kategori);
        $nama = trim($nama);

        // 1. Ekstrak singkatan dalam kurung jika ada (misal: "Instalasi Gawat Darurat (IGD)" -> "IGD")
        if (!empty($nama) && preg_match('/\(([A-Za-z0-9\/\-\.]+)\)/', $nama, $matches)) {
            $candidate = strtoupper(trim($matches[1]));
            if (strlen($candidate) <= 20 && !$this->is_ref_code_exists($kategori, $candidate)) {
                return $candidate;
            }
        }

        // 2. Kategori khusus: golongan_ruang (jika ada romawi di nama seperti XI, XII, V/a, dsb)
        if ($kategori === 'golongan_ruang' && !empty($nama)) {
            if (preg_match('/\b(I{1,3}|IV|V|VI{1,3}|IX|X{1,3}|XI{1,3}|XIV|XV)\/?([a-e])?\b/i', $nama, $m)) {
                $rom = strtoupper($m[0]);
                if (!$this->is_ref_code_exists($kategori, $rom)) {
                    return $rom;
                }
            }
        }

        // 3. Kategori khusus: bank_payroll (akronim otomatis jika nama terdiri dari beberapa kata)
        if ($kategori === 'bank_payroll' && !empty($nama)) {
            $words = preg_split('/\s+/', $nama);
            if (count($words) >= 2) {
                $acronym = '';
                foreach ($words as $w) {
                    $wClean = preg_replace('/[^A-Za-z0-9]/', '', $w);
                    if (!empty($wClean)) {
                        $acronym .= strtoupper($wClean[0]);
                    }
                }
                if (strlen($acronym) >= 2 && strlen($acronym) <= 10 && !$this->is_ref_code_exists($kategori, $acronym)) {
                    return $acronym;
                }
            } else {
                $cleanOne = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $words[0]));
                if (strlen($cleanOne) <= 10 && !$this->is_ref_code_exists($kategori, $cleanOne)) {
                    return $cleanOne;
                }
            }
        }

        // 4. Kategori khusus: status_pegawai
        if ($kategori === 'status_pegawai' && !empty($nama)) {
            $words = preg_split('/\s+/', $nama);
            if (count($words) >= 2) {
                $acronym = '';
                foreach ($words as $w) {
                    $wClean = preg_replace('/[^A-Za-z0-9]/', '', $w);
                    if (!empty($wClean)) {
                        $acronym .= strtoupper($wClean[0]);
                    }
                }
                if (strlen($acronym) >= 2 && strlen($acronym) <= 10 && !$this->is_ref_code_exists($kategori, $acronym)) {
                    return $acronym;
                }
            }
        }

        // 5. PENERAPAN POLA MENGIKUTI USER ("User buat sekali, program mengikuti"):
        // Ambil data referensi yang ada pada kategori ini untuk mendeteksi pola yang digunakan user
        $existingRows = $this->db->select('kode')
            ->where('kategori', $kategori)
            ->where('kode IS NOT NULL', null, false)
            ->order_by('id', 'DESC')
            ->get('pegawai_referensi')
            ->result_array();

        $detectedPrefix = null;
        $maxNumber = 0;
        $padLen = 2;

        // Default prefix per kategori jika belum ada format berurutan
        $defaultPrefixes = [
            'seksi_ruangan'   => 'SEK',
            'jenis_jabatan'   => 'JAB',
            'kedudukan_hukum' => 'KED',
            'golongan_ruang'  => 'GOL',
            'bank_payroll'    => 'BNK',
            'status_pegawai'  => 'STP'
        ];

        // Deteksi pola prefix dari data terakhir yang memiliki format berurutan
        foreach ($existingRows as $r) {
            $c = trim($r['kode']);
            if (preg_match('/^([A-Za-z\-]+)[-_]?([0-9]+)$/', $c, $matches)) {
                $detectedPrefix = rtrim($matches[1], '-_');
                break;
            }
        }

        // Tentukan prefix aktif yang akan diikuti
        if ($detectedPrefix !== null) {
            $prefix = $detectedPrefix;
        } else {
            $prefix = isset($defaultPrefixes[$kategori]) ? $defaultPrefixes[$kategori] : 'REF';
        }

        // Cari nomor urut tertinggi KHUSUS untuk prefix aktif ini
        foreach ($existingRows as $r) {
            $c = trim($r['kode']);
            if (preg_match('/^' . preg_quote($prefix, '/') . '[-_]?([0-9]+)$/i', $c, $m)) {
                $num = (int)$m[1];
                $padLen = max($padLen, strlen($m[1]));
                if ($num > $maxNumber) {
                    $maxNumber = $num;
                }
            }
        }

        // Jika prefix baru belum memiliki nomor urut dan bukan dari kustom user, gunakan total baris
        if ($maxNumber === 0 && $detectedPrefix === null) {
            $maxNumber = count($existingRows);
        }

        // Generate nomor berikutnya
        $nextNum = $maxNumber + 1;
        $newCode = sprintf('%s-%0' . $padLen . 'd', $prefix, $nextNum);

        // Pastikan tidak ada duplikasi kode
        while ($this->is_ref_code_exists($kategori, $newCode)) {
            $nextNum++;
            $newCode = sprintf('%s-%0' . $padLen . 'd', $prefix, $nextNum);
        }

        $this->_log('AUTO_CODE_REF', "Kategori={$kategori} | Prefix={$prefix} | Generated={$newCode}");
        return $newCode;
    }

    /**
     * GENERATOR KODE NIP / ID PEGAWAI OTOMATIS (TERUTAMA NON-ASN / BLUD / KONTRAK)
     * Format Standar SIMRS 18 Digit Kompatibel BKN:
     * - 2 Digit: Prefix Status (99 = Non-ASN/BLUD, 98 = Honorer/Kontrak)
     * - 6 Digit: Tanggal Lahir (YYMMDD)
     * - 6 Digit: Periode Pengangkatan / Masuk (YYYYMM)
     * - 1 Digit: Jenis Kelamin (1 = Laki-laki, 2 = Perempuan)
     * - 3 Digit: Nomor Urut Unik (001, 002, ...)
     */
    public function generate_pegawai_nip($status = 'Non-ASN / BLUD', $tglLahir = null, $jk = 'L') {
        $prefixTwo = '99';
        $statusUpper = strtoupper($status);
        if (strpos($statusUpper, 'HONORER') !== false || strpos($statusUpper, 'KONTRAK') !== false) {
            $prefixTwo = '98';
        }

        $birthStr = '19950101';
        if (!empty($tglLahir)) {
            $ts = strtotime($tglLahir);
            if ($ts) {
                $birthStr = date('Ymd', $ts);
            }
        }
        $birthPart = substr($birthStr, 2, 6); // YYMMDD (6 digit)

        $joinedPart = date('Ym'); // YYYYMM (6 digit, e.g. 202610)
        $jkPart = (strtoupper($jk) === 'P') ? '2' : '1'; // 1 digit

        $baseNip = $prefixTwo . $birthPart . $joinedPart . $jkPart; // Total 15 digit

        $seq = 1;
        $nipCandidate = sprintf('%s%03d', $baseNip, $seq); // Total 18 digit

        while ($this->db->where('nip', $nipCandidate)->count_all_results($this->table) > 0) {
            $seq++;
            $nipCandidate = sprintf('%s%03d', $baseNip, $seq);
        }

        $this->_log('AUTO_CODE_PEGAWAI', "Status={$status} | NIP={$nipCandidate}");
        return $nipCandidate;
    }

    public function save_referensi($data, $id = null) {
        $now = date('Y-m-d H:i:s');

        // Otomatisasi: Jika kode kosong, program otomatis generate mengikuti pola!
        if (empty($data['kode'])) {
            $data['kode'] = $this->generate_reference_code($data['kategori'], $data['nama']);
        }

        // Otomatisasi: Jika sort_order kosong / 0, program otomatis set ke urutan berikutnya
        if (empty($data['sort_order']) || (int)$data['sort_order'] <= 0) {
            $data['sort_order'] = $this->get_next_ref_sort_order($data['kategori']);
        }

        if ($id) {
            $data['updated_at'] = $now;
            $this->db->where('id', (int)$id)->update('pegawai_referensi', $data);
            $this->_log('UPDATE_REF', "ID={$id} | Kategori={$data['kategori']} | Kode={$data['kode']}");
            return (int)$id;
        } else {
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
            $this->db->insert('pegawai_referensi', $data);
            $newId = $this->db->insert_id();
            $this->_log('CREATE_REF', "ID={$newId} | Kategori={$data['kategori']} | Kode={$data['kode']}");
            return $newId;
        }
    }

    public function delete_referensi($id) {
        $this->db->where('id', (int)$id)->delete('pegawai_referensi');
        $this->_log('DELETE_REF', "ID={$id}");
        return true;
    }

    public function get_all_form_references() {
        return [
            'golongan_ruang' => $this->get_referensi('golongan_ruang'),
            'kedudukan_hukum' => $this->get_referensi('kedudukan_hukum'),
            'jenis_jabatan' => $this->get_referensi('jenis_jabatan'),
            'seksi_ruangan' => $this->get_referensi('seksi_ruangan'),
            'bank_payroll' => $this->get_referensi('bank_payroll'),
            'status_pegawai' => $this->get_referensi('status_pegawai')
        ];
    }
}
