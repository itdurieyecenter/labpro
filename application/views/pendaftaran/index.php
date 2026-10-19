<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php $isSampling = !empty($is_sampling_queue); ?>
<div class="space-y-5">

    <!-- 1. BREADCRUMB & PAGE HEADER (Clean Melayu Riau Theme) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-slate-400">Pelayanan Laboratorium</span>
                <span>/</span>
                <span class="text-emerald-800 font-semibold"><?= $isSampling ? 'Sampling & Antrian Lab' : 'Pendaftaran & Order LIS' ?></span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="<?= $isSampling ? 'test-tubes' : 'clipboard-signature' ?>" class="w-6 h-6"></i>
                </span>
                <span><?= $isSampling ? 'Antrian Pengambilan Sampel & Spesimen Lab' : 'Pendaftaran & Order Pemeriksaan LIS' ?></span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                <?= $isSampling 
                    ? 'Daftar spesimen tervalidasi (PAID & GUARANTEED) siap pengambilan darah / penerimaan sampel pengujian LIS' 
                    : 'Registrasi pasien mandiri, rujukan faskes, dan sampel mitra lingkungan &bull; Alur Billing & Validasi Pembayaran' ?>
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" onclick="reloadTable()" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold flex items-center gap-2 transition-colors border border-slate-200" title="Muat Ulang Data">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Refresh</span>
            </button>

            <a href="<?= site_url('client') ?>" class="px-3.5 py-2.5 rounded-xl bg-white hover:bg-emerald-50 text-emerald-800 text-xs font-bold flex items-center gap-2 transition-colors border border-emerald-300 shadow-xs" title="Kelola Database Master Klien / Pasien">
                <i data-lucide="contact-2" class="w-4 h-4 text-emerald-700"></i>
                <span>Master Klien</span>
            </a>

            <?php if (!$isSampling && $can_create): ?>
                <button type="button" onclick="openOrderModal()" class="px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-2 shadow-sm transition-all border border-emerald-600 shadow-emerald-900/10">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>+ Pendaftaran & Order Baru</span>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- 2. SUMMARY STATISTIK CARDS (Melayu Riau Theme) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4" id="stats-grid">
        <!-- Pendaftaran Hari Ini -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-center flex-shrink-0">
                <i data-lucide="calendar-check-2" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Order Hari Ini</span>
                <span id="stat-total-hari-ini" class="text-xl sm:text-2xl font-black text-slate-900 leading-none"><?= $summary['total_hari_ini'] ?></span>
                <span class="text-[10px] text-slate-400 block mt-0.5"><?= $summary['mandiri'] ?> Mandiri &bull; <?= $summary['rujukan'] ?> Rujukan</span>
            </div>
        </div>

        <!-- Menunggu Bayar (UNPAID) -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 flex items-center justify-center flex-shrink-0">
                <i data-lucide="receipt" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Menunggu Kasir</span>
                <span id="stat-unpaid" class="text-xl sm:text-2xl font-black text-amber-800 leading-none"><?= $summary['unpaid'] ?></span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Antrian Pembayaran Kasir</span>
            </div>
        </div>

        <!-- Lunas Kasir (PAID) -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 border border-emerald-300 text-emerald-900 flex items-center justify-center flex-shrink-0">
                <i data-lucide="badge-check" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Lunas Kasir (PAID)</span>
                <span id="stat-paid" class="text-xl sm:text-2xl font-black text-emerald-800 leading-none"><?= $summary['paid'] ?></span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Barcode Sampel Aktif</span>
            </div>
        </div>

        <!-- Dijamin PKS / Piutang Mitra -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-teal-50 border border-teal-200 text-teal-800 flex items-center justify-center flex-shrink-0">
                <i data-lucide="handshake" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">PKS Mitra (GUARANTEED)</span>
                <span id="stat-guaranteed" class="text-xl sm:text-2xl font-black text-teal-800 leading-none"><?= $summary['guaranteed'] ?></span>
                <span class="text-[10px] text-slate-400 block mt-0.5">SPK Terverifikasi &bull; Invoicing</span>
            </div>
        </div>
    </div>

    <!-- 3. FILTER TABS & TOOLBAR CARD -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs space-y-3">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <!-- Filter Tabs Tipe Pasien -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 scrollbar-none flex-wrap">
                <button type="button" onclick="setFilterType('all')" id="tab-type-all" class="filter-type-tab px-3.5 py-2 rounded-xl text-xs font-bold transition-all bg-emerald-700 text-amber-300 shadow-xs flex items-center gap-2">
                    <i data-lucide="layers" class="w-4 h-4"></i>
                    <span>Semua Pendaftaran</span>
                </button>
                <button type="button" onclick="setFilterType('mandiri')" id="tab-type-mandiri" class="filter-type-tab px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-all flex items-center gap-2">
                    <i data-lucide="user" class="w-4 h-4"></i>
                    <span>Pasien Mandiri (Walk-in)</span>
                </button>
                <button type="button" onclick="setFilterType('rujukan_faskes')" id="tab-type-rujukan" class="filter-type-tab px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-all flex items-center gap-2">
                    <i data-lucide="stethoscope" class="w-4 h-4"></i>
                    <span>Rujukan Faskes (RS/PKM)</span>
                </button>
                <button type="button" onclick="setFilterType('perusahaan_lingkungan')" id="tab-type-perusahaan" class="filter-type-tab px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-all flex items-center gap-2">
                    <i data-lucide="building-2" class="w-4 h-4"></i>
                    <span>Mitra & Sampel Lingkungan</span>
                </button>
            </div>

            <!-- Filter Status Pembayaran & Tanggal -->
            <div class="flex items-center gap-2 flex-wrap">
                <select id="filter-status-bayar" onchange="reloadTable()" class="lis-form-control text-xs w-auto py-1 px-3">
                    <option value="all">-- Semua Status Bayar --</option>
                    <option value="UNPAID">Menunggu Kasir (UNPAID)</option>
                    <option value="PAID">Lunas Kasir (PAID)</option>
                    <option value="GUARANTEED">Dijamin PKS (GUARANTEED)</option>
                    <option value="CANCELLED">Dibatalkan</option>
                </select>

                <input type="date" id="filter-tanggal" onchange="reloadTable()" value="<?= date('Y-m-d') ?>" class="lis-form-control text-xs w-auto py-1 px-2.5 font-medium" title="Filter Tanggal Registrasi">

                <button type="button" onclick="document.getElementById('filter-tanggal').value=''; reloadTable();" class="p-2 text-slate-500 hover:text-rose-600 rounded-lg hover:bg-slate-100" title="Reset Filter Tanggal">
                    <i data-lucide="calendar-x" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- 4. TABEL PENDAFTARAN & ORDER LIS (Enterprise DataTables) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-gradient-to-r from-emerald-50/50 via-white to-amber-50/30">
            <div class="flex items-center gap-2.5">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 animate-pulse"></span>
                <h2 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider">
                    Daftar Transaksi Pendaftaran, Order LIS & Validasi Pembayaran
                </h2>
            </div>
            <div class="flex items-center gap-2 text-xs text-slate-400 font-mono text-[11px]">
                <span>BLUD &bull; UPT LABKES PROV. RIAU</span>
            </div>
        </div>

        <div class="p-4 overflow-x-auto">
            <table id="table-pendaftaran" class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-[10px] font-bold text-slate-600 uppercase tracking-wider">
                        <th class="py-3 px-2 text-center w-8">No</th>
                        <th class="py-3 px-3">No. Registrasi / Billing</th>
                        <th class="py-3 px-3">Pasien / Subjek Uji</th>
                        <th class="py-3 px-3">Tipe & Pengirim</th>
                        <th class="py-3 px-2.5 text-center">Pemeriksaan</th>
                        <th class="py-3 px-3 text-right">Total Billing (Rp)</th>
                        <th class="py-3 px-3 text-center">Status Pembayaran</th>
                        <th class="py-3 px-3 text-center">Barcode Sampel</th>
                        <th class="py-3 px-3 text-center w-36">Aksi Pelayanan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <!-- Data loaded via DataTables AJAX -->
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ========================================================================= -->
<!-- MODAL 1: FORMULIR PENDAFTARAN & ORDER LIS BARU                             -->
<!-- ========================================================================= -->
<div id="modal-order" class="lis-modal">
    <div class="lis-modal-box max-w-6xl w-full">
        <!-- Header Modal -->
        <div class="lis-modal-header bg-gradient-to-r from-emerald-900 via-emerald-800 to-amber-950 border-b border-amber-400/40">
            <div>
                <h3 class="lis-modal-title text-white">
                    <i data-lucide="clipboard-signature" class="w-5 h-5 text-amber-300"></i>
                    <span>Formulir Pendaftaran & Order Pemeriksaan LIS</span>
                </h3>
                <p class="lis-modal-subtitle">UPT Laboratorium Kesehatan Provinsi Riau &bull; Alur SIMRS & Billing BLUD</p>
            </div>
            <button type="button" onclick="closeOrderModal()" class="lis-modal-close" title="Tutup Formulir">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Body Modal -->
        <div class="lis-modal-body p-4 sm:p-6 overflow-y-auto max-h-[calc(90vh-130px)] space-y-5 bg-slate-50/70">
            <form id="order-form" onsubmit="saveOrder(event)" class="space-y-5">
                <input type="hidden" name="client_id" id="form-client-id">
                <input type="hidden" name="items_json" id="form-items-json">

                <!-- 1. TOP STATUS BAR: IDENTITAS DOKUMEN & KODE RESMI SIMRS -->
                <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 text-white rounded-2xl p-3.5 sm:p-4 border border-slate-700/60 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-3.5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-600/30 border border-emerald-500/40 text-amber-300 flex items-center justify-center flex-shrink-0 shadow-inner">
                            <i data-lucide="file-badge-2" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold tracking-wide uppercase text-white">Dokumen Order &amp; Registrasi LIS</span>
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-emerald-500/25 text-emerald-300 border border-emerald-400/30">Auto SIMRS</span>
                            </div>
                            <p class="text-[11px] text-slate-300 mt-0.5">Diterbitkan otomatis &bull; Terkoneksi dengan Kasir Billing BLUD &amp; Barcode Tracking</p>
                        </div>
                    </div>

                    <!-- 3 Badges Identifikasi: Satu Baris Proporsional, Rapih, Anti-Potong -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 w-full lg:w-auto">
                        <!-- 1. No Registrasi -->
                        <div class="bg-slate-800/90 border border-slate-700 rounded-xl px-3.5 py-2 flex flex-col justify-center min-w-[155px]">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">No. Registrasi</span>
                            <span id="disp-no-reg" class="font-mono font-bold text-xs text-amber-300 tracking-wide mt-0.5 block truncate">REG-...</span>
                            <input type="hidden" name="nomor_registrasi" id="form-no-reg">
                        </div>
                        <!-- 2. Billing ID -->
                        <div class="bg-slate-800/90 border border-slate-700 rounded-xl px-3.5 py-2 flex flex-col justify-center min-w-[155px]">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Billing ID</span>
                            <span id="disp-no-billing" class="font-mono font-bold text-xs text-emerald-300 tracking-wide mt-0.5 block truncate">BIL-...</span>
                            <input type="hidden" name="nomor_billing" id="form-no-billing">
                        </div>
                        <!-- 3. Barcode Sampel -->
                        <div class="bg-emerald-950/80 border border-emerald-600/50 rounded-xl px-3.5 py-2 flex flex-col justify-center min-w-[155px]">
                            <div class="flex items-center justify-between">
                                <span class="text-[9px] font-bold text-emerald-300 uppercase tracking-widest block">Barcode Sampel</span>
                                <i data-lucide="scan-barcode" class="w-3.5 h-3.5 text-emerald-400"></i>
                            </div>
                            <span id="disp-no-barcode" class="font-mono font-bold text-xs text-amber-200 tracking-wide mt-0.5 block truncate">SMP-...</span>
                            <input type="hidden" name="barcode_sampel" id="form-no-barcode">
                        </div>
                    </div>
                </div>

                <!-- 2. CARD: PILIH TIPE REGISTRASI PELAYANAN (LEBAR PENUH & SIMETRIS) -->
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <label class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-600 inline-block"></span>
                                    Pilih Tipe Registrasi Pelayanan <span class="text-rose-500">*</span>
                                </label>
                                <p class="text-[11px] text-slate-500 mt-0.5">Pilih alur pelayanan untuk menentukan tarif dasar dan skema penjaminan laboratorium</p>
                            </div>
                            <span class="text-[10px] font-semibold text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200/60 hidden sm:inline-block">Langkah 1 dari 4</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5" id="tipe-pasien-selector">
                            <!-- 1. Pasien Mandiri -->
                            <button type="button" onclick="setFormTipe('mandiri')" id="btn-tipe-mandiri" class="relative p-3.5 rounded-xl border-2 border-emerald-700 bg-emerald-50/70 text-emerald-950 text-left transition-all flex items-start gap-3 shadow-xs">
                                <div id="icon-tipe-mandiri" class="w-10 h-10 rounded-xl bg-emerald-700 text-amber-300 flex items-center justify-center flex-shrink-0 shadow-xs transition-colors">
                                    <i data-lucide="user" class="w-5 h-5"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-1">
                                        <span class="font-bold text-xs text-slate-900 block leading-tight">1. Pasien Mandiri</span>
                                        <span id="badge-check-mandiri" class="text-emerald-700 flex-shrink-0">
                                            <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                                        </span>
                                    </div>
                                    <span class="text-[11px] text-slate-500 block mt-1 leading-snug">Walk-in umum / perorangan datang langsung ke lab</span>
                                </div>
                            </button>

                            <!-- 2. Rujukan Faskes -->
                            <button type="button" onclick="setFormTipe('rujukan_faskes')" id="btn-tipe-rujukan" class="relative p-3.5 rounded-xl border-2 border-slate-200 bg-white hover:bg-slate-50/80 text-slate-700 text-left transition-all flex items-start gap-3">
                                <div id="icon-tipe-rujukan" class="w-10 h-10 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center flex-shrink-0 transition-colors">
                                    <i data-lucide="stethoscope" class="w-5 h-5"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-1">
                                        <span class="font-bold text-xs text-slate-800 block leading-tight">2. Rujukan Faskes</span>
                                        <span id="badge-check-rujukan" class="text-emerald-700 flex-shrink-0 hidden">
                                            <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                                        </span>
                                    </div>
                                    <span class="text-[11px] text-slate-500 block mt-1 leading-snug">Puskesmas, Rumah Sakit, Klinik, atau Dokter Luar</span>
                                </div>
                            </button>

                            <!-- 3. Mitra / Lingkungan -->
                            <button type="button" onclick="setFormTipe('perusahaan_lingkungan')" id="btn-tipe-perusahaan" class="relative p-3.5 rounded-xl border-2 border-slate-200 bg-white hover:bg-slate-50/80 text-slate-700 text-left transition-all flex items-start gap-3">
                                <div id="icon-tipe-perusahaan" class="w-10 h-10 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center flex-shrink-0 transition-colors">
                                    <i data-lucide="building-2" class="w-5 h-5"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-1">
                                        <span class="font-bold text-xs text-slate-800 block leading-tight">3. Mitra / Lingkungan</span>
                                        <span id="badge-check-perusahaan" class="text-emerald-700 flex-shrink-0 hidden">
                                            <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                                        </span>
                                    </div>
                                    <span class="text-[11px] text-slate-500 block mt-1 leading-snug">PKS Kontrak Perusahaan, MCU, Sampel Air &amp; Limbah</span>
                                </div>
                            </button>
                        </div>
                        <input type="hidden" name="tipe_pasien" id="form-tipe-pasien" value="mandiri">
                    </div>

                    <!-- PENCARIAN PASIEN & PERUSAHAAN TERDAFTAR (AUTOCOMPLETE DENGAN RESET & TOMBOL DAFTAR BARU) -->
                    <div class="pt-3 border-t border-slate-100">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-2">
                            <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                <i data-lucide="database" class="w-3.5 h-3.5 text-emerald-700"></i>
                                <span>Cari dari Database Master Klien / Pasien / Perusahaan:</span>
                            </label>
                            <span class="text-[10px] text-slate-400">Ketik untuk mencari otomatis, atau klik tombol untuk tambah master klien baru</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <!-- Relative Wrapper agar dropdown hasil pencarian posisinya tepat di bawah input -->
                            <div class="relative flex-1" id="patient-search-wrapper">
                                <input type="text" id="patient-search-input" oninput="searchPatient(this.value)" onfocus="if(this.value.trim().length >= 1) searchPatient(this.value)" placeholder="Ketik Nama Pasien, Faskes Rujukan, Perusahaan Mitra (contoh: MoizCare, Pertamina), NIK, atau No. RM..." class="lis-form-control pl-10 pr-9 text-xs py-2 bg-slate-50/80 focus:bg-white transition-colors font-medium">
                                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-2.5 pointer-events-none"></i>
                                <button type="button" onclick="clearPatientSearch()" id="btn-clear-patient-search" class="hidden absolute right-3 top-2 text-slate-400 hover:text-rose-600 transition-colors" title="Bersihkan Pencarian">
                                    <i data-lucide="x" class="w-4 h-4"></i>
                                </button>

                                <!-- DROPDOWN HASIL PENCARIAN (TEPAT DI BAWAH INPUT & Z-INDEX TINGGI) -->
                                <div id="patient-search-results" class="hidden absolute left-0 right-0 top-full mt-1.5 bg-white border-2 border-emerald-500 rounded-xl shadow-2xl max-h-72 overflow-y-auto z-50 divide-y divide-slate-100"></div>
                            </div>

                            <!-- Tombol Modal Daftar Klien Baru (Tanpa harus bolak-balik ke menu master client) -->
                            <button type="button" onclick="openQuickClientModal(document.getElementById('patient-search-input').value.trim())" class="px-3.5 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-1.5 shadow-xs transition-all flex-shrink-0 border border-emerald-600" title="Daftarkan Pasien atau Perusahaan Baru Langsung ke Master Client">
                                <i data-lucide="user-plus" class="w-4 h-4 text-amber-300"></i>
                                <span>+ Klien / Pasien Baru</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 2-COLUMN STRUCTURED LAYOUT -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

                    <!-- KOLOM KIRI (5 Kolom): IDENTITAS PASIEN & RUJUKAN -->
                    <div class="lg:col-span-5 space-y-4">
                        <!-- Panel Identitas Pasien / Subjek Uji -->
                        <div class="lis-section">
                            <div class="lis-section-header bg-slate-100/90">
                                <div class="lis-section-title">
                                    <span class="lis-section-num bg-emerald-700 text-amber-300">1</span>
                                    <span>Identitas Pasien / Subjek Pemeriksaan</span>
                                </div>
                                <span class="text-[10px] text-emerald-800 font-semibold bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Data Klinis</span>
                            </div>
                            <div class="lis-section-body space-y-3">
                                <div>
                                    <label class="lis-form-label">Nama Lengkap Pasien / Nama Subjek <span class="req">*</span></label>
                                    <input type="text" name="nama_pasien" id="form-nama-pasien" class="lis-form-control font-bold text-slate-900" required placeholder="Nama sesuai identitas resmi">
                                </div>

                                <div class="grid grid-cols-2 gap-2.5">
                                    <div>
                                        <label class="lis-form-label">NIK / KTP</label>
                                        <input type="text" name="nik_pasien" id="form-nik-pasien" maxlength="20" class="lis-form-control font-mono" placeholder="16 Digit NIK">
                                    </div>
                                    <div>
                                        <label class="lis-form-label">No. Rekam Medis (RM)</label>
                                        <input type="text" name="nomor_rm" id="form-nomor-rm" class="lis-form-control font-mono" placeholder="PSN-2026-XXXX">
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-2.5">
                                    <div>
                                        <label class="lis-form-label">Gender <span class="req">*</span></label>
                                        <select name="jenis_kelamin" id="form-gender" class="lis-form-control font-medium">
                                            <option value="L">Laki-laki (L)</option>
                                            <option value="P">Perempuan (P)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="lis-form-label">Tgl Lahir</label>
                                        <input type="date" name="tanggal_lahir" id="form-birth-date" onchange="calculateFormAge(this.value)" class="lis-form-control font-medium">
                                    </div>
                                    <div>
                                        <label class="lis-form-label">Usia (Tahun)</label>
                                        <input type="number" name="usia_tahun" id="form-usia-tahun" min="0" max="130" class="lis-form-control font-mono font-bold" placeholder="Thn">
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2.5">
                                    <div>
                                        <label class="lis-form-label">No. WhatsApp / HP <span class="req">*</span></label>
                                        <input type="tel" name="telepon" id="form-telepon" class="lis-form-control font-mono font-bold" required placeholder="081234567890">
                                    </div>
                                    <div>
                                        <label class="lis-form-label">Alamat Email</label>
                                        <input type="email" name="email" id="form-email" class="lis-form-control" placeholder="pasien@email.com">
                                    </div>
                                </div>

                                <div>
                                    <label class="lis-form-label">Alamat Lengkap & Kota/Kabupaten</label>
                                    <textarea name="alamat" id="form-alamat" rows="2" class="lis-form-control" placeholder="Jl. ..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Panel Rujukan & Keterangan Pengantar -->
                        <div class="lis-section">
                            <div class="lis-section-header bg-slate-100/90">
                                <div class="lis-section-title">
                                    <span class="lis-section-num bg-emerald-700 text-amber-300">2</span>
                                    <span>Instansi Pengirim & Pengantar Klinis</span>
                                </div>
                            </div>
                            <div class="lis-section-body space-y-3">
                                <div class="grid grid-cols-2 gap-2.5">
                                    <div>
                                        <label class="lis-form-label">Instansi / Faskes Pengirim</label>
                                        <input type="text" name="instansi_pengirim" id="form-instansi-pengirim" class="lis-form-control" placeholder="Puskesmas / RS / PT Mitra">
                                    </div>
                                    <div>
                                        <label class="lis-form-label">Dokter Pengirim</label>
                                        <input type="text" name="dokter_pengirim" id="form-dokter-pengirim" class="lis-form-control" placeholder="dr. ... Sp.PK">
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2.5">
                                    <div>
                                        <label class="lis-form-label">No. Surat Rujukan / SPK</label>
                                        <input type="text" name="nomor_surat_pengantar" id="form-no-pengantar" class="lis-form-control font-mono" placeholder="440/PKM/... atau No. SPK">
                                    </div>
                                    <div>
                                        <label class="lis-form-label">Jenis Spesimen / Sampel</label>
                                        <select name="jenis_sampel" id="form-jenis-sampel" class="lis-form-control font-medium">
                                            <option value="Darah Vena / Serum">Darah Vena / Serum</option>
                                            <option value="Darah EDTA (Hematologi)">Darah EDTA (Hematologi)</option>
                                            <option value="Urine Sewaktu / 24 Jam">Urine Sewaktu / 24 Jam</option>
                                            <option value="Swab Nasofaring / Orofaring">Swab Nasofaring / Orofaring</option>
                                            <option value="Feses Segar">Feses Segar</option>
                                            <option value="Air Bersih / Minum">Air Bersih / Minum (Kimia Fisika)</option>
                                            <option value="Air Limbah Industri">Air Limbah Industri</option>
                                            <option value="Makanan / Minuman">Makanan / Minuman (Mikrobiologi)</option>
                                            <option value="Lainnya">Lainnya / Sampel Campuran</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="lis-form-label">Diagnosa Awal / Catatan Medis Khusus</label>
                                    <input type="text" name="diagnosa_klinis" id="form-diagnosa" class="lis-form-control" placeholder="Contoh: Demam H-4, Cek Kolesterol, Uji Baku Mutu Limbah...">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- KOLOM KANAN (7 Kolom): PEMILIHAN PARAMETER & BILLING BLUD -->
                    <div class="lg:col-span-7 space-y-4">
                        
                        <!-- Panel Pemilihan Parameter & Template Uji di LIS -->
                        <div class="lis-section">
                            <div class="lis-section-header bg-emerald-50/80 border-b border-emerald-200">
                                <div class="lis-section-title text-emerald-950">
                                    <span class="lis-section-num bg-emerald-700 text-amber-300">3</span>
                                    <span>Pilih Parameter Uji & Paket Pemeriksaan Lab</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <label class="text-[10px] font-bold text-slate-500 uppercase">Tarif:</label>
                                    <select name="kelompok_tarif" id="form-kelompok-tarif" onchange="loadTariffsForForm()" class="lis-form-control text-xs h-7 py-0 px-2 w-auto font-bold text-emerald-900 bg-white">
                                        <option value="umum" selected>Umum / Reguler</option>
                                        <option value="perusahaan">Perusahaan Mitra</option>
                                        <option value="bpjs">BPJS Kesehatan</option>
                                        <option value="asuransi">Asuransi Swasta</option>
                                    </select>
                                </div>
                            </div>

                            <div class="lis-section-body space-y-3">
                                <!-- Search Parameter & Category Filter -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                    <div>
                                        <select id="select-category-filter" onchange="filterTariffList()" class="lis-form-control text-xs">
                                            <option value="">-- Semua Kategori Lab --</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?= html_escape($cat['category_name']) ?>"><?= html_escape($cat['category_name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="relative" id="tariff-search-wrapper">
                                        <input type="text" id="tariff-search-input" oninput="filterTariffList()" placeholder="Ketik nama tes (misal: Darah Lengkap, Glukosa)..." class="lis-form-control pl-8 pr-7 text-xs">
                                        <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-3 pointer-events-none"></i>
                                        <button type="button" id="btn-clear-tariff-search" onclick="clearTariffSearch()" class="hidden absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 p-0.5" title="Hapus pencarian">
                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                        </button>
                                        <!-- Autocomplete Suggestions -->
                                        <div id="tariff-search-suggestions" class="hidden absolute z-30 left-0 right-0 mt-1 bg-white rounded-xl shadow-2xl border border-emerald-200 overflow-hidden divide-y divide-slate-100 max-h-56 overflow-y-auto"></div>
                                    </div>
                                </div>

                                <!-- Box Pilihan Parameter & Template (Daftar Pilihan) -->
                                <div class="border border-slate-200 rounded-xl overflow-hidden bg-slate-50">
                                    <div class="p-2 border-b border-slate-200 bg-slate-100 flex items-center justify-between text-[11px] font-bold text-slate-600">
                                        <span>Katalog Pemeriksaan Lab Tersedia</span>
                                        <span class="text-[10px] text-slate-400">Klik [+] untuk menambahkan</span>
                                    </div>
                                    <div id="tariff-catalog-container" class="max-h-48 overflow-y-auto p-2 space-y-1.5 bg-white divide-y divide-slate-100">
                                        <div class="text-center py-4 text-xs text-slate-400">Memuat katalog pemeriksaan...</div>
                                    </div>
                                </div>

                                <!-- Box Keranjang Uji (Selected Items) -->
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                                            <i data-lucide="shopping-cart" class="w-3.5 h-3.5 text-emerald-700"></i>
                                            Daftar Uji Dipilih (<span id="cart-item-count">0</span> Item)
                                        </span>
                                        <button type="button" onclick="clearCart()" class="text-[10px] text-rose-600 hover:underline">Hapus Semua</button>
                                    </div>

                                    <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
                                        <table class="w-full text-xs text-left">
                                            <thead class="bg-slate-50 text-[10px] font-bold text-slate-500 uppercase border-b border-slate-200">
                                                <tr>
                                                    <th class="p-2">Item Pemeriksaan</th>
                                                    <th class="p-2">Kategori</th>
                                                    <th class="p-2 text-right">Tarif (Rp)</th>
                                                    <th class="p-2 text-center w-8">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="cart-table-body" class="divide-y divide-slate-100 text-xs">
                                                <tr>
                                                    <td colspan="4" class="text-center py-4 text-slate-400">Belum ada pemeriksaan yang dipilih.</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Panel Alur Pembayaran & Validasi Keuangan BLUD -->
                        <div class="lis-section">
                            <div class="lis-section-header bg-slate-100/90">
                                <div class="lis-section-title">
                                    <span class="lis-section-num bg-emerald-700 text-amber-300">4</span>
                                    <span>Skema Billing & Validasi Pembayaran</span>
                                </div>
                            </div>
                            <div class="lis-section-body space-y-3">
                                <!-- Pilihan 2 Jalur Transaksi Sesuai Diagram -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <label class="p-3 rounded-xl border-2 cursor-pointer transition-all flex items-start gap-2.5 bg-emerald-50/60 border-emerald-700" id="label-alur-langsung">
                                        <input type="radio" name="alur_pembayaran" value="mandiri_langsung" checked onchange="toggleAlurPembayaran('mandiri_langsung')" class="mt-0.5 text-emerald-700">
                                        <div>
                                            <span class="text-xs font-bold block text-emerald-950">Alur Langsung / Mandiri</span>
                                            <span class="text-[10px] text-emerald-800/80 block mt-0.5">Bayar di Kasir (Cash/QRIS/Debit) &bull; Status LIS: PAID</span>
                                        </div>
                                    </label>

                                    <label class="p-3 rounded-xl border-2 cursor-pointer transition-all flex items-start gap-2.5 bg-white border-slate-200" id="label-alur-piutang">
                                        <input type="radio" name="alur_pembayaran" value="piutang_pks" onchange="toggleAlurPembayaran('piutang_pks')" class="mt-0.5 text-emerald-700">
                                        <div>
                                            <span class="text-xs font-bold block text-slate-800">Alur Piutang / PKS Mitra</span>
                                            <span class="text-[10px] text-slate-500 block mt-0.5">Verifikasi SPK/PKS &bull; Status LIS: GUARANTEED</span>
                                        </div>
                                    </label>
                                </div>

                                <!-- Form Isian PKS Mitra (Terbuka saat piutang_pks) -->
                                <div id="box-piutang-pks" class="hidden bg-teal-50/60 p-3 rounded-xl border border-teal-200 space-y-2.5 text-xs">
                                    <div class="font-bold text-teal-950 flex items-center gap-1.5">
                                        <i data-lucide="file-check" class="w-4 h-4 text-teal-700"></i>
                                        <span>Data Verifikasi Penjaminan PKS / SPK Mitra</span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                        <div>
                                            <label class="lis-form-label">No. PKS / Kontrak <span class="req">*</span></label>
                                            <input type="text" name="nomor_pks_spk" id="form-no-pks" class="lis-form-control font-mono font-bold" placeholder="PKS/LABKES/2026/...">
                                        </div>
                                        <div>
                                            <label class="lis-form-label">Instansi Penjamin <span class="req">*</span></label>
                                            <input type="text" name="instansi_penjamin" id="form-instansi-penjamin" class="lis-form-control font-semibold" placeholder="PT. ... / Dinas ...">
                                        </div>
                                        <div>
                                            <label class="lis-form-label">Nama PIC Mitra</label>
                                            <input type="text" name="pic_penjamin" id="form-pic-penjamin" class="lis-form-control" placeholder="Nama Koordinator">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="lis-form-label">Catatan Penjaminan / No. Surat Pengantar</label>
                                        <input type="text" name="catatan_penjamin" id="form-catatan-penjamin" class="lis-form-control" placeholder="Tagihan dikirimkan akhir bulan via rekening BLUD...">
                                    </div>
                                </div>

                                <!-- Ringkasan Kalkulasi Billing ID -->
                                <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 space-y-1.5 text-xs font-mono">
                                    <div class="flex justify-between text-slate-600">
                                        <span>Subtotal Tarif Pemeriksaan:</span>
                                        <span id="disp-subtotal" class="font-bold">Rp 0</span>
                                    </div>
                                    <div class="flex justify-between items-center text-slate-600">
                                        <span class="flex items-center gap-1">
                                            Diskon Mitra (%):
                                            <input type="number" step="0.1" min="0" max="100" name="diskon_persen" id="form-diskon-persen" oninput="calculateBilling()" value="0" class="w-16 h-6 px-1.5 font-mono text-xs border rounded text-right">
                                        </span>
                                        <span id="disp-diskon" class="font-bold text-rose-600">- Rp 0</span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm font-black text-slate-900 border-t border-slate-200 pt-1.5">
                                        <span class="font-sans text-xs uppercase tracking-wider text-emerald-900">Total Tagihan Billing ID:</span>
                                        <span id="disp-total" class="text-base text-emerald-800 font-extrabold">Rp 0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- Footer Modal -->
                <div class="lis-modal-footer px-5 py-3.5 border-t border-slate-200 bg-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 flex-shrink-0">
                    <div class="text-[11px] text-slate-500 flex items-center gap-2">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-700 flex-shrink-0"></i>
                        <span>Standar SIMRS LIS UPT Laboratorium Kesehatan Provinsi Riau</span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <button type="button" onclick="closeOrderModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-100 transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold shadow-md shadow-emerald-700/25 transition-all flex items-center gap-2 border border-emerald-600/40">
                            <i data-lucide="check-circle-2" class="w-4 h-4 text-amber-300"></i>
                            <span>Terbitkan Order LIS & Billing</span>
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: PENDAFTARAN CEPAT MASTER KLIEN / PASIEN BARU                       -->
<!-- ========================================================================= -->
<div id="modal-quick-client" class="lis-modal" style="z-index: 70;">
    <div class="lis-modal-box max-w-2xl w-full border border-emerald-500 shadow-2xl">
        <!-- Header Modal -->
        <div class="lis-modal-header bg-gradient-to-r from-emerald-950 via-emerald-800 to-teal-900 border-b border-amber-400/40">
            <div>
                <h3 class="lis-modal-title text-white">
                    <i data-lucide="user-plus" class="w-5 h-5 text-amber-300"></i>
                    <span>Daftarkan Master Klien / Pasien Baru</span>
                </h3>
                <p class="lis-modal-subtitle">Tersimpan permanen ke master database dan langsung otomatis terpilih ke formulir order</p>
            </div>
            <button type="button" onclick="closeQuickClientModal()" class="lis-modal-close" title="Tutup">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Body Modal -->
        <div class="lis-modal-body p-4 sm:p-5 space-y-4 bg-slate-50/70 max-h-[calc(90vh-140px)] overflow-y-auto">
            <form id="quick-client-form" onsubmit="saveQuickClient(event)" class="space-y-4">
                
                <!-- Toggle Tipe Klien: Perorangan vs Perusahaan/Faskes -->
                <div>
                    <label class="lis-form-label mb-1.5 flex items-center justify-between">
                        <span>Pilih Kategori Klien Baru <span class="req">*</span></span>
                        <span class="text-[10px] text-slate-400">Menentukan data identitas yang dicatat</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2.5">
                        <button type="button" onclick="setQuickClientType('perorangan')" id="btn-qc-perorangan" class="p-3 rounded-xl border-2 border-emerald-700 bg-emerald-50/80 text-emerald-950 text-left flex items-center gap-2.5 shadow-2xs transition-all">
                            <div class="w-8 h-8 rounded-lg bg-emerald-700 text-amber-300 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="user" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <span class="font-bold text-xs block leading-tight">Pasien Perorangan</span>
                                <span class="text-[10px] text-slate-500">Pasien umum, walk-in mandiri</span>
                            </div>
                        </button>
                        <button type="button" onclick="setQuickClientType('perusahaan')" id="btn-qc-perusahaan" class="p-3 rounded-xl border-2 border-slate-200 bg-white text-slate-700 text-left flex items-center gap-2.5 shadow-2xs hover:bg-slate-50 transition-all">
                            <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="building-2" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <span class="font-bold text-xs block leading-tight">Perusahaan / Faskes Mitra</span>
                                <span class="text-[10px] text-slate-500">PT Rekanan, Puskesmas, RS, Lab</span>
                            </div>
                        </button>
                    </div>
                    <input type="hidden" name="client_type" id="qc-client-type" value="perorangan">
                </div>

                <!-- Bagian Form Bersama / Dinamis -->
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs space-y-3">
                    
                    <!-- Nama Klien -->
                    <div>
                        <label class="lis-form-label" id="qc-label-name">Nama Lengkap Pasien <span class="req">*</span></label>
                        <input type="text" name="name" id="qc-name" required class="lis-form-control font-bold text-slate-900" placeholder="Ketik nama lengkap sesuai KTP / Dokumen Resmi">
                    </div>

                    <!-- Fields Khusus Perorangan -->
                    <div id="qc-fields-perorangan" class="space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="lis-form-label">NIK / KTP (16 Digit)</label>
                                <input type="text" name="identity_number" id="qc-nik" maxlength="20" class="lis-form-control font-mono" placeholder="16 digit NIK">
                            </div>
                            <div>
                                <label class="lis-form-label">Jenis Kelamin</label>
                                <select name="gender" id="qc-gender" class="lis-form-control font-medium">
                                    <option value="L">Laki-laki (L)</option>
                                    <option value="P">Perempuan (P)</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="lis-form-label">Tanggal Lahir</label>
                                <input type="date" name="birth_date" id="qc-birth-date" class="lis-form-control font-medium">
                            </div>
                            <div>
                                <label class="lis-form-label">Golongan Darah</label>
                                <select name="blood_type" id="qc-blood-type" class="lis-form-control font-medium">
                                    <option value="">- Tidak Tahu / Belum Cek -</option>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="AB">AB</option>
                                    <option value="O">O</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Fields Khusus Perusahaan / Faskes Mitra -->
                    <div id="qc-fields-perusahaan" class="space-y-3 hidden">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="lis-form-label">No. NPWP / Izin Faskes</label>
                                <input type="text" name="identity_number_corp" id="qc-npwp" class="lis-form-control font-mono" placeholder="NPWP Badan / Izin Operasional">
                            </div>
                            <div>
                                <label class="lis-form-label">Diskon Kerjasama PKS (%)</label>
                                <input type="number" name="contract_discount" id="qc-discount" min="0" max="100" step="0.5" value="0" class="lis-form-control font-mono font-bold" placeholder="0 - 100%">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <div>
                                <label class="lis-form-label">Nama Kontak Person (PIC)</label>
                                <input type="text" name="pic_name" id="qc-pic-name" class="lis-form-control" placeholder="Nama Koordinator / Manajer">
                            </div>
                            <div>
                                <label class="lis-form-label">No. HP PIC / WhatsApp</label>
                                <input type="tel" name="pic_phone" id="qc-pic-phone" class="lis-form-control font-mono" placeholder="08...">
                            </div>
                        </div>

                        <div>
                            <label class="lis-form-label">Termin Pembayaran / Penagihan</label>
                            <select name="payment_term" id="qc-payment-term" class="lis-form-control font-medium">
                                <option value="Tunai / Mandiri">Tunai / Mandiri per Transaksi</option>
                                <option value="Kredit 14 Hari">Kredit / Piutang 14 Hari</option>
                                <option value="Kredit 30 Hari" selected>Kredit / Piutang 30 Hari (Bulanan)</option>
                                <option value="Kredit 45 Hari">Kredit / Piutang 45 Hari</option>
                                <option value="Kredit 60 Hari">Kredit / Piutang 60 Hari</option>
                            </select>
                        </div>
                    </div>

                    <!-- Kontak & Alamat -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="lis-form-label">No. Telepon / WhatsApp <span class="req">*</span></label>
                            <input type="tel" name="phone" id="qc-phone" required class="lis-form-control font-mono font-bold" placeholder="081234567890">
                        </div>
                        <div>
                            <label class="lis-form-label">Email</label>
                            <input type="email" name="email" id="qc-email" class="lis-form-control" placeholder="alamat@email.com">
                        </div>
                    </div>

                    <div>
                        <label class="lis-form-label">Alamat Lengkap & Kota/Kabupaten</label>
                        <textarea name="address" id="qc-address" rows="2" class="lis-form-control" placeholder="Jalan, RT/RW, Kelurahan, Kecamatan, Kota/Kabupaten"></textarea>
                    </div>

                </div>

                <!-- Footer Modal -->
                <div class="pt-3 border-t border-slate-200 flex items-center justify-between gap-2">
                    <button type="button" onclick="closeQuickClientModal()" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-100 transition-colors">
                        Batal
                    </button>
                    <button type="submit" id="btn-save-quick-client" class="px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-1.5 shadow-md shadow-emerald-900/10 border border-emerald-600 transition-all">
                        <i data-lucide="check" class="w-4 h-4 text-amber-300"></i>
                        <span>Simpan &amp; Pilih Klien</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: KASIR TERINTEGRASI AKUN BAYAR (KAS/BANK) & AKUN PIUTANG (MITRA)  -->
<!-- ========================================================================= -->
<div id="modal-kasir" class="lis-modal">
    <div class="lis-modal-box max-w-xl w-full">
        <div class="lis-modal-header bg-gradient-to-r from-emerald-900 via-emerald-800 to-teal-900 border-b border-amber-400/40">
            <div>
                <h3 class="lis-modal-title text-white">
                    <i data-lucide="receipt" class="w-5 h-5 text-amber-300"></i>
                    <span>Kasir Pembayaran Jasa Laboratorium BLUD</span>
                </h3>
                <p class="lis-modal-subtitle">Terhubung Akun Bayar (Kas & Bank) dan Akun Piutang (Mitra Penjamin)</p>
            </div>
            <button type="button" onclick="closeKasirModal()" class="lis-modal-close">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="lis-modal-body p-5 space-y-4 bg-white">
            <input type="hidden" id="kasir-reg-id">
            <input type="hidden" id="kasir-current-tab" value="bayar">

            <!-- Ringkasan Order & Tagihan -->
            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 p-3.5 rounded-xl border border-emerald-200/80 space-y-1.5 text-xs shadow-2xs">
                <div class="flex justify-between items-center">
                    <span class="text-slate-500">No. Registrasi & Billing:</span>
                    <span id="kasir-reg-num" class="font-mono font-bold text-slate-800"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-500">Nama Pasien / Subjek:</span>
                    <span id="kasir-patient-name" class="font-bold text-slate-900"></span>
                </div>
                <div class="flex justify-between items-center border-t border-emerald-200/70 pt-1.5 mt-1 text-sm font-bold">
                    <span class="text-emerald-950">Total Tagihan Pemeriksaan:</span>
                    <span id="kasir-total-tagihan" class="text-emerald-800 font-black font-mono text-base"></span>
                </div>
            </div>

            <!-- TAB SWITCHER: AKUN BAYAR VS AKUN PIUTANG -->
            <div class="flex p-1 bg-slate-100 rounded-xl border border-slate-200">
                <button type="button" id="tab-btn-kasir-bayar" onclick="switchKasirTab('bayar')" class="flex-1 py-2 px-3 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5 bg-emerald-700 text-white shadow-xs">
                    <i data-lucide="wallet-cards" class="w-4 h-4 text-amber-300"></i>
                    <span>Akun Bayar (Kas & Bank)</span>
                </button>
                <button type="button" id="tab-btn-kasir-piutang" onclick="switchKasirTab('piutang')" class="flex-1 py-2 px-3 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 transition-all flex items-center justify-center gap-1.5">
                    <i data-lucide="handshake" class="w-4 h-4"></i>
                    <span>Akun Piutang (Mitra / PKS)</span>
                </button>
            </div>

            <!-- PANEL 1: AKUN BAYAR (KAS & BANK BLUD -> PAID) -->
            <div id="panel-kasir-bayar" class="space-y-3.5">
                <div>
                    <label class="lis-form-label flex items-center justify-between">
                        <span>Pilih Channel Akun Bayar (COA Kas / Bank) <span class="req">*</span></span>
                        <span class="text-[10px] text-emerald-800 font-bold">Master Akun Bayar BLUD</span>
                    </label>
                    <select id="kasir-akun-bayar-id" onchange="onAkunBayarChange()" class="lis-form-control text-xs font-bold text-slate-800">
                        <?php if (!empty($akun_bayar_list)): ?>
                            <?php foreach ($akun_bayar_list as $ab): ?>
                                <option value="<?= $ab['id'] ?>" 
                                        data-admin="<?= (float)$ab['biaya_admin'] ?>" 
                                        data-tipe="<?= html_escape($ab['tipe_transaksi']) ?>" 
                                        data-bank="<?= html_escape($ab['nama_bank']) ?>"
                                        data-norek="<?= html_escape($ab['nomor_rekening_bank']) ?>"
                                        data-coa="<?= html_escape($ab['kode_rekening']) ?>"
                                        data-nama="<?= html_escape($ab['nama_bayar']) ?>">
                                    <?= html_escape($ab['nama_bayar']) ?> &bull; COA: <?= html_escape($ab['kode_rekening']) ?> <?= !empty($ab['nama_bank']) ? ' (' . html_escape($ab['nama_bank']) . ')' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">-- Belum ada master akun bayar aktif --</option>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Detail Metadata Akun Bayar -->
                <div id="kasir-bayar-meta-box" class="p-2.5 bg-slate-50 rounded-xl border border-slate-200 text-[11px] space-y-1">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Rekening COA BLUD:</span>
                        <span id="kasir-disp-coa" class="font-mono font-bold text-slate-800">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Bank & No. Rekening:</span>
                        <span id="kasir-disp-bank" class="font-bold text-slate-800">-</span>
                    </div>
                    <div class="flex justify-between border-t border-slate-200 pt-1">
                        <span class="text-slate-500">Biaya Administrasi:</span>
                        <span id="kasir-disp-admin" class="font-bold text-emerald-800">Rp 0</span>
                    </div>
                </div>

                <!-- Kalkulasi Nominal Bayar & Kembalian -->
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <label class="lis-form-label mb-0">Uang Diterima Kasir (Rp) <span class="req">*</span></label>
                        <div class="flex gap-1">
                            <button type="button" onclick="setNominalPas()" class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200">Uang Pas</button>
                            <button type="button" onclick="addNominal(50000)" class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200">+50rb</button>
                            <button type="button" onclick="addNominal(100000)" class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200">+100rb</button>
                        </div>
                    </div>
                    <div class="relative">
                        <input type="number" id="kasir-nominal-bayar" oninput="calcKembalian()" class="lis-form-control text-sm font-mono font-black pl-9 text-emerald-950">
                        <span class="absolute left-3 top-2 font-bold text-slate-400 text-xs">Rp</span>
                    </div>

                    <div class="flex justify-between items-center p-2.5 bg-emerald-50/60 rounded-xl border border-emerald-200 text-xs">
                        <span class="font-semibold text-slate-600">Uang Kembalian Pasien:</span>
                        <span id="kasir-disp-kembalian" class="font-mono font-black text-emerald-900 text-sm">Rp 0</span>
                    </div>
                </div>
            </div>

            <!-- PANEL 2: AKUN PIUTANG (PENJAMIN / PKS MITRA -> GUARANTEED) -->
            <div id="panel-kasir-piutang" class="space-y-3.5 hidden">
                <div>
                    <label class="lis-form-label flex items-center justify-between">
                        <span>Pilih Akun Piutang / Penjamin (COA Piutang) <span class="req">*</span></span>
                        <span class="text-[10px] text-teal-800 font-bold">Master Mitra Penjamin</span>
                    </label>
                    <select id="kasir-akun-piutang-id" onchange="onAkunPiutangChange()" class="lis-form-control text-xs font-bold text-slate-800">
                        <?php if (!empty($akun_piutang_list)): ?>
                            <?php foreach ($akun_piutang_list as $ap): ?>
                                <option value="<?= $ap['id'] ?>"
                                        data-tempo="<?= (int)$ap['jatuh_tempo_hari'] ?>"
                                        data-kategori="<?= html_escape($ap['kategori_penjamin']) ?>"
                                        data-coa="<?= html_escape($ap['kode_rekening']) ?>"
                                        data-kontak="<?= html_escape($ap['kontak_person']) ?>"
                                        data-nama="<?= html_escape($ap['nama_piutang']) ?>">
                                    <?= html_escape($ap['nama_piutang']) ?> &bull; COA: <?= html_escape($ap['kode_rekening']) ?> (Tempo: <?= $ap['jatuh_tempo_hari'] ?> Hari)
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">-- Belum ada master akun piutang aktif --</option>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Detail Metadata Akun Piutang -->
                <div id="kasir-piutang-meta-box" class="p-2.5 bg-teal-50/60 rounded-xl border border-teal-200 text-[11px] space-y-1">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Rekening COA Piutang:</span>
                        <span id="kasir-disp-piutang-coa" class="font-mono font-bold text-slate-800">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Kategori & Jatuh Tempo:</span>
                        <span id="kasir-disp-piutang-tempo" class="font-bold text-slate-800">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Kontak Person (PIC):</span>
                        <span id="kasir-disp-piutang-pic" class="font-semibold text-slate-800">-</span>
                    </div>
                </div>

                <div>
                    <label class="lis-form-label">Nomor Kontrak PKS / Surat Jaminan / SPK / SEP <span class="req">*</span></label>
                    <input type="text" id="kasir-pks-nomor" class="lis-form-control font-mono font-bold text-xs" placeholder="Contoh: PKS/LABKES/2026/088 atau SEP-BPJS-01928">
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="lis-form-label">Nama PIC / Pejabat Penjamin</label>
                        <input type="text" id="kasir-pks-pic" class="lis-form-control text-xs" placeholder="Nama Koordinator / Manajer">
                    </div>
                    <div>
                        <label class="lis-form-label">Catatan Penagihan / Termin</label>
                        <input type="text" id="kasir-pks-catatan" class="lis-form-control text-xs" placeholder="Ditagihkan berkala...">
                    </div>
                </div>
            </div>
        </div>

        <div class="lis-modal-footer px-5 py-3 border-t border-slate-200 bg-slate-50 flex items-center justify-between">
            <button type="button" onclick="closeKasirModal()" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-100">Batal</button>
            <button type="button" id="kasir-btn-submit" onclick="submitKasirPayment()" class="px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold shadow-md shadow-emerald-700/20 flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4 text-amber-300"></i>
                <span id="kasir-btn-submit-text">Konfirmasi Lunas (PAID)</span>
            </button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: INPUT HASIL PEMERIKSAAN LAB (STANDAR KAN ISO 17025)              -->
<!-- ========================================================================= -->
<div id="modal-input-hasil" class="lis-modal">
    <div class="lis-modal-box max-w-5xl w-full">
        <div class="lis-modal-header bg-gradient-to-r from-teal-900 via-emerald-800 to-slate-900 border-b border-amber-400/40">
            <div>
                <h3 class="lis-modal-title text-white">
                    <i data-lucide="flask-conical" class="w-5 h-5 text-amber-300"></i>
                    <span>Input & Verifikasi Hasil Uji Laboratorium</span>
                </h3>
                <p class="lis-modal-subtitle">Pencatatan Parameter Uji, Nilai Rujukan, Evaluasi Flag & Kesimpulan Klinis</p>
            </div>
            <button type="button" onclick="closeInputHasilModal()" class="lis-modal-close">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="lis-modal-body p-5 space-y-4 bg-white max-h-[75vh] overflow-y-auto">
            <input type="hidden" id="hasil-reg-id">

            <!-- Strip Info Pasien & Sampel -->
            <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-200 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">No. Registrasi</span>
                    <span id="hasil-disp-noreg" class="font-mono font-bold text-slate-800 text-xs"></span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Nama Pasien / RM</span>
                    <span id="hasil-disp-pasien" class="font-bold text-slate-900 text-xs"></span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Barcode Sampel</span>
                    <span id="hasil-disp-barcode" class="font-mono font-bold px-2 py-0.5 rounded bg-emerald-50 text-emerald-900 border border-emerald-200 text-xs inline-block"></span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Status Order</span>
                    <span id="hasil-disp-status" class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold uppercase"></span>
                </div>
            </div>

            <!-- Tabel Dinamis Parameter Uji & Hasil -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="clipboard-list" class="w-4 h-4 text-teal-700"></i>
                        <span>Daftar Parameter Uji Laboratorium</span>
                    </h4>
                    <span class="text-[11px] text-slate-500">Ketik hasil uji numerik / kualitatif; sistem otomatis menyarankan flag normal/abnormal.</span>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-xs text-left" id="table-input-hasil">
                        <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="p-2.5 text-center w-10">No</th>
                                <th class="p-2.5 min-w-[200px]">Parameter Uji / Metode</th>
                                <th class="p-2.5 min-w-[140px]">Nilai Rujukan & Satuan</th>
                                <th class="p-2.5 min-w-[180px]">Nilai Hasil Uji <span class="text-rose-500">*</span></th>
                                <th class="p-2.5 w-32">Flag Status</th>
                                <th class="p-2.5 min-w-[150px]">Catatan / Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="hasil-tbody" class="divide-y divide-slate-200 bg-white">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pengesahan, Catatan Klinis & Status -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                <!-- Dokter PJ & Analis -->
                <div class="space-y-3 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                    <div>
                        <label class="lis-form-label">Dokter Penanggung Jawab Lab (Sp.PK) <span class="req">*</span></label>
                        <input type="text" id="hasil-dokter-pj" class="lis-form-control text-xs font-bold text-slate-800" value="<?= html_escape($default_dokter_pj ?? 'dr. Hj. Siti Rahmah, Sp.PK, M.Kes') ?>">
                    </div>
                    <div>
                        <label class="lis-form-label">Analis / Petugas Pemeriksa Lab <span class="req">*</span></label>
                        <input type="text" id="hasil-analis-nama" class="lis-form-control text-xs font-semibold text-slate-800" placeholder="Nama Petugas Analis">
                    </div>
                    <div>
                        <label class="lis-form-label">Tujuan Alur Order</label>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <label class="p-2 rounded-xl border border-amber-300 flex items-center gap-2 cursor-pointer hover:bg-amber-50 bg-amber-50/50">
                                <input type="radio" name="hasil_status_order" value="validasi" checked class="text-amber-700">
                                <span class="font-bold text-amber-900">Validasi Sp.PK</span>
                            </label>
                            <label class="p-2 rounded-xl border border-slate-200 flex items-center gap-2 cursor-pointer hover:bg-white bg-white">
                                <input type="radio" name="hasil_status_order" value="processing" class="text-teal-700">
                                <span class="font-bold text-slate-800">Draft Analis</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Kesimpulan Klinis -->
                <div class="space-y-2 bg-slate-50 p-3.5 rounded-xl border border-slate-200 flex flex-col justify-between">
                    <div>
                        <label class="lis-form-label flex justify-between items-center">
                            <span>Kesimpulan / Saran Klinis Dokter Patologi:</span>
                            <button type="button" onclick="setPresetKesimpulan()" class="text-[10px] text-teal-800 font-bold hover:underline">+ Preset Standar</button>
                        </label>
                        <textarea id="hasil-kesimpulan-saran" rows="5" class="lis-form-control text-xs" placeholder="Tulis kesimpulan hasil uji laboratorium atau anjuran pemeriksaan lanjutan jika ada..."></textarea>
                    </div>
                    <div class="text-[10px] text-slate-400">
                        * Status <strong>Selesai (Completed)</strong> menandakan hasil telah divalidasi dan siap dicetak resmi pada Lembar Hasil Pemeriksaan Laboratorium.
                    </div>
                </div>
            </div>
        </div>

        <div class="lis-modal-footer px-5 py-3 border-t border-slate-200 bg-slate-50 flex items-center justify-between">
            <button type="button" onclick="closeInputHasilModal()" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-100">Tutup</button>
            <div class="flex items-center gap-2">
                <button type="button" onclick="submitHasilLab(false)" class="px-4 py-2.5 rounded-xl bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold shadow-xs flex items-center gap-1.5">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Hasil Lab</span>
                </button>
                <button type="button" onclick="submitHasilLab(true)" class="px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold shadow-md shadow-emerald-700/20 flex items-center gap-1.5">
                    <i data-lucide="printer" class="w-4 h-4 text-amber-300"></i>
                    <span>Simpan & Cetak Hasil</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 4: DETAIL PENDAFTARAN & AUDIT TRAIL LOG                             -->
<!-- ========================================================================= -->
<div id="modal-detail" class="lis-modal">
    <div class="lis-modal-box max-w-4xl w-full">
        <div class="lis-modal-header bg-gradient-to-r from-slate-900 via-emerald-950 to-slate-900 border-b border-amber-400/40">
            <div>
                <h3 class="lis-modal-title text-white">
                    <i data-lucide="file-text" class="w-5 h-5 text-amber-300"></i>
                    <span id="detail-title">Detail Registrasi & Status Order LIS</span>
                </h3>
                <p class="lis-modal-subtitle">Tracking Alur Pemeriksaan & Audit Trail Log Sistem</p>
            </div>
            <button type="button" onclick="closeDetailModal()" class="lis-modal-close">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="lis-modal-body p-5 space-y-4 bg-slate-50" id="detail-content-body">
            <!-- Dynamic content loaded via JS -->
        </div>

        <div class="lis-modal-footer px-5 py-3 border-t border-slate-200 bg-white flex items-center justify-between">
            <div id="detail-print-buttons" class="flex items-center gap-2"></div>
            <button type="button" onclick="closeDetailModal()" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-100">Tutup</button>
        </div>
    </div>
</div>

<!-- Backdrop modal -->
<div id="modal-backdrop" class="hidden fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-xs"></div>

<!-- ========================================================================= -->
<!-- JAVASCRIPT LOGIC MODUL PENDAFTARAN & ORDER LIS                            -->
<!-- ========================================================================= -->
<script>
    let currentFilterType = 'all';
    let table = null;
    let availableTariffs = { parameters: [], templates: [] };
    let cartItems = [];
    let currentTotalTagihan = 0;

    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
        initTable();
        loadSummary();
    });

    function initTable() {
        table = $('#table-pendaftaran').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?= site_url('pendaftaran/fetch') ?>',
                type: 'GET',
                data: function(d) {
                    d.tipe_pasien = currentFilterType;
                    d.status_bayar = $('#filter-status-bayar').val();
                    d.status_order = <?= !empty($is_sampling_queue) ? "'sampling'" : "''" ?>;
                    d.tanggal = $('#filter-tanggal').val();
                }
            },
            columns: [
                { data: 'no_urut', orderable: false, className: 'text-center text-slate-400 font-medium py-3' },
                {
                    data: null,
                    render: function(d) {
                        return `<div>
                            <span class="font-mono font-bold text-slate-900 block leading-tight">${d.nomor_registrasi}</span>
                            <span class="font-mono text-[10px] text-slate-400 block">${d.nomor_billing}</span>
                            <span class="text-[10px] text-slate-400">${d.tanggal_format}</span>
                        </div>`;
                    }
                },
                {
                    data: null,
                    render: function(d) {
                        const genderBadge = d.jenis_kelamin ? `<span class="px-1.5 py-0.2 rounded text-[9px] font-bold ${d.jenis_kelamin === 'L' ? 'bg-sky-100 text-sky-800' : 'bg-rose-100 text-rose-800'}">${d.jenis_kelamin}</span>` : '';
                        const ageStr = d.usia_tahun ? `${d.usia_tahun} Th` : '';
                        return `<div>
                            <span class="font-bold text-slate-900 block leading-tight">${d.nama_pasien}</span>
                            <div class="flex items-center gap-1.5 text-[10px] text-slate-500 mt-0.5">
                                ${genderBadge}
                                <span>${ageStr}</span>
                                <span>&bull;</span>
                                <span class="font-mono">${d.telepon}</span>
                            </div>
                        </div>`;
                    }
                },
                {
                    data: null,
                    render: function(d) {
                        let typeBadge = '';
                        if (d.tipe_pasien === 'mandiri') {
                            typeBadge = '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">Mandiri (Walk-in)</span>';
                        } else if (d.tipe_pasien === 'rujukan_faskes') {
                            typeBadge = '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-800 border border-sky-200">Rujukan Faskes</span>';
                        } else {
                            typeBadge = '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-teal-50 text-teal-800 border border-teal-200">Mitra / Lingkungan</span>';
                        }
                        const sender = d.instansi_pengirim ? `<span class="text-[10px] text-slate-500 block truncate max-w-[140px] mt-0.5">${d.instansi_pengirim}</span>` : '';
                        return `<div>${typeBadge}${sender}</div>`;
                    }
                },
                {
                    data: 'total_items',
                    className: 'text-center py-3',
                    render: function(data) {
                        return `<span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-100 font-bold text-slate-700 text-xs">${data}</span>`;
                    }
                },
                {
                    data: null,
                    className: 'text-right font-mono py-3',
                    render: function(d) {
                        return `<span class="font-bold text-slate-900 block">${d.total_format}</span>
                                <span class="text-[10px] text-slate-400 capitalize">${d.kelompok_tarif}</span>`;
                    }
                },
                {
                    data: null,
                    className: 'text-center py-3',
                    render: function(d) {
                        if (d.status_pembayaran === 'PAID') {
                            return `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs">
                                <i data-lucide="check" class="w-3 h-3"></i> LUNAS (PAID)
                            </span>`;
                        } else if (d.status_pembayaran === 'GUARANTEED') {
                            return `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-teal-100 text-teal-800 border border-teal-300 shadow-2xs">
                                <i data-lucide="shield-check" class="w-3 h-3"></i> DIJAMIN PKS
                            </span>`;
                        } else if (d.status_pembayaran === 'CANCELLED') {
                            return `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">DIBATALKAN</span>`;
                        } else {
                            return `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300 shadow-2xs animate-pulse">
                                <i data-lucide="clock" class="w-3 h-3"></i> MENUNGGU KASIR
                            </span>`;
                        }
                    }
                },
                {
                    data: null,
                    className: 'text-center py-3',
                    render: function(d) {
                        const isUnlocked = (d.status_pembayaran === 'PAID' || d.status_pembayaran === 'GUARANTEED');
                        const barcodeColor = isUnlocked ? 'bg-emerald-50 text-emerald-950 border-emerald-200' : 'bg-slate-100 text-slate-400 border-slate-200';
                        return `<div>
                            <span class="inline-block font-mono text-[10px] font-bold px-2 py-0.5 rounded border ${barcodeColor}">${d.barcode_sampel}</span>
                            <span class="text-[9px] uppercase tracking-wider block font-semibold text-slate-400 mt-0.5">${d.status_order}</span>
                        </div>`;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    className: 'text-center py-3',
                    render: function(d) {
                        const isSamplingMode = <?= $isSampling ? 'true' : 'false' ?>;

                        // Tombol Cetak Label Barcode Tabung Sampel
                        const btnPrintBarcode = `<a href="<?= site_url('pendaftaran/cetak_barcode') ?>/${d.id}" target="_blank" class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 text-[10px] font-bold transition-all shadow-2xs flex items-center gap-1" title="Cetak Label Barcode Tabung Sampel">
                            <i data-lucide="scan-barcode" class="w-3.5 h-3.5"></i>
                            <span class="hidden xl:inline text-[9px]">Label</span>
                        </a>`;

                        // Tombol Detail & Audit Trail
                        const btnDetail = `<button type="button" onclick="viewDetail(${d.id})" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10px] transition-all shadow-2xs" title="Detail Spesimen & Riwayat Log">
                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                        </button>`;

                        // KHUSUS MENU: SAMPLING & ANTRIAN LAB (Fokus Pengambilan / Penerimaan Sampel)
                        if (isSamplingMode) {
                            let btnSampling = '';
                            if (d.status_order === 'registered' || d.status_order === 'sampling') {
                                btnSampling = `<button type="button" onclick="ambilSampel(${d.id})" class="px-2.5 py-1.5 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-[10px] font-bold flex items-center gap-1.5 shadow-xs transition-all border border-emerald-600" title="Ambil / Terima Sampel Pasien & Teruskan ke Laboratorium">
                                    <i data-lucide="test-tube-2" class="w-3.5 h-3.5 text-amber-300"></i>
                                    <span>Ambil Sampel</span>
                                </button>`;
                            } else {
                                btnSampling = `<span class="px-2 py-1 rounded-lg bg-emerald-50 text-emerald-800 text-[10px] font-bold border border-emerald-200 flex items-center gap-1" title="Sampel telah diambil dan sedang diproses di laboratorium">
                                    <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                                    <span>Di Laboratorium</span>
                                </span>`;
                            }

                            return `<div class="flex items-center justify-center gap-1.5 flex-wrap">
                                ${btnSampling}
                                ${btnPrintBarcode}
                                ${btnDetail}
                            </div>`;
                        }

                        // MENU PENDAFTARAN & ORDER LIS (Front-Office & Billing)
                        // 1. Kasir Pembayaran (Jika UNPAID) atau Cetak Kuitansi (Jika PAID / GUARANTEED)
                        let btnKasir = '';
                        if (d.status_pembayaran === 'UNPAID') {
                            btnKasir = `<button type="button" onclick="openKasirModal(${d.id})" class="p-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-slate-950 text-[10px] font-black transition-all shadow-2xs flex items-center gap-1 animate-pulse" title="Bayar Kasir (Akun Bayar / Akun Piutang)">
                                <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                                <span class="hidden xl:inline text-[9px]">Kasir</span>
                            </button>`;
                        } else {
                            btnKasir = `<a href="<?= site_url('pendaftaran/cetak_kuitansi') ?>/${d.id}" target="_blank" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10px] font-semibold transition-all shadow-2xs flex items-center gap-1" title="Cetak Kuitansi Billing Kasir">
                                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                <span class="hidden xl:inline text-[9px]">Kuitansi</span>
                            </a>`;
                        }

                        // 2. Cetak Lembar Order LIS
                        const btnPrintOrder = `<a href="<?= site_url('pendaftaran/cetak_order') ?>/${d.id}" target="_blank" class="p-1.5 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-800 border border-sky-200 text-[10px] font-bold transition-all shadow-2xs flex items-center gap-1" title="Cetak Lembar Dokumen Order LIS">
                            <i data-lucide="file-check" class="w-3.5 h-3.5 text-sky-700"></i>
                            <span class="hidden xl:inline text-[9px]">Order</span>
                        </a>`;

                        // 3. Cetak Hasil Lab (jika sudah completed)
                        let btnPrintHasil = '';
                        if (d.status_order === 'completed') {
                            btnPrintHasil = `<a href="<?= site_url('pendaftaran/cetak_hasil') ?>/${d.id}" target="_blank" class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-900 border border-emerald-200 text-[10px] font-bold transition-all shadow-2xs flex items-center gap-1" title="Cetak Lembar Resmi Hasil Uji Lab">
                                <i data-lucide="file-text" class="w-3.5 h-3.5 text-emerald-700"></i>
                                <span class="hidden xl:inline text-[9px]">Hasil</span>
                            </a>`;
                        }

                        return `<div class="flex items-center justify-center gap-1 flex-wrap">
                            ${btnKasir}
                            ${btnPrintBarcode}
                            ${btnPrintOrder}
                            ${btnPrintHasil}
                            ${btnDetail}
                        </div>`;
                    }
                }
            ],
            drawCallback: function() {
                if (window.lucide) lucide.createIcons();
            }
        });
    }

    function reloadTable() {
        if (table) table.ajax.reload(null, false);
        loadSummary();
    }

    function ambilSampel(id) {
        Swal.fire({
            title: 'Konfirmasi Pengambilan Sampel',
            text: 'Apakah spesimen pasien ini sudah diambil/diterima dan siap diserahkan ke meja laboratorium?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#047857',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Sampel Diterima',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('status_order', 'processing');
                formData.append('catatan', 'Spesimen telah diambil dan diserahkan ke analis laboratorium');

                fetch('<?= site_url('pendaftaran/update_status') ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        LIS.alert('Berhasil', 'Spesimen berhasil diterima! Status diperbarui ke tahap pengujian lab (Processing).', 'success');
                        reloadTable();
                    } else {
                        LIS.alert('Gagal', res.message || 'Gagal memperbarui status spesimen.', 'error');
                    }
                })
                .catch(err => LIS.showError('Error Pendaftaran', err, 'Terjadi kesalahan sistem saat menghubungi server.'));
            }
        });
    }

    function setFilterType(type) {
        currentFilterType = type;
        ['all', 'mandiri', 'rujukan', 'perusahaan'].forEach(t => {
            const btn = document.getElementById('tab-type-' + t);
            if (!btn) return;
            const match = (t === 'all' && type === 'all') || (t === 'mandiri' && type === 'mandiri') || (t === 'rujukan' && type === 'rujukan_faskes') || (t === 'perusahaan' && type === 'perusahaan_lingkungan');
            if (match) {
                btn.className = 'filter-type-tab px-3.5 py-2 rounded-xl text-xs font-bold transition-all bg-emerald-700 text-amber-300 shadow-xs flex items-center gap-2';
            } else {
                btn.className = 'filter-type-tab px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-all flex items-center gap-2';
            }
        });
        reloadTable();
    }

    function loadSummary() {
        fetch('<?= site_url('pendaftaran/summary') ?>')
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    const s = res.summary;
                    document.getElementById('stat-total-hari-ini').textContent = s.total_hari_ini || 0;
                    document.getElementById('stat-unpaid').textContent = s.unpaid || 0;
                    document.getElementById('stat-paid').textContent = s.paid || 0;
                    document.getElementById('stat-guaranteed').textContent = s.guaranteed || 0;
                }
            });
    }

    // =========================================================================
    // MODAL FORM PENDAFTARAN & ORDER
    // =========================================================================
    function openOrderModal() {
        document.getElementById('order-form').reset();
        document.getElementById('form-client-id').value = '';
        cartItems = [];
        renderCart();
        clearPatientSearch();

        // Ambil kode registrasi, billing, dan barcode otomatis
        fetch('<?= site_url('pendaftaran/get_next_codes') ?>')
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    document.getElementById('form-no-reg').value = res.codes.nomor_registrasi;
                    document.getElementById('form-no-billing').value = res.codes.nomor_billing;
                    document.getElementById('form-no-barcode').value = res.codes.barcode_sampel;

                    const dReg = document.getElementById('disp-no-reg');
                    const dBil = document.getElementById('disp-no-billing');
                    const dBar = document.getElementById('disp-no-barcode');
                    if (dReg) dReg.textContent = res.codes.nomor_registrasi;
                    if (dBil) dBil.textContent = res.codes.nomor_billing;
                    if (dBar) dBar.textContent = res.codes.barcode_sampel;
                }
            });

        setFormTipe('mandiri');
        loadTariffsForForm();

        const modal = document.getElementById('modal-order');
        const backdrop = document.getElementById('modal-backdrop');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        backdrop.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        if (window.lucide) lucide.createIcons();
    }

    function closeOrderModal() {
        const modal = document.getElementById('modal-order');
        const backdrop = document.getElementById('modal-backdrop');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        backdrop.classList.add('hidden');
        document.body.style.overflow = '';
    }

    function setFormTipe(tipe) {
        document.getElementById('form-tipe-pasien').value = tipe;
        const types = [
            { key: 'mandiri', target: 'mandiri' },
            { key: 'rujukan', target: 'rujukan_faskes' },
            { key: 'perusahaan', target: 'perusahaan_lingkungan' }
        ];

        types.forEach(t => {
            const btn = document.getElementById('btn-tipe-' + t.key);
            const icon = document.getElementById('icon-tipe-' + t.key);
            const check = document.getElementById('badge-check-' + t.key);
            const isMatch = (tipe === t.target);

            if (btn) {
                if (isMatch) {
                    btn.className = 'relative p-3.5 rounded-xl border-2 border-emerald-700 bg-emerald-50/70 text-emerald-950 text-left transition-all flex items-start gap-3 shadow-xs';
                } else {
                    btn.className = 'relative p-3.5 rounded-xl border-2 border-slate-200 bg-white hover:bg-slate-50/80 text-slate-700 text-left transition-all flex items-start gap-3';
                }
            }
            if (icon) {
                if (isMatch) {
                    icon.className = 'w-10 h-10 rounded-xl bg-emerald-700 text-amber-300 flex items-center justify-center flex-shrink-0 shadow-xs transition-colors';
                } else {
                    icon.className = 'w-10 h-10 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center flex-shrink-0 transition-colors';
                }
            }
            if (check) {
                if (isMatch) {
                    check.classList.remove('hidden');
                } else {
                    check.classList.add('hidden');
                }
            }
        });

        // Toggle radio alur pembayaran
        if (tipe === 'perusahaan_lingkungan') {
            const radPiutang = document.querySelector('input[name="alur_pembayaran"][value="piutang_pks"]');
            if (radPiutang) radPiutang.checked = true;
            toggleAlurPembayaran('piutang_pks');
            document.getElementById('form-kelompok-tarif').value = 'perusahaan';
            loadTariffsForForm();
        } else {
            const radMandiri = document.querySelector('input[name="alur_pembayaran"][value="mandiri_langsung"]');
            if (radMandiri) radMandiri.checked = true;
            toggleAlurPembayaran('mandiri_langsung');
            document.getElementById('form-kelompok-tarif').value = 'umum';
            loadTariffsForForm();
        }
        if (window.lucide) lucide.createIcons();
    }

    function toggleAlurPembayaran(alur) {
        const boxPks = document.getElementById('box-piutang-pks');
        const labelLangsung = document.getElementById('label-alur-langsung');
        const labelPiutang = document.getElementById('label-alur-piutang');

        if (alur === 'piutang_pks') {
            boxPks.classList.remove('hidden');
            labelPiutang.className = 'p-3 rounded-xl border-2 cursor-pointer transition-all flex items-start gap-2.5 bg-teal-50/60 border-teal-700';
            labelLangsung.className = 'p-3 rounded-xl border-2 cursor-pointer transition-all flex items-start gap-2.5 bg-white border-slate-200';
            if (window.lucide) lucide.createIcons();
        } else {
            boxPks.classList.add('hidden');
            labelLangsung.className = 'p-3 rounded-xl border-2 cursor-pointer transition-all flex items-start gap-2.5 bg-emerald-50/60 border-emerald-700';
            labelPiutang.className = 'p-3 rounded-xl border-2 cursor-pointer transition-all flex items-start gap-2.5 bg-white border-slate-200';
        }
    }

    function calculateFormAge(birthDate) {
        if (!birthDate) return;
        const b = new Date(birthDate);
        const now = new Date();
        let years = now.getFullYear() - b.getFullYear();
        if (now.getMonth() < b.getMonth() || (now.getMonth() === b.getMonth() && now.getDate() < b.getDate())) {
            years--;
        }
        document.getElementById('form-usia-tahun').value = Math.max(0, years);
    }

    // Autocomplete pencarian pasien & master klien
    let searchTimeout = null;
    window._lastPatientSearchResults = [];

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function searchPatient(q) {
        clearTimeout(searchTimeout);
        const resultsBox = document.getElementById('patient-search-results');
        const btnClear = document.getElementById('btn-clear-patient-search');

        if (btnClear) {
            if (q && q.trim().length > 0) btnClear.classList.remove('hidden');
            else btnClear.classList.add('hidden');
        }

        if (!q || q.trim().length === 0) {
            resultsBox.classList.add('hidden');
            resultsBox.innerHTML = '';
            window._lastPatientSearchResults = [];
            return;
        }

        const queryTrimmed = q.trim();

        // Tampilkan indikator loading ringan jika respon belum kembali
        resultsBox.innerHTML = '<div class="p-3 text-xs text-slate-500 text-center flex items-center justify-center gap-2"><span class="animate-spin text-emerald-600">⌛</span> Mencari data master klien / pasien...</div>';
        resultsBox.classList.remove('hidden');

        searchTimeout = setTimeout(() => {
            fetch('<?= site_url('pendaftaran/search_clients') ?>?q=' + encodeURIComponent(queryTrimmed))
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success' && res.data && res.data.length > 0) {
                        window._lastPatientSearchResults = res.data;
                        let html = '<div class="py-1">';
                        res.data.forEach((c, idx) => {
                            const isPerusahaan = (c.client_type === 'perusahaan');
                            const typeBadge = isPerusahaan 
                                ? '<span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-blue-100 text-blue-800 border border-blue-200">Perusahaan / Mitra</span>' 
                                : '<span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 border border-emerald-200">Pasien Perorangan</span>';
                            
                            const idLabel = isPerusahaan ? 'NPWP/Izin' : 'NIK';
                            const idVal = c.identity_number || '-';
                            const extraInfo = isPerusahaan 
                                ? (c.pic_name ? ` &bull; PIC: <strong class="text-slate-700">${escapeHtml(c.pic_name)}</strong>` : '') + (c.contract_discount > 0 ? ` &bull; Diskon PKS: ${c.contract_discount}%` : '')
                                : (c.gender ? ` &bull; ${c.gender === 'L' ? 'Laki-laki' : 'Perempuan'}` : '');

                            html += `<div class="p-2.5 hover:bg-emerald-50/90 cursor-pointer transition-colors border-b border-slate-100 last:border-b-0 text-xs" onclick="selectPatientByIndex(${idx})">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="font-bold text-slate-900">${escapeHtml(c.name)}</span>
                                        ${typeBadge}
                                    </div>
                                    <span class="font-mono text-[10px] text-emerald-900 bg-emerald-100/90 font-bold px-1.5 py-0.5 rounded border border-emerald-200/60 flex-shrink-0">${escapeHtml(c.client_code)}</span>
                                </div>
                                <div class="text-[10px] text-slate-500 mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                    <span>${idLabel}: <strong class="font-mono text-slate-600">${escapeHtml(idVal)}</strong></span>
                                    <span>&bull;</span>
                                    <span>HP: <strong class="font-mono text-slate-600">${escapeHtml(c.phone || '-')}</strong></span>
                                    ${extraInfo}
                                </div>
                                ${c.address ? `<div class="text-[10px] text-slate-400 truncate mt-0.5"><i class="inline-block text-slate-400 mr-0.5">📍</i>${escapeHtml(c.address)}</div>` : ''}
                            </div>`;
                        });

                        // Tambahkan footer untuk pendaftaran baru jika yang dicari belum tepat
                        html += `</div>
                        <div class="p-2.5 bg-slate-50/90 border-t border-slate-200 flex items-center justify-between gap-2">
                            <span class="text-[11px] text-slate-500">Bukan yang Anda cari?</span>
                            <button type="button" onclick="openQuickClientModal('${escapeHtml(queryTrimmed).replace(/'/g, "\\'")}')" class="px-2.5 py-1 text-[11px] font-bold rounded-lg bg-emerald-700 hover:bg-emerald-800 text-amber-200 flex items-center gap-1 transition-colors shadow-2xs">
                                <span>+ Daftarkan Klien Baru</span>
                            </button>
                        </div>`;

                        resultsBox.innerHTML = html;
                        resultsBox.classList.remove('hidden');
                    } else {
                        window._lastPatientSearchResults = [];
                        resultsBox.innerHTML = `
                            <div class="p-4 text-center space-y-2">
                                <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 mx-auto flex items-center justify-center font-bold text-sm">?</div>
                                <p class="text-xs text-slate-600">
                                    Klien / Pasien <span class="font-bold text-slate-900">"${escapeHtml(queryTrimmed)}"</span> belum terdaftar di database master.
                                </p>
                                <button type="button" onclick="openQuickClientModal('${escapeHtml(queryTrimmed).replace(/'/g, "\\'")}')" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 shadow-md transition-all">
                                    <span>+ Daftarkan Klien / Pasien Ini Sekarang</span>
                                </button>
                            </div>
                        `;
                        resultsBox.classList.remove('hidden');
                    }
                })
                .catch(err => {
                    resultsBox.innerHTML = '<div class="p-3 text-xs text-rose-500 text-center">Gagal menghubungi server pencarian data master klien.</div>';
                    resultsBox.classList.remove('hidden');
                });
        }, 220);
    }

    function clearPatientSearch() {
        document.getElementById('patient-search-input').value = '';
        const resultsBox = document.getElementById('patient-search-results');
        resultsBox.classList.add('hidden');
        resultsBox.innerHTML = '';
        window._lastPatientSearchResults = [];
        const btnClear = document.getElementById('btn-clear-patient-search');
        if (btnClear) btnClear.classList.add('hidden');
    }

    function selectPatientByIndex(idx) {
        if (!window._lastPatientSearchResults || !window._lastPatientSearchResults[idx]) return;
        selectPatient(window._lastPatientSearchResults[idx]);
    }

    function selectPatient(c) {
        const resultsBox = document.getElementById('patient-search-results');
        if (resultsBox) resultsBox.classList.add('hidden');

        document.getElementById('patient-search-input').value = c.name + ' (' + c.client_code + ')';
        const btnClear = document.getElementById('btn-clear-patient-search');
        if (btnClear) btnClear.classList.remove('hidden');

        document.getElementById('form-client-id').value = c.id;
        document.getElementById('form-nama-pasien').value = c.name;
        document.getElementById('form-nik-pasien').value = c.identity_number || '';
        document.getElementById('form-nomor-rm').value = c.client_code || '';
        document.getElementById('form-telepon').value = c.phone || '';
        document.getElementById('form-email').value = c.email || '';
        document.getElementById('form-alamat').value = c.address || '';

        const currentTipe = document.getElementById('form-tipe-pasien') ? document.getElementById('form-tipe-pasien').value : 'mandiri';

        if (c.client_type === 'perorangan') {
            document.getElementById('form-gender').value = c.gender || 'L';
            document.getElementById('form-birth-date').value = c.birth_date || '';
            if (c.birth_date) calculateFormAge(c.birth_date);
            if (currentTipe === 'perusahaan_lingkungan') {
                setFormTipe('mandiri');
            }
        } else {
            // Perusahaan atau Faskes Mitra
            if (currentTipe === 'rujukan_faskes') {
                document.getElementById('form-instansi-pengirim').value = c.name;
                document.getElementById('form-instansi-penjamin').value = c.name;
                document.getElementById('form-pic-penjamin').value = c.pic_name || '';
                document.getElementById('form-diskon-persen').value = c.contract_discount || 0;
            } else {
                setFormTipe('perusahaan_lingkungan');
                document.getElementById('form-instansi-penjamin').value = c.name;
                document.getElementById('form-pic-penjamin').value = c.pic_name || '';
                document.getElementById('form-diskon-persen').value = c.contract_discount || 0;
            }
            calculateBilling();
        }
    }

    // Modal Pendaftaran Cepat Master Klien / Pasien
    function openQuickClientModal(presetName = '') {
        const qModal = document.getElementById('modal-quick-client');
        if (!qModal) return;

        // Tutup dropdown hasil pencarian jika sedang terbuka
        const resultsBox = document.getElementById('patient-search-results');
        if (resultsBox) resultsBox.classList.add('hidden');

        // Reset form
        document.getElementById('quick-client-form').reset();

        // Sesuaikan kategori default dengan tab pendaftaran yang sedang aktif
        const activeTipe = document.getElementById('form-tipe-pasien') ? document.getElementById('form-tipe-pasien').value : 'mandiri';
        if (activeTipe === 'perusahaan_lingkungan' || activeTipe === 'rujukan_faskes') {
            setQuickClientType('perusahaan');
        } else {
            setQuickClientType('perorangan');
        }

        // Jika ada teks pencarian sebelumnya, isikan ke input nama
        if (presetName && typeof presetName === 'string') {
            document.getElementById('qc-name').value = presetName.trim();
        }

        qModal.classList.remove('hidden');
        qModal.classList.add('flex');
        qModal.style.display = 'flex';
        if (window.lucide) lucide.createIcons();

        setTimeout(() => {
            const inputName = document.getElementById('qc-name');
            if (inputName) inputName.focus();
        }, 120);
    }

    function closeQuickClientModal() {
        const qModal = document.getElementById('modal-quick-client');
        if (qModal) {
            qModal.classList.remove('flex');
            qModal.classList.add('hidden');
            qModal.style.display = 'none';
        }
    }

    function setQuickClientType(type) {
        document.getElementById('qc-client-type').value = type;
        const btnPerorangan = document.getElementById('btn-qc-perorangan');
        const btnPerusahaan = document.getElementById('btn-qc-perusahaan');
        const fieldsPerorangan = document.getElementById('qc-fields-perorangan');
        const fieldsPerusahaan = document.getElementById('qc-fields-perusahaan');
        const labelName = document.getElementById('qc-label-name');
        const inputName = document.getElementById('qc-name');

        if (type === 'perusahaan') {
            btnPerusahaan.className = 'p-3 rounded-xl border-2 border-emerald-700 bg-emerald-50/80 text-emerald-950 text-left flex items-center gap-2.5 shadow-2xs transition-all';
            btnPerusahaan.querySelector('div:first-child').className = 'w-8 h-8 rounded-lg bg-emerald-700 text-amber-300 flex items-center justify-center flex-shrink-0';

            btnPerorangan.className = 'p-3 rounded-xl border-2 border-slate-200 bg-white text-slate-700 text-left flex items-center gap-2.5 shadow-2xs hover:bg-slate-50 transition-all';
            btnPerorangan.querySelector('div:first-child').className = 'w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center flex-shrink-0';

            fieldsPerorangan.classList.add('hidden');
            fieldsPerusahaan.classList.remove('hidden');

            labelName.innerHTML = 'Nama Perusahaan / Faskes Mitra <span class="req">*</span>';
            inputName.placeholder = 'Ketik nama PT / RS / Puskesmas / Klinik';
        } else {
            btnPerorangan.className = 'p-3 rounded-xl border-2 border-emerald-700 bg-emerald-50/80 text-emerald-950 text-left flex items-center gap-2.5 shadow-2xs transition-all';
            btnPerorangan.querySelector('div:first-child').className = 'w-8 h-8 rounded-lg bg-emerald-700 text-amber-300 flex items-center justify-center flex-shrink-0';

            btnPerusahaan.className = 'p-3 rounded-xl border-2 border-slate-200 bg-white text-slate-700 text-left flex items-center gap-2.5 shadow-2xs hover:bg-slate-50 transition-all';
            btnPerusahaan.querySelector('div:first-child').className = 'w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center flex-shrink-0';

            fieldsPerorangan.classList.remove('hidden');
            fieldsPerusahaan.classList.add('hidden');

            labelName.innerHTML = 'Nama Lengkap Pasien <span class="req">*</span>';
            inputName.placeholder = 'Ketik nama lengkap sesuai KTP / Dokumen Resmi';
        }
        if (window.lucide) lucide.createIcons();
    }

    function saveQuickClient(e) {
        if (e) e.preventDefault();
        const form = document.getElementById('quick-client-form');
        const btnSave = document.getElementById('btn-save-quick-client');
        const origBtnHtml = btnSave ? btnSave.innerHTML : 'Simpan & Pilih Klien';

        const name = document.getElementById('qc-name').value.trim();
        const phone = document.getElementById('qc-phone').value.trim();

        if (!name) {
            LIS.alert('Validasi Gagal', 'Nama Klien / Pasien / Perusahaan wajib diisi.', 'warning');
            return;
        }
        if (!phone) {
            LIS.alert('Validasi Gagal', 'Nomor Telepon / WhatsApp wajib diisi.', 'warning');
            return;
        }

        const formData = new FormData(form);
        if (btnSave) {
            btnSave.disabled = true;
            btnSave.innerHTML = '<span class="inline-block animate-spin mr-1">⌛</span> Menyimpan ke Master...';
        }

        fetch('<?= site_url('pendaftaran/save_quick_client') ?>', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (btnSave) {
                btnSave.disabled = false;
                btnSave.innerHTML = origBtnHtml;
            }
            if (res.status === 'success' && res.data) {
                // Pilih langsung klien baru ke formulir order
                selectPatient(res.data);
                closeQuickClientModal();
                LIS.alert('Sukses', res.message || 'Klien berhasil didaftarkan ke master database.', 'success');
            } else {
                LIS.alert('Gagal Simpan', res.message || 'Terjadi kendala saat mendaftarkan klien.', 'error');
            }
        })
        .catch(err => {
            if (btnSave) {
                btnSave.disabled = false;
                btnSave.innerHTML = origBtnHtml;
            }
            LIS.showError('Gagal Registrasi Klien Baru', err, 'Gagal memproses pendaftaran klien ke server.');
        });
    }

    // Menutup dropdown autocomplete jika pengguna mengklik di luar area pencarian
    document.addEventListener('click', function(e) {
        const searchWrapper = document.getElementById('patient-search-wrapper');
        const resultsBox = document.getElementById('patient-search-results');
        if (searchWrapper && resultsBox && !searchWrapper.contains(e.target)) {
            resultsBox.classList.add('hidden');
        }
    });

    // =========================================================================
    // KATALOG PEMERIKSAAN & KERANJANG PEMERIKSAAN (CART)
    // =========================================================================
    function loadTariffsForForm() {
        const group = document.getElementById('form-kelompok-tarif').value || 'umum';
        const catalogBox = document.getElementById('tariff-catalog-container');
        catalogBox.innerHTML = '<div class="text-center py-4 text-xs text-slate-400">Memuat tarif pemeriksaan...</div>';

        fetch('<?= site_url('pendaftaran/get_tariffs') ?>?tariff_group=' + group)
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    availableTariffs = res.data;
                    renderTariffCatalog();
                }
            });
    }

    function renderTariffCatalog() {
        const catalogBox = document.getElementById('tariff-catalog-container');
        const catFilter = document.getElementById('select-category-filter').value.toLowerCase();
        const searchFilter = document.getElementById('tariff-search-input').value.toLowerCase().trim();

        let html = '';
        let count = 0;

        // 1. Template / Paket Lab
        if (availableTariffs.templates && availableTariffs.templates.length > 0) {
            availableTariffs.templates.forEach(tpl => {
                const matchCat = !catFilter || (tpl.category_name && tpl.category_name.toLowerCase().includes(catFilter));
                const matchSearch = !searchFilter || tpl.template_name.toLowerCase().includes(searchFilter) || tpl.template_code.toLowerCase().includes(searchFilter);

                if (matchCat && matchSearch) {
                    count++;
                    const tarifRp = tpl.total_tarif ? 'Rp ' + Number(tpl.total_tarif).toLocaleString('id-ID') : 'Rp 0';
                    html += `<div class="p-2 rounded-lg border border-amber-200 bg-amber-50/60 flex items-center justify-between text-xs hover:bg-amber-100/60 transition-colors">
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-200 text-amber-900 border border-amber-300">PAKET LAB</span>
                                <span class="font-bold text-slate-900">${tpl.template_name}</span>
                            </div>
                            <span class="text-[10px] text-slate-500">${tpl.category_name || 'Paket Lab'} &bull; ${tpl.items ? tpl.items.length : 0} Parameter</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-bold text-emerald-800">${tarifRp}</span>
                            <button type="button" onclick='addTemplateToCart(${JSON.stringify(tpl)})' class="px-2.5 py-1 rounded bg-emerald-700 hover:bg-emerald-800 text-white text-[10px] font-bold shadow-2xs">
                                + Tambah
                            </button>
                        </div>
                    </div>`;
                }
            });
        }

        // 2. Parameter Satuan
        if (availableTariffs.parameters && availableTariffs.parameters.length > 0) {
            availableTariffs.parameters.forEach(p => {
                const matchCat = !catFilter || (p.category_name && p.category_name.toLowerCase().includes(catFilter));
                const matchSearch = !searchFilter || p.parameter_name.toLowerCase().includes(searchFilter) || p.parameter_code.toLowerCase().includes(searchFilter);

                if (matchCat && matchSearch) {
                    count++;
                    const tarifRp = p.total_tarif ? 'Rp ' + Number(p.total_tarif).toLocaleString('id-ID') : 'Rp 0';
                    html += `<div class="p-2 rounded-lg border border-slate-200 bg-white flex items-center justify-between text-xs hover:bg-slate-50 transition-colors">
                        <div>
                            <span class="font-bold text-slate-800">${p.parameter_name}</span>
                            <div class="text-[10px] text-slate-400 flex items-center gap-1.5">
                                <span class="font-mono text-emerald-800">${p.parameter_code}</span>
                                <span>&bull;</span>
                                <span>${p.category_name || '-'}</span>
                                ${p.method_name ? `<span>&bull; ${p.method_name}</span>` : ''}
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-bold text-slate-800">${tarifRp}</span>
                            <button type="button" onclick='addParameterToCart(${JSON.stringify(p)})' class="px-2.5 py-1 rounded bg-slate-100 hover:bg-emerald-700 hover:text-white text-slate-700 text-[10px] font-bold border border-slate-300 transition-colors">
                                + Tambah
                            </button>
                        </div>
                    </div>`;
                }
            });
        }

        if (count === 0) {
            html = '<div class="text-center py-4 text-xs text-slate-400">Tidak ada pemeriksaan yang sesuai kata kunci.</div>';
        }

        catalogBox.innerHTML = html;
    }

    function filterTariffList() {
        renderTariffCatalog();

        // Update autocomplete suggestions
        const suggestBox = document.getElementById('tariff-search-suggestions');
        const btnClear = document.getElementById('btn-clear-tariff-search');
        if (btnClear) btnClear.classList.toggle('hidden', !searchFilter);

        if (suggestBox) {
            if (!searchFilter) {
                suggestBox.classList.add('hidden');
                suggestBox.innerHTML = '';
            } else {
                const suggestions = [];
                if (availableTariffs.templates) {
                    availableTariffs.templates.forEach(tpl => {
                        if (suggestions.length < 6 && (tpl.template_name.toLowerCase().includes(searchFilter) || tpl.template_code.toLowerCase().includes(searchFilter))) {
                            suggestions.push({ type: 'template', raw: tpl, name: tpl.template_name, code: tpl.template_code, price: tpl.total_tarif });
                        }
                    });
                }
                if (availableTariffs.parameters) {
                    availableTariffs.parameters.forEach(p => {
                        if (suggestions.length < 8 && (p.parameter_name.toLowerCase().includes(searchFilter) || p.parameter_code.toLowerCase().includes(searchFilter))) {
                            suggestions.push({ type: 'parameter', raw: p, name: p.parameter_name, code: p.parameter_code, price: p.total_tarif });
                        }
                    });
                }

                if (suggestions.length === 0) {
                    suggestBox.classList.add('hidden');
                    suggestBox.innerHTML = '';
                } else {
                    let sHtml = '<div class="px-3 py-1.5 bg-slate-50 text-[10px] font-bold text-slate-500 flex justify-between"><span>Saran Pemeriksaan</span><span class="text-emerald-700">' + suggestions.length + ' saran</span></div>';
                    suggestions.forEach((s, idx) => {
                        const rp = s.price ? 'Rp ' + Number(s.price).toLocaleString('id-ID') : 'Rp 0';
                        sHtml += '<div onclick="selectTariffSuggestion(' + idx + ')" class="w-full text-left px-3 py-2 text-xs hover:bg-emerald-50 flex items-center justify-between group transition-colors cursor-pointer">';
                        sHtml += '<div class="truncate mr-2"><div class="font-bold text-slate-800">' + s.name + '</div><div class="text-[10px] text-slate-400 font-mono">' + s.code + ' &bull; <span class="' + (s.type === 'template' ? 'text-amber-700 font-bold' : 'text-slate-500') + '">' + (s.type === 'template' ? 'Paket' : 'Satuan') + '</span></div></div>';
                        sHtml += '<div class="flex items-center gap-1.5 flex-shrink-0"><span class="font-mono text-emerald-800 font-bold text-[11px]">' + rp + '</span><span class="px-2 py-0.5 rounded bg-emerald-700 text-white font-bold text-[10px]">+ Pilih</span></div>';
                        sHtml += '</div>';
                    });
                    suggestBox.innerHTML = sHtml;
                    suggestBox.classList.remove('hidden');
                    window._lastTariffSuggestions = suggestions;
                }
            }
        }
    }

    function selectTariffSuggestion(idx) {
        if (window._lastTariffSuggestions && window._lastTariffSuggestions[idx]) {
            const item = window._lastTariffSuggestions[idx];
            if (item.type === 'template') {
                addTemplateToCart(item.raw);
            } else {
                addParameterToCart(item.raw);
            }
            clearTariffSearch();
        }
    }

    function clearTariffSearch() {
        const input = document.getElementById('tariff-search-input');
        if (input) {
            input.value = '';
            filterTariffList();
        }
        const suggestBox = document.getElementById('tariff-search-suggestions');
        if (suggestBox) suggestBox.classList.add('hidden');
    }

    document.addEventListener('click', function(e) {
        const wrapper = document.getElementById('tariff-search-wrapper');
        const suggestBox = document.getElementById('tariff-search-suggestions');
        if (wrapper && suggestBox && !wrapper.contains(e.target)) {
            suggestBox.classList.add('hidden');
        }
    });

    function addParameterToCart(p) {
        // Cek duplikat
        const exists = cartItems.some(it => it.tipe_item === 'parameter' && it.item_id == p.id);
        if (exists) {
            LIS.toast('info', 'Parameter ' + p.parameter_name + ' sudah ada di daftar.');
            return;
        }

        cartItems.push({
            tipe_item: 'parameter',
            item_id: p.id,
            kode_item: p.parameter_code,
            nama_item: p.parameter_name,
            kategori_lab: p.category_name || 'Laboratorium',
            metode_uji: p.method_name || '-',
            satuan: p.unit || '-',
            jasa_manajemen: Number(p.jasa_manajemen || 0),
            jasa_dokter: Number(p.jasa_dokter || 0),
            jasa_petugas: Number(p.jasa_petugas || 0),
            kso: Number(p.kso || 0),
            bhp: Number(p.bhp || 0),
            tarif: Number(p.total_tarif || 0)
        });

        renderCart();
        LIS.toast('success', p.parameter_name + ' ditambahkan ke order.');
    }

    function addTemplateToCart(tpl) {
        const exists = cartItems.some(it => it.tipe_item === 'template' && it.item_id == tpl.id);
        if (exists) {
            LIS.toast('info', 'Paket ' + tpl.template_name + ' sudah ada di daftar.');
            return;
        }

        cartItems.push({
            tipe_item: 'template',
            item_id: tpl.id,
            kode_item: tpl.template_code,
            nama_item: tpl.template_name,
            kategori_lab: tpl.category_name || 'Paket Lab',
            metode_uji: 'Standar Paket',
            satuan: '-',
            jasa_manajemen: Number(tpl.jasa_manajemen || 0),
            jasa_dokter: Number(tpl.jasa_dokter || 0),
            jasa_petugas: Number(tpl.jasa_petugas || 0),
            kso: Number(tpl.kso || 0),
            bhp: Number(tpl.bhp || 0),
            tarif: Number(tpl.total_tarif || 0)
        });

        renderCart();
        LIS.toast('success', 'Paket ' + tpl.template_name + ' ditambahkan.');
    }

    function removeCartItem(idx) {
        cartItems.splice(idx, 1);
        renderCart();
    }

    function clearCart() {
        cartItems = [];
        renderCart();
    }

    function renderCart() {
        const tbody = document.getElementById('cart-table-body');
        document.getElementById('cart-item-count').textContent = cartItems.length;

        if (cartItems.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-slate-400">Belum ada pemeriksaan yang dipilih.</td></tr>';
            calculateBilling();
            return;
        }

        let html = '';
        cartItems.forEach((it, idx) => {
            const badge = it.tipe_item === 'template' ? '<span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-100 text-amber-800 border border-amber-300">PAKET</span> ' : '';
            html += `<tr>
                <td class="p-2 font-medium text-slate-800">
                    ${badge}${it.nama_item}
                    <div class="text-[10px] text-slate-400 font-mono">${it.kode_item} &bull; ${it.metode_uji}</div>
                </td>
                <td class="p-2 text-slate-500">${it.kategori_lab}</td>
                <td class="p-2 text-right font-mono font-bold text-slate-900">Rp ${Number(it.tarif).toLocaleString('id-ID')}</td>
                <td class="p-2 text-center">
                    <button type="button" onclick="removeCartItem(${idx})" class="p-1 text-slate-400 hover:text-rose-600 rounded">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    </button>
                </td>
            </tr>`;
        });

        tbody.innerHTML = html;
        if (window.lucide) lucide.createIcons();
        calculateBilling();
    }

    function calculateBilling() {
        let subtotal = 0;
        cartItems.forEach(it => {
            subtotal += Number(it.tarif || 0);
        });

        const diskonPersen = Number(document.getElementById('form-diskon-persen').value || 0);
        const diskonNominal = (subtotal * diskonPersen) / 100;
        const total = Math.max(0, subtotal - diskonNominal);

        currentTotalTagihan = total;

        document.getElementById('disp-subtotal').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
        document.getElementById('disp-diskon').textContent = '- Rp ' + diskonNominal.toLocaleString('id-ID');
        document.getElementById('disp-total').textContent = 'Rp ' + total.toLocaleString('id-ID');

        document.getElementById('form-items-json').value = JSON.stringify(cartItems);
    }

    // Submit Order LIS
    function saveOrder(e) {
        e.preventDefault();

        if (cartItems.length === 0) {
            LIS.alert('Order Kosong', 'Silakan pilih minimal 1 parameter atau paket pemeriksaan lab.', 'warning');
            return;
        }

        const fd = new FormData(document.getElementById('order-form'));

        fetch('<?= site_url('pendaftaran/save') ?>', {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                closeOrderModal();
                LIS.toast('success', res.message);
                reloadTable();

                // Buka dialog aksi cepat pasca registrasi order
                const reg = res.data;
                const isPaid = (reg.status_pembayaran === 'PAID' || reg.status_pembayaran === 'GUARANTEED');

                Swal.fire({
                    title: 'Pendaftaran Berhasil!',
                    html: `
                        <div class="text-left text-xs space-y-2 p-3 bg-slate-50 rounded-xl border border-slate-200">
                            <div class="flex justify-between items-center"><span class="text-slate-500">No. Registrasi:</span><strong class="font-mono text-slate-900">${reg.nomor_registrasi}</strong></div>
                            <div class="flex justify-between items-center"><span class="text-slate-500">Nama Pasien:</span><strong class="text-slate-900">${reg.nama_pasien}</strong></div>
                            <div class="flex justify-between items-center"><span class="text-slate-500">Barcode Sampel:</span><strong class="font-mono text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">${reg.barcode_sampel}</strong></div>
                            <div class="flex justify-between items-center"><span class="text-slate-500">Total Biaya:</span><strong class="text-emerald-900 font-bold font-mono">Rp ${Number(reg.total_biaya).toLocaleString('id-ID')}</strong></div>
                            <div class="flex justify-between items-center border-t border-slate-200 pt-1.5"><span class="text-slate-500">Status Pembayaran:</span><span class="px-2 py-0.5 rounded text-[10px] font-bold ${isPaid ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'}">${reg.status_pembayaran}</span></div>
                        </div>
                        <p class="text-xs text-slate-500 mt-3 text-center">Data order telah masuk ke antrean pemeriksaan LIS. Silakan pilih tindakan:</p>
                    `,
                    icon: 'success',
                    showDenyButton: true,
                    showCancelButton: true,
                    confirmButtonText: '🏷️ Cetak Label Barcode',
                    denyButtonText: (!isPaid) ? '💳 Bayar ke Kasir' : '🧪 Input Hasil Lab',
                    cancelButtonText: '👁️ Lihat di Daftar',
                    confirmButtonColor: '#047857',
                    denyButtonColor: (!isPaid) ? '#d97706' : '#0f766e',
                }).then((action) => {
                    if (action.isConfirmed) {
                        window.open('<?= site_url('pendaftaran/cetak_barcode') ?>/' + reg.id, '_blank');
                    } else if (action.isDenied) {
                        if (!isPaid) {
                            openKasirModal(reg.id);
                        } else {
                            openInputHasilModal(reg.id);
                        }
                    }
                });
            } else {
                LIS.alert('Gagal Menyimpan', res.message || 'Terjadi kesalahan formulir.', 'error');
            }
        })
        .catch(err => LIS.showError('Gagal Memperbarui Status', err, 'Terjadi gangguan jaringan atau server.'));
    }

    // =========================================================================
    // MODAL KASIR TERHUBUNG AKUN BAYAR & AKUN PIUTANG
    // =========================================================================
    let currentKasirTotal = 0;
    let currentKasirAdmin = 0;

    function openKasirModal(id) {
        fetch('<?= site_url('pendaftaran/get') ?>/' + id)
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    const reg = res.data;
                    document.getElementById('kasir-reg-id').value = reg.id;
                    document.getElementById('kasir-reg-num').textContent = reg.nomor_registrasi + ' (' + reg.nomor_billing + ')';
                    document.getElementById('kasir-patient-name').textContent = reg.nama_pasien;
                    
                    currentKasirTotal = Number(reg.total_biaya || 0);
                    document.getElementById('kasir-total-tagihan').textContent = 'Rp ' + currentKasirTotal.toLocaleString('id-ID');

                    // Set default tab berdasarkan alur pembayaran awal
                    if (reg.alur_pembayaran === 'piutang_pks') {
                        switchKasirTab('piutang');
                    } else {
                        switchKasirTab('bayar');
                    }

                    // Isi data piutang awal jika ada
                    if (document.getElementById('kasir-pks-nomor')) {
                        document.getElementById('kasir-pks-nomor').value = reg.nomor_pks_spk || reg.nomor_surat_pengantar || '';
                        document.getElementById('kasir-pks-pic').value = reg.pic_penjamin || '';
                        document.getElementById('kasir-pks-catatan').value = reg.catatan_penjamin || '';
                    }

                    onAkunBayarChange();
                    onAkunPiutangChange();

                    const modal = document.getElementById('modal-kasir');
                    const backdrop = document.getElementById('modal-backdrop');
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    backdrop.classList.remove('hidden');
                    if (window.lucide) lucide.createIcons();
                }
            })
            .catch(err => LIS.showError('Gagal Memuat Kasir', err, 'Gagal memuat rincian kasir transaksi.'));
    }

    function closeKasirModal() {
        const modal = document.getElementById('modal-kasir');
        const backdrop = document.getElementById('modal-backdrop');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        backdrop.classList.add('hidden');
    }

    function switchKasirTab(tab) {
        document.getElementById('kasir-current-tab').value = tab;
        const btnBayar = document.getElementById('tab-btn-kasir-bayar');
        const btnPiutang = document.getElementById('tab-btn-kasir-piutang');
        const panelBayar = document.getElementById('panel-kasir-bayar');
        const panelPiutang = document.getElementById('panel-kasir-piutang');
        const submitText = document.getElementById('kasir-btn-submit-text');
        const submitBtn = document.getElementById('kasir-btn-submit');

        if (tab === 'piutang') {
            btnPiutang.className = 'flex-1 py-2 px-3 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5 bg-teal-700 text-white shadow-xs';
            btnBayar.className = 'flex-1 py-2 px-3 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 transition-all flex items-center justify-center gap-1.5';
            panelPiutang.classList.remove('hidden');
            panelBayar.classList.add('hidden');
            submitText.textContent = 'Konfirmasi Jaminan Piutang (GUARANTEED)';
            submitBtn.className = 'px-5 py-2.5 rounded-xl bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold shadow-md shadow-teal-700/20 flex items-center gap-2';
        } else {
            btnBayar.className = 'flex-1 py-2 px-3 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5 bg-emerald-700 text-white shadow-xs';
            btnPiutang.className = 'flex-1 py-2 px-3 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 transition-all flex items-center justify-center gap-1.5';
            panelBayar.classList.remove('hidden');
            panelPiutang.classList.add('hidden');
            submitText.textContent = 'Konfirmasi Lunas Kas BLUD (PAID)';
            submitBtn.className = 'px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold shadow-md shadow-emerald-700/20 flex items-center gap-2';
        }
        if (window.lucide) lucide.createIcons();
    }

    function onAkunBayarChange() {
        const select = document.getElementById('kasir-akun-bayar-id');
        if (!select || select.selectedIndex < 0) return;
        const opt = select.options[select.selectedIndex];
        if (!opt) return;

        const coa = opt.getAttribute('data-coa') || '-';
        const bank = opt.getAttribute('data-bank') || '';
        const norek = opt.getAttribute('data-norek') || '';
        const adminPct = parseFloat(opt.getAttribute('data-admin') || 0);

        document.getElementById('kasir-disp-coa').textContent = coa;
        document.getElementById('kasir-disp-bank').textContent = bank ? (bank + ' - ' + norek) : 'Kasir Tunai Loket';

        // Hitung biaya admin jika ada (misal QRIS 0.7%)
        let adminNominal = 0;
        if (adminPct > 0) {
            adminNominal = Math.round((currentKasirTotal * adminPct) / 100);
            document.getElementById('kasir-disp-admin').textContent = adminPct + '% (Rp ' + adminNominal.toLocaleString('id-ID') + ')';
        } else {
            document.getElementById('kasir-disp-admin').textContent = 'Rp 0 (Bebas Biaya)';
        }
        currentKasirAdmin = adminNominal;

        const totalFinal = currentKasirTotal + adminNominal;
        document.getElementById('kasir-nominal-bayar').value = totalFinal;
        calcKembalian();
    }

    function onAkunPiutangChange() {
        const select = document.getElementById('kasir-akun-piutang-id');
        if (!select || select.selectedIndex < 0) return;
        const opt = select.options[select.selectedIndex];
        if (!opt) return;

        const coa = opt.getAttribute('data-coa') || '-';
        const kategori = opt.getAttribute('data-kategori') || '-';
        const tempo = opt.getAttribute('data-tempo') || '0';
        const kontak = opt.getAttribute('data-kontak') || '-';

        document.getElementById('kasir-disp-piutang-coa').textContent = coa;
        document.getElementById('kasir-disp-piutang-tempo').textContent = kategori.toUpperCase().replace('_', ' ') + ' &bull; Jatuh Tempo ' + tempo + ' Hari';
        document.getElementById('kasir-disp-piutang-pic').textContent = kontak;
    }

    function setNominalPas() {
        const total = currentKasirTotal + currentKasirAdmin;
        document.getElementById('kasir-nominal-bayar').value = total;
        calcKembalian();
    }

    function addNominal(amt) {
        const cur = Number(document.getElementById('kasir-nominal-bayar').value || 0);
        document.getElementById('kasir-nominal-bayar').value = cur + amt;
        calcKembalian();
    }

    function calcKembalian() {
        const total = currentKasirTotal + currentKasirAdmin;
        const bayar = Number(document.getElementById('kasir-nominal-bayar').value || 0);
        const kembali = Math.max(0, bayar - total);
        document.getElementById('kasir-disp-kembalian').textContent = 'Rp ' + kembali.toLocaleString('id-ID');
    }

    function submitKasirPayment() {
        const id = document.getElementById('kasir-reg-id').value;
        const tab = document.getElementById('kasir-current-tab').value;

        const fd = new FormData();
        fd.append('id', id);
        fd.append('jenis_transaksi', tab);

        if (tab === 'piutang') {
            const akunPiutangId = document.getElementById('kasir-akun-piutang-id').value;
            const noPks = document.getElementById('kasir-pks-nomor').value.trim();
            const pic = document.getElementById('kasir-pks-pic').value.trim();
            const catatan = document.getElementById('kasir-pks-catatan').value.trim();

            if (!akunPiutangId) {
                LIS.alert('Peringatan', 'Silakan pilih Akun Piutang Penjamin.', 'warning');
                return;
            }
            if (!noPks) {
                LIS.alert('Peringatan', 'Nomor Kontrak PKS / Surat Jaminan / SPK wajib diisi.', 'warning');
                return;
            }

            fd.append('akun_piutang_id', akunPiutangId);
            fd.append('nomor_pks_spk', noPks);
            fd.append('pic_penjamin', pic);
            fd.append('catatan_penjamin', catatan);
        } else {
            const akunBayarId = document.getElementById('kasir-akun-bayar-id').value;
            const nominalBayar = document.getElementById('kasir-nominal-bayar').value;

            if (!akunBayarId) {
                LIS.alert('Peringatan', 'Silakan pilih Akun Bayar Kas/Bank.', 'warning');
                return;
            }

            fd.append('akun_bayar_id', akunBayarId);
            fd.append('nominal_bayar', nominalBayar);
        }

        fetch('<?= site_url('pendaftaran/validasi_kasir') ?>', {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(res => {
            if (res.status) {
                closeKasirModal();
                LIS.toast('success', res.message);
                reloadTable();

                const isPks = (tab === 'piutang');
                Swal.fire({
                    title: isPks ? 'Jaminan Piutang Terverifikasi!' : 'Pembayaran Kasir Berhasil!',
                    html: `
                        <div class="text-left text-xs p-3 bg-emerald-50 rounded-xl border border-emerald-200 space-y-1.5">
                            <p class="font-bold text-emerald-950">${res.message}</p>
                            ${res.nomor_kuitansi ? '<p>No. Kuitansi: <strong class="font-mono text-slate-800">' + res.nomor_kuitansi + '</strong></p>' : ''}
                            <p>Barcode Sampel: <strong class="font-mono text-emerald-800 bg-white px-2 py-0.5 rounded border border-emerald-300">' + res.barcode_sampel + '</strong></p>
                        </div>
                        <p class="text-xs text-slate-500 mt-2 text-center">Spesimen siap diambil dan diuji di laboratorium.</p>
                    `,
                    icon: 'success',
                    showDenyButton: true,
                    confirmButtonText: '🖨️ Cetak Kuitansi / Bukti',
                    denyButtonText: '🏷️ Cetak Label Barcode',
                    confirmButtonColor: '#047857',
                    denyButtonColor: '#0f766e'
                }).then((act) => {
                    if (act.isConfirmed) {
                        window.open('<?= site_url('pendaftaran/cetak_kuitansi') ?>/' + id, '_blank');
                    } else if (act.isDenied) {
                        window.open('<?= site_url('pendaftaran/cetak_barcode') ?>/' + id, '_blank');
                    }
                });
            } else {
                LIS.alert('Gagal Validasi', res.message, 'error');
            }
        })
        .catch(err => LIS.showError('Gagal Validasi Kasir/Piutang', err, 'Terjadi gangguan jaringan atau server.'));
    }

    // Alias openPksModal untuk kompatibilitas jika dipanggil dari tempat lain
    function openPksModal(id) {
        openKasirModal(id);
        switchKasirTab('piutang');
    }

    // =========================================================================
    // MODAL INPUT HASIL PEMERIKSAAN LAB (STANDAR KAN ISO 17025)
    // =========================================================================
    let currentHasilItems = [];

    function openInputHasilModal(id) {
        fetch('<?= site_url('pendaftaran/get_hasil_lab') ?>/' + id)
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    const data = res.data;
                    const reg = data.pendaftaran;
                    currentHasilItems = data.items || [];

                    document.getElementById('hasil-reg-id').value = reg.id;
                    document.getElementById('hasil-disp-noreg').textContent = reg.nomor_registrasi;
                    document.getElementById('hasil-disp-pasien').textContent = reg.nama_pasien + (reg.nomor_rm ? ' (' + reg.nomor_rm + ')' : '');
                    document.getElementById('hasil-disp-barcode').textContent = reg.barcode_sampel;
                    
                    const statusBadge = document.getElementById('hasil-disp-status');
                    statusBadge.textContent = reg.status_order;
                    if (reg.status_order === 'completed') {
                        statusBadge.className = 'inline-block px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800 border border-emerald-300';
                    } else if (reg.status_order === 'validasi') {
                        statusBadge.className = 'inline-block px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-amber-100 text-amber-800 border border-amber-300';
                    } else if (reg.status_order === 'processing') {
                        statusBadge.className = 'inline-block px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-sky-100 text-sky-800 border border-sky-300';
                    } else {
                        statusBadge.className = 'inline-block px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-amber-100 text-amber-800 border border-amber-300';
                    }

                    // Isi dokter pj & analis
                    document.getElementById('hasil-dokter-pj').value = data.default_dokter || 'dr. Hj. Siti Rahmah, Sp.PK, M.Kes';
                    document.getElementById('hasil-analis-nama').value = data.default_analis || 'Petugas Analis';
                    document.getElementById('hasil-kesimpulan-saran').value = data.kesimpulan_saran || '';

                    // Status radio
                    const radios = document.getElementsByName('hasil_status_order');
                    radios.forEach(r => {
                        const targetStatus = reg.status_order === 'completed' ? 'completed' : (reg.status_order === 'processing' ? 'processing' : 'validasi');
                        r.checked = (r.value === targetStatus);
                    });

                    // Render parameter rows
                    renderHasilTable(currentHasilItems);

                    const modal = document.getElementById('modal-input-hasil');
                    const backdrop = document.getElementById('modal-backdrop');
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    backdrop.classList.remove('hidden');
                    if (window.lucide) lucide.createIcons();
                } else {
                    LIS.alert('Error', res.message || 'Gagal memuat rincian uji lab.', 'error');
                }
            })
            .catch(err => LIS.showError('Gagal Memuat Hasil Lab', err, 'Terjadi kesalahan saat memuat hasil lab.'));
    }

    function closeInputHasilModal() {
        const modal = document.getElementById('modal-input-hasil');
        const backdrop = document.getElementById('modal-backdrop');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        backdrop.classList.add('hidden');
    }

    function renderHasilTable(items) {
        const tbody = document.getElementById('hasil-tbody');
        if (!items || items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="p-4 text-center text-slate-400">Belum ada parameter uji laboratorium pada order ini.</td></tr>';
            return;
        }

        let html = '';
        items.forEach((it, idx) => {
            const flag = it.hasil_flag || 'normal';
            html += `
                <tr class="hover:bg-slate-50 transition-colors" data-id="${it.id}">
                    <td class="p-2.5 text-center text-slate-400 font-bold">${idx + 1}</td>
                    <td class="p-2.5">
                        <strong class="text-slate-900 block">${it.nama_item}</strong>
                        <div class="flex items-center gap-1.5 text-[10px] text-slate-500 mt-0.5">
                            <span class="px-1.5 py-0.2 rounded bg-slate-100 text-slate-700 font-mono font-bold">${it.kode_item || '-'}</span>
                            <span>&bull;</span>
                            <span class="text-teal-800 font-semibold">${it.kategori_lab || 'Umum'}</span>
                            <span>&bull;</span>
                            <span>${it.metode_uji || '-'}</span>
                        </div>
                    </td>
                    <td class="p-2.5">
                        <span class="font-bold text-slate-800 block">${it.nilai_rujukan || '-'}</span>
                        <span class="text-[10px] text-slate-500 font-mono">Satuan: <strong>${it.satuan || '-'}</strong></span>
                    </td>
                    <td class="p-2.5">
                        <div class="relative">
                            <input type="text" id="hasil-val-${it.id}" oninput="autoEvalFlag(${it.id})" value="${it.hasil_nilai || ''}" class="lis-form-control text-xs font-bold text-slate-900 pr-7" placeholder="Ketik hasil...">
                            ${it.hasil_nilai ? '<button type="button" onclick="clearHasilVal(' + it.id + ')" class="absolute right-2 top-2 text-slate-400 hover:text-rose-500 font-bold text-xs">&times;</button>' : ''}
                        </div>
                        <div class="flex items-center gap-1 mt-1 flex-wrap">
                            <button type="button" onclick="setResultVal(${it.id}, 'Negatif', 'normal')" class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700">Negatif</button>
                            <button type="button" onclick="setResultVal(${it.id}, 'Non Reaktif', 'normal')" class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700">Non Reaktif</button>
                            <button type="button" onclick="setResultVal(${it.id}, 'Positif (+)', 'critical')" class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200">Positif (+)</button>
                            <button type="button" onclick="setResultVal(${it.id}, 'Normal', 'normal')" class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-50 hover:bg-emerald-100 text-emerald-800">Normal</button>
                        </div>
                    </td>
                    <td class="p-2.5">
                        <select id="hasil-flag-${it.id}" class="lis-form-control text-xs font-bold ${getFlagClass(flag)}">
                            <option value="normal" ${flag === 'normal' ? 'selected' : ''} class="text-emerald-800">Normal</option>
                            <option value="low" ${flag === 'low' ? 'selected' : ''} class="text-blue-800 font-bold">Low (L)</option>
                            <option value="high" ${flag === 'high' ? 'selected' : ''} class="text-orange-800 font-bold">High (H)</option>
                            <option value="critical" ${flag === 'critical' ? 'selected' : ''} class="text-rose-800 font-bold">Critical (C)</option>
                        </select>
                    </td>
                    <td class="p-2.5">
                        <input type="text" id="hasil-notes-${it.id}" value="${it.catatan_hasil || ''}" class="lis-form-control text-xs" placeholder="Keterangan uji...">
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    function getFlagClass(flag) {
        if (flag === 'low') return 'bg-blue-50 text-blue-800 border-blue-300';
        if (flag === 'high') return 'bg-orange-50 text-orange-800 border-orange-300';
        if (flag === 'critical') return 'bg-rose-50 text-rose-800 border-rose-300';
        return 'text-emerald-800';
    }

    function setResultVal(detailId, val, flag) {
        const inp = document.getElementById('hasil-val-' + detailId);
        if (inp) inp.value = val;
        const sel = document.getElementById('hasil-flag-' + detailId);
        if (sel) {
            sel.value = flag;
            sel.className = 'lis-form-control text-xs font-bold ' + getFlagClass(flag);
        }
    }

    function clearHasilVal(detailId) {
        const inp = document.getElementById('hasil-val-' + detailId);
        if (inp) {
            inp.value = '';
            autoEvalFlag(detailId);
        }
    }

    function autoEvalFlag(detailId) {
        const inp = document.getElementById('hasil-val-' + detailId);
        const sel = document.getElementById('hasil-flag-' + detailId);
        if (!inp || !sel) return;

        const valStr = inp.value.trim().toLowerCase();
        if (!valStr) {
            sel.value = 'normal';
            sel.className = 'lis-form-control text-xs font-bold ' + getFlagClass('normal');
            return;
        }

        // Cari item terkait
        const it = currentHasilItems.find(x => x.id == detailId);
        const refStr = (it && it.nilai_rujukan) ? it.nilai_rujukan.trim() : '';

        let suggestedFlag = 'normal';

        // 1. Evaluasi Kualitatif
        if (valStr.includes('positif') || valStr.includes('reaktif')) {
            suggestedFlag = 'critical';
        } else if (valStr.includes('negatif') || valStr.includes('non reaktif') || valStr.includes('normal')) {
            suggestedFlag = 'normal';
        } else if (!isNaN(parseFloat(valStr))) {
            // 2. Evaluasi Numerik terhadap Nilai Rujukan (e.g. "13.0 - 17.5" atau "< 200" atau "> 50")
            const num = parseFloat(valStr);

            if (refStr.includes('-')) {
                const parts = refStr.split('-');
                const min = parseFloat(parts[0].replace(/[^0-9.]/g, ''));
                const max = parseFloat(parts[1].replace(/[^0-9.]/g, ''));

                if (!isNaN(min) && !isNaN(max)) {
                    if (num < min) suggestedFlag = 'low';
                    else if (num > max) suggestedFlag = 'high';
                    else suggestedFlag = 'normal';
                }
            } else if (refStr.startsWith('<')) {
                const max = parseFloat(refStr.replace(/[^0-9.]/g, ''));
                if (!isNaN(max)) {
                    if (num >= max) suggestedFlag = 'high';
                    else suggestedFlag = 'normal';
                }
            } else if (refStr.startsWith('>')) {
                const min = parseFloat(refStr.replace(/[^0-9.]/g, ''));
                if (!isNaN(min)) {
                    if (num <= min) suggestedFlag = 'low';
                    else suggestedFlag = 'normal';
                }
            }
        }

        sel.value = suggestedFlag;
        sel.className = 'lis-form-control text-xs font-bold ' + getFlagClass(suggestedFlag);
    }

    function setPresetKesimpulan() {
        document.getElementById('hasil-kesimpulan-saran').value = 
            'Semua pengujian parameter laboratorium telah dilaksanakan sesuai Standar Operasional Prosedur (SOP) dan Kontrol Kualitas Laboratorium Internal (IQC). Parameter abnormal disarankan untuk dikonfirmasi klinis atau uji berkala.';
    }

    function submitHasilLab(printAfter) {
        const id = document.getElementById('hasil-reg-id').value;
        const dokterPj = document.getElementById('hasil-dokter-pj').value.trim();
        const analisNama = document.getElementById('hasil-analis-nama').value.trim();
        const kesimpulan = document.getElementById('hasil-kesimpulan-saran').value.trim();
        const statusOrder = document.querySelector('input[name="hasil_status_order"]:checked')?.value || 'processing';

        if (!id) return;
        if (!dokterPj) {
            LIS.alert('Peringatan', 'Nama Dokter Penanggung Jawab Lab wajib diisi.', 'warning');
            return;
        }

        // Kumpulkan data baris tabel
        const results = [];
        currentHasilItems.forEach(it => {
            const valInp = document.getElementById('hasil-val-' + it.id);
            const flagSel = document.getElementById('hasil-flag-' + it.id);
            const notesInp = document.getElementById('hasil-notes-' + it.id);

            results.push({
                id: it.id,
                hasil_nilai: valInp ? valInp.value.trim() : '',
                hasil_flag: flagSel ? flagSel.value : 'normal',
                catatan_hasil: notesInp ? notesInp.value.trim() : ''
            });
        });

        const fd = new FormData();
        fd.append('id', id);
        fd.append('results_json', JSON.stringify(results));
        fd.append('dokter_pj', dokterPj);
        fd.append('analis_nama', analisNama);
        fd.append('kesimpulan_saran', kesimpulan);
        fd.append('status_order', statusOrder);

        fetch('<?= site_url('pendaftaran/save_hasil_lab') ?>', {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(res => {
            if (res.status) {
                closeInputHasilModal();
                LIS.toast('success', res.message);
                reloadTable();

                if (printAfter || statusOrder === 'completed') {
                    window.open('<?= site_url('pendaftaran/cetak_hasil') ?>/' + id, '_blank');
                }
            } else {
                LIS.alert('Gagal Menyimpan', res.message || 'Terjadi kesalahan.', 'error');
            }
        })
        .catch(err => LIS.showError('Gagal Menyimpan Hasil Lab', err, 'Terjadi gangguan jaringan atau server.'));
    }

    // =========================================================================
    // MODAL DETAIL & AUDIT LOG
    // =========================================================================
    function viewDetail(id) {
        fetch('<?= site_url('pendaftaran/get') ?>/' + id)
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    const reg = res.data;
                    document.getElementById('detail-title').textContent = `${reg.nomor_registrasi} - ${reg.nama_pasien}`;

                    let itemsHtml = '';
                    if (reg.items && reg.items.length > 0) {
                        reg.items.forEach((it, idx) => {
                            itemsHtml += `<tr>
                                <td class="p-2 text-center text-slate-400">${idx+1}</td>
                                <td class="p-2 font-mono font-bold">${it.kode_item}</td>
                                <td class="p-2 font-medium">${it.nama_item}</td>
                                <td class="p-2 text-slate-500">${it.kategori_lab}</td>
                                <td class="p-2 text-slate-500">${it.metode_uji || '-'}</td>
                                <td class="p-2 text-right font-mono font-bold">Rp ${Number(it.tarif).toLocaleString('id-ID')}</td>
                                <td class="p-2 text-center font-bold uppercase text-[10px]">${it.status_pengujian}</td>
                            </tr>`;
                        });
                    }

                    let logsHtml = '';
                    if (reg.logs && reg.logs.length > 0) {
                        reg.logs.forEach(l => {
                            logsHtml += `<div class="p-2.5 rounded-lg bg-white border border-slate-200 text-xs">
                                <div class="flex items-center justify-between text-slate-400 text-[10px]">
                                    <span class="font-bold text-emerald-800">${l.action}</span>
                                    <span>${l.created_at} &bull; Oleh: ${l.user_name || 'Sistem'}</span>
                                </div>
                                <p class="text-slate-700 mt-1">${l.description}</p>
                            </div>`;
                        });
                    }

                    const body = document.getElementById('detail-content-body');
                    body.innerHTML = `
                        <!-- Grid Info -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs bg-white p-4 rounded-xl border border-slate-200">
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Data Registrasi:</span>
                                <p class="font-mono font-bold text-slate-900 mt-0.5">${reg.nomor_registrasi}</p>
                                <p class="font-mono text-slate-500">${reg.nomor_billing}</p>
                                <p class="text-slate-600 mt-1">Barcode: <span class="font-mono font-bold text-emerald-800">${reg.barcode_sampel}</span></p>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Pasien / Subjek Uji:</span>
                                <p class="font-bold text-slate-900 mt-0.5">${reg.nama_pasien}</p>
                                <p class="text-slate-500">${reg.jenis_kelamin || '-'} &bull; ${reg.usia_tahun ? reg.usia_tahun + ' Tahun' : '-'} &bull; RM: ${reg.nomor_rm || '-'}</p>
                                <p class="text-slate-600 mt-1">HP: ${reg.telepon}</p>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Status Layanan:</span>
                                <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-800 border">${reg.status_pembayaran}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 capitalize">${reg.status_order}</span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-1">Spesimen: <strong>${reg.jenis_sampel}</strong></p>
                            </div>
                        </div>

                        <!-- Tabel Items -->
                        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                            <div class="p-2.5 bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-700 flex justify-between">
                                <span>Pemeriksaan Laboratorium (${reg.items ? reg.items.length : 0} Item)</span>
                                <span class="font-mono text-emerald-800">Total: Rp ${Number(reg.total_biaya).toLocaleString('id-ID')}</span>
                            </div>
                            <table class="w-full text-xs text-left">
                                <thead class="bg-slate-50/50 text-[10px] font-bold text-slate-400 uppercase border-b border-slate-100">
                                    <tr>
                                        <th class="p-2 text-center w-8">No</th>
                                        <th class="p-2">Kode</th>
                                        <th class="p-2">Nama Tes</th>
                                        <th class="p-2">Kategori</th>
                                        <th class="p-2">Metode</th>
                                        <th class="p-2 text-right">Tarif</th>
                                        <th class="p-2 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">${itemsHtml}</tbody>
                            </table>
                        </div>

                        <!-- Logs Audit Trail -->
                        <div class="space-y-2">
                            <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block">Riwayat Alur Transaksi & Audit Trail:</span>
                            <div class="space-y-1.5 max-h-40 overflow-y-auto">${logsHtml}</div>
                        </div>
                    `;

                    // Print Buttons
                    document.getElementById('detail-print-buttons').innerHTML = `
                        <a href="<?= site_url('pendaftaran/cetak_kuitansi') ?>/${reg.id}" target="_blank" class="px-3 py-1.5 rounded-lg bg-emerald-700 text-white text-xs font-bold flex items-center gap-1.5">
                            <i data-lucide="printer" class="w-3.5 h-3.5"></i> Kuitansi
                        </a>
                        <a href="<?= site_url('pendaftaran/cetak_barcode') ?>/${reg.id}" target="_blank" class="px-3 py-1.5 rounded-lg bg-white border border-emerald-300 text-emerald-800 text-xs font-bold flex items-center gap-1.5">
                            <i data-lucide="scan-barcode" class="w-3.5 h-3.5"></i> Barcode Sampel
                        </a>
                        <a href="<?= site_url('pendaftaran/cetak_order') ?>/${reg.id}" target="_blank" class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 text-xs font-semibold flex items-center gap-1.5">
                            <i data-lucide="clipboard-list" class="w-3.5 h-3.5"></i> Form Order
                        </a>
                    `;

                    const modal = document.getElementById('modal-detail');
                    const backdrop = document.getElementById('modal-backdrop');
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    backdrop.classList.remove('hidden');
                    if (window.lucide) lucide.createIcons();
                }
            });
    }

    function closeDetailModal() {
        const modal = document.getElementById('modal-detail');
        const backdrop = document.getElementById('modal-backdrop');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        backdrop.classList.add('hidden');
    }
</script>
