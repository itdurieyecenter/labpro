<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LHU Belum Sah - <?= html_escape($order['order_number']) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>">
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-xl w-full bg-white rounded-3xl shadow-xl border border-rose-200 overflow-hidden">
        <div class="bg-gradient-to-r from-rose-900 to-slate-900 p-6 text-white text-center">
            <h1 class="text-xl font-black uppercase">LHU Belum Sah &mdash; Tidak Dapat Dicetak</h1>
            <p class="text-xs text-rose-200 mt-1">Laporan Hasil Uji hanya dapat dicetak setelah seluruh tahapan verifikasi berjenjang ditandatangani.</p>
        </div>
        <div class="p-6 space-y-4 text-xs">
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3">
                <div><span class="text-slate-500">No. Order:</span> <strong class="font-mono"><?= html_escape($order['order_number']) ?></strong></div>
                <div><span class="text-slate-500">Customer:</span> <strong><?= html_escape($order['customer_name']) ?></strong></div>
                <div><span class="text-slate-500">Status order:</span> <strong><?= html_escape(strtoupper($order['status'])) ?></strong></div>
            </div>
            <div class="font-bold text-slate-800">Progres verifikasi: <?= (int)$kab['signed_count'] ?> / <?= (int)$kab['total_steps'] ?> tingkat</div>
            <?php if ($order['status'] !== 'validasi' && $order['status'] !== 'completed'): ?>
                <p class="text-amber-800 bg-amber-50 border border-amber-200 rounded-xl p-3">Hasil belum dikirim ke tahap verifikasi (masih dalam pengujian).</p>
            <?php endif; ?>
            <ol class="space-y-1.5">
                <?php foreach ($approvals as $a): $ok = $a['status'] === 'approved'; ?>
                <li class="flex items-center justify-between p-2.5 rounded-lg border <?= $ok ? 'border-emerald-200 bg-emerald-50' : ($a['status'] === 'pending' ? 'border-amber-300 bg-amber-50' : 'border-slate-200 bg-slate-50') ?>">
                    <span><strong><?= (int)$a['urutan'] ?>.</strong> <?= html_escape($a['jabatan_approval']) ?> <span class="text-slate-500">&mdash; <?= html_escape($a['pegawai_nama']) ?></span></span>
                    <span class="font-bold <?= $ok ? 'text-emerald-700' : 'text-slate-500' ?>"><?= $ok ? 'Tertanda tangan' : ($a['status'] === 'pending' ? 'Menunggu' : 'Antre') ?></span>
                </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </div>
</body>
</html>
