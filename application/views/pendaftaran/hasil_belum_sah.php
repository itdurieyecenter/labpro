<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= html_escape($title ?? 'Hasil Lab Belum Sah') ?> - LIS PRO</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css') ?>">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4 sm:p-6">

    <div class="max-w-2xl w-full bg-white rounded-3xl shadow-xl border border-rose-200 overflow-hidden">
        
        <!-- Top Warning Header -->
        <div class="bg-gradient-to-r from-rose-900 via-rose-800 to-slate-900 p-6 sm:p-7 text-white text-center relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none">
                <i data-lucide="shield-alert" class="w-60 h-60 text-white"></i>
            </div>
            
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-rose-500/20 border border-rose-400/40 text-rose-300 mb-3 shadow-inner">
                <i data-lucide="shield-alert" class="w-8 h-8"></i>
            </div>
            
            <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white uppercase">
                Hasil Uji Belum Sah!
            </h1>
            <p class="text-xs text-rose-200 mt-1 max-w-md mx-auto">
                Dokumen hasil pemeriksaan ini belum dapat diterbitkan atau dicetak sebagai sertifikat resmi karena masih dalam masa approval berjenjang.
            </p>
            
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-950/70 border border-rose-400/50 text-rose-300 text-[11px] font-bold mt-3">
                <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                <span>Status: Belum Memenuhi Syarat Legalitas Hasil Sah</span>
            </div>
        </div>

        <!-- Body Content -->
        <div class="p-6 sm:p-8 space-y-6">
            
            <!-- Identitas Pasien & Sampel -->
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Barcode Sampel</span>
                    <strong class="font-mono text-slate-900 text-xs block"><?= html_escape($reg['barcode_sampel']) ?></strong>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">No. Registrasi</span>
                    <strong class="font-mono text-slate-800 text-xs block"><?= html_escape($reg['nomor_registrasi']) ?></strong>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Nama Pasien</span>
                    <strong class="text-slate-900 text-xs block truncate"><?= html_escape($reg['nama_pasien']) ?></strong>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Tgl. Registrasi</span>
                    <span class="text-slate-700 text-xs block"><?= date('d/m/Y', strtotime($reg['tanggal_pendaftaran'])) ?></span>
                </div>
            </div>

            <!-- Tracker Progres Approval Berjenjang -->
            <div class="space-y-3">
                <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                    <span class="text-xs font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="git-commit" class="w-4 h-4 text-rose-600"></i>
                        <span>Progres Rantai Pengesahan (<?= $keabsahan['signed_count'] ?> / <?= $keabsahan['total_steps'] ?> Tingkat Disahkan)</span>
                    </span>
                    <span class="text-[11px] font-bold text-rose-700 bg-rose-50 px-2.5 py-0.5 rounded-full border border-rose-200">
                        Sisa <?= $keabsahan['remaining_count'] ?> TTD
                    </span>
                </div>

                <div class="space-y-2">
                    <?php if (!empty($approvals)): ?>
                        <?php foreach ($approvals as $app): ?>
                            <?php 
                                $isApproved = ($app['status'] === 'approved' && !empty($app['tanda_tangan']));
                                $isPending = ($app['status'] === 'pending');
                            ?>
                            <div class="flex items-center justify-between p-3 rounded-xl border <?= $isApproved ? 'border-emerald-200 bg-emerald-50/40' : ($isPending ? 'border-amber-300 bg-amber-50/60 ring-2 ring-amber-300/40' : 'border-slate-200 bg-slate-50/60 opacity-70') ?> text-xs">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="w-6 h-6 rounded-lg font-mono font-bold text-[10px] flex items-center justify-center <?= $isApproved ? 'bg-emerald-600 text-white' : ($isPending ? 'bg-amber-600 text-white animate-pulse' : 'bg-slate-300 text-slate-700') ?>">
                                        <?= $app['urutan'] ?>
                                    </span>
                                    <div class="min-w-0">
                                        <strong class="text-slate-900 block truncate"><?= html_escape($app['jabatan_approval']) ?></strong>
                                        <span class="text-[11px] text-slate-500 block truncate"><?= html_escape($app['pegawai_nama']) ?></span>
                                    </div>
                                </div>

                                <div class="flex-shrink-0 text-right">
                                    <?php if ($isApproved): ?>
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-full border border-emerald-300">
                                            <i data-lucide="check" class="w-3 h-3 text-emerald-700"></i>
                                            <span>Sudah Bertandatangan</span>
                                        </span>
                                    <?php elseif ($isPending): ?>
                                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-900 bg-amber-100 px-2 py-0.5 rounded-full border border-amber-300 animate-pulse">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                            <span>Giliran Menandatangani</span>
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 text-[10px] font-medium text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">
                                            <i data-lucide="lock" class="w-3 h-3"></i>
                                            <span>Antrean</span>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Penjelasan Kebijakan Hukum & Akreditasi -->
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-900 space-y-1.5">
                <div class="font-bold flex items-center gap-1.5 text-amber-950">
                    <i data-lucide="info" class="w-4 h-4 text-amber-700"></i>
                    <span>Kebijakan Pengendalian Mutu & Validasi Resmi (ISO 15189):</span>
                </div>
                <p class="leading-relaxed text-[11px] text-amber-900/90">
                    <?= html_escape($keabsahan['keterangan_hukum']) ?>
                </p>
                <div class="text-[10px] text-amber-800 font-semibold pt-1">
                    Mode Pembatasan: <strong>Blokir Cetak Sampai Seluruh Tanda Tangan Lengkap</strong>.
                </div>
            </div>

            <!-- Tombol Navigasi & Aksi -->
            <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-3">
                <button type="button" onclick="window.history.back()" class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-100 transition-colors text-center">
                    ← Kembali ke Halaman Sebelumnya
                </button>

                <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                    <a href="<?= site_url('validasi') ?>" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold shadow-md shadow-emerald-900/20 transition-all flex items-center justify-center gap-2 border border-emerald-600">
                        <i data-lucide="stamp" class="w-4 h-4 text-amber-300"></i>
                        <span>Buka Meja Validasi & Approval</span>
                    </a>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div class="bg-slate-50 border-t border-slate-200 px-6 py-3 text-center text-[10px] text-slate-400">
            Laboratorium Information System (LIS PRO) &bull; UPT Laboratorium Kesehatan Provinsi Riau
        </div>
    </div>

    <script>
        if (window.lucide) {
            lucide.createIcons();
        }
    </script>
</body>
</html>
