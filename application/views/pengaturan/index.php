<?php
$kop_info = kop_surat_data();
?>
<div class="flex-1 bg-slate-50 min-h-screen" x-data="{ 
    activeTab: 'aplikasi',
    previewInstansi: '<?= html_escape($kop_info['instansi']) ?>',
    previewInduk: '<?= html_escape($kop_info['induk']) ?>',
    previewLine1: '<?= html_escape($kop_info['line1']) ?>',
    previewLine2: '<?= html_escape($kop_info['line2']) ?>',
    previewLine3: '<?= html_escape($kop_info['line3']) ?>',
    previewAlamat: '<?= html_escape($kop_info['alamat']) ?>',
    previewKontak: 'Telp: <?= html_escape($kop_info['telepon']) ?> &bull; WA: <?= html_escape($kop_info['whatsapp']) ?> &bull; Email: <?= html_escape($kop_info['email']) ?>',
    previewLogoInstansi: '<?= html_escape($kop_info['logo_instansi_url']) ?>',
    previewLogoKan: '<?= html_escape($kop_info['logo_kan_url']) ?>',
    hasCustomInstansi: <?= $kop_info['has_custom_instansi'] ? 'true' : 'false' ?>,
    hasCustomKan: <?= $kop_info['has_custom_kan'] ? 'true' : 'false' ?>,
    uploadingInstansi: false,
    uploadingKan: false,
    showAiKey: false,
    showSecretKey: false
}">

    <!-- Page Header (Melayu Riau Gradient Accent) -->
    <div class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-800 via-emerald-700 to-amber-500 flex items-center justify-center text-amber-200 shadow-md shadow-emerald-950/20 border border-amber-400/30 flex-shrink-0">
                        <i data-lucide="sliders-horizontal" class="w-6 h-6 text-amber-200"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Pengaturan Sistem &amp; Profil Faskes</h1>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">SIMRS Terintegrasi</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Konfigurasi identitas laboratorium, logo kop dokumen resmi, kecerdasan buatan (AI), dan bridging interoperabilitas alat lab</p>
                    </div>
                </div>

                <!-- Status Badge Identitas Faskes -->
                <div class="flex items-center gap-2">
                    <div class="bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2 flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 ring-4 ring-emerald-100 animate-pulse"></span>
                        <div class="text-left">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block leading-tight">Database Faskes</span>
                            <span class="text-xs font-bold text-slate-700 font-mono"><?= html_escape($app['singkatan_aplikasi'] ?? 'LIS PRO RIAU') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB NAVIGATION TABS (Aplikasi, AI, Bridging LIS) -->
            <div class="flex items-center gap-2 mt-6 border-b border-slate-200 -mb-px overflow-x-auto">
                <!-- Tab 1: Pengaturan Aplikasi -->
                <button type="button" 
                        @click="activeTab = 'aplikasi'; $nextTick(() => { if (window.lucide) lucide.createIcons(); });" 
                        class="px-4 py-3 text-xs font-bold border-b-2 flex items-center gap-2.5 transition-all whitespace-nowrap cursor-pointer"
                        :class="activeTab === 'aplikasi' 
                            ? 'border-emerald-700 text-emerald-900 bg-emerald-50/50 rounded-t-xl font-extrabold' 
                            : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300'">
                    <i data-lucide="building-2" class="w-4 h-4" :class="activeTab === 'aplikasi' ? 'text-emerald-700' : 'text-slate-400'"></i>
                    <span>Pengaturan Aplikasi, Logo &amp; Profil Lab</span>
                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold"
                          :class="activeTab === 'aplikasi' ? 'bg-emerald-700 text-amber-200' : 'bg-slate-100 text-slate-500'">
                        Utama
                    </span>
                </button>

                <!-- Tab 2: AI (Kecerdasan Buatan) -->
                <button type="button" 
                        @click="activeTab = 'ai'; $nextTick(() => { if (window.lucide) lucide.createIcons(); });" 
                        class="px-4 py-3 text-xs font-bold border-b-2 flex items-center gap-2.5 transition-all whitespace-nowrap cursor-pointer"
                        :class="activeTab === 'ai' 
                            ? 'border-purple-600 text-purple-950 bg-purple-50/50 rounded-t-xl font-extrabold' 
                            : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300'">
                    <i data-lucide="sparkles" class="w-4 h-4" :class="activeTab === 'ai' ? 'text-purple-600' : 'text-slate-400'"></i>
                    <span>Kecerdasan Buatan (AI)</span>
                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold"
                          :class="activeTab === 'ai' ? 'bg-purple-700 text-white' : 'bg-slate-100 text-slate-500'">
                        Smart LIS
                    </span>
                </button>

                <!-- Tab 3: Bridging LIS -->
                <button type="button" 
                        @click="activeTab = 'bridging'; $nextTick(() => { if (window.lucide) lucide.createIcons(); });" 
                        class="px-4 py-3 text-xs font-bold border-b-2 flex items-center gap-2.5 transition-all whitespace-nowrap cursor-pointer"
                        :class="activeTab === 'bridging' 
                            ? 'border-teal-600 text-teal-950 bg-teal-50/50 rounded-t-xl font-extrabold' 
                            : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300'">
                    <i data-lucide="network" class="w-4 h-4" :class="activeTab === 'bridging' ? 'text-teal-700' : 'text-slate-400'"></i>
                    <span>Bridging LIS &amp; Alat Lab</span>
                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold"
                          :class="activeTab === 'bridging' ? 'bg-teal-700 text-amber-200' : 'bg-slate-100 text-slate-500'">
                        Interoperabilitas
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- MAIN CONTAINER -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        <!-- ========================================================================= -->
        <!-- TAB 1: PENGATURAN APLIKASI & PROFIL LABORATORIUM                          -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'aplikasi'" x-cloak class="space-y-6">

            <!-- LIVE PREVIEW KOP DOKUMEN & BRANDING -->
            <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 text-white rounded-2xl p-4 sm:p-5 border border-slate-700/70 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-700/80 pb-3 mb-3">
                    <div class="flex items-center gap-2">
                        <i data-lucide="file-check" class="w-4 h-4 text-amber-300"></i>
                        <span class="text-xs font-bold tracking-wide uppercase text-white">Live Preview Kop Dokumen Resmi, Nota &amp; Surat SIMRS</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="<?= site_url('pengaturan/preview_kop') ?>" target="_blank" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-[11px] rounded-lg transition-all flex items-center gap-1.5 shadow-sm cursor-pointer">
                            <i data-lucide="printer" class="w-3.5 h-3.5 text-slate-950"></i>
                            <span>Uji Cetak Kop Dokumen</span>
                        </a>
                        <span class="text-[10px] text-emerald-300 font-semibold bg-emerald-500/20 px-2 py-0.5 rounded border border-emerald-400/30">
                            Sinkron Otomatis
                        </span>
                    </div>
                </div>

                <div class="bg-white text-slate-900 rounded-xl p-4 sm:p-5 shadow-inner border border-slate-200">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 border-b-2 border-slate-900 pb-3">
                        <!-- Logo Kiri -->
                        <div class="w-16 h-16 rounded-xl bg-slate-50 flex items-center justify-center p-1.5 shadow-xs border border-slate-200 flex-shrink-0">
                            <img :src="previewLogoInstansi" alt="Logo Instansi" class="max-w-full max-h-full object-contain">
                        </div>
                        <!-- Teks Tengah -->
                        <div class="text-center flex-1">
                            <h4 class="text-[11px] font-bold text-slate-600 tracking-wider uppercase leading-tight" x-text="previewLine1 || previewInduk">PEMERINTAH PROVINSI RIAU</h4>
                            <h3 class="text-xs font-extrabold text-slate-800 uppercase tracking-wide leading-tight mt-0.5" x-text="previewLine2">DINAS KESEHATAN</h3>
                            <h2 class="text-sm sm:text-base font-black text-emerald-950 uppercase tracking-wide leading-snug mt-0.5" x-text="previewLine3 || previewInstansi">UPT LABORATORIUM KESEHATAN DAN LINGKUNGAN</h2>
                            <p class="text-[11px] text-slate-600 leading-tight mt-1" x-text="previewAlamat">Jl. Cut Nyak Dien No. 01, Kel. Sukajadi, Kota Pekanbaru, Riau - 28121</p>
                            <p class="text-[10px] text-slate-500 font-mono mt-0.5" x-html="previewKontak">Telp: (0761) 23456 &bull; WA: 0812-7567-8901 &bull; Email: labkes@riau.go.id</p>
                        </div>
                        <!-- Logo Kanan -->
                        <div class="w-16 h-16 rounded-xl bg-slate-50 flex items-center justify-center p-1.5 shadow-xs border border-slate-200 flex-shrink-0">
                            <img :src="previewLogoKan" alt="Logo KAN" class="max-w-full max-h-full object-contain">
                        </div>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-[9px] text-slate-400 font-mono">
                        <span>Standar Rekam Medis Elektronik Terintegrasi SIMRS LIS PRO</span>
                        <span>Nomor Registrasi Faskes: <?= html_escape($app['nomor_izin_lab'] ?? '445/DINKES-LAB/2024/008') ?></span>
                    </div>
                </div>
            </div>

            <!-- KARTU UPLOAD & KELOLA LOGO RESMI (INSTANSI & KAN) -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="px-5 py-4 bg-slate-50/90 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 rounded-lg bg-emerald-700 text-amber-300 text-xs font-bold flex items-center justify-center">1</span>
                        <h3 class="text-xs sm:text-sm font-bold text-slate-800 tracking-wide uppercase">Logo &amp; Atribut Kop Dokumen (Letterhead Standard)</h3>
                    </div>
                    <span class="text-[10px] text-emerald-800 font-semibold bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                        Header Otomatis di Seluruh Nota, Kuitansi &amp; PDF
                    </span>
                </div>
                <div class="p-5">
                    <p class="text-xs text-slate-500 mb-5">
                        Logo yang diunggah di bawah ini akan otomatis menjadi <strong>kop surat seragam</strong> pada seluruh modul: <strong>kuitansi pembayaran kasir BLUD, nota rincian biaya, formulir permintaan uji lab, barcode spesimen, lembar hasil pengujian laboratorium (LHU), dan surat resmi</strong>.
                    </p>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- 1. LOGO UTAMA INSTANSI (KIRI KOP) -->
                        <div class="bg-slate-50/70 border border-slate-200 rounded-xl p-4 flex flex-col sm:flex-row gap-4 items-center sm:items-start relative group hover:border-emerald-300 transition-all">
                            <div class="w-24 h-24 rounded-xl bg-white border border-slate-200 p-2 flex items-center justify-center shadow-sm flex-shrink-0 relative overflow-hidden">
                                <img :src="previewLogoInstansi" alt="Logo Instansi" id="img-preview-instansi" class="max-w-full max-h-full object-contain">
                                <div x-show="uploadingInstansi" class="absolute inset-0 bg-white/80 backdrop-blur-xs flex items-center justify-center">
                                    <span class="animate-spin text-emerald-700 text-xl font-bold">⏳</span>
                                </div>
                            </div>
                            <div class="flex-1 text-center sm:text-left space-y-2">
                                <div class="flex items-center justify-center sm:justify-start gap-2">
                                    <h4 class="text-xs font-bold text-slate-900">Logo Instansi / Faskes (Kiri Kop)</h4>
                                    <template x-if="hasCustomInstansi">
                                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">Kustom</span>
                                    </template>
                                    <template x-if="!hasCustomInstansi">
                                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-slate-200 text-slate-600">Default Labkes</span>
                                    </template>
                                </div>
                                <p class="text-[11px] text-slate-500 leading-relaxed">
                                    Logo resmi pemerintah daerah atau instansi faskes laboratorium. Tampil pada pojok kiri atas kop seluruh dokumen.
                                </p>
                                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 pt-1">
                                    <label class="px-3 py-1.5 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-semibold cursor-pointer shadow-xs transition-all flex items-center gap-1.5 border border-emerald-600">
                                        <i data-lucide="upload-cloud" class="w-3.5 h-3.5"></i>
                                        <span>Unggah Logo Instansi</span>
                                        <input type="file" id="file-logo-instansi" accept="image/png,image/jpeg,image/webp,image/svg+xml" class="hidden" onchange="uploadLogoFile('logo_instansi', this)">
                                    </label>
                                    <button type="button" x-show="hasCustomInstansi" @click="removeLogoFile('logo_instansi')" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold transition-all flex items-center gap-1 cursor-pointer">
                                        <i data-lucide="trash-2" class="w-3 h-3"></i>
                                        <span>Reset Default</span>
                                    </button>
                                </div>
                                <span class="text-[10px] text-slate-400 block font-mono">Format: PNG Transparan, SVG, JPG, WebP (Maks. 3 MB)</span>
                            </div>
                        </div>

                        <!-- 2. LOGO AKREDITASI KAN / ISO (KANAN KOP) -->
                        <div class="bg-slate-50/70 border border-slate-200 rounded-xl p-4 flex flex-col sm:flex-row gap-4 items-center sm:items-start relative group hover:border-emerald-300 transition-all">
                            <div class="w-24 h-24 rounded-xl bg-white border border-slate-200 p-2 flex items-center justify-center shadow-sm flex-shrink-0 relative overflow-hidden">
                                <img :src="previewLogoKan" alt="Logo KAN" id="img-preview-kan" class="max-w-full max-h-full object-contain">
                                <div x-show="uploadingKan" class="absolute inset-0 bg-white/80 backdrop-blur-xs flex items-center justify-center">
                                    <span class="animate-spin text-emerald-700 text-xl font-bold">⏳</span>
                                </div>
                            </div>
                            <div class="flex-1 text-center sm:text-left space-y-2">
                                <div class="flex items-center justify-center sm:justify-start gap-2">
                                    <h4 class="text-xs font-bold text-slate-900">Logo Akreditasi KAN / ISO (Kanan Kop)</h4>
                                    <template x-if="hasCustomKan">
                                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">Kustom</span>
                                    </template>
                                    <template x-if="!hasCustomKan">
                                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-slate-200 text-slate-600">Default KAN</span>
                                    </template>
                                </div>
                                <p class="text-[11px] text-slate-500 leading-relaxed">
                                    Logo Komite Akreditasi Nasional (LP-891-IDN), ISO 15189 / 17025, atau Germas. Tampil pada pojok kanan atas kop.
                                </p>
                                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 pt-1">
                                    <label class="px-3 py-1.5 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-semibold cursor-pointer shadow-xs transition-all flex items-center gap-1.5 border border-emerald-600">
                                        <i data-lucide="upload-cloud" class="w-3.5 h-3.5"></i>
                                        <span>Unggah Logo KAN</span>
                                        <input type="file" id="file-logo-kan" accept="image/png,image/jpeg,image/webp,image/svg+xml" class="hidden" onchange="uploadLogoFile('logo_kan', this)">
                                    </label>
                                    <button type="button" x-show="hasCustomKan" @click="removeLogoFile('logo_kan')" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold transition-all flex items-center gap-1 cursor-pointer">
                                        <i data-lucide="trash-2" class="w-3 h-3"></i>
                                        <span>Reset Default</span>
                                    </button>
                                </div>
                                <span class="text-[10px] text-slate-400 block font-mono">Format: PNG Transparan, SVG, JPG, WebP (Maks. 3 MB)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FORM ISIAN LENGKAP PENGATURAN APLIKASI -->
            <form id="form-pengaturan-aplikasi" onsubmit="savePengaturanAplikasi(event)" class="space-y-6">

                <!-- 2. IDENTITAS & BRANDING INSTANSI -->
                <div class="lis-section">
                    <div class="lis-section-header bg-slate-100/90">
                        <div class="lis-section-title">
                            <span class="lis-section-num bg-emerald-700 text-amber-300">2</span>
                            <span>Identitas &amp; Nama Instansi Laboratorium</span>
                        </div>
                        <span class="text-[10px] text-emerald-800 font-semibold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Nama &amp; Perizinan</span>
                    </div>
                    <div class="lis-section-body space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="lis-form-label">Nama Aplikasi LIS <span class="req">*</span></label>
                                <input type="text" name="nama_aplikasi" id="app-nama-aplikasi" value="<?= html_escape($app['nama_aplikasi'] ?? '') ?>" class="lis-form-control font-bold text-slate-900" required placeholder="Contoh: LIS PRO - Laboratory Information System">
                                <span class="lis-form-hint">Nama sistem utama yang ditampilkan pada judul halaman dan dokumen</span>
                            </div>
                            <div>
                                <label class="lis-form-label">Singkatan / Brand Singkat <span class="req">*</span></label>
                                <input type="text" name="singkatan_aplikasi" id="app-singkatan-aplikasi" value="<?= html_escape($app['singkatan_aplikasi'] ?? '') ?>" class="lis-form-control font-bold uppercase tracking-wider" required placeholder="Contoh: LIS PRO LABKES RIAU">
                                <span class="lis-form-hint">Nama ringkas untuk sidebar, favicon, dan watermark cetak</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="lis-form-label">Nama Resmi Instansi Laboratorium <span class="req">*</span></label>
                                <input type="text" name="nama_instansi" id="app-nama-instansi" value="<?= html_escape($app['nama_instansi'] ?? '') ?>" 
                                       @input="previewInstansi = $event.target.value" 
                                       class="lis-form-control font-bold text-emerald-950" required placeholder="UPT Laboratorium Kesehatan & Lingkungan Provinsi Riau">
                                <span class="lis-form-hint">Nama unit kerja resmi faskes pada header dokumen</span>
                            </div>
                            <div>
                                <label class="lis-form-label">Instansi Induk / Pemerintah <span class="req">*</span></label>
                                <input type="text" name="instansi_induk" id="app-instansi-induk" value="<?= html_escape($app['instansi_induk'] ?? '') ?>" 
                                       @input="previewInduk = $event.target.value" 
                                       class="lis-form-control font-semibold" required placeholder="Dinas Kesehatan Provinsi Riau">
                                <span class="lis-form-hint">Dinas pengampu atau pemerintah daerah pengelola BLUD</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="lis-form-label">Slogan / Motto Pelayanan</label>
                                <input type="text" name="slogan_aplikasi" id="app-slogan-aplikasi" value="<?= html_escape($app['slogan_aplikasi'] ?? '') ?>" class="lis-form-control italic" placeholder="Akurat, Cepat, Terpercaya...">
                            </div>
                            <div>
                                <label class="lis-form-label">No. Izin Operasional Lab / Registrasi</label>
                                <input type="text" name="nomor_izin_lab" id="app-nomor-izin-lab" value="<?= html_escape($app['nomor_izin_lab'] ?? '') ?>" class="lis-form-control font-mono" placeholder="445/DINKES-LAB/2024/008">
                            </div>
                            <div>
                                <label class="lis-form-label">Tipe &amp; Kualifikasi Laboratorium</label>
                                <select name="tipe_laboratorium" id="app-tipe-laboratorium" class="lis-form-control font-medium">
                                    <?php 
                                    $tipe_curr = $app['tipe_laboratorium'] ?? 'Laboratorium Kesehatan Daerah (Labkesda BLUD)';
                                    $options_tipe = [
                                        'Laboratorium Kesehatan Daerah (Labkesda BLUD)',
                                        'Laboratorium Rumah Sakit Pemerintah (RSUD)',
                                        'Laboratorium Rumah Sakit Swasta',
                                        'Laboratorium Klinik Pratama Mandiri',
                                        'Laboratorium Lingkungan & Kalibrasi Terakreditasi',
                                        'Balai Besar Laboratorium Kesehatan (BBLK)'
                                    ];
                                    foreach ($options_tipe as $opt):
                                    ?>
                                        <option value="<?= html_escape($opt) ?>" <?= ($tipe_curr === $opt) ? 'selected' : '' ?>><?= html_escape($opt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. ALAMAT & KONTAK RESMI -->
                <div class="lis-section">
                    <div class="lis-section-header bg-slate-100/90">
                        <div class="lis-section-title">
                            <span class="lis-section-num bg-emerald-700 text-amber-300">3</span>
                            <span>Alamat Lengkap &amp; Kontak Resmi Faskes</span>
                        </div>
                        <span class="text-[10px] text-slate-500 font-semibold">Komunikasi &amp; Lokasi</span>
                    </div>
                    <div class="lis-section-body space-y-4">
                        <div>
                            <label class="lis-form-label">Alamat Lengkap Kantor / Gedung Laboratorium <span class="req">*</span></label>
                            <textarea name="alamat_lengkap" id="app-alamat-lengkap" rows="2" 
                                      @input="previewAlamat = $event.target.value" 
                                      class="lis-form-control" required placeholder="Jl. Cut Nyak Dien No. 01, Kel. Sukajadi..."><?= html_escape($app['alamat_lengkap'] ?? '') ?></textarea>
                            <span class="lis-form-hint">Alamat fisik operasional yang dicantumkan pada kop surat dan kuitansi</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="lis-form-label">Kota / Kabupaten <span class="req">*</span></label>
                                <input type="text" name="kota_kabupaten" id="app-kota-kabupaten" value="<?= html_escape($app['kota_kabupaten'] ?? '') ?>" class="lis-form-control" required placeholder="Kota Pekanbaru">
                            </div>
                            <div>
                                <label class="lis-form-label">Provinsi <span class="req">*</span></label>
                                <input type="text" name="provinsi" id="app-provinsi" value="<?= html_escape($app['provinsi'] ?? '') ?>" class="lis-form-control font-semibold" required placeholder="Riau">
                            </div>
                            <div>
                                <label class="lis-form-label">Kode Pos</label>
                                <input type="text" name="kode_pos" id="app-kode-pos" value="<?= html_escape($app['kode_pos'] ?? '') ?>" class="lis-form-control font-mono" placeholder="28121">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="lis-form-label">Nomor Telepon Kantor</label>
                                <div class="relative">
                                    <input type="text" name="telepon" id="app-telepon" value="<?= html_escape($app['telepon'] ?? '') ?>" @input="updateContactPreview()" class="lis-form-control pl-8 font-mono" placeholder="(0761) 23456">
                                    <i data-lucide="phone" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-3 pointer-events-none"></i>
                                </div>
                            </div>
                            <div>
                                <label class="lis-form-label">WhatsApp Layanan / CS <span class="req">*</span></label>
                                <div class="relative">
                                    <input type="text" name="whatsapp_layanan" id="app-whatsapp-layanan" value="<?= html_escape($app['whatsapp_layanan'] ?? '') ?>" @input="updateContactPreview()" class="lis-form-control pl-8 font-mono font-bold text-emerald-800" required placeholder="0812-7567-8901">
                                    <i data-lucide="message-square" class="w-3.5 h-3.5 text-emerald-600 absolute left-2.5 top-3 pointer-events-none"></i>
                                </div>
                            </div>
                            <div>
                                <label class="lis-form-label">Email Resmi Faskes <span class="req">*</span></label>
                                <div class="relative">
                                    <input type="email" name="email_resmi" id="app-email-resmi" value="<?= html_escape($app['email_resmi'] ?? '') ?>" @input="updateContactPreview()" class="lis-form-control pl-8" required placeholder="labkes@riau.go.id">
                                    <i data-lucide="mail" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-3 pointer-events-none"></i>
                                </div>
                            </div>
                            <div>
                                <label class="lis-form-label">Website Resmi Faskes</label>
                                <div class="relative">
                                    <input type="url" name="website_resmi" id="app-website-resmi" value="<?= html_escape($app['website_resmi'] ?? '') ?>" class="lis-form-control pl-8" placeholder="https://labkesda.riau.go.id">
                                    <i data-lucide="globe" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-3 pointer-events-none"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. PEJABAT & PENANGGUNG JAWAB LABORATORIUM -->
                <div class="lis-section">
                    <div class="lis-section-header bg-slate-100/90">
                        <div class="lis-section-title">
                            <span class="lis-section-num bg-emerald-700 text-amber-300">4</span>
                            <span>Pejabat &amp; Penanggung Jawab Medis Laboratorium</span>
                        </div>
                        <span class="text-[10px] text-amber-800 font-semibold bg-amber-50 px-2 py-0.5 rounded border border-amber-200">Legalitas Tanda Tangan Hasil</span>
                    </div>
                    <div class="lis-section-body space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="lis-form-label">Nama Kepala Laboratorium / Pimpinan <span class="req">*</span></label>
                                <input type="text" name="kepala_lab" id="app-kepala-lab" value="<?= html_escape($app['kepala_lab'] ?? '') ?>" class="lis-form-control font-bold" required placeholder="dr. H. Hendra Saputra, Sp.PK">
                                <span class="lis-form-hint">Nama lengkap beserta gelar pimpinan instansi</span>
                            </div>
                            <div>
                                <label class="lis-form-label">NIP Kepala Laboratorium</label>
                                <input type="text" name="nip_kepala_lab" id="app-nip-kepala-lab" value="<?= html_escape($app['nip_kepala_lab'] ?? '') ?>" class="lis-form-control font-mono" placeholder="19780512 200501 1 004">
                            </div>
                            <div>
                                <label class="lis-form-label">Pangkat / Golongan Ruang</label>
                                <input type="text" name="pangkat_kepala_lab" id="app-pangkat-kepala-lab" value="<?= html_escape($app['pangkat_kepala_lab'] ?? '') ?>" class="lis-form-control" placeholder="Pembina (Gol. IV/a)">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="lis-form-label">Dokter Penanggung Jawab Teknis (Sp.PK) <span class="req">*</span></label>
                                <input type="text" name="dokter_penanggung_jawab" id="app-dokter-penanggung-jawab" value="<?= html_escape($app['dokter_penanggung_jawab'] ?? '') ?>" class="lis-form-control font-bold text-slate-900" required placeholder="dr. Hj. Siti Rahmah, Sp.PK, M.Kes">
                                <span class="lis-form-hint">Dokter penanggung jawab validasi lembar hasil laboratorium</span>
                            </div>
                            <div>
                                <label class="lis-form-label">SIP Dokter Penanggung Jawab <span class="req">*</span></label>
                                <input type="text" name="sip_dokter" id="app-sip-dokter" value="<?= html_escape($app['sip_dokter'] ?? '') ?>" class="lis-form-control font-mono" required placeholder="503/SIP-D/2023/0421">
                                <span class="lis-form-hint">Nomor Surat Izin Praktik (SIP) dokter spesialis</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="lis-form-label">Kepala Seksi Pelayanan &amp; Mutu</label>
                                <input type="text" name="kasi_pelayanan" id="app-kasi-pelayanan" value="<?= html_escape($app['kasi_pelayanan'] ?? '') ?>" class="lis-form-control" placeholder="Ahmad Zulkarnain, S.Tr.Kes">
                            </div>
                            <div>
                                <label class="lis-form-label">NIP Kasi Pelayanan</label>
                                <input type="text" name="nip_kasi_pelayanan" id="app-nip-kasi-pelayanan" value="<?= html_escape($app['nip_kasi_pelayanan'] ?? '') ?>" class="lis-form-control font-mono" placeholder="19850314 200902 1 002">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. FORMAT KOP SURAT CETAK & BILLING BLUD -->
                <div class="lis-section">
                    <div class="lis-section-header bg-slate-100/90">
                        <div class="lis-section-title">
                            <span class="lis-section-num bg-emerald-700 text-amber-300">5</span>
                            <span>Format Kop Dokumen Cetak &amp; Kebijakan Billing BLUD</span>
                        </div>
                        <span class="text-[10px] text-teal-800 font-semibold bg-teal-50 px-2 py-0.5 rounded border border-teal-200">Kuitansi &amp; Hasil Uji</span>
                    </div>
                    <div class="lis-section-body space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="lis-form-label">Kop Dokumen Baris 1</label>
                                <input type="text" name="kop_header_line1" id="app-kop-line1" value="<?= html_escape($app['kop_header_line1'] ?? 'PEMERINTAH PROVINSI RIAU') ?>" @input="previewLine1 = $event.target.value" class="lis-form-control font-bold uppercase text-xs" placeholder="PEMERINTAH PROVINSI RIAU">
                            </div>
                            <div>
                                <label class="lis-form-label">Kop Dokumen Baris 2</label>
                                <input type="text" name="kop_header_line2" id="app-kop-line2" value="<?= html_escape($app['kop_header_line2'] ?? 'DINAS KESEHATAN') ?>" @input="previewLine2 = $event.target.value" class="lis-form-control font-bold uppercase text-xs" placeholder="DINAS KESEHATAN">
                            </div>
                            <div>
                                <label class="lis-form-label">Kop Dokumen Baris 3</label>
                                <input type="text" name="kop_header_line3" id="app-kop-line3" value="<?= html_escape($app['kop_header_line3'] ?? 'UPT LABORATORIUM KESEHATAN DAN LINGKUNGAN') ?>" @input="previewLine3 = $event.target.value" class="lis-form-control font-bold uppercase text-xs text-emerald-950" placeholder="UPT LABORATORIUM KESEHATAN DAN LINGKUNGAN">
                            </div>
                        </div>

                        <div>
                            <label class="lis-form-label">Catatan Kaki (Footer Disclaimer Hasil Uji &amp; Akreditasi)</label>
                            <textarea name="kop_footer_note" id="app-kop-footer-note" rows="2" class="lis-form-control text-xs" placeholder="Hasil laboratorium ini telah divalidasi secara elektronik..."><?= html_escape($app['kop_footer_note'] ?? '') ?></textarea>
                            <span class="lis-form-hint">Dicantumkan pada bagian bawah lembar hasil pemeriksaan dan kuitansi pembayaran</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="lis-form-label">Masa Berlaku Hasil Uji (Hari)</label>
                                <div class="relative">
                                    <input type="number" name="masa_berlaku_hasil" id="app-masa-berlaku-hasil" value="<?= html_escape($app['masa_berlaku_hasil'] ?? '30') ?>" min="1" max="365" class="lis-form-control font-mono font-bold pr-12 text-right">
                                    <span class="absolute right-3 top-2.5 text-xs text-slate-400 font-medium">Hari</span>
                                </div>
                            </div>
                            <div>
                                <label class="lis-form-label">Biaya Administrasi Default (Rp)</label>
                                <div class="relative">
                                    <input type="number" name="biaya_administrasi_default" id="app-biaya-admin" value="<?= html_escape($app['biaya_administrasi_default'] ?? '15000') ?>" min="0" step="500" class="lis-form-control font-mono font-bold pl-8 text-emerald-900">
                                    <span class="absolute left-3 top-2.5 text-xs text-slate-400 font-bold">Rp</span>
                                </div>
                            </div>
                            <div>
                                <label class="lis-form-label">Zona Waktu Server</label>
                                <select name="zona_waktu" id="app-zona-waktu" class="lis-form-control font-mono">
                                    <option value="Asia/Jakarta" <?= (($app['zona_waktu'] ?? '') === 'Asia/Jakarta') ? 'selected' : '' ?>>Asia/Jakarta (WIB - GMT+7)</option>
                                    <option value="Asia/Makassar" <?= (($app['zona_waktu'] ?? '') === 'Asia/Makassar') ? 'selected' : '' ?>>Asia/Makassar (WITA - GMT+8)</option>
                                    <option value="Asia/Jayapura" <?= (($app['zona_waktu'] ?? '') === 'Asia/Jayapura') ? 'selected' : '' ?>>Asia/Jayapura (WIT - GMT+9)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ACTION BAR SIMPAN PENGATURAN -->
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5 text-xs text-slate-600">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-700 flex-shrink-0"></i>
                        <span>Perubahan pengaturan aplikasi akan langsung diterapkan ke seluruh modul SIMRS.</span>
                    </div>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <button type="submit" id="btn-save-aplikasi" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center justify-center gap-2 shadow-md shadow-emerald-900/20 transition-all border border-emerald-600 cursor-pointer">
                            <i data-lucide="save" class="w-4 h-4 text-amber-300"></i>
                            <span>Simpan Perubahan Pengaturan Aplikasi</span>
                        </button>
                    </div>
                </div>

            </form>
        </div>


        <!-- ========================================================================= -->
        <!-- TAB 2: KECERDASAN BUATAN (AI MEDICAL LABORATORY ASSISTANT)                -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'ai'" x-cloak class="space-y-6">

            <!-- BANNER AI STATUS -->
            <div class="bg-gradient-to-r from-purple-950 via-slate-900 to-indigo-950 text-white rounded-2xl p-5 border border-purple-800/50 shadow-md">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-purple-600 to-pink-500 text-white flex items-center justify-center shadow-lg shadow-purple-900/40 flex-shrink-0">
                            <i data-lucide="sparkles" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-extrabold text-white tracking-wide">LIS Smart AI Clinical Engine</h3>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-500/30 text-purple-200 border border-purple-400/30">Next-Gen Laboratory AI</span>
                            </div>
                            <p class="text-xs text-purple-200/80 mt-0.5">Analisis interpretasi nilai abnormal, peringatan kritis patologi, dan ringkasan riwayat medis pasien berbasis LLM</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" onclick="testKoneksiAi()" class="px-4 py-2 rounded-xl bg-purple-800/80 hover:bg-purple-700 text-purple-100 text-xs font-bold border border-purple-600/60 shadow-xs flex items-center gap-2 transition-all">
                            <i data-lucide="activity" class="w-4 h-4 text-pink-300"></i>
                            <span>Tes Handshake AI</span>
                        </button>
                    </div>
                </div>
            </div>

            <form id="form-pengaturan-ai" onsubmit="savePengaturanAi(event)" class="space-y-6">
                <!-- PANEL KONFIGURASI ENGINE AI -->
                <div class="lis-section">
                    <div class="lis-section-header bg-slate-100/90">
                        <div class="lis-section-title">
                            <i data-lucide="cpu" class="w-4 h-4 text-purple-600"></i>
                            <span>Konfigurasi Provider &amp; Model AI</span>
                        </div>
                        <span class="text-[10px] font-bold text-purple-800 bg-purple-50 px-2.5 py-0.5 rounded border border-purple-200">LLM Provider</span>
                    </div>

                    <div class="lis-section-body space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="lis-form-label">Provider Kecerdasan Buatan <span class="req">*</span></label>
                                <select name="ai_provider" id="ai-provider" class="lis-form-control font-semibold text-purple-950">
                                    <option value="gemini" <?= (($ai['ai_provider'] ?? '') === 'gemini') ? 'selected' : '' ?>>Google Gemini (Disarankan - Cepat &amp; Medis)</option>
                                    <option value="openai" <?= (($ai['ai_provider'] ?? '') === 'openai') ? 'selected' : '' ?>>OpenAI (ChatGPT-4o / GPT-4o-mini)</option>
                                    <option value="anthropic" <?= (($ai['ai_provider'] ?? '') === 'anthropic') ? 'selected' : '' ?>>Anthropic Claude 3.5 Sonnet</option>
                                    <option value="ollama" <?= (($ai['ai_provider'] ?? '') === 'ollama') ? 'selected' : '' ?>>Local On-Premise Ollama (DeepSeek / Llama 3)</option>
                                </select>
                            </div>

                            <div>
                                <label class="lis-form-label">Versi Model LLM</label>
                                <input type="text" name="ai_model" id="ai-model" value="<?= html_escape($ai['ai_model'] ?? 'gemini-1.5-flash') ?>" class="lis-form-control font-mono font-bold" placeholder="gemini-1.5-flash">
                                <span class="lis-form-hint">Gunakan model yang responsif dan hemat token</span>
                            </div>

                            <div>
                                <label class="lis-form-label">Kunci Rahasia API Key Provider <span class="req">*</span></label>
                                <div class="relative">
                                    <input :type="showAiKey ? 'text' : 'password'" name="ai_api_key" id="ai-api-key" value="<?= html_escape($ai['ai_api_key'] ?? '') ?>" class="lis-form-control pr-10 font-mono" placeholder="AIzaSy... / sk-proj-...">
                                    <button type="button" @click="showAiKey = !showAiKey" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600">
                                        <i data-lucide="eye" x-show="!showAiKey" class="w-4 h-4"></i>
                                        <i data-lucide="eye-off" x-show="showAiKey" class="w-4 h-4"></i>
                                    </button>
                                </div>
                                <span class="lis-form-hint">Disimpan aman dan terenkripsi pada server</span>
                            </div>
                        </div>

                        <!-- TOGGLE FITUR OTOMATISASI AI DI LIS -->
                        <div class="pt-3 border-t border-slate-100">
                            <label class="text-xs font-bold text-slate-800 uppercase tracking-wider block mb-3">Fitur Cerdas Otomatisasi AI di LIS PRO</label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <label class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-purple-50/50 flex items-start gap-3 cursor-pointer transition-colors">
                                    <input type="checkbox" name="ai_auto_interpretation" value="1" <?= (($ai['ai_auto_interpretation'] ?? '1') === '1') ? 'checked' : '' ?> class="mt-0.5 rounded text-purple-600 focus:ring-purple-500">
                                    <div>
                                        <span class="text-xs font-bold text-slate-900 block">Interpretasi Nilai Abnormal</span>
                                        <span class="text-[11px] text-slate-500 block mt-0.5">Menganalisis hasil tes darah/urin abnormal secara otomatis</span>
                                    </div>
                                </label>

                                <label class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-purple-50/50 flex items-start gap-3 cursor-pointer transition-colors">
                                    <input type="checkbox" name="ai_critical_alert" value="1" <?= (($ai['ai_critical_alert'] ?? '1') === '1') ? 'checked' : '' ?> class="mt-0.5 rounded text-purple-600 focus:ring-purple-500">
                                    <div>
                                        <span class="text-xs font-bold text-slate-900 block">Peringatan Nilai Kritis</span>
                                        <span class="text-[11px] text-slate-500 block mt-0.5">Deteksi dini kondisi gawat darurat laboratorium patologi</span>
                                    </div>
                                </label>

                                <label class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-purple-50/50 flex items-start gap-3 cursor-pointer transition-colors">
                                    <input type="checkbox" name="ai_clinical_summary" value="1" <?= (($ai['ai_clinical_summary'] ?? '1') === '1') ? 'checked' : '' ?> class="mt-0.5 rounded text-purple-600 focus:ring-purple-500">
                                    <div>
                                        <span class="text-xs font-bold text-slate-900 block">Ringkasan Riwayat Medis</span>
                                        <span class="text-[11px] text-slate-500 block mt-0.5">Membuat resume ringkas tren kesehatan pasien berkala</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- SYSTEM PROMPT TEMPLATE -->
                        <div class="pt-3 border-t border-slate-100">
                            <label class="lis-form-label">System Prompt Template AI Spesialis Patologi Klinik <span class="req">*</span></label>
                            <textarea name="ai_prompt_template" id="ai-prompt-template" rows="4" class="lis-form-control font-mono text-xs leading-relaxed" required><?= html_escape($ai['ai_prompt_template'] ?? '') ?></textarea>
                            <span class="lis-form-hint">Petunjuk instruksi peran klinis bagi agen AI saat menganalisis hasil uji laboratorium</span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" id="btn-save-ai" class="px-6 py-2.5 rounded-xl bg-purple-700 hover:bg-purple-800 text-white text-xs font-bold flex items-center gap-2 shadow-md shadow-purple-900/20 transition-all">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Simpan Konfigurasi AI</span>
                    </button>
                </div>
            </form>
        </div>


        <!-- ========================================================================= -->
        <!-- TAB 3: BRIDGING LIS & ALAT LABORATORIUM (INTEROPERABILITAS)               -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'bridging'" x-cloak class="space-y-6">

            <!-- BANNER BRIDGING -->
            <div class="bg-gradient-to-r from-teal-950 via-slate-900 to-emerald-950 text-white rounded-2xl p-5 border border-teal-800/50 shadow-md">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-teal-700 to-emerald-500 text-white flex items-center justify-center shadow-lg shadow-teal-900/40 flex-shrink-0">
                            <i data-lucide="network" class="w-6 h-6 text-amber-200"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-extrabold text-white tracking-wide">Interoperabilitas &amp; Bridging Alat Lab</h3>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-teal-500/30 text-teal-200 border border-teal-400/30">HL7 &bull; ASTM &bull; FHIR</span>
                            </div>
                            <p class="text-xs text-teal-200/80 mt-0.5">Integrasi SatuSehat Kemenkes RI, komunikasi dua arah analyzer otomatis, dan webhook callback SIMRS Rumah Sakit</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" onclick="testKoneksiBridging('satusehat')" class="px-4 py-2 rounded-xl bg-teal-800/80 hover:bg-teal-700 text-teal-100 text-xs font-bold border border-teal-600/60 shadow-xs flex items-center gap-2 transition-all">
                            <i data-lucide="zap" class="w-4 h-4 text-amber-300"></i>
                            <span>Tes Ping SatuSehat</span>
                        </button>
                    </div>
                </div>
            </div>

            <form id="form-pengaturan-bridging" onsubmit="savePengaturanBridging(event)" class="space-y-6">

                <!-- 1. BRIDGING SATUSEHAT KEMENKES -->
                <div class="lis-section">
                    <div class="lis-section-header bg-slate-100/90">
                        <div class="lis-section-title">
                            <i data-lucide="cloud" class="w-4 h-4 text-teal-700"></i>
                            <span>1. Bridging SatuSehat Kemenkes RI (FHIR Protocol)</span>
                        </div>
                        <label class="flex items-center gap-2 text-xs font-bold text-teal-900 cursor-pointer">
                            <input type="checkbox" name="satusehat_active" value="1" <?= (($bri['satusehat_active'] ?? '1') === '1') ? 'checked' : '' ?> class="rounded text-teal-700 focus:ring-teal-500">
                            <span>Status Bridging Aktif</span>
                        </label>
                    </div>

                    <div class="lis-section-body space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="lis-form-label">Environment SatuSehat <span class="req">*</span></label>
                                <select name="satusehat_env" id="bri-satusehat-env" class="lis-form-control font-semibold">
                                    <option value="development" <?= (($bri['satusehat_env'] ?? '') === 'development') ? 'selected' : '' ?>>Development (Sandbox DTO)</option>
                                    <option value="staging" <?= (($bri['satusehat_env'] ?? '') === 'staging') ? 'selected' : '' ?>>Staging / UAT</option>
                                    <option value="production" <?= (($bri['satusehat_env'] ?? '') === 'production') ? 'selected' : '' ?>>Production (Live Nasional)</option>
                                </select>
                            </div>
                            <div>
                                <label class="lis-form-label">Organization ID Faskes <span class="req">*</span></label>
                                <input type="text" name="satusehat_org_id" id="bri-satusehat-org-id" value="<?= html_escape($bri['satusehat_org_id'] ?? '') ?>" class="lis-form-control font-mono font-bold" placeholder="Contoh: 100000123">
                            </div>
                            <div>
                                <label class="lis-form-label">OAuth2 Client ID <span class="req">*</span></label>
                                <input type="text" name="satusehat_client_id" id="bri-satusehat-client-id" value="<?= html_escape($bri['satusehat_client_id'] ?? '') ?>" class="lis-form-control font-mono text-xs" placeholder="client_labkes_...">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="lis-form-label">OAuth2 Client Secret</label>
                                <div class="relative">
                                    <input :type="showSecretKey ? 'text' : 'password'" name="satusehat_client_secret" id="bri-satusehat-client-secret" value="<?= html_escape($bri['satusehat_client_secret'] ?? '') ?>" class="lis-form-control pr-10 font-mono text-xs" placeholder="secret_...">
                                    <button type="button" @click="showSecretKey = !showSecretKey" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600">
                                        <i data-lucide="eye" x-show="!showSecretKey" class="w-4 h-4"></i>
                                        <i data-lucide="eye-off" x-show="showSecretKey" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="lis-form-label">Auth Token Endpoint URL</label>
                                <input type="url" name="satusehat_auth_url" id="bri-satusehat-auth-url" value="<?= html_escape($bri['satusehat_auth_url'] ?? '') ?>" class="lis-form-control font-mono text-xs" placeholder="https://api-satusehat-dev.dto.kemkes.go.id/oauth2/v1">
                            </div>
                        </div>

                        <div>
                            <label class="lis-form-label">FHIR Base URL (DiagnosticReport / Specimen)</label>
                            <input type="url" name="satusehat_fhir_url" id="bri-satusehat-fhir-url" value="<?= html_escape($bri['satusehat_fhir_url'] ?? '') ?>" class="lis-form-control font-mono text-xs" placeholder="https://api-satusehat-dev.dto.kemkes.go.id/fhir-r4/v1">
                        </div>
                    </div>
                </div>

                <!-- 2. INTERFACING ALAT ANALYZER LABORATORIUM -->
                <div class="lis-section">
                    <div class="lis-section-header bg-slate-100/90">
                        <div class="lis-section-title">
                            <i data-lucide="cpu" class="w-4 h-4 text-teal-700"></i>
                            <span>2. Komunikasi Alat Analyzer Laboratorium (ASTM / HL7)</span>
                        </div>
                        <button type="button" onclick="testKoneksiBridging('analyzer')" class="text-[10px] text-teal-800 font-bold hover:underline flex items-center gap-1">
                            <i data-lucide="radio" class="w-3.5 h-3.5"></i>
                            <span>Uji Port Alat</span>
                        </button>
                    </div>

                    <div class="lis-section-body space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="lis-form-label">Standar Protokol Analyzer</label>
                                <select name="analyzer_protocol" id="bri-analyzer-protocol" class="lis-form-control font-mono text-xs font-semibold">
                                    <option value="ASTM_E1394" <?= (($bri['analyzer_protocol'] ?? '') === 'ASTM_E1394') ? 'selected' : '' ?>>ASTM E1394 / ASTM E1381 (Serial &amp; TCP)</option>
                                    <option value="HL7_V2" <?= (($bri['analyzer_protocol'] ?? '') === 'HL7_V2') ? 'selected' : '' ?>>HL7 v2.5 / v2.6 MLLP Protocol</option>
                                    <option value="CSV_WATCHER" <?= (($bri['analyzer_protocol'] ?? '') === 'CSV_WATCHER') ? 'selected' : '' ?>>File Directory / CSV Watcher</option>
                                </select>
                            </div>
                            <div>
                                <label class="lis-form-label">IP Host Analyzer / Bridge Service</label>
                                <input type="text" name="analyzer_ip" id="bri-analyzer-ip" value="<?= html_escape($bri['analyzer_ip'] ?? '192.168.1.150') ?>" class="lis-form-control font-mono font-bold" placeholder="192.168.1.150">
                            </div>
                            <div>
                                <label class="lis-form-label">Port Komunikasi (TCP Listening)</label>
                                <input type="number" name="analyzer_port" id="bri-analyzer-port" value="<?= html_escape($bri['analyzer_port'] ?? '5000') ?>" class="lis-form-control font-mono font-bold" placeholder="5000">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. WEBHOOK SIMRS & HIS -->
                <div class="lis-section">
                    <div class="lis-section-header bg-slate-100/90">
                        <div class="lis-section-title">
                            <i data-lucide="send" class="w-4 h-4 text-teal-700"></i>
                            <span>3. Webhook Callback SIMRS / HIS Faskes Pengirim</span>
                        </div>
                        <span class="text-[10px] text-slate-500 font-semibold">Integrasi RS &amp; Puskesmas</span>
                    </div>

                    <div class="lis-section-body space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="lis-form-label">Endpoint Callback Webhook URL</label>
                                <input type="url" name="his_webhook_url" id="bri-his-webhook-url" value="<?= html_escape($bri['his_webhook_url'] ?? '') ?>" class="lis-form-control font-mono text-xs" placeholder="http://simrs.rsud.riau.go.id/api/lis/callback">
                                <span class="lis-form-hint">Otomatis mengirimkan payload JSON hasil validasi ke sistem faskes pengirim</span>
                            </div>
                            <div>
                                <label class="lis-form-label">Secret API Key Webhook</label>
                                <input type="text" name="his_api_key" id="bri-his-api-key" value="<?= html_escape($bri['his_api_key'] ?? '') ?>" class="lis-form-control font-mono text-xs" placeholder="his_tok_...">
                                <span class="lis-form-hint">Token header otorisasi Bearer untuk mengamankan callback</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" id="btn-save-bridging" class="px-6 py-2.5 rounded-xl bg-teal-700 hover:bg-teal-800 text-amber-200 text-xs font-bold flex items-center gap-2 shadow-md shadow-teal-900/20 transition-all border border-teal-600">
                        <i data-lucide="save" class="w-4 h-4 text-amber-300"></i>
                        <span>Simpan Pengaturan Bridging</span>
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<!-- ========================================================================= -->
<!-- JAVASCRIPT LOGIC PENGATURAN                                               -->
<!-- ========================================================================= -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.lucide) lucide.createIcons();
});

// 1. Simpan Pengaturan Aplikasi (AJAX)
function savePengaturanAplikasi(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save-aplikasi');
    const originalText = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<span class="inline-block animate-spin mr-1">⌛</span> Menyimpan...';

    const form = document.getElementById('form-pengaturan-aplikasi');
    const fd = new FormData(form);

    fetch('<?= site_url('pengaturan/save_aplikasi') ?>', {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        if (window.lucide) lucide.createIcons();

        if (res.status === 'success') {
            LIS.toast('success', res.message);
        } else {
            LIS.alert('Gagal Menyimpan', res.message, 'error');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        if (window.lucide) lucide.createIcons();
        LIS.alert('Kesalahan Jaringan', 'Gagal menghubungi server.', 'error');
    });
}

// 2. Simpan Pengaturan AI (AJAX)
function savePengaturanAi(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save-ai');
    const originalText = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<span class="inline-block animate-spin mr-1">⌛</span> Menyimpan...';

    const form = document.getElementById('form-pengaturan-ai');
    const fd = new FormData(form);

    fetch('<?= site_url('pengaturan/save_ai') ?>', {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        if (window.lucide) lucide.createIcons();

        if (res.status === 'success') {
            LIS.toast('success', res.message);
        } else {
            LIS.alert('Gagal Menyimpan', res.message, 'error');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        if (window.lucide) lucide.createIcons();
        LIS.alert('Kesalahan', 'Gagal menyimpan konfigurasi AI.', 'error');
    });
}

// 3. Simpan Pengaturan Bridging (AJAX)
function savePengaturanBridging(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save-bridging');
    const originalText = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = '<span class="inline-block animate-spin mr-1">⌛</span> Menyimpan...';

    const form = document.getElementById('form-pengaturan-bridging');
    const fd = new FormData(form);

    fetch('<?= site_url('pengaturan/save_bridging') ?>', {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        if (window.lucide) lucide.createIcons();

        if (res.status === 'success') {
            LIS.toast('success', res.message);
        } else {
            LIS.alert('Gagal Menyimpan', res.message, 'error');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        if (window.lucide) lucide.createIcons();
        LIS.alert('Kesalahan', 'Gagal menyimpan konfigurasi bridging.', 'error');
    });
}

// 4. Test Koneksi AI
function testKoneksiAi() {
    const provider = document.getElementById('ai-provider').value;
    const apiKey = document.getElementById('ai-api-key').value;

    LIS.toast('info', 'Menguji koneksi ke server AI ' + provider.toUpperCase() + '...');

    const fd = new FormData();
    fd.append('provider', provider);
    fd.append('api_key', apiKey);

    fetch('<?= site_url('pengaturan/test_ai') ?>', {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            LIS.alert('Koneksi AI Terhubung', res.message, 'success');
        } else {
            LIS.alert('Peringatan Koneksi AI', res.message, 'warning');
        }
    })
    .catch(() => {
        LIS.alert('Gagal', 'Terjadi gangguan saat menguji AI.', 'error');
    });
}

// 5. Test Koneksi Bridging
function testKoneksiBridging(target) {
    LIS.toast('info', 'Melakukan uji handshake koneksi [' + target + ']...');

    const fd = new FormData();
    fd.append('target', target);

    fetch('<?= site_url('pengaturan/test_bridging') ?>', {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
        LIS.alert('Hasil Pengujian Bridging', res.message, res.status);
    })
    .catch(() => {
        LIS.alert('Gagal', 'Gagal melakukan tes ping bridging.', 'error');
    });
}

// 6. Upload Berkas Logo (Instansi / KAN) via AJAX
function uploadLogoFile(type, input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];

    // Cek ukuran berkas (Maks 3 MB)
    if (file.size > 3 * 1024 * 1024) {
        LIS.alert('Ukuran Terlalu Besar', 'Maksimal ukuran berkas logo adalah 3 MB.', 'warning');
        input.value = '';
        return;
    }

    const mainContainer = document.querySelector('[x-data]');
    const alpineData = mainContainer ? Alpine.$data(mainContainer) : null;
    if (alpineData) {
        if (type === 'logo_instansi') alpineData.uploadingInstansi = true;
        if (type === 'logo_kan') alpineData.uploadingKan = true;
    }

    const fd = new FormData();
    fd.append('logo_type', type);
    fd.append('logo_file', file);

    fetch('<?= site_url('pengaturan/upload_logo') ?>', {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
        if (alpineData) {
            if (type === 'logo_instansi') alpineData.uploadingInstansi = false;
            if (type === 'logo_kan') alpineData.uploadingKan = false;
        }
        input.value = '';

        if (res.status === 'success') {
            LIS.toast('success', res.message);
            const tsUrl = res.file_url + '?t=' + Date.now();
            if (alpineData) {
                if (type === 'logo_instansi') {
                    alpineData.previewLogoInstansi = tsUrl;
                    alpineData.hasCustomInstansi = true;
                } else {
                    alpineData.previewLogoKan = tsUrl;
                    alpineData.hasCustomKan = true;
                }
            }
        } else {
            LIS.alert('Gagal Unggah Logo', res.message, 'error');
        }
    })
    .catch(err => {
        if (alpineData) {
            if (type === 'logo_instansi') alpineData.uploadingInstansi = false;
            if (type === 'logo_kan') alpineData.uploadingKan = false;
        }
        input.value = '';
        LIS.alert('Kesalahan Server', 'Gagal mengunggah logo ke server.', 'error');
    });
}

// 7. Reset / Hapus Berkas Logo Kustom via AJAX
function removeLogoFile(type) {
    const label = (type === 'logo_kan') ? 'Logo Akreditasi KAN' : 'Logo Instansi Laboratorium';
    LIS.confirm(
        'Reset ' + label + '?',
        'Logo kustom akan dihapus dan dokumen cetak otomatis kembali menggunakan logo resmi default sistem.',
        'warning',
        'Ya, Kembalikan ke Default',
        'Batal'
    ).then((result) => {
        if (result.isConfirmed) {
            const mainContainer = document.querySelector('[x-data]');
            const alpineData = mainContainer ? Alpine.$data(mainContainer) : null;
            if (alpineData) {
                if (type === 'logo_instansi') alpineData.uploadingInstansi = true;
                if (type === 'logo_kan') alpineData.uploadingKan = true;
            }

            const fd = new FormData();
            fd.append('logo_type', type);

            fetch('<?= site_url('pengaturan/remove_logo') ?>', {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(res => {
                if (alpineData) {
                    if (type === 'logo_instansi') alpineData.uploadingInstansi = false;
                    if (type === 'logo_kan') alpineData.uploadingKan = false;
                }

                if (res.status === 'success') {
                    LIS.toast('success', res.message);
                    const tsUrl = res.default_url + '?t=' + Date.now();
                    if (alpineData) {
                        if (type === 'logo_instansi') {
                            alpineData.previewLogoInstansi = tsUrl;
                            alpineData.hasCustomInstansi = false;
                        } else {
                            alpineData.previewLogoKan = tsUrl;
                            alpineData.hasCustomKan = false;
                        }
                    }
                } else {
                    LIS.alert('Gagal', res.message, 'error');
                }
            })
            .catch(() => {
                if (alpineData) {
                    if (type === 'logo_instansi') alpineData.uploadingInstansi = false;
                    if (type === 'logo_kan') alpineData.uploadingKan = false;
                }
                LIS.alert('Kesalahan', 'Gagal menghapus logo.', 'error');
            });
        }
    });
}

// 8. Helper sinkronisasi kontak pada preview kop dokumen
function updateContactPreview() {
    const mainContainer = document.querySelector('[x-data]');
    const alpineData = mainContainer ? Alpine.$data(mainContainer) : null;
    if (!alpineData) return;

    const telp = document.getElementById('app-telepon')?.value || '';
    const wa   = document.getElementById('app-whatsapp-layanan')?.value || '';
    const mail = document.getElementById('app-email-resmi')?.value || '';

    let parts = [];
    if (telp) parts.push('Telp: ' + telp);
    if (wa) parts.push('WA: ' + wa);
    if (mail) parts.push('Email: ' + mail);

    alpineData.previewKontak = parts.join(' &bull; ');
}
</script>

