<div class="space-y-5">

    <!-- 1. DASHBOARD HEADER (Clean Melayu Riau Theme) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <span class="text-slate-400">SIMRS Terpadu</span>
                <span>/</span>
                <span class="text-emerald-800 font-semibold">Dashboard Utama</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                </span>
                <span>Selamat Datang, <?= html_escape($user['full_name']) ?></span>
                <span class="hidden sm:inline"><?= ui_badge($user['role_display_name'], ($user['role_name'] === 'admin' ? 'primary' : ($user['role_name'] === 'dokter' ? 'info' : ($user['role_name'] === 'analis' ? 'success' : 'warning')))) ?></span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">UPT Laboratorium Kesehatan Provinsi Riau &bull; Mode Hak Akses RBAC Aktif</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <?= ui_btn('Hak Akses (RBAC)', 'outline-primary', 'sm', ['href' => site_url('dashboard/roles'), 'icon' => 'shield']) ?>
            <?= ui_btn('Logout', 'danger', 'sm', ['href' => site_url('logout'), 'icon' => 'log-out']) ?>
        </div>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <?= ui_alert($this->session->flashdata('success'), 'success') ?>
    <?php endif; ?>

    <!-- 2. SUMMARY STATISTIK CARDS (Melayu Riau Theme) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-center flex-shrink-0">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Pasien Hari Ini</span>
                <span class="text-xl sm:text-2xl font-black text-slate-900 leading-none">42</span>
                <span class="text-[10px] text-emerald-600 font-semibold block mt-0.5">+12% dari kemarin</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 flex items-center justify-center flex-shrink-0">
                <i data-lucide="test-tube-2" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Sampling Tertunda</span>
                <span class="text-xl sm:text-2xl font-black text-slate-900 leading-none">8</span>
                <span class="text-[10px] text-amber-600 font-semibold block mt-0.5">Menunggu antrian</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-teal-50 border border-teal-200 text-teal-800 flex items-center justify-center flex-shrink-0">
                <i data-lucide="flask-conical" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Proses Analisis</span>
                <span class="text-xl sm:text-2xl font-black text-slate-900 leading-none">15</span>
                <span class="text-[10px] text-teal-600 font-semibold block mt-0.5">Di alat analyzer</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 border border-emerald-300 text-emerald-900 flex items-center justify-center flex-shrink-0">
                <i data-lucide="check-circle-2" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Selesai / Otorisasi</span>
                <span class="text-xl sm:text-2xl font-black text-emerald-800 leading-none">28</span>
                <span class="text-[10px] text-emerald-700 font-semibold block mt-0.5">Hasil tervalidasi</span>
            </div>
        </div>
    </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Siap Validasi</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-1">19</h3>
                <span class="text-[11px] text-emerald-600 font-medium flex items-center gap-1 mt-1">
                    <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i> Menunggu dokter Sp.PK
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="file-check" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">

            <?= ui_panel_start('Standar UI Terpadu: Tombol & Warna Seragam', 'palette', ui_badge('Fungsi Helper UI', 'primary')) ?>
            <p class="text-xs text-slate-500 mb-4">Semua tombol dan warna di bawah ini dihasilkan dari fungsi helper <code class="bg-slate-100 px-1.5 py-0.5 rounded text-sky-700 font-mono">ui_btn($text, $variant, $size)</code> sehingga seragam di seluruh modul:</p>
            
            <div class="space-y-4">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Varian Warna Solid (Primary, Success, Danger, Warning, Secondary, Info)</span>
                    <div class="flex flex-wrap gap-2">
                        <?= ui_btn('Simpan Data (Primary)', 'primary', 'md', ['icon' => 'save']) ?>
                        <?= ui_btn('Validasi Hasil (Success)', 'success', 'md', ['icon' => 'check-circle']) ?>
                        <?= ui_btn('Hapus Pasien (Danger)', 'danger', 'md', ['icon' => 'trash-2']) ?>
                        <?= ui_btn('Revisi Order (Warning)', 'warning', 'md', ['icon' => 'alert-circle']) ?>
                        <?= ui_btn('Batal / Netral (Secondary)', 'secondary', 'md', ['icon' => 'x']) ?>
                        <?= ui_btn('Cetak Hasil (Info)', 'info', 'md', ['icon' => 'printer']) ?>
                    </div>
                </div>

                <div class="pt-2">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Varian Outline & Ukuran (sm, md, lg)</span>
                    <div class="flex flex-wrap items-center gap-2">
                        <?= ui_btn('Kecil (sm)', 'outline-primary', 'sm', ['icon' => 'file-text']) ?>
                        <?= ui_btn('Sedang (md)', 'outline-primary', 'md', ['icon' => 'file-text']) ?>
                        <?= ui_btn('Besar (lg)', 'outline-primary', 'lg', ['icon' => 'file-text']) ?>
                        <?= ui_btn('Outline Secondary', 'outline-secondary', 'md', ['icon' => 'refresh-cw']) ?>
                    </div>
                </div>

                <div class="pt-2">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Varian Badge Status Seragam</span>
                    <div class="flex flex-wrap gap-2">
                        <?= ui_badge('Status: Baru', 'primary', 'tag') ?>
                        <?= ui_badge('Status: Selesai Validasi', 'success', 'check') ?>
                        <?= ui_badge('Status: Nilai Kritis', 'danger', 'alert-triangle') ?>
                        <?= ui_badge('Status: Pending Sampling', 'warning', 'clock') ?>
                        <?= ui_badge('Status: Arsip', 'secondary', 'archive') ?>
                        <?= ui_badge('Status: Proses Analisis', 'info', 'activity') ?>
                    </div>
                </div>
            </div>
            <?= ui_panel_end() ?>

            <?= ui_panel_start('Standar Komponen Form Input Terpadu', 'edit-3', ui_badge('ui_input() & ui_select()', 'info')) ?>
            <p class="text-xs text-slate-500 mb-4">Input form otomatis menggunakan standar focus ring, warna border, label, dan ikon:</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?= ui_input('sample_id', 'Nomor Rekam Medis (No RM)', 'RM-2026-00918', 'text', [
                    'icon' => 'hash',
                    'help' => 'ID unik pasien terdaftar di LIS'
                ]) ?>

                <?= ui_select('pemeriksaan', 'Paket Pemeriksaan Laboratorium', [
                    'DL' => 'Darah Lengkap 5 Diff (Hematologi)',
                    'KIMIA' => 'Kimia Darah (SGOT, SGPT, Ureum, Creatinine)',
                    'SERO' => 'Serologi & Imunologi (HBsAg, HIV, NS1)',
                    'URIN' => 'Urin Rutin & Sedimen Mikroskopis'
                ], 'DL', ['help' => 'Pilih paket tes lab']) ?>

                <div class="md:col-span-2">
                    <?= ui_input('dokter_pengirim', 'Dokter Perujuk / Pengirim', 'dr. Anita Wijaya, Sp.PD', 'text', [
                        'icon' => 'stethoscope'
                    ]) ?>
                </div>

                <div class="md:col-span-2">
                    <?= ui_input('catatan_kritis', 'Catatan Klinis / Indikasi', 'Pasien riwayat demam 4 hari, susp. Dengue', 'text', [
                        'icon' => 'clipboard-pen'
                    ]) ?>
                </div>
            </div>
            <?= ui_panel_end() ?>

        </div>

        <div class="space-y-6">

            <?= ui_panel_start('Hak Akses Role Anda', 'lock') ?>
            <div class="space-y-3">
                <div class="p-3 rounded-lg bg-slate-50 border border-slate-200">
                    <span class="text-[11px] text-slate-500 block">Akun Aktif:</span>
                    <strong class="text-sm text-slate-900"><?= html_escape($user['username']) ?></strong>
                    <span class="text-xs text-slate-500 block"><?= html_escape($user['email'] ?? 'Tidak ada email') ?></span>
                </div>

                <div class="p-3 rounded-lg bg-sky-50 border border-sky-200">
                    <span class="text-[11px] text-sky-700 block font-semibold">Role Terdaftar:</span>
                    <span class="text-sm font-bold text-sky-900"><?= html_escape($user['role_display_name']) ?></span>
                </div>

                <div class="pt-2">
                    <span class="text-xs font-semibold text-slate-700 block mb-2">Menu yang diizinkan untuk Role ini:</span>
                    <div class="space-y-1.5 max-h-72 overflow-y-auto pr-1">
                        <?php foreach ($menu_tree as $m): ?>
                            <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-100 text-xs">
                                <span class="flex items-center gap-2 font-medium text-slate-700">
                                    <i data-lucide="<?= html_escape($m['menu_icon']) ?>" class="w-3.5 h-3.5 text-sky-600"></i>
                                    <?= html_escape($m['menu_name']) ?>
                                </span>
                                <?= ui_badge('Akses Diizinkan', 'success') ?>
                            </div>
                            <?php if (!empty($m['children'])): ?>
                                <?php foreach ($m['children'] as $ch): ?>
                                    <div class="flex items-center justify-between p-2 pl-6 rounded-lg bg-white border border-slate-100 text-xs text-slate-600">
                                        <span class="flex items-center gap-2">
                                            <i data-lucide="<?= html_escape($ch['menu_icon']) ?>" class="w-3 h-3 text-slate-400"></i>
                                            <?= html_escape($ch['menu_name']) ?>
                                        </span>
                                        <?= ui_badge('Submenu', 'info') ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?= ui_panel_end() ?>

            <?= ui_panel_start('Simulasi Ganti Role (Testing)', 'users') ?>
            <p class="text-xs text-slate-500 mb-3">Klik tombol role di bawah untuk melihat perbedaan hak akses menu secara langsung:</p>
            <div class="space-y-2">
                <a href="<?= site_url('auth/quick_login/admin') ?>" class="block p-3 rounded-lg border <?= ($user['role_name'] === 'admin') ? 'border-sky-500 bg-sky-50' : 'border-slate-200 hover:bg-slate-50' ?> transition-colors">
                    <div class="flex items-center justify-between">
                        <strong class="text-xs text-slate-900">Administrator</strong>
                        <?= ui_badge('Full Access', 'primary') ?>
                    </div>
                    <span class="text-[11px] text-slate-500 block mt-0.5">Semua menu & modul LIS aktif</span>
                </a>

                <a href="<?= site_url('auth/quick_login/dokter') ?>" class="block p-3 rounded-lg border <?= ($user['role_name'] === 'dokter') ? 'border-sky-500 bg-sky-50' : 'border-slate-200 hover:bg-slate-50' ?> transition-colors">
                    <div class="flex items-center justify-between">
                        <strong class="text-xs text-slate-900">Dokter Sp.PK</strong>
                        <?= ui_badge('Validasi Lab', 'info') ?>
                    </div>
                    <span class="text-[11px] text-slate-500 block mt-0.5">Hanya menu Validasi, Hasil & Laporan</span>
                </a>

                <a href="<?= site_url('auth/quick_login/analis') ?>" class="block p-3 rounded-lg border <?= ($user['role_name'] === 'analis') ? 'border-sky-500 bg-sky-50' : 'border-slate-200 hover:bg-slate-50' ?> transition-colors">
                    <div class="flex items-center justify-between">
                        <strong class="text-xs text-slate-900">Analis Lab</strong>
                        <?= ui_badge('Teknis Lab', 'success') ?>
                    </div>
                    <span class="text-[11px] text-slate-500 block mt-0.5">Order lab, input hasil & pengerjaan sampel</span>
                </a>

                <a href="<?= site_url('auth/quick_login/petugas') ?>" class="block p-3 rounded-lg border <?= ($user['role_name'] === 'registrasi') ? 'border-sky-500 bg-sky-50' : 'border-slate-200 hover:bg-slate-50' ?> transition-colors">
                    <div class="flex items-center justify-between">
                        <strong class="text-xs text-slate-900">Petugas Registrasi</strong>
                        <?= ui_badge('Front Office', 'warning') ?>
                    </div>
                    <span class="text-[11px] text-slate-500 block mt-0.5">Registrasi pasien & order pemeriksaan</span>
                </a>
            </div>
            <?= ui_panel_end() ?>

        </div>

    </div>

</div>
