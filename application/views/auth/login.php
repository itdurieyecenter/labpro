<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= html_escape($title ?? 'Login - LIS PRO') ?></title>
    <link rel="stylesheet" href="<?= asset_url('css/tailwind.css') ?>">
    <script src="<?= asset_url('js/alpine.min.js') ?>" defer></script>
    <script src="<?= asset_url('js/lucide.min.js') ?>"></script>
    <script src="<?= asset_url('js/app.js') ?>"></script>
</head>
<body class="h-full antialiased font-sans flex items-center justify-center p-4">

<div class="w-full max-w-md" x-data="{ username: '', password: '' }">
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-sky-600 to-cyan-500 text-white shadow-xl shadow-sky-600/30 mb-3">
            <i data-lucide="flask-conical" class="w-9 h-9"></i>
        </div>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">LIS <span class="text-sky-600">PRO</span></h1>
        <p class="text-xs text-slate-500 mt-1">Laboratory Information System &bull; RBAC Module</p>
    </div>

    <?php if ($this->session->flashdata('error')): ?>
        <?= ui_alert($this->session->flashdata('error'), 'danger') ?>
    <?php endif; ?>

    <?php if ($this->session->flashdata('success')): ?>
        <?= ui_alert($this->session->flashdata('success'), 'success') ?>
    <?php endif; ?>

    <?= ui_panel_start('Autentikasi Pengguna', 'lock') ?>

    <form action="<?= site_url('auth/login') ?>" method="POST" class="space-y-1">
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

        <div class="flex items-center justify-between text-xs py-2">
            <label class="flex items-center gap-2 text-slate-600 cursor-pointer">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                <span>Ingat saya di perangkat ini</span>
            </label>
            <span class="text-sky-600 font-medium">Bantuan Admin</span>
        </div>

        <div class="pt-2">
            <?= ui_btn('Masuk ke Sistem LIS', 'primary', 'lg', [
                'type' => 'submit',
                'icon' => 'log-in',
                'class' => 'w-full'
            ]) ?>
        </div>
    </form>

    <div class="mt-6 pt-5 border-t border-slate-100">
        <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-2.5 text-center">Pilih Akun Demo RBAC (Password: password123)</p>
        <div class="grid grid-cols-2 gap-2 text-xs">
            <button type="button" @click="username = 'admin'; password = 'password123'" class="p-2.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-sky-50 hover:border-sky-300 text-left transition-all">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-800">admin</span>
                    <?= ui_badge('Admin', 'primary') ?>
                </div>
                <span class="text-[10px] text-slate-500 block mt-0.5">Semua Menu & Otoritas</span>
            </button>

            <button type="button" @click="username = 'dokter'; password = 'password123'" class="p-2.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-sky-50 hover:border-sky-300 text-left transition-all">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-800">dokter</span>
                    <?= ui_badge('Dokter', 'info') ?>
                </div>
                <span class="text-[10px] text-slate-500 block mt-0.5">Validasi & Ekspertise</span>
            </button>

            <button type="button" @click="username = 'analis'; password = 'password123'" class="p-2.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-sky-50 hover:border-sky-300 text-left transition-all">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-800">analis</span>
                    <?= ui_badge('Analis', 'success') ?>
                </div>
                <span class="text-[10px] text-slate-500 block mt-0.5">Pemeriksaan & Hasil</span>
            </button>

            <button type="button" @click="username = 'petugas'; password = 'password123'" class="p-2.5 rounded-lg border border-slate-200 bg-slate-50 hover:bg-sky-50 hover:border-sky-300 text-left transition-all">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-800">petugas</span>
                    <?= ui_badge('Petugas', 'warning') ?>
                </div>
                <span class="text-[10px] text-slate-500 block mt-0.5">Registrasi & Billing</span>
            </button>
        </div>
    </div>

    <?= ui_panel_end() ?>

    <p class="text-center text-xs text-slate-400 mt-6">
        LIS PRO &bull; PHP 7.4 &bull; CodeIgniter 3 &bull; MariaDB/MySQL
    </p>
</div>

<script>
    if (window.lucide) {
        lucide.createIcons();
    }
</script>
</body>
</html>
