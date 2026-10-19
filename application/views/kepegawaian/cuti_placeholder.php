<div class="space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-slate-400">Kepegawaian</span>
                <span>/</span>
                <span class="text-emerald-800 font-semibold"><?= html_escape($module_name) ?></span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="<?= $icon ?>" class="w-6 h-6"></i>
                </span>
                <span><?= html_escape($module_name) ?></span>
            </h1>
            <p class="text-xs text-slate-500 mt-1"><?= html_escape($description) ?></p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="<?= site_url('pegawai') ?>" class="px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-2 shadow-sm transition-all border border-emerald-600">
                <i data-lucide="users" class="w-4 h-4"></i>
                <span>Kelola Data Pegawai</span>
            </a>
        </div>
    </div>

    <!-- Banner Info Modul Cuti ASN -->
    <div class="bg-gradient-to-br from-emerald-900 via-teal-900 to-slate-900 text-white rounded-3xl p-6 sm:p-8 border border-emerald-700/50 shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-72 h-72 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-10 -top-10 w-72 h-72 rounded-full bg-amber-500/10 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-400/20 text-amber-300 text-xs font-bold border border-amber-400/30 mb-4">
                <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                <span>Modul Kepegawaian & Cuti Terintegrasi SIMRS</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight leading-tight">
                Sistem Pengajuan & Approval Cuti ASN UPT Laboratorium Kesehatan Riau
            </h2>
            <p class="text-slate-300 text-sm mt-3 leading-relaxed">
                Modul ini disiapkan untuk terintegrasi penuh dengan <strong>Master Data Pegawai</strong>. Alur verifikasi berjenjang dari Pengusul $\rightarrow$ Atasan Langsung Seksi/Instalasi $\rightarrow$ Kasubbag Tata Usaha $\rightarrow$ Kepala UPT Laboratorium Kesehatan Provinsi Riau.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
                <div class="bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/10">
                    <span class="text-xs text-amber-300 font-bold block mb-1">Tahap 1: Master Pegawai</span>
                    <span class="text-xs text-slate-200">Database lengkap NIP BKN, Golongan, Seksi, dan Jabatan (Telah Aktif).</span>
                </div>
                <div class="bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/10">
                    <span class="text-xs text-amber-300 font-bold block mb-1">Tahap 2: Pengajuan Cuti</span>
                    <span class="text-xs text-slate-200">Kalkulasi otomatis sisa cuti N-2, N-1, dan N berjalan per pegawai.</span>
                </div>
                <div class="bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/10">
                    <span class="text-xs text-amber-300 font-bold block mb-1">Tahap 3: Approval & SK Cuti</span>
                    <span class="text-xs text-slate-200">Digital signature / barcode verifikasi persetujuan pimpinan Labkesda.</span>
                </div>
            </div>
        </div>
    </div>
</div>
