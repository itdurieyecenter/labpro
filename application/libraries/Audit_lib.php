<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pencatat riwayat aktivitas pengguna (audit trail).
 *
 * Dipakai dua arah:
 *   - otomatis oleh Audit_hook untuk setiap permintaan tulis (POST),
 *   - eksplisit lewat record() bila sebuah aksi perlu keterangan lebih rinci.
 *
 * Isi badan permintaan sengaja tidak pernah ikut dicatat agar kata sandi dan
 * data pasien tidak bocor ke dalam log.
 */
class Audit_lib {

    protected $CI;
    protected $table = 'activity_log';

    /** Controller yang tidak perlu dicatat karena bersifat polling atau baca data. */
    protected $abaikan = ['errors_handler', 'welcome'];

    /** Method yang hanya membaca data meski diakses lewat POST. */
    protected $abaikan_aksi = ['fetch', 'antrean', 'rekap', 'saldo', 'hitung_hari', 'summary', 'get'];

    /**
     * Aksi GET yang tetap wajib dicatat. Logout bukan permintaan tulis, tetapi
     * pasangan masuk-keluar diperlukan untuk menelusuri sesi pengguna.
     */
    protected $wajib_dicatat_get = ['auth/logout'];

    /**
     * Identitas pengguna yang direkam sebelum controller berjalan, dipakai bila
     * sesi sudah tidak tersedia saat pencatatan, misalnya pada aksi logout.
     */
    protected $user_cadangan = null;

    public function __construct() {
        $this->CI =& get_instance();
    }

    public function set_user_cadangan($user) {
        $this->user_cadangan = $user;
    }

    /**
     * Catat satu aktivitas. Kegagalan pencatatan tidak boleh menggagalkan
     * aksi utama pengguna, sehingga seluruh galat ditelan di sini.
     */
    public function record($modul, $aksi, $deskripsi = null, $refTabel = null, $refId = null, $metode = null) {
        try {
            if (!$this->CI->db->table_exists($this->table)) {
                return false;
            }

            $user = $this->CI->session->userdata('user') ?: $this->user_cadangan;

            $this->CI->db->insert($this->table, [
                'user_id' => isset($user['id']) ? (int)$user['id'] : null,
                'user_nama' => $user['full_name'] ?? 'Tamu / Belum Login',
                'user_role' => $user['role_name'] ?? null,
                'modul' => $modul,
                'aksi' => $aksi,
                'metode' => $metode ?: $this->CI->input->method(true),
                'url' => substr((string)uri_string(), 0, 255),
                'deskripsi' => $deskripsi,
                'ref_tabel' => $refTabel,
                'ref_id' => $refId !== null ? (int)$refId : null,
                'ip_address' => $this->CI->input->ip_address(),
                'user_agent' => substr((string)$this->CI->input->user_agent(), 0, 255),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            return true;
        } catch (Exception $e) {
            log_message('error', '[AUDIT] Gagal mencatat aktivitas: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Apakah pasangan controller/method ini layak dicatat otomatis.
     */
    public function layak_dicatat($modul, $aksi, $metode = 'POST') {
        $modul = strtolower($modul);
        $aksi = strtolower($aksi);

        if (in_array($modul, $this->abaikan, true)) {
            return false;
        }

        if (strtoupper($metode) !== 'POST') {
            return in_array($modul . '/' . $aksi, $this->wajib_dicatat_get, true);
        }

        return !in_array($aksi, $this->abaikan_aksi, true);
    }

    /**
     * Susun keterangan ringkas yang dapat dibaca manusia dari nama aksi.
     */
    public function deskripsi_otomatis($modul, $aksi) {
        $kamus = [
            'save' => 'Menyimpan data',
            'delete' => 'Menghapus data',
            'hapus' => 'Menghapus data',
            'ajukan' => 'Mengirim pengajuan ke alur persetujuan',
            'batalkan' => 'Membatalkan pengajuan',
            'proses_approval' => 'Memutuskan persetujuan',
            'rule_save' => 'Mengubah konfigurasi alur approval',
            'rule_delete' => 'Menghapus tahap approval',
            'rule_move' => 'Mengubah urutan tahap approval',
            'simpan_saldo' => 'Menyesuaikan saldo hak cuti',
            'login' => 'Masuk ke sistem',
            'logout' => 'Keluar dari sistem',
        ];

        $label = $kamus[strtolower($aksi)] ?? ('Menjalankan aksi ' . $aksi);
        return $label . ' pada modul ' . ucfirst($modul);
    }

    /**
     * Pangkas catatan yang sudah melewati masa retensi.
     */
    public function pangkas_retensi($hari = null) {
        $hari = (int)($hari ?: get_setting('audit_retensi_hari', 365));
        if ($hari <= 0) {
            return 0;
        }

        $batas = date('Y-m-d H:i:s', strtotime('-' . $hari . ' days'));
        $this->CI->db->where('created_at <', $batas)->delete($this->table);

        return $this->CI->db->affected_rows();
    }
}
