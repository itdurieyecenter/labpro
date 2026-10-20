<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Kartu Inventaris Barang B (Peralatan dan Mesin)
 * Format mengacu Permendagri No. 47 Tahun 2021 tentang pengelolaan BMD.
 */
$p = $aset['penyusutan'];
$k = $aset['kalibrasi'];
$rp = function ($n) { return 'Rp ' . number_format((float)$n, 0, ',', '.'); };
$kondisiLabel = ['baik' => 'Baik (B)', 'rusak_ringan' => 'Rusak Ringan (RR)', 'rusak_berat' => 'Rusak Berat (RB)'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= html_escape($title) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.min.css') ?>">
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, sans-serif; background:#fff; color:#0f172a; }
        table.kib { width:100%; border-collapse:collapse; }
        table.kib td, table.kib th { border:1px solid #0f172a; padding:4px 8px; vertical-align:top; font-size:11px; }
        table.kib th { background:#f1f5f9; text-align:left; font-weight:700; }
        .sect { background:#e2e8f0; font-weight:700; text-transform:uppercase; font-size:10px; letter-spacing:.04em; }
        @media print { .no-print { display:none !important; } @page { size:A4; margin:12mm; } }
    </style>
</head>
<body class="p-8 max-w-4xl mx-auto">

    <div class="no-print mb-5 flex justify-between items-center bg-slate-100 p-3 rounded-xl border border-slate-300">
        <span class="text-xs font-semibold text-slate-700">Pratinjau KIB-B &mdash; <?= html_escape($aset['nomor_register']) ?></span>
        <button onclick="window.print()" class="px-4 py-1.5 rounded-lg bg-emerald-700 text-white text-xs font-bold shadow hover:bg-emerald-800">Cetak Sekarang</button>
    </div>

    <?php $this->load->view('layout/kop_surat'); ?>

    <div class="text-center my-4 border-b-2 border-slate-900 pb-3">
        <h2 class="text-sm font-black uppercase tracking-wider text-slate-900">Kartu Inventaris Barang (KIB) B</h2>
        <p class="text-xs text-slate-700 font-semibold">Peralatan dan Mesin</p>
        <p class="text-[10px] text-slate-500 mt-0.5">Nomor Register: <strong class="font-mono"><?= html_escape($aset['nomor_register']) ?></strong></p>
    </div>

    <table class="kib mb-3">
        <tr><td colspan="4" class="sect">I. Identitas Barang Milik Daerah</td></tr>
        <tr>
            <th style="width:20%">Kode Barang</th><td style="width:30%" class="font-mono"><?= html_escape($aset['kode_bmd']) ?></td>
            <th style="width:20%">NUP</th><td style="width:30%"><?= (int)$aset['nup'] ?></td>
        </tr>
        <tr>
            <th>Nama Barang</th><td class="font-bold"><?= html_escape($aset['nama_aset']) ?></td>
            <th>Golongan</th><td><?= html_escape($aset['golongan']) ?></td>
        </tr>
        <tr>
            <th>Merk / Tipe</th><td><?= html_escape($aset['merk_tipe'] ?: '-') ?></td>
            <th>Nomor Seri Pabrik</th><td class="font-mono"><?= html_escape($aset['nomor_seri'] ?: '-') ?></td>
        </tr>
        <tr>
            <th>Ukuran / Spesifikasi</th><td><?= html_escape($aset['ukuran_spesifikasi'] ?: '-') ?></td>
            <th>Bahan</th><td><?= html_escape($aset['bahan'] ?: '-') ?></td>
        </tr>
    </table>

    <table class="kib mb-3">
        <tr><td colspan="4" class="sect">II. Perolehan dan Nilai</td></tr>
        <tr>
            <th style="width:20%">Tanggal Perolehan</th><td style="width:30%"><?= html_escape($aset['tanggal_perolehan'] ?: '-') ?></td>
            <th style="width:20%">Tahun Perolehan</th><td style="width:30%"><?= html_escape($aset['tahun_perolehan'] ?: '-') ?></td>
        </tr>
        <tr>
            <th>Asal / Sumber Dana</th><td><?= html_escape($aset['cara_perolehan'] ?: '-') ?></td>
            <th>Nomor Dokumen</th><td class="font-mono"><?= html_escape($aset['nomor_dokumen_perolehan'] ?: '-') ?></td>
        </tr>
        <tr>
            <th>Nilai Perolehan</th><td class="font-bold"><?= $rp($aset['nilai_perolehan']) ?></td>
            <th>Masa Manfaat</th><td><?= (int)$aset['masa_manfaat_tahun'] ?> tahun</td>
        </tr>
        <tr>
            <th>Penyusutan / Tahun</th><td><?= $rp($p['per_tahun']) ?></td>
            <th>Umur Aset</th><td><?= (int)$p['umur_tahun'] ?> tahun</td>
        </tr>
        <tr>
            <th>Akumulasi Penyusutan</th><td><?= $rp($p['akumulasi']) ?> (<?= (int)$p['persen_susut'] ?>%)</td>
            <th>Nilai Buku</th><td class="font-bold"><?= $rp($p['nilai_buku']) ?></td>
        </tr>
    </table>

    <table class="kib mb-3">
        <tr><td colspan="4" class="sect">III. Penempatan dan Kondisi</td></tr>
        <tr>
            <th style="width:20%">Lokasi Ruangan</th><td style="width:30%"><?= html_escape($aset['lokasi_ruangan'] ?: '-') ?></td>
            <th style="width:20%">Penanggung Jawab</th><td style="width:30%"><?= html_escape($aset['penanggung_jawab_nama'] ?: '-') ?></td>
        </tr>
        <tr>
            <th>Kondisi</th><td class="font-bold"><?= $kondisiLabel[$aset['kondisi']] ?? $aset['kondisi'] ?></td>
            <th>Status</th><td><?= ucfirst(html_escape($aset['status'])) ?></td>
        </tr>
    </table>

    <table class="kib mb-3">
        <tr><td colspan="4" class="sect">IV. Kalibrasi (ISO/IEC 17025)</td></tr>
        <?php if ((int)$aset['wajib_kalibrasi'] !== 1): ?>
            <tr><td colspan="4" class="text-slate-500">Barang ini bukan instrumen ukur sehingga tidak diwajibkan kalibrasi.</td></tr>
        <?php else: ?>
            <tr>
                <th style="width:20%">Status</th>
                <td style="width:30%" class="font-bold"><?= html_escape($k['label']) ?></td>
                <th style="width:20%">Kelaikan Pakai</th>
                <td style="width:30%" class="font-bold <?= $aset['laik_pakai'] ? '' : 'text-rose-700' ?>">
                    <?= $aset['laik_pakai'] ? 'Laik dipakai menguji' : 'TIDAK LAIK — dilarang dipakai menguji' ?>
                </td>
            </tr>
            <tr>
                <th>Berlaku Sampai</th><td><?= html_escape($k['berlaku_sampai'] ?: '-') ?></td>
                <th>Interval Kalibrasi</th><td><?= (int)$aset['interval_kalibrasi_bulan'] ?> bulan</td>
            </tr>
            <tr>
                <th>Sertifikat Terakhir</th><td class="font-mono"><?= html_escape($k['sertifikat'] ?? '-') ?></td>
                <th>Lembaga</th><td><?= html_escape($k['lembaga'] ?? '-') ?></td>
            </tr>
        <?php endif; ?>
    </table>

    <?php if (!empty($aset['riwayat_kalibrasi'])): ?>
        <table class="kib mb-3">
            <tr><td colspan="5" class="sect">V. Riwayat Kalibrasi</td></tr>
            <tr><th>Tanggal</th><th>Berlaku Sampai</th><th>Lembaga</th><th>Sertifikat</th><th>Hasil</th></tr>
            <?php foreach ($aset['riwayat_kalibrasi'] as $c): ?>
                <tr>
                    <td><?= html_escape($c['tanggal_kalibrasi']) ?></td>
                    <td><?= html_escape($c['masa_berlaku_sampai']) ?></td>
                    <td><?= html_escape($c['lembaga_kalibrasi']) ?></td>
                    <td class="font-mono"><?= html_escape($c['nomor_sertifikat'] ?: '-') ?></td>
                    <td><?= ucwords(str_replace('_', ' ', $c['hasil'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <?php if (!empty($aset['riwayat_pemeliharaan'])): ?>
        <table class="kib mb-3">
            <tr><td colspan="5" class="sect">VI. Riwayat Pemeliharaan</td></tr>
            <tr><th>Jenis</th><th>Rencana</th><th>Realisasi</th><th>Pelaksana</th><th>Biaya</th></tr>
            <?php foreach ($aset['riwayat_pemeliharaan'] as $m): ?>
                <tr>
                    <td><?= ucfirst(html_escape($m['jenis'])) ?></td>
                    <td><?= html_escape($m['tanggal_rencana'] ?: '-') ?></td>
                    <td><?= html_escape($m['tanggal_realisasi'] ?: '-') ?></td>
                    <td><?= html_escape($m['nama_pelaksana'] ?: $m['pelaksana']) ?></td>
                    <td><?= $rp($m['biaya']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <?php if (!empty($aset['riwayat_mutasi'])): ?>
        <table class="kib mb-3">
            <tr><td colspan="5" class="sect">VII. Rekam Jejak Mutasi</td></tr>
            <tr><th>Nomor</th><th>Tanggal</th><th>Jenis</th><th>Perubahan</th><th>Dokumen</th></tr>
            <?php foreach ($aset['riwayat_mutasi'] as $m): ?>
                <tr>
                    <td class="font-mono"><?= html_escape($m['nomor_transaksi']) ?></td>
                    <td><?= html_escape($m['tanggal']) ?></td>
                    <td><?= ucwords(str_replace('_', ' ', $m['tipe'])) ?></td>
                    <td>
                        <?php if ($m['dari_ruangan'] || $m['ke_ruangan']): ?>
                            <?= html_escape($m['dari_ruangan'] ?: '-') ?> &rarr; <?= html_escape($m['ke_ruangan'] ?: '-') ?>
                        <?php elseif ($m['ke_status']): ?>
                            status &rarr; <?= html_escape($m['ke_status']) ?>
                        <?php elseif ($m['ke_kondisi']): ?>
                            kondisi &rarr; <?= html_escape(str_replace('_', ' ', $m['ke_kondisi'])) ?>
                        <?php else: ?>-<?php endif; ?>
                    </td>
                    <td class="font-mono"><?= html_escape($m['nomor_dokumen'] ?: '-') ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <div class="mt-6 text-center w-64 ml-auto text-xs">
        <p><?= html_escape($kop['kota']) ?>, <?= date('d F Y') ?></p>
        <p class="mb-1">Pengurus Barang Pengguna</p>
        <div class="h-14"></div>
        <p class="font-bold underline"><?= html_escape($aset['penanggung_jawab_nama'] ?: '..............................') ?></p>
        <p class="text-[10px]">NIP. ..............................</p>
    </div>

    <p class="text-[9px] text-slate-500 mt-5 border-t border-slate-300 pt-2">
        Dicetak otomatis oleh LIS PRO &mdash; <?= html_escape($kop['instansi']) ?> pada <?= date('d F Y H:i') ?> WIB.
        Format mengacu Permendagri No. 47 Tahun 2021 tentang Pengelolaan Barang Milik Daerah.
    </p>
</body>
</html>
