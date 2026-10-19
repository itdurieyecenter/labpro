<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-5" x-data="rekeningApp()">

    <!-- 1. BREADCRUMB & PAGE HEADER (Melayu Riau Theme) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-slate-400">Keuangan & Akuntansi</span>
                <span>/</span>
                <span class="text-emerald-800 font-semibold">Akun Rekening</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="wallet" class="w-6 h-6"></i>
                </span>
                <span>Master Akun Rekening & Bagan Akun (COA)</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Pengelolaan Bagan Akun Standar (COA), Rekening Fiskal Tahunan, Akun Bayar Kasir, dan Akun Piutang Penjamin &bull; Standar BLUD Labkesda</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" @click="refreshCurrentTab()" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold flex items-center gap-2 transition-colors border border-slate-200" title="Muat Ulang Data">
                <i data-lucide="refresh-cw" class="w-4 h-4" :class="{ 'animate-spin': loading }"></i>
                <span class="hidden sm:inline">Refresh</span>
            </button>

            <a href="<?= site_url('dashboard') ?>" class="px-3.5 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold flex items-center gap-2 transition-colors border border-slate-200 shadow-2xs">
                <i data-lucide="arrow-left" class="w-4 h-4 text-slate-500"></i>
                <span class="hidden sm:inline">Dashboard</span>
            </a>

            <?php if ($can_create): ?>
                <button type="button" @click="openActiveCreateModal()"
                        class="px-4 py-2.5 rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-2 border bg-emerald-700 text-amber-200 border-emerald-600 shadow-emerald-900/10"
                        :class="{
                            'bg-emerald-700 text-amber-200 hover:bg-emerald-800 border-emerald-600 shadow-emerald-900/10': activeTab === 'coa' || activeTab === 'bayar',
                            'bg-amber-500 hover:bg-amber-600 text-slate-950 border-amber-400 shadow-amber-900/10': activeTab === 'tahun',
                            'bg-sky-700 hover:bg-sky-800 text-white border-sky-600': activeTab === 'piutang'
                        }">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span x-text="activeButtonText()">+ Tambah Akun COA</span>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- 2. SUMMARY STATISTIK KEUANGAN & REKENING (Melayu Riau Theme) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Card 1: Total COA -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-center flex-shrink-0">
                <i data-lucide="file-spreadsheet" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Bagan Akun (COA)</span>
                <span class="text-xl sm:text-2xl font-black text-slate-900 leading-none" x-text="summary.total_coa ?? '<?= $summary['total_coa'] ?>'">-</span>
                <span class="text-[10px] text-emerald-700 font-semibold block mt-0.5"><span x-text="summary.total_coa_aktif ?? '<?= $summary['total_coa_aktif'] ?>'">-</span> Akun Aktif</span>
            </div>
        </div>

        <!-- Card 2: Saldo Kas & Bank -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 flex items-center justify-center flex-shrink-0">
                <i data-lucide="landmark" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Kas & Bank Berjalan</span>
                <span class="text-lg sm:text-xl font-black text-emerald-900 leading-none" x-text="formatRupiah(summary.total_saldo_kas ?? <?= $summary['total_saldo_kas'] ?>)">-</span>
                <span class="text-[10px] text-slate-400 block mt-0.5"><span x-text="summary.kas_bank_aktif ?? '<?= $summary['kas_bank_aktif'] ?>'">-</span> Rekening Kasir & Bank</span>
            </div>
        </div>

        <!-- Card 3: Channel Akun Bayar Kasir -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-teal-50 border border-teal-200 text-teal-800 flex items-center justify-center flex-shrink-0">
                <i data-lucide="banknote" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Metode Pembayaran</span>
                <span class="text-xl sm:text-2xl font-black text-slate-900 leading-none" x-text="summary.total_akun_bayar ?? '<?= $summary['total_akun_bayar'] ?>'">-</span>
                <span class="text-[10px] text-teal-700 font-semibold block mt-0.5">Tunai, QRIS, EDC, Transfer</span>
            </div>
        </div>

        <!-- Card 4: Mitra Akun Piutang -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-sky-50 border border-sky-200 text-sky-800 flex items-center justify-center flex-shrink-0">
                <i data-lucide="hand-coins" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Mitra Piutang / PKS</span>
                <span class="text-xl sm:text-2xl font-black text-slate-900 leading-none" x-text="summary.total_akun_piutang ?? '<?= $summary['total_akun_piutang'] ?>'">-</span>
                <span class="text-[10px] text-sky-700 font-semibold block mt-0.5">BPJS, PT / Korporasi, PKM</span>
            </div>
        </div>
    </div>

    <!-- 3. TAB NAVIGASI UTAMA (4 TABS LENGKAP) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="flex items-center border-b border-slate-200 px-4 pt-3 overflow-x-auto gap-2 bg-slate-50/60 scrollbar-none">
            <!-- TAB 1: COA -->
            <button type="button" @click="setTab('coa')"
                    class="flex items-center gap-2.5 px-4 py-3 border-b-2 text-xs font-bold transition-all whitespace-nowrap focus:outline-none"
                    :class="activeTab === 'coa' ? 'border-emerald-700 text-emerald-800 bg-white rounded-t-xl shadow-xs' : 'border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-100/60 rounded-t-lg'">
                <i data-lucide="file-spreadsheet" class="w-4 h-4" :class="activeTab === 'coa' ? 'text-emerald-700' : 'text-slate-400'"></i>
                <span>1. Bagan Akun (COA)</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold"
                      :class="activeTab === 'coa' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200/80 text-slate-600'"
                      x-text="coas.length"><?= count($coas) ?></span>
            </button>

            <!-- TAB 2: REKENING TAHUN -->
            <button type="button" @click="setTab('tahun')"
                    class="flex items-center gap-2.5 px-4 py-3 border-b-2 text-xs font-bold transition-all whitespace-nowrap focus:outline-none"
                    :class="activeTab === 'tahun' ? 'border-amber-500 text-amber-900 bg-white rounded-t-xl shadow-xs' : 'border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-100/60 rounded-t-lg'">
                <i data-lucide="calendar-clock" class="w-4 h-4" :class="activeTab === 'tahun' ? 'text-amber-600' : 'text-slate-400'"></i>
                <span>2. Rekening Tahun & Anggaran</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold"
                      :class="activeTab === 'tahun' ? 'bg-amber-100 text-amber-800' : 'bg-slate-200/80 text-slate-600'"
                      x-text="rekeningTahun.length"><?= count($rekening_tahun) ?></span>
            </button>

            <!-- TAB 3: AKUN BAYAR -->
            <button type="button" @click="setTab('bayar')"
                    class="flex items-center gap-2.5 px-4 py-3 border-b-2 text-xs font-bold transition-all whitespace-nowrap focus:outline-none"
                    :class="activeTab === 'bayar' ? 'border-emerald-700 text-emerald-800 bg-white rounded-t-xl shadow-xs' : 'border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-100/60 rounded-t-lg'">
                <i data-lucide="banknote" class="w-4 h-4" :class="activeTab === 'bayar' ? 'text-emerald-700' : 'text-slate-400'"></i>
                <span>3. Akun Bayar (Kasir)</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold"
                      :class="activeTab === 'bayar' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200/80 text-slate-600'"
                      x-text="akunBayar.length"><?= count($akun_bayar) ?></span>
            </button>

            <!-- TAB 4: AKUN PIUTANG -->
            <button type="button" @click="setTab('piutang')"
                    class="flex items-center gap-2.5 px-4 py-3 border-b-2 text-xs font-bold transition-all whitespace-nowrap focus:outline-none"
                    :class="activeTab === 'piutang' ? 'border-sky-600 text-sky-800 bg-white rounded-t-xl shadow-xs' : 'border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-100/60 rounded-t-lg'">
                <i data-lucide="hand-coins" class="w-4 h-4" :class="activeTab === 'piutang' ? 'text-sky-600' : 'text-slate-400'"></i>
                <span>4. Akun Piutang (Penjamin)</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold"
                      :class="activeTab === 'piutang' ? 'bg-sky-100 text-sky-800' : 'bg-slate-200/80 text-slate-600'"
                      x-text="akunPiutang.length"><?= count($akun_piutang) ?></span>
            </button>
        </div>

        <div class="p-5">

            <!-- =============================================================== -->
            <!-- TAB CONTENT 1: BAGAN AKUN (COA)                                 -->
            <!-- =============================================================== -->
            <div x-show="activeTab === 'coa'" x-cloak class="space-y-4">
                <!-- Filter Bar COA -->
                <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <div class="flex items-center gap-2 flex-wrap flex-1">
                        <!-- Search Input with Autocomplete Suggestions -->
                        <div class="relative flex-1 min-w-[200px]" @click.outside="coaSearchSuggestionsOpen = false">
                            <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" x-model="filters.coa.search"
                                   @input="coaSearchSuggestionsOpen = true; filterCoa()"
                                   @focus="coaSearchSuggestionsOpen = true"
                                   @keydown.escape="coaSearchSuggestionsOpen = false"
                                   placeholder="Cari kode akun atau nama rekening..."
                                   class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                            <button type="button" x-show="filters.coa.search" @click="filters.coa.search = ''; filterCoa(); coaSearchSuggestionsOpen = false"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-0.5" title="Hapus pencarian">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>

                            <!-- Autocomplete Suggestions Popup -->
                            <div x-show="coaSearchSuggestionsOpen && coaSearchSuggestions().length > 0" x-cloak
                                 class="absolute z-30 left-0 right-0 mt-1 bg-white rounded-xl shadow-2xl border border-emerald-200 overflow-hidden divide-y divide-slate-100 max-h-64 overflow-y-auto">
                                <div class="px-3 py-1.5 bg-slate-50 text-[10px] font-bold text-slate-500 flex items-center justify-between">
                                    <span>Saran Rekening COA</span>
                                    <span class="text-emerald-700" x-text="coaSearchSuggestions().length + ' saran'"></span>
                                </div>
                                <template x-for="item in coaSearchSuggestions()" :key="item.id">
                                    <button type="button" @click="selectCoaSuggestion(item)"
                                            class="w-full text-left px-3 py-2 text-xs hover:bg-emerald-50 flex items-center justify-between group transition-colors">
                                        <div class="flex items-center gap-2 truncate">
                                            <span class="font-mono font-bold text-emerald-800 bg-emerald-100 px-1.5 py-0.5 rounded text-[11px]" x-text="item.kode_rekening"></span>
                                            <span class="font-semibold text-slate-800 truncate" x-text="item.nama_rekening"></span>
                                        </div>
                                        <div class="flex items-center gap-1.5 flex-shrink-0 ml-2">
                                            <span class="text-[9px] uppercase px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-semibold" x-text="item.kategori"></span>
                                            <span class="text-[9px] font-mono text-slate-400" x-text="'L' + item.level"></span>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Filter Kategori Dinamis -->
                        <select x-model="filters.coa.kategori" @change="filterCoa()"
                                class="px-3 py-2 text-xs rounded-lg border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium">
                            <option value="">Semua Kategori</option>
                            <template x-for="kat in kategoriList" :key="kat.id">
                                <option :value="kat.slug" x-text="kat.kode_kategori + '. ' + kat.nama_kategori"></option>
                            </template>
                        </select>

                        <!-- Filter Level -->
                        <select x-model="filters.coa.level" @change="filterCoa()"
                                class="px-3 py-2 text-xs rounded-lg border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium">
                            <option value="">Semua Level</option>
                            <option value="1">Level 1 - Golongan Header</option>
                            <option value="2">Level 2 - Bidang / Kelompok</option>
                            <option value="3">Level 3 - Jenis Rekening</option>
                            <option value="4">Level 4 - Objek Rincian</option>
                        </select>

                        <!-- Filter Status -->
                        <select x-model="filters.coa.is_active" @change="filterCoa()"
                                class="px-3 py-2 text-xs rounded-lg border border-slate-300 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium">
                            <option value="">Semua Status</option>
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>

                        <button type="button" @click="resetCoaFilters()" class="px-3 py-2 text-xs text-slate-600 hover:text-slate-900 bg-white border border-slate-300 rounded-lg transition-colors">
                            Reset
                        </button>

                        <button type="button" @click="openKategoriModal()"
                                class="px-3 py-2 text-xs font-bold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 rounded-lg flex items-center gap-1.5 transition-colors shadow-xs">
                            <i data-lucide="layers" class="w-4 h-4 text-emerald-700"></i>
                            <span>Kategori PSAK (<span x-text="kategoriList.length"></span>)</span>
                        </button>
                    </div>

                    <div class="text-xs text-slate-500 font-semibold whitespace-nowrap self-center">
                        Menampilkan <span class="text-emerald-800 font-bold" x-text="filteredCoas.length">0</span> akun
                    </div>
                </div>

                <!-- Table COA -->
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-100 text-slate-700 border-b border-slate-200 uppercase font-black tracking-wider text-[11px]">
                                <th class="py-3 px-4 w-40">Kode Rekening</th>
                                <th class="py-3 px-4">Nama Akun Rekening</th>
                                <th class="py-3 px-3 w-28 text-center">Kategori</th>
                                <th class="py-3 px-3 w-20 text-center">Posisi</th>
                                <th class="py-3 px-3 w-24 text-center">Level</th>
                                <th class="py-3 px-4 w-44">Atribut Khusus</th>
                                <th class="py-3 px-3 w-24 text-center">Status</th>
                                <th class="py-3 px-4 w-28 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="item in filteredCoas" :key="item.id">
                                <tr class="hover:bg-emerald-50/40 transition-colors"
                                    :class="{
                                        'bg-slate-50 font-bold text-slate-900': item.level == '1',
                                        'bg-white text-slate-800': item.level != '1'
                                    }">
                                    <!-- Kode Rekening -->
                                    <td class="py-3 px-4 font-mono font-bold whitespace-nowrap">
                                        <span :class="item.level == '1' ? 'text-emerald-900 text-sm' : 'text-slate-800'" x-text="item.kode_rekening"></span>
                                    </td>

                                    <!-- Nama Rekening with Level Indentation -->
                                    <td class="py-3 px-4">
                                        <div class="flex items-center" :style="'padding-left: ' + ((item.level - 1) * 16) + 'px'">
                                            <template x-if="item.level == '1'">
                                                <span class="w-2 h-2 rounded-full bg-emerald-600 mr-2 flex-shrink-0"></span>
                                            </template>
                                            <template x-if="item.level == '2'">
                                                <i data-lucide="corner-down-right" class="w-3.5 h-3.5 text-slate-400 mr-1.5 flex-shrink-0"></i>
                                            </template>
                                            <template x-if="item.level >= '3'">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-300 mr-2 flex-shrink-0"></span>
                                            </template>

                                            <div class="min-w-0">
                                                <span class="block"
                                                      :class="{
                                                          'font-black text-slate-900 tracking-wide': item.level == '1',
                                                          'font-bold text-slate-800': item.level == '2',
                                                          'font-medium text-slate-700': item.level >= '3'
                                                      }"
                                                      x-text="item.nama_rekening"></span>
                                                <template x-if="item.keterangan">
                                                    <span class="text-[10px] text-slate-400 block mt-0.5 line-clamp-1" x-text="item.keterangan"></span>
                                                </template>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Kategori -->
                                    <td class="py-3 px-3 text-center whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded-md font-bold uppercase text-[10px]"
                                              :class="{
                                                  'bg-emerald-100 text-emerald-800 border border-emerald-200': item.kategori === 'aset',
                                                  'bg-amber-100 text-amber-800 border border-amber-200': item.kategori === 'kewajiban',
                                                  'bg-purple-100 text-purple-800 border border-purple-200': item.kategori === 'ekuitas',
                                                  'bg-sky-100 text-sky-800 border border-sky-200': item.kategori === 'pendapatan',
                                                  'bg-rose-100 text-rose-800 border border-rose-200': item.kategori === 'beban'
                                              }"
                                              x-text="item.kategori"></span>
                                    </td>

                                    <!-- Posisi Normal -->
                                    <td class="py-3 px-3 text-center whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded-md font-black text-[10px]"
                                              :class="item.posisi_normal === 'D' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'"
                                              x-text="item.posisi_normal === 'D' ? 'Debit [D]' : 'Kredit [K]'"></span>
                                    </td>

                                    <!-- Level -->
                                    <td class="py-3 px-3 text-center whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200"
                                              x-text="'Lv ' + item.level"></span>
                                    </td>

                                    <!-- Atribut Khusus -->
                                    <td class="py-3 px-4 whitespace-nowrap">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <template x-if="item.is_kas_bank == '1'">
                                                <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 border border-emerald-300 font-bold text-[10px] flex items-center gap-1">
                                                    <i data-lucide="landmark" class="w-3 h-3"></i> Kas/Bank
                                                </span>
                                            </template>
                                            <template x-if="item.is_piutang == '1'">
                                                <span class="px-2 py-0.5 rounded-md bg-sky-100 text-sky-800 border border-sky-300 font-bold text-[10px] flex items-center gap-1">
                                                    <i data-lucide="hand-coins" class="w-3 h-3"></i> Piutang
                                                </span>
                                            </template>
                                            <template x-if="item.is_pendapatan == '1'">
                                                <span class="px-2 py-0.5 rounded-md bg-teal-100 text-teal-800 border border-teal-300 font-bold text-[10px] flex items-center gap-1">
                                                    <i data-lucide="receipt" class="w-3 h-3"></i> Tarif Lab
                                                </span>
                                            </template>
                                            <template x-if="item.is_kas_bank != '1' && item.is_piutang != '1' && item.is_pendapatan != '1'">
                                                <span class="text-[10px] text-slate-400">-</span>
                                            </template>
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="py-3 px-3 text-center whitespace-nowrap">
                                        <button type="button" @click="toggleCoa(item)"
                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold transition-transform active:scale-95 cursor-pointer"
                                                :class="item.is_active == '1' ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-200 text-slate-600 hover:bg-slate-300'">
                                            <span x-text="item.is_active == '1' ? '● Aktif' : '○ Nonaktif'"></span>
                                        </button>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <?php if ($can_create): ?>
                                                <button type="button" @click="openCreateSubModal(item)"
                                                        class="px-2 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 font-bold text-[11px] flex items-center gap-1 transition-colors"
                                                        title="Tambah Sub-Akun di bawah rekening ini">
                                                    <i data-lucide="plus" class="w-3 h-3"></i>
                                                    <span>Sub</span>
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($can_edit): ?>
                                                <button type="button" @click="openModalCoa('edit', item)"
                                                        class="p-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 transition-colors" title="Edit Akun COA">
                                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($can_delete): ?>
                                                <button type="button" @click="deleteCoa(item)"
                                                        class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-colors" title="Hapus Akun">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- =============================================================== -->
            <!-- TAB CONTENT 2: REKENING TAHUN (ANGGARAN & SALDO AWAL)            -->
            <!-- =============================================================== -->
            <div x-show="activeTab === 'tahun'" x-cloak class="space-y-4">
                <!-- Filter Bar Tahun -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <div class="flex items-center gap-2 flex-wrap flex-1">
                        <!-- Search Input with Autocomplete Suggestions -->
                        <div class="relative flex-1 min-w-[200px]" @click.outside="tahunSearchSuggestionsOpen = false">
                            <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" x-model="filters.tahun.search"
                                   @input="tahunSearchSuggestionsOpen = true; filterTahun()"
                                   @focus="tahunSearchSuggestionsOpen = true"
                                   @keydown.escape="tahunSearchSuggestionsOpen = false"
                                   placeholder="Cari rekening COA tahun..."
                                   class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-500 bg-white">
                            <button type="button" x-show="filters.tahun.search" @click="filters.tahun.search = ''; filterTahun(); tahunSearchSuggestionsOpen = false"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-0.5" title="Hapus pencarian">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>

                            <!-- Autocomplete Suggestions Popup -->
                            <div x-show="tahunSearchSuggestionsOpen && tahunSearchSuggestions().length > 0" x-cloak
                                 class="absolute z-30 left-0 right-0 mt-1 bg-white rounded-xl shadow-2xl border border-amber-200 overflow-hidden divide-y divide-slate-100 max-h-64 overflow-y-auto">
                                <div class="px-3 py-1.5 bg-slate-50 text-[10px] font-bold text-slate-500 flex items-center justify-between">
                                    <span>Saran Rekening Anggaran</span>
                                    <span class="text-amber-800" x-text="tahunSearchSuggestions().length + ' saran'"></span>
                                </div>
                                <template x-for="item in tahunSearchSuggestions()" :key="item.id">
                                    <button type="button" @click="selectTahunSuggestion(item)"
                                            class="w-full text-left px-3 py-2 text-xs hover:bg-amber-50 flex items-center justify-between group transition-colors">
                                        <div class="flex items-center gap-2 truncate">
                                            <span class="font-mono font-bold text-amber-900 bg-amber-100 px-1.5 py-0.5 rounded text-[11px]" x-text="item.kode_rekening"></span>
                                            <span class="font-semibold text-slate-800 truncate" x-text="item.nama_rekening"></span>
                                        </div>
                                        <span class="text-[10px] font-mono text-emerald-700 font-bold ml-2" x-text="formatRupiah(item.saldo_awal)"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5 ml-1">
                            <i data-lucide="calendar" class="w-4 h-4 text-amber-600"></i> Tahun Fiskal:
                        </span>
                        <select x-model="filters.tahun.tahun" @change="fetchTahunList()"
                                class="px-3 py-2 text-xs rounded-lg border border-slate-300 bg-white font-bold text-amber-900 focus:outline-none focus:ring-2 focus:ring-amber-500">
                            <template x-for="yr in tahunList" :key="yr">
                                <option :value="yr" x-text="yr + (yr == currentYear ? ' (Tahun Berjalan)' : '')"></option>
                            </template>
                        </select>

                        <!-- Filter Status -->
                        <select x-model="filters.tahun.status" @change="fetchTahunList()"
                                class="px-3 py-2 text-xs rounded-lg border border-slate-300 bg-white font-medium focus:outline-none focus:ring-2 focus:ring-amber-500">
                            <option value="">Semua Status Buku</option>
                            <option value="aktif">Aktif / Berjalan</option>
                            <option value="tutup_buku">Tutup Buku</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="fetchTahunList()" class="px-3 py-2 text-xs font-semibold bg-white border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-100 flex items-center gap-1.5">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i> Muat Ulang
                        </button>
                    </div>
                </div>

                <!-- Table Rekening Tahun -->
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-100 text-slate-700 border-b border-slate-200 uppercase font-black tracking-wider text-[11px]">
                                <th class="py-3 px-3 w-20 text-center">Tahun</th>
                                <th class="py-3 px-4 w-36">Kode COA</th>
                                <th class="py-3 px-4">Nama Rekening COA</th>
                                <th class="py-3 px-3 text-right">Saldo Awal</th>
                                <th class="py-3 px-3 text-right">Target Anggaran</th>
                                <th class="py-3 px-3 text-right">Debet</th>
                                <th class="py-3 px-3 text-right">Kredit</th>
                                <th class="py-3 px-4 text-right">Saldo Akhir</th>
                                <th class="py-3 px-3 w-28 text-center">Status</th>
                                <th class="py-3 px-4 w-28 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <template x-for="item in filteredTahun" :key="item.id">
                                <tr class="hover:bg-amber-50/30 transition-colors">
                                    <td class="py-3 px-3 text-center font-bold text-amber-900" x-text="item.tahun"></td>
                                    <td class="py-3 px-4 font-mono font-bold text-slate-800" x-text="item.kode_rekening"></td>
                                    <td class="py-3 px-4">
                                        <span class="font-bold text-slate-900 block" x-text="item.nama_rekening ?? '-'"></span>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <span class="text-[9px] uppercase px-1.5 py-0.2 rounded font-bold"
                                                  :class="item.posisi_normal === 'D' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'"
                                                  x-text="item.kategori + ' (' + item.posisi_normal + ')'"></span>
                                            <template x-if="item.keterangan">
                                                <span class="text-[10px] text-slate-400 truncate" x-text="'&bull; ' + item.keterangan"></span>
                                            </template>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 text-right font-mono" x-text="formatRupiah(item.saldo_awal)"></td>
                                    <td class="py-3 px-3 text-right font-mono text-slate-600" x-text="formatRupiah(item.target_anggaran)"></td>
                                    <td class="py-3 px-3 text-right font-mono text-emerald-700" x-text="formatRupiah(item.realisasi_debet)"></td>
                                    <td class="py-3 px-3 text-right font-mono text-amber-700" x-text="formatRupiah(item.realisasi_kredit)"></td>
                                    <td class="py-3 px-4 text-right font-mono font-black text-slate-900 bg-slate-50/50" x-text="formatRupiah(item.saldo_akhir)"></td>
                                    <td class="py-3 px-3 text-center whitespace-nowrap">
                                        <button type="button" @click="toggleStatusTahun(item)"
                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold cursor-pointer transition-transform active:scale-95"
                                                :class="item.status === 'aktif' ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-200 text-slate-700 hover:bg-slate-300'">
                                            <span x-text="item.status === 'aktif' ? '● Aktif' : '🔒 Tutup Buku'"></span>
                                        </button>
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <?php if ($can_edit): ?>
                                                <button type="button" @click="openModalTahun('edit', item)"
                                                        class="p-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 transition-colors" title="Edit Anggaran & Saldo">
                                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($can_delete): ?>
                                                <button type="button" @click="deleteTahun(item)"
                                                        class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-colors" title="Hapus Data">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <template x-if="rekeningTahun.length === 0">
                                <tr>
                                    <td colspan="10" class="py-8 text-center text-slate-400">
                                        <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                                        <p class="font-medium">Belum ada data anggaran atau saldo awal untuk tahun fiskal ini.</p>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- =============================================================== -->
            <!-- TAB CONTENT 3: AKUN BAYAR (KASIR & CHANNEL PENERIMAAN)          -->
            <!-- =============================================================== -->
            <div x-show="activeTab === 'bayar'" x-cloak class="space-y-4">
                <!-- Filter Bar Bayar -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <div class="flex items-center gap-2 flex-wrap flex-1">
                        <!-- Search Input with Autocomplete Suggestions -->
                        <div class="relative flex-1 min-w-[200px]" @click.outside="bayarSearchSuggestionsOpen = false">
                            <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" x-model="filters.bayar.search"
                                   @input="bayarSearchSuggestionsOpen = true; filterBayar()"
                                   @focus="bayarSearchSuggestionsOpen = true"
                                   @keydown.escape="bayarSearchSuggestionsOpen = false"
                                   placeholder="Cari kode atau nama channel pembayaran..."
                                   class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white">
                            <button type="button" x-show="filters.bayar.search" @click="filters.bayar.search = ''; filterBayar(); bayarSearchSuggestionsOpen = false"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-0.5" title="Hapus pencarian">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>

                            <!-- Autocomplete Suggestions Popup -->
                            <div x-show="bayarSearchSuggestionsOpen && bayarSearchSuggestions().length > 0" x-cloak
                                 class="absolute z-30 left-0 right-0 mt-1 bg-white rounded-xl shadow-2xl border border-emerald-200 overflow-hidden divide-y divide-slate-100 max-h-64 overflow-y-auto">
                                <div class="px-3 py-1.5 bg-slate-50 text-[10px] font-bold text-slate-500 flex items-center justify-between">
                                    <span>Saran Channel Pembayaran</span>
                                    <span class="text-emerald-700" x-text="bayarSearchSuggestions().length + ' saran'"></span>
                                </div>
                                <template x-for="item in bayarSearchSuggestions()" :key="item.id">
                                    <button type="button" @click="selectBayarSuggestion(item)"
                                            class="w-full text-left px-3 py-2 text-xs hover:bg-emerald-50 flex items-center justify-between group transition-colors">
                                        <div class="flex items-center gap-2 truncate">
                                            <span class="font-mono font-bold text-emerald-800 bg-emerald-100 px-1.5 py-0.5 rounded text-[11px]" x-text="item.kode_bayar"></span>
                                            <span class="font-semibold text-slate-800 truncate" x-text="item.nama_bayar"></span>
                                        </div>
                                        <span class="text-[9px] uppercase px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-semibold" x-text="item.tipe_transaksi"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <select x-model="filters.bayar.tipe" @change="filterBayar()"
                                class="px-3 py-2 text-xs rounded-lg border border-slate-300 bg-white font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">Semua Tipe Channel</option>
                            <option value="cash">💵 Tunai / Kasir Cash</option>
                            <option value="qris">📱 QRIS Statis/Dinamis</option>
                            <option value="debit">💳 Kartu Debit EDC</option>
                            <option value="transfer">🏦 Transfer Bank Rekening</option>
                        </select>

                        <select x-model="filters.bayar.is_active" @change="filterBayar()"
                                class="px-3 py-2 text-xs rounded-lg border border-slate-300 bg-white font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">Semua Status</option>
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>

                    <div class="text-xs text-slate-500 font-semibold self-center whitespace-nowrap">
                        Total <span class="text-emerald-800 font-bold" x-text="filteredBayar.length">0</span> channel pembayaran
                    </div>
                </div>

                <!-- Table Akun Bayar -->
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-100 text-slate-700 border-b border-slate-200 uppercase font-black tracking-wider text-[11px]">
                                <th class="py-3 px-4 w-32">Kode Bayar</th>
                                <th class="py-3 px-4">Nama Channel Pembayaran</th>
                                <th class="py-3 px-3 w-28 text-center">Tipe</th>
                                <th class="py-3 px-4">Akun COA Terhubung</th>
                                <th class="py-3 px-4">Informasi Bank / Rekening</th>
                                <th class="py-3 px-3 w-28 text-right">Biaya Admin</th>
                                <th class="py-3 px-3 w-24 text-center">Status</th>
                                <th class="py-3 px-4 w-28 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <template x-for="item in filteredBayar" :key="item.id">
                                <tr class="hover:bg-emerald-50/30 transition-colors">
                                    <td class="py-3 px-4 font-mono font-black text-emerald-900" x-text="item.kode_bayar"></td>
                                    <td class="py-3 px-4">
                                        <span class="font-bold text-slate-900 text-sm block" x-text="item.nama_bayar"></span>
                                        <span class="text-[10px] text-slate-400 block mt-0.5" x-text="'Urutan Tampil: #' + item.urutan"></span>
                                    </td>
                                    <td class="py-3 px-3 text-center whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-lg font-bold text-[10px] uppercase flex items-center justify-center gap-1 mx-auto"
                                              :class="{
                                                  'bg-emerald-100 text-emerald-800': item.tipe_transaksi === 'cash',
                                                  'bg-amber-100 text-amber-800': item.tipe_transaksi === 'qris',
                                                  'bg-sky-100 text-sky-800': item.tipe_transaksi === 'debit',
                                                  'bg-purple-100 text-purple-800': item.tipe_transaksi === 'transfer'
                                              }">
                                            <span x-text="item.tipe_transaksi"></span>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="font-mono font-bold text-slate-800 text-xs block" x-text="item.kode_rekening"></span>
                                        <span class="text-[11px] text-slate-600 font-medium block truncate max-w-xs" x-text="item.coa_nama ?? '-'"></span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="font-semibold text-slate-900 block" x-text="item.nama_bank || '-'"></span>
                                        <span class="font-mono text-slate-600 block text-[11px]" x-text="item.nomor_rekening_bank || '-'"></span>
                                        <template x-if="item.atas_nama">
                                            <span class="text-[10px] text-slate-400 block" x-text="'a.n ' + item.atas_nama"></span>
                                        </template>
                                    </td>
                                    <td class="py-3 px-3 text-right font-mono font-semibold" x-text="formatRupiah(item.biaya_admin)"></td>
                                    <td class="py-3 px-3 text-center whitespace-nowrap">
                                        <button type="button" @click="toggleBayar(item)"
                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold cursor-pointer transition-transform active:scale-95"
                                                :class="item.is_active == '1' ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-200 text-slate-600 hover:bg-slate-300'">
                                            <span x-text="item.is_active == '1' ? '● Aktif' : '○ Nonaktif'"></span>
                                        </button>
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <?php if ($can_edit): ?>
                                                <button type="button" @click="openModalBayar('edit', item)"
                                                        class="p-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 transition-colors" title="Edit Akun Bayar">
                                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($can_delete): ?>
                                                <button type="button" @click="deleteBayar(item)"
                                                        class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-colors" title="Hapus Channel">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- =============================================================== -->
            <!-- TAB CONTENT 4: AKUN PIUTANG (PENJAMIN & PKS KERJASAMA)          -->
            <!-- =============================================================== -->
            <div x-show="activeTab === 'piutang'" x-cloak class="space-y-4">
                <!-- Filter Bar Piutang -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <div class="flex items-center gap-2 flex-wrap flex-1">
                        <!-- Search Input with Autocomplete Suggestions -->
                        <div class="relative flex-1 min-w-[200px]" @click.outside="piutangSearchSuggestionsOpen = false">
                            <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" x-model="filters.piutang.search"
                                   @input="piutangSearchSuggestionsOpen = true; filterPiutang()"
                                   @focus="piutangSearchSuggestionsOpen = true"
                                   @keydown.escape="piutangSearchSuggestionsOpen = false"
                                   placeholder="Cari kode, nama instansi penjamin, atau PIC..."
                                   class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-sky-500 bg-white">
                            <button type="button" x-show="filters.piutang.search" @click="filters.piutang.search = ''; filterPiutang(); piutangSearchSuggestionsOpen = false"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-0.5" title="Hapus pencarian">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>

                            <!-- Autocomplete Suggestions Popup -->
                            <div x-show="piutangSearchSuggestionsOpen && piutangSearchSuggestions().length > 0" x-cloak
                                 class="absolute z-30 left-0 right-0 mt-1 bg-white rounded-xl shadow-2xl border border-sky-200 overflow-hidden divide-y divide-slate-100 max-h-64 overflow-y-auto">
                                <div class="px-3 py-1.5 bg-slate-50 text-[10px] font-bold text-slate-500 flex items-center justify-between">
                                    <span>Saran Mitra Penjamin / Piutang</span>
                                    <span class="text-sky-700" x-text="piutangSearchSuggestions().length + ' saran'"></span>
                                </div>
                                <template x-for="item in piutangSearchSuggestions()" :key="item.id">
                                    <button type="button" @click="selectPiutangSuggestion(item)"
                                            class="w-full text-left px-3 py-2 text-xs hover:bg-sky-50 flex items-center justify-between group transition-colors">
                                        <div class="flex items-center gap-2 truncate">
                                            <span class="font-mono font-bold text-sky-800 bg-sky-100 px-1.5 py-0.5 rounded text-[11px]" x-text="item.kode_piutang"></span>
                                            <span class="font-semibold text-slate-800 truncate" x-text="item.nama_piutang"></span>
                                        </div>
                                        <span class="text-[9px] uppercase px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-semibold" x-text="item.kategori_penjamin"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <select x-model="filters.piutang.penjamin" @change="filterPiutang()"
                                class="px-3 py-2 text-xs rounded-lg border border-slate-300 bg-white font-medium focus:outline-none focus:ring-2 focus:ring-sky-500">
                            <option value="">Semua Kategori Penjamin</option>
                            <option value="bpjs">BPJS Kesehatan / KIS</option>
                            <option value="pks_perusahaan">PKS Perusahaan / Korporasi</option>
                            <option value="faskes_perujuk">Faskes / Puskesmas Rujukan</option>
                            <option value="asuransi_lain">Asuransi Swasta / Komersial</option>
                        </select>

                        <select x-model="filters.piutang.is_active" @change="filterPiutang()"
                                class="px-3 py-2 text-xs rounded-lg border border-slate-300 bg-white font-medium focus:outline-none focus:ring-2 focus:ring-sky-500">
                            <option value="">Semua Status</option>
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>

                    <div class="text-xs text-slate-500 font-semibold self-center whitespace-nowrap">
                        Total <span class="text-sky-800 font-bold" x-text="filteredPiutang.length">0</span> mitra penjamin
                    </div>
                </div>

                <!-- Table Akun Piutang -->
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-100 text-slate-700 border-b border-slate-200 uppercase font-black tracking-wider text-[11px]">
                                <th class="py-3 px-4 w-32">Kode Piutang</th>
                                <th class="py-3 px-4">Nama Mitra / Instansi Penjamin</th>
                                <th class="py-3 px-3 w-36 text-center">Kategori</th>
                                <th class="py-3 px-4">Akun COA Terhubung</th>
                                <th class="py-3 px-3 w-28 text-center">Jatuh Tempo</th>
                                <th class="py-3 px-4">Kontak PIC & Informasi</th>
                                <th class="py-3 px-3 w-24 text-center">Status</th>
                                <th class="py-3 px-4 w-28 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <template x-for="item in filteredPiutang" :key="item.id">
                                <tr class="hover:bg-sky-50/30 transition-colors">
                                    <td class="py-3 px-4 font-mono font-black text-sky-900" x-text="item.kode_piutang"></td>
                                    <td class="py-3 px-4">
                                        <span class="font-bold text-slate-900 text-sm block" x-text="item.nama_piutang"></span>
                                        <template x-if="item.alamat">
                                            <span class="text-[10px] text-slate-400 block mt-0.5 line-clamp-1" x-text="item.alamat"></span>
                                        </template>
                                    </td>
                                    <td class="py-3 px-3 text-center whitespace-nowrap">
                                        <span class="px-2.5 py-1 rounded-lg font-bold text-[10px] uppercase block"
                                              :class="{
                                                  'bg-emerald-100 text-emerald-800 border border-emerald-200': item.kategori_penjamin === 'bpjs',
                                                  'bg-blue-100 text-blue-800 border border-blue-200': item.kategori_penjamin === 'pks_perusahaan',
                                                  'bg-amber-100 text-amber-800 border border-amber-200': item.kategori_penjamin === 'faskes_perujuk',
                                                  'bg-purple-100 text-purple-800 border border-purple-200': item.kategori_penjamin === 'asuransi_lain'
                                              }">
                                            <span x-text="item.kategori_penjamin.replace('_', ' ')"></span>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="font-mono font-bold text-slate-800 text-xs block" x-text="item.kode_rekening"></span>
                                        <span class="text-[11px] text-slate-600 font-medium block truncate max-w-xs" x-text="item.coa_nama ?? '-'"></span>
                                    </td>
                                    <td class="py-3 px-3 text-center whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded-full font-bold text-[10px] bg-slate-100 text-slate-700 border border-slate-200"
                                              x-text="item.jatuh_tempo_hari + ' Hari TOP'"></span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="font-semibold text-slate-900 block" x-text="item.kontak_person || '-'"></span>
                                        <div class="flex items-center gap-2 text-[11px] text-slate-500 mt-0.5">
                                            <template x-if="item.no_telepon">
                                                <span class="flex items-center gap-1 font-mono"><i data-lucide="phone" class="w-3 h-3 text-slate-400"></i><span x-text="item.no_telepon"></span></span>
                                            </template>
                                            <template x-if="item.email">
                                                <span class="flex items-center gap-1 truncate"><i data-lucide="mail" class="w-3 h-3 text-slate-400"></i><span x-text="item.email"></span></span>
                                            </template>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 text-center whitespace-nowrap">
                                        <button type="button" @click="togglePiutang(item)"
                                                class="px-2 py-0.5 rounded-full text-[10px] font-bold cursor-pointer transition-transform active:scale-95"
                                                :class="item.is_active == '1' ? 'bg-sky-100 text-sky-800 hover:bg-sky-200' : 'bg-slate-200 text-slate-600 hover:bg-slate-300'">
                                            <span x-text="item.is_active == '1' ? '● Aktif' : '○ Nonaktif'"></span>
                                        </button>
                                    </td>
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <?php if ($can_edit): ?>
                                                <button type="button" @click="openModalPiutang('edit', item)"
                                                        class="p-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 transition-colors" title="Edit Akun Piutang">
                                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($can_delete): ?>
                                                <button type="button" @click="deletePiutang(item)"
                                                        class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 transition-colors" title="Hapus Mitra">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- =================================================================== -->
    <!-- MODAL 1: FORM TAMBAH / EDIT AKUN COA                                -->
    <!-- =================================================================== -->
    <div x-show="modalCoa.show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
        <div class="bg-white rounded-2xl max-w-xl w-full border border-slate-200 shadow-2xl overflow-hidden transition-all transform max-h-[90vh] flex flex-col my-auto"
             @click.away="modalCoa.show = false">
            <!-- Modal Header -->
            <div class="px-5 py-4 bg-gradient-to-r from-emerald-800 to-emerald-700 text-white flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-white/10 text-amber-300 border border-white/10">
                        <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold" x-text="modalCoa.mode === 'create' ? 'Tambah Akun COA Baru' : 'Edit Akun COA Rekening'"></h3>
                        <p class="text-[11px] text-emerald-100">Bagan Akun Standar Keuangan BLUD Labkesda</p>
                    </div>
                </div>
                <button type="button" @click="modalCoa.show = false" class="text-emerald-100 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Modal Form Body -->
            <form @submit.prevent="submitCoa()" class="flex flex-col flex-1 min-h-0">
                <div class="p-5 space-y-4 overflow-y-auto flex-1">
                <input type="hidden" x-model="modalCoa.form.id">

                <!-- 1. Rekening Induk / Parent Selector -->
                <div class="p-3.5 rounded-xl bg-emerald-50/70 border border-emerald-200 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-emerald-950 flex items-center gap-1.5">
                            <i data-lucide="git-fork" class="w-4 h-4 text-emerald-700"></i>
                            <span>Rekening Utama / Induk (Parent)</span>
                        </label>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md"
                              :class="modalCoa.form.parent_kode ? 'bg-emerald-200 text-emerald-900 border border-emerald-300' : 'bg-slate-200 text-slate-700'"
                              x-text="modalCoa.form.parent_kode ? 'Sub-Akun Level ' + modalCoa.form.level : 'Akun Utama (Header Level 1)'">
                        </span>
                    </div>
                    <!-- Custom Searchable Autocomplete Combobox for Parent Account -->
                    <div class="relative" @click.outside="parentDropdownOpen = false">
                        <!-- Trigger Box -->
                        <button type="button" @click="parentDropdownOpen = !parentDropdownOpen; if(parentDropdownOpen) $nextTick(() => $refs.parentSearchInput.focus())"
                                class="w-full flex items-center justify-between px-3 py-2 text-xs rounded-xl border border-emerald-300 bg-white hover:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 text-left transition-all shadow-xs">
                            <div class="flex items-center gap-2 truncate flex-1 min-w-0">
                                <template x-if="!modalCoa.form.parent_kode">
                                    <span class="text-slate-500 font-medium italic">-- Tanpa Induk (Sebagai Akun Utama / Header Level 1) --</span>
                                </template>
                                <template x-if="modalCoa.form.parent_kode">
                                    <div class="flex items-center gap-2 truncate">
                                        <span class="font-mono font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded text-[11px]" x-text="modalCoa.form.parent_kode"></span>
                                        <span class="font-bold text-slate-800 truncate" x-text="getParentName(modalCoa.form.parent_kode)"></span>
                                        <span class="text-[10px] text-slate-400 font-medium" x-text="'(Level ' + getParentLevel(modalCoa.form.parent_kode) + ')'"></span>
                                    </div>
                                </template>
                            </div>
                            <div class="flex items-center gap-1.5 flex-shrink-0 ml-2">
                                <template x-if="modalCoa.form.parent_kode">
                                    <span @click.stop="clearParentAccount()" class="p-1 rounded-md text-slate-400 hover:text-rose-600 hover:bg-rose-50 cursor-pointer" title="Hapus Induk / Jadikan Akun Utama">
                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                    </span>
                                </template>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-emerald-700 transition-transform duration-200" :class="parentDropdownOpen ? 'rotate-180' : ''"></i>
                            </div>
                        </button>

                        <!-- Autocomplete Dropdown Popover -->
                        <div x-show="parentDropdownOpen" x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-1"
                             class="absolute z-50 left-0 right-0 mt-1.5 bg-white rounded-xl shadow-2xl border border-emerald-200 overflow-hidden flex flex-col max-h-80">
                            
                            <!-- Search Input inside Dropdown -->
                            <div class="p-2.5 bg-slate-50 border-b border-slate-200">
                                <div class="relative">
                                    <i data-lucide="search" class="w-4 h-4 text-emerald-700 absolute left-3 top-1/2 -translate-y-1/2"></i>
                                    <input type="text" x-ref="parentSearchInput" x-model="parentSearch"
                                           placeholder="🔍 Cari kode atau nama rekening induk..."
                                           class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white font-medium"
                                           @keydown.escape="parentDropdownOpen = false">
                                    <button type="button" x-show="parentSearch" @click="parentSearch = ''; $refs.parentSearchInput.focus()"
                                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-0.5">
                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                                <div class="flex items-center justify-between text-[10px] text-slate-500 mt-1 px-1">
                                    <span>Tersedia: <b class="text-emerald-800" x-text="filteredParentAccounts().length + ' akun'"></b></span>
                                    <span class="text-slate-400">Tekan Esc untuk tutup</span>
                                </div>
                            </div>

                            <!-- Scrollable Items List -->
                            <div class="overflow-y-auto divide-y divide-slate-100 flex-1 p-1">
                                <!-- Root Option -->
                                <button type="button" @click="selectParentAccount(null)"
                                        class="w-full text-left px-3 py-2 rounded-lg text-xs font-semibold hover:bg-emerald-50 transition-colors flex items-center justify-between"
                                        :class="!modalCoa.form.parent_kode ? 'bg-emerald-100 text-emerald-900 font-bold' : 'text-slate-700'">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="folder-root" class="w-4 h-4 text-emerald-600"></i>
                                        <span>-- Tanpa Induk (Sebagai Akun Utama / Header Level 1) --</span>
                                    </div>
                                    <template x-if="!modalCoa.form.parent_kode">
                                        <i data-lucide="check" class="w-4 h-4 text-emerald-700"></i>
                                    </template>
                                </button>

                                <!-- Filtered Accounts -->
                                <template x-for="p in filteredParentAccounts()" :key="p.id">
                                    <button type="button" @click="selectParentAccount(p)"
                                            :disabled="modalCoa.mode === 'edit' && p.kode_rekening === modalCoa.form.kode_rekening"
                                            class="w-full text-left px-3 py-2 rounded-lg text-xs hover:bg-emerald-50 transition-colors flex items-center justify-between group disabled:opacity-40 disabled:cursor-not-allowed"
                                            :class="modalCoa.form.parent_kode === p.kode_rekening ? 'bg-emerald-100/80 font-bold text-emerald-950' : 'text-slate-800'">
                                        <div class="flex items-center gap-2 truncate min-w-0" :style="'padding-left: ' + ((p.level - 1) * 12) + 'px'">
                                            <span class="text-slate-300 group-hover:text-emerald-500 font-mono text-[10px]" x-show="p.level > 1">└─</span>
                                            <span class="font-mono font-bold text-[11px] px-1.5 py-0.5 rounded bg-slate-100 group-hover:bg-emerald-200 group-hover:text-emerald-900"
                                                  :class="modalCoa.form.parent_kode === p.kode_rekening ? 'bg-emerald-200 text-emerald-900 font-black' : 'text-slate-800'"
                                                  x-text="p.kode_rekening"></span>
                                            <span class="truncate" x-text="p.nama_rekening"></span>
                                            <span class="text-[9px] uppercase px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-semibold flex-shrink-0" x-text="p.kategori"></span>
                                        </div>
                                        <div class="flex items-center gap-1.5 flex-shrink-0 ml-2">
                                            <span class="text-[10px] px-1.5 py-0.5 rounded font-mono font-semibold"
                                                  :class="p.level == 1 ? 'bg-purple-100 text-purple-800 font-bold' : (p.level == 2 ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-600')"
                                                  x-text="'L' + p.level"></span>
                                            <template x-if="modalCoa.form.parent_kode === p.kode_rekening">
                                                <i data-lucide="check" class="w-4 h-4 text-emerald-700"></i>
                                            </template>
                                        </div>
                                    </button>
                                </template>

                                <!-- Empty State -->
                                <div x-show="filteredParentAccounts().length === 0" class="p-4 text-center text-xs text-slate-400">
                                    <i data-lucide="search-x" class="w-6 h-6 mx-auto mb-1 text-slate-300"></i>
                                    Tidak ada rekening yang cocok dengan kata kunci "<span class="font-bold text-slate-600" x-text="parentSearch"></span>"
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="text-[10px] text-emerald-800" x-show="modalCoa.form.parent_kode">
                        * Kategori, level, dan posisi normal otomatis diwariskan dari induk. Nomor rekening baru disarankan otomatis.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <!-- Kode Rekening -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700">Kode Rekening <span class="text-rose-500">*</span></label>
                            <template x-if="modalCoa.form.parent_kode">
                                <button type="button" @click="fetchSuggestedCode(modalCoa.form.parent_kode)" class="text-[10px] text-emerald-700 hover:underline font-bold flex items-center gap-0.5">
                                    <i data-lucide="sparkles" class="w-3 h-3"></i>
                                    <span>Auto-Suggest</span>
                                </button>
                            </template>
                        </div>
                        <input type="text" x-model="modalCoa.form.kode_rekening" required placeholder="Contoh: 1.1.02.01"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono font-bold">
                    </div>

                    <!-- Kategori Akun -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700">Kategori Akun <span class="text-rose-500">*</span></label>
                            <button type="button" @click="openQuickAddKategori()" class="text-[11px] text-emerald-700 hover:text-emerald-900 font-bold flex items-center gap-0.5">
                                <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                                <span>+ Kategori Baru</span>
                            </button>
                        </div>
                        <select x-model="modalCoa.form.kategori" :disabled="!!modalCoa.form.parent_kode" required
                                @change="onCategoryChange()"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium"
                                :class="modalCoa.form.parent_kode ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : 'bg-white'">
                            <option value="">- Pilih Kategori Akun -</option>
                            <template x-for="kat in kategoriList" :key="kat.id">
                                <option :value="kat.slug" x-text="kat.kode_kategori + '. ' + kat.nama_kategori + ' (' + (kat.posisi_normal === 'D' ? 'Debit' : 'Kredit') + ')'"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <!-- Nama Rekening -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Akun Rekening <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="modalCoa.form.nama_rekening" required placeholder="Contoh: Kas di Rekening Operasional Bank Mandiri"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-semibold">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <!-- Posisi Normal -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Posisi Normal</label>
                        <select x-model="modalCoa.form.posisi_normal"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-bold">
                            <option value="D">Debit [D]</option>
                            <option value="K">Kredit [K]</option>
                        </select>
                    </div>

                    <!-- Level Rekening -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Level Rekening</label>
                        <select x-model="modalCoa.form.level" :disabled="!!modalCoa.form.parent_kode"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-medium"
                                :class="modalCoa.form.parent_kode ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : 'bg-white'">
                            <option value="1">Level 1 (Golongan Induk)</option>
                            <option value="2">Level 2 (Bidang / Kelompok)</option>
                            <option value="3">Level 3 (Jenis Akun)</option>
                            <option value="4">Level 4 (Objek Rincian)</option>
                        </select>
                    </div>
                </div>

                <!-- Checkboxes: Flag Atribut Khusus -->
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                    <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block mb-1">Indikator Modul Operasional:</span>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                        <label class="flex items-center gap-2 text-slate-700 font-medium cursor-pointer">
                            <input type="checkbox" x-model="modalCoa.form.is_kas_bank" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span>Akun Kas / Bank</span>
                        </label>
                        <label class="flex items-center gap-2 text-slate-700 font-medium cursor-pointer">
                            <input type="checkbox" x-model="modalCoa.form.is_piutang" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                            <span>Akun Piutang</span>
                        </label>
                        <label class="flex items-center gap-2 text-slate-700 font-medium cursor-pointer">
                            <input type="checkbox" x-model="modalCoa.form.is_pendapatan" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                            <span>Akun Tarif Layanan</span>
                        </label>
                    </div>
                </div>

                <!-- Keterangan & Status Aktif -->
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Catatan Tambahan</label>
                        <input type="text" x-model="modalCoa.form.keterangan" placeholder="Contoh: Rekening transaksi kas operasional harian"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                            <input type="checkbox" x-model="modalCoa.form.is_active" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span>Status Rekening Aktif</span>
                        </label>
                    </div>
                </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-5 py-3.5 border-t border-slate-200 bg-slate-50 flex items-center justify-end gap-2 flex-shrink-0">
                    <button type="button" @click="modalCoa.show = false" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 text-xs font-bold hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-emerald-700 text-amber-200 text-xs font-bold hover:bg-emerald-800 flex items-center gap-2 shadow-xs">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span x-text="modalCoa.mode === 'create' ? 'Simpan Akun COA' : 'Perbarui Akun COA'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- MODAL 1B: KELOLA MASTER KATEGORI AKUN (PSAK)                        -->
    <!-- =================================================================== -->
    <div x-show="modalKategori.show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
        <div class="bg-white rounded-2xl max-w-2xl w-full border border-slate-200 shadow-2xl overflow-hidden transition-all transform max-h-[90vh] flex flex-col my-auto"
             @click.away="modalKategori.show = false">
            <!-- Modal Header -->
            <div class="px-5 py-4 bg-gradient-to-r from-emerald-800 to-teal-800 text-white flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-white/10 text-amber-300 border border-white/10">
                        <i data-lucide="layers" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold">Kelola Master Kategori Akun (PSAK)</h3>
                        <p class="text-[11px] text-emerald-100">Klasifikasi Utama Bagan Akun Standar BLUD Labkesda</p>
                    </div>
                </div>
                <button type="button" @click="modalKategori.show = false" class="text-emerald-100 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Modal Body (Form & Table) -->
            <div class="p-5 overflow-y-auto flex-1 space-y-5">
                <!-- Form Tambah / Edit Kategori -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                            <i data-lucide="plus-circle" class="w-4 h-4 text-emerald-600"></i>
                            <span x-text="modalKategori.mode === 'create' ? 'Tambah Kategori Akun Baru' : 'Edit Kategori Akun'"></span>
                        </h4>
                        <template x-if="modalKategori.mode === 'edit'">
                            <button type="button" @click="cancelEditKategori()" class="text-[11px] text-rose-600 hover:underline font-bold">
                                Batal Edit
                            </button>
                        </template>
                    </div>

                    <form @submit.prevent="submitKategori()" class="space-y-3">
                        <input type="hidden" x-model="modalKategori.form.id">
                        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Kode Kategori <span class="text-rose-500">*</span></label>
                                <input type="text" x-model="modalKategori.form.kode_kategori" required placeholder="Contoh: 6"
                                       class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono font-bold">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Kategori <span class="text-rose-500">*</span></label>
                                <input type="text" x-model="modalKategori.form.nama_kategori" required placeholder="Contoh: PENDAPATAN LAIN-LAIN"
                                       class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-bold uppercase">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Posisi Normal</label>
                                <select x-model="modalKategori.form.posisi_normal"
                                        class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-bold">
                                    <option value="D">Debit [D]</option>
                                    <option value="K">Kredit [K]</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="sm:col-span-2">
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Deskripsi / Ruang Lingkup</label>
                                <input type="text" x-model="modalKategori.form.deskripsi" placeholder="Contoh: Pendapatan di luar operasional seperti jasa giro dan hibah"
                                       class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Urutan</label>
                                <div class="flex items-center gap-2">
                                    <input type="number" x-model="modalKategori.form.urutan" min="1"
                                           class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-bold">
                                    <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold flex items-center gap-1.5 whitespace-nowrap shadow-xs">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                        <span x-text="modalKategori.mode === 'create' ? 'Simpan' : 'Update'"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Tabel Daftar Kategori Akun -->
                <div>
                    <div class="flex items-center justify-between gap-3 mb-2 flex-wrap">
                        <h4 class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                            <span>Daftar Kategori Akun Aktif (<span x-text="filteredKategoriList().length"></span>)</span>
                            <span class="text-[10px] text-slate-500 font-normal hidden sm:inline">&bull; Sesuai Standar PSAK</span>
                        </h4>
                        <!-- Search input for kategori -->
                        <div class="relative w-52">
                            <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2"></i>
                            <input type="text" x-model="kategoriSearch" placeholder="Cari kategori akun..."
                                   class="w-full pl-8 pr-7 py-1 text-xs rounded-lg border border-slate-300 bg-white focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            <button type="button" x-show="kategoriSearch" @click="kategoriSearch = ''" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <i data-lucide="x" class="w-3 h-3"></i>
                            </button>
                        </div>
                    </div>
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px] border-b border-slate-200">
                                    <th class="py-2.5 px-3 w-16 text-center">Kode</th>
                                    <th class="py-2.5 px-3">Nama Kategori</th>
                                    <th class="py-2.5 px-3 w-20 text-center">Posisi</th>
                                    <th class="py-2.5 px-3 w-24 text-center">Total Akun</th>
                                    <th class="py-2.5 px-3 w-20 text-center">Status</th>
                                    <th class="py-2.5 px-3 w-24 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="kat in filteredKategoriList()" :key="kat.id">
                                    <tr class="hover:bg-slate-50">
                                        <td class="py-2 px-3 text-center font-mono font-bold text-emerald-900" x-text="kat.kode_kategori"></td>
                                        <td class="py-2 px-3">
                                            <div class="font-bold text-slate-900" x-text="kat.nama_kategori"></div>
                                            <div class="text-[10px] text-slate-500" x-text="kat.deskripsi || '-'"></div>
                                        </td>
                                        <td class="py-2 px-3 text-center">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                                  :class="kat.posisi_normal === 'D' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'"
                                                  x-text="kat.posisi_normal === 'D' ? 'Debit' : 'Kredit'">
                                            </span>
                                        </td>
                                        <td class="py-2 px-3 text-center">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700"
                                                  x-text="(kat.total_akun || 0) + ' Akun'">
                                            </span>
                                        </td>
                                        <td class="py-2 px-3 text-center">
                                            <button type="button" @click="toggleKategori(kat)"
                                                    class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                                    :class="kat.is_active == '1' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600'">
                                                <span x-text="kat.is_active == '1' ? 'Aktif' : 'Nonaktif'"></span>
                                            </button>
                                        </td>
                                        <td class="py-2 px-3 text-right whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-1">
                                                <button type="button" @click="editKategori(kat)"
                                                        class="p-1 rounded bg-amber-50 text-amber-700 hover:bg-amber-100" title="Edit Kategori">
                                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                                </button>
                                                <template x-if="!kat.is_system && (!kat.total_akun || kat.total_akun == 0)">
                                                    <button type="button" @click="deleteKategori(kat)"
                                                            class="p-1 rounded bg-rose-50 text-rose-700 hover:bg-rose-100" title="Hapus Kategori">
                                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    </button>
                                                </template>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-5 py-3 border-t border-slate-200 bg-slate-50 flex items-center justify-end flex-shrink-0">
                <button type="button" @click="modalKategori.show = false" class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- MODAL 2: FORM ATUR REKENING TAHUN & SALDO AWAL                      -->
    <!-- =================================================================== -->
    <div x-show="modalTahun.show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
        <div class="bg-white rounded-2xl max-w-lg w-full border border-slate-200 shadow-2xl overflow-hidden transition-all transform max-h-[90vh] flex flex-col my-auto"
             @click.away="modalTahun.show = false">
            <div class="px-5 py-4 bg-gradient-to-r from-amber-600 to-amber-700 text-white flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-white/10 text-amber-200 border border-white/10">
                        <i data-lucide="calendar-clock" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold" x-text="modalTahun.mode === 'create' ? 'Atur Saldo & Anggaran Tahun' : 'Edit Rekening Tahun'"></h3>
                        <p class="text-[11px] text-amber-100">Saldo Awal, Target Anggaran & Realisasi</p>
                    </div>
                </div>
                <button type="button" @click="modalTahun.show = false" class="text-amber-100 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form @submit.prevent="submitTahun()" class="flex flex-col flex-1 min-h-0">
                <div class="p-5 space-y-3.5 overflow-y-auto flex-1">
                <input type="hidden" x-model="modalTahun.form.id">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Fiskal <span class="text-rose-500">*</span></label>
                        <input type="number" x-model="modalTahun.form.tahun" required min="2020" max="2050"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-500 font-bold text-amber-900">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Buku</label>
                        <select x-model="modalTahun.form.status"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-500 font-semibold">
                            <option value="aktif">Aktif / Berjalan</option>
                            <option value="tutup_buku">Tutup Buku</option>
                        </select>
                    </div>
                </div>

                <!-- Searchable Autocomplete Combobox for Rekening COA -->
                <div class="relative" @click.outside="tahunCoaDropdownOpen = false">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Akun Rekening COA <span class="text-rose-500">*</span></label>
                    <input type="hidden" x-model="modalTahun.form.kode_rekening" required>

                    <!-- Trigger Box -->
                    <button type="button" @click="tahunCoaDropdownOpen = !tahunCoaDropdownOpen; if(tahunCoaDropdownOpen) $nextTick(() => $refs.tahunCoaSearchInput.focus())"
                            class="w-full flex items-center justify-between px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white hover:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500 text-left transition-all shadow-xs">
                        <div class="flex items-center gap-2 truncate flex-1 min-w-0">
                            <template x-if="!modalTahun.form.kode_rekening">
                                <span class="text-slate-400 font-medium">-- Pilih Akun Rekening COA --</span>
                            </template>
                            <template x-if="modalTahun.form.kode_rekening">
                                <div class="flex items-center gap-2 truncate">
                                    <span class="font-mono font-bold text-amber-900 bg-amber-100 px-2 py-0.5 rounded text-[11px]" x-text="modalTahun.form.kode_rekening"></span>
                                    <span class="font-bold text-slate-800 truncate" x-text="getParentName(modalTahun.form.kode_rekening)"></span>
                                </div>
                            </template>
                        </div>
                        <div class="flex items-center gap-1.5 flex-shrink-0 ml-2">
                            <template x-if="modalTahun.form.kode_rekening">
                                <span @click.stop="selectTahunAccount(null)" class="p-1 rounded-md text-slate-400 hover:text-rose-600 hover:bg-rose-50 cursor-pointer">
                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                </span>
                            </template>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-amber-700 transition-transform duration-200" :class="tahunCoaDropdownOpen ? 'rotate-180' : ''"></i>
                        </div>
                    </button>

                    <!-- Autocomplete Popover Dropdown -->
                    <div x-show="tahunCoaDropdownOpen" x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-1"
                         class="absolute z-50 left-0 right-0 mt-1.5 bg-white rounded-xl shadow-2xl border border-amber-300 overflow-hidden flex flex-col max-h-72">
                        
                        <div class="p-2.5 bg-slate-50 border-b border-slate-200">
                            <div class="relative">
                                <i data-lucide="search" class="w-4 h-4 text-amber-700 absolute left-3 top-1/2 -translate-y-1/2"></i>
                                <input type="text" x-ref="tahunCoaSearchInput" x-model="tahunCoaSearch"
                                       placeholder="🔍 Cari kode atau nama rekening COA..."
                                       class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border border-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-500 bg-white font-medium"
                                       @keydown.escape="tahunCoaDropdownOpen = false">
                                <button type="button" x-show="tahunCoaSearch" @click="tahunCoaSearch = ''; $refs.tahunCoaSearchInput.focus()"
                                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-0.5">
                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                            <div class="flex items-center justify-between text-[10px] text-slate-500 mt-1 px-1">
                                <span>Ditemukan: <b class="text-amber-900" x-text="filteredTahunAccounts().length + ' akun'"></b></span>
                                <span class="text-slate-400">Tekan Esc untuk tutup</span>
                            </div>
                        </div>

                        <div class="overflow-y-auto divide-y divide-slate-100 flex-1 p-1">
                            <template x-for="c in filteredTahunAccounts()" :key="c.id">
                                <button type="button" @click="selectTahunAccount(c)"
                                        class="w-full text-left px-3 py-2 rounded-lg text-xs hover:bg-amber-50 transition-colors flex items-center justify-between group"
                                        :class="modalTahun.form.kode_rekening === c.kode_rekening ? 'bg-amber-100 font-bold text-amber-950' : 'text-slate-800'">
                                    <div class="flex items-center gap-2 truncate min-w-0" :style="'padding-left: ' + ((c.level - 1) * 10) + 'px'">
                                        <span class="font-mono font-bold text-[11px] px-1.5 py-0.5 rounded bg-slate-100 group-hover:bg-amber-200"
                                              :class="modalTahun.form.kode_rekening === c.kode_rekening ? 'bg-amber-200 text-amber-900' : 'text-slate-800'"
                                              x-text="c.kode_rekening"></span>
                                        <span class="truncate" x-text="c.nama_rekening"></span>
                                        <span class="text-[9px] uppercase px-1.5 py-0.2 rounded font-semibold"
                                              :class="c.posisi_normal === 'D' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'"
                                              x-text="c.posisi_normal"></span>
                                    </div>
                                    <div class="flex items-center gap-1.5 flex-shrink-0 ml-2">
                                        <span class="text-[10px] text-slate-400 font-mono" x-text="'L' + c.level"></span>
                                        <template x-if="modalTahun.form.kode_rekening === c.kode_rekening">
                                            <i data-lucide="check" class="w-4 h-4 text-amber-700"></i>
                                        </template>
                                    </div>
                                </button>
                            </template>
                            <div x-show="filteredTahunAccounts().length === 0" class="p-4 text-center text-xs text-slate-400">
                                Tidak ada akun yang cocok.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Saldo Awal (Rp)</label>
                        <input type="number" step="0.01" x-model="modalTahun.form.saldo_awal" placeholder="0"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-500 font-mono font-bold text-emerald-800">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Target Anggaran (Rp)</label>
                        <input type="number" step="0.01" x-model="modalTahun.form.target_anggaran" placeholder="0"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-500 font-mono font-semibold">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Realisasi Debet (Rp)</label>
                        <input type="number" step="0.01" x-model="modalTahun.form.realisasi_debet" placeholder="0"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Realisasi Kredit (Rp)</label>
                        <input type="number" step="0.01" x-model="modalTahun.form.realisasi_kredit" placeholder="0"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-500 font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Catatan</label>
                    <input type="text" x-model="modalTahun.form.keterangan" placeholder="Contoh: Saldo awal hasil audit fiskal BLUD"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-500">
                </div>
                </div>

                <div class="px-5 py-3.5 border-t border-slate-200 bg-slate-50 flex items-center justify-end gap-2 flex-shrink-0">
                    <button type="button" @click="modalTahun.show = false" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 text-xs font-bold hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-amber-600 text-white text-xs font-bold hover:bg-amber-700 flex items-center gap-2 shadow-xs">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Rekening Tahun</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- MODAL 3: FORM TAMBAH / EDIT AKUN BAYAR KASIR                        -->
    <!-- =================================================================== -->
    <div x-show="modalBayar.show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
        <div class="bg-white rounded-2xl max-w-lg w-full border border-slate-200 shadow-2xl overflow-hidden transition-all transform max-h-[90vh] flex flex-col my-auto"
             @click.away="modalBayar.show = false">
            <div class="px-5 py-4 bg-gradient-to-r from-emerald-800 to-teal-800 text-white flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-white/10 text-amber-300 border border-white/10">
                        <i data-lucide="banknote" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold" x-text="modalBayar.mode === 'create' ? 'Tambah Akun Bayar Kasir' : 'Edit Channel Pembayaran'"></h3>
                        <p class="text-[11px] text-emerald-100">Penerimaan Kasir Walk-in & Gateway BLUD</p>
                    </div>
                </div>
                <button type="button" @click="modalBayar.show = false" class="text-emerald-100 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form @submit.prevent="submitBayar()" class="flex flex-col flex-1 min-h-0">
                <div class="p-5 space-y-3.5 overflow-y-auto flex-1">
                <input type="hidden" x-model="modalBayar.form.id">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kode Channel <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="modalBayar.form.kode_bayar" required placeholder="Contoh: QRIS-BRKS"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono font-bold uppercase">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Transaksi <span class="text-rose-500">*</span></label>
                        <select x-model="modalBayar.form.tipe_transaksi" required
                                class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-bold">
                            <option value="cash">💵 Tunai / Kasir Cash</option>
                            <option value="qris">📱 QRIS Statis/Dinamis</option>
                            <option value="debit">💳 Kartu Debit EDC</option>
                            <option value="transfer">🏦 Transfer Bank Rekening</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Channel Pembayaran <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="modalBayar.form.nama_bayar" required placeholder="Contoh: QRIS Bank Riau Kepri Syariah"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-semibold">
                </div>

                <!-- Searchable Autocomplete Combobox for Akun Bayar COA -->
                <div class="relative" @click.outside="bayarCoaDropdownOpen = false">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Hubungkan ke Akun COA <span class="text-rose-500">*</span></label>
                    <input type="hidden" x-model="modalBayar.form.kode_rekening" required>

                    <!-- Trigger Button -->
                    <button type="button" @click="bayarCoaDropdownOpen = !bayarCoaDropdownOpen; if(bayarCoaDropdownOpen) $nextTick(() => $refs.bayarCoaSearchInput.focus())"
                            class="w-full flex items-center justify-between px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white hover:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 text-left transition-all shadow-xs">
                        <div class="flex items-center gap-2 truncate flex-1 min-w-0">
                            <template x-if="!modalBayar.form.kode_rekening">
                                <span class="text-slate-400 font-medium">-- Pilih Akun Kas / Bank COA --</span>
                            </template>
                            <template x-if="modalBayar.form.kode_rekening">
                                <div class="flex items-center gap-2 truncate">
                                    <span class="font-mono font-bold text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded text-[11px]" x-text="modalBayar.form.kode_rekening"></span>
                                    <span class="font-bold text-slate-800 truncate" x-text="getParentName(modalBayar.form.kode_rekening)"></span>
                                    <template x-if="getBayarCoaObj(modalBayar.form.kode_rekening)?.is_kas_bank == '1'">
                                        <span class="text-[9px] bg-emerald-50 text-emerald-700 border border-emerald-200 px-1 rounded font-bold">Kas/Bank</span>
                                    </template>
                                </div>
                            </template>
                        </div>
                        <div class="flex items-center gap-1.5 flex-shrink-0 ml-2">
                            <template x-if="modalBayar.form.kode_rekening">
                                <span @click.stop="selectBayarAccount(null)" class="p-1 rounded-md text-slate-400 hover:text-rose-600 hover:bg-rose-50 cursor-pointer">
                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                </span>
                            </template>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-emerald-700 transition-transform duration-200" :class="bayarCoaDropdownOpen ? 'rotate-180' : ''"></i>
                        </div>
                    </button>

                    <!-- Dropdown Panel -->
                    <div x-show="bayarCoaDropdownOpen" x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-1"
                         class="absolute z-50 left-0 right-0 mt-1.5 bg-white rounded-xl shadow-2xl border border-emerald-300 overflow-hidden flex flex-col max-h-72">
                        
                        <div class="p-2.5 bg-slate-50 border-b border-slate-200">
                            <div class="relative">
                                <i data-lucide="search" class="w-4 h-4 text-emerald-700 absolute left-3 top-1/2 -translate-y-1/2"></i>
                                <input type="text" x-ref="bayarCoaSearchInput" x-model="bayarCoaSearch"
                                       placeholder="🔍 Cari kode atau nama akun kas/bank..."
                                       class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border border-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-white font-medium"
                                       @keydown.escape="bayarCoaDropdownOpen = false">
                                <button type="button" x-show="bayarCoaSearch" @click="bayarCoaSearch = ''; $refs.bayarCoaSearchInput.focus()"
                                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-0.5">
                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                            <div class="flex items-center justify-between text-[10px] text-slate-500 mt-1 px-1">
                                <span>Tersedia: <b class="text-emerald-800" x-text="filteredBayarAccounts().length + ' akun'"></b></span>
                                <span class="text-slate-400">Tekan Esc untuk tutup</span>
                            </div>
                        </div>

                        <div class="overflow-y-auto divide-y divide-slate-100 flex-1 p-1">
                            <template x-for="c in filteredBayarAccounts()" :key="c.id">
                                <button type="button" @click="selectBayarAccount(c)"
                                        class="w-full text-left px-3 py-2 rounded-lg text-xs hover:bg-emerald-50 transition-colors flex items-center justify-between group"
                                        :class="modalBayar.form.kode_rekening === c.kode_rekening ? 'bg-emerald-100 font-bold text-emerald-950' : 'text-slate-800'">
                                    <div class="flex items-center gap-2 truncate min-w-0" :style="'padding-left: ' + ((c.level - 1) * 8) + 'px'">
                                        <span class="font-mono font-bold text-[11px] px-1.5 py-0.5 rounded"
                                              :class="c.is_kas_bank == '1' ? 'bg-emerald-100 text-emerald-800 font-black' : 'bg-slate-100 text-slate-700'"
                                              x-text="c.kode_rekening"></span>
                                        <span class="truncate" x-text="c.nama_rekening"></span>
                                        <template x-if="c.is_kas_bank == '1'">
                                            <span class="text-[9px] bg-emerald-100 text-emerald-800 font-bold px-1.5 py-0.2 rounded border border-emerald-300">Kas/Bank</span>
                                        </template>
                                    </div>
                                    <div class="flex items-center gap-1.5 flex-shrink-0 ml-2">
                                        <span class="text-[9px] uppercase px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-medium" x-text="c.kategori"></span>
                                        <template x-if="modalBayar.form.kode_rekening === c.kode_rekening">
                                            <i data-lucide="check" class="w-4 h-4 text-emerald-700"></i>
                                        </template>
                                    </div>
                                </button>
                            </template>
                            <div x-show="filteredBayarAccounts().length === 0" class="p-4 text-center text-xs text-slate-400">
                                Tidak ada akun yang cocok.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Bank / Penerbit</label>
                        <input type="text" x-model="modalBayar.form.nama_bank" placeholder="Contoh: Bank Riau Kepri Syariah"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Rekening / NMID</label>
                        <input type="text" x-model="modalBayar.form.nomor_rekening_bank" placeholder="Contoh: 101-08-12345-6"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Atas Nama Rekening</label>
                        <input type="text" x-model="modalBayar.form.atas_nama" placeholder="Contoh: Bendahara BLUD Labkes Riau"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Biaya Admin (Rp)</label>
                        <input type="number" step="0.01" x-model="modalBayar.form.biaya_admin" placeholder="0"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                        <input type="checkbox" x-model="modalBayar.form.is_active" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span>Aktifkan Channel Pembayaran Ini</span>
                    </label>
                </div>
                </div>

                <div class="px-5 py-3.5 border-t border-slate-200 bg-slate-50 flex items-center justify-end gap-2 flex-shrink-0">
                    <button type="button" @click="modalBayar.show = false" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 text-xs font-bold hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-emerald-700 text-amber-200 text-xs font-bold hover:bg-emerald-800 flex items-center gap-2 shadow-xs">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Akun Bayar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- MODAL 4: FORM TAMBAH / EDIT AKUN PIUTANG PENJAMIN                   -->
    <!-- =================================================================== -->
    <div x-show="modalPiutang.show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
        <div class="bg-white rounded-2xl max-w-lg w-full border border-slate-200 shadow-2xl overflow-hidden transition-all transform max-h-[90vh] flex flex-col my-auto"
             @click.away="modalPiutang.show = false">
            <div class="px-5 py-4 bg-gradient-to-r from-sky-800 to-indigo-800 text-white flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-white/10 text-sky-200 border border-white/10">
                        <i data-lucide="hand-coins" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold" x-text="modalPiutang.mode === 'create' ? 'Tambah Akun Piutang Penjamin' : 'Edit Mitra Penjamin'"></h3>
                        <p class="text-[11px] text-sky-100">Klaim BPJS, Kerjasama Korporasi, & Rujukan Faskes</p>
                    </div>
                </div>
                <button type="button" @click="modalPiutang.show = false" class="text-sky-100 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form @submit.prevent="submitPiutang()" class="flex flex-col flex-1 min-h-0">
                <div class="p-5 space-y-3.5 overflow-y-auto flex-1">
                <input type="hidden" x-model="modalPiutang.form.id">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kode Piutang <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="modalPiutang.form.kode_piutang" required placeholder="Contoh: PIUT-PHR"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-sky-500 font-mono font-bold uppercase">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Penjamin <span class="text-rose-500">*</span></label>
                        <select x-model="modalPiutang.form.kategori_penjamin" required
                                class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-sky-500 font-bold">
                            <option value="bpjs">BPJS Kesehatan / JKN</option>
                            <option value="pks_perusahaan">PKS Perusahaan / Korporasi</option>
                            <option value="faskes_perujuk">Faskes / Puskesmas Rujukan</option>
                            <option value="asuransi_lain">Asuransi Swasta / Komersial</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Mitra / Instansi Penjamin <span class="text-rose-500">*</span></label>
                    <input type="text" x-model="modalPiutang.form.nama_piutang" required placeholder="Contoh: PT Pertamina Hulu Rokan (PHR)"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-sky-500 font-semibold">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Searchable Autocomplete Combobox for Akun Piutang COA -->
                    <div class="sm:col-span-2 relative" @click.outside="piutangCoaDropdownOpen = false">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Hubungkan ke Akun COA <span class="text-rose-500">*</span></label>
                        <input type="hidden" x-model="modalPiutang.form.kode_rekening" required>

                        <!-- Trigger Button -->
                        <button type="button" @click="piutangCoaDropdownOpen = !piutangCoaDropdownOpen; if(piutangCoaDropdownOpen) $nextTick(() => $refs.piutangCoaSearchInput.focus())"
                                class="w-full flex items-center justify-between px-3 py-2 text-xs rounded-xl border border-slate-300 bg-white hover:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500 text-left transition-all shadow-xs">
                            <div class="flex items-center gap-2 truncate flex-1 min-w-0">
                                <template x-if="!modalPiutang.form.kode_rekening">
                                    <span class="text-slate-400 font-medium">-- Pilih Akun Piutang COA --</span>
                                </template>
                                <template x-if="modalPiutang.form.kode_rekening">
                                    <div class="flex items-center gap-2 truncate">
                                        <span class="font-mono font-bold text-sky-800 bg-sky-100 px-2 py-0.5 rounded text-[11px]" x-text="modalPiutang.form.kode_rekening"></span>
                                        <span class="font-bold text-slate-800 truncate" x-text="getParentName(modalPiutang.form.kode_rekening)"></span>
                                        <template x-if="getPiutangCoaObj(modalPiutang.form.kode_rekening)?.is_piutang == '1'">
                                            <span class="text-[9px] bg-sky-50 text-sky-700 border border-sky-200 px-1 rounded font-bold">Piutang</span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                            <div class="flex items-center gap-1.5 flex-shrink-0 ml-2">
                                <template x-if="modalPiutang.form.kode_rekening">
                                    <span @click.stop="selectPiutangAccount(null)" class="p-1 rounded-md text-slate-400 hover:text-rose-600 hover:bg-rose-50 cursor-pointer">
                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                    </span>
                                </template>
                                <i data-lucide="chevron-down" class="w-4 h-4 text-sky-700 transition-transform duration-200" :class="piutangCoaDropdownOpen ? 'rotate-180' : ''"></i>
                            </div>
                        </button>

                        <!-- Dropdown Panel -->
                        <div x-show="piutangCoaDropdownOpen" x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-1"
                             class="absolute z-50 left-0 right-0 mt-1.5 bg-white rounded-xl shadow-2xl border border-sky-300 overflow-hidden flex flex-col max-h-72">
                            
                            <div class="p-2.5 bg-slate-50 border-b border-slate-200">
                                <div class="relative">
                                    <i data-lucide="search" class="w-4 h-4 text-sky-700 absolute left-3 top-1/2 -translate-y-1/2"></i>
                                    <input type="text" x-ref="piutangCoaSearchInput" x-model="piutangCoaSearch"
                                           placeholder="🔍 Cari kode atau nama akun piutang..."
                                           class="w-full pl-9 pr-8 py-2 text-xs rounded-lg border border-sky-300 focus:outline-none focus:ring-2 focus:ring-sky-500 bg-white font-medium"
                                           @keydown.escape="piutangCoaDropdownOpen = false">
                                    <button type="button" x-show="piutangCoaSearch" @click="piutangCoaSearch = ''; $refs.piutangCoaSearchInput.focus()"
                                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-0.5">
                                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                                <div class="flex items-center justify-between text-[10px] text-slate-500 mt-1 px-1">
                                    <span>Tersedia: <b class="text-sky-800" x-text="filteredPiutangAccounts().length + ' akun'"></b></span>
                                    <span class="text-slate-400">Tekan Esc untuk tutup</span>
                                </div>
                            </div>

                            <div class="overflow-y-auto divide-y divide-slate-100 flex-1 p-1">
                                <template x-for="c in filteredPiutangAccounts()" :key="c.id">
                                    <button type="button" @click="selectPiutangAccount(c)"
                                            class="w-full text-left px-3 py-2 rounded-lg text-xs hover:bg-sky-50 transition-colors flex items-center justify-between group"
                                            :class="modalPiutang.form.kode_rekening === c.kode_rekening ? 'bg-sky-100 font-bold text-sky-950' : 'text-slate-800'">
                                        <div class="flex items-center gap-2 truncate min-w-0" :style="'padding-left: ' + ((c.level - 1) * 8) + 'px'">
                                            <span class="font-mono font-bold text-[11px] px-1.5 py-0.5 rounded"
                                                  :class="c.is_piutang == '1' ? 'bg-sky-100 text-sky-800 font-black' : 'bg-slate-100 text-slate-700'"
                                                  x-text="c.kode_rekening"></span>
                                            <span class="truncate" x-text="c.nama_rekening"></span>
                                            <template x-if="c.is_piutang == '1'">
                                                <span class="text-[9px] bg-sky-100 text-sky-800 font-bold px-1.5 py-0.2 rounded border border-sky-300">Piutang</span>
                                            </template>
                                        </div>
                                        <div class="flex items-center gap-1.5 flex-shrink-0 ml-2">
                                            <span class="text-[9px] uppercase px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-medium" x-text="c.kategori"></span>
                                            <template x-if="modalPiutang.form.kode_rekening === c.kode_rekening">
                                                <i data-lucide="check" class="w-4 h-4 text-sky-700"></i>
                                            </template>
                                        </div>
                                    </button>
                                </template>
                                <div x-show="filteredPiutangAccounts().length === 0" class="p-4 text-center text-xs text-slate-400">
                                    Tidak ada akun yang cocok.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">TOP (Hari)</label>
                        <input type="number" x-model="modalPiutang.form.jatuh_tempo_hari" placeholder="30" min="1"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-sky-500 font-bold text-center">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kontak Person (PIC)</label>
                        <input type="text" x-model="modalPiutang.form.kontak_person" placeholder="Contoh: Ibu Sri Rahayu"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">No. Telepon / WhatsApp</label>
                        <input type="text" x-model="modalPiutang.form.no_telepon" placeholder="Contoh: 0812-7000-1111"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-sky-500 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email Tagihan / PIC</label>
                        <input type="email" x-model="modalPiutang.form.email" placeholder="Contoh: klaim@mitra.com"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Kantor Mitra</label>
                        <input type="text" x-model="modalPiutang.form.alamat" placeholder="Contoh: Jl. Sudirman No. 100, Pekanbaru"
                               class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                        <input type="checkbox" x-model="modalPiutang.form.is_active" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                        <span>Status Kerjasama Mitra Aktif</span>
                    </label>
                </div>
                </div>

                <div class="px-5 py-3.5 border-t border-slate-200 bg-slate-50 flex items-center justify-end gap-2 flex-shrink-0">
                    <button type="button" @click="modalPiutang.show = false" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 text-xs font-bold hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-sky-700 text-white text-xs font-bold hover:bg-sky-800 flex items-center gap-2 shadow-xs">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Akun Piutang</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- JAVASCRIPT LOGIC (Alpine.js & AJAX Handlers) -->
<script>
function rekeningApp() {
    return {
        activeTab: '<?= $active_tab ?>',
        loading: false,
        currentYear: <?= $curr_tahun ?>,

        // Summary Stats
        summary: <?= json_encode($summary) ?>,

        // Master Lists
        kategoriList: <?= json_encode($kategori_list) ?>,
        coas: <?= json_encode($coas) ?>,
        filteredCoas: <?= json_encode($coas) ?>,

        rekeningTahun: <?= json_encode($rekening_tahun) ?>,
        filteredTahun: <?= json_encode($rekening_tahun) ?>,
        tahunList: <?= json_encode($tahun_list) ?>,

        akunBayar: <?= json_encode($akun_bayar) ?>,
        filteredBayar: <?= json_encode($akun_bayar) ?>,

        akunPiutang: <?= json_encode($akun_piutang) ?>,
        filteredPiutang: <?= json_encode($akun_piutang) ?>,

        // Filters State
        filters: {
            coa: { search: '', kategori: '', level: '', is_active: '' },
            tahun: { tahun: <?= $curr_tahun ?>, status: '', search: '' },
            bayar: { search: '', tipe: '', is_active: '' },
            piutang: { search: '', penjamin: '', is_active: '' }
        },

        // Autocomplete & Searchable Combobox States
        parentSearch: '',
        parentDropdownOpen: false,

        tahunCoaSearch: '',
        tahunCoaDropdownOpen: false,

        bayarCoaSearch: '',
        bayarCoaDropdownOpen: false,

        piutangCoaSearch: '',
        piutangCoaDropdownOpen: false,

        coaSearchSuggestionsOpen: false,
        tahunSearchSuggestionsOpen: false,
        bayarSearchSuggestionsOpen: false,
        piutangSearchSuggestionsOpen: false,
        kategoriSearch: '',

        // Modals State
        modalKategori: {
            show: false,
            mode: 'create',
            form: {
                id: '',
                kode_kategori: '',
                nama_kategori: '',
                posisi_normal: 'D',
                urutan: 1,
                is_active: true,
                deskripsi: ''
            }
        },

        modalCoa: {
            show: false,
            mode: 'create',
            form: {
                id: '',
                kode_rekening: '',
                nama_rekening: '',
                kategori: 'aset',
                kategori_id: '',
                posisi_normal: 'D',
                level: 1,
                parent_kode: '',
                is_kas_bank: false,
                is_piutang: false,
                is_pendapatan: false,
                is_active: true,
                keterangan: ''
            }
        },

        modalTahun: {
            show: false,
            mode: 'create',
            form: {
                id: '',
                tahun: <?= $curr_tahun ?>,
                kode_rekening: '',
                saldo_awal: 0,
                target_anggaran: 0,
                realisasi_debet: 0,
                realisasi_kredit: 0,
                status: 'aktif',
                keterangan: ''
            }
        },

        modalBayar: {
            show: false,
            mode: 'create',
            form: {
                id: '',
                kode_bayar: '',
                nama_bayar: '',
                kode_rekening: '',
                tipe_transaksi: 'cash',
                nama_bank: '',
                nomor_rekening_bank: '',
                atas_nama: '',
                biaya_admin: 0,
                urutan: 1,
                is_active: true
            }
        },

        modalPiutang: {
            show: false,
            mode: 'create',
            form: {
                id: '',
                kode_piutang: '',
                nama_piutang: '',
                kode_rekening: '',
                kategori_penjamin: 'bpjs',
                jatuh_tempo_hari: 30,
                kontak_person: '',
                no_telepon: '',
                email: '',
                alamat: '',
                is_active: true
            }
        },

        init() {
            this.reinitIcons();
        },

        setTab(tab) {
            this.activeTab = tab;
            // Update browser URL without reloading
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.pushState({}, '', url);
            this.reinitIcons();
        },

        reinitIcons() {
            this.$nextTick(() => {
                if (window.lucide) {
                    lucide.createIcons();
                }
            });
        },

        openActiveCreateModal() {
            if (this.activeTab === 'coa') this.openModalCoa('create');
            else if (this.activeTab === 'tahun') this.openModalTahun('create');
            else if (this.activeTab === 'bayar') this.openModalBayar('create');
            else if (this.activeTab === 'piutang') this.openModalPiutang('create');
        },

        activeButtonText() {
            if (this.activeTab === 'coa') return '+ Tambah Akun COA';
            if (this.activeTab === 'tahun') return '+ Atur Anggaran Tahun';
            if (this.activeTab === 'bayar') return '+ Tambah Akun Bayar';
            if (this.activeTab === 'piutang') return '+ Tambah Mitra Piutang';
            return '+ Tambah Data';
        },

        formatRupiah(val) {
            const num = parseFloat(val) || 0;
            return 'Rp ' + num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        },

        refreshCurrentTab() {
            this.loading = true;
            this.fetchSummary();
            if (this.activeTab === 'coa') {
                this.fetchCoaList();
            } else if (this.activeTab === 'tahun') {
                this.fetchTahunList();
            } else if (this.activeTab === 'bayar') {
                this.fetchBayarList();
            } else if (this.activeTab === 'piutang') {
                this.fetchPiutangList();
            }
        },

        fetchSummary() {
            fetch('<?= site_url("rekening/get_summary_ajax") ?>')
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        this.summary = res.data;
                    }
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        // =====================================================================
        // COA METHODS
        // =====================================================================
        filterCoa() {
            const s = (this.filters.coa.search || '').toLowerCase().trim();
            const k = this.filters.coa.kategori;
            const l = this.filters.coa.level;
            const a = this.filters.coa.is_active;

            this.filteredCoas = this.coas.filter(item => {
                const matchSearch = !s || (item.kode_rekening || '').toLowerCase().includes(s) || (item.nama_rekening || '').toLowerCase().includes(s);
                const matchKat = !k || item.kategori === k;
                const matchLvl = !l || item.level == l;
                const matchAct = a === '' || item.is_active == a;
                return matchSearch && matchKat && matchLvl && matchAct;
            });
            this.reinitIcons();
        },

        resetCoaFilters() {
            this.filters.coa = { search: '', kategori: '', level: '', is_active: '' };
            this.filteredCoas = [...this.coas];
            this.reinitIcons();
        },

        fetchCoaList() {
            fetch('<?= site_url("rekening/get_coa_list_ajax") ?>')
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        this.coas = res.data;
                        this.filterCoa();
                    }
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        getIndentedParentLabel(p) {
            const lvl = parseInt(p.level) || 1;
            let indent = '';
            if (lvl === 1) indent = '● ';
            else if (lvl === 2) indent = '  └─ ';
            else if (lvl === 3) indent = '      ├─ ';
            else if (lvl >= 4) indent = '          • ';
            const katName = (p.nama_kategori || p.kategori || '').toUpperCase();
            return indent + p.kode_rekening + ' - ' + p.nama_rekening + ' [' + katName + ']';
        },

        openCreateSubModal(parentItem) {
            this.modalCoa.mode = 'create';
            this.parentSearch = '';
            this.parentDropdownOpen = false;
            const parentLevel = parseInt(parentItem.level) || 1;
            const nextLevel = Math.min(parentLevel + 1, 4);

            this.modalCoa.form = {
                id: '',
                kode_rekening: '',
                nama_rekening: '',
                kategori: parentItem.kategori,
                kategori_id: parentItem.kategori_id || '',
                posisi_normal: parentItem.posisi_normal || 'D',
                level: nextLevel,
                parent_kode: parentItem.kode_rekening,
                is_kas_bank: parentItem.is_kas_bank == '1',
                is_piutang: parentItem.is_piutang == '1',
                is_pendapatan: parentItem.is_pendapatan == '1',
                is_active: true,
                keterangan: 'Sub-rekening di bawah ' + parentItem.nama_rekening
            };

            this.modalCoa.show = true;
            this.fetchSuggestedCode(parentItem.kode_rekening);
            this.reinitIcons();
        },

        onParentChange() {
            const pk = this.modalCoa.form.parent_kode;
            if (!pk) {
                this.modalCoa.form.level = 1;
                this.onCategoryChange();
                return;
            }
            this.fetchSuggestedCode(pk);
        },

        fetchSuggestedCode(parentKode) {
            if (!parentKode) return;
            fetch('<?= site_url("rekening/suggest_child_code_ajax?parent_kode=") ?>' + encodeURIComponent(parentKode))
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        this.modalCoa.form.kode_rekening = res.suggested_code;
                        this.modalCoa.form.level = res.next_level;
                        this.modalCoa.form.posisi_normal = res.posisi_normal;
                        this.modalCoa.form.kategori = res.kategori;
                        this.modalCoa.form.kategori_id = res.kategori_id || '';
                        if (res.is_kas_bank) this.modalCoa.form.is_kas_bank = true;
                        if (res.is_piutang) this.modalCoa.form.is_piutang = true;
                        if (res.is_pendapatan) this.modalCoa.form.is_pendapatan = true;
                    }
                });
        },

        onCategoryChange() {
            const slug = this.modalCoa.form.kategori;
            const kat = this.kategoriList.find(k => k.slug === slug || k.kode_kategori === slug);
            if (kat) {
                this.modalCoa.form.posisi_normal = kat.posisi_normal || 'D';
                this.modalCoa.form.kategori_id = kat.id;
            }
        },

        openModalCoa(mode, item = null) {
            this.modalCoa.mode = mode;
            this.parentSearch = '';
            this.parentDropdownOpen = false;
            if (mode === 'create') {
                const defaultKat = this.kategoriList[0] || { slug: 'aset', id: 1, posisi_normal: 'D' };
                this.modalCoa.form = {
                    id: '',
                    kode_rekening: '',
                    nama_rekening: '',
                    kategori: defaultKat.slug,
                    kategori_id: defaultKat.id,
                    posisi_normal: defaultKat.posisi_normal,
                    level: 1,
                    parent_kode: '',
                    is_kas_bank: false,
                    is_piutang: false,
                    is_pendapatan: false,
                    is_active: true,
                    keterangan: ''
                };
            } else if (item) {
                this.modalCoa.form = {
                    id: item.id,
                    kode_rekening: item.kode_rekening,
                    nama_rekening: item.nama_rekening,
                    kategori: item.kategori,
                    kategori_id: item.kategori_id || '',
                    posisi_normal: item.posisi_normal,
                    level: parseInt(item.level) || 1,
                    parent_kode: item.parent_kode || '',
                    is_kas_bank: item.is_kas_bank == '1',
                    is_piutang: item.is_piutang == '1',
                    is_pendapatan: item.is_pendapatan == '1',
                    is_active: item.is_active == '1',
                    keterangan: item.keterangan || ''
                };
            }
            this.modalCoa.show = true;
            this.reinitIcons();
        },

        // =====================================================================
        // MASTER KATEGORI AKUN METHODS (PSAK)
        // =====================================================================
        openKategoriModal() {
            this.modalKategori.mode = 'create';
            this.kategoriSearch = '';
            this.modalKategori.form = {
                id: '',
                kode_kategori: '',
                nama_kategori: '',
                posisi_normal: 'D',
                urutan: this.kategoriList.length + 1,
                is_active: true,
                deskripsi: ''
            };
            this.modalKategori.show = true;
            this.reinitIcons();
        },

        openQuickAddKategori() {
            this.openKategoriModal();
        },

        filteredKategoriList() {
            const q = (this.kategoriSearch || '').toLowerCase().trim();
            if (!q) return this.kategoriList;
            return this.kategoriList.filter(k => 
                (k.kode_kategori || '').toLowerCase().includes(q) ||
                (k.nama_kategori || '').toLowerCase().includes(q) ||
                (k.deskripsi || '').toLowerCase().includes(q)
            );
        },

        fetchKategoriList() {
            fetch('<?= site_url("rekening/get_kategori_list_ajax") ?>')
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        this.kategoriList = res.data;
                    }
                });
        },

        submitKategori() {
            const formData = new FormData();
            for (let k in this.modalKategori.form) {
                if (typeof this.modalKategori.form[k] === 'boolean') {
                    formData.append(k, this.modalKategori.form[k] ? '1' : '0');
                } else {
                    formData.append(k, this.modalKategori.form[k] ?? '');
                }
            }

            fetch('<?= site_url("rekening/save_kategori_ajax") ?>', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    LIS.toast('success', res.message);
                    this.kategoriList = res.kategori_list;
                    // Sinkronisasi otomatis ke form COA jika sedang dibuka
                    if (this.modalCoa.show && res.kategori) {
                        this.modalCoa.form.kategori = res.kategori.slug;
                        this.modalCoa.form.kategori_id = res.kategori.id;
                        this.modalCoa.form.posisi_normal = res.kategori.posisi_normal;
                    }
                    this.modalKategori.mode = 'create';
                    this.modalKategori.form = {
                        id: '',
                        kode_kategori: '',
                        nama_kategori: '',
                        posisi_normal: 'D',
                        urutan: this.kategoriList.length + 1,
                        is_active: true,
                        deskripsi: ''
                    };
                } else {
                    LIS.alert('Gagal Menyimpan', res.message, 'error');
                }
            })
            .catch(() => {
                LIS.alert('Kesalahan', 'Gagal memproses kategori.', 'error');
            });
        },

        editKategori(kat) {
            this.modalKategori.mode = 'edit';
            this.modalKategori.form = {
                id: kat.id,
                kode_kategori: kat.kode_kategori,
                nama_kategori: kat.nama_kategori,
                posisi_normal: kat.posisi_normal,
                urutan: kat.urutan,
                is_active: kat.is_active == '1',
                deskripsi: kat.deskripsi || ''
            };
        },

        cancelEditKategori() {
            this.modalKategori.mode = 'create';
            this.modalKategori.form = {
                id: '',
                kode_kategori: '',
                nama_kategori: '',
                posisi_normal: 'D',
                urutan: this.kategoriList.length + 1,
                is_active: true,
                deskripsi: ''
            };
        },

        deleteKategori(kat) {
            LIS.confirm('Hapus Kategori Akun?', 'Hapus kategori <b>' + kat.nama_kategori + '</b>?').then(result => {
                if (result.isConfirmed) {
                    fetch('<?= site_url("rekening/delete_kategori_ajax/") ?>' + kat.id, { method: 'POST' })
                        .then(r => r.json())
                        .then(res => {
                            if (res.status === 'success') {
                                LIS.toast('success', res.message);
                                this.kategoriList = res.kategori_list;
                            } else {
                                LIS.alert('Peringatan', res.message, 'warning');
                            }
                        });
                }
            });
        },

        toggleKategori(kat) {
            fetch('<?= site_url("rekening/toggle_kategori_ajax/") ?>' + kat.id, { method: 'POST' })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        kat.is_active = res.is_active;
                        LIS.toast('success', res.message);
                    } else {
                        LIS.alert('Gagal', res.message, 'error');
                    }
                });
        },

        submitCoa() {
            const formData = new FormData();
            for (let k in this.modalCoa.form) {
                if (typeof this.modalCoa.form[k] === 'boolean') {
                    formData.append(k, this.modalCoa.form[k] ? '1' : '0');
                } else {
                    formData.append(k, this.modalCoa.form[k] ?? '');
                }
            }

            fetch('<?= site_url("rekening/save_coa_ajax") ?>', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    LIS.toast('success', res.message);
                    this.modalCoa.show = false;
                    this.fetchCoaList();
                    this.fetchSummary();
                } else {
                    LIS.alert('Gagal Menyimpan', res.message, 'error');
                }
            })
            .catch(() => {
                LIS.alert('Kesalahan Sistem', 'Tidak dapat menghubungi server.', 'error');
            });
        },

        toggleCoa(item) {
            fetch('<?= site_url("rekening/toggle_coa_ajax/") ?>' + item.id, { method: 'POST' })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        item.is_active = res.new_status;
                        LIS.toast('success', res.message);
                    } else {
                        LIS.alert('Gagal', res.message, 'error');
                    }
                });
        },

        deleteCoa(item) {
            LIS.confirm('Hapus Akun COA?', 'Apakah Anda yakin ingin menghapus akun <b>' + item.kode_rekening + ' - ' + item.nama_rekening + '</b>?').then(result => {
                if (result.isConfirmed) {
                    fetch('<?= site_url("rekening/delete_coa_ajax/") ?>' + item.id, { method: 'POST' })
                        .then(r => r.json())
                        .then(res => {
                            if (res.status === 'success') {
                                LIS.toast('success', res.message);
                                this.fetchCoaList();
                                this.fetchSummary();
                            } else {
                                LIS.alert('Tidak Dapat Dihapus', res.message, 'warning');
                            }
                        });
                }
            });
        },

        // =====================================================================
        // REKENING TAHUN METHODS
        // =====================================================================
        fetchTahunList() {
            this.loading = true;
            const params = new URLSearchParams({
                tahun: this.filters.tahun.tahun,
                status: this.filters.tahun.status
            });

            fetch('<?= site_url("rekening/get_tahun_list_ajax?") ?>' + params.toString())
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        this.rekeningTahun = res.data;
                        this.filterTahun();
                    }
                })
                .finally(() => {
                    this.loading = false;
                    this.reinitIcons();
                });
        },

        filterTahun() {
            const s = (this.filters.tahun.search || '').toLowerCase().trim();
            if (!s) {
                this.filteredTahun = this.rekeningTahun;
                this.reinitIcons();
                return;
            }
            this.filteredTahun = this.rekeningTahun.filter(item => {
                return (item.kode_rekening || '').toLowerCase().includes(s) ||
                       (item.nama_rekening || '').toLowerCase().includes(s) ||
                       (item.kategori || '').toLowerCase().includes(s);
            });
            this.reinitIcons();
        },

        openModalTahun(mode, item = null) {
            this.modalTahun.mode = mode;
            this.tahunCoaSearch = '';
            this.tahunCoaDropdownOpen = false;
            if (mode === 'create') {
                this.modalTahun.form = {
                    id: '',
                    tahun: this.filters.tahun.tahun || this.currentYear,
                    kode_rekening: '',
                    saldo_awal: 0,
                    target_anggaran: 0,
                    realisasi_debet: 0,
                    realisasi_kredit: 0,
                    status: 'aktif',
                    keterangan: ''
                };
            } else if (item) {
                this.modalTahun.form = {
                    id: item.id,
                    tahun: item.tahun,
                    kode_rekening: item.kode_rekening,
                    saldo_awal: item.saldo_awal,
                    target_anggaran: item.target_anggaran,
                    realisasi_debet: item.realisasi_debet,
                    realisasi_kredit: item.realisasi_kredit,
                    status: item.status,
                    keterangan: item.keterangan || ''
                };
            }
            this.modalTahun.show = true;
            this.reinitIcons();
        },

        submitTahun() {
            const formData = new FormData();
            for (let k in this.modalTahun.form) {
                formData.append(k, this.modalTahun.form[k] ?? '');
            }

            fetch('<?= site_url("rekening/save_tahun_ajax") ?>', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    LIS.toast('success', res.message);
                    this.modalTahun.show = false;
                    this.fetchTahunList();
                    this.fetchSummary();
                } else {
                    LIS.alert('Gagal Menyimpan', res.message, 'error');
                }
            });
        },

        toggleStatusTahun(item) {
            const newAction = item.status === 'aktif' ? 'Tutup Buku' : 'Buka Kembali';
            LIS.confirm(newAction + ' Rekening?', 'Apakah Anda ingin mengubah status akun <b>' + item.kode_rekening + '</b> menjadi ' + newAction + '?').then(result => {
                if (result.isConfirmed) {
                    fetch('<?= site_url("rekening/toggle_tahun_status_ajax/") ?>' + item.id, { method: 'POST' })
                        .then(r => r.json())
                        .then(res => {
                            if (res.status === 'success') {
                                item.status = res.new_status;
                                LIS.toast('success', res.message);
                            } else {
                                LIS.alert('Gagal', res.message, 'error');
                            }
                        });
                }
            });
        },

        deleteTahun(item) {
            LIS.confirm('Hapus Data Rekening Tahun?', 'Data saldo dan anggaran untuk tahun ' + item.tahun + ' akan dihapus.').then(result => {
                if (result.isConfirmed) {
                    fetch('<?= site_url("rekening/delete_tahun_ajax/") ?>' + item.id, { method: 'POST' })
                        .then(r => r.json())
                        .then(res => {
                            if (res.status === 'success') {
                                LIS.toast('success', res.message);
                                this.fetchTahunList();
                                this.fetchSummary();
                            } else {
                                LIS.alert('Gagal', res.message, 'warning');
                            }
                        });
                }
            });
        },

        // =====================================================================
        // AKUN BAYAR METHODS
        // =====================================================================
        filterBayar() {
            const s = (this.filters.bayar.search || '').toLowerCase().trim();
            const t = this.filters.bayar.tipe;
            const a = this.filters.bayar.is_active;

            this.filteredBayar = this.akunBayar.filter(item => {
                const matchSearch = !s || (item.kode_bayar || '').toLowerCase().includes(s) || (item.nama_bayar || '').toLowerCase().includes(s) || (item.nama_bank || '').toLowerCase().includes(s);
                const matchTipe = !t || item.tipe_transaksi === t;
                const matchAct = a === '' || item.is_active == a;
                return matchSearch && matchTipe && matchAct;
            });
            this.reinitIcons();
        },

        fetchBayarList() {
            fetch('<?= site_url("rekening/get_bayar_list_ajax") ?>')
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        this.akunBayar = res.data;
                        this.filterBayar();
                    }
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        openModalBayar(mode, item = null) {
            this.modalBayar.mode = mode;
            this.bayarCoaSearch = '';
            this.bayarCoaDropdownOpen = false;
            if (mode === 'create') {
                this.modalBayar.form = {
                    id: '',
                    kode_bayar: '',
                    nama_bayar: '',
                    kode_rekening: '',
                    tipe_transaksi: 'cash',
                    nama_bank: '',
                    nomor_rekening_bank: '',
                    atas_nama: '',
                    biaya_admin: 0,
                    urutan: this.akunBayar.length + 1,
                    is_active: true
                };
            } else if (item) {
                this.modalBayar.form = {
                    id: item.id,
                    kode_bayar: item.kode_bayar,
                    nama_bayar: item.nama_bayar,
                    kode_rekening: item.kode_rekening,
                    tipe_transaksi: item.tipe_transaksi,
                    nama_bank: item.nama_bank || '',
                    nomor_rekening_bank: item.nomor_rekening_bank || '',
                    atas_nama: item.atas_nama || '',
                    biaya_admin: item.biaya_admin,
                    urutan: item.urutan,
                    is_active: item.is_active == '1'
                };
            }
            this.modalBayar.show = true;
            this.reinitIcons();
        },

        submitBayar() {
            const formData = new FormData();
            for (let k in this.modalBayar.form) {
                if (typeof this.modalBayar.form[k] === 'boolean') {
                    formData.append(k, this.modalBayar.form[k] ? '1' : '0');
                } else {
                    formData.append(k, this.modalBayar.form[k] ?? '');
                }
            }

            fetch('<?= site_url("rekening/save_bayar_ajax") ?>', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    LIS.toast('success', res.message);
                    this.modalBayar.show = false;
                    this.fetchBayarList();
                    this.fetchSummary();
                } else {
                    LIS.alert('Gagal Menyimpan', res.message, 'error');
                }
            });
        },

        toggleBayar(item) {
            fetch('<?= site_url("rekening/toggle_bayar_ajax/") ?>' + item.id, { method: 'POST' })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        item.is_active = res.new_status;
                        LIS.toast('success', res.message);
                    } else {
                        LIS.alert('Gagal', res.message, 'error');
                    }
                });
        },

        deleteBayar(item) {
            LIS.confirm('Hapus Channel Bayar?', 'Apakah Anda yakin ingin menghapus channel pembayaran <b>' + item.nama_bayar + '</b>?').then(result => {
                if (result.isConfirmed) {
                    fetch('<?= site_url("rekening/delete_bayar_ajax/") ?>' + item.id, { method: 'POST' })
                        .then(r => r.json())
                        .then(res => {
                            if (res.status === 'success') {
                                LIS.toast('success', res.message);
                                this.fetchBayarList();
                                this.fetchSummary();
                            } else {
                                LIS.alert('Gagal', res.message, 'warning');
                            }
                        });
                }
            });
        },

        // =====================================================================
        // AKUN PIUTANG METHODS
        // =====================================================================
        filterPiutang() {
            const s = (this.filters.piutang.search || '').toLowerCase().trim();
            const p = this.filters.piutang.penjamin;
            const a = this.filters.piutang.is_active;

            this.filteredPiutang = this.akunPiutang.filter(item => {
                const matchSearch = !s || (item.kode_piutang || '').toLowerCase().includes(s) || (item.nama_piutang || '').toLowerCase().includes(s) || (item.kontak_person || '').toLowerCase().includes(s);
                const matchPenjamin = !p || item.kategori_penjamin === p;
                const matchAct = a === '' || item.is_active == a;
                return matchSearch && matchPenjamin && matchAct;
            });
            this.reinitIcons();
        },

        fetchPiutangList() {
            fetch('<?= site_url("rekening/get_piutang_list_ajax") ?>')
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        this.akunPiutang = res.data;
                        this.filterPiutang();
                    }
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        openModalPiutang(mode, item = null) {
            this.modalPiutang.mode = mode;
            this.piutangCoaSearch = '';
            this.piutangCoaDropdownOpen = false;
            if (mode === 'create') {
                this.modalPiutang.form = {
                    id: '',
                    kode_piutang: '',
                    nama_piutang: '',
                    kode_rekening: '',
                    kategori_penjamin: 'bpjs',
                    jatuh_tempo_hari: 30,
                    kontak_person: '',
                    no_telepon: '',
                    email: '',
                    alamat: '',
                    is_active: true
                };
            } else if (item) {
                this.modalPiutang.form = {
                    id: item.id,
                    kode_piutang: item.kode_piutang,
                    nama_piutang: item.nama_piutang,
                    kode_rekening: item.kode_rekening,
                    kategori_penjamin: item.kategori_penjamin,
                    jatuh_tempo_hari: item.jatuh_tempo_hari,
                    kontak_person: item.kontak_person || '',
                    no_telepon: item.no_telepon || '',
                    email: item.email || '',
                    alamat: item.alamat || '',
                    is_active: item.is_active == '1'
                };
            }
            this.modalPiutang.show = true;
            this.reinitIcons();
        },

        submitPiutang() {
            const formData = new FormData();
            for (let k in this.modalPiutang.form) {
                if (typeof this.modalPiutang.form[k] === 'boolean') {
                    formData.append(k, this.modalPiutang.form[k] ? '1' : '0');
                } else {
                    formData.append(k, this.modalPiutang.form[k] ?? '');
                }
            }

            fetch('<?= site_url("rekening/save_piutang_ajax") ?>', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    LIS.toast('success', res.message);
                    this.modalPiutang.show = false;
                    this.fetchPiutangList();
                    this.fetchSummary();
                } else {
                    LIS.alert('Gagal Menyimpan', res.message, 'error');
                }
            });
        },

        togglePiutang(item) {
            fetch('<?= site_url("rekening/toggle_piutang_ajax/") ?>' + item.id, { method: 'POST' })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        item.is_active = res.new_status;
                        LIS.toast('success', res.message);
                    } else {
                        LIS.alert('Gagal', res.message, 'error');
                    }
                });
        },

        deletePiutang(item) {
            LIS.confirm('Hapus Mitra Piutang?', 'Apakah Anda yakin ingin menghapus akun piutang <b>' + item.nama_piutang + '</b>?').then(result => {
                if (result.isConfirmed) {
                    fetch('<?= site_url("rekening/delete_piutang_ajax/") ?>' + item.id, { method: 'POST' })
                        .then(r => r.json())
                        .then(res => {
                            if (res.status === 'success') {
                                LIS.toast('success', res.message);
                                this.fetchPiutangList();
                                this.fetchSummary();
                            } else {
                                LIS.alert('Gagal', res.message, 'warning');
                            }
                        });
                }
            });
        },

        // =====================================================================
        // AUTOCOMPLETE & SEARCHABLE COMBOBOX HELPERS
        // =====================================================================
        getParentObj(kode) {
            return this.coas.find(c => c.kode_rekening === kode) || null;
        },
        getParentName(kode) {
            if (!kode) return '';
            const c = this.getParentObj(kode);
            return c ? c.nama_rekening : kode;
        },
        getParentLevel(kode) {
            if (!kode) return '1';
            const c = this.getParentObj(kode);
            return c ? c.level : '1';
        },
        filteredParentAccounts() {
            let list = this.coas;
            if (this.modalCoa.mode === 'edit' && this.modalCoa.form.kode_rekening) {
                list = list.filter(c => c.kode_rekening !== this.modalCoa.form.kode_rekening);
            }
            const q = (this.parentSearch || '').toLowerCase().trim();
            if (!q) return list;
            return list.filter(c => 
                (c.kode_rekening || '').toLowerCase().includes(q) ||
                (c.nama_rekening || '').toLowerCase().includes(q) ||
                (c.kategori || '').toLowerCase().includes(q)
            );
        },
        selectParentAccount(p) {
            if (!p) {
                this.modalCoa.form.parent_kode = '';
            } else {
                this.modalCoa.form.parent_kode = p.kode_rekening;
            }
            this.parentDropdownOpen = false;
            this.parentSearch = '';
            this.onParentChange();
            this.reinitIcons();
        },
        clearParentAccount() {
            this.modalCoa.form.parent_kode = '';
            this.parentDropdownOpen = false;
            this.parentSearch = '';
            this.onParentChange();
            this.reinitIcons();
        },

        // Tahun COA Combobox
        getTahunCoaObj(kode) {
            return this.coas.find(c => c.kode_rekening === kode) || null;
        },
        filteredTahunAccounts() {
            const q = (this.tahunCoaSearch || '').toLowerCase().trim();
            if (!q) return this.coas;
            return this.coas.filter(c => 
                (c.kode_rekening || '').toLowerCase().includes(q) ||
                (c.nama_rekening || '').toLowerCase().includes(q) ||
                (c.kategori || '').toLowerCase().includes(q)
            );
        },
        selectTahunAccount(c) {
            if (c) {
                this.modalTahun.form.kode_rekening = c.kode_rekening;
            } else {
                this.modalTahun.form.kode_rekening = '';
            }
            this.tahunCoaDropdownOpen = false;
            this.tahunCoaSearch = '';
            this.reinitIcons();
        },

        // Bayar COA Combobox
        getBayarCoaObj(kode) {
            return this.coas.find(c => c.kode_rekening === kode) || null;
        },
        filteredBayarAccounts() {
            const q = (this.bayarCoaSearch || '').toLowerCase().trim();
            let list = this.coas;
            if (!q) return list;
            return list.filter(c => 
                (c.kode_rekening || '').toLowerCase().includes(q) ||
                (c.nama_rekening || '').toLowerCase().includes(q) ||
                (c.nama_bank || '').toLowerCase().includes(q) ||
                (c.kategori || '').toLowerCase().includes(q)
            );
        },
        selectBayarAccount(c) {
            if (c) {
                this.modalBayar.form.kode_rekening = c.kode_rekening;
            } else {
                this.modalBayar.form.kode_rekening = '';
            }
            this.bayarCoaDropdownOpen = false;
            this.bayarCoaSearch = '';
            this.reinitIcons();
        },

        // Piutang COA Combobox
        getPiutangCoaObj(kode) {
            return this.coas.find(c => c.kode_rekening === kode) || null;
        },
        filteredPiutangAccounts() {
            const q = (this.piutangCoaSearch || '').toLowerCase().trim();
            let list = this.coas;
            if (!q) return list;
            return list.filter(c => 
                (c.kode_rekening || '').toLowerCase().includes(q) ||
                (c.nama_rekening || '').toLowerCase().includes(q) ||
                (c.kategori || '').toLowerCase().includes(q)
            );
        },
        selectPiutangAccount(c) {
            if (c) {
                this.modalPiutang.form.kode_rekening = c.kode_rekening;
            } else {
                this.modalPiutang.form.kode_rekening = '';
            }
            this.piutangCoaDropdownOpen = false;
            this.piutangCoaSearch = '';
            this.reinitIcons();
        },

        // Form Cari Autocomplete Suggestions
        coaSearchSuggestions() {
            const q = (this.filters.coa.search || '').toLowerCase().trim();
            if (!q || q.length < 1) return [];
            return this.coas.filter(c => 
                (c.kode_rekening || '').toLowerCase().includes(q) ||
                (c.nama_rekening || '').toLowerCase().includes(q) ||
                (c.kategori || '').toLowerCase().includes(q)
            ).slice(0, 8);
        },
        selectCoaSuggestion(item) {
            this.filters.coa.search = item.nama_rekening;
            this.coaSearchSuggestionsOpen = false;
            this.filterCoa();
        },

        bayarSearchSuggestions() {
            const q = (this.filters.bayar.search || '').toLowerCase().trim();
            if (!q || q.length < 1) return [];
            return this.akunBayar.filter(b => 
                (b.kode_bayar || '').toLowerCase().includes(q) ||
                (b.nama_bayar || '').toLowerCase().includes(q) ||
                (b.nama_bank || '').toLowerCase().includes(q)
            ).slice(0, 8);
        },
        selectBayarSuggestion(item) {
            this.filters.bayar.search = item.nama_bayar;
            this.bayarSearchSuggestionsOpen = false;
            this.filterBayar();
        },

        piutangSearchSuggestions() {
            const q = (this.filters.piutang.search || '').toLowerCase().trim();
            if (!q || q.length < 1) return [];
            return this.akunPiutang.filter(p => 
                (p.kode_piutang || '').toLowerCase().includes(q) ||
                (p.nama_piutang || '').toLowerCase().includes(q) ||
                (p.kontak_person || '').toLowerCase().includes(q)
            ).slice(0, 8);
        },
        selectPiutangSuggestion(item) {
            this.filters.piutang.search = item.nama_piutang;
            this.piutangSearchSuggestionsOpen = false;
            this.filterPiutang();
        },

        tahunSearchSuggestions() {
            const q = (this.filters.tahun.search || '').toLowerCase().trim();
            if (!q || q.length < 1) return [];
            return this.rekeningTahun.filter(t => 
                (t.kode_rekening || '').toLowerCase().includes(q) ||
                (t.nama_rekening || '').toLowerCase().includes(q)
            ).slice(0, 8);
        },
        selectTahunSuggestion(item) {
            this.filters.tahun.search = item.nama_rekening;
            this.tahunSearchSuggestionsOpen = false;
            this.filterTahun();
        }
    };
}
</script>
