<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Mencatat setiap permintaan tulis ke audit trail.
 *
 * Ditempatkan di hook agar seluruh modul terliput tanpa menyisipkan pemanggilan
 * di tiap controller. Pencatatan dijadwalkan lewat register_shutdown_function,
 * bukan dikerjakan langsung, karena redirect() CodeIgniter memanggil exit()
 * sehingga hook post_controller tidak pernah dijalankan pada aksi seperti
 * login dan logout.
 *
 * Hanya metadata yang dicatat, tidak pernah isi badan permintaan.
 */
class Audit_hook {

    public function catat() {
        $CI =& get_instance();

        $metode = $CI->input->method(true);
        $modul = $CI->router->fetch_class();
        $aksi = $CI->router->fetch_method();

        $CI->load->library('audit_lib');

        if (!$CI->audit_lib->layak_dicatat($modul, $aksi, $metode)) {
            return;
        }

        // Identitas diambil sekarang, selagi sesi masih utuh: pada logout sesi
        // sudah dihancurkan sebelum pencatatan berlangsung.
        $CI->audit_lib->set_user_cadangan($CI->session->userdata('user'));

        // Dieksekusi saat skrip berakhir, sehingga sesi pengguna sudah terisi
        // meski controller mengakhiri proses lebih awal lewat redirect().
        register_shutdown_function(function () use ($modul, $aksi) {
            $CI =& get_instance();
            $CI->audit_lib->record(
                $modul,
                $aksi,
                $CI->audit_lib->deskripsi_otomatis($modul, $aksi)
            );
        });
    }
}
