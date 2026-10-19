<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6">
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xl overflow-hidden p-8 sm:p-12 text-center relative">

        <!-- Background soft gradient aura -->
        <div class="absolute -top-24 -left-24 w-72 h-72 bg-emerald-100/50 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-72 h-72 bg-amber-100/50 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 space-y-6">
            <!-- 404 Badge Icon with Lancang Kuning & Emerald Melayu Gradients -->
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-gradient-to-tr from-emerald-900 via-emerald-800 to-amber-600 text-amber-300 shadow-xl shadow-emerald-900/30 border border-amber-400/40">
                <i data-lucide="compass" class="w-10 h-10 animate-spin" style="animation-duration: 12s;"></i>
            </div>

            <!-- Big 404 Header -->
            <div>
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-rose-50 text-rose-700 border border-rose-200 mb-3">
                    Kode Status HTTP 404 &bull; Not Found
                </span>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Halaman / Modul Tidak Ditemukan
                </h1>
                <p class="text-sm text-slate-500 max-w-lg mx-auto mt-2">
                    Menu atau URL yang Anda tuju belum terdaftar pada controller atau sedang dalam tahap integrasi modul SIMRS UPT Labkesda Riau.
                </p>
            </div>

            <!-- Requested URI Box -->
            <div class="max-w-md mx-auto p-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-left flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-100/80 text-emerald-800 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="link" class="w-4 h-4"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Rute yang Diakses:</span>
                    <p class="font-mono text-xs text-emerald-800 font-bold truncate">
                        <?= site_url(html_escape($requested_uri)) ?>
                    </p>
                </div>
            </div>

            <!-- Quick Action Buttons -->
            <div class="pt-4 flex flex-wrap items-center justify-center gap-3">
                <a href="<?= site_url('dashboard') ?>" class="px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-semibold shadow-md shadow-emerald-700/25 transition-all flex items-center gap-2 border border-emerald-600/40">
                    <i data-lucide="layout-dashboard" class="w-4 h-4 text-amber-300"></i>
                    <span>Kembali ke Dashboard</span>
                </a>
                <a href="<?= site_url('menu') ?>" class="px-4 py-2.5 rounded-xl border border-slate-300 hover:border-emerald-300 bg-white hover:bg-emerald-50/50 text-slate-700 text-xs font-semibold shadow-xs transition-colors flex items-center gap-2">
                    <i data-lucide="menu" class="w-4 h-4 text-emerald-700"></i>
                    <span>Buka Manajemen Menu</span>
                </a>
                <a href="<?= site_url('tarif') ?>" class="px-4 py-2.5 rounded-xl border border-slate-300 hover:border-amber-300 bg-white hover:bg-amber-50/50 text-slate-700 text-xs font-semibold shadow-xs transition-colors flex items-center gap-2">
                    <i data-lucide="list-plus" class="w-4 h-4 text-amber-600"></i>
                    <span>Master Tarif & Paket</span>
                </a>
            </div>

            <!-- Helpful Info Card for Administrators -->
            <div class="max-w-xl mx-auto pt-6 text-left border-t border-slate-100 text-xs text-slate-500">
                <div class="flex items-start gap-2.5 p-3 rounded-xl bg-amber-50/80 border border-amber-200 text-amber-900 text-[11px]">
                    <i data-lucide="info" class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5"></i>
                    <div>
                        <strong class="font-semibold block">Petunjuk Konfigurasi SIMRS:</strong>
                        Jika menu ini baru saja ditambahkan di sidebar namun fiturnya belum dibuat, pastikan Anda telah membuat controller dan view yang bersesuaian atau sesuaikan rutenya di <code>application/config/routes.php</code>.
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
