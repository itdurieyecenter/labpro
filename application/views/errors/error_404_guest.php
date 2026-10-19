<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan &bull; Labkes Prov. Riau</title>
    <link rel="stylesheet" href="<?= asset_url('css/tailwind.css') ?>">
    <script src="<?= asset_url('js/lucide.min.js') ?>"></script>
</head>
<body class="h-full flex items-center justify-center p-6 text-slate-800 antialiased font-sans">
    <div class="max-w-md w-full bg-white p-8 rounded-3xl border border-slate-200/90 shadow-2xl text-center space-y-6 relative overflow-hidden">

        <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-gradient-to-tr from-emerald-900 via-emerald-800 to-amber-600 text-amber-300 shadow-xl shadow-emerald-900/30 border border-amber-400/40">
            <i data-lucide="compass" class="w-10 h-10"></i>
        </div>
        <div>
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-rose-50 text-rose-700 border border-rose-200 mb-2">
                404 Not Found
            </span>
            <h1 class="text-2xl font-bold text-slate-900">Halaman Tidak Ditemukan</h1>
            <p class="text-xs text-slate-500 mt-2">Halaman yang Anda tuju tidak tersedia atau telah dipindahkan pada portal SIMRS UPT Labkesda Riau.</p>
        </div>
        <div>
            <a href="<?= site_url('login') ?>" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-semibold shadow-md shadow-emerald-700/25 transition-all border border-emerald-600/40">
                <i data-lucide="log-in" class="w-4 h-4 text-amber-300"></i>
                <span>Masuk ke LIS PRO</span>
            </a>
        </div>
    </div>
    <script>
        if (window.lucide) lucide.createIcons();
    </script>
</body>
</html>
