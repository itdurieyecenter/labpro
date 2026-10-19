<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-5">

    <!-- 1. BREADCRUMB & PAGE HEADER (Clean Melayu Riau Theme) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-slate-400">Master Data</span>
                <span>/</span>
                <span class="text-emerald-800 font-semibold">Data Klien & Pasien</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="contact-2" class="w-6 h-6"></i>
                </span>
                <span>Master Data Klien & Pasien</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Registrasi master rekam medis pasien mandiri, rujukan faskes, dan instansi mitra MCU &bull; Standar SIMRS Terpadu</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" onclick="loadSummary(); if(table) table.ajax.reload(null, false);" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold flex items-center gap-2 transition-colors border border-slate-200" title="Muat Ulang Data">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Refresh</span>
            </button>

            <a href="<?= site_url('dashboard') ?>" class="px-3.5 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold flex items-center gap-2 transition-colors border border-slate-200 shadow-2xs" title="Kembali ke Dashboard">
                <i data-lucide="arrow-left" class="w-4 h-4 text-slate-500"></i>
                <span class="hidden sm:inline">Dashboard</span>
            </a>

            <?php if ($can_create): ?>
                <button type="button" onclick="openModal('create')"
                        class="px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-2 shadow-sm transition-all border border-emerald-600 shadow-emerald-900/10">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    <span>Tambah Klien Baru</span>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- 2. SUMMARY STATISTIK CARDS (Melayu Riau Theme) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4" id="stats-grid">
        <!-- Total Klien -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-center flex-shrink-0">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Total Klien</span>
                <span id="stat-val-total" class="text-xl sm:text-2xl font-black text-slate-900 leading-none">-</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Database Pasien & Mitra</span>
            </div>
        </div>

        <!-- Pasien Perorangan -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 flex items-center justify-center flex-shrink-0">
                <i data-lucide="user" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Perorangan</span>
                <span id="stat-val-perorangan" class="text-xl sm:text-2xl font-black text-slate-900 leading-none">-</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Mandiri & Rujukan Faskes</span>
            </div>
        </div>

        <!-- Perusahaan & Rekanan -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-teal-50 border border-teal-200 text-teal-800 flex items-center justify-center flex-shrink-0">
                <i data-lucide="building-2" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Mitra Rekanan</span>
                <span id="stat-val-perusahaan" class="text-xl sm:text-2xl font-black text-slate-900 leading-none">-</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Kerjasama MCU & Instansi</span>
            </div>
        </div>

        <!-- Status Aktif Pelayanan -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 border border-emerald-300 text-emerald-900 flex items-center justify-center flex-shrink-0">
                <i data-lucide="check-circle-2" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Status Aktif</span>
                <span id="stat-val-active" class="text-xl sm:text-2xl font-black text-emerald-800 leading-none">-</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Siap Pelayanan Lab</span>
            </div>
        </div>
    </div>

    <!-- 3. FILTER TABS & TOOLBAR (Melayu Riau Theme) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-3 rounded-2xl border border-slate-200 shadow-xs">
        <div class="flex items-center gap-1.5 flex-wrap">
            <button type="button" onclick="setFilter('all')" id="tab-all"
                    class="filter-client-tab px-3.5 py-2 rounded-xl text-xs font-bold transition-all bg-emerald-700 text-amber-300 shadow-xs flex items-center gap-2">
                <i data-lucide="layers" class="w-4 h-4"></i>
                <span>Semua Klien & Pasien</span>
            </button>
            <button type="button" onclick="setFilter('perorangan')" id="tab-perorangan"
                    class="filter-client-tab px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-all flex items-center gap-2">
                <i data-lucide="user" class="w-4 h-4"></i>
                <span>Pasien Perorangan</span>
            </button>
            <button type="button" onclick="setFilter('perusahaan')" id="tab-perusahaan"
                    class="filter-client-tab px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-all flex items-center gap-2">
                <i data-lucide="building-2" class="w-4 h-4"></i>
                <span>Perusahaan & Rekanan</span>
            </button>
        </div>
        <div class="text-[11px] text-slate-400 font-mono hidden md:flex items-center gap-1.5 px-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            Server-side Indexing Aktif
        </div>
    </div>

    <div id="alert-container"></div>

    <!-- 4. TABEL DATA KLIEN (Enterprise DataTables SIMRS) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-gradient-to-r from-emerald-50/50 via-white to-amber-50/30">
            <div class="flex items-center gap-2.5">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 animate-pulse"></span>
                <h2 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider">
                    Daftar Master Klien & Rekam Medis Pasien Laboratorium
                </h2>
            </div>
            <div class="flex items-center gap-2 text-xs text-slate-400">
                <span class="font-mono text-[11px]">SIMRS &bull; UPT LABKESDA RIAU</span>
            </div>
        </div>

        <div class="p-4 overflow-x-auto">
            <table id="clients-table" class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-[10px] font-bold text-slate-600 uppercase tracking-wider">
                        <th class="py-3 px-3 w-12 text-center" data-orderable="false">No</th>
                        <th class="py-3 px-3 w-36 font-bold" data-orderable="true">Kode Registrasi</th>
                        <th class="py-3 px-3 font-bold" data-orderable="true">Nama Pasien / Perusahaan</th>
                        <th class="py-3 px-3 w-28" data-orderable="true">Kategori</th>
                        <th class="py-3 px-3 w-40" data-orderable="true">Kontak / WA</th>
                        <th class="py-3 px-3 w-28 text-center" data-orderable="true">Status</th>
                        <th class="py-3 px-3 w-24 text-center" data-orderable="false">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- =================================================================== -->
<!-- MODAL: FORM MASTER KLIEN LENGKAP & FULL-WIDTH (STANDAR SIMRS)       -->
<!-- =================================================================== -->
<div id="client-modal" class="lis-modal">
    <div class="lis-modal-box max-w-6xl w-full">

        <!-- SIMRS Modal Header -->
        <div class="lis-modal-header bg-gradient-to-r from-emerald-900 via-emerald-800 to-amber-950 border-b border-amber-400/40">
            <div>
                <h3 class="lis-modal-title text-white">
                    <i data-lucide="user-check" class="w-5 h-5 text-amber-300"></i>
                    <span id="modal-title">Formulir Master Klien & Rekam Medis Pasien</span>
                </h3>
                <p class="lis-modal-subtitle">UPT Laboratorium Kesehatan Provinsi Riau &bull; Standar Pelayanan Laboratorium SIMRS Terpadu</p>
            </div>
            <button type="button" onclick="closeModal()" class="lis-modal-close" title="Tutup Formulir">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="lis-modal-body p-4 sm:p-6 overflow-y-auto max-h-[calc(90vh-130px)] space-y-5 bg-slate-50/70">
            <form id="client-form" onsubmit="saveClient(event)" class="space-y-5">
                <input type="hidden" name="id" id="client-id">
                <input type="hidden" name="client_type" id="client-type-input" value="perorangan">

                <!-- TOP SELECTOR: TIPE REGISTRASI KLIEN & KODE OTOMATIS -->
                <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-2xs flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1">
                        <label class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block mb-2">Pilih Tipe Registrasi Pelayanan <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="client-type-toggle">
                            <!-- Toggle Button Pasien Perorangan -->
                            <button type="button" id="btn-type-perorangan" onclick="setClientType('perorangan')"
                                    class="p-3.5 rounded-xl border-2 border-emerald-700 bg-emerald-50/80 text-emerald-950 text-left transition-all flex items-start gap-3 shadow-xs">
                                <div class="w-9 h-9 rounded-xl bg-emerald-700 text-amber-300 flex items-center justify-center flex-shrink-0 mt-0.5 border border-amber-400/30">
                                    <i data-lucide="user" class="w-5 h-5"></i>
                                </div>
                                <div class="min-w-0">
                                    <span class="font-bold text-xs block leading-tight">Pasien Perorangan (Individu)</span>
                                    <span class="text-[10px] text-emerald-800/80 block mt-0.5 leading-tight">Pasien umum mandiri, rujukan dokter praktek/klinik/RS luar</span>
                                </div>
                            </button>

                            <!-- Toggle Button Perusahaan / Rekanan -->
                            <button type="button" id="btn-type-perusahaan" onclick="setClientType('perusahaan')"
                                    class="p-3.5 rounded-xl border-2 border-slate-200 bg-white text-slate-600 hover:bg-slate-50 text-left transition-all flex items-start gap-3">
                                <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <i data-lucide="building-2" class="w-5 h-5"></i>
                                </div>
                                <div class="min-w-0">
                                    <span class="font-bold text-xs block leading-tight text-slate-800">Perusahaan / Instansi Mitra</span>
                                    <span class="text-[10px] text-slate-500 block mt-0.5 leading-tight">Korporasi swasta, BUMN, dinas Pemprov Riau, kontrak MCU</span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Kode Registrasi Labkesda -->
                    <div class="w-full md:w-72 bg-slate-50 p-3.5 rounded-xl border border-slate-200 flex-shrink-0">
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">No. RM / Kode Klien</label>
                            <span class="inline-flex items-center gap-1 text-[9px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-full border border-emerald-300/60">
                                <i data-lucide="sparkles" class="w-3 h-3 text-emerald-600"></i> Terisi Otomatis
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <input type="text" name="client_code" id="client-code" class="lis-form-control font-mono font-black text-emerald-950 bg-white" placeholder="PSN-2026-0001" readonly>
                            <button type="button" onclick="generateCode()" title="Generate Ulang Kode Otomatis"
                                    class="h-9 px-3 rounded-md bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-1 transition-colors flex-shrink-0 border border-emerald-600 shadow-2xs">
                                <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                        <span class="text-[9px] text-slate-400 mt-1 block">Otomatis tergenerasi berurutan per tahun</span>
                    </div>
                </div>

                <!-- 2-COLUMN STRUCTURED FORM CONTENT -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

                    <!-- KOLOM KIRI (7 Kolom): IDENTITAS & DEMOGRAFI KLINIS -->
                    <div class="lg:col-span-7 space-y-4">
                        
                        <!-- Panel Identitas Pasien (Mode Perorangan) -->
                        <div id="panel-identitas-pasien" class="lis-section">
                            <div class="lis-section-header bg-slate-100/90">
                                <div class="lis-section-title">
                                    <span class="lis-section-num bg-emerald-700 text-amber-300 border border-amber-400/30">1</span>
                                    <span>Identitas Demografi Medis Pasien</span>
                                </div>
                                <span class="text-[10px] font-semibold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Rekam Medis LIS</span>
                            </div>
                            <div class="lis-section-body space-y-3.5">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div class="md:col-span-2">
                                        <label class="lis-form-label">Nama Lengkap Pasien <span class="req">*</span></label>
                                        <input type="text" name="name" id="client-name" oninput="updateLivePreview()" class="lis-form-control font-bold text-slate-900" required placeholder="Nama lengkap sesuai KTP / Kartu Identitas">
                                        <span class="lis-form-hint">Sertakan gelar jika ada (misal: dr. / H. / Hj.)</span>
                                    </div>
                                    <div>
                                        <label class="lis-form-label">No. Identitas (NIK / KTP)</label>
                                        <input type="text" name="identity_number" id="identity-number" oninput="checkNik(this.value); updateLivePreview();" maxlength="20" class="lis-form-control font-mono font-semibold" placeholder="16 Digit NIK KTP">
                                        <span id="nik-hint" class="lis-form-hint">KTP / Paspor / KIA</span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div>
                                        <label class="lis-form-label">Jenis Kelamin <span class="req">*</span></label>
                                        <select name="gender" id="gender" onchange="updateLivePreview()" class="lis-form-control font-medium">
                                            <option value="">-- Pilih Gender --</option>
                                            <option value="L">Laki-laki (L)</option>
                                            <option value="P">Perempuan (P)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="lis-form-label">Tanggal Lahir</label>
                                        <input type="date" name="birth_date" id="birth-date" onchange="calcAge(this.value); updateLivePreview();" class="lis-form-control font-medium">
                                        <span id="age-calc-badge" class="text-[10px] font-bold text-emerald-700 mt-1 block"></span>
                                    </div>
                                    <div>
                                        <label class="lis-form-label">Golongan Darah</label>
                                        <select name="blood_type" id="blood-type" onchange="updateLivePreview()" class="lis-form-control font-semibold">
                                            <option value="">- Belum Diketahui -</option>
                                            <option value="A">A (Rhesus +)</option>
                                            <option value="B">B (Rhesus +)</option>
                                            <option value="AB">AB (Rhesus +)</option>
                                            <option value="O">O (Rhesus +)</option>
                                            <option value="A-">A (Rhesus -)</option>
                                            <option value="B-">B (Rhesus -)</option>
                                            <option value="AB-">AB (Rhesus -)</option>
                                            <option value="O-">O (Rhesus -)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Panel Profil Perusahaan (Mode Perusahaan - Ditampilkan saat tipe = perusahaan) -->
                        <div id="panel-identitas-perusahaan" class="lis-section hidden">
                            <div class="lis-section-header bg-amber-50/80 border-b border-amber-200">
                                <div class="lis-section-title text-amber-950 font-bold">
                                    <span class="lis-section-num bg-amber-600 text-slate-950 border border-amber-400">1</span>
                                    <span>Identitas Instansi / Perusahaan Rekanan</span>
                                </div>
                                <span class="text-[10px] font-bold text-amber-800 bg-amber-100 px-2.5 py-0.5 rounded-full border border-amber-300">Mitra MCU & Uji Lab</span>
                            </div>
                            <div class="lis-section-body space-y-3.5">
                                <div>
                                    <label class="lis-form-label">Nama Perusahaan / Instansi Pemohon <span class="req">*</span></label>
                                    <input type="text" name="company_name" id="company-name" oninput="syncCompanyName(this.value); updateLivePreview();" class="lis-form-control font-bold text-slate-900" placeholder="e.g. PT. Riau Andalan Pulp & Paper, Dinas Lingkungan Hidup Prov. Riau">
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div class="md:col-span-2">
                                        <label class="lis-form-label">Nama PIC / Koordinator MCU</label>
                                        <input type="text" name="pic_name" id="pic-name" oninput="updateLivePreview()" class="lis-form-control" placeholder="Nama Person in Charge / HRD / Dokter Perusahaan">
                                    </div>
                                    <div>
                                        <label class="lis-form-label">No. Telepon / WA PIC</label>
                                        <input type="tel" name="pic_phone" id="pic-phone" oninput="updateLivePreview()" class="lis-form-control font-mono" placeholder="081234567890">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="lis-form-label">Diskon Kontrak Kerjasama Laboratorium</label>
                                        <div class="lis-input-group">
                                            <input type="number" step="0.1" min="0" max="100" name="contract_discount" id="contract-discount" oninput="updateLivePreview()" placeholder="0.0" value="0.0">
                                            <span class="lis-input-addon border-r-0 border-l font-bold text-amber-700">%</span>
                                        </div>
                                        <span class="lis-form-hint">Diskon tarif khusus pemeriksaan MCU korporat</span>
                                    </div>
                                    <div>
                                        <label class="lis-form-label">Skema Penagihan (Billing Term)</label>
                                        <select name="company_payment_term" id="company-payment-term" onchange="document.getElementById('payment-term').value = this.value; updateLivePreview();" class="lis-form-control font-medium">
                                            <option value="Invoice Bulanan">Invoice / Tagihan Bulanan</option>
                                            <option value="Termin Per Proyek">Termin Per Selesai MCU</option>
                                            <option value="Transfer di Muka">Transfer di Muka / DP</option>
                                            <option value="Tunai / Kasir">Tunai Langsung di Kasir</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Panel Alamat & Domisili Wilayah Riau -->
                        <div class="lis-section">
                            <div class="lis-section-header bg-slate-100/90">
                                <div class="lis-section-title">
                                    <span class="lis-section-num bg-emerald-700 text-amber-300 border border-amber-400/30">2</span>
                                    <span>Alamat Domisili & Wilayah Pelayanan</span>
                                </div>
                                <span class="text-[10px] text-slate-500 font-medium">Bumi Lancang Kuning</span>
                            </div>
                            <div class="lis-section-body space-y-3">
                                <div>
                                    <label class="lis-form-label">Alamat Lengkap (Jalan, RT/RW, Kelurahan, Kecamatan)</label>
                                    <textarea name="address" id="client-address" rows="2" oninput="updateLivePreview()" class="lis-form-control" placeholder="Jl. ... RT ... / RW ... Kelurahan ... Kecamatan ..."></textarea>
                                </div>
                                <div>
                                    <label class="lis-form-label">Kabupaten / Kota Wilayah Riau</label>
                                    <select name="riau_city" id="riau-city" onchange="updateLivePreview()" class="lis-form-control font-medium">
                                        <option value="Kota Pekanbaru">Kota Pekanbaru (Pusat Labkesda)</option>
                                        <option value="Kota Dumai">Kota Dumai</option>
                                        <option value="Kab. Kampar">Kabupaten Kampar</option>
                                        <option value="Kab. Siak">Kabupaten Siak</option>
                                        <option value="Kab. Bengkalis">Kabupaten Bengkalis</option>
                                        <option value="Kab. Pelalawan">Kabupaten Pelalawan</option>
                                        <option value="Kab. Indragiri Hulu">Kabupaten Indragiri Hulu (Inhu)</option>
                                        <option value="Kab. Indragiri Hilir">Kabupaten Indragiri Hilir (Inhil)</option>
                                        <option value="Kab. Rokan Hulu">Kabupaten Rokan Hulu (Rohul)</option>
                                        <option value="Kab. Rokan Hilir">Kabupaten Rokan Hilir (Rohil)</option>
                                        <option value="Kab. Kuantan Singingi">Kabupaten Kuantan Singingi (Kuansing)</option>
                                        <option value="Kab. Kepulauan Meranti">Kabupaten Kepulauan Meranti</option>
                                        <option value="Luar Wilayah Riau">Luar Wilayah Provinsi Riau</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- KOLOM KANAN (5 Kolom): KONTAK, PENJAMINAN & KARTU LIVE VERIFIKASI -->
                    <div class="lg:col-span-5 space-y-4">

                        <!-- Panel Kontak & Notifikasi Online -->
                        <div class="lis-section">
                            <div class="lis-section-header bg-slate-100/90">
                                <div class="lis-section-title">
                                    <span class="lis-section-num bg-emerald-700 text-amber-300 border border-amber-400/30">3</span>
                                    <span>Kontak & E-Hasil Uji Laboratorium</span>
                                </div>
                            </div>
                            <div class="lis-section-body space-y-3">
                                <div>
                                    <label class="lis-form-label">Nomor WhatsApp / HP Utama <span class="req">*</span></label>
                                    <div class="relative">
                                        <input type="tel" name="phone" id="client-phone" oninput="updateLivePreview()" class="lis-form-control font-mono font-bold text-slate-800" required placeholder="081234567890">
                                    </div>
                                    <span class="lis-form-hint">Digunakan untuk notifikasi SMS / Link WhatsApp Hasil Pemeriksaan</span>
                                </div>

                                <div>
                                    <label class="lis-form-label">Alamat Email Resmi</label>
                                    <input type="email" name="email" id="client-email" oninput="updateLivePreview()" class="lis-form-control" placeholder="pasien@email.com atau hrd@perusahaan.com">
                                    <span class="lis-form-hint">Pengiriman salinan PDF hasil uji lab resmi ber-barcode</span>
                                </div>
                            </div>
                        </div>

                        <!-- Panel Penjaminan & Administrasi SIMRS -->
                        <div class="lis-section">
                            <div class="lis-section-header bg-emerald-50/80 border-b border-emerald-200">
                                <div class="lis-section-title text-emerald-950 font-bold">
                                    <span class="lis-section-num bg-emerald-700 text-amber-300 border border-amber-400/30">4</span>
                                    <span>Penjamin & Skema Administrasi</span>
                                </div>
                            </div>
                            <div class="lis-section-body space-y-3">
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="lis-form-label">Penjamin Biaya <span class="req">*</span></label>
                                        <select name="payment_term" id="payment-term" onchange="updateLivePreview()" class="lis-form-control font-semibold text-emerald-950">
                                            <option value="Tunai / Mandiri">Umum / Tunai Mandiri</option>
                                            <option value="BPJS Kesehatan">BPJS Kesehatan</option>
                                            <option value="Asuransi Swasta">Asuransi Swasta Mitra</option>
                                            <option value="Tagihan Perusahaan">Tagihan Instansi / Kontrak</option>
                                            <option value="Transfer Bank">Transfer Bank Pemprov</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="lis-form-label">Status Klien</label>
                                        <select name="status" id="client-status" onchange="updateLivePreview()" class="lis-form-control font-semibold">
                                            <option value="active">Aktif (Dapat Dilayani)</option>
                                            <option value="inactive">Non-aktif (Ditangguhkan)</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="lis-form-label">Catatan Medis Khusus / Alergi / Keterangan Kontrak</label>
                                    <textarea name="notes" id="client-notes" rows="2" class="lis-form-control" placeholder="Contoh: Riwayat alergi obat/antikoagulan, puasa 10 jam, nomor kontrak MCU..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- KARTU PRATINJAU LANGSUNG (LIVE VERIFICATION CARD) -->
                        <div class="bg-gradient-to-br from-emerald-950 via-slate-900 to-teal-950 p-4 rounded-2xl border border-amber-400/40 text-white shadow-xl relative overflow-hidden">
                            <div class="absolute -right-6 -bottom-6 w-28 h-28 rounded-full bg-amber-400/10 blur-xl pointer-events-none"></div>
                            
                            <div class="flex items-center justify-between pb-3 border-b border-emerald-900/60">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                                    <span class="text-[10px] font-black uppercase tracking-wider text-amber-300">Pratinjau Rekam Medis</span>
                                </div>
                                <span id="preview-type-badge" class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-400 text-slate-950">Pasien</span>
                            </div>

                            <div class="pt-3 space-y-2 text-xs">
                                <div>
                                    <span class="text-[10px] text-slate-400 block">Kode Registrasi:</span>
                                    <span id="preview-code" class="font-mono font-bold text-amber-300 text-sm">PSN-2026-XXXX</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 block">Nama Pasien / Klien:</span>
                                    <span id="preview-name" class="font-bold text-white text-sm block truncate">- Belum Diisi -</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 pt-1 border-t border-emerald-900/40 text-[11px]">
                                    <div>
                                        <span class="text-[10px] text-slate-400 block">No. WhatsApp:</span>
                                        <span id="preview-phone" class="font-mono text-emerald-200 truncate block">-</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-400 block">Penjamin:</span>
                                        <span id="preview-payment" class="text-amber-200 font-semibold truncate block">Umum / Tunai</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            </form>
        </div>

        <!-- SIMRS Modal Footer -->
        <div class="lis-modal-footer px-5 py-3.5 border-t border-slate-200 bg-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 flex-shrink-0">
            <div class="text-[11px] text-slate-500 flex items-center gap-2">
                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-700 flex-shrink-0"></i>
                <span class="font-semibold text-slate-700">Terstandarisasi SIMRS UPT Laboratorium Kesehatan Provinsi Riau &bull; Terakreditasi KARS</span>
            </div>
            <div class="flex items-center gap-2.5">
                <button type="button" onclick="closeModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-100 transition-colors">
                    Batal
                </button>
                <button type="button" onclick="document.getElementById('client-form').requestSubmit()" class="px-6 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold shadow-md shadow-emerald-700/25 transition-all flex items-center gap-2 border border-emerald-600/40">
                    <i data-lucide="save" class="w-4 h-4 text-amber-300"></i>
                    <span>Simpan Master Data</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Backdrop click to close -->
<div id="modal-backdrop" class="hidden fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-xs" onclick="closeModal()"></div>

<script>
    let currentFilter = 'all';
    let currentClientType = 'perorangan';
    let table = null;

    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();
        initDataTable();
        loadSummary();
    });

    // Kalkulasi Usia Otomatis dari Tanggal Lahir
    function calcAge(dateString) {
        const badge = document.getElementById('age-calc-badge');
        if (!dateString) {
            badge.textContent = '';
            return;
        }
        const birth = new Date(dateString);
        const today = new Date();
        if (isNaN(birth.getTime())) {
            badge.textContent = '';
            return;
        }

        let years = today.getFullYear() - birth.getFullYear();
        let months = today.getMonth() - birth.getMonth();
        if (months < 0 || (months === 0 && today.getDate() < birth.getDate())) {
            years--;
            months += 12;
        }
        badge.textContent = `Usia Klinis: ${years} Tahun ${months} Bulan`;
    }

    function checkNik(val) {
        const hint = document.getElementById('nik-hint');
        val = val ? val.trim() : '';
        if (val.length === 16 && /^\d+$/.test(val)) {
            hint.innerHTML = '<span class="text-emerald-600 font-bold">✓ 16 Digit NIK Valid</span>';
        } else if (val.length > 0) {
            hint.innerHTML = `<span class="text-amber-600 font-medium">${val.length} / 16 digit</span>`;
        } else {
            hint.textContent = 'KTP / Paspor / KIA';
        }
    }

    function syncCompanyName(val) {
        document.getElementById('client-name').value = val;
    }

    function updateLivePreview() {
        const isPerusahaan = currentClientType === 'perusahaan';
        const nameVal = isPerusahaan 
            ? (document.getElementById('company-name').value || document.getElementById('client-name').value)
            : document.getElementById('client-name').value;
        const codeVal = document.getElementById('client-code').value;
        const phoneVal = document.getElementById('client-phone').value;
        const paymentVal = document.getElementById('payment-term').value;

        document.getElementById('preview-name').textContent = nameVal ? nameVal : '- Belum Diisi -';
        document.getElementById('preview-code').textContent = codeVal ? codeVal : (isPerusahaan ? 'CORP-2026-XXXX' : 'PSN-2026-XXXX');
        document.getElementById('preview-phone').textContent = phoneVal ? phoneVal : '-';
        document.getElementById('preview-payment').textContent = paymentVal ? paymentVal : 'Umum / Tunai';

        const typeBadge = document.getElementById('preview-type-badge');
        if (isPerusahaan) {
            typeBadge.textContent = 'Perusahaan';
            typeBadge.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-teal-400 text-slate-950';
        } else {
            typeBadge.textContent = 'Pasien';
            typeBadge.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-amber-400 text-slate-950';
        }
    }

    function loadSummary() {
        fetch('<?= site_url('client/fetch') ?>?summary=1&type=' + currentFilter)
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    const s = res.summary;
                    document.getElementById('stat-val-total').textContent = s.total || 0;
                    document.getElementById('stat-val-perorangan').textContent = s.perorangan || 0;
                    document.getElementById('stat-val-perusahaan').textContent = s.perusahaan || 0;
                    document.getElementById('stat-val-active').textContent = s.active || 0;
                }
            })
            .catch(() => {});
    }

    function setFilter(type) {
        currentFilter = type;
        ['all', 'perorangan', 'perusahaan'].forEach(t => {
            const btn = document.getElementById('tab-' + t);
            if (!btn) return;
            if (t === type) {
                btn.className = 'filter-client-tab px-3.5 py-2 rounded-xl text-xs font-bold transition-all bg-emerald-700 text-amber-300 shadow-xs flex items-center gap-2';
            } else {
                btn.className = 'filter-client-tab px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-all flex items-center gap-2';
            }
        });
        if (table) {
            table.ajax.url('<?= site_url('client/fetch') ?>?client_type=' + (type === 'all' ? '' : type) + '&status=').load();
            loadSummary();
        }
    }

    function initDataTable() {
        table = $('#clients-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?= site_url('client/fetch') ?>?client_type=' + (currentFilter === 'all' ? '' : currentFilter) + '&status=',
                type: 'GET',
                dataSrc: function (json) {
                    if (json.status === 'success') {
                        return json.data;
                    }
                    return [];
                }
            },
            columns: [
                { data: 'no_urut', name: 'no_urut', orderable: false, searchable: false, className: 'text-center font-medium text-slate-400 py-3' },
                { data: 'client_code', name: 'client_code', orderable: true, searchable: true, className: 'font-mono font-bold text-slate-900 py-3' },
                { data: 'name', name: 'name', orderable: true, searchable: true, className: 'font-semibold text-slate-800 py-3' },
                { data: 'client_type', name: 'client_type', orderable: true, searchable: true, className: 'py-3' },
                { data: 'phone', name: 'phone', orderable: true, searchable: true, className: 'font-mono text-slate-600 py-3' },
                { data: 'status', name: 'status', orderable: true, searchable: true, className: 'text-center py-3' },
                { data: 'id', name: 'id', orderable: false, searchable: false, className: 'text-center py-3' }
            ],
            columnDefs: [
                {
                    targets: 0,
                    render: function (data, type, row, meta) {
                        return meta.settings._iDisplayStart + meta.row + 1;
                    }
                },
                {
                    targets: 1, // Kode Klien
                    render: function (data, type, row) {
                        return '<span class="font-mono font-bold text-emerald-950 px-2 py-0.5 rounded bg-emerald-50 border border-emerald-200">' + data + '</span>';
                    }
                },
                {
                    targets: 2, // Nama
                    render: function (data, type, row) {
                        let sub = '';
                        if (row.client_type === 'perorangan' && row.identity_number) {
                            sub = '<span class="text-[10px] text-slate-400 font-mono block">NIK: ' + row.identity_number + '</span>';
                        } else if (row.client_type === 'perusahaan' && row.pic_name) {
                            sub = '<span class="text-[10px] text-amber-700 font-medium block">PIC: ' + row.pic_name + '</span>';
                        }
                        return '<div class="leading-tight"><strong class="text-slate-800 text-[13px]">' + data + '</strong>' + sub + '</div>';
                    }
                },
                {
                    targets: 3, // Tipe Klien
                    render: function (data) {
                        if (data === 'perorangan') {
                            return '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-300"><i data-lucide="user" class="w-3 h-3 text-emerald-600"></i> Pasien</span>';
                        }
                        return '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-300"><i data-lucide="building-2" class="w-3 h-3 text-amber-600"></i> Perusahaan</span>';
                    }
                },
                {
                    targets: 4, // Phone
                    render: function (data, type, row) {
                        let email = row.email ? '<span class="text-[10px] text-slate-400 block truncate max-w-[150px]">' + row.email + '</span>' : '';
                        return '<div><span class="font-mono text-slate-700 font-medium">' + (data || '-') + '</span>' + email + '</div>';
                    }
                },
                {
                    targets: 5, // Status
                    render: function (data) {
                        if (data === 'active') {
                            return '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-300"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Aktif</span>';
                        }
                        return '<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">Nonaktif</span>';
                    }
                },
                {
                    targets: 6, // Aksi
                    render: function (data, type, row) {
                        let html = '<div class="flex items-center justify-center gap-1.5">';
                        <?php if ($can_edit): ?>
                        html += '<button type="button" onclick="openModal(\'edit\', ' + row.id + ')" class="p-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors border border-emerald-200" title="Edit Data Klien"><i data-lucide="edit-3" class="w-3.5 h-3.5"></i></button>';
                        <?php endif; ?>
                        <?php if ($can_delete): ?>
                        html += '<button type="button" onclick="deleteClient(' + row.id + ', \'' + row.name.replace(/'/g, "\\'") + '\')" class="p-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 transition-colors border border-rose-200" title="Hapus Klien"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>';
                        <?php endif; ?>
                        html += '</div>';
                        return html;
                    }
                }
            ],
            order: [[1, 'desc']],
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            language: {
                processing: "Memuat data klien Labkesda...",
                emptyTable: "Belum ada data klien terdaftar di sistem.",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data klien",
                infoEmpty: "Menampilkan 0 data",
                search: "Cari Data:",
                lengthMenu: "Tampil _MENU_",
                paginate: {
                    first: "«",
                    previous: "Sebelum",
                    next: "Setelah",
                    last: "»"
                }
            },
            drawCallback: function () {
                lucide.createIcons();
            }
        });
    }

    function setClientType(type) {
        currentClientType = type;
        document.getElementById('client-type-input').value = type;
        const isPerusahaan = type === 'perusahaan';

        // Toggle Visual Button Styling
        const btnPerorangan = document.getElementById('btn-type-perorangan');
        const btnPerusahaan = document.getElementById('btn-type-perusahaan');

        if (isPerusahaan) {
            btnPerusahaan.className = 'p-3.5 rounded-xl border-2 border-emerald-700 bg-emerald-50/80 text-emerald-950 text-left transition-all flex items-start gap-3 shadow-xs';
            btnPerusahaan.querySelector('div').className = 'w-9 h-9 rounded-xl bg-emerald-700 text-amber-300 flex items-center justify-center flex-shrink-0 mt-0.5 border border-amber-400/30';
            
            btnPerorangan.className = 'p-3.5 rounded-xl border-2 border-slate-200 bg-white text-slate-600 hover:bg-slate-50 text-left transition-all flex items-start gap-3';
            btnPerorangan.querySelector('div').className = 'w-9 h-9 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center flex-shrink-0 mt-0.5';
        } else {
            btnPerorangan.className = 'p-3.5 rounded-xl border-2 border-emerald-700 bg-emerald-50/80 text-emerald-950 text-left transition-all flex items-start gap-3 shadow-xs';
            btnPerorangan.querySelector('div').className = 'w-9 h-9 rounded-xl bg-emerald-700 text-amber-300 flex items-center justify-center flex-shrink-0 mt-0.5 border border-amber-400/30';

            btnPerusahaan.className = 'p-3.5 rounded-xl border-2 border-slate-200 bg-white text-slate-600 hover:bg-slate-50 text-left transition-all flex items-start gap-3';
            btnPerusahaan.querySelector('div').className = 'w-9 h-9 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center flex-shrink-0 mt-0.5';
        }

        // Show/hide dedicated panels
        document.getElementById('panel-identitas-pasien').classList.toggle('hidden', isPerusahaan);
        document.getElementById('panel-identitas-perusahaan').classList.toggle('hidden', !isPerusahaan);

        // Required attribute toggle
        document.getElementById('client-name').required = !isPerusahaan;
        document.getElementById('company-name').required = isPerusahaan;

        // Code placeholder
        const codeInput = document.getElementById('client-code');
        codeInput.placeholder = isPerusahaan ? 'CORP-2026-0001' : 'PSN-2026-0001';

        // Auto generate code if adding new
        const idVal = document.getElementById('client-id').value;
        if (!idVal) {
            generateCode();
        }

        updateLivePreview();
        if (window.lucide) lucide.createIcons();
    }

    function openModal(mode, id = null) {
        const modal = document.getElementById('client-modal');
        const backdrop = document.getElementById('modal-backdrop');
        const form = document.getElementById('client-form');

        form.reset();
        document.getElementById('client-id').value = '';
        document.getElementById('client-code').value = '';
        document.getElementById('client-name').value = '';
        document.getElementById('company-name').value = '';
        document.getElementById('client-phone').value = '';
        document.getElementById('client-email').value = '';
        document.getElementById('client-address').value = '';
        document.getElementById('client-status').value = 'active';
        document.getElementById('client-notes').value = '';
        document.getElementById('contract-discount').value = '0.0';
        document.getElementById('payment-term').value = 'Tunai / Mandiri';
        document.getElementById('gender').value = '';
        document.getElementById('birth-date').value = '';
        document.getElementById('blood-type').value = '';
        document.getElementById('pic-name').value = '';
        document.getElementById('pic-phone').value = '';
        document.getElementById('identity-number').value = '';
        document.getElementById('age-calc-badge').textContent = '';
        document.getElementById('nik-hint').textContent = 'KTP / Paspor / KIA';

        document.getElementById('modal-title').textContent = mode === 'create' ? 'Registrasi Master Klien Baru' : 'Edit Data Master Klien';

        // Default type
        setClientType('perorangan');

        if (mode === 'edit' && id) {
            fetch('<?= site_url('client/get') ?>/' + id)
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        const c = res.data;
                        document.getElementById('client-id').value = c.id;
                        document.getElementById('client-code').value = c.client_code;
                        document.getElementById('client-name').value = c.name;
                        document.getElementById('client-phone').value = c.phone;
                        document.getElementById('client-email').value = c.email || '';
                        document.getElementById('client-address').value = c.address || '';
                        document.getElementById('client-status').value = c.status;
                        document.getElementById('client-notes').value = c.notes || '';
                        document.getElementById('contract-discount').value = c.contract_discount || 0;
                        document.getElementById('payment-term').value = c.payment_term || 'Tunai / Mandiri';

                        setClientType(c.client_type);

                        if (c.client_type === 'perorangan') {
                            document.getElementById('gender').value = c.gender || '';
                            document.getElementById('birth-date').value = c.birth_date || '';
                            document.getElementById('blood-type').value = c.blood_type || '';
                            document.getElementById('identity-number').value = c.identity_number || '';
                            if (c.birth_date) calcAge(c.birth_date);
                            if (c.identity_number) checkNik(c.identity_number);
                        } else {
                            document.getElementById('company-name').value = c.name || '';
                            document.getElementById('pic-name').value = c.pic_name || '';
                            document.getElementById('pic-phone').value = c.pic_phone || '';
                        }

                        updateLivePreview();
                    }
                });
        } else {
            generateCode();
            updateLivePreview();
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        backdrop.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        if (window.lucide) lucide.createIcons();
    }

    function closeModal() {
        const modal = document.getElementById('client-modal');
        const backdrop = document.getElementById('modal-backdrop');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        backdrop.classList.add('hidden');
        document.body.style.overflow = '';
    }

    function generateCode() {
        fetch('<?= site_url('client/get_next_code') ?>/' + currentClientType)
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    document.getElementById('client-code').value = res.code;
                    updateLivePreview();
                }
            });
    }

    function saveClient(e) {
        if (e) e.preventDefault();

        // Ensure name is synced if perusahaan
        if (currentClientType === 'perusahaan') {
            const compName = document.getElementById('company-name').value;
            if (!compName) {
                LIS.alert('Data Tidak Lengkap', 'Nama perusahaan/instansi wajib diisi.', 'warning');
                return;
            }
            document.getElementById('client-name').value = compName;
        }

        const form = document.getElementById('client-form');
        const fd = new FormData(form);

        fetch('<?= site_url('client/save') ?>', {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                LIS.toast('success', res.message);
                closeModal();
                table.ajax.reload(null, false);
                loadSummary();
            } else {
                LIS.showError('Gagal Menyimpan', res, res.message || 'Terjadi kesalahan formulir.');
            }
        })
        .catch(err => LIS.showError('Gagal Menyimpan Klien', err, 'Terjadi kesalahan saat menyimpan data master klien.'));
    }

    function deleteClient(id, name) {
        LIS.confirm('Hapus Data Klien', 'Yakin ingin menghapus master klien <strong>' + name + '</strong>?<br><span class="text-xs text-rose-600">Catatan: Data yang terhapus tidak dapat dipulihkan.</span>', 'Ya, Hapus Data')
            .then((result) => {
                if (result.isConfirmed) {
                    LIS.fetchJson('<?= site_url('client/delete') ?>/' + id, {
                        method: 'DELETE',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(res => {
                        if (res.status === 'success') {
                            LIS.toast('success', res.message);
                            table.ajax.reload(null, false);
                            loadSummary();
                        } else {
                            LIS.showError('Gagal Menghapus', res, res.message || 'Terjadi kesalahan.');
                        }
                    })
                    .catch(err => LIS.showError('Gagal Menghapus Klien', err, 'Terjadi kesalahan saat menghapus data master klien.'));
                }
            });
    }
</script>
