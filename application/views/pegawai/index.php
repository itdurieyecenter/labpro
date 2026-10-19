<div class="space-y-5">

    <!-- 1. BREADCRUMB & PAGE HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-slate-400">Kepegawaian</span>
                <span>/</span>
                <span class="text-emerald-800 font-semibold">Data Pegawai</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </span>
                <span>Master Data Pegawai & Nakes ASN</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">UPT Laboratorium Kesehatan Provinsi Riau &bull; Standar Format BKN & Kemenkes RI</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" onclick="reloadTable()" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold flex items-center gap-2 transition-colors border border-slate-200" title="Muat Ulang Data">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Refresh</span>
            </button>

            <button type="button" onclick="openRefModal('all', 'Kelola Master Referensi Pegawai')" class="px-3.5 py-2.5 rounded-xl bg-white hover:bg-emerald-50 text-emerald-800 text-xs font-bold flex items-center gap-2 transition-colors border border-emerald-300 shadow-xs" title="Kelola Data Referensi Kepegawaian">
                <i data-lucide="sliders-horizontal" class="w-4 h-4 text-emerald-700"></i>
                <span>Kelola Referensi</span>
            </button>

            <?php if ($can_create): ?>
                <button type="button" onclick="openModalPegawai('create')" class="px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-2 shadow-sm transition-all border border-emerald-600 shadow-emerald-900/10">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    <span>Tambah Pegawai Baru</span>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- 2. SUMMARY STATISTIK CARDS (Melayu Riau Theme) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Total Pegawai -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-center flex-shrink-0">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Total Pegawai</span>
                <span id="stat-total" class="text-xl sm:text-2xl font-black text-slate-900 leading-none"><?= $summary['total'] ?></span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Pegawai Labkes Riau</span>
            </div>
        </div>

        <!-- PNS / CPNS -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 flex items-center justify-center flex-shrink-0">
                <i data-lucide="shield-check" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">PNS & CPNS</span>
                <span id="stat-pns" class="text-xl sm:text-2xl font-black text-amber-800 leading-none"><?= $summary['asn_total'] ?></span>
                <span class="text-[10px] text-slate-400 block mt-0.5"><?= $summary['pns'] ?> PNS &bull; <?= $summary['cpns'] ?> CPNS</span>
            </div>
        </div>

        <!-- PPPK -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-teal-50 border border-teal-200 text-teal-800 flex items-center justify-center flex-shrink-0">
                <i data-lucide="user-check" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">PPPK</span>
                <span id="stat-pppk" class="text-xl sm:text-2xl font-black text-teal-800 leading-none"><?= $summary['pppk'] ?></span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Pegawai Kontrak ASN</span>
            </div>
        </div>

        <!-- Nakes Terkredensial / STR Aktif -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-center flex-shrink-0">
                <i data-lucide="award" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Kredensial STR</span>
                <span id="stat-str" class="text-xl sm:text-2xl font-black text-rose-800 leading-none"><?= $summary['str_aktif'] ?></span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Nakes STR Terdaftar</span>
            </div>
        </div>
    </div>

    <!-- 3. FILTER & TOOLBAR CARD -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs space-y-3">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <!-- Filter Tabs Status Pegawai -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 scrollbar-none">
                <button type="button" onclick="filterStatus('all')" id="tab-status-all" class="filter-tab-btn active-tab px-3 py-1.5 rounded-xl text-xs font-bold transition-all">
                    Semua Pegawai
                </button>
                <button type="button" onclick="filterStatus('PNS')" id="tab-status-pns" class="filter-tab-btn px-3 py-1.5 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100 transition-all">
                    PNS
                </button>
                <button type="button" onclick="filterStatus('CPNS')" id="tab-status-cpns" class="filter-tab-btn px-3 py-1.5 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100 transition-all">
                    CPNS
                </button>
                <button type="button" onclick="filterStatus('PPPK')" id="tab-status-pppk" class="filter-tab-btn px-3 py-1.5 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100 transition-all">
                    PPPK
                </button>
                <button type="button" onclick="filterStatus('Non-ASN / BLUD')" id="tab-status-nonasn" class="filter-tab-btn px-3 py-1.5 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-100 transition-all">
                    Non-ASN / BLUD
                </button>
            </div>

            <!-- Dropdown Filter Seksi / Instalasi -->
            <div class="flex items-center gap-2">
                <label for="filter-seksi" class="text-xs font-bold text-slate-500 whitespace-nowrap">Seksi / Ruangan:</label>
                <select id="filter-seksi" onchange="reloadTable()" class="h-9 px-3 text-xs font-medium rounded-xl border border-slate-200 bg-slate-50 text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-600">
                    <option value="all">-- Semua Seksi / Instalasi --</option>
                    <?php foreach ($seksi_ref as $key => $name): ?>
                        <option value="<?= html_escape($key) ?>"><?= html_escape($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- 4. DATATABLES CARD -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-emerald-600"></span>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Daftar Pegawai & Kredensial Medis UPT Labkesda</h3>
            </div>
            <span class="text-xs text-slate-400 font-mono">SIMRS &bull; SIASN BKN</span>
        </div>

        <div class="p-4 sm:p-5 overflow-x-auto">
            <table id="table-pegawai" class="w-full text-xs text-left display nowrap" style="width:100%">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                        <th class="py-3 px-3 w-12 text-center">No</th>
                        <th class="py-3 px-3">NIP & NIK BKN</th>
                        <th class="py-3 px-3">Nama Pegawai & Gelar</th>
                        <th class="py-3 px-3">Golongan & Pangkat</th>
                        <th class="py-3 px-3">Jabatan & Penempatan</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-3">Kredensial STR / SIP</th>
                        <th class="py-3 px-3 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100"></tbody>
            </table>
        </div>
    </div>

</div>

<!-- ========================================================================= -->
<!-- 5. FULL-WIDTH RESPONSIVE MODAL: FORM REGISTRASI / EDIT PEGAWAI           -->
<!-- ========================================================================= -->
<div id="modal-backdrop" class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-50 hidden transition-opacity"></div>

<div id="modal-pegawai" class="fixed inset-0 z-50 hidden items-center justify-center p-2 sm:p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-6xl max-h-[92vh] flex flex-col overflow-hidden my-auto animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Modal Header (Melayu Emerald & Lancang Kuning) -->
        <div class="px-6 py-4 bg-gradient-to-r from-emerald-950 via-emerald-900 to-slate-900 text-white flex items-center justify-between border-b border-amber-500/30 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-emerald-700 to-amber-500 text-amber-100 flex items-center justify-center shadow-md border border-amber-400/40">
                    <i data-lucide="user-check" class="w-5 h-5 text-amber-200"></i>
                </div>
                <div>
                    <h3 id="modal-pegawai-title" class="text-base sm:text-lg font-black tracking-tight text-white flex items-center gap-2">
                        <span>Registrasi Data Pegawai Baru</span>
                    </h3>
                    <p class="text-xs text-amber-200/80 font-medium">UPT Laboratorium Kesehatan Provinsi Riau &bull; Standar SIASN BKN</p>
                </div>
            </div>

            <button type="button" onclick="closeModalPegawai()" class="text-slate-400 hover:text-white p-2 rounded-xl hover:bg-white/10 transition-colors focus:outline-none">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Modal Body (Scrollable Form) -->
        <div class="p-6 overflow-y-auto space-y-6 flex-1 bg-slate-50/60">
            <form id="form-pegawai" onsubmit="savePegawai(event)" class="space-y-6">
                <input type="hidden" name="id" id="pegawai-id">

                <!-- SECTION 1: IDENTITAS UTAMA & DEMOGRAFI ASN -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2 text-emerald-900 font-bold text-sm">
                            <span class="w-6 h-6 rounded-lg bg-emerald-700 text-amber-300 flex items-center justify-center text-xs font-black shadow-xs">1</span>
                            <span>Identitas Demografi & Kependudukan (BKN Kemendagri)</span>
                        </div>
                        <span class="text-[10px] font-mono text-slate-400">SIASN Format 18 Digit</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- NIP 18 Digit dengan Smart Parser & Kode Otomatis Non-ASN -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-slate-700">
                                    Nomor Induk Pegawai (NIP) <span class="text-rose-500">*</span>
                                </label>
                                <button type="button" onclick="generateNipOtomatis()" class="text-[10px] font-bold text-emerald-800 hover:text-emerald-950 bg-emerald-50 hover:bg-emerald-100 px-2 py-0.5 rounded border border-emerald-300 inline-flex items-center gap-1 transition-all" title="Generate NIP / Kode Pegawai Otomatis untuk Non-ASN / Honorer / Kontrak">
                                    <i data-lucide="sparkles" class="w-3 h-3 text-amber-500"></i> <span>Kode Otomatis</span>
                                </button>
                            </div>
                            <div class="relative">
                                <input type="text" name="nip" id="pegawai-nip" maxlength="18" oninput="parseNip(this.value)" class="lis-form-control font-mono font-bold text-emerald-950 uppercase" placeholder="198504182008012003" required>
                            </div>
                            <div id="nip-parser-hint" class="text-[10px] text-slate-400 mt-1 flex items-center gap-1">
                                <span>18 digit standar BKN: Tgl Lahir + Thn/Bln Masuk + JK + No Urut (klik Kode Otomatis untuk Non-ASN)</span>
                            </div>
                        </div>

                        <!-- NIK 16 Digit -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                NIK e-KTP (Kemendagri)
                            </label>
                            <input type="text" name="nik" id="pegawai-nik" maxlength="16" class="lis-form-control font-mono" placeholder="1471011205780002">
                            <span class="text-[10px] text-slate-400 mt-1 block">16 digit nomor induk kependudukan</span>
                        </div>

                        <!-- Status Pegawai -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-slate-700">
                                    Status Kepegawaian <span class="text-rose-500">*</span>
                                </label>
                                <button type="button" onclick="openRefModal('status_pegawai', 'Status Kepegawaian')" class="text-[10px] font-bold text-emerald-800 hover:text-emerald-950 bg-emerald-50 hover:bg-emerald-100 px-2 py-0.5 rounded border border-emerald-300 inline-flex items-center gap-1 transition-all" title="Kelola Master Status Kepegawaian">
                                    <i data-lucide="plus" class="w-3 h-3"></i> <span>Kelola</span>
                                </button>
                            </div>
                            <select name="status_pegawai" id="pegawai-status" onchange="onStatusPegawaiChange(this.value)" class="lis-form-control font-bold text-slate-800" required>
                                <?php if (!empty($form_ref['status_pegawai'])): ?>
                                    <?php foreach ($form_ref['status_pegawai'] as $r): ?>
                                        <option value="<?= html_escape($r['kode'] ?: $r['nama']) ?>"><?= html_escape($r['nama']) ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="PNS">PNS (Pegawai Negeri Sipil)</option>
                                    <option value="CPNS">CPNS (Calon Pegawai Negeri Sipil)</option>
                                    <option value="PPPK">PPPK (Pemerintah Perjanjian Kerja)</option>
                                    <option value="Non-ASN / BLUD">Non-ASN / Tenaga BLUD / Honorer</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Gelar & Nama Lengkap -->
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Gelar Depan</label>
                            <input type="text" name="gelar_depan" id="pegawai-gelar-depan" oninput="updatePreviewCard()" class="lis-form-control" placeholder="dr., drg., Apt.">
                        </div>
                        <div class="md:col-span-7">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap (Tanpa Gelar) <span class="text-rose-500">*</span></label>
                            <input type="text" name="nama_lengkap" id="pegawai-nama" oninput="updatePreviewCard()" class="lis-form-control font-bold text-slate-900" placeholder="Zulkifli Mansur" required>
                        </div>
                        <div class="md:col-span-3">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Gelar Belakang</label>
                            <input type="text" name="gelar_belakang" id="pegawai-gelar-belakang" oninput="updatePreviewCard()" class="lis-form-control" placeholder="Sp.PK, M.Biomed, A.Md.AK">
                        </div>
                    </div>

                    <!-- Tempat, Tanggal Lahir, Gender, Agama, Status Nikah, Gol Darah -->
                    <div class="grid grid-cols-2 md:grid-cols-6 gap-3">
                        <div class="col-span-2 md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" id="pegawai-tempat-lahir" class="lis-form-control" placeholder="Pekanbaru">
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" id="pegawai-tanggal-lahir" class="lis-form-control font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Jenis Kelamin</label>
                            <select name="jenis_kelamin" id="pegawai-jk" onchange="updatePreviewCard()" class="lis-form-control">
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Agama</label>
                            <select name="agama" id="pegawai-agama" class="lis-form-control">
                                <option value="Islam">Islam</option>
                                <option value="Kristen Protestan">Kristen</option>
                                <option value="Katolik">Katolik</option>
                                <option value="Hindu">Hindu</option>
                                <option value="Buddha">Buddha</option>
                                <option value="Khonghucu">Khonghucu</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Gol. Darah</label>
                            <select name="golongan_darah" id="pegawai-goldar" class="lis-form-control">
                                <option value="">-</option>
                                <option value="A+">A+</option>
                                <option value="B+">B+</option>
                                <option value="AB+">AB+</option>
                                <option value="O+">O+</option>
                                <option value="A-">A-</option>
                                <option value="B-">B-</option>
                                <option value="AB-">AB-</option>
                                <option value="O-">O-</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: STATUS KEPEGAWAIAN & KEPANGKATAN BKN -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2 text-emerald-900 font-bold text-sm">
                            <span class="w-6 h-6 rounded-lg bg-emerald-700 text-amber-300 flex items-center justify-center text-xs font-black shadow-xs">2</span>
                            <span>Kepangkatan, Golongan Ruang & Legalitas SK</span>
                        </div>
                        <span class="text-[10px] text-slate-400">Badan Kepegawaian Negara</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Golongan Ruang -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-slate-700">Golongan Ruang</label>
                                <button type="button" onclick="openRefModal('golongan_ruang', 'Golongan Ruang & Pangkat BKN')" class="text-[10px] font-bold text-emerald-800 hover:text-emerald-950 bg-emerald-50 hover:bg-emerald-100 px-2 py-0.5 rounded border border-emerald-300 inline-flex items-center gap-1 transition-all" title="Kelola Master Golongan Ruang">
                                    <i data-lucide="plus" class="w-3 h-3"></i> <span>Kelola</span>
                                </button>
                            </div>
                            <select name="kode_golongan" id="pegawai-golongan" onchange="syncGolonganPangkat(this.value)" class="lis-form-control font-bold text-emerald-950">
                                <option value="">-- Pilih Golongan --</option>
                                <?php if (!empty($form_ref['golongan_ruang'])): ?>
                                    <?php foreach ($form_ref['golongan_ruang'] as $r): ?>
                                        <option value="<?= html_escape($r['kode'] ?: $r['nama']) ?>" data-pangkat="<?= html_escape($r['nama']) ?>"><?= html_escape($r['kode'] ?: $r['nama']) ?> - <?= html_escape($r['nama']) ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <?php foreach ($pangkat_ref as $k => $v): ?>
                                        <option value="<?= $k ?>" data-pangkat="<?= html_escape($v) ?>"><?= $k ?> - <?= html_escape($v) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Nama Pangkat Resmi -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Pangkat</label>
                            <input type="text" name="nama_pangkat" id="pegawai-pangkat" oninput="updatePreviewCard()" class="lis-form-control font-medium text-slate-800" placeholder="e.g. Pembina Tingkat I">
                        </div>

                        <!-- TMT Golongan Terakhir -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">TMT Pangkat Terakhir</label>
                            <input type="date" name="tmt_golongan" id="pegawai-tmt-golongan" class="lis-form-control font-mono">
                        </div>

                        <!-- Nomor SK Kenaikan Pangkat -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Nomor SK Pangkat</label>
                            <input type="text" name="nomor_sk_kp" id="pegawai-sk-kp" class="lis-form-control" placeholder="823.4/BKD/KP/2022/...">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 pt-1">
                        <!-- TMT CPNS -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">TMT CPNS</label>
                            <input type="date" name="tmt_cpns" id="pegawai-tmt-cpns" class="lis-form-control font-mono">
                        </div>
                        <!-- Nomor SK CPNS -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">No. SK CPNS</label>
                            <input type="text" name="nomor_sk_cpns" id="pegawai-sk-cpns" class="lis-form-control" placeholder="813/BKD/2005/...">
                        </div>
                        <!-- TMT PNS (100%) -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">TMT PNS (100%)</label>
                            <input type="date" name="tmt_pns" id="pegawai-tmt-pns" class="lis-form-control font-mono">
                        </div>
                        <!-- Nomor SK PNS -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">No. SK PNS</label>
                            <input type="text" name="nomor_sk_pns" id="pegawai-sk-pns" class="lis-form-control" placeholder="821/BKD/2006/...">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-1">
                        <!-- Masa Kerja Golongan (MKG) -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Masa Kerja (Tahun)</label>
                            <input type="number" min="0" max="50" name="mkg_tahun" id="pegawai-mkg-thn" class="lis-form-control font-mono" value="0">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Masa Kerja (Bulan)</label>
                            <input type="number" min="0" max="11" name="mkg_bulan" id="pegawai-mkg-bln" class="lis-form-control font-mono" value="0">
                        </div>
                        <!-- TMT KGB Terakhir -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">TMT KGB Terakhir</label>
                            <input type="date" name="tmt_kgb_terakhir" id="pegawai-tmt-kgb" class="lis-form-control font-mono">
                        </div>
                        <!-- Kedudukan Pegawai -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-slate-600">Kedudukan Hukum</label>
                                <button type="button" onclick="openRefModal('kedudukan_hukum', 'Kedudukan Hukum Pegawai')" class="text-[10px] font-bold text-emerald-800 hover:text-emerald-950 bg-emerald-50 hover:bg-emerald-100 px-2 py-0.5 rounded border border-emerald-300 inline-flex items-center gap-1 transition-all" title="Kelola Master Kedudukan Hukum">
                                    <i data-lucide="plus" class="w-3 h-3"></i> <span>Kelola</span>
                                </button>
                            </div>
                            <select name="kedudukan_pegawai" id="pegawai-kedudukan" class="lis-form-control font-semibold text-slate-800">
                                <?php if (!empty($form_ref['kedudukan_hukum'])): ?>
                                    <?php foreach ($form_ref['kedudukan_hukum'] as $r): ?>
                                        <option value="<?= html_escape($r['kode'] ?: $r['nama']) ?>"><?= html_escape($r['nama']) ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="Aktif">Aktif Bekerja</option>
                                    <option value="Tugas Belajar">Tugas Belajar</option>
                                    <option value="Izin Belajar">Izin Belajar</option>
                                    <option value="Cuti Di Luar Tanggungan">Cuti Di Luar Tanggungan Negara (CLTN)</option>
                                    <option value="Pensiun">Pensiun</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: JABATAN & PENEMPATAN LABKESDA RIAU -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2 text-emerald-900 font-bold text-sm">
                            <span class="w-6 h-6 rounded-lg bg-emerald-700 text-amber-300 flex items-center justify-center text-xs font-black shadow-xs">3</span>
                            <span>Jabatan & Penempatan Seksi / Instalasi Laboratorium</span>
                        </div>
                        <span class="text-[10px] text-slate-400">UPT Labkesda Dinkes Riau</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Jenis Jabatan -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-slate-700">Jenis Jabatan ASN</label>
                                <button type="button" onclick="openRefModal('jenis_jabatan', 'Jenis Jabatan ASN')" class="text-[10px] font-bold text-emerald-800 hover:text-emerald-950 bg-emerald-50 hover:bg-emerald-100 px-2 py-0.5 rounded border border-emerald-300 inline-flex items-center gap-1 transition-all" title="Kelola Master Jenis Jabatan">
                                    <i data-lucide="plus" class="w-3 h-3"></i> <span>Kelola</span>
                                </button>
                            </div>
                            <select name="jenis_jabatan" id="pegawai-jenis-jabatan" class="lis-form-control font-semibold">
                                <?php if (!empty($form_ref['jenis_jabatan'])): ?>
                                    <?php foreach ($form_ref['jenis_jabatan'] as $r): ?>
                                        <option value="<?= html_escape($r['kode'] ?: $r['nama']) ?>"><?= html_escape($r['nama']) ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="Fungsional Tertentu (JFT)">Fungsional Tertentu (JFT Tenaga Kesehatan)</option>
                                    <option value="Struktural">Struktural / Manajerial Lab</option>
                                    <option value="Pelaksana (JFU)">Pelaksana / Fungsional Umum (JFU)</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Nama Jabatan -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nomenklatur Jabatan <span class="text-rose-500">*</span></label>
                            <input type="text" name="nama_jabatan" id="pegawai-jabatan" oninput="updatePreviewCard()" class="lis-form-control font-bold text-slate-900" placeholder="e.g. Pranata Laboratorium Kesehatan Ahli Muda" required>
                        </div>

                        <!-- Jenjang Jabatan -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Jenjang Fungsional / Eselon</label>
                            <input type="text" name="jenjang_jabatan" id="pegawai-jenjang" class="lis-form-control" placeholder="e.g. Pertama, Muda, Madya, Terampil, Eselon III/a">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-1">
                        <!-- Seksi / Instalasi Labkesda -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-slate-700">Seksi / Instalasi Penempatan <span class="text-rose-500">*</span></label>
                                <button type="button" onclick="openRefModal('seksi_ruangan', 'Seksi & Instalasi Penempatan')" class="text-[10px] font-bold text-emerald-800 hover:text-emerald-950 bg-emerald-50 hover:bg-emerald-100 px-2 py-0.5 rounded border border-emerald-300 inline-flex items-center gap-1 transition-all" title="Kelola Master Seksi / Instalasi">
                                    <i data-lucide="plus" class="w-3 h-3"></i> <span>Kelola</span>
                                </button>
                            </div>
                            <select name="seksi_ruangan" id="pegawai-seksi" onchange="updatePreviewCard()" class="lis-form-control font-bold text-emerald-950" required>
                                <?php if (!empty($form_ref['seksi_ruangan'])): ?>
                                    <?php foreach ($form_ref['seksi_ruangan'] as $r): ?>
                                        <?php $sKey = $r['kode'] ?: $r['nama']; ?>
                                        <?php $sVal = $r['keterangan'] ?: $r['nama']; ?>
                                        <option value="<?= html_escape($sKey) ?>"><?= html_escape($sVal) ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <?php foreach ($seksi_ref as $k => $v): ?>
                                        <option value="<?= html_escape($k) ?>"><?= html_escape($v) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Kelas Jabatan (TPP Grade) -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Kelas Jabatan (Grade TPP Pemprov)</label>
                            <input type="number" min="1" max="17" name="kelas_jabatan" id="pegawai-kelas-jabatan" class="lis-form-control font-mono font-bold" value="8" placeholder="Grade 1 - 17">
                        </div>

                        <!-- TMT Jabatan -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">TMT Jabatan</label>
                            <input type="date" name="tmt_jabatan" id="pegawai-tmt-jabatan" class="lis-form-control font-mono">
                        </div>
                    </div>
                </div>

                <!-- SECTION 4: KREDENSIALING KHUSUS TENAGA KESEHATAN (STR / SIP) -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2 text-emerald-900 font-bold text-sm">
                            <span class="w-6 h-6 rounded-lg bg-emerald-700 text-amber-300 flex items-center justify-center text-xs font-black shadow-xs">4</span>
                            <span>Kredensialing Tenaga Kesehatan Laboratorium (STR & SIP)</span>
                        </div>
                        <span class="text-[10px] font-semibold text-rose-700 bg-rose-50 px-2 py-0.5 rounded border border-rose-200">Legalitas Praktik Nakes</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Nomor STR -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nomor STR (Surat Tanda Registrasi)</label>
                            <input type="text" name="nomor_str" id="pegawai-str" class="lis-form-control font-mono font-semibold" placeholder="14.2.1.200.2.20.098765">
                        </div>

                        <!-- Tgl Terbit STR -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Terbit STR</label>
                            <input type="date" name="tgl_terbit_str" id="pegawai-tgl-str" class="lis-form-control font-mono">
                        </div>

                        <!-- Masa Berlaku STR -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Masa Berlaku STR</label>
                            <div class="flex items-center gap-2">
                                <input type="text" name="masa_berlaku_str" id="pegawai-berlaku-str" class="lis-form-control" value="Seumur Hidup" placeholder="Seumur Hidup / Tanggal">
                                <button type="button" onclick="document.getElementById('pegawai-berlaku-str').value = 'Seumur Hidup'" class="px-2.5 py-2 rounded-lg bg-emerald-100 hover:bg-emerald-200 text-emerald-900 text-xs font-bold flex-shrink-0" title="Set Seumur Hidup">
                                    Seumur Hidup
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                        <!-- Nomor SIP -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nomor SIP (Surat Izin Praktik)</label>
                            <input type="text" name="nomor_sip" id="pegawai-sip" class="lis-form-control font-mono" placeholder="503/DPMPTSP/SIP-ATLM/2023/...">
                        </div>

                        <!-- Masa Berlaku SIP -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Masa Berlaku SIP Hingga</label>
                            <input type="date" name="masa_berlaku_sip" id="pegawai-berlaku-sip" class="lis-form-control font-mono">
                        </div>
                    </div>

                    <!-- Sertifikasi Khusus Labkes -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Sertifikasi & Pelatihan Teknis Laboratorium</label>
                        <textarea name="sertifikasi_khusus" id="pegawai-sertifikasi" rows="2" class="lis-form-control text-xs" placeholder="Contoh: Flebotomi Klinis Terstandar, Biosafety Officer (BSL-2), Validasi Sysmex Hematology, ISO 15189, RT-PCR Biomolekuler"></textarea>
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Kompetensi penunjang akreditasi laboratorium kesehatan daerah</span>
                    </div>
                </div>

                <!-- SECTION 5: PENDIDIKAN FORMAL -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2 text-emerald-900 font-bold text-sm">
                            <span class="w-6 h-6 rounded-lg bg-emerald-700 text-amber-300 flex items-center justify-center text-xs font-black shadow-xs">5</span>
                            <span>Pendidikan Formal Terakhir & Legalitas Ijazah</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Jenjang Pendidikan -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tingkat Pendidikan</label>
                            <select name="tingkat_pendidikan" id="pegawai-pendidikan" class="lis-form-control font-semibold">
                                <option value="D-III">D-III (Ahli Madya)</option>
                                <option value="D-IV">D-IV / Sarjana Terapan</option>
                                <option value="S-1">S-1 (Sarjana)</option>
                                <option value="S-2">S-2 (Magister)</option>
                                <option value="Spesialis">Spesialis (Sp-1 / Sp.PK)</option>
                                <option value="S-3">S-3 (Doktor)</option>
                                <option value="Profesi">Pendidikan Profesi</option>
                                <option value="SMA/SMK">SMA / SMK Analis Kimia</option>
                            </select>
                        </div>

                        <!-- Jurusan / Program Studi -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jurusan / Program Studi</label>
                            <input type="text" name="jurusan" id="pegawai-jurusan" class="lis-form-control" placeholder="e.g. Analis Kesehatan / TLM, Kedokteran">
                        </div>

                        <!-- Nama Kampus -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Universitas / Institusi</label>
                            <input type="text" name="nama_institusi" id="pegawai-kampus" class="lis-form-control" placeholder="Poltekkes Kemenkes Riau, UNRI, dll.">
                        </div>

                        <!-- Tahun Lulus -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Tahun Kelulusan</label>
                            <input type="number" min="1970" max="2030" name="tahun_lulus" id="pegawai-tahun-lulus" class="lis-form-control font-mono" placeholder="2018">
                        </div>
                    </div>
                </div>

                <!-- SECTION 6: ADMINISTRASI, FINANSIAL & KONTAK -->
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2 text-emerald-900 font-bold text-sm">
                            <span class="w-6 h-6 rounded-lg bg-emerald-700 text-amber-300 flex items-center justify-center text-xs font-black shadow-xs">6</span>
                            <span>Administrasi Finansial & Kontak Komunikasi</span>
                        </div>
                        <span class="text-[10px] text-slate-400">Payroll & Asuransi ASN</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Nomor HP / WhatsApp -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">No. WhatsApp / HP</label>
                            <input type="text" name="telepon" id="pegawai-telepon" class="lis-form-control font-mono" placeholder="081275xxxxxx">
                        </div>

                        <!-- Email ASN / Pribadi -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Email Pegawai</label>
                            <input type="email" name="email" id="pegawai-email" class="lis-form-control" placeholder="nama@riau.go.id">
                        </div>

                        <!-- Bank Payroll -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-slate-600">Bank Rekening Payroll</label>
                                <button type="button" onclick="openRefModal('bank_payroll', 'Bank Rekening Payroll')" class="text-[10px] font-bold text-emerald-800 hover:text-emerald-950 bg-emerald-50 hover:bg-emerald-100 px-2 py-0.5 rounded border border-emerald-300 inline-flex items-center gap-1 transition-all" title="Kelola Master Bank Payroll">
                                    <i data-lucide="plus" class="w-3 h-3"></i> <span>Kelola</span>
                                </button>
                            </div>
                            <select name="bank_payroll" id="pegawai-bank" class="lis-form-control font-semibold text-emerald-950">
                                <?php if (!empty($form_ref['bank_payroll'])): ?>
                                    <?php foreach ($form_ref['bank_payroll'] as $r): ?>
                                        <option value="<?= html_escape($r['nama']) ?>"><?= html_escape($r['nama']) ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="Bank Riau Kepri Syariah">Bank Riau Kepri Syariah</option>
                                    <option value="Bank Mandiri">Bank Mandiri</option>
                                    <option value="Bank Rakyat Indonesia (BRI)">Bank Rakyat Indonesia (BRI)</option>
                                    <option value="Bank Negara Indonesia (BNI)">Bank Negara Indonesia (BNI)</option>
                                    <option value="Bank Syariah Indonesia (BSI)">Bank Syariah Indonesia (BSI)</option>
                                    <option value="Bank Central Asia (BCA)">Bank Central Asia (BCA)</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Nomor Rekening -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Nomor Rekening Bank</label>
                            <input type="text" name="nomor_rekening" id="pegawai-rekening" class="lis-form-control font-mono" placeholder="101-08-xxxxx">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-1">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">NPWP</label>
                            <input type="text" name="npwp" id="pegawai-npwp" class="lis-form-control font-mono" placeholder="08.123.456.7-...">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">No. BPJS Kesehatan</label>
                            <input type="text" name="no_bpjs_kesehatan" id="pegawai-bpjs" class="lis-form-control font-mono" placeholder="0001234567890">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">No. TASPEN</label>
                            <input type="text" name="no_taspen" id="pegawai-taspen" class="lis-form-control font-mono" placeholder="19850418...">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">No. KARPEG</label>
                            <input type="text" name="no_karpeg" id="pegawai-karpeg" class="lis-form-control font-mono" placeholder="K.0987654">
                        </div>
                    </div>

                    <!-- Alamat Lengkap -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Tempat Tinggal (Domisili Riau)</label>
                        <textarea name="alamat" id="pegawai-alamat" rows="2" class="lis-form-control text-xs" placeholder="Jl. Diponegoro No. 45, Pekanbaru, Riau"></textarea>
                    </div>
                </div>

                <!-- LIVE PREVIEW ID BADGE ASN -->
                <div class="bg-gradient-to-r from-emerald-900 to-slate-900 text-white p-4 rounded-2xl border border-amber-500/30 flex items-center justify-between shadow-md">
                    <div class="flex items-center gap-3.5">
                        <div id="preview-avatar" class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 text-slate-950 font-black text-lg flex items-center justify-center flex-shrink-0 shadow-md">
                            ASN
                        </div>
                        <div>
                            <span class="text-[10px] text-amber-300 font-bold tracking-wider uppercase block">Kartu Identitas Pegawai Labkesda</span>
                            <h4 id="preview-nama" class="text-sm font-bold text-white leading-tight">Nama Pegawai Lengkap</h4>
                            <div class="flex items-center gap-2 mt-0.5 text-xs text-slate-300">
                                <span id="preview-nip" class="font-mono text-amber-200 font-bold">NIP: -</span>
                                <span>&bull;</span>
                                <span id="preview-jabatan">Pranata Laboratorium</span>
                            </div>
                        </div>
                    </div>
                    <div class="hidden sm:block text-right">
                        <span id="preview-pangkat" class="inline-block px-2.5 py-1 rounded-lg bg-emerald-800 text-amber-300 text-xs font-bold border border-amber-400/30">
                            Pangkat / Golongan
                        </span>
                        <span id="preview-seksi" class="block text-[10px] text-slate-400 mt-1">UPT Labkesda Riau</span>
                    </div>
                </div>

                <!-- Footer Actions Modal -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                    <button type="button" onclick="closeModalPegawai()" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-100 text-xs font-semibold transition-colors">
                        Batal
                    </button>
                    <button type="submit" id="btn-save-pegawai" class="px-6 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-2 shadow-sm transition-all border border-emerald-600 shadow-emerald-900/10">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Simpan Data Pegawai</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 6. MODAL DOSSIER / DETAIL PROFIL PEGAWAI                                 -->
<!-- ========================================================================= -->
<div id="modal-detail" class="fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden my-auto">
        <!-- Header -->
        <div class="px-6 py-4 bg-gradient-to-r from-emerald-950 via-emerald-900 to-slate-900 text-white flex items-center justify-between border-b border-amber-500/30 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-400/20 text-amber-300 flex items-center justify-center border border-amber-400/40">
                    <i data-lucide="file-badge" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Biodata & Profil Lengkap ASN</h3>
                    <p class="text-xs text-amber-200/80 font-mono" id="detail-dossier-nip">NIP: -</p>
                </div>
            </div>
            <button type="button" onclick="closeModalDetail()" class="text-slate-400 hover:text-white p-2 rounded-xl hover:bg-white/10 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Detail Content -->
        <div class="p-6 overflow-y-auto space-y-6 flex-1 bg-slate-50/50" id="detail-content-body">
            <!-- Will be dynamically populated via JS -->
        </div>

        <div class="px-6 py-3.5 bg-white border-t border-slate-200 flex items-center justify-between flex-shrink-0">
            <span class="text-xs text-slate-400">UPT Laboratorium Kesehatan Provinsi Riau &bull; Sistem Terpadu SIMRS</span>
            <button type="button" onclick="closeModalDetail()" class="px-5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                Tutup Profil
            </button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 6B. MODAL KELOLA MASTER REFERENSI PEGAWAI (DINAMIS 6 KATEGORI)          -->
<!-- ========================================================================= -->
<div id="modal-ref-backdrop" class="fixed inset-0 bg-slate-950/75 backdrop-blur-xs z-50 hidden transition-opacity"></div>

<div id="modal-referensi-pegawai" class="fixed inset-0 z-50 hidden items-center justify-center p-2 sm:p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-4xl max-h-[92vh] flex flex-col overflow-hidden my-auto animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Header -->
        <div class="px-6 py-4 bg-gradient-to-r from-emerald-950 via-emerald-900 to-slate-900 text-white flex items-center justify-between border-b border-amber-500/30 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-400/20 text-amber-300 flex items-center justify-center border border-amber-400/40">
                    <i data-lucide="sliders-horizontal" class="w-5 h-5 text-amber-300"></i>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-white flex items-center gap-2">
                        <span>Kelola Master Referensi Pegawai</span>
                        <span id="badge-ref-active-category" class="text-[11px] font-bold bg-amber-400 text-slate-950 px-2.5 py-0.5 rounded-full">Golongan Ruang</span>
                    </h3>
                    <p class="text-xs text-amber-200/80 font-medium">Tambah, edit, dan hapus data master referensi secara dinamis real-time</p>
                </div>
            </div>

            <button type="button" onclick="closeRefModal()" class="text-slate-400 hover:text-white p-2 rounded-xl hover:bg-white/10 transition-colors focus:outline-none">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- 6 Category Tabs Selector (Horizontal scrollable) -->
        <div class="px-6 pt-3 pb-2 bg-slate-100 border-b border-slate-200 flex-shrink-0 overflow-x-auto">
            <div class="flex items-center gap-1.5 min-w-max">
                <button type="button" onclick="switchRefTab('golongan_ruang')" id="tab-ref-golongan_ruang" class="tab-ref-btn px-3 py-1.5 rounded-xl text-xs font-bold transition-all bg-emerald-800 text-amber-300 shadow-xs border border-amber-400/40 flex items-center gap-1.5">
                    <i data-lucide="award" class="w-3.5 h-3.5"></i>
                    <span>Golongan Ruang</span>
                </button>
                <button type="button" onclick="switchRefTab('kedudukan_hukum')" id="tab-ref-kedudukan_hukum" class="tab-ref-btn px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-white transition-all flex items-center gap-1.5">
                    <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                    <span>Kedudukan Hukum</span>
                </button>
                <button type="button" onclick="switchRefTab('jenis_jabatan')" id="tab-ref-jenis_jabatan" class="tab-ref-btn px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-white transition-all flex items-center gap-1.5">
                    <i data-lucide="briefcase" class="w-3.5 h-3.5"></i>
                    <span>Jenis Jabatan ASN</span>
                </button>
                <button type="button" onclick="switchRefTab('seksi_ruangan')" id="tab-ref-seksi_ruangan" class="tab-ref-btn px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-white transition-all flex items-center gap-1.5">
                    <i data-lucide="building-2" class="w-3.5 h-3.5"></i>
                    <span>Seksi / Instalasi</span>
                </button>
                <button type="button" onclick="switchRefTab('bank_payroll')" id="tab-ref-bank_payroll" class="tab-ref-btn px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-white transition-all flex items-center gap-1.5">
                    <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
                    <span>Bank Payroll</span>
                </button>
                <button type="button" onclick="switchRefTab('status_pegawai')" id="tab-ref-status_pegawai" class="tab-ref-btn px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-white transition-all flex items-center gap-1.5">
                    <i data-lucide="badge-check" class="w-3.5 h-3.5"></i>
                    <span>Status Kepegawaian</span>
                </button>
            </div>
        </div>

        <!-- Body Scrollable -->
        <div class="p-6 overflow-y-auto space-y-5 flex-1 bg-slate-50/70">
            
            <!-- Form Tambah / Edit Box -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <div class="flex items-center gap-2 text-emerald-900 font-bold text-xs sm:text-sm">
                        <i data-lucide="edit-3" class="w-4 h-4 text-emerald-700"></i>
                        <span id="form-ref-title">Form Tambah Referensi Baru</span>
                    </div>
                    <button type="button" id="btn-cancel-ref-edit" onclick="resetRefForm()" class="hidden text-xs text-slate-500 hover:text-rose-600 font-semibold items-center gap-1">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> <span>Batal Edit</span>
                    </button>
                </div>

                <form id="form-referensi" onsubmit="saveReferensi(event)" class="space-y-3">
                    <input type="hidden" name="id" id="ref-id">
                    <input type="hidden" name="kategori" id="ref-kategori" value="golongan_ruang">

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                        <!-- Kode / Singkatan -->
                        <div class="md:col-span-3">
                            <div class="flex items-center justify-between mb-1">
                                <label id="label-ref-kode" class="block text-xs font-bold text-slate-700">Kode / Singkatan</label>
                                <span id="badge-auto-code" class="text-[10px] font-bold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-300 inline-flex items-center gap-1 transition-all">
                                    <i data-lucide="sparkles" class="w-3 h-3 text-amber-500"></i>
                                    <span id="auto-code-text">✨ Otomatis</span>
                                </span>
                            </div>
                            <div class="relative">
                                <input type="text" name="kode" id="ref-kode" oninput="onUserManualCodeEdit()" class="lis-form-control font-mono font-bold uppercase pr-8" placeholder="e.g. IV/a, PNS, BRKS">
                                <button type="button" onclick="regenerateRefCode()" title="Generate Ulang Kode Otomatis dari Program" class="absolute right-2 top-2.5 text-slate-400 hover:text-emerald-700 p-0.5 rounded transition-colors focus:outline-none">
                                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-1">Cukup buat 1x, selebihnya program mengikuti.</p>
                        </div>

                        <!-- Nama / Nomenklatur -->
                        <div class="md:col-span-4">
                            <label id="label-ref-nama" class="block text-xs font-bold text-slate-700 mb-1">Nama Referensi <span class="text-rose-500">*</span></label>
                            <input type="text" name="nama" id="ref-nama" oninput="onRefNameInput(this.value)" class="lis-form-control font-bold text-slate-900" placeholder="e.g. Pembina" required>
                            <p class="text-[10px] text-slate-400 mt-1">Ketik nama, kode & singkatan tersinkron otomatis.</p>
                        </div>

                        <!-- Keterangan / Deskripsi -->
                        <div class="md:col-span-3">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Keterangan / Deskripsi</label>
                            <input type="text" name="keterangan" id="ref-keterangan" class="lis-form-control" placeholder="Deskripsi atau catatan">
                        </div>

                        <!-- Sort Order -->
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">No. Urut</label>
                            <input type="number" min="0" max="999" name="sort_order" id="ref-sort-order" class="lis-form-control font-mono font-bold text-slate-800" value="1">
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <span id="ref-help-text" class="text-[11px] text-slate-400">Data akan langsung tersinkron ke formulir pendaftaran pegawai.</span>
                        <div class="flex items-center gap-2">
                            <button type="button" id="btn-reset-ref" onclick="resetRefForm()" class="px-4 py-2 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-600 text-xs font-semibold">
                                Reset
                            </button>
                            <button type="submit" id="btn-save-ref" class="px-5 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-1.5 shadow-sm transition-all border border-emerald-600">
                                <i data-lucide="check" class="w-4 h-4"></i>
                                <span id="btn-save-ref-text">Simpan Data Referensi</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Table Daftar Data Referensi -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-3.5 sm:p-4 border-b border-slate-100 flex items-center justify-between gap-3 flex-wrap">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wide">
                            Daftar Referensi: <span id="label-table-kategori" class="text-emerald-800 font-black">Golongan Ruang</span>
                        </h4>
                        <span id="ref-count-badge" class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[10px] font-bold font-mono">0 item</span>
                    </div>

                    <!-- Search Input with Autocomplete -->
                    <div class="relative w-full sm:w-64" id="ref-search-wrapper">
                        <input type="text" id="ref-search-input" oninput="filterRefTable(this.value)" placeholder="Cari nama atau kode..." class="w-full pl-8 pr-7 py-1.5 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-600">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5"></i>
                        <button type="button" id="btn-clear-ref-search" onclick="clearRefSearch()" class="hidden absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 p-0.5" title="Hapus pencarian">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>
                        <!-- Autocomplete Suggestions -->
                        <div id="ref-search-suggestions" class="hidden absolute z-30 left-0 right-0 mt-1 bg-white rounded-xl shadow-2xl border border-emerald-200 overflow-hidden divide-y divide-slate-100 max-h-56 overflow-y-auto"></div>
                    </div>
                </div>

                <div class="overflow-x-auto max-h-72">
                    <table class="w-full text-xs text-left" id="table-ref-items">
                        <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] tracking-wider sticky top-0 border-b border-slate-200">
                            <tr>
                                <th class="px-3 py-2.5 text-center w-12">No</th>
                                <th class="px-3 py-2.5 w-28">Kode / Nilai</th>
                                <th class="px-3 py-2.5">Nama Referensi</th>
                                <th class="px-3 py-2.5">Keterangan</th>
                                <th class="px-3 py-2.5 text-center w-16">Urut</th>
                                <th class="px-3 py-2.5 text-center w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="ref-table-body" class="divide-y divide-slate-100">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div class="px-6 py-3 bg-white border-t border-slate-200 flex items-center justify-between flex-shrink-0">
            <span class="text-xs text-slate-400">Setiap perubahan referensi tersinkron otomatis ke formulir tanpa reload.</span>
            <button type="button" onclick="closeRefModal()" class="px-5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                Tutup Jendela
            </button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 7. JAVASCRIPT LOGIC (FULL AJAX CRUD, SMART NIP, AUTO SYNC)               -->
<!-- ========================================================================= -->
<script>
let tablePegawai = null;
let currentFilterStatus = 'all';

document.addEventListener('DOMContentLoaded', function() {
    initTable();
});

function initTable() {
    tablePegawai = $('#table-pegawai').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: '<?= site_url('pegawai/fetch') ?>',
            type: 'GET',
            data: function(d) {
                d.status_pegawai = currentFilterStatus;
                d.seksi_ruangan = $('#filter-seksi').val() || 'all';
            }
        },
        columns: [
            { data: 'no_urut', className: 'text-center font-bold text-slate-400 py-3' },
            {
                data: 'nip',
                className: 'py-3',
                render: function(data, type, row) {
                    const formatted = row.nip_formatted || data;
                    const nik = row.nik ? '<div class="text-[10px] text-slate-400 font-mono mt-0.5">NIK: ' + row.nik + '</div>' : '';
                    return '<div class="font-mono font-bold text-slate-900 text-xs">' + formatted + '</div>' + nik;
                }
            },
            {
                data: 'nama_bergelar',
                className: 'py-3 font-medium',
                render: function(data, type, row) {
                    const jkBadge = row.jenis_kelamin === 'L' 
                        ? '<span class="w-4 h-4 rounded-full bg-blue-100 text-blue-700 text-[10px] font-bold inline-flex items-center justify-center">L</span>'
                        : '<span class="w-4 h-4 rounded-full bg-rose-100 text-rose-700 text-[10px] font-bold inline-flex items-center justify-center">P</span>';
                    const telp = row.telepon ? '<div class="text-[10px] text-slate-400 mt-0.5 flex items-center gap-1"><i data-lucide="phone" class="w-3 h-3 text-slate-400"></i> ' + row.telepon + '</div>' : '';
                    return '<div class="flex items-center gap-1.5"><span class="font-bold text-slate-900 text-xs">' + data + '</span>' + jkBadge + '</div>' + telp;
                }
            },
            {
                data: 'kode_golongan',
                className: 'py-3',
                render: function(data, type, row) {
                    if (!data) return '<span class="text-slate-400">-</span>';
                    const pangkat = row.nama_pangkat ? '<div class="text-[10px] text-slate-500 font-medium">' + row.nama_pangkat + '</div>' : '';
                    return '<span class="inline-block px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200 font-mono font-bold text-[11px]">' + data + '</span>' + pangkat;
                }
            },
            {
                data: 'nama_jabatan',
                className: 'py-3',
                render: function(data, type, row) {
                    const seksi = row.seksi_ruangan ? '<div class="text-[10px] text-amber-800 font-semibold mt-0.5 flex items-center gap-1"><i data-lucide="map-pin" class="w-2.5 h-2.5"></i> ' + row.seksi_ruangan + '</div>' : '';
                    return '<div class="font-semibold text-slate-800 text-xs">' + data + '</div>' + seksi;
                }
            },
            {
                data: 'status_pegawai',
                className: 'text-center py-3',
                render: function(data) {
                    let cls = 'bg-slate-100 text-slate-700 border-slate-200';
                    if (data === 'PNS') cls = 'bg-emerald-100 text-emerald-900 border-emerald-300 font-bold';
                    else if (data === 'CPNS') cls = 'bg-amber-100 text-amber-900 border-amber-300 font-bold';
                    else if (data === 'PPPK') cls = 'bg-teal-100 text-teal-900 border-teal-300 font-bold';
                    else if (data && data.includes('Non-ASN')) cls = 'bg-slate-100 text-slate-700 border-slate-300';
                    return '<span class="px-2.5 py-1 rounded-full text-[10px] border ' + cls + '">' + (data || '-') + '</span>';
                }
            },
            {
                data: 'nomor_str',
                className: 'py-3',
                render: function(data, type, row) {
                    if (!data) return '<span class="text-slate-400 text-[10px]">Non-Nakes / Belum STR</span>';
                    const berlaku = row.masa_berlaku_str ? '<div class="text-[10px] text-emerald-700 font-bold">' + row.masa_berlaku_str + '</div>' : '';
                    return '<div class="font-mono text-[11px] text-slate-700 font-semibold">' + data + '</div>' + berlaku;
                }
            },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                className: 'text-center py-3',
                render: function(data, type, row) {
                    let html = '<div class="flex items-center justify-center gap-1">';
                    html += '<button type="button" onclick="viewDetail(' + data + ')" class="p-1.5 rounded-lg bg-emerald-50 text-emerald-800 hover:bg-emerald-100 transition-colors border border-emerald-200" title="Lihat Dossier ASN"><i data-lucide="eye" class="w-3.5 h-3.5"></i></button>';

                    <?php if ($can_edit): ?>
                    html += '<button type="button" onclick="openModalPegawai(\'edit\', ' + data + ')" class="p-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors border border-blue-200" title="Edit Pegawai"><i data-lucide="edit-3" class="w-3.5 h-3.5"></i></button>';
                    <?php endif; ?>

                    <?php if ($can_delete): ?>
                    html += '<button type="button" onclick="deletePegawai(' + data + ', \'' + (row.nama_lengkap || '').replace(/'/g, "\\'") + '\')" class="p-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 transition-colors border border-rose-200" title="Hapus"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>';
                    <?php endif; ?>

                    html += '</div>';
                    return html;
                }
            }
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari NIP, Nama, Jabatan, STR...",
            lengthMenu: "Tampil _MENU_",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ pegawai",
            infoEmpty: "Data tidak ditemukan",
            infoFiltered: "(disaring dari _MAX_ total)",
            zeroRecords: "Tidak ada data pegawai yang sesuai",
            paginate: {
                previous: '<i data-lucide="chevron-left" class="w-4 h-4"></i>',
                next: '<i data-lucide="chevron-right" class="w-4 h-4"></i>'
            }
        },
        drawCallback: function() {
            if (window.lucide) lucide.createIcons();
        }
    });
}

function reloadTable() {
    if (tablePegawai) tablePegawai.ajax.reload();
    refreshSummary();
}

function filterStatus(status) {
    currentFilterStatus = status;
    document.querySelectorAll('.filter-tab-btn').forEach(btn => {
        btn.classList.remove('active-tab', 'bg-emerald-700', 'text-amber-300', 'font-bold');
        btn.classList.add('text-slate-600', 'hover:bg-slate-100');
    });

    let tabId = 'tab-status-all';
    if (status === 'PNS') tabId = 'tab-status-pns';
    else if (status === 'CPNS') tabId = 'tab-status-cpns';
    else if (status === 'PPPK') tabId = 'tab-status-pppk';
    else if (status && status.includes('Non-ASN')) tabId = 'tab-status-nonasn';

    const activeEl = document.getElementById(tabId);
    if (activeEl) {
        activeEl.classList.remove('text-slate-600', 'hover:bg-slate-100');
        activeEl.classList.add('active-tab', 'bg-emerald-700', 'text-amber-300', 'font-bold');
    }

    reloadTable();
}

function refreshSummary() {
    fetch('<?= site_url('pegawai/summary') ?>')
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                const d = res.data;
                document.getElementById('stat-total').textContent = d.total;
                document.getElementById('stat-pns').textContent = d.asn_total;
                document.getElementById('stat-pppk').textContent = d.pppk;
                document.getElementById('stat-str').textContent = d.str_aktif;
            }
        })
        .catch(err => console.error(err));
}

// Polyfill toastr agar selalu kompatibel dengan LIS.toast
window.toastr = window.toastr || {
    success: function(m) { if (window.LIS) LIS.toast('success', m); else alert(m); },
    error: function(m) { if (window.LIS) LIS.toast('error', m); else alert(m); },
    warning: function(m) { if (window.LIS) LIS.toast('warning', m); else alert(m); },
    info: function(m) { if (window.LIS) LIS.toast('info', m); else alert(m); }
};

// =========================================================================
// SMART PARSER: NIP 18 DIGIT BKN & KODE PEGAWAI NON-ASN
// =========================================================================
function parseNip(nip) {
    const clean = (nip || '').replace(/[^0-9]/g, '');
    const hintEl = document.getElementById('nip-parser-hint');
    if (!hintEl) return;

    if (clean.length === 18) {
        if (clean.startsWith('99') || clean.startsWith('98')) {
            const prefixLabel = clean.startsWith('99') ? 'Non-ASN / BLUD' : 'Honorer / Kontrak';
            hintEl.innerHTML = `<span class="inline-flex items-center gap-1 text-teal-800 bg-teal-50 border border-teal-300 px-2.5 py-0.5 rounded-lg font-bold"><i data-lucide="check-circle" class="w-3.5 h-3.5 text-teal-600"></i> Kode Terstandar ${prefixLabel} Labkesda (18 Digit Resmi)</span>`;
        } else {
            const yyyy = clean.substring(0, 4);
            const mm = clean.substring(4, 6);
            const dd = clean.substring(6, 8);
            const cpnsYyyy = clean.substring(8, 12);
            const cpnsMm = clean.substring(12, 14);
            const genderDigit = clean.substring(14, 15);

            const birthDateStr = `${yyyy}-${mm}-${dd}`;
            const cpnsDateStr = `${cpnsYyyy}-${cpnsMm}-01`;

            // Auto set Tanggal Lahir jika belum diisi manual
            const tglLahirInput = document.getElementById('pegawai-tanggal-lahir');
            if (tglLahirInput && !tglLahirInput.value) tglLahirInput.value = birthDateStr;

            // Auto set TMT CPNS jika belum diisi manual
            const tmtCpnsInput = document.getElementById('pegawai-tmt-cpns');
            if (tmtCpnsInput && !tmtCpnsInput.value) tmtCpnsInput.value = cpnsDateStr;

            // Auto set Gender (1: Laki-laki, 2: Perempuan)
            const jkSelect = document.getElementById('pegawai-jk');
            if (jkSelect) {
                jkSelect.value = (genderDigit === '1') ? 'L' : 'P';
            }

            const genderName = (genderDigit === '1') ? 'Laki-laki' : 'Perempuan';
            hintEl.innerHTML = `<span class="inline-flex items-center gap-1 text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded font-bold"><i data-lucide="check-circle" class="w-3 h-3 text-emerald-600"></i> NIP BKN Valid &bull; Lahir: ${dd}/${mm}/${yyyy} &bull; TMT CPNS: ${cpnsMm}/${cpnsYyyy} &bull; JK: ${genderName}</span>`;
        }
    } else {
        hintEl.innerHTML = `<span class="text-slate-400">18 digit standar BKN: ${clean.length}/18 digit dimasukkan</span>`;
    }

    updatePreviewCard();
    if (window.lucide) lucide.createIcons();
}

/**
 * Generate NIP / Kode Pegawai Otomatis untuk Non-ASN / Honorer / Kontrak
 */
function generateNipOtomatis() {
    const status = document.getElementById('pegawai-status')?.value || 'Non-ASN / BLUD';
    const tglLahir = document.getElementById('pegawai-tanggal-lahir')?.value || '';
    const jk = document.getElementById('pegawai-jk')?.value || 'L';

    fetch(`<?= site_url('pegawai/generate_pegawai_nip') ?>?status=${encodeURIComponent(status)}&tanggal_lahir=${encodeURIComponent(tglLahir)}&jenis_kelamin=${encodeURIComponent(jk)}`)
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                const nipInput = document.getElementById('pegawai-nip');
                if (nipInput) {
                    nipInput.value = res.nip;
                    parseNip(res.nip);
                    updatePreviewCard();
                }
                LIS.toast('success', `Kode/NIP otomatis dibuat: ${res.nip_formatted}`);
            }
        })
        .catch(err => {
            console.error('Error generating NIP:', err);
            LIS.toast('error', 'Gagal membuat NIP otomatis.');
        });
}

function onStatusPegawaiChange(status) {
    const nipInput = document.getElementById('pegawai-nip');
    const nipVal = nipInput ? nipInput.value.trim() : '';
    // Jika NIP kosong atau sudah merupakan format kode 99/98 sebelumnya, buat otomatis
    if (!nipVal && status && (status.includes('Non-ASN') || status.includes('Honorer') || status.includes('Kontrak') || status.includes('BLUD'))) {
        generateNipOtomatis();
    }
}

// Auto sync Nama Pangkat saat Golongan dipilih
function syncGolonganPangkat(gol) {
    const select = document.getElementById('pegawai-golongan');
    const selectedOpt = select.options[select.selectedIndex];
    const pangkat = selectedOpt ? selectedOpt.getAttribute('data-pangkat') : '';
    const pangkatInput = document.getElementById('pegawai-pangkat');

    if (pangkat && pangkatInput) {
        pangkatInput.value = pangkat;
    }
    updatePreviewCard();
}

// Update Live Preview ID Badge
function updatePreviewCard() {
    const gelarDepan = document.getElementById('pegawai-gelar-depan').value.trim();
    const nama = document.getElementById('pegawai-nama').value.trim() || 'Nama Pegawai Lengkap';
    const gelarBelakang = document.getElementById('pegawai-gelar-belakang').value.trim();

    const fullName = (gelarDepan ? gelarDepan + ' ' : '') + nama + (gelarBelakang ? ', ' + gelarBelakang : '');
    document.getElementById('preview-nama').textContent = fullName;

    const nip = document.getElementById('pegawai-nip').value.trim();
    document.getElementById('preview-nip').textContent = 'NIP: ' + (nip || '-');

    const jabatan = document.getElementById('pegawai-jabatan').value.trim() || 'Pranata Laboratorium';
    document.getElementById('preview-jabatan').textContent = jabatan;

    const gol = document.getElementById('pegawai-golongan').value.trim();
    const pangkat = document.getElementById('pegawai-pangkat').value.trim();
    document.getElementById('preview-pangkat').textContent = (pangkat ? pangkat : 'Pangkat') + (gol ? ' (' + gol + ')' : '');

    const seksi = document.getElementById('pegawai-seksi').value.trim();
    document.getElementById('preview-seksi').textContent = seksi || 'UPT Labkesda Riau';

    // Avatar initials
    const words = nama.split(' ');
    let initials = 'ASN';
    if (words.length >= 2) {
        initials = (words[0][0] + words[1][0]).toUpperCase();
    } else if (words.length === 1 && words[0].length > 0) {
        initials = words[0].substring(0, 2).toUpperCase();
    }
    document.getElementById('preview-avatar').textContent = initials;
}

// =========================================================================
// MODAL CONTROLS: CREATE & EDIT
// =========================================================================
function openModalPegawai(mode, id = null) {
    const form = document.getElementById('form-pegawai');
    form.reset();
    document.getElementById('pegawai-id').value = '';
    document.getElementById('pegawai-berlaku-str').value = 'Seumur Hidup';
    document.getElementById('modal-pegawai-title').innerHTML = mode === 'create' 
        ? '<i data-lucide="user-plus" class="w-5 h-5 text-amber-300"></i><span>Registrasi Data Pegawai Baru</span>'
        : '<i data-lucide="edit-3" class="w-5 h-5 text-amber-300"></i><span>Edit Data Pegawai ASN / Nakes</span>';

    if (mode === 'edit' && id) {
        fetch('<?= site_url('pegawai/get') ?>/' + id)
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    const d = res.data;
                    document.getElementById('pegawai-id').value = d.id;
                    document.getElementById('pegawai-nip').value = d.nip || '';
                    document.getElementById('pegawai-nik').value = d.nik || '';
                    document.getElementById('pegawai-status').value = d.status_pegawai || 'PNS';
                    document.getElementById('pegawai-gelar-depan').value = d.gelar_depan || '';
                    document.getElementById('pegawai-nama').value = d.nama_lengkap || '';
                    document.getElementById('pegawai-gelar-belakang').value = d.gelar_belakang || '';
                    document.getElementById('pegawai-tempat-lahir').value = d.tempat_lahir || '';
                    document.getElementById('pegawai-tanggal-lahir').value = d.tanggal_lahir || '';
                    document.getElementById('pegawai-jk').value = d.jenis_kelamin || 'L';
                    document.getElementById('pegawai-agama').value = d.agama || 'Islam';
                    document.getElementById('pegawai-goldar').value = d.golongan_darah || '';

                    document.getElementById('pegawai-golongan').value = d.kode_golongan || '';
                    document.getElementById('pegawai-pangkat').value = d.nama_pangkat || '';
                    document.getElementById('pegawai-tmt-golongan').value = d.tmt_golongan || '';
                    document.getElementById('pegawai-sk-kp').value = d.nomor_sk_kp || '';
                    document.getElementById('pegawai-tmt-cpns').value = d.tmt_cpns || '';
                    document.getElementById('pegawai-sk-cpns').value = d.nomor_sk_cpns || '';
                    document.getElementById('pegawai-tmt-pns').value = d.tmt_pns || '';
                    document.getElementById('pegawai-sk-pns').value = d.nomor_sk_pns || '';
                    document.getElementById('pegawai-mkg-thn').value = d.mkg_tahun || 0;
                    document.getElementById('pegawai-mkg-bln').value = d.mkg_bulan || 0;
                    document.getElementById('pegawai-tmt-kgb').value = d.tmt_kgb_terakhir || '';
                    document.getElementById('pegawai-kedudukan').value = d.kedudukan_pegawai || 'Aktif';

                    document.getElementById('pegawai-jenis-jabatan').value = d.jenis_jabatan || 'Fungsional Tertentu (JFT)';
                    document.getElementById('pegawai-jabatan').value = d.nama_jabatan || '';
                    document.getElementById('pegawai-jenjang').value = d.jenjang_jabatan || '';
                    document.getElementById('pegawai-seksi').value = d.seksi_ruangan || 'Instalasi Laboratorium Klinik';
                    document.getElementById('pegawai-kelas-jabatan').value = d.kelas_jabatan || 8;
                    document.getElementById('pegawai-tmt-jabatan').value = d.tmt_jabatan || '';

                    document.getElementById('pegawai-str').value = d.nomor_str || '';
                    document.getElementById('pegawai-tgl-str').value = d.tgl_terbit_str || '';
                    document.getElementById('pegawai-berlaku-str').value = d.masa_berlaku_str || 'Seumur Hidup';
                    document.getElementById('pegawai-sip').value = d.nomor_sip || '';
                    document.getElementById('pegawai-berlaku-sip').value = d.masa_berlaku_sip || '';
                    document.getElementById('pegawai-sertifikasi').value = d.sertifikasi_khusus || '';

                    document.getElementById('pegawai-pendidikan').value = d.tingkat_pendidikan || 'D-III';
                    document.getElementById('pegawai-jurusan').value = d.jurusan || '';
                    document.getElementById('pegawai-kampus').value = d.nama_institusi || '';
                    document.getElementById('pegawai-tahun-lulus').value = d.tahun_lulus || '';

                    document.getElementById('pegawai-telepon').value = d.telepon || '';
                    document.getElementById('pegawai-email').value = d.email || '';
                    document.getElementById('pegawai-bank').value = d.bank_payroll || 'Bank Riau Kepri Syariah';
                    document.getElementById('pegawai-rekening').value = d.nomor_rekening || '';
                    document.getElementById('pegawai-npwp').value = d.npwp || '';
                    document.getElementById('pegawai-bpjs').value = d.no_bpjs_kesehatan || '';
                    document.getElementById('pegawai-taspen').value = d.no_taspen || '';
                    document.getElementById('pegawai-karpeg').value = d.no_karpeg || '';
                    document.getElementById('pegawai-alamat').value = d.alamat || '';

                    parseNip(d.nip || '');
                    updatePreviewCard();
                }
            });
    } else {
        parseNip('');
        updatePreviewCard();
    }

    document.getElementById('modal-pegawai').classList.remove('hidden');
    document.getElementById('modal-pegawai').classList.add('flex');
    document.getElementById('modal-backdrop').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    if (window.lucide) lucide.createIcons();
}

function closeModalPegawai() {
    document.getElementById('modal-pegawai').classList.remove('flex');
    document.getElementById('modal-pegawai').classList.add('hidden');
    document.getElementById('modal-backdrop').classList.add('hidden');
    document.body.style.overflow = '';
}

function savePegawai(e) {
    e.preventDefault();

    const nip = document.getElementById('pegawai-nip')?.value.trim();
    const nama = document.getElementById('pegawai-nama')?.value.trim();
    const jabatan = document.getElementById('pegawai-jabatan')?.value.trim();

    if (!nip) {
        LIS.toast('warning', 'Nomor Induk Pegawai (NIP) wajib diisi.');
        document.getElementById('pegawai-nip')?.focus();
        return;
    }
    if (!nama) {
        LIS.toast('warning', 'Nama Lengkap Pegawai wajib diisi.');
        document.getElementById('pegawai-nama')?.focus();
        return;
    }
    if (!jabatan) {
        LIS.toast('warning', 'Nama Jabatan Pegawai wajib diisi.');
        document.getElementById('pegawai-jabatan')?.focus();
        return;
    }

    const form = document.getElementById('form-pegawai');
    const fd = new FormData(form);

    const btn = document.getElementById('btn-save-pegawai');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i><span>Menyimpan...</span>';
    if (window.lucide) lucide.createIcons();

    fetch('<?= site_url('pegawai/save') ?>', {
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
            closeModalPegawai();
            reloadTable();
        } else {
            LIS.alert('Gagal Menyimpan Data Pegawai', res.message, 'warning');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        if (window.lucide) lucide.createIcons();
        console.error(err);
        LIS.alert('Kesalahan Server', 'Terjadi gangguan saat memproses data kepegawaian.', 'error');
    });
}

function deletePegawai(id, name) {
    LIS.confirm(
        'Hapus Data Pegawai',
        `Yakin ingin menghapus data pegawai <strong>${name}</strong> dari basis data UPT Labkesda?<br><span class="text-xs text-rose-600">Catatan: Tindakan ini permanen.</span>`,
        'Ya, Hapus Pegawai'
    ).then(res => {
        if (res.isConfirmed) {
            fetch('<?= site_url('pegawai/delete') ?>/' + id, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    LIS.toast('success', data.message);
                    reloadTable();
                } else {
                    LIS.alert('Gagal Menghapus', data.message, 'error');
                }
            })
            .catch(err => {
                LIS.alert('Kesalahan Sistem', 'Tidak dapat menghapus data pegawai.', 'error');
            });
        }
    });
}

// =========================================================================
// DETAIL DOSSIER MODAL
// =========================================================================
function viewDetail(id) {
    fetch('<?= site_url('pegawai/get') ?>/' + id)
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                const d = res.data;
                document.getElementById('detail-dossier-nip').textContent = 'NIP: ' + (d.nip_formatted || d.nip);

                const html = `
                    <!-- Profil Banner -->
                    <div class="bg-gradient-to-r from-emerald-900 to-slate-900 text-white p-5 rounded-2xl border border-amber-500/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-emerald-700 via-teal-600 to-amber-500 text-amber-100 font-black text-2xl flex items-center justify-center border-2 border-amber-400/40 shadow-lg">
                                ${d.jenis_kelamin === 'L' ? '<i data-lucide="user" class="w-8 h-8"></i>' : '<i data-lucide="user" class="w-8 h-8"></i>'}
                            </div>
                            <div>
                                <span class="px-2.5 py-0.5 rounded-full bg-amber-400/20 text-amber-300 text-[10px] font-bold border border-amber-400/40 inline-block mb-1">
                                    ${d.status_pegawai} &bull; ${d.kedudukan_pegawai}
                                </span>
                                <h3 class="text-lg font-bold text-white">${d.nama_bergelar}</h3>
                                <p class="text-xs text-slate-300 mt-0.5">${d.nama_jabatan} &bull; ${d.seksi_ruangan}</p>
                            </div>
                        </div>

                        <div class="text-left sm:text-right">
                            <span class="inline-block px-3 py-1 rounded-xl bg-emerald-800 text-amber-200 text-xs font-bold border border-emerald-600">
                                ${d.nama_pangkat || '-'} (${d.kode_golongan || '-'})
                            </span>
                            <span class="block text-[11px] text-slate-400 font-mono mt-1">Kelas Jabatan: Grade ${d.kelas_jabatan || '-'}</span>
                        </div>
                    </div>

                    <!-- 3-Column Info Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Identitas & Demografi -->
                        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs space-y-2 text-xs">
                            <h4 class="font-bold text-slate-900 border-b border-slate-100 pb-2 flex items-center gap-2 text-emerald-800">
                                <i data-lucide="user-check" class="w-4 h-4"></i> Identitas Pegawai
                            </h4>
                            <div class="grid grid-cols-3 gap-1">
                                <span class="text-slate-500">NIP BKN:</span>
                                <span class="col-span-2 font-mono font-bold text-slate-900">${d.nip_formatted || d.nip}</span>
                            </div>
                            <div class="grid grid-cols-3 gap-1">
                                <span class="text-slate-500">NIK KTP:</span>
                                <span class="col-span-2 font-mono">${d.nik || '-'}</span>
                            </div>
                            <div class="grid grid-cols-3 gap-1">
                                <span class="text-slate-500">TTL:</span>
                                <span class="col-span-2">${d.tempat_lahir || '-'}, ${d.tanggal_lahir || '-'}</span>
                            </div>
                            <div class="grid grid-cols-3 gap-1">
                                <span class="text-slate-500">Jenis Kelamin:</span>
                                <span class="col-span-2">${d.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan'} (${d.agama || '-'})</span>
                            </div>
                            <div class="grid grid-cols-3 gap-1">
                                <span class="text-slate-500">Pendidikan:</span>
                                <span class="col-span-2 font-semibold text-slate-800">${d.tingkat_pendidikan || '-'} ${d.jurusan ? '&bull; ' + d.jurusan : ''} (${d.nama_institusi || '-'})</span>
                            </div>
                        </div>

                        <!-- Kredensial Medis & STR/SIP -->
                        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs space-y-2 text-xs">
                            <h4 class="font-bold text-slate-900 border-b border-slate-100 pb-2 flex items-center gap-2 text-rose-800">
                                <i data-lucide="award" class="w-4 h-4"></i> Kredensial STR & SIP Nakes
                            </h4>
                            <div class="grid grid-cols-3 gap-1">
                                <span class="text-slate-500">Nomor STR:</span>
                                <span class="col-span-2 font-mono font-bold text-rose-900">${d.nomor_str || 'Tidak Ada / Non-Nakes'}</span>
                            </div>
                            <div class="grid grid-cols-3 gap-1">
                                <span class="text-slate-500">Masa Berlaku STR:</span>
                                <span class="col-span-2 font-semibold text-emerald-800">${d.masa_berlaku_str || '-'}</span>
                            </div>
                            <div class="grid grid-cols-3 gap-1">
                                <span class="text-slate-500">Nomor SIP:</span>
                                <span class="col-span-2 font-mono">${d.nomor_sip || '-'}</span>
                            </div>
                            <div class="grid grid-cols-3 gap-1">
                                <span class="text-slate-500">Masa Berlaku SIP:</span>
                                <span class="col-span-2">${d.masa_berlaku_sip || '-'}</span>
                            </div>
                            <div class="grid grid-cols-3 gap-1">
                                <span class="text-slate-500">Sertifikasi:</span>
                                <span class="col-span-2 text-[11px] text-slate-600">${d.sertifikasi_khusus || '-'}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Kepangkatan & Administrasi Payroll -->
                    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs space-y-2 text-xs">
                        <h4 class="font-bold text-slate-900 border-b border-slate-100 pb-2 flex items-center gap-2 text-amber-800">
                            <i data-lucide="credit-card" class="w-4 h-4"></i> Finansial, Kontak & Administrasi
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <span class="text-slate-500 block">Bank Payroll & Rekening:</span>
                                <span class="font-bold text-slate-900">${d.bank_payroll || '-'}</span>
                                <span class="font-mono text-slate-700 block">${d.nomor_rekening || '-'}</span>
                            </div>
                            <div>
                                <span class="text-slate-500 block">No. HP / WhatsApp:</span>
                                <span class="font-bold text-slate-900 font-mono">${d.telepon || '-'}</span>
                                <span class="text-slate-500 block">${d.email || '-'}</span>
                            </div>
                            <div>
                                <span class="text-slate-500 block">Jaminan Sosial & Taspen:</span>
                                <span class="text-slate-700 block">BPJS: ${d.no_bpjs_kesehatan || '-'}</span>
                                <span class="text-slate-700 block">TASPEN: ${d.no_taspen || '-'}</span>
                            </div>
                        </div>
                        <div class="pt-2 border-t border-slate-100">
                            <span class="text-slate-500">Alamat Domisili: </span>
                            <span class="text-slate-800 font-medium">${d.alamat || '-'}</span>
                        </div>
                    </div>
                `;

                document.getElementById('detail-content-body').innerHTML = html;
                document.getElementById('modal-detail').classList.remove('hidden');
                document.getElementById('modal-detail').classList.add('flex');
                document.getElementById('modal-backdrop').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                if (window.lucide) lucide.createIcons();
            }
        });
}

function closeModalDetail() {
    document.getElementById('modal-detail').classList.remove('flex');
    document.getElementById('modal-detail').classList.add('hidden');
    document.getElementById('modal-backdrop').classList.add('hidden');
    document.body.style.overflow = '';
}

// =========================================================================
// 8. MASTER REFERENSI KEPEGAWAIAN (6 KATEGORI DINAMIS)
// =========================================================================
let currentRefCategory = 'golongan_ruang';
let rawCategoryItems = [];
let allReferencesCache = <?= json_encode($form_ref ?? []) ?>;
let isCodeManuallyEdited = false;
let refCodeDebounceTimer = null;

function onUserManualCodeEdit() {
    isCodeManuallyEdited = true;
    updateAutoCodeBadge(false);
}

function updateAutoCodeBadge(isAuto) {
    const badge = document.getElementById('badge-auto-code');
    const txt = document.getElementById('auto-code-text');
    if (!badge || !txt) return;

    if (isAuto) {
        badge.className = 'text-[10px] font-bold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-300 inline-flex items-center gap-1 transition-all';
        badge.innerHTML = '<i data-lucide="sparkles" class="w-3 h-3 text-amber-500"></i> <span id="auto-code-text">✨ Otomatis</span>';
    } else {
        badge.className = 'text-[10px] font-bold text-amber-800 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-300 inline-flex items-center gap-1 transition-all';
        badge.innerHTML = '<i data-lucide="check" class="w-3 h-3 text-emerald-600"></i> <span id="auto-code-text">Kustom (1x Dibuat)</span>';
    }
    if (window.lucide) lucide.createIcons();
}

function regenerateRefCode() {
    isCodeManuallyEdited = false;
    const namaVal = document.getElementById('ref-nama')?.value || '';
    fetchAutoRefCode(currentRefCategory, namaVal);
}

function onRefNameInput(namaVal) {
    if (isCodeManuallyEdited) return; // Hormati kode jika user pernah mengubahnya kustom

    clearTimeout(refCodeDebounceTimer);
    refCodeDebounceTimer = setTimeout(() => {
        fetchAutoRefCode(currentRefCategory, namaVal);
    }, 200);
}

function fetchAutoRefCode(kategori, nama = '') {
    const editId = document.getElementById('ref-id')?.value;
    if (editId && isCodeManuallyEdited) return;

    fetch('<?= site_url('pegawai/generate_ref_code') ?>?kategori=' + encodeURIComponent(kategori) + '&nama=' + encodeURIComponent(nama))
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                if (!isCodeManuallyEdited) {
                    const kodeInput = document.getElementById('ref-kode');
                    if (kodeInput) kodeInput.value = res.code;
                    updateAutoCodeBadge(true);
                }
                if (!editId) {
                    const sortInput = document.getElementById('ref-sort-order');
                    if (sortInput) sortInput.value = res.next_sort;
                }
            }
        })
        .catch(err => {
            console.error('Error auto-generating ref code:', err);
        });
}

const refCategoryMeta = {
    'golongan_ruang': {
        title: 'Golongan Ruang & Pangkat BKN',
        kodeLabel: 'Kode Golongan',
        kodePlaceholder: 'e.g. IV/a, III/d, IX',
        namaLabel: 'Nama Pangkat Resmi',
        namaPlaceholder: 'e.g. Pembina, Penata',
        ketPlaceholder: 'e.g. Golongan IV/a - Pembina'
    },
    'kedudukan_hukum': {
        title: 'Kedudukan Hukum Pegawai',
        kodeLabel: 'Kode / Singkatan',
        kodePlaceholder: 'e.g. Aktif, Tubel, CLTN',
        namaLabel: 'Nama Kedudukan Hukum',
        namaPlaceholder: 'e.g. Aktif Bekerja, Tugas Belajar',
        ketPlaceholder: 'Keterangan status kedudukan'
    },
    'jenis_jabatan': {
        title: 'Jenis Jabatan ASN',
        kodeLabel: 'Kode / Singkatan',
        kodePlaceholder: 'e.g. JFT, JFU, Struktural',
        namaLabel: 'Nama Jenis Jabatan',
        namaPlaceholder: 'e.g. Fungsional Tertentu (JFT)',
        ketPlaceholder: 'Nomenklatur kelompok jabatan'
    },
    'seksi_ruangan': {
        title: 'Seksi & Instalasi Penempatan',
        kodeLabel: 'Kode Seksi / Singkatan',
        kodePlaceholder: 'e.g. Kimia Klinik, Hematologi',
        namaLabel: 'Nama Seksi / Instalasi',
        namaPlaceholder: 'e.g. Instalasi Kimia Klinik',
        ketPlaceholder: 'Nama lengkap unit instalasi'
    },
    'bank_payroll': {
        title: 'Bank Rekening Payroll',
        kodeLabel: 'Kode Bank',
        kodePlaceholder: 'e.g. BRKS, MANDIRI, BRI',
        namaLabel: 'Nama Bank Resmi',
        namaPlaceholder: 'e.g. Bank Riau Kepri Syariah',
        ketPlaceholder: 'Keterangan kantor cabang atau rekening'
    },
    'status_pegawai': {
        title: 'Status Kepegawaian',
        kodeLabel: 'Kode Status',
        kodePlaceholder: 'e.g. PNS, CPNS, PPPK, BLUD',
        namaLabel: 'Nama Status Pegawai',
        namaPlaceholder: 'e.g. PNS (Pegawai Negeri Sipil)',
        ketPlaceholder: 'Keterangan legalitas pegawai'
    }
};

function openRefModal(kategori = 'all', customTitle = '') {
    if (!kategori || kategori === 'all') {
        kategori = 'golongan_ruang';
    }
    switchRefTab(kategori);

    document.getElementById('modal-referensi-pegawai').classList.remove('hidden');
    document.getElementById('modal-referensi-pegawai').classList.add('flex');
    document.getElementById('modal-ref-backdrop').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    if (window.lucide) lucide.createIcons();
}

function closeRefModal() {
    document.getElementById('modal-referensi-pegawai').classList.remove('flex');
    document.getElementById('modal-referensi-pegawai').classList.add('hidden');
    document.getElementById('modal-ref-backdrop').classList.add('hidden');
    if (document.getElementById('modal-pegawai').classList.contains('hidden') && 
        document.getElementById('modal-detail').classList.contains('hidden')) {
        document.body.style.overflow = '';
    }
    resetRefForm();
}

function switchRefTab(kategori) {
    currentRefCategory = kategori;
    document.getElementById('ref-kategori').value = kategori;

    // Update Tab Buttons UI
    document.querySelectorAll('.tab-ref-btn').forEach(btn => {
        btn.classList.remove('bg-emerald-800', 'text-amber-300', 'border', 'border-amber-400/40', 'shadow-xs');
        btn.classList.add('text-slate-600', 'hover:bg-white');
    });
    const activeTab = document.getElementById('tab-ref-' + kategori);
    if (activeTab) {
        activeTab.classList.remove('text-slate-600', 'hover:bg-white');
        activeTab.classList.add('bg-emerald-800', 'text-amber-300', 'border', 'border-amber-400/40', 'shadow-xs');
    }

    // Update Labels & Placeholders
    const meta = refCategoryMeta[kategori] || refCategoryMeta['golongan_ruang'];
    document.getElementById('badge-ref-active-category').textContent = meta.title;
    document.getElementById('label-table-kategori').textContent = meta.title;
    document.getElementById('label-ref-kode').textContent = meta.kodeLabel;
    document.getElementById('ref-kode').placeholder = meta.kodePlaceholder;
    document.getElementById('label-ref-nama').innerHTML = meta.namaLabel + ' <span class="text-rose-500">*</span>';
    document.getElementById('ref-nama').placeholder = meta.namaPlaceholder;
    document.getElementById('ref-keterangan').placeholder = meta.ketPlaceholder;

    resetRefForm();
    loadRefTable(kategori);
}

function loadRefTable(kategori) {
    fetch('<?= site_url('pegawai/fetch_referensi') ?>?kategori=' + encodeURIComponent(kategori))
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                rawCategoryItems = res.data || [];
                renderRefTable(rawCategoryItems);
            }
        })
        .catch(err => {
            console.error('Error fetching referensi:', err);
        });
}

function renderRefTable(items) {
    const tbody = document.getElementById('ref-table-body');
    const badgeCount = document.getElementById('ref-count-badge');
    badgeCount.textContent = (items ? items.length : 0) + ' item';

    if (!items || items.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                    <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                    <p class="font-semibold text-xs">Belum ada data referensi untuk kategori ini.</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Gunakan form di atas untuk menambahkan data baru.</p>
                </td>
            </tr>
        `;
        if (window.lucide) lucide.createIcons();
        return;
    }

    let html = '';
    items.forEach((item, idx) => {
        const kodeBadge = item.kode 
            ? `<span class="inline-block px-2 py-0.5 rounded bg-emerald-50 text-emerald-800 font-mono font-bold text-[11px] border border-emerald-200">${item.kode}</span>`
            : '<span class="text-slate-400">-</span>';

        html += `
            <tr class="hover:bg-emerald-50/40 transition-colors">
                <td class="px-3 py-2 text-center font-mono font-bold text-slate-400">${idx + 1}</td>
                <td class="px-3 py-2">${kodeBadge}</td>
                <td class="px-3 py-2 font-bold text-slate-900">${item.nama}</td>
                <td class="px-3 py-2 text-slate-600 text-[11px]">${item.keterangan || '-'}</td>
                <td class="px-3 py-2 text-center font-mono text-slate-500">${item.sort_order || 0}</td>
                <td class="px-3 py-2 text-center">
                    <div class="flex items-center justify-center gap-1">
                        <button type="button" onclick="editReferensi(${item.id})" class="p-1.5 rounded-lg text-emerald-700 hover:bg-emerald-100 hover:text-emerald-900 transition-colors" title="Edit Referensi">
                            <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                        </button>
                        <button type="button" onclick="deleteReferensi(${item.id}, '${item.nama.replace(/'/g, "\\'")}')" class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-100 hover:text-rose-800 transition-colors" title="Hapus Referensi">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });

    tbody.innerHTML = html;
    if (window.lucide) lucide.createIcons();
}

function filterRefTable(keyword) {
    const q = keyword.toLowerCase().trim();
    const btnClear = document.getElementById('btn-clear-ref-search');
    const suggestBox = document.getElementById('ref-search-suggestions');
    if (btnClear) btnClear.classList.toggle('hidden', !q);

    if (!q) {
        renderRefTable(rawCategoryItems);
        if (suggestBox) { suggestBox.classList.add('hidden'); suggestBox.innerHTML = ''; }
        return;
    }
    const filtered = rawCategoryItems.filter(item => {
        return (item.nama && item.nama.toLowerCase().includes(q)) ||
               (item.kode && item.kode.toLowerCase().includes(q)) ||
               (item.keterangan && item.keterangan.toLowerCase().includes(q));
    });
    renderRefTable(filtered);

    if (suggestBox) {
        if (filtered.length === 0) {
            suggestBox.classList.add('hidden');
            suggestBox.innerHTML = '';
        } else {
            let html = '<div class="px-3 py-1.5 bg-slate-50 text-[10px] font-bold text-slate-500 flex justify-between"><span>Saran Data</span><span class="text-emerald-700">' + Math.min(filtered.length, 6) + ' saran</span></div>';
            filtered.slice(0, 6).forEach(item => {
                html += '<button type="button" onclick="selectRefSuggestion(\'' + (item.nama || '').replace(/'/g, "\\'") + '\')" class="w-full text-left px-3 py-2 text-xs hover:bg-emerald-50 flex items-center justify-between group transition-colors">';
                html += '<span class="font-bold text-slate-800 truncate">' + item.nama + '</span>';
                if (item.kode) html += '<span class="font-mono text-[10px] text-emerald-800 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 ml-2">' + item.kode + '</span>';
                html += '</button>';
            });
            suggestBox.innerHTML = html;
            suggestBox.classList.remove('hidden');
        }
    }
}

function selectRefSuggestion(val) {
    const input = document.getElementById('ref-search-input');
    if (input) {
        input.value = val;
        filterRefTable(val);
    }
    const suggestBox = document.getElementById('ref-search-suggestions');
    if (suggestBox) suggestBox.classList.add('hidden');
}

function clearRefSearch() {
    const input = document.getElementById('ref-search-input');
    if (input) {
        input.value = '';
        filterRefTable('');
    }
}

document.addEventListener('click', function(e) {
    const wrapper = document.getElementById('ref-search-wrapper');
    const suggestBox = document.getElementById('ref-search-suggestions');
    if (wrapper && suggestBox && !wrapper.contains(e.target)) {
        suggestBox.classList.add('hidden');
    }
});

function resetRefForm() {
    isCodeManuallyEdited = false;
    document.getElementById('ref-id').value = '';
    document.getElementById('ref-kode').value = '';
    document.getElementById('ref-nama').value = '';
    document.getElementById('ref-keterangan').value = '';
    document.getElementById('ref-sort-order').value = (rawCategoryItems.length || 0) + 1;
    document.getElementById('form-ref-title').textContent = 'Form Tambah Referensi Baru';
    document.getElementById('btn-save-ref-text').textContent = 'Simpan Data Referensi';
    document.getElementById('btn-cancel-ref-edit').classList.add('hidden');
    document.getElementById('btn-cancel-ref-edit').classList.remove('inline-flex');
    updateAutoCodeBadge(true);

    // Ambil kode dan nomor urut otomatis berikutnya dari program
    fetchAutoRefCode(currentRefCategory, '');
    if (window.lucide) lucide.createIcons();
}

function editReferensi(id) {
    fetch('<?= site_url('pegawai/get_referensi_item/') ?>' + id)
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success') {
                const item = res.data;
                document.getElementById('ref-id').value = item.id;
                document.getElementById('ref-kategori').value = item.kategori;
                document.getElementById('ref-kode').value = item.kode || '';
                document.getElementById('ref-nama').value = item.nama || '';
                document.getElementById('ref-keterangan').value = item.keterangan || '';
                document.getElementById('ref-sort-order').value = item.sort_order || 0;

                isCodeManuallyEdited = true;
                updateAutoCodeBadge(false);

                document.getElementById('form-ref-title').innerHTML = `Edit Referensi: <span class="text-amber-800 font-bold">${item.nama}</span>`;
                document.getElementById('btn-save-ref-text').textContent = 'Perbarui Referensi';
                document.getElementById('btn-cancel-ref-edit').classList.remove('hidden');
                document.getElementById('btn-cancel-ref-edit').classList.add('inline-flex');

                document.getElementById('ref-nama').focus();
                if (window.lucide) lucide.createIcons();
            }
        })
        .catch(err => {
            console.error('Error fetching item for edit:', err);
        });
}

function saveReferensi(e) {
    e.preventDefault();

    const nama = document.getElementById('ref-nama')?.value.trim();
    if (!nama) {
        LIS.toast('warning', 'Nama Referensi wajib diisi.');
        document.getElementById('ref-nama')?.focus();
        return;
    }

    const btn = document.getElementById('btn-save-ref');
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 animate-spin"></i> Menyimpan...';
    if (window.lucide) lucide.createIcons();

    const formData = new FormData(document.getElementById('form-referensi'));

    fetch('<?= site_url('pegawai/save_referensi') ?>', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = origHtml;
        if (window.lucide) lucide.createIcons();

        if (res.status === 'success') {
            LIS.toast('success', res.message);
            resetRefForm();
            loadRefTable(currentRefCategory);
            if (res.references) {
                updateAllDropdowns(res.references, res.saved_item.kategori, res.saved_item.kode || res.saved_item.nama);
            }
        } else {
            LIS.toast('error', res.message || 'Gagal menyimpan data referensi.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = origHtml;
        if (window.lucide) lucide.createIcons();
        console.error(err);
        LIS.toast('error', 'Terjadi kesalahan jaringan.');
    });
}

function deleteReferensi(id, nama) {
    LIS.confirm(
        'Hapus Referensi',
        `Apakah Anda yakin ingin menghapus referensi <strong>${nama}</strong>? Data yang sudah tersimpan pada pegawai tidak akan terhapus namun referensi tidak lagi muncul di pilihan baru.`,
        'Ya, Hapus Data'
    ).then(res => {
        if (res && (res.isConfirmed || res === true)) {
            fetch('<?= site_url('pegawai/delete_referensi/') ?>' + id, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    LIS.toast('success', data.message);
                    loadRefTable(currentRefCategory);
                    if (data.references) {
                        updateAllDropdowns(data.references);
                    }
                } else {
                    LIS.toast('error', data.message || 'Gagal menghapus referensi.');
                }
            })
            .catch(err => {
                console.error(err);
                LIS.toast('error', 'Terjadi kesalahan server saat menghapus.');
            });
        }
    });
}

function updateAllDropdowns(refs, catToSelect = null, valToSelect = null) {
    if (!refs) return;
    allReferencesCache = refs;

    // 1. Status Kepegawaian
    if (refs.status_pegawai) {
        const sel = document.getElementById('pegawai-status');
        if (sel) {
            const curVal = (catToSelect === 'status_pegawai' && valToSelect) ? valToSelect : sel.value;
            sel.innerHTML = refs.status_pegawai.map(r => {
                const v = r.kode || r.nama;
                return `<option value="${v}">${r.nama}</option>`;
            }).join('');
            if (curVal) sel.value = curVal;
        }
    }

    // 2. Golongan Ruang
    if (refs.golongan_ruang) {
        const sel = document.getElementById('pegawai-golongan');
        if (sel) {
            const curVal = (catToSelect === 'golongan_ruang' && valToSelect) ? valToSelect : sel.value;
            let optHtml = '<option value="">-- Pilih Golongan --</option>';
            optHtml += refs.golongan_ruang.map(r => {
                const code = r.kode || r.nama;
                return `<option value="${code}" data-pangkat="${r.nama}">${code} - ${r.nama}</option>`;
            }).join('');
            sel.innerHTML = optHtml;
            if (curVal) {
                sel.value = curVal;
                syncGolonganPangkat(curVal);
            }
        }
    }

    // 3. Kedudukan Hukum
    if (refs.kedudukan_hukum) {
        const sel = document.getElementById('pegawai-kedudukan');
        if (sel) {
            const curVal = (catToSelect === 'kedudukan_hukum' && valToSelect) ? valToSelect : sel.value;
            sel.innerHTML = refs.kedudukan_hukum.map(r => {
                const v = r.kode || r.nama;
                return `<option value="${v}">${r.nama}</option>`;
            }).join('');
            if (curVal) sel.value = curVal;
        }
    }

    // 4. Jenis Jabatan
    if (refs.jenis_jabatan) {
        const sel = document.getElementById('pegawai-jenis-jabatan');
        if (sel) {
            const curVal = (catToSelect === 'jenis_jabatan' && valToSelect) ? valToSelect : sel.value;
            sel.innerHTML = refs.jenis_jabatan.map(r => {
                const v = r.kode || r.nama;
                return `<option value="${v}">${r.nama}</option>`;
            }).join('');
            if (curVal) sel.value = curVal;
        }
    }

    // 5. Seksi / Instalasi (Form + Filter Main Table)
    if (refs.seksi_ruangan) {
        const selForm = document.getElementById('pegawai-seksi');
        const selFilter = document.getElementById('filter-seksi');

        const curValForm = (catToSelect === 'seksi_ruangan' && valToSelect) ? valToSelect : (selForm ? selForm.value : '');
        const curValFilter = selFilter ? selFilter.value : 'all';

        const optHtml = refs.seksi_ruangan.map(r => {
            const k = r.kode || r.nama;
            const v = r.keterangan || r.nama;
            return `<option value="${k}">${v}</option>`;
        }).join('');

        if (selForm) {
            selForm.innerHTML = optHtml;
            if (curValForm) selForm.value = curValForm;
        }

        if (selFilter) {
            selFilter.innerHTML = '<option value="all">-- Semua Seksi / Instalasi --</option>' + optHtml;
            if (curValFilter) selFilter.value = curValFilter;
        }
    }

    // 6. Bank Payroll
    if (refs.bank_payroll) {
        const sel = document.getElementById('pegawai-bank');
        if (sel) {
            const curVal = (catToSelect === 'bank_payroll' && valToSelect) ? valToSelect : sel.value;
            sel.innerHTML = refs.bank_payroll.map(r => {
                return `<option value="${r.nama}">${r.nama}</option>`;
            }).join('');
            if (curVal) sel.value = curVal;
        }
    }

    updatePreviewCard();
}
</script>

<style>
.filter-tab-btn.active-tab {
    background-color: #047857;
    color: #fef08a;
    font-weight: 700;
    box-shadow: 0 1px 3px rgba(4, 120, 87, 0.2);
}
</style>
