<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kepegawaian extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->rbac_lib->require_login();
        $this->load->model('pegawai_model');
    }

    private function _render_placeholder($title, $active_menu, $module_name, $description, $icon = 'calendar-days') {
        $data['title'] = $title . ' - UPT Laboratorium Kesehatan Provinsi Riau';
        $data['active_menu'] = $active_menu;
        $data['user'] = $this->rbac_lib->user();
        $data['menu_tree'] = $this->rbac_lib->get_menu_tree();
        $data['module_name'] = $module_name;
        $data['description'] = $description;
        $data['icon'] = $icon;

        $this->load->view('layout/header', $data);
        $this->load->view('layout/sidebar', $data);
        $this->load->view('layout/topbar', $data);
        $this->load->view('kepegawaian/cuti_placeholder', $data);
        $this->load->view('layout/footer', $data);
    }

    public function approval_setting() {
        $this->_render_placeholder(
            'Setting Alur Approval Cuti ASN',
            'kepegawaian/approval_setting',
            'Setting Hirarki Approval & Atasan Langsung',
            'Konfigurasi pejabat penilai, atasan langsung seksi/instalasi, dan Kepala UPT Labkesda untuk verifikasi cuti ASN.',
            'user-cog'
        );
    }

    public function pengajuan_cuti() {
        $this->_render_placeholder(
            'Pengajuan Permohonan Cuti ASN',
            'kepegawaian/pengajuan_cuti',
            'Formulir Pengajuan Cuti Pegawai (Cuti Tahunan, Sakit, Alasan Penting, Melahirkan)',
            'Modul pengisian mandiri permohonan cuti pegawai lengkap dengan kalkulasi sisa hak cuti tahunan N-2, N-1, dan N berjalan.',
            'calendar-days'
        );
    }

    public function approval_cuti() {
        $this->_render_placeholder(
            'Verifikasi & Persetujuan Cuti ASN',
            'kepegawaian/approval_cuti',
            'Antrean Approval Cuti oleh Atasan Langsung & Pimpinan',
            'Daftar persetujuan cuti berjenjang (Kepala Seksi/Instalasi -> Kasubbag TU -> Kepala UPT Laboratorium Kesehatan Provinsi Riau).',
            'check-check'
        );
    }

    public function laporan_cuti() {
        $this->_render_placeholder(
            'Laporan & Rekapitulasi Cuti Pegawai',
            'kepegawaian/laporan_cuti',
            'Rekapitulasi Cuti Tahunan & Kehadiran Pegawai Labkesda',
            'Laporan berkala penggunaan hak cuti seluruh pegawai dan cetak format SK Cuti standar BKN / BKD Provinsi Riau.',
            'file-bar-chart'
        );
    }
}
