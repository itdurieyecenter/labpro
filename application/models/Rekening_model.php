<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rekening_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // =========================================================================
    // 1. DASHBOARD & SUMMARY STATISTIK
    // =========================================================================
    public function get_summary() {
        // Total COA
        $total_coa = $this->db->count_all('akun_rekening');
        $total_coa_aktif = $this->db->where('is_active', 1)->count_all_results('akun_rekening');

        // Total Kas & Bank Aktif
        $kas_bank_aktif = $this->db->where(['is_kas_bank' => 1, 'is_active' => 1])->count_all_results('akun_rekening');

        // Akun Bayar Kasir
        $total_akun_bayar = $this->db->where('is_active', 1)->count_all_results('akun_bayar');

        // Akun Piutang Penjamin
        $total_akun_piutang = $this->db->where('is_active', 1)->count_all_results('akun_piutang');

        // Total Saldo Kas Berjalan (Tahun 2026 atau tahun max)
        $max_tahun = $this->db->select_max('tahun')->get('rekening_tahun')->row()->tahun ?? date('Y');
        $saldo_kas_query = $this->db->select_sum('rt.saldo_akhir')
            ->from('rekening_tahun rt')
            ->join('akun_rekening ar', 'ar.kode_rekening = rt.kode_rekening')
            ->where('rt.tahun', $max_tahun)
            ->where('ar.is_kas_bank', 1)
            ->get()->row();
        $total_saldo_kas = $saldo_kas_query->saldo_akhir ?? 0;

        // Total Piutang Berjalan
        $saldo_piutang_query = $this->db->select_sum('rt.saldo_akhir')
            ->from('rekening_tahun rt')
            ->join('akun_rekening ar', 'ar.kode_rekening = rt.kode_rekening')
            ->where('rt.tahun', $max_tahun)
            ->where('ar.is_piutang', 1)
            ->get()->row();
        $total_saldo_piutang = $saldo_piutang_query->saldo_akhir ?? 0;

        return [
            'total_coa'            => (int)$total_coa,
            'total_coa_aktif'      => (int)$total_coa_aktif,
            'kas_bank_aktif'       => (int)$kas_bank_aktif,
            'total_akun_bayar'     => (int)$total_akun_bayar,
            'total_akun_piutang'   => (int)$total_akun_piutang,
            'tahun_terkini'        => (int)$max_tahun,
            'total_saldo_kas'      => (float)$total_saldo_kas,
            'total_saldo_piutang'  => (float)$total_saldo_piutang
        ];
    }

    // =========================================================================
    // 1B. MASTER KATEGORI AKUN (PSAK)
    // =========================================================================
    public function get_all_kategori($only_active = false) {
        $this->db->select('ka.*, COUNT(ar.id) as total_akun');
        $this->db->from('kategori_akun ka');
        $this->db->join('akun_rekening ar', 'ar.kategori_id = ka.id OR ar.kategori = ka.slug', 'left');
        if ($only_active) {
            $this->db->where('ka.is_active', 1);
        }
        $this->db->group_by('ka.id');
        $this->db->order_by('ka.urutan', 'ASC');
        $this->db->order_by('ka.kode_kategori', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_kategori_by_id($id) {
        return $this->db->get_where('kategori_akun', ['id' => $id])->row_array();
    }

    public function get_kategori_by_kode($kode) {
        return $this->db->get_where('kategori_akun', ['kode_kategori' => $kode])->row_array();
    }

    public function get_kategori_by_slug($slug) {
        return $this->db->get_where('kategori_akun', ['slug' => $slug])->row_array();
    }

    public function insert_kategori($data) {
        if (empty($data['slug'])) {
            $slug_source = $data['nama_kategori'] ?? 'kategori_' . time();
            $data['slug'] = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($slug_source)));
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('kategori_akun', $data);
        return $this->db->insert_id();
    }

    public function update_kategori($id, $data) {
        if (isset($data['nama_kategori']) && empty($data['slug'])) {
            $data['slug'] = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($data['nama_kategori'])));
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update('kategori_akun', $data);
    }

    public function delete_kategori($id) {
        $kat = $this->get_kategori_by_id($id);
        if (!$kat) return ['status' => false, 'message' => 'Kategori akun tidak ditemukan.'];
        if (!empty($kat['is_system'])) {
            return ['status' => false, 'message' => 'Kategori standar sistem (PSAK) tidak dapat dihapus.'];
        }

        // Cek apakah digunakan oleh akun COA
        $count = $this->db->group_start()
            ->where('kategori_id', $id)
            ->or_where('kategori', $kat['slug'])
            ->group_end()
            ->count_all_results('akun_rekening');
        if ($count > 0) {
            return ['status' => false, 'message' => "Kategori '{$kat['nama_kategori']}' sedang digunakan oleh {$count} akun rekening COA."];
        }

        $this->db->delete('kategori_akun', ['id' => $id]);
        return ['status' => true, 'message' => 'Kategori akun berhasil dihapus.'];
    }

    public function toggle_kategori_status($id) {
        $kat = $this->get_kategori_by_id($id);
        if (!$kat) return false;
        $new_status = ($kat['is_active'] == 1) ? 0 : 1;
        $this->db->where('id', $id)->update('kategori_akun', [
            'is_active' => $new_status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        return $new_status;
    }

    // =========================================================================
    // 2. BAGAN AKUN (CHART OF ACCOUNTS / COA)
    // =========================================================================
    public function get_all_coa($filters = []) {
        $this->db->select('ar.*, parent.nama_rekening as parent_nama, ka.nama_kategori, ka.kode_kategori as kategori_kode_ref, ka.posisi_normal as kategori_posisi_normal');
        $this->db->from('akun_rekening ar');
        $this->db->join('akun_rekening parent', 'parent.kode_rekening = ar.parent_kode', 'left');
        $this->db->join('kategori_akun ka', 'ka.id = ar.kategori_id OR ka.slug = ar.kategori', 'left');

        if (!empty($filters['kategori'])) {
            $this->db->group_start();
            $this->db->where('ar.kategori', $filters['kategori']);
            $this->db->or_where('ka.slug', $filters['kategori']);
            $this->db->or_where('ka.kode_kategori', $filters['kategori']);
            $this->db->group_end();
        }
        if (!empty($filters['level'])) {
            $this->db->where('ar.level', (int)$filters['level']);
        }
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $this->db->where('ar.is_active', (int)$filters['is_active']);
        }
        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $this->db->group_start();
            $this->db->like('ar.kode_rekening', $s);
            $this->db->or_like('ar.nama_rekening', $s);
            $this->db->or_like('ar.keterangan', $s);
            $this->db->or_like('ka.nama_kategori', $s);
            $this->db->group_end();
        }

        $this->db->order_by('ar.kode_rekening', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_coa_by_id($id) {
        $this->db->select('ar.*, parent.nama_rekening as parent_nama, ka.nama_kategori, ka.kode_kategori as kategori_kode_ref');
        $this->db->from('akun_rekening ar');
        $this->db->join('akun_rekening parent', 'parent.kode_rekening = ar.parent_kode', 'left');
        $this->db->join('kategori_akun ka', 'ka.id = ar.kategori_id OR ka.slug = ar.kategori', 'left');
        $this->db->where('ar.id', $id);
        return $this->db->get()->row_array();
    }

    public function get_coa_by_kode($kode) {
        $this->db->select('ar.*, parent.nama_rekening as parent_nama, ka.nama_kategori, ka.kode_kategori as kategori_kode_ref');
        $this->db->from('akun_rekening ar');
        $this->db->join('akun_rekening parent', 'parent.kode_rekening = ar.parent_kode', 'left');
        $this->db->join('kategori_akun ka', 'ka.id = ar.kategori_id OR ka.slug = ar.kategori', 'left');
        $this->db->where('ar.kode_rekening', $kode);
        return $this->db->get()->row_array();
    }

    public function suggest_next_child_code($parent_kode) {
        $parent = $this->get_coa_by_kode($parent_kode);
        if (!$parent) return '';

        // Ambil semua anak langsung dari parent_kode
        $children = $this->db->select('kode_rekening')
            ->from('akun_rekening')
            ->where('parent_kode', $parent_kode)
            ->order_by('kode_rekening', 'ASC')
            ->get()->result_array();

        $prefix_len = strlen($parent_kode) + 1; // + dot
        $max_num = 0;
        foreach ($children as $c) {
            $sub = substr($c['kode_rekening'], $prefix_len);
            if (is_numeric($sub)) {
                $n = (int)$sub;
                if ($n > $max_num) $max_num = $n;
            }
        }

        $next_num = $max_num + 1;
        if ($parent['level'] == 1) {
            return $parent_kode . '.' . $next_num;
        } else {
            return $parent_kode . '.' . str_pad($next_num, 2, '0', STR_PAD_LEFT);
        }
    }

    public function insert_coa($data) {
        if (!empty($data['parent_kode'])) {
            $parent = $this->get_coa_by_kode($data['parent_kode']);
            if ($parent) {
                if (empty($data['kategori'])) {
                    $data['kategori'] = $parent['kategori'];
                }
                if (empty($data['kategori_id']) && !empty($parent['kategori_id'])) {
                    $data['kategori_id'] = $parent['kategori_id'];
                }
                $data['level'] = (int)$parent['level'] + 1;
            }
        }
        if (!empty($data['kategori_id']) && empty($data['kategori'])) {
            $kat = $this->get_kategori_by_id($data['kategori_id']);
            if ($kat) $data['kategori'] = $kat['slug'];
        } elseif (!empty($data['kategori']) && empty($data['kategori_id'])) {
            $kat = $this->get_kategori_by_slug($data['kategori']);
            if ($kat) $data['kategori_id'] = $kat['id'];
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('akun_rekening', $data);
        return $this->db->insert_id();
    }

    public function update_coa($id, $data) {
        if (!empty($data['parent_kode'])) {
            $parent = $this->get_coa_by_kode($data['parent_kode']);
            if ($parent) {
                if (empty($data['kategori'])) {
                    $data['kategori'] = $parent['kategori'];
                }
                if (empty($data['kategori_id']) && !empty($parent['kategori_id'])) {
                    $data['kategori_id'] = $parent['kategori_id'];
                }
                $data['level'] = (int)$parent['level'] + 1;
            }
        }
        if (!empty($data['kategori_id']) && empty($data['kategori'])) {
            $kat = $this->get_kategori_by_id($data['kategori_id']);
            if ($kat) $data['kategori'] = $kat['slug'];
        } elseif (!empty($data['kategori']) && empty($data['kategori_id'])) {
            $kat = $this->get_kategori_by_slug($data['kategori']);
            if ($kat) $data['kategori_id'] = $kat['id'];
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update('akun_rekening', $data);
    }

    public function delete_coa($id) {
        $coa = $this->get_coa_by_id($id);
        if (!$coa) return false;

        // Cek apakah punya child rekening
        $has_children = $this->db->where('parent_kode', $coa['kode_rekening'])->count_all_results('akun_rekening') > 0;
        if ($has_children) {
            return ['status' => false, 'message' => 'Akun tidak dapat dihapus karena memiliki sub-rekening di bawahnya.'];
        }

        // Cek apakah terpakai di rekening tahun
        $in_tahun = $this->db->where('kode_rekening', $coa['kode_rekening'])->count_all_results('rekening_tahun') > 0;
        if ($in_tahun) {
            return ['status' => false, 'message' => 'Akun telah memiliki mutasi saldo / anggaran pada Rekening Tahun.'];
        }

        // Cek apakah terpakai di akun bayar
        $in_bayar = $this->db->where('kode_rekening', $coa['kode_rekening'])->count_all_results('akun_bayar') > 0;
        if ($in_bayar) {
            return ['status' => false, 'message' => 'Akun terhubung ke Master Akun Bayar Kasir.'];
        }

        // Cek apakah terpakai di akun piutang
        $in_piutang = $this->db->where('kode_rekening', $coa['kode_rekening'])->count_all_results('akun_piutang') > 0;
        if ($in_piutang) {
            return ['status' => false, 'message' => 'Akun terhubung ke Master Akun Piutang Penjamin.'];
        }

        $this->db->delete('akun_rekening', ['id' => $id]);
        return ['status' => true, 'message' => 'Akun rekening berhasil dihapus.'];
    }

    public function toggle_coa_status($id) {
        $coa = $this->get_coa_by_id($id);
        if (!$coa) return false;
        $new_status = ($coa['is_active'] == 1) ? 0 : 1;
        $this->db->where('id', $id)->update('akun_rekening', [
            'is_active' => $new_status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        return $new_status;
    }

    public function get_coa_dropdown($filter_type = 'all') {
        $this->db->select('kode_rekening, nama_rekening, level, kategori, posisi_normal, is_kas_bank, is_piutang');
        $this->db->from('akun_rekening');
        $this->db->where('is_active', 1);

        if ($filter_type === 'kas_bank') {
            $this->db->where('is_kas_bank', 1);
        } elseif ($filter_type === 'piutang') {
            $this->db->where('is_piutang', 1);
        } elseif ($filter_type === 'pendapatan') {
            $this->db->where('is_pendapatan', 1);
        }

        $this->db->order_by('kode_rekening', 'ASC');
        return $this->db->get()->result_array();
    }

    // =========================================================================
    // 3. REKENING TAHUN (SALDO AWAL & ANGGARAN PER TAHUN FISKAL)
    // =========================================================================
    public function get_all_rekening_tahun($tahun = null, $status = null) {
        $this->db->select('rt.*, ar.nama_rekening, ar.kategori, ar.posisi_normal, ar.level, ar.is_kas_bank, ar.is_piutang');
        $this->db->from('rekening_tahun rt');
        $this->db->join('akun_rekening ar', 'ar.kode_rekening = rt.kode_rekening', 'left');

        if (!empty($tahun)) {
            $this->db->where('rt.tahun', (int)$tahun);
        }
        if (!empty($status)) {
            $this->db->where('rt.status', $status);
        }

        $this->db->order_by('rt.tahun', 'DESC');
        $this->db->order_by('rt.kode_rekening', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_distinct_tahun() {
        $this->db->distinct();
        $this->db->select('tahun');
        $this->db->order_by('tahun', 'DESC');
        $rows = $this->db->get('rekening_tahun')->result_array();
        $tahuns = array_column($rows, 'tahun');
        $curr = (int)date('Y');
        if (!in_array($curr, $tahuns)) {
            array_unshift($tahuns, $curr);
        }
        return $tahuns;
    }

    public function get_rekening_tahun_by_id($id) {
        $this->db->select('rt.*, ar.nama_rekening, ar.kategori, ar.posisi_normal');
        $this->db->from('rekening_tahun rt');
        $this->db->join('akun_rekening ar', 'ar.kode_rekening = rt.kode_rekening', 'left');
        $this->db->where('rt.id', $id);
        return $this->db->get()->row_array();
    }

    public function save_rekening_tahun($data) {
        // Hitung Saldo Akhir otomatis berdasarkan Posisi Normal Akun
        $coa = $this->get_coa_by_kode($data['kode_rekening']);
        $pos = $coa['posisi_normal'] ?? 'D';

        $saldo_awal  = (float)($data['saldo_awal'] ?? 0);
        $real_debet  = (float)($data['realisasi_debet'] ?? 0);
        $real_kredit = (float)($data['realisasi_kredit'] ?? 0);

        if ($pos === 'D') {
            $saldo_akhir = $saldo_awal + $real_debet - $real_kredit;
        } else {
            $saldo_akhir = $saldo_awal + $real_kredit - $real_debet;
        }
        $data['saldo_akhir'] = $saldo_akhir;

        if (!empty($data['id'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $id = $data['id'];
            unset($data['id']);
            $this->db->where('id', $id);
            $this->db->update('rekening_tahun', $data);
            return $id;
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('rekening_tahun', $data);
            return $this->db->insert_id();
        }
    }

    public function delete_rekening_tahun($id) {
        $rt = $this->get_rekening_tahun_by_id($id);
        if (!$rt) return false;
        if ($rt['status'] === 'tutup_buku') {
            return ['status' => false, 'message' => 'Tahun buku yang telah ditutup tidak dapat dihapus.'];
        }
        $this->db->delete('rekening_tahun', ['id' => $id]);
        return ['status' => true, 'message' => 'Data rekening tahun berhasil dihapus.'];
    }

    public function toggle_status_tahun($id) {
        $rt = $this->get_rekening_tahun_by_id($id);
        if (!$rt) return false;
        $new_status = ($rt['status'] === 'aktif') ? 'tutup_buku' : 'aktif';
        $this->db->where('id', $id)->update('rekening_tahun', [
            'status'     => $new_status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        return $new_status;
    }

    // =========================================================================
    // 4. AKUN BAYAR (KASIR & CHANNEL PENERIMAAN)
    // =========================================================================
    public function get_all_akun_bayar($filters = []) {
        $this->db->select('ab.*, ar.nama_rekening as coa_nama, ar.posisi_normal, ar.kategori as coa_kategori');
        $this->db->from('akun_bayar ab');
        $this->db->join('akun_rekening ar', 'ar.kode_rekening = ab.kode_rekening', 'left');

        if (!empty($filters['tipe'])) {
            $this->db->where('ab.tipe_transaksi', $filters['tipe']);
        }
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $this->db->where('ab.is_active', (int)$filters['is_active']);
        }
        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $this->db->group_start();
            $this->db->like('ab.kode_bayar', $s);
            $this->db->or_like('ab.nama_bayar', $s);
            $this->db->or_like('ab.nama_bank', $s);
            $this->db->or_like('ab.nomor_rekening_bank', $s);
            $this->db->group_end();
        }

        $this->db->order_by('ab.urutan', 'ASC');
        $this->db->order_by('ab.kode_bayar', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_akun_bayar_by_id($id) {
        $this->db->select('ab.*, ar.nama_rekening as coa_nama');
        $this->db->from('akun_bayar ab');
        $this->db->join('akun_rekening ar', 'ar.kode_rekening = ab.kode_rekening', 'left');
        $this->db->where('ab.id', $id);
        return $this->db->get()->row_array();
    }

    public function insert_akun_bayar($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('akun_bayar', $data);
        return $this->db->insert_id();
    }

    public function update_akun_bayar($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update('akun_bayar', $data);
    }

    public function delete_akun_bayar($id) {
        $this->db->delete('akun_bayar', ['id' => $id]);
        return true;
    }

    public function toggle_akun_bayar_status($id) {
        $ab = $this->get_akun_bayar_by_id($id);
        if (!$ab) return false;
        $new_status = ($ab['is_active'] == 1) ? 0 : 1;
        $this->db->where('id', $id)->update('akun_bayar', [
            'is_active'  => $new_status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        return $new_status;
    }

    // =========================================================================
    // 5. AKUN PIUTANG (PENJAMIN & PKS KERJASAMA)
    // =========================================================================
    public function get_all_akun_piutang($filters = []) {
        $this->db->select('ap.*, ar.nama_rekening as coa_nama, ar.posisi_normal, ar.kategori as coa_kategori');
        $this->db->from('akun_piutang ap');
        $this->db->join('akun_rekening ar', 'ar.kode_rekening = ap.kode_rekening', 'left');

        if (!empty($filters['penjamin'])) {
            $this->db->where('ap.kategori_penjamin', $filters['penjamin']);
        }
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $this->db->where('ap.is_active', (int)$filters['is_active']);
        }
        if (!empty($filters['search'])) {
            $s = $filters['search'];
            $this->db->group_start();
            $this->db->like('ap.kode_piutang', $s);
            $this->db->or_like('ap.nama_piutang', $s);
            $this->db->or_like('ap.kontak_person', $s);
            $this->db->or_like('ap.email', $s);
            $this->db->group_end();
        }

        $this->db->order_by('ap.kode_piutang', 'ASC');
        return $this->db->get()->result_array();
    }

    public function get_akun_piutang_by_id($id) {
        $this->db->select('ap.*, ar.nama_rekening as coa_nama');
        $this->db->from('akun_piutang ap');
        $this->db->join('akun_rekening ar', 'ar.kode_rekening = ap.kode_rekening', 'left');
        $this->db->where('ap.id', $id);
        return $this->db->get()->row_array();
    }

    public function insert_akun_piutang($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('akun_piutang', $data);
        return $this->db->insert_id();
    }

    public function update_akun_piutang($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        return $this->db->update('akun_piutang', $data);
    }

    public function delete_akun_piutang($id) {
        $this->db->delete('akun_piutang', ['id' => $id]);
        return true;
    }

    public function toggle_akun_piutang_status($id) {
        $ap = $this->get_akun_piutang_by_id($id);
        if (!$ap) return false;
        $new_status = ($ap['is_active'] == 1) ? 0 : 1;
        $this->db->where('id', $id)->update('akun_piutang', [
            'is_active'  => $new_status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        return $new_status;
    }
}
