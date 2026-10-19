<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-6">

    <!-- 1. BREADCRUMB & PAGE HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="<?= site_url('pengaturan') ?>" class="hover:text-emerald-700 transition-colors">Pengaturan Sistem</a>
                <span>/</span>
                <span class="text-emerald-800 font-semibold">Setting Approval Hasil Lab</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="stamp" class="w-6 h-6 text-emerald-700"></i>
                </span>
                <span>Setting Hirarki & Approval Hasil Lab Berjenjang</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Konfigurasi tahapan verifikasi berjenjang (multi-level hingga 10+ tingkat), penugasan pegawai, nomor urut, dan tanda tangan digital sebelum hasil lab dirilis ke customer.
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" onclick="location.reload()" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold flex items-center gap-2 transition-colors border border-slate-200" title="Muat Ulang Halaman">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Refresh</span>
            </button>
            <?php if ($can_edit): ?>
            <button type="button" id="btn-tambah-approval" onclick="openAddModal()" class="px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold flex items-center gap-2 shadow-xs transition-all border border-emerald-600">
                <i data-lucide="plus-circle" class="w-4 h-4 text-amber-300"></i>
                <span>+ Tambah Tingkat Approval</span>
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- 2. STATISTIK RINGKAS HIRARKI APPROVAL -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white p-4 rounded-2xl border border-emerald-200 bg-emerald-50/20 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center justify-center flex-shrink-0">
                <i data-lucide="layers" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-emerald-800 block truncate">Total Tingkat Approval</span>
                <span class="text-xl font-extrabold text-emerald-950 leading-tight"><?= count($rules) ?> Tingkat</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-teal-200 bg-teal-50/20 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-teal-100 text-teal-800 border border-teal-300 flex items-center justify-center flex-shrink-0">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-teal-800 block truncate">Pegawai Ditugaskan</span>
                <?php
                    $uniquePegawai = count(array_unique(array_column($rules, 'pegawai_id')));
                ?>
                <span class="text-xl font-extrabold text-teal-950 leading-tight"><?= $uniquePegawai ?> Pegawai</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-amber-200 bg-amber-50/20 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-800 border border-amber-300 flex items-center justify-center flex-shrink-0">
                <i data-lucide="file-signature" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-amber-800 block truncate">Tanda Tangan Terdaftar</span>
                <?php
                    $hasSignCount = count(array_filter($rules, function($r) { return !empty($r['pegawai_tanda_tangan']); }));
                ?>
                <span class="text-xl font-extrabold text-amber-950 leading-tight"><?= $hasSignCount ?> / <?= count($rules) ?></span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 border border-slate-300 flex items-center justify-center flex-shrink-0">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 block truncate">Standar Validasi</span>
                <span class="text-sm font-extrabold text-slate-900 leading-tight block">KAN ISO 15189</span>
            </div>
        </div>
    </div>

    <!-- 3. VISUAL WORKFLOW PIPELINE (ROADMAP HIRARKI) -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                <i data-lucide="git-commit" class="w-4 h-4 text-emerald-700"></i>
                <span>Alur Pengesahan Berjenjang (Sequential Approval Pipeline)</span>
            </h2>
            <span class="text-[11px] text-slate-400">Pelepasan hasil ke customer wajib melewati urutan ini secara runtut</span>
        </div>

        <?php if (!empty($rules)): ?>
        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 overflow-x-auto">
            <div class="flex items-center gap-2 sm:gap-3 min-w-max">
                <!-- Start Point: Analis Input -->
                <div class="flex items-center gap-2">
                    <div class="px-3 py-2 rounded-xl bg-slate-200 border border-slate-300 text-center">
                        <span class="text-[9px] uppercase font-bold text-slate-500 block">Titik Awal</span>
                        <span class="text-xs font-bold text-slate-800">Hasil Selesai Diinput</span>
                    </div>
                    <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400 flex-shrink-0"></i>
                </div>

                <!-- Approval Steps -->
                <?php foreach ($rules as $idx => $r): ?>
                <div class="flex items-center gap-2">
                    <div class="p-3 rounded-xl <?= $r['is_active'] ? 'bg-white border-emerald-300 text-emerald-950 shadow-2xs' : 'bg-slate-100 border-slate-200 opacity-60 text-slate-500' ?> border min-w-[200px] max-w-[240px]">
                        <div class="flex items-center justify-between mb-1">
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black <?= $r['is_active'] ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-slate-200 text-slate-600' ?>">
                                TINGKAT <?= $r['urutan'] ?>
                            </span>
                            <?php if (!empty($r['pegawai_tanda_tangan'])): ?>
                            <span class="text-[9px] font-semibold text-emerald-700 flex items-center gap-0.5" title="Tanda tangan terdaftar">
                                <i data-lucide="check" class="w-3 h-3 text-emerald-600"></i> TTD
                            </span>
                            <?php endif; ?>
                        </div>
                        <strong class="text-xs font-bold block truncate text-slate-900" title="<?= html_escape($r['jabatan_approval']) ?>">
                            <?= html_escape($r['jabatan_approval']) ?>
                        </strong>
                        <span class="text-[11px] text-slate-600 block truncate" title="<?= html_escape($r['nama_formatted']) ?>">
                            <?= html_escape($r['nama_formatted']) ?>
                        </span>
                        <span class="text-[9px] text-slate-400 block font-mono">
                            NIP: <?= html_escape($r['nip'] ?: '-') ?>
                        </span>
                    </div>

                    <?php if ($idx < count($rules) - 1): ?>
                    <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400 flex-shrink-0"></i>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

                <!-- End Point: Released -->
                <div class="flex items-center gap-2">
                    <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400 flex-shrink-0"></i>
                    <div class="px-3.5 py-2 rounded-xl bg-emerald-800 text-white border border-emerald-900 text-center shadow-xs">
                        <span class="text-[9px] uppercase font-bold text-amber-300 block">Selesai & Resmi</span>
                        <span class="text-xs font-black">Rilis ke Customer</span>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="p-6 text-center text-slate-400 bg-slate-50 rounded-xl border border-dashed border-slate-300">
            <p class="text-xs">Belum ada tahapan approval yang dibuat. Klik tombol <strong>+ Tambah Tingkat Approval</strong> di atas untuk mengonfigurasi hirarki pengesahan.</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- 4. TABEL DAFTAR TINGKAT APPROVAL -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-200 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Daftar Konfigurasi Urutan Approval</h3>
                <p class="text-xs text-slate-500">Anda dapat mengubah nomor urut langsung atau menggunakan tombol panah naik/turun untuk mengatur posisi.</p>
            </div>
            <div class="text-xs text-slate-500 font-medium">
                Kapasitas: <span class="font-bold text-emerald-800">Tidak terbatas (bisa 10+ tingkat)</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-bold bg-slate-50">
                        <th class="py-3 px-3 w-16 text-center">Urutan</th>
                        <th class="py-3 px-3">Pegawai Penanggung Jawab</th>
                        <th class="py-3 px-3">Jabatan Approval di Lembar Hasil</th>
                        <th class="py-3 px-3 text-center w-36">Tanda Tangan</th>
                        <th class="py-3 px-3">Wewenang / Keterangan</th>
                        <th class="py-3 px-3 text-center w-24">Status</th>
                        <th class="py-3 px-3 text-center w-44">Aksi Pengaturan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    <?php if (empty($rules)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-8 text-slate-400">
                            Belum ada konfigurasi approval hasil lab.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($rules as $idx => $r): ?>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <!-- Urutan -->
                        <td class="py-3 px-3 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl font-black text-xs bg-emerald-100 text-emerald-900 border border-emerald-300 shadow-2xs">
                                <?= $r['urutan'] ?>
                            </span>
                        </td>

                        <!-- Pegawai -->
                        <td class="py-3 px-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 border border-slate-200 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                    <?php if (!empty($r['foto'])): ?>
                                        <img src="<?= base_url($r['foto']) ?>" class="w-full h-full object-cover rounded-xl" alt="Foto">
                                    <?php else: ?>
                                        <i data-lucide="user" class="w-4 h-4 text-slate-400"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="min-w-0">
                                    <strong class="text-slate-900 block leading-tight truncate"><?= html_escape($r['nama_formatted']) ?></strong>
                                    <span class="text-[10px] text-slate-500 font-mono block">NIP: <?= html_escape($r['nip'] ?: '-') ?></span>
                                    <span class="text-[10px] text-teal-700 font-medium truncate block"><?= html_escape($r['pegawai_jabatan'] ?: $r['seksi_ruangan']) ?></span>
                                </div>
                            </div>
                        </td>

                        <!-- Jabatan Approval -->
                        <td class="py-3 px-3">
                            <strong class="text-slate-900 block leading-tight"><?= html_escape($r['jabatan_approval']) ?></strong>
                            <span class="text-[10px] text-slate-400">Muncul pada stempel/lembar hasil resmi</span>
                        </td>

                        <!-- Tanda Tangan Digital -->
                        <td class="py-3 px-3 text-center">
                            <?php if (!empty($r['pegawai_tanda_tangan'])): ?>
                            <div class="inline-flex flex-col items-center">
                                <div class="w-24 h-10 border border-slate-200 rounded-lg p-1 bg-white flex items-center justify-center shadow-2xs">
                                    <img src="<?= base_url($r['pegawai_tanda_tangan']) ?>" class="max-h-full max-w-full object-contain" alt="TTD">
                                </div>
                                <span class="text-[9px] text-emerald-700 font-bold mt-0.5">Tanda Tangan Siap</span>
                            </div>
                            <?php else: ?>
                            <span class="px-2 py-1 rounded-lg text-[10px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                Belum Upload TTD
                            </span>
                            <?php endif; ?>
                        </td>

                        <!-- Keterangan -->
                        <td class="py-3 px-3">
                            <p class="text-[11px] text-slate-600 line-clamp-2 max-w-xs">
                                <?= html_escape($r['keterangan'] ?: 'Memeriksa dan menyetujui kelayakan hasil uji laboratorium sebelum dirilis.') ?>
                            </p>
                            <?php if ($r['is_required']): ?>
                                <span class="inline-block mt-0.5 px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 text-slate-600 border border-slate-200">Wajib Dilalui</span>
                            <?php endif; ?>
                        </td>

                        <!-- Status -->
                        <td class="py-3 px-3 text-center">
                            <?php if ($r['is_active']): ?>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    Aktif
                                </span>
                            <?php else: ?>
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-300">
                                    Nonaktif
                                </span>
                            <?php endif; ?>
                        </td>

                        <!-- Aksi -->
                        <td class="py-3 px-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <!-- Geser Urutan Naik / Turun -->
                                <?php if ($can_edit): ?>
                                <button type="button" onclick="moveOrder(<?= $r['id'] ?>, 'up')" class="p-1.5 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-600 transition-colors" title="Pindah Urutan Naik (Prioritas Lebih Dulu)" <?= $idx === 0 ? 'disabled class="opacity-30 cursor-not-allowed p-1.5 rounded-lg border border-slate-200"' : '' ?>>
                                    <i data-lucide="arrow-up" class="w-3.5 h-3.5"></i>
                                </button>
                                <button type="button" onclick="moveOrder(<?= $r['id'] ?>, 'down')" class="p-1.5 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-600 transition-colors" title="Pindah Urutan Turun" <?= $idx === count($rules) - 1 ? 'disabled class="opacity-30 cursor-not-allowed p-1.5 rounded-lg border border-slate-200"' : '' ?>>
                                    <i data-lucide="arrow-down" class="w-3.5 h-3.5"></i>
                                </button>
                                
                                <!-- Edit -->
                                <button type="button" onclick="openEditModal(<?= $r['id'] ?>)" class="p-1.5 rounded-lg bg-teal-50 hover:bg-teal-100 text-teal-800 border border-teal-300 transition-colors" title="Ubah Pengaturan & Tanda Tangan">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                </button>
                                <?php endif; ?>

                                <!-- Hapus -->
                                <?php if ($can_delete): ?>
                                <button type="button" onclick="deleteApproval(<?= $r['id'] ?>, '<?= html_escape($r['jabatan_approval']) ?>')" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-300 transition-colors" title="Hapus Tingkat Approval Ini">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 5. PENGATURAN KEBIJAKAN KEABSAHAN HASIL LAB & TANDA TANGAN BERJENJANG -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-200 bg-gradient-to-r from-amber-500/10 via-emerald-500/5 to-transparent flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-900 border border-amber-300 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="shield-alert" class="w-5 h-5 text-amber-700"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span>Kebijakan Keabsahan Hasil Lab & Tanda Tangan Berjenjang</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">Legal Policy</span>
                    </h3>
                    <p class="text-xs text-slate-500">
                        Aturan hukum penerbitan: Jika dalam masa approval berjenjang belum seluruh pihak bertandatangan, hasil lab belum sah dan dicegah dari penyalahgunaan.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-400 font-medium">Validasi ISO 15189 / KAN</span>
            </div>
        </div>

        <form id="form-approval-policy" onsubmit="submitApprovalPolicy(event)" class="p-5 sm:p-6 space-y-6">
            <!-- Switch 1: Wajib Semua TTD -->
            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="file-check-2" class="w-4 h-4 text-emerald-700"></i>
                        <span>Wajib 100% Seluruh Tingkat Bertandatangan Digital Agar Sah</span>
                    </label>
                    <p class="text-[11px] text-slate-500 leading-relaxed max-w-2xl">
                        Hasil laboratorium baru berstatus <strong>RESMI SAH</strong> apabila setiap tingkat approval (Tingkat 1 hingga tingkat terakhir) telah memberikan persetujuan (Approved) dan membubuhkan tanda tangan digital. Jika masih ada satu atau lebih tingkat yang belum menandatangani, status hasil tetap dianggap <strong>BELUM SAH</strong>.
                    </p>
                </div>
                <div class="flex items-center flex-shrink-0">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="approval_wajib_semua_ttd" id="policy-wajib-semua-ttd" value="1" <?= (!isset($policy['wajib_semua_ttd']) || $policy['wajib_semua_ttd'] == '1') ? 'checked' : '' ?> class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-300 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                        <span class="ml-3 text-xs font-bold text-slate-700 peer-checked:text-emerald-800">Wajib Lengkap</span>
                    </label>
                </div>
            </div>

            <!-- Radio Group: Mode Perlakuan Saat Masa Berjenjang Belum Lengkap -->
            <div class="space-y-3">
                <div>
                    <label class="text-xs font-bold text-slate-900 block mb-1">
                        Mode Perlakuan Saat Masa Berjenjang Belum Semua Tanda Tangan:
                    </label>
                    <p class="text-[11px] text-slate-500">
                        Pilih bagaimana sistem memperlakukan dokumen hasil laboratorium yang proses approval berjenjangnya masih berjalan (belum sah).
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Option 1: Watermark & Warning (Draft Internal) -->
                    <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all hover:bg-slate-50 <?= (!isset($policy['mode_belum_lengkap']) || $policy['mode_belum_lengkap'] === 'watermark') ? 'border-amber-500 bg-amber-50/20 shadow-xs' : 'border-slate-200' ?>" id="label-mode-watermark">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center gap-2.5">
                                <input type="radio" name="approval_mode_belum_lengkap" value="watermark" <?= (!isset($policy['mode_belum_lengkap']) || $policy['mode_belum_lengkap'] === 'watermark') ? 'checked' : '' ?> onchange="updateModeUI()" class="w-4 h-4 text-amber-600 focus:ring-amber-500">
                                <span class="font-bold text-xs text-slate-900">Mode Watermark & Warning (Draft Lembar Hasil)</span>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-800 border border-amber-300">Rekomendasi Fleksibel</span>
                        </div>
                        <p class="text-[11px] text-slate-600 leading-relaxed pl-6">
                            Hasil lab <strong>tetap dapat dicetak</strong> untuk keperluan telaah internal antar tim klinis. Namun, dokumen cetak akan otomatis dibubuhi <strong>watermark besar diagonal "HASIL BELUM SAH"</strong>, banner peringatan merah tebal, stempel status belum sah, dan QR code keabsahan dinonaktifkan sehingga tidak dapat disalahgunakan sebagai hasil resmi ke pasien.
                        </p>
                    </label>

                    <!-- Option 2: Blokir Total (Strict Lockout) -->
                    <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all hover:bg-slate-50 <?= (isset($policy['mode_belum_lengkap']) && $policy['mode_belum_lengkap'] === 'block') ? 'border-rose-500 bg-rose-50/20 shadow-xs' : 'border-slate-200' ?>" id="label-mode-block">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center gap-2.5">
                                <input type="radio" name="approval_mode_belum_lengkap" value="block" <?= (isset($policy['mode_belum_lengkap']) && $policy['mode_belum_lengkap'] === 'block') ? 'checked' : '' ?> onchange="updateModeUI()" class="w-4 h-4 text-rose-600 focus:ring-rose-500">
                                <span class="font-bold text-xs text-slate-900">Mode Blokir Total (Strict Lockout)</span>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-rose-100 text-rose-800 border border-rose-300">Protokol Ketat</span>
                        </div>
                        <p class="text-[11px] text-slate-600 leading-relaxed pl-6">
                            Dokumen hasil <strong>sama sekali tidak dapat dicetak/diakses</strong> sebelum 100% semua tanda tangan berjenjang terpenuhi. Pengguna yang mencoba mencetak akan dialihkan ke layar peringatan resmi yang merinci tahapan mana yang masih belum ditandatangani.
                        </p>
                    </label>
                </div>
            </div>

            <!-- Detail Kustomisasi Watermark & Keterangan Hukum -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="lis-form-label font-bold text-slate-900">
                        Teks Watermark Dokumen Belum Sah
                    </label>
                    <input type="text" name="approval_teks_watermark" id="policy-teks-watermark" value="<?= html_escape($policy['teks_watermark'] ?? 'HASIL BELUM SAH • MENUNGGU APPROVAL BERJENJANG') ?>" required class="lis-form-control text-xs font-bold text-rose-900 border-rose-300 focus:border-rose-500">
                    <span class="text-[10px] text-slate-400 block mt-1">Dicetak melintang secara diagonal pada latar lembar hasil saat belum sah.</span>
                </div>

                <div>
                    <label class="lis-form-label font-bold text-slate-900">
                        Klausul Hukum & Disclaimer Keabsahan
                    </label>
                    <textarea name="approval_keterangan_hukum" id="policy-keterangan-hukum" rows="2" class="lis-form-control text-xs"><?= html_escape($policy['keterangan_hukum'] ?? 'Hasil pemeriksaan ini BELUM SAH secara hukum dan belum dapat diserahkan ke pelanggan karena proses verifikasi berjenjang belum lengkap bertandatangan.') ?></textarea>
                    <span class="text-[10px] text-slate-400 block mt-1">Dicantumkan pada catatan kaki dokumen saat status hasil belum lengkap bertandatangan.</span>
                </div>
            </div>

            <!-- Tombol Simpan Kebijakan -->
            <?php if ($can_edit): ?>
            <div class="pt-3 border-t border-slate-200 flex items-center justify-between">
                <div class="text-[11px] text-slate-400 flex items-center gap-1.5">
                    <i data-lucide="info" class="w-4 h-4 text-slate-400"></i>
                    <span>Kebijakan ini berlaku langsung untuk seluruh lembar hasil pemeriksaan laboratorium di sistem.</span>
                </div>
                <button type="submit" id="btn-save-policy" class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-md shadow-amber-900/10 transition-all flex items-center gap-2 border border-amber-500">
                    <i data-lucide="save" class="w-4 h-4 text-white"></i>
                    <span>Simpan Pengaturan Kebijakan Keabsahan</span>
                </button>
            </div>
            <?php endif; ?>
        </form>
    </div>

</div>

<!-- ========================================================================= -->
<!-- MODAL TAMBAH / UBAH TINGKAT APPROVAL                                      -->
<!-- ========================================================================= -->
<div id="modal-approval-rule" class="lis-modal">
    <div class="lis-modal-box max-w-2xl w-full">
        <!-- Header Modal -->
        <div class="lis-modal-header bg-gradient-to-r from-slate-900 via-teal-900 to-slate-900 border-b border-amber-400/40">
            <div>
                <h3 class="lis-modal-title text-white">
                    <i data-lucide="stamp" class="w-5 h-5 text-amber-300"></i>
                    <span id="modal-rule-title">Tambah Tingkat Approval Baru</span>
                </h3>
                <p class="lis-modal-subtitle">Konfigurasi Nomor Urut, Penugasan Pegawai, Wewenang & Tanda Tangan Digital</p>
            </div>
            <button type="button" onclick="closeApprovalModal()" class="lis-modal-close" title="Tutup">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Body Form -->
        <form id="form-approval-rule" onsubmit="submitApprovalRule(event)" enctype="multipart/form-data" class="lis-modal-body p-5 space-y-4 bg-white max-h-[80vh] overflow-y-auto">
            <input type="hidden" id="rule-id" name="id">

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <!-- Nomor Urut Approval -->
                <div>
                    <label class="lis-form-label font-bold text-slate-900">
                        Nomor Urut <span class="req">*</span>
                    </label>
                    <input type="number" name="urutan" id="rule-urutan" required min="1" max="99" value="<?= $next_urutan ?>" class="lis-form-control font-black text-center text-sm border-emerald-300">
                    <span class="text-[10px] text-slate-400 block mt-1">Contoh: 1, 2, 3... hingga 10+</span>
                </div>

                <!-- Pilih Pegawai -->
                <div class="sm:col-span-2">
                    <label class="lis-form-label font-bold text-slate-900">
                        Pilih Pegawai Penanggung Jawab <span class="req">*</span>
                    </label>
                    <select name="pegawai_id" id="rule-pegawai-id" required onchange="onPegawaiChanged()" class="lis-form-control text-xs">
                        <option value="">-- Pilih Pegawai dari Master --</option>
                        <?php foreach ($pegawai_list as $p): ?>
                        <option value="<?= $p['id'] ?>" 
                                data-jabatan="<?= html_escape($p['nama_jabatan'] ?: 'Pejabat Verifikator') ?>"
                                data-nip="<?= html_escape($p['nip'] ?: '-') ?>"
                                data-ttd="<?= html_escape($p['tanda_tangan'] ?: '') ?>">
                            <?= html_escape($p['nama_formatted']) ?> (NIP: <?= html_escape($p['nip'] ?: '-') ?> &bull; <?= html_escape($p['nama_jabatan'] ?: 'Pegawai') ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Jabatan Approval Pada Lembar Hasil Resmi -->
            <div>
                <label class="lis-form-label font-bold text-slate-900">
                    Titel / Jabatan Approval di Lembar Hasil <span class="req">*</span>
                </label>
                <input type="text" name="jabatan_approval" id="rule-jabatan" required placeholder="Contoh: Analis Pemeriksa / Dokter Penanggung Jawab Sp.PK / Kepala Labkesda" class="lis-form-control text-xs font-semibold">
                
                <!-- Quick Preset Buttons -->
                <div class="flex items-center gap-1.5 flex-wrap mt-1.5">
                    <span class="text-[10px] text-slate-400">Preset Cepat:</span>
                    <button type="button" onclick="setJabatanPreset('Analis Pemeriksa Lab (ATLM)')" class="px-2 py-0.5 rounded text-[10px] bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200">
                        Analis ATLM
                    </button>
                    <button type="button" onclick="setJabatanPreset('Koordinator Teknis & Mutu Lab')" class="px-2 py-0.5 rounded text-[10px] bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200">
                        Koordinator Teknis
                    </button>
                    <button type="button" onclick="setJabatanPreset('Dokter Penanggung Jawab Sp.PK')" class="px-2 py-0.5 rounded text-[10px] bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200">
                        Dokter Sp.PK
                    </button>
                    <button type="button" onclick="setJabatanPreset('Kepala UPT Laboratorium Kesehatan')" class="px-2 py-0.5 rounded text-[10px] bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200">
                        Kepala UPT Labkesda
                    </button>
                </div>
            </div>

            <!-- Keterangan Wewenang -->
            <div>
                <label class="lis-form-label">Deskripsi Tugas / Wewenang Verifikasi</label>
                <textarea name="keterangan" id="rule-keterangan" rows="2" class="lis-form-control text-xs" placeholder="Misal: Memeriksa kesesuaian nilai hasil alat dengan kendali mutu analitik harian..."></textarea>
            </div>

            <!-- Tanda Tangan Digital Pegawai -->
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="lis-form-label font-bold text-slate-900 mb-0 flex items-center gap-1.5">
                        <i data-lucide="file-signature" class="w-4 h-4 text-emerald-700"></i>
                        <span>Tanda Tangan Digital Pegawai</span>
                    </label>
                    <span class="text-[10px] text-slate-400">Digunakan pada lembar approval & cetak hasil resmi</span>
                </div>

                <!-- Preview TTD Lama -->
                <div id="wrapper-existing-ttd" class="hidden flex items-center gap-3 p-2.5 bg-white rounded-lg border border-slate-200">
                    <img id="img-existing-ttd" src="" alt="TTD Pegawai" class="h-12 max-w-[150px] object-contain border border-slate-100 p-1 rounded">
                    <div class="text-xs">
                        <span class="font-bold text-emerald-800 block">Tanda Tangan Tersimpan</span>
                        <span class="text-[10px] text-slate-400">Pegawai ini sudah memiliki tanda tangan aktif. Anda dapat menggambarnya ulang atau mengupload gambar baru jika diperlukan.</span>
                    </div>
                </div>

                <!-- Tab Pilih Metode TTD: Canvas vs Upload File -->
                <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
                    <button type="button" onclick="switchTtdTab('canvas')" id="btn-tab-ttd-canvas" class="px-3 py-1 text-xs font-bold rounded-lg bg-emerald-800 text-white shadow-2xs">
                        ✍️ Goreskan Tanda Tangan (Canvas Pad)
                    </button>
                    <button type="button" onclick="switchTtdTab('upload')" id="btn-tab-ttd-upload" class="px-3 py-1 text-xs font-semibold rounded-lg text-slate-600 hover:text-slate-900">
                        📁 Upload Gambar Tanda Tangan (PNG/JPG)
                    </button>
                </div>

                <!-- Panel 1: Canvas Signature Pad -->
                <div id="panel-ttd-canvas" class="space-y-2">
                    <div class="relative bg-white rounded-xl border-2 border-dashed border-slate-300 overflow-hidden shadow-inner">
                        <canvas id="signature-canvas" width="600" height="160" class="w-full h-36 touch-none cursor-crosshair bg-white"></canvas>
                        <div class="absolute bottom-2 left-2 text-[10px] text-slate-400 font-mono pointer-events-none select-none">
                            Tanda tangan di dalam area ini menggunakan mouse atau stylus pen
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <button type="button" onclick="clearSignatureCanvas()" class="px-2.5 py-1 text-[11px] font-semibold text-rose-700 hover:bg-rose-50 rounded-lg transition-colors border border-rose-200 flex items-center gap-1">
                            <i data-lucide="eraser" class="w-3.5 h-3.5"></i>
                            <span>Hapus / Bersihkan Canvas</span>
                        </button>
                        <span class="text-[10px] text-slate-400">Format otomatis disimpan sebagai gambar PNG transparan</span>
                    </div>
                    <input type="hidden" name="signature_base64" id="signature-base64">
                </div>

                <!-- Panel 2: Upload File Gambar TTD -->
                <div id="panel-ttd-upload" class="hidden space-y-2">
                    <input type="file" name="tanda_tangan_file" id="tanda-tangan-file" accept="image/png, image/jpeg" class="lis-form-control text-xs bg-white">
                    <span class="text-[10px] text-slate-400 block">Disarankan gambar berlatar belakang transparan (format PNG) dengan resolusi minimal 300x150 px.</span>
                </div>
            </div>

            <!-- Pengaturan Status -->
            <div class="flex items-center gap-6 p-3 bg-slate-50 rounded-xl border border-slate-200">
                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-800">
                    <input type="checkbox" name="is_required" id="rule-is-required" value="1" checked class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                    <span>Wajib Dilalui Sebelum Rilis</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-800">
                    <input type="checkbox" name="is_active" id="rule-is-active" value="1" checked class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500">
                    <span>Status Aktif</span>
                </label>
            </div>

            <!-- Footer Modal -->
            <div class="pt-3 border-t border-slate-200 flex items-center justify-between">
                <button type="button" onclick="closeApprovalModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-100 transition-colors">
                    Batal
                </button>
                <button type="submit" id="btn-save-rule" class="px-6 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold shadow-md shadow-emerald-900/20 transition-all flex items-center gap-2 border border-emerald-600">
                    <i data-lucide="check" class="w-4 h-4 text-amber-300"></i>
                    <span>Simpan Tingkat Approval</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let canvas = null;
    let ctx = null;
    let isDrawing = false;
    let hasDrawn = false;

    document.addEventListener('DOMContentLoaded', function() {
        initSignatureCanvas();
        if (window.lucide) lucide.createIcons();
    });

    // Inisialisasi Canvas Tanda Tangan
    function initSignatureCanvas() {
        canvas = document.getElementById('signature-canvas');
        if (!canvas) return;
        ctx = canvas.getContext('2d');
        ctx.strokeStyle = '#0f172a'; // Slate-900
        ctx.lineWidth = 2.5;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            return {
                x: (clientX - rect.left) * scaleX,
                y: (clientY - rect.top) * scaleY
            };
        }

        function startDraw(e) {
            isDrawing = true;
            hasDrawn = true;
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
            if (e.cancelable) e.preventDefault();
        }

        function draw(e) {
            if (!isDrawing) return;
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            if (e.cancelable) e.preventDefault();
        }

        function stopDraw() {
            if (isDrawing) {
                isDrawing = false;
                document.getElementById('signature-base64').value = canvas.toDataURL('image/png');
            }
        }

        // Mouse events
        canvas.addEventListener('mousedown', startDraw);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDraw);
        canvas.addEventListener('mouseleave', stopDraw);

        // Touch events
        canvas.addEventListener('touchstart', startDraw, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        canvas.addEventListener('touchend', stopDraw);
    }

    function clearSignatureCanvas() {
        if (!ctx || !canvas) return;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        hasDrawn = false;
        document.getElementById('signature-base64').value = '';
    }

    function switchTtdTab(tab) {
        const btnCanvas = document.getElementById('btn-tab-ttd-canvas');
        const btnUpload = document.getElementById('btn-tab-ttd-upload');
        const panelCanvas = document.getElementById('panel-ttd-canvas');
        const panelUpload = document.getElementById('panel-ttd-upload');

        if (tab === 'canvas') {
            btnCanvas.className = 'px-3 py-1 text-xs font-bold rounded-lg bg-emerald-800 text-white shadow-2xs';
            btnUpload.className = 'px-3 py-1 text-xs font-semibold rounded-lg text-slate-600 hover:text-slate-900';
            panelCanvas.classList.remove('hidden');
            panelUpload.classList.add('hidden');
        } else {
            btnUpload.className = 'px-3 py-1 text-xs font-bold rounded-lg bg-emerald-800 text-white shadow-2xs';
            btnCanvas.className = 'px-3 py-1 text-xs font-semibold rounded-lg text-slate-600 hover:text-slate-900';
            panelUpload.classList.remove('hidden');
            panelCanvas.classList.add('hidden');
        }
    }

    function onPegawaiChanged() {
        const sel = document.getElementById('rule-pegawai-id');
        const opt = sel.options[sel.selectedIndex];
        if (!opt || !opt.value) {
            document.getElementById('wrapper-existing-ttd').classList.add('hidden');
            return;
        }

        const defaultJabatan = opt.getAttribute('data-jabatan');
        const currentJabatan = document.getElementById('rule-jabatan').value;
        if (!currentJabatan || currentJabatan.trim() === '') {
            document.getElementById('rule-jabatan').value = defaultJabatan;
        }

        const ttdUrl = opt.getAttribute('data-ttd');
        if (ttdUrl && ttdUrl.trim() !== '') {
            document.getElementById('img-existing-ttd').src = '<?= base_url() ?>' + ttdUrl;
            document.getElementById('wrapper-existing-ttd').classList.remove('hidden');
        } else {
            document.getElementById('wrapper-existing-ttd').classList.add('hidden');
        }
    }

    function setJabatanPreset(title) {
        document.getElementById('rule-jabatan').value = title;
    }

    function openAddModal() {
        document.getElementById('form-approval-rule').reset();
        document.getElementById('rule-id').value = '';
        document.getElementById('rule-urutan').value = '<?= $next_urutan ?>';
        document.getElementById('modal-rule-title').textContent = 'Tambah Tingkat Approval Baru';
        document.getElementById('wrapper-existing-ttd').classList.add('hidden');
        clearSignatureCanvas();
        switchTtdTab('canvas');

        const modal = document.getElementById('modal-approval-rule');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.style.display = 'flex';
        if (window.lucide) lucide.createIcons();
    }

    function openEditModal(id) {
        LIS.fetchJson('<?= site_url('pengaturan/approval_get') ?>/' + id)
            .then(res => {
                if (res.status === 'success') {
                    const d = res.data;
                    document.getElementById('rule-id').value = d.id;
                    document.getElementById('rule-urutan').value = d.urutan;
                    document.getElementById('rule-pegawai-id').value = d.pegawai_id;
                    document.getElementById('rule-jabatan').value = d.jabatan_approval;
                    document.getElementById('rule-keterangan').value = d.keterangan || '';
                    document.getElementById('rule-is-required').checked = (parseInt(d.is_required) === 1);
                    document.getElementById('rule-is-active').checked = (parseInt(d.is_active) === 1);

                    document.getElementById('modal-rule-title').textContent = 'Ubah Tingkat Approval #' + d.urutan;

                    if (d.pegawai_tanda_tangan && d.pegawai_tanda_tangan.trim() !== '') {
                        document.getElementById('img-existing-ttd').src = '<?= base_url() ?>' + d.pegawai_tanda_tangan;
                        document.getElementById('wrapper-existing-ttd').classList.remove('hidden');
                    } else {
                        document.getElementById('wrapper-existing-ttd').classList.add('hidden');
                    }

                    clearSignatureCanvas();
                    switchTtdTab('canvas');

                    const modal = document.getElementById('modal-approval-rule');
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    modal.style.display = 'flex';
                    if (window.lucide) lucide.createIcons();
                } else {
                    LIS.showError('Gagal Memuat Data', res, res.message || 'Gagal mengambil detail tingkat approval.');
                }
            })
            .catch(err => {
                LIS.showError('Gagal Memuat Data', err, 'Terjadi kendala saat mengambil data tingkat approval.');
            });
    }

    function closeApprovalModal() {
        const modal = document.getElementById('modal-approval-rule');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        modal.style.display = 'none';
    }

    function submitApprovalRule(e) {
        e.preventDefault();
        const form = document.getElementById('form-approval-rule');
        const btnSave = document.getElementById('btn-save-rule');
        const origBtnText = btnSave.innerHTML;

        btnSave.disabled = true;
        btnSave.innerHTML = '<span class="inline-block animate-spin mr-1">⌛</span> Menyimpan...';

        const formData = new FormData(form);

        LIS.fetchJson('<?= site_url('pengaturan/approval_save') ?>', {
            method: 'POST',
            body: formData
        })
        .then(res => {
            btnSave.disabled = false;
            btnSave.innerHTML = origBtnText;

            if (res.status === true || res.status === 'success') {
                closeApprovalModal();
                LIS.toast('success', res.message || 'Konfigurasi tingkat approval berhasil disimpan.');
                setTimeout(() => location.reload(), 800);
            } else {
                LIS.showError('Gagal Menyimpan', res, res.message || 'Gagal menyimpan aturan approval.');
            }
        })
        .catch(err => {
            btnSave.disabled = false;
            btnSave.innerHTML = origBtnText;
            LIS.showError('Error Simpan Approval', err, 'Terjadi kesalahan sistem saat menyimpan tingkat approval.');
        });
    }

    function moveOrder(id, direction) {
        const fd = new FormData();
        fd.append('id', id);
        fd.append('direction', direction);

        LIS.fetchJson('<?= site_url('pengaturan/approval_move') ?>', {
            method: 'POST',
            body: fd
        })
        .then(res => {
            if (res.status === true || res.status === 'success') {
                LIS.toast('success', res.message || 'Urutan approval berhasil disesuaikan.');
                setTimeout(() => location.reload(), 500);
            } else {
                LIS.showError('Gagal Mengubah Urutan', res, res.message || 'Gagal memindahkan posisi.');
            }
        })
        .catch(err => {
            LIS.showError('Error Ubah Urutan', err, 'Terjadi kesalahan sistem saat memindahkan posisi.');
        });
    }

    function deleteApproval(id, jabatan) {
        Swal.fire({
            title: 'Hapus Tingkat Approval?',
            html: `Apakah Anda yakin ingin menghapus <strong>${jabatan}</strong> dari hirarki pengesahan?<br><small class="text-slate-400">Nomor urut setelahnya akan otomatis dinormalkan.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#b91c1c',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus Tingkat Ini',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('id', id);

                LIS.fetchJson('<?= site_url('pengaturan/approval_delete') ?>', {
                    method: 'POST',
                    body: fd
                })
                .then(res => {
                    if (res.status === true || res.status === 'success') {
                        LIS.toast('success', res.message || 'Tingkat approval berhasil dihapus.');
                        setTimeout(() => location.reload(), 600);
                    } else {
                        LIS.showError('Gagal Menghapus', res, res.message || 'Gagal menghapus tingkat approval.');
                    }
                })
                .catch(err => {
                    LIS.showError('Error Hapus', err, 'Terjadi kendala saat menghapus tingkat approval.');
                });
            }
        });
    }

    function updateModeUI() {
        const mode = document.querySelector('input[name="approval_mode_belum_lengkap"]:checked')?.value || 'watermark';
        const labelWm = document.getElementById('label-mode-watermark');
        const labelBlock = document.getElementById('label-mode-block');

        if (mode === 'watermark') {
            if (labelWm) {
                labelWm.classList.add('border-amber-500', 'bg-amber-50/20', 'shadow-xs');
                labelWm.classList.remove('border-slate-200');
            }
            if (labelBlock) {
                labelBlock.classList.remove('border-rose-500', 'bg-rose-50/20', 'shadow-xs');
                labelBlock.classList.add('border-slate-200');
            }
        } else {
            if (labelWm) {
                labelWm.classList.remove('border-amber-500', 'bg-amber-50/20', 'shadow-xs');
                labelWm.classList.add('border-slate-200');
            }
            if (labelBlock) {
                labelBlock.classList.add('border-rose-500', 'bg-rose-50/20', 'shadow-xs');
                labelBlock.classList.remove('border-slate-200');
            }
        }
    }

    function submitApprovalPolicy(e) {
        e.preventDefault();
        const form = document.getElementById('form-approval-policy');
        const btn = document.getElementById('btn-save-policy');
        const originalHtml = btn ? btn.innerHTML : '';

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin text-white"></i> <span>Menyimpan Kebijakan...</span>';
            if (window.lucide) lucide.createIcons();
        }

        const fd = new FormData(form);

        LIS.fetchJson('<?= site_url('pengaturan/approval_save_policy') ?>', {
            method: 'POST',
            body: fd
        })
        .then(res => {
            if (res.status === true || res.status === 'success') {
                LIS.toast('success', res.message || 'Kebijakan keabsahan berhasil disimpan.');
                Swal.fire({
                    title: 'Kebijakan Keabsahan Disimpan!',
                    html: `Pengaturan keabsahan hasil lab telah berhasil diperbarui.<br><small class="text-slate-500">Hasil lab yang belum lengkap semua tanda tangan berjenjang otomatis diberlakukan mode <strong>${res.data?.approval_mode_belum_lengkap === 'block' ? 'Blokir Ketat' : 'Watermark Draft'}</strong>.</small>`,
                    icon: 'success',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#059669'
                });
            } else {
                LIS.showError('Gagal Menyimpan Kebijakan', res, res.message || 'Gagal menyimpan pengaturan kebijakan approval.');
            }
        })
        .catch(err => {
            LIS.showError('Error Kebijakan', err, 'Terjadi kesalahan sistem saat menyimpan kebijakan approval.');
        })
        .finally(() => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                if (window.lucide) lucide.createIcons();
            }
        });
    }
</script>
