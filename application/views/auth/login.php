<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= html_escape($title ?? 'Login - LIS PRO UPT Labkes Provinsi Riau') ?></title>
    <link rel="stylesheet" href="<?= asset_url('css/tailwind.css') ?>?v=<?= filemtime(FCPATH . 'assets/css/tailwind.css') ?>">
    <script src="<?= asset_url('js/alpine.min.js') ?>" defer></script>
    <script src="<?= asset_url('js/lucide.min.js') ?>"></script>
    <script src="<?= asset_url('js/app.js') ?>"></script>
    <style>
        .iso-bg {
            background: radial-gradient(circle at 18% 20%, rgba(16, 185, 129, 0.22) 0%, transparent 45%),
                        radial-gradient(circle at 85% 80%, rgba(245, 158, 11, 0.16) 0%, transparent 45%),
                        linear-gradient(135deg, #022c22 0%, #064e3b 52%, #0f172a 100%);
        }
        .iso-grid-pattern {
            background-image: linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
            background-size: 28px 28px;
        }
        .iso-card-3d {
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-bottom: 5px solid #047857;
            border-right: 3px solid #047857;
            border-radius: 20px;
            box-shadow: 0 24px 48px -12px rgba(2, 44, 34, 0.55);
        }
        .iso-showcase-frame {
            border-radius: 20px;
            border: 2px solid rgba(251, 191, 36, 0.45);
            border-bottom: 6px solid #d97706;
            border-right: 4px solid #d97706;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.65);
            transition: transform 0.35s ease, box-shadow 0.35s ease;
        }
        .iso-showcase-frame:hover {
            transform: translate(-3px, -4px);
            box-shadow: 6px 30px 55px -10px rgba(0, 0, 0, 0.75);
        }
        .iso-demo-btn {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-bottom: 3px solid #94a3b8;
            border-right: 2px solid #cbd5e1;
            border-radius: 12px;
            transition: all 0.16s ease;
        }
        .iso-demo-btn:hover {
            background: #ecfdf5;
            border-color: #10b981;
            border-bottom-color: #047857;
            border-right-color: #047857;
            transform: translate(-1px, -2px);
        }
        .iso-demo-btn:active {
            transform: translate(0, 1px);
            border-bottom-width: 1px;
        }
    </style>
</head>
<body class="min-h-screen iso-bg iso-grid-pattern antialiased font-sans flex items-center justify-center p-4 sm:p-6 lg:p-10">

<div class="w-full max-w-6xl grid grid-cols-1 lg:grid-cols-12 gap-8 items-center" x-data="{ username: '', password: '' }">

    <!-- Kolom Kiri: 3D Isometric Showcase LIS PRO -->
    <div class="lg:col-span-7 text-white space-y-5 hidden lg:block pr-4">
        <div class="inline-flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-emerald-950/80 border border-amber-400/40 text-amber-300 text-xs font-bold shadow-lg">
            <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
            <span>UPT LABORATORIUM KESEHATAN PROVINSI RIAU</span>
        </div>

        <h1 class="text-3xl xl:text-4xl font-black tracking-tight leading-tight">
            Sistem Informasi Laboratorium <span class="text-amber-400">Klinis &amp; Lingkungan</span> Terintegrasi
        </h1>
        <p class="text-sm text-emerald-100/85 leading-relaxed max-w-2xl">
            Platform manajemen pengujian laboratorium berstandar akreditasi <strong>ISO 15189 &amp; KAN ISO/IEC 17025</strong> dengan pelacakan spesimen <em>Barcode CODE128</em>, evaluasi baku mutu otomatis, dan pengesahan LHU berbasis QR.
        </p>

        <div class="relative pt-1">
            <img src="<?= asset_url('img/lispro-isometric.jpg') ?>"
                 alt="LIS PRO 3D Isometric Architecture"
                 class="w-full h-[320px] object-cover iso-showcase-frame">
            <div class="grid grid-cols-3 gap-3 mt-4">
                <div class="p-3 rounded-xl bg-emerald-950/75 border border-emerald-700/60 border-b-4 border-b-amber-500 backdrop-blur-sm">
                    <div class="text-[11px] font-bold text-amber-300 uppercase">Lab Klinis &amp; Patologi</div>
                    <div class="text-xs text-emerald-100 mt-0.5">Registrasi, Barcode Tabung, Nilai Kritis &amp; Validasi</div>
                </div>
                <div class="p-3 rounded-xl bg-emerald-950/75 border border-emerald-700/60 border-b-4 border-b-emerald-400 backdrop-blur-sm">
                    <div class="text-[11px] font-bold text-emerald-300 uppercase">Lab Kesling Multi-Media</div>
                    <div class="text-xs text-emerald-100 mt-0.5">Air Minum, Air Bersih, Limbah, Badan Air &amp; Udara</div>
                </div>
                <div class="p-3 rounded-xl bg-emerald-950/75 border border-emerald-700/60 border-b-4 border-b-sky-400 backdrop-blur-sm">
                    <div class="text-[11px] font-bold text-sky-300 uppercase">Manajemen &amp; BLUD</div>
                    <div class="text-xs text-emerald-100 mt-0.5">Gudang FEFO, Aset KIB-B, Kepegawaian &amp; Billing</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: 3D Isometric Tactile Login Card -->
    <div class="lg:col-span-5 w-full max-w-md mx-auto">
        <div class="iso-card-3d p-6 sm:p-7">
            <div class="flex items-center gap-3.5 pb-4 mb-5 border-b border-slate-200">
                <div class="w-13 h-13 p-3 rounded-2xl bg-gradient-to-tr from-emerald-900 via-emerald-700 to-teal-600 text-amber-300 shadow-lg border-b-4 border-emerald-950 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="flask-conical" class="w-7 h-7"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xl font-black text-slate-900 tracking-tight">LIS <span class="text-emerald-700">PRO</span></span>
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-amber-100 text-amber-900 border border-amber-300">RIAU</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">Portal Autentikasi Petugas &amp; Pejabat Lab</p>
                </div>
            </div>

            <?php if ($this->session->flashdata('error')): ?>
                <?= ui_alert($this->session->flashdata('error'), 'danger') ?>
            <?php endif; ?>

            <?php if ($this->session->flashdata('success')): ?>
                <?= ui_alert($this->session->flashdata('success'), 'success') ?>
            <?php endif; ?>

            <form action="<?= site_url('auth/login') ?>" method="POST" class="space-y-3">
                <?= ui_input('username', 'Username Akun', set_value('username'), 'text', [
                    'icon' => 'user',
                    'placeholder' => 'Masukkan username Anda',
                    'required' => 'required',
                    'autofocus' => 'autofocus',
                    'x-model' => 'username',
                    'error' => form_error('username')
                ]) ?>

                <?= ui_input('password', 'Kata Sandi', '', 'password', [
                    'icon' => 'key-round',
                    'placeholder' => 'Masukkan kata sandi',
                    'required' => 'required',
                    'x-model' => 'password',
                    'error' => form_error('password')
                ]) ?>

                <div class="flex items-center justify-between text-xs py-1">
                    <label class="flex items-center gap-2 text-slate-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-emerald-700 focus:ring-emerald-600">
                        <span>Ingat perangkat ini</span>
                    </label>
                    <span class="text-emerald-700 font-semibold">RBAC Terproteksi</span>
                </div>

                <div class="pt-1">
                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-emerald-700 hover:bg-emerald-800 active:translate-y-0.5 text-amber-200 font-bold text-sm flex items-center justify-center gap-2 border border-emerald-600 border-b-4 border-b-emerald-950 shadow-lg transition-all cursor-pointer">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        <span>Masuk ke Sistem LIS PRO</span>
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-4 border-t border-slate-200">
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2.5 text-center">Klik Cepat Akun Demo RBAC (Sandi: password123)</p>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <button type="button" @click="username = 'admin'; password = 'password123'" class="iso-demo-btn p-2.5 text-left cursor-pointer">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-900">admin</span>
                            <?= ui_badge('Admin', 'primary') ?>
                        </div>
                        <span class="text-[10px] text-slate-500 block mt-0.5">Semua Menu &amp; Otoritas</span>
                    </button>

                    <button type="button" @click="username = 'dokter'; password = 'password123'" class="iso-demo-btn p-2.5 text-left cursor-pointer">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-900">dokter</span>
                            <?= ui_badge('Dokter', 'info') ?>
                        </div>
                        <span class="text-[10px] text-slate-500 block mt-0.5">Validasi &amp; Ekspertise</span>
                    </button>

                    <button type="button" @click="username = 'analis'; password = 'password123'" class="iso-demo-btn p-2.5 text-left cursor-pointer">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-900">analis</span>
                            <?= ui_badge('Analis', 'success') ?>
                        </div>
                        <span class="text-[10px] text-slate-500 block mt-0.5">Pemeriksaan &amp; Hasil</span>
                    </button>

                    <button type="button" @click="username = 'petugas'; password = 'password123'" class="iso-demo-btn p-2.5 text-left cursor-pointer">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-900">petugas</span>
                            <?= ui_badge('Petugas', 'warning') ?>
                        </div>
                        <span class="text-[10px] text-slate-500 block mt-0.5">Registrasi &amp; Billing</span>
                    </button>
                </div>
            </div>
        </div>

        <p class="text-center text-xs text-emerald-200/70 mt-4">
            &copy; <?= date('Y') ?> UPT Laboratorium Kesehatan Provinsi Riau &bull; LIS PRO
        </p>
    </div>

</div>

<script>
    if (window.lucide) {
        lucide.createIcons();
    }
</script>
</body>
</html>
