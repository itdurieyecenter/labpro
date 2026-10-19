<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Pelayanan & Penerimaan BLUD - Labkesda Riau</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.min.css') ?>">
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, sans-serif; background: #fff; color: #0f172a; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body class="p-8 max-w-5xl mx-auto">

    <div class="no-print mb-5 flex justify-between items-center bg-slate-100 p-3 rounded-xl border border-slate-300">
        <span class="text-xs font-semibold text-slate-700">Pratinjau Dokumen Cetak Laporan SIMRS & Billing BLUD</span>
        <button onclick="window.print()" class="px-4 py-1.5 rounded-lg bg-emerald-700 text-white text-xs font-bold shadow hover:bg-emerald-800">
            Cetak Sekarang (Print)
        </button>
    </div>

    <!-- KOP RESMI LABKESDA RIAU -->
    <?php $this->load->view('layout/kop_surat'); ?>

    <div class="text-center my-4 border-b-2 border-slate-900 pb-3">
        <h2 class="text-base font-black uppercase tracking-wider text-slate-900">REKAPITULASI PELAYANAN LIS & PENERIMAAN BILLING BLUD</h2>
        <p class="text-xs text-slate-600 font-medium mt-0.5">
            Periode: <strong><?= date('d/m/Y', strtotime($start_date)) ?></strong> s/d <strong><?= date('d/m/Y', strtotime($end_date)) ?></strong>
        </p>
    </div>

    <!-- Ringkasan Keuangan -->
    <div class="grid grid-cols-3 gap-3 mb-5 text-xs font-mono">
        <div class="border border-slate-300 p-2.5 rounded">
            <span class="text-[10px] text-slate-500 block uppercase font-sans">Total Tagihan Billing:</span>
            <strong class="text-sm">Rp <?= number_format($tot_omset, 0, ',', '.') ?></strong>
        </div>
        <div class="border border-emerald-300 bg-emerald-50/30 p-2.5 rounded">
            <span class="text-[10px] text-emerald-800 block uppercase font-sans">Penerimaan Kasir (PAID):</span>
            <strong class="text-sm text-emerald-900">Rp <?= number_format($tot_paid, 0, ',', '.') ?></strong>
        </div>
        <div class="border border-amber-300 bg-amber-50/30 p-2.5 rounded">
            <span class="text-[10px] text-amber-800 block uppercase font-sans">Piutang PKS (GUARANTEED):</span>
            <strong class="text-sm text-amber-900">Rp <?= number_format($tot_piutang, 0, ',', '.') ?></strong>
        </div>
    </div>

    <!-- Tabel Rincian -->
    <table class="w-full text-left text-xs border border-slate-300 border-collapse mb-6">
        <thead>
            <tr class="bg-slate-100 uppercase text-[9px] font-bold border-b border-slate-300">
                <th class="p-2 border-r border-slate-300 w-8 text-center">No</th>
                <th class="p-2 border-r border-slate-300">Tanggal</th>
                <th class="p-2 border-r border-slate-300">No. Registrasi / Billing</th>
                <th class="p-2 border-r border-slate-300">Nama Pasien / Subjek</th>
                <th class="p-2 border-r border-slate-300">Tipe Pelayanan</th>
                <th class="p-2 border-r border-slate-300 text-right">Tagihan</th>
                <th class="p-2 border-r border-slate-300 text-center">Pembayaran</th>
                <th class="p-2 text-center">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            <?php $no = 1; foreach ($registrations as $r): ?>
                <tr>
                    <td class="p-2 border-r border-slate-200 text-center font-mono text-[10px]"><?= $no++ ?></td>
                    <td class="p-2 border-r border-slate-200 text-[10px]"><?= date('d/m/Y', strtotime($r['tanggal_pendaftaran'])) ?></td>
                    <td class="p-2 border-r border-slate-200 font-mono text-[10px]"><?= $r['nomor_registrasi'] ?> / <?= $r['nomor_billing'] ?></td>
                    <td class="p-2 border-r border-slate-200 font-bold"><?= html_escape($r['nama_pasien']) ?></td>
                    <td class="p-2 border-r border-slate-200 capitalize"><?= str_replace('_', ' ', $r['tipe_pasien']) ?></td>
                    <td class="p-2 border-r border-slate-200 text-right font-mono font-bold">Rp <?= number_format($r['total_biaya'], 0, ',', '.') ?></td>
                    <td class="p-2 border-r border-slate-200 text-center text-[10px] font-bold"><?= $r['status_pembayaran'] ?></td>
                    <td class="p-2 text-center text-[10px] uppercase"><?= $r['status_order'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Tanda Tangan Pejabat Pengesah -->
    <div class="grid grid-cols-2 gap-8 text-xs text-center mt-10">
        <div>
            <p>Mengetahui,</p>
            <p class="font-bold">Kepala UPT Laboratorium Kesehatan</p>
            <div class="h-20"></div>
            <p class="font-bold underline text-slate-900"><?= get_setting('kepala_lab', 'dr. Hj. Eka Safitri, M.Kes') ?></p>
            <p class="text-[10px] text-slate-500">NIP. 19780512 200501 2 004</p>
        </div>
        <div>
            <p>Pekanbaru, <?= date('d F Y') ?></p>
            <p class="font-bold">Bendahara Penerimaan / Kasir BLUD</p>
            <div class="h-20"></div>
            <p class="font-bold underline text-slate-900"><?= $this->session->userdata('user')['full_name'] ?? 'Petugas Keuangan' ?></p>
            <p class="text-[10px] text-slate-500">Petugas SIMRS LIS PRO</p>
        </div>
    </div>

</body>
</html>
