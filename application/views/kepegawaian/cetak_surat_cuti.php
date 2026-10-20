<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Formulir Permintaan dan Pemberian Cuti
 * Mengikuti Anak Lampiran Peraturan BKN No. 24 Tahun 2017.
 */
$approvals = $cuti['approvals'] ?? [];
$atasanLangsung = !empty($approvals) ? $approvals[0] : null;
$pejabatBerwenang = !empty($approvals) ? end($approvals) : null;
$approvalTengah = count($approvals) > 2 ? array_slice($approvals, 1, -1) : [];

$bulanIndo = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$tglIndo = function ($iso) use ($bulanIndo) {
    if (empty($iso)) return '-';
    $ts = strtotime($iso);
    return date('j', $ts) . ' ' . $bulanIndo[(int)date('n', $ts)] . ' ' . date('Y', $ts);
};

$tahunN = (int)$cuti['tahun_hak'];
$kodeJenis = $cuti['jenis_cuti_kode'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= html_escape($title) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.min.css') ?>">
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, sans-serif; background: #fff; color: #0f172a; }
        table.form td, table.form th { border: 1px solid #0f172a; padding: 4px 8px; vertical-align: top; }
        .kotak { display: inline-block; width: 12px; height: 12px; border: 1px solid #0f172a; margin-right: 5px; vertical-align: middle; }
        .kotak.silang { position: relative; }
        .kotak.silang::after { content: "\2713"; position: absolute; top: -4px; left: 1px; font-size: 12px; font-weight: 700; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            @page { size: A4; margin: 12mm; }
        }
    </style>
</head>
<body class="p-8 max-w-4xl mx-auto text-[11px]">

    <div class="no-print mb-5 flex justify-between items-center bg-slate-100 p-3 rounded-xl border border-slate-300">
        <span class="text-xs font-semibold text-slate-700">
            Pratinjau Surat Izin Cuti &mdash; <?= html_escape($cuti['nomor_surat_izin'] ?: $cuti['nomor_pengajuan']) ?>
        </span>
        <button onclick="window.print()" class="px-4 py-1.5 rounded-lg bg-emerald-700 text-white text-xs font-bold shadow hover:bg-emerald-800">
            Cetak Sekarang (Print)
        </button>
    </div>

    <?php $this->load->view('layout/kop_surat'); ?>

    <div class="text-center my-4">
        <h2 class="text-sm font-black uppercase tracking-wide text-slate-900">FORMULIR PERMINTAAN DAN PEMBERIAN CUTI</h2>
        <p class="text-[10px] text-slate-600 mt-0.5">Nomor: <strong><?= html_escape($cuti['nomor_surat_izin'] ?: '-') ?></strong></p>
    </div>

    <table class="form w-full border-collapse mb-3">
        <tr class="bg-slate-100">
            <td colspan="4" class="font-bold uppercase text-[10px]">I. Data Pegawai</td>
        </tr>
        <tr>
            <td class="w-[18%] font-semibold">Nama</td>
            <td class="w-[32%]"><?= html_escape($cuti['pegawai_nama']) ?></td>
            <td class="w-[18%] font-semibold">NIP</td>
            <td class="w-[32%] font-mono"><?= html_escape($cuti['pegawai_nip'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="font-semibold">Jabatan</td>
            <td><?= html_escape($cuti['pegawai_jabatan'] ?: '-') ?></td>
            <td class="font-semibold">Masa Kerja</td>
            <td><?= (int)$cuti['pegawai_mkg_tahun'] ?> Tahun</td>
        </tr>
        <tr>
            <td class="font-semibold">Pangkat / Gol. Ruang</td>
            <td><?= html_escape(trim(($cuti['pegawai_pangkat'] ?: '-') . ' / ' . ($cuti['pegawai_golongan'] ?: '-'))) ?></td>
            <td class="font-semibold">Unit Kerja</td>
            <td><?= html_escape($cuti['pegawai_seksi'] ?: '-') ?></td>
        </tr>
    </table>

    <table class="form w-full border-collapse mb-3">
        <tr class="bg-slate-100">
            <td colspan="2" class="font-bold uppercase text-[10px]">II. Jenis Cuti yang Diambil</td>
        </tr>
        <tr>
            <td class="w-1/2">
                <span class="kotak <?= $kodeJenis === 'TAHUNAN' ? 'silang' : '' ?>"></span> Cuti Tahunan<br>
                <span class="kotak <?= $kodeJenis === 'BESAR' ? 'silang' : '' ?>"></span> Cuti Besar<br>
                <span class="kotak <?= $kodeJenis === 'SAKIT' ? 'silang' : '' ?>"></span> Cuti Sakit
            </td>
            <td class="w-1/2">
                <span class="kotak <?= $kodeJenis === 'MELAHIRKAN' ? 'silang' : '' ?>"></span> Cuti Melahirkan<br>
                <span class="kotak <?= $kodeJenis === 'PENTING' ? 'silang' : '' ?>"></span> Cuti Karena Alasan Penting<br>
                <span class="kotak <?= $kodeJenis === 'CLTN' ? 'silang' : '' ?>"></span> Cuti di Luar Tanggungan Negara
            </td>
        </tr>
    </table>

    <table class="form w-full border-collapse mb-3">
        <tr class="bg-slate-100">
            <td class="font-bold uppercase text-[10px]">III. Alasan Cuti</td>
        </tr>
        <tr>
            <td class="py-3"><?= nl2br(html_escape($cuti['alasan'])) ?></td>
        </tr>
    </table>

    <table class="form w-full border-collapse mb-3">
        <tr class="bg-slate-100">
            <td colspan="3" class="font-bold uppercase text-[10px]">IV. Lamanya Cuti</td>
        </tr>
        <tr>
            <td class="w-[34%]">Selama <strong><?= (int)$cuti['jumlah_hari'] ?></strong> hari kerja</td>
            <td class="w-[33%]">Mulai tanggal<br><strong><?= $tglIndo($cuti['tanggal_mulai']) ?></strong></td>
            <td class="w-[33%]">s.d. tanggal<br><strong><?= $tglIndo($cuti['tanggal_selesai']) ?></strong></td>
        </tr>
    </table>

    <table class="form w-full border-collapse mb-3">
        <tr class="bg-slate-100">
            <td colspan="4" class="font-bold uppercase text-[10px]">V. Catatan Cuti</td>
        </tr>
        <tr class="bg-slate-50 text-center font-semibold text-[10px]">
            <td>Jenis Cuti</td>
            <td class="w-[18%]">Sisa Tahun N-2 (<?= $tahunN - 2 ?>)</td>
            <td class="w-[18%]">Sisa Tahun N-1 (<?= $tahunN - 1 ?>)</td>
            <td class="w-[18%]">Sisa Tahun N (<?= $tahunN ?>)</td>
        </tr>
        <tr class="text-center">
            <td class="text-left">Cuti Tahunan</td>
            <td><?= (int)($saldo['sisa_n2'] ?? 0) ?> hari</td>
            <td><?= (int)($saldo['sisa_n1'] ?? 0) ?> hari</td>
            <td><?= (int)($saldo['sisa_n'] ?? 0) ?> hari</td>
        </tr>
        <tr>
            <td colspan="4" class="text-[10px] text-slate-600">
                Pemotongan hak cuti pada permohonan ini &mdash;
                N-2: <strong><?= (int)$cuti['pakai_n2'] ?></strong> hari,
                N-1: <strong><?= (int)$cuti['pakai_n1'] ?></strong> hari,
                tahun berjalan: <strong><?= (int)$cuti['pakai_n'] ?></strong> hari.
                <em>Sisa di atas adalah kondisi setelah permohonan ini diperhitungkan.</em>
            </td>
        </tr>
    </table>

    <table class="form w-full border-collapse mb-3">
        <tr class="bg-slate-100">
            <td colspan="2" class="font-bold uppercase text-[10px]">VI. Alamat Selama Menjalankan Cuti</td>
        </tr>
        <tr>
            <td class="w-[70%] py-3"><?= html_escape($cuti['alamat_selama_cuti'] ?: '-') ?></td>
            <td class="w-[30%]">Telp.<br><strong class="font-mono"><?= html_escape($cuti['telepon_selama_cuti'] ?: '-') ?></strong></td>
        </tr>
    </table>

    <?php if ($atasanLangsung): ?>
        <table class="form w-full border-collapse mb-3">
            <tr class="bg-slate-100">
                <td class="font-bold uppercase text-[10px]">VII. Pertimbangan Atasan Langsung</td>
            </tr>
            <tr>
                <td>
                    <div class="mb-2">
                        <span class="kotak <?= $atasanLangsung['status'] === 'approved' ? 'silang' : '' ?>"></span> Disetujui
                        <span class="kotak ml-6 <?= $atasanLangsung['status'] === 'rejected' ? 'silang' : '' ?>"></span> Tidak Disetujui
                    </div>
                    <?php if (!empty($atasanLangsung['catatan'])): ?>
                        <p class="text-[10px] text-slate-700 mb-2">Catatan: <?= html_escape($atasanLangsung['catatan']) ?></p>
                    <?php endif; ?>
                    <div class="mt-4 text-center w-64 ml-auto">
                        <p class="text-[10px] mb-1"><?= html_escape($atasanLangsung['jabatan_approval']) ?></p>
                        <?php if (!empty($atasanLangsung['tanda_tangan']) && file_exists(FCPATH . $atasanLangsung['tanda_tangan'])): ?>
                            <img src="<?= base_url($atasanLangsung['tanda_tangan']) ?>" alt="Tanda tangan" class="h-12 mx-auto object-contain">
                        <?php else: ?>
                            <div class="h-12"></div>
                        <?php endif; ?>
                        <p class="font-bold underline"><?= html_escape($atasanLangsung['pegawai_nama']) ?></p>
                        <p class="text-[10px] font-mono">NIP. <?= html_escape($atasanLangsung['pegawai_nip'] ?: '-') ?></p>
                    </div>
                </td>
            </tr>
        </table>
    <?php endif; ?>

    <?php if (!empty($approvalTengah)): ?>
        <table class="form w-full border-collapse mb-3">
            <tr class="bg-slate-100">
                <td colspan="<?= count($approvalTengah) ?>" class="font-bold uppercase text-[10px]">Persetujuan Antara</td>
            </tr>
            <tr>
                <?php foreach ($approvalTengah as $a): ?>
                    <td class="text-center align-bottom py-2">
                        <p class="text-[10px] mb-1"><?= html_escape($a['jabatan_approval']) ?></p>
                        <?php if (!empty($a['tanda_tangan']) && file_exists(FCPATH . $a['tanda_tangan'])): ?>
                            <img src="<?= base_url($a['tanda_tangan']) ?>" alt="Tanda tangan" class="h-12 mx-auto object-contain">
                        <?php else: ?>
                            <div class="h-12"></div>
                        <?php endif; ?>
                        <p class="font-bold underline"><?= html_escape($a['pegawai_nama']) ?></p>
                        <p class="text-[10px] font-mono">NIP. <?= html_escape($a['pegawai_nip'] ?: '-') ?></p>
                    </td>
                <?php endforeach; ?>
            </tr>
        </table>
    <?php endif; ?>

    <?php if ($pejabatBerwenang): ?>
        <table class="form w-full border-collapse mb-3">
            <tr class="bg-slate-100">
                <td class="font-bold uppercase text-[10px]">VIII. Keputusan Pejabat yang Berwenang Memberikan Cuti</td>
            </tr>
            <tr>
                <td>
                    <div class="mb-2">
                        <span class="kotak <?= $pejabatBerwenang['status'] === 'approved' ? 'silang' : '' ?>"></span> Disetujui
                        <span class="kotak ml-6"></span> Perubahan
                        <span class="kotak ml-6"></span> Ditangguhkan
                        <span class="kotak ml-6 <?= $pejabatBerwenang['status'] === 'rejected' ? 'silang' : '' ?>"></span> Tidak Disetujui
                    </div>
                    <?php if (!empty($pejabatBerwenang['catatan'])): ?>
                        <p class="text-[10px] text-slate-700 mb-2">Catatan: <?= html_escape($pejabatBerwenang['catatan']) ?></p>
                    <?php endif; ?>
                    <div class="mt-4 text-center w-64 ml-auto">
                        <p class="text-[10px]"><?= html_escape($kop['kota']) ?>, <?= $tglIndo($cuti['waktu_selesai_approval']) ?></p>
                        <p class="text-[10px] mb-1"><?= html_escape($pejabatBerwenang['jabatan_approval']) ?></p>
                        <?php if (!empty($pejabatBerwenang['tanda_tangan']) && file_exists(FCPATH . $pejabatBerwenang['tanda_tangan'])): ?>
                            <img src="<?= base_url($pejabatBerwenang['tanda_tangan']) ?>" alt="Tanda tangan" class="h-12 mx-auto object-contain">
                        <?php else: ?>
                            <div class="h-12"></div>
                        <?php endif; ?>
                        <p class="font-bold underline"><?= html_escape($pejabatBerwenang['pegawai_nama']) ?></p>
                        <p class="text-[10px] font-mono">NIP. <?= html_escape($pejabatBerwenang['pegawai_nip'] ?: '-') ?></p>
                    </div>
                </td>
            </tr>
        </table>
    <?php endif; ?>

    <p class="text-[9px] text-slate-500 mt-4 border-t border-slate-300 pt-2">
        Dokumen ini diterbitkan otomatis oleh LIS PRO &mdash; <?= html_escape($kop['instansi']) ?>.
        Nomor permohonan <?= html_escape($cuti['nomor_pengajuan']) ?>, dicetak <?= $tglIndo(date('Y-m-d')) ?>.
        Dasar hukum: PP No. 11 Tahun 2017 dan Peraturan BKN No. 24 Tahun 2017.
    </p>

</body>
</html>
