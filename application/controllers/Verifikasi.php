<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Verifikasi keaslian LHU (Lembar Hasil Uji) — HALAMAN PUBLIK TANPA LOGIN.
 *
 * Dibuka dengan memindai QR pada dokumen hasil resmi. Hanya menampilkan
 * METADATA KEASLIAN (nomor registrasi, tanggal, status sah, jumlah tanda tangan,
 * nama lab) — TIDAK menampilkan nilai hasil medis maupun identitas lengkap pasien.
 *
 * Token pada URL adalah HMAC-SHA256 dari id|barcode_sampel memakai kunci rahasia
 * server, sehingga dokumen tidak bisa dienumerasi oleh pihak yang tidak memegang
 * dokumen aslinya.
 */
class Verifikasi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Sengaja TANPA require_login — ini endpoint publik.
        $this->load->model('pendaftaran_model');
        $this->load->model('approval_model');
    }

    /**
     * Samarkan nama pasien: setiap kata disisakan huruf pertama + sensor.
     * "Budi Hidayat" -> "B*** H******"
     */
    private function _mask_nama($nama) {
        $nama = trim((string)$nama);
        if ($nama === '') return '-';
        $parts = preg_split('/\s+/', $nama);
        $out = [];
        foreach ($parts as $p) {
            $len = function_exists('mb_strlen') ? mb_strlen($p) : strlen($p);
            $first = function_exists('mb_substr') ? mb_substr($p, 0, 1) : substr($p, 0, 1);
            $out[] = $first . str_repeat('*', max(1, $len - 1));
        }
        return implode(' ', $out);
    }

    public function cek($id = null, $token = null) {
        $data['profile'] = app_profile();
        $data['valid'] = false;
        $data['reg'] = null;

        $reg = $id ? $this->pendaftaran_model->get_minimal_for_verify($id) : null;

        // Dokumen tidak ada ATAU token tidak cocok -> tampilkan status tidak valid.
        if (!$reg || empty($token)) {
            $data['alasan'] = 'Dokumen tidak ditemukan.';
            $this->load->view('verifikasi/cek', $data);
            return;
        }

        $expected = $this->pendaftaran_model->verification_token($reg['id'], $reg['barcode_sampel']);
        if (!hash_equals($expected, (string)$token)) {
            $data['alasan'] = 'Kode verifikasi tidak cocok. Dokumen tidak dapat dipastikan keasliannya.';
            $this->load->view('verifikasi/cek', $data);
            return;
        }

        // Token sah -> ambil status keabsahan berjenjang.
        $keabsahan = $this->approval_model->check_keabsahan($reg['id']);

        $data['valid'] = true;
        $data['is_sah'] = !empty($keabsahan['is_sah']);
        $data['signed_count'] = $keabsahan['signed_count'] ?? 0;
        $data['total_steps'] = $keabsahan['total_steps'] ?? 0;
        $data['reg'] = [
            'nomor_registrasi' => $reg['nomor_registrasi'],
            'barcode_sampel'   => $reg['barcode_sampel'],
            'nama_mask'        => $this->_mask_nama($reg['nama_pasien']),
            'tanggal'          => $reg['tanggal_pendaftaran'],
            'waktu_selesai'    => $reg['waktu_hasil_selesai'],
            'dokter_pj'        => $reg['dokter_pj'],
            'kategori'         => $reg['kategori_pemeriksaan'],
        ];

        log_message('info', sprintf('[VERIFIKASI_PUBLIK] Reg=%s | sah=%s', $reg['nomor_registrasi'], $data['is_sah'] ? '1' : '0'));

        $this->load->view('verifikasi/cek', $data);
    }
}
