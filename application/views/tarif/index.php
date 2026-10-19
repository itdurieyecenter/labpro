<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-5">

    <!-- 1. BREADCRUMB & PAGE HEADER (Clean Melayu Riau Theme) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-slate-400">Master Tarif</span>
                <span>/</span>
                <span class="text-emerald-800 font-semibold">Tarif Laboratorium</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="badge-percent" class="w-6 h-6"></i>
                </span>
                <span>Master Tarif & Parameter Laboratorium</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Pengaturan tarif parameter satuan, template/paket pemeriksaan, metode uji, dan nilai rujukan &bull; Standar SIMRS</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" onclick="loadStats();" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold flex items-center gap-2 transition-colors border border-slate-200" title="Muat Ulang Data">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Refresh</span>
            </button>

            <a href="<?= site_url('dashboard') ?>" class="px-3.5 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold flex items-center gap-2 transition-colors border border-slate-200 shadow-2xs" title="Kembali ke Dashboard">
                <i data-lucide="arrow-left" class="w-4 h-4 text-slate-500"></i>
                <span class="hidden sm:inline">Dashboard</span>
            </a>

            <?php if ($can_create): ?>
                <button type="button" onclick="openModalParameter('create')" class="px-4 py-2.5 rounded-xl bg-emerald-700 text-amber-200 text-xs font-bold hover:bg-emerald-800 shadow-sm transition-all flex items-center gap-2 border border-emerald-600 shadow-emerald-900/10">
                    <i data-lucide="plus" class="w-4 h-4"></i> + Parameter & Tarif
                </button>
                <button type="button" onclick="openModalTemplate('create')" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs shadow-sm transition-all flex items-center gap-2 border border-amber-400 shadow-amber-900/10">
                    <i data-lucide="layers" class="w-4 h-4"></i> + Template / Paket
                </button>
                <button type="button" onclick="openModalMetode('create')" class="px-4 py-2.5 rounded-xl bg-white hover:bg-emerald-50 text-emerald-800 text-xs font-bold transition-all flex items-center gap-2 border border-emerald-300 shadow-xs">
                    <i data-lucide="microscope" class="w-4 h-4 text-emerald-700"></i> + Metode Lab
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- 2. SUMMARY STATISTIK MASTER LAB (Melayu Riau Theme) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4" id="stats-grid">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-center flex-shrink-0">
                <i data-lucide="flask-conical" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Total Parameter</span>
                <span class="text-xl sm:text-2xl font-black text-slate-900 leading-none" id="stat-param">-</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Parameter Uji Klinis</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 flex items-center justify-center flex-shrink-0">
                <i data-lucide="layers" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Paket / Template</span>
                <span class="text-xl sm:text-2xl font-black text-slate-900 leading-none" id="stat-template">-</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Paket Pemeriksaan Lab</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-teal-50 border border-teal-200 text-teal-800 flex items-center justify-center flex-shrink-0">
                <i data-lucide="activity" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Metode Pemeriksaan</span>
                <span class="text-xl sm:text-2xl font-black text-slate-900 leading-none" id="stat-metode">-</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">SOP & Alat Analyzer</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 border border-emerald-300 text-emerald-900 flex items-center justify-center flex-shrink-0">
                <i data-lucide="boxes" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Departemen / Kategori</span>
                <span class="text-xl sm:text-2xl font-black text-emerald-800 leading-none" id="stat-kategori">-</span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Instalasi Laboratorium</span>
            </div>
        </div>
    </div>

    <!-- Tab Navigasi Master Lab -->
    <div class="bg-white p-1 rounded-2xl border border-slate-200 shadow-sm flex flex-wrap gap-1">
        <button type="button" onclick="switchTab('parameter')" id="btn-tab-parameter"
                class="flex-1 py-3 px-4 rounded-xl text-xs font-semibold transition-all flex items-center justify-center gap-2 bg-emerald-700 text-white shadow-sm shadow-emerald-700/20">
            <i data-lucide="list-plus" class="w-4 h-4 text-amber-300"></i>
            <span>1. Tarif & Parameter Satuan</span>
        </button>
        <button type="button" onclick="switchTab('template')" id="btn-tab-template"
                class="flex-1 py-3 px-4 rounded-xl text-xs font-semibold transition-all flex items-center justify-center gap-2 text-slate-600 hover:bg-slate-50">
            <i data-lucide="layers" class="w-4 h-4"></i>
            <span>2. Template / Paket Lab & Tarif</span>
        </button>
        <button type="button" onclick="switchTab('metode')" id="btn-tab-metode"
                class="flex-1 py-3 px-4 rounded-xl text-xs font-semibold transition-all flex items-center justify-center gap-2 text-slate-600 hover:bg-slate-50">
            <i data-lucide="microscope" class="w-4 h-4"></i>
            <span>3. Master Metode Pemeriksaan</span>
        </button>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 1: PARAMETER & TARIF SATUAN                                -->
    <!-- ============================================================== -->
    <div id="tab-content-parameter" class="space-y-4">
        <!-- Filter Kategori & Kelompok Tarif -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3 w-full md:w-auto flex-wrap">
                <span class="text-xs font-semibold text-slate-700">Filter Kategori:</span>
                <select id="filter-param-category" onchange="reloadParamTable()" class="lis-input text-xs py-1.5 px-3 w-auto min-w-[180px]">
                    <option value="">-- Semua Kategori Lab --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= html_escape($cat['category_name']) ?> (<?= $cat['category_code'] ?>)</option>
                    <?php endforeach; ?>
                </select>

                <span class="text-xs font-semibold text-slate-700 ml-2">Kelompok Tarif:</span>
                <select id="filter-param-tariff-group" onchange="reloadParamTable()" class="lis-input text-xs py-1.5 px-3 w-auto min-w-[140px]">
                    <option value="umum" selected>Umum / Reguler</option>
                    <option value="perusahaan">Rekanan / Perusahaan</option>
                    <option value="bpjs">BPJS Kesehatan</option>
                    <option value="asuransi">Asuransi Swasta</option>
                </select>
            </div>

            <div class="text-xs text-slate-500 text-right w-full md:w-auto">
                <span class="inline-flex items-center gap-1.5 text-sky-700 font-medium">
                    <i data-lucide="info" class="w-3.5 h-3.5"></i>
                    Rincian: Manajemen + Dokter + Petugas + KSO + BHP = Total
                </span>
            </div>
        </div>

        <!-- Tabel DataTables Server-Side Parameter & Tarif -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-x-auto p-2">
            <table id="table-parameters" class="w-full text-left text-[11px]">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-3 py-2.5 font-semibold text-slate-700 text-center w-10">No</th>
                        <th class="px-3 py-2.5 font-semibold text-slate-700">Kode</th>
                        <th class="px-3 py-2.5 font-semibold text-slate-700">Nama Parameter</th>
                        <th class="px-3 py-2.5 font-semibold text-slate-700">Kategori</th>
                        <th class="px-3 py-2.5 font-semibold text-slate-700 text-center">Satuan</th>
                        <th class="px-3 py-2.5 font-semibold text-slate-700">Metode Uji</th>
                        <th class="px-3 py-2.5 font-semibold text-slate-700">Nilai Rujukan (Umur / Gender)</th>
                        <th class="px-3 py-2.5 font-semibold text-slate-700 text-right">J. Manajemen</th>
                        <th class="px-3 py-2.5 font-semibold text-slate-700 text-right">J. Dokter</th>
                        <th class="px-3 py-2.5 font-semibold text-slate-700 text-right">J. Petugas</th>
                        <th class="px-3 py-2.5 font-semibold text-slate-700 text-right">KSO</th>
                        <th class="px-3 py-2.5 font-semibold text-slate-700 text-right">BHP</th>
                        <th class="px-3 py-2.5 font-semibold text-sky-900 text-right bg-sky-50/50">Total Tarif</th>
                        <th class="px-3 py-2.5 font-semibold text-slate-700 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100"></tbody>
            </table>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 2: TEMPLATE / PAKET LABORATORIUM                           -->
    <!-- ============================================================== -->
    <div id="tab-content-template" class="space-y-4 hidden">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3 w-full md:w-auto flex-wrap">
                <span class="text-xs font-semibold text-slate-700">Filter Kategori:</span>
                <select id="filter-template-category" onchange="reloadTemplateTable()" class="lis-input text-xs py-1.5 px-3 w-auto min-w-[180px]">
                    <option value="">-- Semua Kategori --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= html_escape($cat['category_name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <span class="text-xs font-semibold text-slate-700 ml-2">Kelompok Tarif:</span>
                <select id="filter-template-tariff-group" onchange="reloadTemplateTable()" class="lis-input text-xs py-1.5 px-3 w-auto min-w-[140px]">
                    <option value="umum" selected>Umum / Reguler</option>
                    <option value="perusahaan">Rekanan / Perusahaan</option>
                    <option value="bpjs">BPJS Kesehatan</option>
                    <option value="asuransi">Asuransi Swasta</option>
                </select>
            </div>

            <div class="text-xs text-slate-500 text-right w-full md:w-auto">
                <span class="inline-flex items-center gap-1.5 text-cyan-700 font-medium">
                    <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                    Tarif paket berlaku untuk seluruh item parameter di dalam template
                </span>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-x-auto p-2">
            <table id="table-templates" class="w-full text-left text-[11px]">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-3 py-2.5 font-semibold text-slate-700 text-center w-10">No</th>
                        <th class="px-3 py-2.5 font-semibold text-slate-700">Kode Paket</th>
                        <th class="px-3 py-2.5 font-semibold text-slate-700">Nama Template / Paket Lab</th>
                        <th class="px-3 py-2.5 font-semibold text-slate-700">Kategori</th>
                        <th class="px-3 py-2.5 font-semibold text-slate-700">Daftar Parameter Terkandung</th>
                        <th class="px-3 py-2.5 font-semibold text-slate-700 text-right">J. Manajemen</th>
                        <th class="px-3 py-2.5 font-semibold text-slate-700 text-right">J. Dokter</th>
                        <th class="px-3 py-2.5 font-semibold text-slate-700 text-right">J. Petugas</th>
                        <th class="px-3 py-2.5 font-semibold text-slate-700 text-right">KSO</th>
                        <th class="px-3 py-2.5 font-semibold text-slate-700 text-right">BHP</th>
                        <th class="px-3 py-2.5 font-semibold text-cyan-900 text-right bg-cyan-50/50">Total Paket</th>
                        <th class="px-3 py-2.5 font-semibold text-slate-700 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100"></tbody>
            </table>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 3: METODE PEMERIKSAAN                                      -->
    <!-- ============================================================== -->
    <div id="tab-content-metode" class="space-y-4 hidden">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <p class="text-xs text-slate-600">
                Daftar metode pemeriksaan laboratorium klinik, nomor SOP/IKM akreditasi, serta alat otomatis (analyzer) yang digunakan.
            </p>
            <?php if ($can_create): ?>
                <button type="button" onclick="openModalMetode('create')" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition-all flex items-center gap-1.5">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Metode
                </button>
            <?php endif; ?>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-x-auto p-2">
            <table id="table-methods" class="w-full text-left text-[11px]">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-3 py-2.5 font-semibold text-slate-700 text-center w-10">No</th>
                        <th class="px-3 py-2.5 font-semibold text-slate-700">Kode Metode</th>
                        <th class="px-3 py-2.5 font-semibold text-slate-700">Nama Metode Pemeriksaan</th>
                        <th class="px-3 py-2.5 font-semibold text-slate-700">Standar / IKM / SOP</th>
                        <th class="px-3 py-2.5 font-semibold text-slate-700">Alat Analyzer / Instrumen</th>
                        <th class="px-3 py-2.5 font-semibold text-slate-700">Keterangan</th>
                        <th class="px-3 py-2.5 font-semibold text-slate-700 text-center w-20">Status</th>
                        <th class="px-3 py-2.5 font-semibold text-slate-700 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100"></tbody>
            </table>
        </div>
    </div>

</div>

<!-- =================================================================== -->
<!-- MODAL 1: FORM PARAMETER & TARIF SATUAN (STANDAR SIMRS KESEHATAN)   -->
<!-- =================================================================== -->
<div id="modal-parameter" class="lis-modal">
    <div class="lis-modal-box max-w-3xl">
        <!-- SIMRS Header (Melayu Riau Theme) -->
        <div class="lis-modal-header bg-gradient-to-r from-emerald-900 via-emerald-800 to-teal-900 border-b border-amber-400/30">
            <div>
                <h3 class="lis-modal-title">
                    <i data-lucide="flask-conical" class="w-5 h-5 text-amber-300"></i>
                    <span id="modal-param-title">Form Master Parameter & Tarif Laboratorium</span>
                </h3>
                <p class="lis-modal-subtitle">UPT Labkesda Prov. Riau &bull; Modul Pengaturan Uji Klinis & Standar Tarif Pelayanan SIMRS</p>
            </div>
            <button type="button" onclick="closeModalParameter()" class="lis-modal-close">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- SIMRS Form Body -->
        <div class="lis-modal-body">
            <form id="form-parameter" onsubmit="saveParameter(event)" class="space-y-4">
                <input type="hidden" name="id" id="param-id">

                <!-- Section 1: Identifikasi Parameter -->
                <div class="lis-section">
                    <div class="lis-section-header">
                        <div class="lis-section-title">
                            <span class="lis-section-num">1</span>
                            <span>Identifikasi & Spesifikasi Parameter Lab</span>
                        </div>
                        <span class="text-[10px] text-slate-500 font-mono">Form SIMRS v2.6</span>
                    </div>
                    <div class="lis-section-body space-y-3">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="lis-form-label">Kategori / Departemen Lab <span class="req">*</span></label>
                                <select name="category_id" id="param-category-id" class="lis-form-control font-medium" onchange="fetchNextParamCode()" required>
                                    <option value="">-- Pilih Departemen --</option>
                                    <?php foreach ($categories as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= html_escape($c['category_name']) ?> (<?= $c['category_code'] ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="lis-form-label flex items-center justify-between">
                                    <span>Kode Parameter</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-full border border-emerald-300/60">
                                        <i data-lucide="sparkles" class="w-3 h-3 text-emerald-600"></i> Terisi Otomatis
                                    </span>
                                </label>
                                <div class="flex items-center gap-1.5">
                                    <input type="text" name="parameter_code" id="param-code" class="lis-form-control font-mono uppercase font-black text-emerald-950 bg-slate-50 focus:bg-white" placeholder="HEM-023">
                                    <button type="button" onclick="fetchNextParamCode()" title="Generate Ulang Kode Otomatis" class="h-9 px-2.5 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-1 transition-colors flex-shrink-0 border border-emerald-600 shadow-2xs">
                                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                                <span class="lis-form-hint">Otomatis berurutan sesuai kategori lab</span>
                            </div>

                            <div>
                                <label class="lis-form-label">Satuan Hasil (Unit)</label>
                                <input type="text" name="unit" id="param-unit" class="lis-form-control font-mono" placeholder="g/dl, /mm 3, %, f l, pg">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div class="md:col-span-2">
                                <label class="lis-form-label">Nama Lengkap Parameter Pemeriksaan <span class="req">*</span></label>
                                <input type="text" name="parameter_name" id="param-name" class="lis-form-control font-semibold text-slate-800" placeholder="e.g. Haemoglobin, Leukosit, LED Westergreen" required>
                            </div>

                            <div>
                                <label class="lis-form-label">Tipe Data Hasil</label>
                                <select name="data_type" id="param-data-type" class="lis-form-control">
                                    <option value="numeric">Numerik (Nilai Normal Min-Max)</option>
                                    <option value="text">Teks Bebas / Deskripsi</option>
                                    <option value="options">Kualitatif (Negatif/Positif, Non Reaktif)</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="lis-form-label">Metode Pemeriksaan Default</label>
                                <select name="method_id" id="param-method-id" class="lis-form-control">
                                    <option value="">-- Pilih Metode / Instrumen --</option>
                                    <?php foreach ($methods as $m): ?>
                                        <option value="<?= $m['id'] ?>"><?= html_escape($m['method_name']) ?> <?= !empty($m['standard_code']) ? '('.html_escape($m['standard_code']).')' : '' ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="lis-form-hint">SOP / Analyzer yang digunakan untuk parameter ini</span>
                            </div>

                            <div>
                                <label class="lis-form-label">Nomor Urut Tampilan Pada Lembar Hasil</label>
                                <input type="number" min="0" name="sort_order" id="param-sort-order" class="lis-form-control font-mono" value="0">
                                <span class="lis-form-hint">Menentukan posisi cetak di formulir lab</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Komponen Billing & Tarif SIMRS -->
                <div class="lis-section">
                    <div class="lis-section-header bg-emerald-50 border-emerald-200">
                        <div class="lis-section-title text-emerald-900">
                            <span class="w-5 h-5 rounded-full bg-emerald-600 text-white text-[10px] font-bold flex items-center justify-center flex-shrink-0">2</span>
                            <span>Rincian Komponen Tarif Layanan Laboratorium (SIMRS Billing)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-slate-600 font-semibold">Kelompok Tarif:</span>
                            <select name="tariff_group" id="param-tariff-group" class="h-7 px-2 rounded border border-emerald-300 bg-white text-[11px] font-semibold text-emerald-900 focus:outline-none">
                                <option value="umum">Umum / Reguler</option>
                                <option value="perusahaan">Rekanan / Perusahaan</option>
                                <option value="bpjs">BPJS Kesehatan</option>
                                <option value="asuransi">Asuransi Swasta</option>
                            </select>
                        </div>
                    </div>
                    <div class="lis-section-body space-y-3">
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <div class="lis-form-group">
                                <label class="lis-form-label">Jasa Sarana / Manajemen</label>
                                <div class="lis-input-group">
                                    <span class="lis-input-addon">Rp</span>
                                    <input type="number" step="100" min="0" name="jasa_manajemen" id="param-jasa-manajemen" oninput="calcParamTotal()" value="0">
                                </div>
                                <span class="lis-form-hint">Operasional & Administrasi</span>
                            </div>

                            <div class="lis-form-group">
                                <label class="lis-form-label">Jasa Dokter Penanggung Jawab</label>
                                <div class="lis-input-group">
                                    <span class="lis-input-addon">Rp</span>
                                    <input type="number" step="100" min="0" name="jasa_dokter" id="param-jasa-dokter" oninput="calcParamTotal()" value="0">
                                </div>
                                <span class="lis-form-hint">Ekspertise Sp.PK / Dokter</span>
                            </div>

                            <div class="lis-form-group">
                                <label class="lis-form-label">Jasa Petugas / Analis Lab</label>
                                <div class="lis-input-group">
                                    <span class="lis-input-addon">Rp</span>
                                    <input type="number" step="100" min="0" name="jasa_petugas" id="param-jasa-petugas" oninput="calcParamTotal()" value="0">
                                </div>
                                <span class="lis-form-hint">Flebotomis & Pemeriksa</span>
                            </div>

                            <div class="lis-form-group">
                                <label class="lis-form-label">KSO (Sewa Analyzer)</label>
                                <div class="lis-input-group">
                                    <span class="lis-input-addon">Rp</span>
                                    <input type="number" step="100" min="0" name="kso" id="param-kso" oninput="calcParamTotal()" value="0">
                                </div>
                                <span class="lis-form-hint">Sewa operasional alat per tes</span>
                            </div>

                            <div class="lis-form-group">
                                <label class="lis-form-label">BHP & Reagen</label>
                                <div class="lis-input-group">
                                    <span class="lis-input-addon">Rp</span>
                                    <input type="number" step="100" min="0" name="bhp" id="param-bhp" oninput="calcParamTotal()" value="0">
                                </div>
                                <span class="lis-form-hint">Tabung, reagen, syringe, tip</span>
                            </div>

                            <!-- Total Display Box SIMRS -->
                            <div class="lis-tariff-total flex-col !items-start justify-center p-3">
                                <span class="lis-tariff-total-label">TOTAL TARIF LAYANAN</span>
                                <span class="lis-tariff-total-value text-xl" id="param-total-display">Rp 0</span>
                                <span class="text-[10px] text-emerald-100 mt-0.5">Otomatis dihitung sistem</span>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- SIMRS Modal Footer -->
        <div class="lis-modal-footer">
            <div class="text-[11px] text-slate-500 flex items-center gap-1.5">
                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i>
                <span>Tervalidasi Master Lab SIMRS</span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="closeModalParameter()" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-100 transition-colors">
                    Batal
                </button>
                <button type="button" onclick="document.getElementById('form-parameter').requestSubmit()" class="px-5 py-2 rounded-lg bg-emerald-700 text-white text-xs font-semibold hover:bg-emerald-800 shadow-sm shadow-emerald-700/30 transition-all flex items-center gap-1.5 border border-emerald-600/40">
                    <i data-lucide="save" class="w-4 h-4 text-amber-300"></i>
                    <span>Simpan Parameter & Tarif</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- =================================================================== -->
<!-- MODAL 2: KELOLA NILAI RUJUKAN & KLASIFIKASI UMUR (STANDAR SIMRS)   -->
<!-- =================================================================== -->
<div id="modal-reference" class="lis-modal">
    <div class="lis-modal-box max-w-4xl">
        <!-- SIMRS Header -->
        <div class="lis-modal-header bg-gradient-to-r from-emerald-800 to-emerald-700">
            <div>
                <h3 class="lis-modal-title">
                    <i data-lucide="activity" class="w-5 h-5 text-emerald-200"></i>
                    <span>Standar Nilai Rujukan & Klasifikasi Umur Klinis</span>
                </h3>
                <p class="lis-modal-subtitle" id="ref-param-subtitle">Parameter: -</p>
            </div>
            <button type="button" onclick="closeModalReference()" class="lis-modal-close">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="lis-modal-body space-y-4">
            <!-- Section Tabel Standar Nilai Rujukan Terdaftar -->
            <div class="lis-section">
                <div class="lis-section-header">
                    <div class="lis-section-title">
                        <span class="lis-section-num bg-emerald-600">1</span>
                        <span>Daftar Nilai Rujukan Normal Berdasarkan Kelompok Umur & Gender</span>
                    </div>
                </div>
                <div class="p-0 overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-100 border-b border-slate-200 text-[10px] uppercase text-slate-700 font-bold">
                            <tr>
                                <th class="p-2.5">Gender</th>
                                <th class="p-2.5">Klasifikasi Umur</th>
                                <th class="p-2.5">Label Rentang</th>
                                <th class="p-2.5 font-mono text-center">Normal Bawah</th>
                                <th class="p-2.5 font-mono text-center">Normal Atas</th>
                                <th class="p-2.5 font-mono text-rose-700 text-center">Nilai Kritis (Low / High)</th>
                                <th class="p-2.5 font-bold text-emerald-800">Tampilan Lembar Hasil</th>
                                <th class="p-2.5 text-center w-16">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="ref-table-body" class="divide-y divide-slate-100">
                            <tr><td colspan="8" class="p-4 text-center text-slate-400">Memuat rujukan...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Section Input Rujukan Baru / Edit -->
            <div class="lis-section border-emerald-300">
                <div class="lis-section-header bg-emerald-50 border-emerald-200">
                    <div class="lis-section-title text-emerald-900" id="ref-form-title">
                        <span class="lis-section-num bg-emerald-700">2</span>
                        <span>Tambah / Perbarui Standar Nilai Rujukan</span>
                    </div>
                    <span class="text-[10px] text-emerald-700 font-semibold">Validasi Otomatis</span>
                </div>
                <div class="lis-section-body">
                    <form id="form-reference" onsubmit="saveReferenceRange(event)" class="space-y-3">
                        <input type="hidden" name="id" id="ref-id">
                        <input type="hidden" name="parameter_id" id="ref-parameter-id">

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="lis-form-label">Jenis Kelamin Pasien <span class="req">*</span></label>
                                <select name="gender" id="ref-gender" class="lis-form-control" required>
                                    <option value="ALL">Semua Gender (L / P)</option>
                                    <option value="L">Laki-laki (♂ Pria)</option>
                                    <option value="P">Perempuan (♀ Wanita)</option>
                                </select>
                            </div>

                            <div>
                                <label class="lis-form-label">Klasifikasi Tahap Usia <span class="req">*</span></label>
                                <select name="age_classification" id="ref-age-class" onchange="autoFillAgeDays()" class="lis-form-control" required>
                                    <option value="semua_umur">Semua Kelompok Umur</option>
                                    <option value="neonatus">Neonatus (0 - 28 hari)</option>
                                    <option value="bayi">Bayi (1 - 12 bulan)</option>
                                    <option value="anak">Anak-anak (1 - 12 tahun)</option>
                                    <option value="remaja">Remaja (13 - 17 tahun)</option>
                                    <option value="dewasa" selected>Dewasa (≥ 18 tahun)</option>
                                    <option value="lansia">Lansia (≥ 60 tahun)</option>
                                    <option value="hamil">Ibu Hamil</option>
                                </select>
                            </div>

                            <div>
                                <label class="lis-form-label">Label Umur Tampilan <span class="req">*</span></label>
                                <input type="text" name="age_label" id="ref-age-label" class="lis-form-control" placeholder="e.g. Dewasa (Pria), Anak (1-12 th)" required>
                            </div>

                            <div>
                                <label class="lis-form-label">Rentang Umur Min (Hari)</label>
                                <input type="number" min="0" name="age_min_days" id="ref-age-min-days" class="lis-form-control font-mono" value="6570">
                            </div>

                            <div>
                                <label class="lis-form-label">Rentang Umur Max (Hari)</label>
                                <input type="number" min="0" name="age_max_days" id="ref-age-max-days" class="lis-form-control font-mono" value="36500">
                            </div>

                            <div>
                                <label class="lis-form-label">Teks Nilai Normal (Kualitatif)</label>
                                <input type="text" name="text_normal" id="ref-text-normal" class="lis-form-control" placeholder="e.g. Negatif, Non Reaktif, 0 - 1">
                            </div>

                            <div>
                                <label class="lis-form-label">Batas Bawah Normal (Min)</label>
                                <input type="number" step="0.001" name="min_val" id="ref-min-val" oninput="autoFillDisplayRef()" class="lis-form-control font-mono" placeholder="13.5">
                            </div>

                            <div>
                                <label class="lis-form-label">Batas Atas Normal (Max)</label>
                                <input type="number" step="0.001" name="max_val" id="ref-max-val" oninput="autoFillDisplayRef()" class="lis-form-control font-mono" placeholder="16.5">
                            </div>

                            <div>
                                <label class="lis-form-label">Teks Tampilan Hasil Cetak <span class="req">*</span></label>
                                <input type="text" name="reference_display" id="ref-display" class="lis-form-control font-bold text-emerald-800" placeholder="♂ 13,5 - 16,5" required>
                            </div>

                            <div>
                                <label class="lis-form-label">Nilai Kritis Bawah (Panic Low)</label>
                                <input type="number" step="0.001" name="critical_low" id="ref-crit-low" class="lis-form-control font-mono text-rose-700" placeholder="7.0">
                                <span class="lis-form-hint">Muncul peringatan darurat jika < batas</span>
                            </div>

                            <div>
                                <label class="lis-form-label">Nilai Kritis Atas (Panic High)</label>
                                <input type="number" step="0.001" name="critical_high" id="ref-crit-high" class="lis-form-control font-mono text-rose-700" placeholder="20.0">
                                <span class="lis-form-hint">Muncul peringatan darurat jika > batas</span>
                            </div>

                            <div class="flex items-end">
                                <button type="submit" class="w-full h-9 rounded-md bg-emerald-700 text-white text-xs font-semibold hover:bg-emerald-800 transition-all flex items-center justify-center gap-1.5 shadow-sm">
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                    <span>Simpan Nilai Rujukan</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- SIMRS Modal Footer -->
        <div class="lis-modal-footer">
            <span class="text-[11px] text-slate-500">Nilai rujukan otomatis dipasangkan saat pasien registrasi berdasarkan umur & gender</span>
            <button type="button" onclick="closeModalReference()" class="px-5 py-2 rounded-lg bg-slate-200 text-slate-800 text-xs font-semibold hover:bg-slate-300 transition-colors">
                Tutup Jendela
            </button>
        </div>
    </div>
</div>

<!-- =================================================================== -->
<!-- MODAL 3: FORM TEMPLATE / PAKET LABORATORIUM (STANDAR SIMRS)        -->
<!-- =================================================================== -->
<div id="modal-template" class="lis-modal">
    <div class="lis-modal-box max-w-5xl">
        <!-- SIMRS Header (Melayu Riau Theme) -->
        <div class="lis-modal-header bg-gradient-to-r from-emerald-900 via-emerald-800 to-amber-950 border-b border-amber-400/30">
            <div>
                <h3 class="lis-modal-title">
                    <i data-lucide="layers" class="w-5 h-5 text-amber-300"></i>
                    <span id="modal-tpl-title">Form Master Paket Pemeriksaan Laboratorium</span>
                </h3>
                <p class="lis-modal-subtitle">UPT Labkesda Prov. Riau &bull; Standar SIMRS Kesehatan &bull; Konfigurasi Paket Layanan, Bundling Multi-Departemen & Skema Billing Tarif</p>
            </div>
            <button type="button" onclick="closeModalTemplate()" class="lis-modal-close">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="lis-modal-body">
            <form id="form-template" onsubmit="saveTemplate(event)" class="space-y-5">
                <input type="hidden" name="id" id="tpl-id">

                <!-- Section 1: Identifikasi & Konfigurasi Layanan Medis SIMRS -->
                <div class="lis-section">
                    <div class="lis-section-header bg-slate-100/80">
                        <div class="lis-section-title">
                            <span class="lis-section-num bg-emerald-700 text-amber-300 border border-amber-400/30">1</span>
                            <span>Identitas Paket & Klasifikasi Medis SIMRS</span>
                        </div>
                        <span class="text-[11px] font-medium text-slate-500">Standar Formularium SIMRS</span>
                    </div>
                    <div class="lis-section-body space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="lis-form-label">Departemen / Kategori Utama <span class="req">*</span></label>
                                <select name="category_id" id="tpl-category-id" class="lis-form-control font-semibold text-slate-800" onchange="fetchNextTemplateCode()" required>
                                    <option value="">-- Pilih Departemen --</option>
                                    <?php foreach ($categories as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= html_escape($c['category_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="lis-form-hint">Kategori induk pengelompokan paket di rekam medis</span>
                            </div>

                            <div>
                                <label class="lis-form-label flex items-center justify-between">
                                    <span>Kode Paket Laboratorium</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-full border border-emerald-300/60">
                                        <i data-lucide="sparkles" class="w-3 h-3 text-emerald-600"></i> Terisi Otomatis
                                    </span>
                                </label>
                                <div class="flex items-center gap-1.5">
                                    <input type="text" name="template_code" id="tpl-code" class="lis-form-control font-mono uppercase font-black text-emerald-950 bg-slate-50 focus:bg-white" placeholder="PKT-HEM-01">
                                    <button type="button" onclick="fetchNextTemplateCode()" class="px-2.5 h-9 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-1 transition-colors flex-shrink-0 border border-emerald-600 shadow-2xs" title="Generate Ulang Kode Paket">
                                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                                <span class="lis-form-hint">Format SIMRS: PKT-[Kategori]-[Nomor Urut]</span>
                            </div>

                            <div>
                                <label class="lis-form-label">Tipe Layanan SIMRS</label>
                                <select name="service_type" id="tpl-service-type" class="lis-form-control font-medium">
                                    <option value="all">Semua Layanan (Rawat Jalan & Inap)</option>
                                    <option value="rajal">Rawat Jalan / Poliklinik</option>
                                    <option value="ranap">Rawat Inap</option>
                                    <option value="igd">Gawat Darurat (IGD)</option>
                                    <option value="mcu">Medical Check Up (MCU)</option>
                                    <option value="rujukan">Rujukan Faskes Luar</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="lis-form-label">Nama Paket Pemeriksaan Laboratorium <span class="req">*</span></label>
                                <input type="text" name="template_name" id="tpl-name" class="lis-form-control font-bold text-slate-900 text-[13px]" placeholder="e.g. Paket Darah Lengkap 5 Diff (Hematologi Rutin), Paket MCU Pra-Nikah" required>
                            </div>

                            <div>
                                <label class="lis-form-label">Estimasi Waktu Hasil (TAT)</label>
                                <select name="tat_duration" id="tpl-tat-duration" class="lis-form-control font-medium">
                                    <option value="60 Menit (Cito)">60 Menit (Cito / Urgent)</option>
                                    <option value="120 Menit (2 Jam)" selected>120 Menit (2 Jam - Rutin)</option>
                                    <option value="Same Day (4-6 Jam)">Same Day (4 - 6 Jam)</option>
                                    <option value="24 Jam (1 Hari)">24 Jam (1 Hari Kerja)</option>
                                    <option value="2-3 Hari">2 - 3 Hari Kerja</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="lis-form-label">Instruksi Persiapan Pasien (Patient Preparation)</label>
                                <input type="text" name="patient_preparation" id="tpl-patient-prep" class="lis-form-control" placeholder="e.g. Puasa 10-12 jam sebelum pengambilan sampel darah, minum air putih diperbolehkan">
                            </div>

                            <div>
                                <label class="lis-form-label">Urutan & Status Paket</label>
                                <div class="flex items-center gap-3">
                                    <input type="number" name="sort_order" id="tpl-sort-order" class="lis-form-control w-20 font-mono" value="0" min="0" title="Urutan Tampil">
                                    <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                                        <input type="checkbox" name="is_active" id="tpl-is-active" value="1" checked class="rounded text-cyan-600 focus:ring-cyan-500 w-4 h-4">
                                        <span>Aktifkan Paket</span>
                                    </label>
                                </div>
                            </div>

                            <div class="md:col-span-3">
                                <label class="lis-form-label">Indikasi Klinis / Cakupan Diagnosa</label>
                                <textarea name="description" id="tpl-desc" rows="2" class="lis-form-control" placeholder="Cakupan evaluasi klinis dan indikasi medis paket pemeriksaan laboratorium ini..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Pemilihan Parameter Lab (Multi-Departemen Bundling) -->
                <div class="lis-section">
                    <div class="lis-section-header bg-slate-100/80">
                        <div class="lis-section-title">
                            <span class="lis-section-num bg-emerald-700 text-amber-300 border border-amber-400/30">2</span>
                            <span>Komponen Parameter Uji Lab (Bundling SIMRS)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span id="tpl-selected-count-badge" class="px-2.5 py-0.5 rounded-full bg-emerald-800 text-amber-300 font-bold text-[11px] shadow-xs border border-amber-400/40">
                                0 Parameter Terpilih
                            </span>
                        </div>
                    </div>
                    
                    <div class="p-3 sm:p-4 space-y-3 bg-slate-50/50">
                        <!-- Filter Bar & Search -->
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-2.5 rounded-xl border border-slate-200">
                            <!-- Category Pills Filter -->
                            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 text-xs" id="tpl-category-pills">
                                <button type="button" onclick="filterTplParamsCategory('all')" id="pill-cat-all" class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-700 text-white shadow-xs">Semua</button>
                                <?php foreach ($categories as $c): ?>
                                    <button type="button" onclick="filterTplParamsCategory(<?= $c['id'] ?>)" id="pill-cat-<?= $c['id'] ?>" class="px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 transition-colors whitespace-nowrap">
                                        <?= html_escape($c['category_name']) ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>

                            <!-- Fast Search & Quick Select with Autocomplete -->
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <div class="relative w-48 sm:w-56" id="tpl-param-search-wrapper">
                                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2"></i>
                                    <input type="text" id="tpl-param-search" oninput="filterTplParamsSearch(this.value)" placeholder="Cari parameter / kode..." class="h-8 pl-8 pr-7 rounded-lg border border-slate-300 text-xs text-slate-800 bg-white placeholder-slate-400 focus:outline-none focus:border-cyan-500 w-full">
                                    <button type="button" id="btn-clear-tpl-param-search" onclick="clearTplParamSearch()" class="hidden absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-0.5" title="Hapus pencarian">
                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <!-- Autocomplete Suggestions -->
                                    <div id="tpl-param-search-suggestions" class="hidden absolute z-30 left-0 right-0 mt-1 bg-white rounded-xl shadow-2xl border border-cyan-200 overflow-hidden divide-y divide-slate-100 max-h-56 overflow-y-auto"></div>
                                </div>
                                <button type="button" onclick="toggleSelectAllTplParams(true)" class="px-2 py-1 rounded border border-slate-300 bg-white hover:bg-slate-50 text-[11px] font-semibold text-slate-600 transition-colors" title="Centang Semua Yang Tampil">
                                    Pilih Semua
                                </button>
                                <button type="button" onclick="toggleSelectAllTplParams(false)" class="px-2 py-1 rounded border border-slate-300 bg-white hover:bg-slate-50 text-[11px] font-semibold text-rose-600 transition-colors" title="Hapus Semua Pilihan">
                                    Reset
                                </button>
                            </div>
                        </div>

                        <!-- Parameter Checklist Scrollable Grid -->
                        <div id="tpl-param-checklist" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 max-h-64 overflow-y-auto p-2.5 bg-white border border-slate-200 rounded-xl">
                            <p class="text-slate-400 p-4 col-span-3 text-center text-xs">Memuat daftar master parameter uji lab...</p>
                        </div>

                        <!-- Live Cost Comparison Card (SIMRS Standard) -->
                        <div class="p-3 bg-gradient-to-r from-sky-50 via-cyan-50 to-teal-50 border border-cyan-200 rounded-xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-cyan-600 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                                    <i data-lucide="calculator" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider block">Akumulasi Tarif Satuan Normal:</span>
                                    <span class="text-sm font-bold text-slate-800" id="tpl-individual-total-display">Rp 0</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <div>
                                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider block">Total Tarif Paket SIMRS:</span>
                                    <span class="text-sm font-extrabold text-cyan-800" id="tpl-package-total-display">Rp 0</span>
                                </div>
                                <div class="px-3 py-1.5 rounded-lg bg-emerald-100 text-emerald-800 border border-emerald-200 text-center">
                                    <span class="text-[9px] font-bold uppercase tracking-wider block">Diskon / Manfaat Pasien:</span>
                                    <span class="text-xs font-bold" id="tpl-savings-display">Hemat Rp 0 (0%)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Billing Tarif Paket SIMRS (Melayu Riau Theme) -->
                <div class="lis-section">
                    <div class="lis-section-header bg-emerald-50/70 border-emerald-200">
                        <div class="lis-section-title text-emerald-950 font-bold">
                            <span class="lis-section-num bg-emerald-700 text-amber-300 border border-amber-400/30">3</span>
                            <span>Komponen Rincian Tarif Paket (Billing SIMRS & Remunerasi)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] text-slate-700 font-bold">Penjamin / Kelompok:</span>
                            <select name="tariff_group" id="tpl-tariff-group" onchange="switchTemplateTariffGroup(this.value)" class="h-8 px-2.5 rounded-lg border border-emerald-300 bg-white text-xs font-bold text-emerald-900 focus:outline-none shadow-2xs">
                                <option value="umum">Umum / Reguler</option>
                                <option value="perusahaan">Rekanan / Corporate MCU</option>
                                <option value="bpjs">BPJS Kesehatan</option>
                                <option value="asuransi">Asuransi Swasta</option>
                            </select>
                        </div>
                    </div>

                    <div class="lis-section-body space-y-4">
                        <!-- Quick Discount Presets from Individual Sum -->
                        <div class="flex flex-wrap items-center justify-between gap-2 p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            <span class="text-[11px] font-semibold text-slate-600 flex items-center gap-1.5">
                                <i data-lucide="tag" class="w-3.5 h-3.5 text-amber-600"></i>
                                Otomatisasi Nilai Tarif Paket dari Total Satuan:
                            </span>
                            <div class="flex items-center gap-1.5">
                                <button type="button" onclick="applyQuickDiscount(0)" class="px-2.5 py-1 rounded-md bg-white border border-slate-200 hover:bg-slate-100 text-[11px] font-semibold text-slate-700 transition-colors">100% (Sama)</button>
                                <button type="button" onclick="applyQuickDiscount(10)" class="px-2.5 py-1 rounded-md bg-white border border-slate-200 hover:bg-amber-50 text-[11px] font-bold text-amber-800 transition-colors">Diskon 10%</button>
                                <button type="button" onclick="applyQuickDiscount(15)" class="px-2.5 py-1 rounded-md bg-white border border-slate-200 hover:bg-amber-50 text-[11px] font-bold text-amber-800 transition-colors">Diskon 15%</button>
                                <button type="button" onclick="applyQuickDiscount(20)" class="px-2.5 py-1 rounded-md bg-white border border-slate-200 hover:bg-amber-50 text-[11px] font-bold text-amber-800 transition-colors">Diskon 20%</button>
                                <button type="button" onclick="applyQuickDiscount(25)" class="px-2.5 py-1 rounded-md bg-white border border-slate-200 hover:bg-amber-50 text-[11px] font-bold text-amber-800 transition-colors">Diskon 25%</button>
                            </div>
                        </div>

                        <!-- 5 Komponen Billing Tarif RS -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3.5">
                            <div class="lis-form-group">
                                <label class="lis-form-label">Jasa Sarana / Fasilitas RS (Manajemen)</label>
                                <div class="lis-input-group">
                                    <span class="lis-input-addon">Rp</span>
                                    <input type="number" step="100" min="0" name="jasa_manajemen" id="tpl-jasa-manajemen" oninput="calcTplTotal()" value="0">
                                </div>
                            </div>

                            <div class="lis-form-group">
                                <label class="lis-form-label">Jasa Medis Dokter Sp.PK (Otorisasi)</label>
                                <div class="lis-input-group">
                                    <span class="lis-input-addon">Rp</span>
                                    <input type="number" step="100" min="0" name="jasa_dokter" id="tpl-jasa-dokter" oninput="calcTplTotal()" value="0">
                                </div>
                            </div>

                            <div class="lis-form-group">
                                <label class="lis-form-label">Jasa Petugas / Analis Laboratorium (ATLM)</label>
                                <div class="lis-input-group">
                                    <span class="lis-input-addon">Rp</span>
                                    <input type="number" step="100" min="0" name="jasa_petugas" id="tpl-jasa-petugas" oninput="calcTplTotal()" value="0">
                                </div>
                            </div>

                            <div class="lis-form-group">
                                <label class="lis-form-label">KSO (Sewa Analyzer / Reagen Mitra)</label>
                                <div class="lis-input-group">
                                    <span class="lis-input-addon">Rp</span>
                                    <input type="number" step="100" min="0" name="kso" id="tpl-kso" oninput="calcTplTotal()" value="0">
                                </div>
                            </div>

                            <div class="lis-form-group">
                                <label class="lis-form-label">BHP & Reagen Paket (Disposables)</label>
                                <div class="lis-input-group">
                                    <span class="lis-input-addon">Rp</span>
                                    <input type="number" step="100" min="0" name="bhp" id="tpl-bhp" oninput="calcTplTotal()" value="0">
                                </div>
                            </div>

                            <!-- Total Paket Display Box (Melayu Riau Theme) -->
                            <div class="lis-tariff-total !from-emerald-900 via-emerald-800 !to-teal-900 flex-col !items-start justify-center p-3.5 shadow-md border border-amber-400/40">
                                <span class="lis-tariff-total-label text-amber-300 font-bold">TOTAL TARIF PAKET SIMRS</span>
                                <span class="lis-tariff-total-value text-2xl font-black text-white" id="tpl-total-display">Rp 0</span>
                                <span class="text-[10px] text-amber-200/80 mt-0.5">Billing tunggal otomatis untuk seluruh uji paket Labkes Riau</span>
                            </div>

                            <div class="col-span-2 sm:col-span-3">
                                <label class="lis-form-label">Catatan Billing / Masa Berlaku Tarif</label>
                                <input type="text" name="notes" id="tpl-notes" class="lis-form-control" placeholder="e.g. Tarif paket berlaku untuk pendaftaran rawat jalan dan MCU reguler">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- SIMRS Modal Footer -->
        <div class="lis-modal-footer">
            <div class="text-[11px] text-slate-500 flex items-center gap-2">
                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-700"></i>
                <span class="font-medium">Form Terstandarisasi Sistem Informasi Manajemen Rumah Sakit (SIMRS)</span>
            </div>
            <div class="flex items-center gap-2.5">
                <button type="button" onclick="closeModalTemplate()" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-100 transition-colors">
                    Batal
                </button>
                <button type="button" onclick="document.getElementById('form-template').requestSubmit()" class="px-5 py-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-slate-950 font-bold text-xs shadow-md shadow-amber-500/25 transition-all flex items-center gap-2 border border-amber-400/40">
                    <i data-lucide="save" class="w-4 h-4 text-slate-950"></i>
                    <span>Simpan Template & Tarif Paket</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- =================================================================== -->
<!-- MODAL 4: FORM MASTER METODE PEMERIKSAAN (STANDAR SIMRS)            -->
<!-- =================================================================== -->
<div id="modal-metode" class="lis-modal">
    <div class="lis-modal-box max-w-xl">
        <!-- SIMRS Header -->
        <div class="lis-modal-header bg-gradient-to-r from-slate-800 to-slate-700">
            <div>
                <h3 class="lis-modal-title">
                    <i data-lucide="microscope" class="w-5 h-5 text-emerald-300"></i>
                    <span id="modal-metode-title">Form Master Metode Pemeriksaan</span>
                </h3>
                <p class="lis-modal-subtitle">Standarisasi SOP Akreditasi & Instrumen Analyzer Laboratorium</p>
            </div>
            <button type="button" onclick="closeModalMetode()" class="lis-modal-close">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="lis-modal-body">
            <form id="form-metode" onsubmit="saveMetode(event)" class="space-y-4">
                <input type="hidden" name="id" id="metode-id">

                <div class="lis-section">
                    <div class="lis-section-header">
                        <div class="lis-section-title">
                            <span class="lis-section-num bg-slate-700">1</span>
                            <span>Data Standar Metode Uji & Instrumen</span>
                        </div>
                    </div>
                    <div class="lis-section-body space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="lis-form-label flex items-center justify-between">
                                    <span>Kode Metode Lab</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-full border border-emerald-300/60">
                                        <i data-lucide="sparkles" class="w-3 h-3 text-emerald-600"></i> Terisi Otomatis
                                    </span>
                                </label>
                                <div class="flex items-center gap-1.5">
                                    <input type="text" name="method_code" id="metode-code" class="lis-form-control font-mono uppercase font-black text-emerald-950 bg-slate-50 focus:bg-white" placeholder="MET-010">
                                    <button type="button" onclick="fetchNextMethodCode()" class="px-2.5 h-9 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-1 transition-colors flex-shrink-0 border border-emerald-600 shadow-2xs" title="Generate Ulang Kode Otomatis">
                                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                                <span class="lis-form-hint">Dihasilkan otomatis berurutan oleh sistem</span>
                            </div>

                            <div>
                                <label class="lis-form-label flex items-center justify-between">
                                    <span>Kode Standar SOP / IKM</span>
                                    <span class="text-[10px] text-slate-400 font-mono">IKM/SOP Labkes</span>
                                </label>
                                <input type="text" name="standard_code" id="metode-standard" class="lis-form-control font-mono" placeholder="IKM/5.4.41/LKL-PR">
                                <span class="lis-form-hint">Nomor acuan dokumen SOP Labkes Riau</span>
                            </div>
                        </div>

                        <div>
                            <label class="lis-form-label">Nama Metode Pemeriksaan <span class="req">*</span></label>
                            <input type="text" name="method_name" id="metode-name" class="lis-form-control font-semibold text-slate-800" placeholder="e.g. Automatic B.C Counter, Westergreen, Manual" required>
                        </div>

                        <div>
                            <label class="lis-form-label">Nama Alat / Instrumen Analyzer Otomatis</label>
                            <input type="text" name="instrument_name" id="metode-instrument" class="lis-form-control" placeholder="e.g. Hematology Analyzer Mindray BC-5380 5-Diff">
                            <span class="lis-form-hint">Merek dan tipe alat analyzer yang terhubung ke LIS</span>
                        </div>

                        <div>
                            <label class="lis-form-label">Keterangan / Prinsip Reaksi Singkat</label>
                            <textarea name="description" id="metode-desc" rows="3" class="lis-form-control" placeholder="Penjelasan metode kerja uji laboratorium..."></textarea>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- SIMRS Modal Footer -->
        <div class="lis-modal-footer">
            <span class="text-[11px] text-slate-500">Standar ISO 15189 & Akreditasi KARS</span>
            <div class="flex items-center gap-2">
                <button type="button" onclick="closeModalMetode()" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-100 transition-colors">
                    Batal
                </button>
                <button type="button" onclick="document.getElementById('form-metode').requestSubmit()" class="px-5 py-2 rounded-lg bg-emerald-700 text-white text-xs font-semibold hover:bg-emerald-800 shadow-sm transition-all flex items-center gap-1.5">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Metode</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- JAVASCRIPT LOGIC & DATATABLES SERVER-SIDE -->
<script>
let tableParam = null;
let tableTemplate = null;
let tableMethod = null;
let currentTab = '<?= $active_tab ?? "parameter" ?>';

document.addEventListener('DOMContentLoaded', () => {
    lucide.createIcons();
    loadLabSummary();
    switchTab(currentTab);
});

// Format Rupiah
function formatRupiah(amount) {
    return 'Rp ' + Number(amount || 0).toLocaleString('id-ID');
}

// ==========================================
// 1. STATISTIK MASTER LAB
// ==========================================
function loadLabSummary() {
    fetch('<?= site_url('tarif/summary') ?>')
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success' && res.data) {
                document.getElementById('stat-param').textContent = res.data.total_parameter;
                document.getElementById('stat-template').textContent = res.data.total_template;
                document.getElementById('stat-metode').textContent = res.data.total_metode;
                document.getElementById('stat-kategori').textContent = res.data.total_kategori;
            }
        });
}

// ==========================================
// 2. TAB SWITCHER
// ==========================================
function switchTab(tab) {
    currentTab = tab;
    ['parameter', 'template', 'metode'].forEach(t => {
        const btn = document.getElementById('btn-tab-' + t);
        const content = document.getElementById('tab-content-' + t);
        if (t === tab) {
            btn.className = 'flex-1 py-3 px-4 rounded-xl text-xs font-semibold transition-all flex items-center justify-center gap-2 bg-emerald-700 text-white shadow-sm shadow-emerald-700/20';
            content.classList.remove('hidden');
        } else {
            btn.className = 'flex-1 py-3 px-4 rounded-xl text-xs font-semibold transition-all flex items-center justify-center gap-2 text-slate-600 hover:bg-slate-50';
            content.classList.add('hidden');
        }
    });

    if (tab === 'parameter') {
        if (!tableParam) initTableParameters();
        else tableParam.ajax.reload(null, false);
    } else if (tab === 'template') {
        if (!tableTemplate) initTableTemplates();
        else tableTemplate.ajax.reload(null, false);
    } else if (tab === 'metode') {
        if (!tableMethod) initTableMethods();
        else tableMethod.ajax.reload(null, false);
    }

    setTimeout(() => lucide.createIcons(), 150);
}

// ==========================================
// 3. DATATABLES PARAMETER & TARIF SATUAN
// ==========================================
function initTableParameters() {
    tableParam = $('#table-parameters').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= site_url('tarif/fetch_parameters') ?>',
            type: 'GET',
            data: function(d) {
                d.category_id = $('#filter-param-category').val();
                d.tariff_group = $('#filter-param-tariff-group').val();
            }
        },
        columns: [
            { data: 'no_urut', orderable: false, searchable: false, className: 'text-center font-medium' },
            { data: 'parameter_code', className: 'font-mono font-bold text-slate-800' },
            { data: 'parameter_name', className: 'font-medium text-slate-900' },
            { 
                data: 'category_name',
                render: function(data) {
                    return '<span class="lis-badge lis-badge-secondary">' + (data || '-') + '</span>';
                }
            },
            { data: 'unit', className: 'text-center font-medium text-slate-600' },
            { 
                data: 'method_name',
                render: function(data, type, row) {
                    if (!data) return '<span class="text-slate-400">-</span>';
                    return '<span class="text-slate-700 font-medium">' + data + '</span>' + 
                           (row.standard_code ? '<br><span class="text-[9px] font-mono text-slate-400">' + row.standard_code + '</span>' : '');
                }
            },
            { 
                data: 'ref_display_summary',
                render: function(data, type, row) {
                    let btnRef = '<button type="button" onclick="openModalReference(' + row.id + ', \'' + row.parameter_name.replace(/'/g, "\\'") + '\')" class="inline-flex items-center gap-1 text-[10px] text-sky-700 font-semibold hover:underline mt-1"><i data-lucide="edit-2" class="w-3 h-3"></i> Atur Rujukan (' + row.ref_range_count + ')</button>';
                    if (!data) {
                        return '<span class="text-amber-600 text-[10px]">Belum diatur</span><br>' + btnRef;
                    }
                    return '<div class="text-[10px] leading-tight text-slate-600">' + data + '</div>' + btnRef;
                }
            },
            { data: 'jasa_manajemen', className: 'text-right font-mono', render: formatRupiah },
            { data: 'jasa_dokter', className: 'text-right font-mono', render: formatRupiah },
            { data: 'jasa_petugas', className: 'text-right font-mono', render: formatRupiah },
            { data: 'kso', className: 'text-right font-mono', render: formatRupiah },
            { data: 'bhp', className: 'text-right font-mono', render: formatRupiah },
            { 
                data: 'total_tarif', 
                className: 'text-right font-mono font-extrabold text-sky-900 bg-sky-50/40', 
                render: formatRupiah 
            },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    let html = '<div class="flex items-center justify-center gap-1">';
                    <?php if ($can_edit): ?>
                    html += '<button type="button" onclick="openModalParameter(\'edit\', ' + data + ')" class="p-1 rounded bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors" title="Edit Parameter & Tarif"><i data-lucide="edit-3" class="w-3.5 h-3.5"></i></button>';
                    <?php endif; ?>
                    <?php if ($can_delete): ?>
                    html += '<button type="button" onclick="deleteParameter(' + data + ', \'' + row.parameter_name.replace(/'/g, "\\'") + '\')" class="p-1 rounded bg-rose-50 text-rose-700 hover:bg-rose-100 transition-colors" title="Hapus"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>';
                    <?php endif; ?>
                    html += '</div>';
                    return html;
                }
            }
        ],
        order: [[1, 'asc']],
        pageLength: 15,
        lengthMenu: [10, 15, 25, 50, 100],
        language: {
            processing: "Memuat parameter...",
            search: "Cari Parameter:",
            lengthMenu: "Tampil _MENU_",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ parameter",
            infoEmpty: "0 parameter",
            emptyTable: "Tidak ada data parameter laboratorium.",
            paginate: { first: "«", previous: "‹", next: "›", last: "»" }
        },
        drawCallback: function() {
            lucide.createIcons();
        }
    });
}

function reloadParamTable() {
    if (tableParam) tableParam.ajax.reload();
}

// Auto Hitung Total Tarif Parameter
function calcParamTotal() {
    const m = parseFloat(document.getElementById('param-jasa-manajemen').value) || 0;
    const d = parseFloat(document.getElementById('param-jasa-dokter').value) || 0;
    const p = parseFloat(document.getElementById('param-jasa-petugas').value) || 0;
    const k = parseFloat(document.getElementById('param-kso').value) || 0;
    const b = parseFloat(document.getElementById('param-bhp').value) || 0;
    const total = m + d + p + k + b;
    document.getElementById('param-total-display').textContent = formatRupiah(total);
}

function openModalParameter(mode, id = null) {
    const modal = document.getElementById('modal-parameter');
    const form = document.getElementById('form-parameter');
    form.reset();
    document.getElementById('param-id').value = '';
    document.getElementById('param-total-display').textContent = 'Rp 0';
    document.getElementById('modal-param-title').textContent = mode === 'create' ? 'Tambah Parameter & Tarif Lab' : 'Edit Parameter & Tarif Lab';

    if (mode === 'edit' && id) {
        const group = $('#filter-param-tariff-group').val() || 'umum';
        fetch('<?= site_url('tarif/get_parameter') ?>/' + id + '?tariff_group=' + group)
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    const d = res.data;
                    document.getElementById('param-id').value = d.id;
                    document.getElementById('param-category-id').value = d.category_id;
                    document.getElementById('param-code').value = d.parameter_code;
                    document.getElementById('param-name').value = d.parameter_name;
                    document.getElementById('param-unit').value = d.unit || '';
                    document.getElementById('param-method-id').value = d.method_id || '';
                    document.getElementById('param-data-type').value = d.data_type || 'numeric';
                    document.getElementById('param-sort-order').value = d.sort_order || 0;

                    document.getElementById('param-tariff-group').value = group;
                    document.getElementById('param-jasa-manajemen').value = d.jasa_manajemen || 0;
                    document.getElementById('param-jasa-dokter').value = d.jasa_dokter || 0;
                    document.getElementById('param-jasa-petugas').value = d.jasa_petugas || 0;
                    document.getElementById('param-kso').value = d.kso || 0;
                    document.getElementById('param-bhp').value = d.bhp || 0;
                    calcParamTotal();
                }
            });
    } else {
        const catSelect = document.getElementById('param-category-id');
        if (catSelect && catSelect.options.length > 1 && !catSelect.value) {
            catSelect.selectedIndex = 1;
        }
        fetchNextParamCode();
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
    lucide.createIcons();
}

function fetchNextParamCode() {
    const catSelect = document.getElementById('param-category-id');
    const catId = catSelect ? catSelect.value : '';
    const url = '<?= site_url('tarif/get_next_parameter_code') ?>' + (catId ? '/' + catId : '');
    fetch(url)
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success' && res.code) {
                document.getElementById('param-code').value = res.code;
            }
        })
        .catch(err => console.error(err));
}

function closeModalParameter() {
    const modal = document.getElementById('modal-parameter');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
}

function saveParameter(e) {
    e.preventDefault();
    const form = document.getElementById('form-parameter');
    const fd = new FormData(form);

    fetch('<?= site_url('tarif/save_parameter') ?>', {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            LIS.toast('success', res.message);
            closeModalParameter();
            reloadParamTable();
            loadLabSummary();
        } else {
            LIS.alert('Gagal Menyimpan', res.message || 'Terjadi kesalahan.', 'error');
        }
    })
    .catch(() => LIS.alert('Error', 'Gagal tersambung ke server.', 'error'));
}

function deleteParameter(id, name) {
    LIS.confirm('Hapus Parameter', 'Yakin menghapus parameter <strong>' + name + '</strong> beserta tarif dan rujukan terkait?', 'Ya, Hapus')
        .then(result => {
            if (result.isConfirmed) {
                fetch('<?= site_url('tarif/delete_parameter') ?>/' + id, {
                    method: 'DELETE',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        LIS.toast('success', res.message);
                        reloadParamTable();
                        loadLabSummary();
                    } else {
                        LIS.alert('Gagal', res.message, 'error');
                    }
                });
            }
        });
}

// ==========================================
// 4. MODAL NILAI RUJUKAN & KLASIFIKASI UMUR
// ==========================================
function openModalReference(paramId, paramName) {
    const modal = document.getElementById('modal-reference');
    document.getElementById('ref-parameter-id').value = paramId;
    document.getElementById('ref-param-subtitle').textContent = 'Parameter: ' + paramName;
    resetRefForm();
    loadReferenceTable(paramId);

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
    lucide.createIcons();
}

function closeModalReference() {
    const modal = document.getElementById('modal-reference');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
    reloadParamTable();
}

function resetRefForm() {
    document.getElementById('ref-id').value = '';
    document.getElementById('ref-gender').value = 'ALL';
    document.getElementById('ref-age-class').value = 'dewasa';
    document.getElementById('ref-age-label').value = 'Dewasa (≥18 th)';
    document.getElementById('ref-age-min-days').value = '6570';
    document.getElementById('ref-age-max-days').value = '36500';
    document.getElementById('ref-text-normal').value = '';
    document.getElementById('ref-min-val').value = '';
    document.getElementById('ref-max-val').value = '';
    document.getElementById('ref-crit-low').value = '';
    document.getElementById('ref-crit-high').value = '';
    document.getElementById('ref-display').value = '';
    document.getElementById('ref-form-title').innerHTML = '<i data-lucide="plus-circle" class="w-4 h-4 text-emerald-600"></i> Tambah Standar Nilai Rujukan Baru';
    lucide.createIcons();
}

function autoFillAgeDays() {
    const ac = document.getElementById('ref-age-class').value;
    const minD = document.getElementById('ref-age-min-days');
    const maxD = document.getElementById('ref-age-max-days');
    const lbl = document.getElementById('ref-age-label');

    if (ac === 'neonatus') {
        minD.value = 0; maxD.value = 28; lbl.value = 'Neonatus (0-28 hari)';
    } else if (ac === 'bayi') {
        minD.value = 29; maxD.value = 365; lbl.value = 'Bayi (1-12 bulan)';
    } else if (ac === 'anak') {
        minD.value = 366; maxD.value = 4380; lbl.value = 'Anak-anak (1-12 tahun)';
    } else if (ac === 'remaja') {
        minD.value = 4381; maxD.value = 6205; lbl.value = 'Remaja (13-17 tahun)';
    } else if (ac === 'dewasa') {
        minD.value = 6570; maxD.value = 36500; lbl.value = 'Dewasa (≥ 18 tahun)';
    } else if (ac === 'lansia') {
        minD.value = 21900; maxD.value = 36500; lbl.value = 'Lansia (≥ 60 tahun)';
    } else if (ac === 'hamil') {
        minD.value = 5475; maxD.value = 18250; lbl.value = 'Ibu Hamil';
    } else {
        minD.value = 0; maxD.value = 36500; lbl.value = 'Semua Umur';
    }
}

function autoFillDisplayRef() {
    const min = document.getElementById('ref-min-val').value;
    const max = document.getElementById('ref-max-val').value;
    const gender = document.getElementById('ref-gender').value;
    const prefix = gender === 'L' ? '♂ ' : (gender === 'P' ? '♀ ' : '');
    if (min && max) {
        document.getElementById('ref-display').value = prefix + min + ' - ' + max;
    }
}

function loadReferenceTable(paramId) {
    const tbody = document.getElementById('ref-table-body');
    tbody.innerHTML = '<tr><td colspan="8" class="p-4 text-center text-slate-400">Memuat rujukan...</td></tr>';

    fetch('<?= site_url('tarif/get_reference_ranges') ?>/' + paramId)
        .then(r => r.json())
        .then(res => {
            tbody.innerHTML = '';
            if (res.status === 'success' && res.data && res.data.length > 0) {
                res.data.forEach(r => {
                    const gBadge = r.gender === 'L' ? '<span class="lis-badge lis-badge-info">♂ Pria</span>' : 
                                  (r.gender === 'P' ? '<span class="lis-badge lis-badge-danger">♀ Wanita</span>' : 
                                  '<span class="lis-badge lis-badge-secondary">Semua</span>');
                    const crit = (r.critical_low || r.critical_high) ? (r.critical_low || '-') + ' / ' + (r.critical_high || '-') : '-';
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-50';
                    tr.innerHTML = `
                        <td class="p-2.5 font-medium">${gBadge}</td>
                        <td class="p-2.5 font-semibold text-slate-800">${r.age_label}</td>
                        <td class="p-2.5 text-slate-500 font-mono text-[10px]">${r.age_min_days} - ${r.age_max_days} hr</td>
                        <td class="p-2.5 font-mono">${r.min_val !== null ? r.min_val : '-'}</td>
                        <td class="p-2.5 font-mono">${r.max_val !== null ? r.max_val : '-'}</td>
                        <td class="p-2.5 font-mono text-rose-600">${crit}</td>
                        <td class="p-2.5 font-bold text-emerald-800">${r.reference_display}</td>
                        <td class="p-2.5 text-center">
                            <button type="button" onclick="deleteReferenceRange(${r.id})" class="p-1 rounded bg-rose-50 text-rose-700 hover:bg-rose-100" title="Hapus"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="8" class="p-4 text-center text-slate-400">Belum ada nilai rujukan untuk parameter ini. Silakan tambahkan pada form di bawah.</td></tr>';
            }
            lucide.createIcons();
        });
}

function saveReferenceRange(e) {
    e.preventDefault();
    const form = document.getElementById('form-reference');
    const fd = new FormData(form);

    fetch('<?= site_url('tarif/save_reference_range') ?>', {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            LIS.toast('success', res.message);
            const pid = document.getElementById('ref-parameter-id').value;
            loadReferenceTable(pid);
            resetRefForm();
        } else {
            LIS.alert('Gagal', res.message, 'error');
        }
    });
}

function deleteReferenceRange(id) {
    LIS.confirm('Hapus Rujukan', 'Hapus baris nilai rujukan ini?', 'Ya, Hapus')
        .then(result => {
            if (result.isConfirmed) {
                fetch('<?= site_url('tarif/delete_reference_range') ?>/' + id, {
                    method: 'DELETE',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        LIS.toast('success', res.message);
                        const pid = document.getElementById('ref-parameter-id').value;
                        loadReferenceTable(pid);
                    }
                });
            }
        });
}

// ==========================================
// 5. DATATABLES TEMPLATE / PAKET LAB
// ==========================================
function initTableTemplates() {
    tableTemplate = $('#table-templates').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= site_url('tarif/fetch_templates') ?>',
            type: 'GET',
            data: function(d) {
                d.category_id = $('#filter-template-category').val();
                d.tariff_group = $('#filter-template-tariff-group').val();
            }
        },
        columns: [
            { data: 'no_urut', orderable: false, searchable: false, className: 'text-center font-medium' },
            { data: 'template_code', className: 'font-mono font-bold text-slate-800' },
            { data: 'template_name', className: 'font-bold text-slate-900' },
            { 
                data: 'category_name',
                render: function(data) {
                    return '<span class="lis-badge lis-badge-secondary">' + (data || '-') + '</span>';
                }
            },
            { 
                data: 'item_names',
                render: function(data, type, row) {
                    if (!data) return '<span class="text-amber-600 text-[10px]">0 parameter</span>';
                    return '<div class="text-[10px] text-slate-600 max-w-xs truncate" title="' + data + '"><strong class="text-cyan-800">' + row.item_count + ' tes:</strong> ' + data + '</div>';
                }
            },
            { data: 'jasa_manajemen', className: 'text-right font-mono', render: formatRupiah },
            { data: 'jasa_dokter', className: 'text-right font-mono', render: formatRupiah },
            { data: 'jasa_petugas', className: 'text-right font-mono', render: formatRupiah },
            { data: 'kso', className: 'text-right font-mono', render: formatRupiah },
            { data: 'bhp', className: 'text-right font-mono', render: formatRupiah },
            { 
                data: 'total_tarif', 
                className: 'text-right font-mono font-extrabold text-cyan-900 bg-cyan-50/40', 
                render: formatRupiah 
            },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    let html = '<div class="flex items-center justify-center gap-1">';
                    <?php if ($can_edit): ?>
                    html += '<button type="button" onclick="openModalTemplate(\'edit\', ' + data + ')" class="p-1 rounded bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors" title="Edit Template & Tarif"><i data-lucide="edit-3" class="w-3.5 h-3.5"></i></button>';
                    <?php endif; ?>
                    <?php if ($can_delete): ?>
                    html += '<button type="button" onclick="deleteTemplate(' + data + ', \'' + row.template_name.replace(/'/g, "\\'") + '\')" class="p-1 rounded bg-rose-50 text-rose-700 hover:bg-rose-100 transition-colors" title="Hapus"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>';
                    <?php endif; ?>
                    html += '</div>';
                    return html;
                }
            }
        ],
        order: [[1, 'asc']],
        pageLength: 15,
        lengthMenu: [10, 15, 25, 50],
        language: {
            processing: "Memuat template...",
            search: "Cari Paket:",
            lengthMenu: "Tampil _MENU_",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ paket",
            infoEmpty: "0 paket",
            emptyTable: "Tidak ada data template laboratorium.",
            paginate: { first: "«", previous: "‹", next: "›", last: "»" }
        },
        drawCallback: function() {
            lucide.createIcons();
        }
    });
}

function reloadTemplateTable() {
    if (tableTemplate) tableTemplate.ajax.reload();
}

let allTemplateParameters = [];
let currentTemplateCheckedIds = new Set();
let activeParamCategoryFilter = 'all';
let activeParamSearchQuery = '';

function reloadTemplateTable() {
    if (tableTemplate) tableTemplate.ajax.reload();
}

function fetchNextTemplateCode() {
    const catSelect = document.getElementById('tpl-category-id');
    const catId = catSelect ? catSelect.value : '';
    const url = '<?= site_url('tarif/get_next_template_code') ?>' + (catId ? '/' + catId : '');
    fetch(url)
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success' && res.code) {
                document.getElementById('tpl-code').value = res.code;
            }
        })
        .catch(err => console.error(err));
}

function generatePackageCode() {
    fetchNextTemplateCode();
}

function calcTplTotal() {
    const m = parseFloat(document.getElementById('tpl-jasa-manajemen').value) || 0;
    const d = parseFloat(document.getElementById('tpl-jasa-dokter').value) || 0;
    const p = parseFloat(document.getElementById('tpl-jasa-petugas').value) || 0;
    const k = parseFloat(document.getElementById('tpl-kso').value) || 0;
    const b = parseFloat(document.getElementById('tpl-bhp').value) || 0;
    const total = m + d + p + k + b;

    document.getElementById('tpl-total-display').textContent = formatRupiah(total);
    document.getElementById('tpl-package-total-display').textContent = formatRupiah(total);

    updateCostComparison(total);
}

function updateCostComparison(packageTotal = null) {
    if (packageTotal === null) {
        const m = parseFloat(document.getElementById('tpl-jasa-manajemen').value) || 0;
        const d = parseFloat(document.getElementById('tpl-jasa-dokter').value) || 0;
        const p = parseFloat(document.getElementById('tpl-jasa-petugas').value) || 0;
        const k = parseFloat(document.getElementById('tpl-kso').value) || 0;
        const b = parseFloat(document.getElementById('tpl-bhp').value) || 0;
        packageTotal = m + d + p + k + b;
    }

    // Hitung total tarif satuan dari parameter yang dicentang
    let individualSum = 0;
    let selectedCount = 0;

    allTemplateParameters.forEach(item => {
        if (currentTemplateCheckedIds.has(parseInt(item.id))) {
            selectedCount++;
            individualSum += parseFloat(item.standard_tariff) || 0;
        }
    });

    document.getElementById('tpl-selected-count-badge').textContent = `${selectedCount} Parameter Terpilih`;
    document.getElementById('tpl-individual-total-display').textContent = formatRupiah(individualSum);
    document.getElementById('tpl-package-total-display').textContent = formatRupiah(packageTotal);

    const savingsElem = document.getElementById('tpl-savings-display');
    if (individualSum > 0 && packageTotal > 0) {
        const diff = individualSum - packageTotal;
        if (diff > 0) {
            const pct = Math.round((diff / individualSum) * 100);
            savingsElem.textContent = `Hemat ${formatRupiah(diff)} (${pct}%)`;
            savingsElem.className = 'text-xs font-bold text-emerald-800';
        } else if (diff === 0) {
            savingsElem.textContent = 'Tarif Satuan Standar';
            savingsElem.className = 'text-xs font-semibold text-slate-700';
        } else {
            const markup = Math.abs(diff);
            savingsElem.textContent = `Penyesuaian SIMRS (+${formatRupiah(markup)})`;
            savingsElem.className = 'text-xs font-semibold text-sky-800';
        }
    } else {
        savingsElem.textContent = 'Hemat Rp 0 (0%)';
        savingsElem.className = 'text-xs font-semibold text-slate-500';
    }
}

function loadAllParametersForTemplate(tariff_group = 'umum', checkedIds = []) {
    const container = document.getElementById('tpl-param-checklist');
    container.innerHTML = '<p class="text-slate-400 p-4 col-span-3 text-center text-xs">Memuat katalog parameter laboratorium SIMRS...</p>';

    currentTemplateCheckedIds = new Set(checkedIds.map(id => parseInt(id)));

    fetch('<?= site_url('tarif/get_parameters_list') ?>?tariff_group=' + encodeURIComponent(tariff_group))
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success' && res.data) {
                allTemplateParameters = res.data;
                renderTemplateParameters();
                updateCostComparison();
            } else {
                container.innerHTML = '<p class="text-rose-500 p-4 col-span-3 text-center text-xs">Gagal memuat parameter laboratorium.</p>';
            }
        })
        .catch(() => {
            container.innerHTML = '<p class="text-rose-500 p-4 col-span-3 text-center text-xs">Koneksi bermasalah saat memuat parameter.</p>';
        });
}

function renderTemplateParameters() {
    const container = document.getElementById('tpl-param-checklist');
    container.innerHTML = '';

    const filtered = allTemplateParameters.filter(p => {
        // Filter kategori
        if (activeParamCategoryFilter !== 'all' && parseInt(p.category_id) !== parseInt(activeParamCategoryFilter)) {
            return false;
        }
        // Filter pencarian teks
        if (activeParamSearchQuery) {
            const name = (p.parameter_name || '').toLowerCase();
            const code = (p.parameter_code || '').toLowerCase();
            const unit = (p.unit || '').toLowerCase();
            if (!name.includes(activeParamSearchQuery) && !code.includes(activeParamSearchQuery) && !unit.includes(activeParamSearchQuery)) {
                return false;
            }
        }
        return true;
    });

    if (filtered.length === 0) {
        container.innerHTML = '<p class="text-slate-400 p-4 col-span-3 text-center text-xs">Tidak ada parameter yang sesuai dengan filter pencarian.</p>';
        return;
    }

    filtered.forEach(p => {
        const pId = parseInt(p.id);
        const isChecked = currentTemplateCheckedIds.has(pId);
        const card = document.createElement('label');
        card.className = `flex items-start gap-2.5 p-2 rounded-xl border transition-all cursor-pointer select-none ${isChecked ? 'bg-cyan-50/70 border-cyan-300 shadow-2xs' : 'bg-slate-50/60 border-slate-200 hover:bg-slate-100/60'}`;

        card.innerHTML = `
            <input type="checkbox" name="parameter_ids[]" value="${p.id}" ${isChecked ? 'checked' : ''}
                   onchange="onParamToggleChange(this, ${pId})"
                   class="rounded text-cyan-600 focus:ring-cyan-500 w-4 h-4 mt-0.5 flex-shrink-0">
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-1">
                    <span class="font-bold text-slate-800 text-xs truncate" title="${p.parameter_name}">${p.parameter_name}</span>
                </div>
                <div class="flex items-center justify-between gap-1.5 mt-0.5 text-[10px]">
                    <span class="font-mono text-cyan-800 font-semibold">${p.parameter_code}</span>
                    <span class="px-1.5 py-0.2 rounded bg-slate-200/70 text-slate-600 font-medium">${p.category_name || 'Lab'}</span>
                </div>
                <div class="flex items-center justify-between gap-1 mt-1 text-[10px]">
                    <span class="text-slate-400 font-medium truncate">${p.unit ? p.unit : 'Hasil Kualitatif'}</span>
                    <span class="font-bold text-slate-700">${formatRupiah(p.standard_tariff || 0)}</span>
                </div>
            </div>
        `;
        container.appendChild(card);
    });
}

function onParamToggleChange(checkbox, paramId) {
    if (checkbox.checked) {
        currentTemplateCheckedIds.add(paramId);
    } else {
        currentTemplateCheckedIds.delete(paramId);
    }

    const parentLabel = checkbox.closest('label');
    if (parentLabel) {
        if (checkbox.checked) {
            parentLabel.className = 'flex items-start gap-2.5 p-2 rounded-xl border transition-all cursor-pointer select-none bg-cyan-50/70 border-cyan-300 shadow-2xs';
        } else {
            parentLabel.className = 'flex items-start gap-2.5 p-2 rounded-xl border transition-all cursor-pointer select-none bg-slate-50/60 border-slate-200 hover:bg-slate-100/60';
        }
    }

    updateCostComparison();
}

function filterTplParamsCategory(catId) {
    activeParamCategoryFilter = catId;
    
    // Update visual tab pills
    const pills = document.querySelectorAll('#tpl-category-pills button');
    pills.forEach(p => {
        p.className = 'px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors whitespace-nowrap';
    });
    
    const activePill = document.getElementById(`pill-cat-${catId}`);
    if (activePill) {
        activePill.className = 'px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-700 text-white shadow-xs whitespace-nowrap';
    }

    renderTemplateParameters();
}

function filterTplParamsSearch(val) {
    activeParamSearchQuery = (val || '').toLowerCase().trim();
    const btnClear = document.getElementById('btn-clear-tpl-param-search');
    const suggestBox = document.getElementById('tpl-param-search-suggestions');
    if (btnClear) btnClear.classList.toggle('hidden', !activeParamSearchQuery);

    renderTemplateParameters();

    if (suggestBox) {
        if (!activeParamSearchQuery) {
            suggestBox.classList.add('hidden');
            suggestBox.innerHTML = '';
        } else {
            const matches = allTemplateParameters.filter(p => {
                const name = (p.parameter_name || '').toLowerCase();
                const code = (p.parameter_code || '').toLowerCase();
                return name.includes(activeParamSearchQuery) || code.includes(activeParamSearchQuery);
            }).slice(0, 6);

            if (matches.length === 0) {
                suggestBox.classList.add('hidden');
                suggestBox.innerHTML = '';
            } else {
                let html = '<div class="px-3 py-1.5 bg-slate-50 text-[10px] font-bold text-slate-500 flex justify-between"><span>Saran Parameter Uji</span><span class="text-cyan-700">' + matches.length + ' saran</span></div>';
                matches.forEach(p => {
                    const isChecked = currentTemplateCheckedIds.has(parseInt(p.id));
                    html += '<button type="button" onclick="selectTplParamSuggestion(' + p.id + ')" class="w-full text-left px-3 py-2 text-xs hover:bg-cyan-50 flex items-center justify-between group transition-colors">';
                    html += '<div class="truncate"><span class="font-bold text-slate-800">' + p.parameter_name + '</span><span class="font-mono text-[10px] text-slate-500 ml-1.5">[' + (p.parameter_code || '') + ']</span></div>';
                    html += '<span class="text-[10px] font-bold px-1.5 py-0.5 rounded ' + (isChecked ? 'bg-cyan-100 text-cyan-800' : 'bg-slate-100 text-slate-600') + '">' + (isChecked ? '✓ Terpilih' : '+ Pilih') + '</span>';
                    html += '</button>';
                });
                suggestBox.innerHTML = html;
                suggestBox.classList.remove('hidden');
            }
        }
    }
}

function selectTplParamSuggestion(paramId) {
    const pId = parseInt(paramId);
    currentTemplateCheckedIds.add(pId);
    renderTemplateParameters();
    updateCostComparison();
    const suggestBox = document.getElementById('tpl-param-search-suggestions');
    if (suggestBox) suggestBox.classList.add('hidden');
}

function clearTplParamSearch() {
    const input = document.getElementById('tpl-param-search');
    if (input) {
        input.value = '';
        filterTplParamsSearch('');
    }
}

document.addEventListener('click', function(e) {
    const wrapper = document.getElementById('tpl-param-search-wrapper');
    const suggestBox = document.getElementById('tpl-param-search-suggestions');
    if (wrapper && suggestBox && !wrapper.contains(e.target)) {
        suggestBox.classList.add('hidden');
    }
});

function toggleSelectAllTplParams(select) {
    const checkboxes = document.querySelectorAll('#tpl-param-checklist input[type="checkbox"]');
    checkboxes.forEach(cb => {
        cb.checked = select;
        const pId = parseInt(cb.value);
        if (select) {
            currentTemplateCheckedIds.add(pId);
        } else {
            currentTemplateCheckedIds.delete(pId);
        }
    });
    renderTemplateParameters();
    updateCostComparison();
}

function applyQuickDiscount(pct) {
    let individualSum = 0;
    allTemplateParameters.forEach(item => {
        if (currentTemplateCheckedIds.has(parseInt(item.id))) {
            individualSum += parseFloat(item.standard_tariff) || 0;
        }
    });

    if (individualSum <= 0) {
        LIS.alert('Perhatian', 'Centang minimal satu parameter uji terlebih dahulu untuk menerapkan kalkulasi otomatis tarif paket.', 'info');
        return;
    }

    const multiplier = 1 - (pct / 100);
    const targetTotal = Math.round(individualSum * multiplier);

    // Distribusi proporsional standar SIMRS RS:
    // Sarana/Manajemen 30%, Dokter 25%, Petugas ATLM 20%, KSO 15%, BHP 10%
    const man = Math.round(targetTotal * 0.30);
    const dok = Math.round(targetTotal * 0.25);
    const pet = Math.round(targetTotal * 0.20);
    const kso = Math.round(targetTotal * 0.15);
    const bhp = targetTotal - (man + dok + pet + kso);

    document.getElementById('tpl-jasa-manajemen').value = man;
    document.getElementById('tpl-jasa-dokter').value = dok;
    document.getElementById('tpl-jasa-petugas').value = pet;
    document.getElementById('tpl-kso').value = kso;
    document.getElementById('tpl-bhp').value = bhp;

    calcTplTotal();
    LIS.toast('success', `Tarif paket SIMRS berhasil diset ${pct > 0 ? 'dengan diskon ' + pct + '%' : '100%'} (${formatRupiah(targetTotal)})`);
}

function switchTemplateTariffGroup(newGroup) {
    const checkedArray = Array.from(currentTemplateCheckedIds);
    loadAllParametersForTemplate(newGroup, checkedArray);
}

function openModalTemplate(mode, id = null) {
    const modal = document.getElementById('modal-template');
    const form = document.getElementById('form-template');
    form.reset();
    document.getElementById('tpl-id').value = '';
    document.getElementById('tpl-total-display').textContent = 'Rp 0';
    document.getElementById('tpl-service-type').value = 'all';
    document.getElementById('tpl-tat-duration').value = '120 Menit (2 Jam)';
    document.getElementById('tpl-is-active').checked = true;
    document.getElementById('modal-tpl-title').textContent = mode === 'create' ? 'Tambah Master Paket Laboratorium (SIMRS)' : 'Edit Master Paket Laboratorium (SIMRS)';

    activeParamCategoryFilter = 'all';
    activeParamSearchQuery = '';
    filterTplParamsCategory('all');

    if (mode === 'edit' && id) {
        const group = $('#filter-template-tariff-group').val() || 'umum';
        fetch('<?= site_url('tarif/get_template') ?>/' + id + '?tariff_group=' + group)
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    const d = res.data;
                    document.getElementById('tpl-id').value = d.id;
                    document.getElementById('tpl-category-id').value = d.category_id;
                    document.getElementById('tpl-code').value = d.template_code;
                    document.getElementById('tpl-name').value = d.template_name;
                    document.getElementById('tpl-desc').value = d.description || '';
                    document.getElementById('tpl-service-type').value = d.service_type || 'all';
                    document.getElementById('tpl-tat-duration').value = d.tat_duration || '120 Menit (2 Jam)';
                    document.getElementById('tpl-patient-prep').value = d.patient_preparation || '';
                    document.getElementById('tpl-sort-order').value = d.sort_order || 0;
                    document.getElementById('tpl-is-active').checked = parseInt(d.is_active) === 1;

                    document.getElementById('tpl-tariff-group').value = group;
                    document.getElementById('tpl-jasa-manajemen').value = d.jasa_manajemen || 0;
                    document.getElementById('tpl-jasa-dokter').value = d.jasa_dokter || 0;
                    document.getElementById('tpl-jasa-petugas').value = d.jasa_petugas || 0;
                    document.getElementById('tpl-kso').value = d.kso || 0;
                    document.getElementById('tpl-bhp').value = d.bhp || 0;
                    document.getElementById('tpl-notes').value = d.notes || '';
                    calcTplTotal();

                    const checkedIds = (d.items || []).map(i => parseInt(i.parameter_id));
                    loadAllParametersForTemplate(group, checkedIds);
                }
            });
    } else {
        const group = $('#filter-template-tariff-group').val() || 'umum';
        document.getElementById('tpl-tariff-group').value = group;
        const catSelect = document.getElementById('tpl-category-id');
        if (catSelect && catSelect.options.length > 1 && !catSelect.value) {
            catSelect.selectedIndex = 1;
        }
        loadAllParametersForTemplate(group, []);
        fetchNextTemplateCode();
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
    if (window.lucide) lucide.createIcons();
}

function closeModalTemplate() {
    const modal = document.getElementById('modal-template');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
}

function saveTemplate(e) {
    e.preventDefault();

    if (currentTemplateCheckedIds.size === 0) {
        LIS.alert('Validasi Paket', 'Pilih minimal satu parameter uji lab yang termasuk ke dalam paket ini.', 'warning');
        return;
    }

    const form = document.getElementById('form-template');
    const fd = new FormData(form);

    // Pastikan seluruh parameter terpilih dari Set ikut terkirim
    fd.delete('parameter_ids[]');
    currentTemplateCheckedIds.forEach(pId => {
        fd.append('parameter_ids[]', pId);
    });

    fetch('<?= site_url('tarif/save_template') ?>', {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            LIS.toast('success', res.message);
            closeModalTemplate();
            reloadTemplateTable();
            loadLabSummary();
        } else {
            LIS.alert('Gagal Menyimpan', res.message, 'error');
        }
    })
    .catch(() => {
        LIS.alert('Error', 'Terjadi kesalahan sistem saat menyimpan template paket.', 'error');
    });
}

function deleteTemplate(id, name) {
    LIS.confirm('Hapus Template', 'Yakin menghapus template paket <strong>' + name + '</strong>?', 'Ya, Hapus')
        .then(result => {
            if (result.isConfirmed) {
                fetch('<?= site_url('tarif/delete_template') ?>/' + id, {
                    method: 'DELETE',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        LIS.toast('success', res.message);
                        reloadTemplateTable();
                        loadLabSummary();
                    } else {
                        LIS.alert('Gagal', res.message, 'error');
                    }
                });
            }
        });
}

// ==========================================
// 6. DATATABLES METODE PEMERIKSAAN
// ==========================================
function initTableMethods() {
    tableMethod = $('#table-methods').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= site_url('tarif/fetch_methods') ?>',
            type: 'GET'
        },
        columns: [
            { data: 'no_urut', orderable: false, searchable: false, className: 'text-center font-medium' },
            { data: 'method_code', className: 'font-mono font-bold text-slate-800' },
            { data: 'method_name', className: 'font-bold text-slate-900' },
            { 
                data: 'standard_code',
                render: function(data) {
                    return data ? '<span class="lis-badge lis-badge-info font-mono">' + data + '</span>' : '<span class="text-slate-400">-</span>';
                }
            },
            { data: 'instrument_name', className: 'text-slate-700' },
            { data: 'description', className: 'text-slate-500' },
            { 
                data: 'is_active',
                className: 'text-center',
                render: function(data) {
                    return data == 1 ? '<span class="lis-badge lis-badge-success">Aktif</span>' : '<span class="lis-badge lis-badge-danger">Nonaktif</span>';
                }
            },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    let html = '<div class="flex items-center justify-center gap-1">';
                    <?php if ($can_edit): ?>
                    html += '<button type="button" onclick="openModalMetode(\'edit\', ' + data + ')" class="p-1 rounded bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors" title="Edit"><i data-lucide="edit-3" class="w-3.5 h-3.5"></i></button>';
                    <?php endif; ?>
                    <?php if ($can_delete): ?>
                    html += '<button type="button" onclick="deleteMethod(' + data + ', \'' + row.method_name.replace(/'/g, "\\'") + '\')" class="p-1 rounded bg-rose-50 text-rose-700 hover:bg-rose-100 transition-colors" title="Hapus"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>';
                    <?php endif; ?>
                    html += '</div>';
                    return html;
                }
            }
        ],
        order: [[1, 'asc']],
        pageLength: 15,
        lengthMenu: [10, 15, 25, 50],
        language: {
            processing: "Memuat metode...",
            search: "Cari Metode:",
            lengthMenu: "Tampil _MENU_",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ metode",
            infoEmpty: "0 metode",
            emptyTable: "Tidak ada data metode pemeriksaan.",
            paginate: { first: "«", previous: "‹", next: "›", last: "»" }
        },
        drawCallback: function() {
            lucide.createIcons();
        }
    });
}

function reloadMethodTable() {
    if (tableMethod) tableMethod.ajax.reload();
}

function openModalMetode(mode, id = null) {
    const modal = document.getElementById('modal-metode');
    const form = document.getElementById('form-metode');
    form.reset();
    document.getElementById('metode-id').value = '';
    document.getElementById('modal-metode-title').textContent = mode === 'create' ? 'Tambah Metode Pemeriksaan' : 'Edit Metode Pemeriksaan';

    if (mode === 'edit' && id) {
        fetch('<?= site_url('tarif/get_method') ?>/' + id)
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    const d = res.data;
                    document.getElementById('metode-id').value = d.id;
                    document.getElementById('metode-code').value = d.method_code;
                    document.getElementById('metode-name').value = d.method_name;
                    document.getElementById('metode-standard').value = d.standard_code || '';
                    document.getElementById('metode-instrument').value = d.instrument_name || '';
                    document.getElementById('metode-desc').value = d.description || '';
                }
            });
    } else {
        fetchNextMethodCode();
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
    lucide.createIcons();
}

function fetchNextMethodCode() {
    fetch('<?= site_url('tarif/get_next_method_code') ?>')
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                if (res.code) {
                    document.getElementById('metode-code').value = res.code;
                }
                const stdInput = document.getElementById('metode-standard');
                if (stdInput && !stdInput.value && res.standard_code) {
                    stdInput.value = res.standard_code;
                }
            }
        })
        .catch(err => console.error(err));
}

function closeModalMetode() {
    const modal = document.getElementById('modal-metode');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
}

function saveMetode(e) {
    e.preventDefault();
    const form = document.getElementById('form-metode');
    const fd = new FormData(form);

    fetch('<?= site_url('tarif/save_method') ?>', {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            LIS.toast('success', res.message);
            closeModalMetode();
            reloadMethodTable();
            loadLabSummary();
        } else {
            LIS.alert('Gagal', res.message, 'error');
        }
    });
}

function deleteMethod(id, name) {
    LIS.confirm('Hapus Metode', 'Yakin menghapus metode <strong>' + name + '</strong>?', 'Ya, Hapus')
        .then(result => {
            if (result.isConfirmed) {
                fetch('<?= site_url('tarif/delete_method') ?>/' + id, {
                    method: 'DELETE',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        LIS.toast('success', res.message);
                        reloadMethodTable();
                        loadLabSummary();
                    } else {
                        LIS.alert('Gagal', res.message, 'error');
                    }
                });
            }
        });
}
</script>
