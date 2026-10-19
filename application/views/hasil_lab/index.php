<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-5">

    <!-- 1. BREADCRUMB & PAGE HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-slate-400">Pelayanan Laboratorium</span>
                <span>/</span>
                <span class="text-emerald-800 font-semibold">Input Hasil Lab</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-teal-50 text-teal-800 border border-teal-200 shadow-2xs">
                    <i data-lucide="flask-conical" class="w-6 h-6 text-teal-700"></i>
                </span>
                <span>Pengerjaan & Input Hasil Laboratorium</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Pencatatan parameter uji, evaluasi nilai rujukan otomatis, deteksi flag kritis &bull; Standar Akreditasi KAN ISO 17025 / ISO 15189
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" onclick="reloadTable()" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold flex items-center gap-2 transition-colors border border-slate-200" title="Muat Ulang Data">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Refresh</span>
            </button>
            <a href="<?= site_url('validasi') ?>" class="px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold flex items-center gap-2 shadow-xs transition-all border border-emerald-600">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-amber-300"></i>
                <span>Ke Menu Validasi Sp.PK</span>
            </a>
        </div>
    </div>

    <!-- 2. SUMMARY STATISTIK KHUSUS ANALIS -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-teal-50 text-teal-800 border border-teal-200 flex items-center justify-center flex-shrink-0">
                <i data-lucide="test-tubes" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 block truncate">Total Sampel di Lab</span>
                <span class="text-xl font-extrabold text-slate-900 leading-tight"><?= number_format($stats['total_processing']) ?></span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-amber-200 bg-amber-50/20 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-800 border border-amber-300 flex items-center justify-center flex-shrink-0">
                <i data-lucide="clock" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-amber-800 block truncate">Menunggu Input Hasil</span>
                <span class="text-xl font-extrabold text-amber-950 leading-tight"><?= number_format($stats['belum_diinput']) ?></span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-emerald-200 bg-emerald-50/20 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center justify-center flex-shrink-0">
                <i data-lucide="check-check" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-emerald-800 block truncate">Selesai Hari Ini</span>
                <span class="text-xl font-extrabold text-emerald-950 leading-tight"><?= number_format($stats['selesai_hari_ini']) ?></span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-rose-200 bg-rose-50/20 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-rose-100 text-rose-800 border border-rose-300 flex items-center justify-center flex-shrink-0">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-600"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-rose-800 block truncate">Nilai Kritis (Panic)</span>
                <span class="text-xl font-extrabold text-rose-950 leading-tight"><?= number_format($stats['kritis_count']) ?></span>
            </div>
        </div>
    </div>

    <!-- 3. FILTER & TABEL PENGERJAAN HASIL LAB -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        
        <!-- Filter Bar -->
        <div class="p-4 sm:p-5 border-b border-slate-200 bg-slate-50/70 space-y-3">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                
                <!-- Status Tab -->
                <div class="flex items-center gap-1.5 p-1 bg-slate-200/60 rounded-xl max-w-fit">
                    <button type="button" onclick="setStatusFilter('processing')" id="tab-status-processing" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all bg-teal-800 text-white shadow-xs">
                        Sedang Dikerjakan (Processing)
                    </button>
                    <button type="button" onclick="setStatusFilter('validasi')" id="tab-status-validasi" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 transition-all">
                        Menunggu Validasi Sp.PK
                    </button>
                    <button type="button" onclick="setStatusFilter('completed')" id="tab-status-completed" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 transition-all">
                        Sudah Selesai (Completed)
                    </button>
                    <button type="button" onclick="setStatusFilter('all')" id="tab-status-all" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 transition-all">
                        Semua Status
                    </button>
                </div>

                <!-- Kategori & Tanggal Filter -->
                <div class="flex items-center gap-2 flex-wrap">
                    <select id="filter-kategori" onchange="reloadTable()" class="lis-form-control text-xs w-44">
                        <option value="all">Semua Kategori Lab</option>
                        <option value="klinis">Laboratorium Klinis</option>
                        <option value="lingkungan">Laboratorium Lingkungan</option>
                    </select>
                    <input type="date" id="filter-tanggal" onchange="reloadTable()" class="lis-form-control text-xs w-36">
                    <button type="button" onclick="resetFilters()" class="px-3 py-2 rounded-xl text-xs text-slate-500 hover:text-slate-800 hover:bg-slate-200 transition-colors">
                        Reset
                    </button>
                </div>

            </div>
        </div>

        <!-- Tabel Data -->
        <div class="p-4 sm:p-5 overflow-x-auto">
            <table id="table-hasil-lab" class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-bold bg-slate-50">
                        <th class="py-3 px-3 w-12 text-center">No</th>
                        <th class="py-3 px-3">Barcode Sampel</th>
                        <th class="py-3 px-3">Identitas Pasien</th>
                        <th class="py-3 px-3">Pengirim & Sampel</th>
                        <th class="py-3 px-3">Parameter Uji</th>
                        <th class="py-3 px-3 text-center">Status Pengerjaan</th>
                        <th class="py-3 px-3 text-center w-36">Aksi Analis</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium"></tbody>
            </table>
        </div>

    </div>

</div>

<!-- ========================================================================= -->
<!-- MODAL: INPUT HASIL PEMERIKSAAN LAB (STANDAR KAN ISO 17025)                -->
<!-- ========================================================================= -->
<div id="modal-input-hasil" class="lis-modal">
    <div class="lis-modal-box max-w-5xl w-full">
        <!-- Header Modal -->
        <div class="lis-modal-header bg-gradient-to-r from-teal-900 via-emerald-800 to-slate-900 border-b border-amber-400/40">
            <div>
                <h3 class="lis-modal-title text-white">
                    <i data-lucide="flask-conical" class="w-5 h-5 text-amber-300"></i>
                    <span>Lembar Kerja Pengerjaan & Input Hasil Laboratorium</span>
                </h3>
                <p class="lis-modal-subtitle">Pencatatan Parameter Uji, Nilai Rujukan, Evaluasi Flag & Kesimpulan Klinis</p>
            </div>
            <button type="button" onclick="closeInputHasilModal()" class="lis-modal-close" title="Tutup">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Body Modal -->
        <div class="lis-modal-body p-5 space-y-4 bg-white max-h-[75vh] overflow-y-auto">
            
            <!-- Banner Identitas Sampel & Pasien -->
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Barcode Sampel</span>
                    <span id="hasil-barcode" class="font-mono font-extrabold text-emerald-800 text-sm">SMP-2026-XXXX</span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">No. Registrasi / RM</span>
                    <span id="hasil-reg-num" class="font-mono font-bold text-slate-800">REG-...</span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Nama Pasien / Subjek</span>
                    <span id="hasil-patient-name" class="font-bold text-slate-900">Nama Pasien</span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Usia / Jenis Kelamin</span>
                    <span id="hasil-gender-age" class="font-semibold text-slate-700">-</span>
                </div>
            </div>

            <!-- Form Input Hasil Parameter -->
            <form id="form-input-hasil" onsubmit="submitHasilLab(event)" class="space-y-4">
                <input type="hidden" id="hasil-reg-id" name="id">

                <div class="border border-slate-200 rounded-xl overflow-hidden shadow-2xs">
                    <table class="w-full text-left text-xs divide-y divide-slate-200">
                        <thead class="bg-slate-100 text-[10px] font-bold uppercase text-slate-600 tracking-wider">
                            <tr>
                                <th class="p-3 w-10 text-center">No</th>
                                <th class="p-3">Parameter Uji</th>
                                <th class="p-3 w-40">Nilai Hasil <span class="text-rose-600">*</span></th>
                                <th class="p-3 w-20">Satuan</th>
                                <th class="p-3 w-36">Nilai Rujukan</th>
                                <th class="p-3 w-24 text-center">Flag Evaluasi</th>
                                <th class="p-3">Catatan Analis</th>
                            </tr>
                        </thead>
                        <tbody id="hasil-table-body" class="divide-y divide-slate-100">
                            <tr>
                                <td colspan="7" class="text-center py-6 text-slate-400">Memuat parameter uji...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Catatan Tambahan, Kesimpulan Klinis & Penanggung Jawab -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                    <div class="space-y-2.5">
                        <div>
                            <label class="lis-form-label">Catatan Teknis Analis / Catatan Sampel</label>
                            <input type="text" name="catatan_hasil" id="hasil-catatan-teknis" class="lis-form-control text-xs" placeholder="Misal: Sampel agak lipemik, pengujian diulang duplek">
                        </div>
                        <div>
                            <label class="lis-form-label">Kesimpulan Klinis / Ekspertise</label>
                            <textarea name="kesimpulan_klinis" id="hasil-kesimpulan" rows="2" class="lis-form-control text-xs" placeholder="Kesan klinis atau rekomendasi uji lanjutan..."></textarea>
                        </div>
                    </div>

                    <div class="space-y-2.5">
                        <div>
                            <label class="lis-form-label">Analis Pemeriksa (ATLM) <span class="req">*</span></label>
                            <input type="text" name="analis_nama" id="hasil-analis-nama" required value="<?= html_escape($default_analis) ?>" class="lis-form-control font-semibold text-xs">
                        </div>
                        <div>
                            <label class="lis-form-label">Dokter Penanggung Jawab Laboratorium (Sp.PK)</label>
                            <input type="text" name="dokter_pj" id="hasil-dokter-pj" value="<?= html_escape($default_dokter_pj) ?>" class="lis-form-control font-semibold text-xs">
                        </div>
                        <div>
                            <label class="lis-form-label">Tujuan Alur Kerja Hasil Uji</label>
                            <select name="status_order" id="hasil-status-order" class="lis-form-control font-bold text-xs bg-white">
                                <option value="validasi">🚀 Hasil Selesai (Kirim ke Validasi Dokter Sp.PK)</option>
                                <option value="processing">⏳ Simpan Sementara (Draft Analis / Processing)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Footer Modal -->
                <div class="pt-3 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5">
                    <button type="button" onclick="closeInputHasilModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-100 transition-colors">
                        Batal
                    </button>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="submitHasilWithStatus('processing')" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all border border-slate-300 flex items-center gap-1.5 shadow-2xs">
                            <i data-lucide="file-edit" class="w-4 h-4 text-slate-500"></i>
                            <span>Simpan Draft</span>
                        </button>
                        <button type="button" onclick="submitHasilWithStatus('validasi')" id="btn-save-hasil" class="px-6 py-2.5 rounded-xl bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold shadow-md shadow-teal-900/20 transition-all flex items-center gap-2 border border-teal-600">
                            <i data-lucide="send" class="w-4 h-4 text-amber-300"></i>
                            <span>🚀 Kirim ke Validasi Dokter Sp.PK</span>
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
    let tableHasil = null;
    let currentStatus = 'processing';
    let currentLoadedItems = [];

    document.addEventListener('DOMContentLoaded', function() {
        initTable();
    });

    function initTable() {
        tableHasil = $('#table-hasil-lab').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?= site_url('hasil_lab/fetch') ?>',
                type: 'GET',
                data: function(d) {
                    d.status_order = currentStatus;
                    d.kategori = $('#filter-kategori').val();
                    d.tanggal = $('#filter-tanggal').val();
                }
            },
            columns: [
                { data: 'no_urut', orderable: false, className: 'text-center text-slate-400 font-medium py-3' },
                {
                    data: null,
                    render: function(d) {
                        return `<div>
                            <span class="inline-block font-mono text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-50 text-emerald-950 border border-emerald-200">${d.barcode_sampel}</span>
                            <span class="font-mono text-[10px] text-slate-400 block mt-0.5">${d.nomor_registrasi}</span>
                        </div>`;
                    }
                },
                {
                    data: null,
                    render: function(d) {
                        return `<div>
                            <strong class="text-slate-900 block font-bold leading-tight">${d.nama_pasien}</strong>
                            <span class="text-[10px] text-slate-500 font-mono">${d.nomor_rm || '-'}</span> &bull; 
                            <span class="text-[10px] text-slate-600 font-medium">${d.jenis_kelamin || '-'}, ${d.usia_tahun || '-'} th</span>
                        </div>`;
                    }
                },
                {
                    data: null,
                    render: function(d) {
                        return `<div>
                            <span class="text-slate-800 font-medium block">${d.instansi_pengirim || d.dokter_pengirim || 'Umum Mandiri'}</span>
                            <span class="text-[10px] text-teal-700 font-semibold block">${d.jenis_sampel || 'Darah'}</span>
                        </div>`;
                    }
                },
                {
                    data: null,
                    render: function(d) {
                        return `<div>
                            <span class="text-slate-700 text-xs font-semibold block leading-tight">${d.param_summary}</span>
                            <span class="text-[10px] text-slate-400">${d.filled_items}/${d.total_items} parameter diisi</span>
                        </div>`;
                    }
                },
                {
                    data: null,
                    className: 'text-center py-3',
                    render: function(d) {
                        if (d.status_order === 'completed') {
                            return `<span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center justify-center gap-1">
                                <i data-lucide="shield-check" class="w-3 h-3"></i>
                                <span>Selesai (Completed)</span>
                            </span>`;
                        } else if (d.status_order === 'validasi') {
                            return `<span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300 flex items-center justify-center gap-1">
                                <i data-lucide="clock" class="w-3 h-3"></i>
                                <span>Menunggu Sp.PK</span>
                            </span>`;
                        } else if (d.filled_items > 0) {
                            return `<span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-teal-50 text-teal-800 border border-teal-300 flex items-center justify-center gap-1">
                                <i data-lucide="file-edit" class="w-3 h-3"></i>
                                <span>Draft (${d.filled_items}/${d.total_items})</span>
                            </span>`;
                        } else {
                            return `<span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-300 flex items-center justify-center gap-1">
                                <i data-lucide="flask-conical" class="w-3 h-3"></i>
                                <span>Belum Diinput</span>
                            </span>`;
                        }
                    }
                },
                {
                    data: null,
                    orderable: false,
                    className: 'text-center py-3',
                    render: function(d) {
                        const btnInput = `<button type="button" onclick="openInputHasilModal(${d.id})" class="px-2.5 py-1.5 rounded-lg bg-teal-700 hover:bg-teal-800 text-white text-[10px] font-bold transition-all shadow-xs flex items-center gap-1.5 border border-teal-600" title="Input Hasil Laboratorium">
                            <i data-lucide="flask-conical" class="w-3.5 h-3.5 text-amber-300"></i>
                            <span>Input Hasil</span>
                        </button>`;

                        const btnBarcode = `<a href="<?= site_url('pendaftaran/cetak_barcode') ?>/${d.id}" target="_blank" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10px] transition-all shadow-2xs" title="Cetak Barcode Tabung">
                            <i data-lucide="scan-barcode" class="w-3.5 h-3.5"></i>
                        </a>`;

                        return `<div class="flex items-center justify-center gap-1.5">
                            ${btnInput}
                            ${btnBarcode}
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
        if (tableHasil) tableHasil.ajax.reload(null, false);
    }

    function setStatusFilter(status) {
        currentStatus = status;
        ['processing', 'validasi', 'completed', 'all'].forEach(s => {
            const btn = document.getElementById('tab-status-' + s);
            if (btn) {
                if (s === status) {
                    btn.className = 'px-3 py-1.5 rounded-lg text-xs font-bold transition-all bg-teal-800 text-white shadow-xs';
                } else {
                    btn.className = 'px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 transition-all';
                }
            }
        });
        reloadTable();
    }

    function resetFilters() {
        $('#filter-kategori').val('all');
        $('#filter-tanggal').val('');
        setStatusFilter('processing');
    }

    // Modal Input Hasil
    function openInputHasilModal(id) {
        LIS.fetchJson('<?= site_url('hasil_lab/get_hasil') ?>/' + id)
            .then(res => {
                if (res.status === 'success') {
                    const reg = res.data.pendaftaran;
                    const items = res.data.items;
                    currentLoadedItems = items;

                    document.getElementById('hasil-reg-id').value = reg.id;
                    document.getElementById('hasil-barcode').textContent = reg.barcode_sampel;
                    document.getElementById('hasil-reg-num').textContent = reg.nomor_registrasi;
                    document.getElementById('hasil-patient-name').textContent = reg.nama_pasien;
                    document.getElementById('hasil-gender-age').textContent = (reg.jenis_kelamin || '-') + ' / ' + (reg.usia_tahun || '-') + ' Thn';

                    document.getElementById('hasil-catatan-teknis').value = reg.catatan_hasil || '';
                    document.getElementById('hasil-kesimpulan').value = reg.kesimpulan_saran || reg.kesimpulan_klinis || '';
                    if (reg.analis_nama) document.getElementById('hasil-analis-nama').value = reg.analis_nama;
                    if (reg.dokter_pj) document.getElementById('hasil-dokter-pj').value = reg.dokter_pj;
                    document.getElementById('hasil-status-order').value = reg.status_order || 'processing';

                    const tbody = document.getElementById('hasil-table-body');
                    if (items.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-6 text-slate-400">Tidak ada parameter uji untuk order ini.</td></tr>';
                    } else {
                        let html = '';
                        items.forEach((it, idx) => {
                            const valHasil = (it.hasil_nilai !== null && it.hasil_nilai !== undefined) ? it.hasil_nilai : (it.hasil || '');
                            let flagVal = it.hasil_flag || it.flag_kritis || 'normal';
                            // Format flag untuk tampilan UI
                            if (flagVal === 'normal') flagVal = 'Normal';
                            else if (flagVal === 'low') flagVal = 'L';
                            else if (flagVal === 'high') flagVal = 'H';
                            else if (flagVal === 'critical') flagVal = 'L*';

                            const noteVal = it.catatan_hasil || it.catatan_pemeriksaan || '';

                            let flagBadgeClass = 'bg-slate-100 text-slate-700 border-slate-300';
                            if (flagVal === 'Normal') flagBadgeClass = 'bg-emerald-50 text-emerald-800 border-emerald-300';
                            else if (flagVal === 'L' || flagVal === 'H') flagBadgeClass = 'bg-amber-100 text-amber-900 border-amber-300';
                            else if (flagVal === 'L*' || flagVal === 'H*') flagBadgeClass = 'bg-rose-100 text-rose-900 border-rose-300 font-black animate-pulse';

                            html += `<tr class="hover:bg-slate-50 transition-colors" data-idx="${idx}">
                                <td class="p-3 text-center text-slate-400 font-medium">${idx + 1}</td>
                                <td class="p-3">
                                    <strong class="text-slate-900 block leading-tight">${it.nama_item}</strong>
                                    <span class="text-[10px] text-slate-500">${it.kategori_lab || '-'}</span>
                                    <input type="hidden" name="results[${idx}][id]" value="${it.id}">
                                </td>
                                <td class="p-3">
                                    <input type="text" name="results[${idx}][hasil_nilai]" value="${valHasil}" oninput="evaluateItemFlag(${idx}, this.value)" class="lis-form-control font-mono font-bold text-xs" placeholder="Hasil...">
                                </td>
                                <td class="p-3 font-mono text-[11px] text-slate-600">${it.satuan || '-'}</td>
                                <td class="p-3 font-mono text-[11px] text-slate-600">${it.nilai_rujukan || '-'}</td>
                                <td class="p-3 text-center">
                                    <select name="results[${idx}][hasil_flag]" id="flag-select-${idx}" class="text-[10px] font-bold rounded px-1.5 py-1 border ${flagBadgeClass}">
                                        <option value="Normal" ${flagVal === 'Normal' ? 'selected' : ''}>Normal</option>
                                        <option value="L" ${flagVal === 'L' ? 'selected' : ''}>Low (L)</option>
                                        <option value="H" ${flagVal === 'H' ? 'selected' : ''}>High (H)</option>
                                        <option value="L*" ${flagVal === 'L*' ? 'selected' : ''}>Kritis Low (L*)</option>
                                        <option value="H*" ${flagVal === 'H*' ? 'selected' : ''}>Kritis High (H*)</option>
                                    </select>
                                </td>
                                <td class="p-3">
                                    <input type="text" name="results[${idx}][catatan_hasil]" value="${noteVal}" class="lis-form-control text-[11px]" placeholder="Keterangan...">
                                </td>
                            </tr>`;
                        });
                        tbody.innerHTML = html;
                    }

                    const modal = document.getElementById('modal-input-hasil');
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    modal.style.display = 'flex';
                    if (window.lucide) lucide.createIcons();
                } else {
                    LIS.showError('Gagal Memuat Hasil Lab', res, res.message || 'Gagal memuat rincian parameter order.');
                }
            })
            .catch(err => {
                LIS.showError('Gagal Memuat Hasil Lab', err, 'Terjadi kendala teknis saat memuat data pemeriksaan.');
            });
    }

    function closeInputHasilModal() {
        const modal = document.getElementById('modal-input-hasil');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        modal.style.display = 'none';
    }

    function evaluateItemFlag(idx, val) {
        if (!currentLoadedItems || !currentLoadedItems[idx]) return;
        const item = currentLoadedItems[idx];
        const flagSelect = document.getElementById('flag-select-' + idx);
        if (!flagSelect) return;

        const numVal = parseFloat(val.replace(',', '.'));
        if (isNaN(numVal)) return;

        const ref = item.nilai_rujukan;
        if (ref && ref.includes('-')) {
            const parts = ref.split('-').map(s => parseFloat(s.trim().replace(',', '.')));
            if (parts.length === 2 && !isNaN(parts[0]) && !isNaN(parts[1])) {
                const min = parts[0];
                const max = parts[1];

                if (numVal < min) {
                    flagSelect.value = (numVal < min * 0.7) ? 'L*' : 'L';
                } else if (numVal > max) {
                    flagSelect.value = (numVal > max * 1.3) ? 'H*' : 'H';
                } else {
                    flagSelect.value = 'Normal';
                }
            }
        }
    }

    function submitHasilWithStatus(status) {
        const statusSelect = document.getElementById('hasil-status-order');
        if (statusSelect) {
            statusSelect.value = status;
        }
        const form = document.getElementById('form-input-hasil');
        if (form) {
            if (form.reportValidity && !form.reportValidity()) {
                return;
            }
            form.requestSubmit();
        }
    }

    function submitHasilLab(e) {
        e.preventDefault();
        const form = document.getElementById('form-input-hasil');
        const btnSave = document.getElementById('btn-save-hasil');
        const origBtnText = btnSave.innerHTML;

        btnSave.disabled = true;
        btnSave.innerHTML = '<span class="inline-block animate-spin mr-1">⌛</span> Menyimpan Hasil...';

        const formData = new FormData(form);

        LIS.fetchJson('<?= site_url('hasil_lab/save_hasil') ?>', {
            method: 'POST',
            body: formData
        })
        .then(res => {
            btnSave.disabled = false;
            btnSave.innerHTML = origBtnText;

            if (res.status === 'success' || res.status === true) {
                closeInputHasilModal();
                LIS.toast('success', res.message || 'Hasil pengujian laboratorium berhasil disimpan.');
                reloadTable();
            } else {
                LIS.showError('Gagal Menyimpan Hasil', res, res.message || 'Gagal menyimpan hasil laboratorium.');
            }
        })
        .catch(err => {
            btnSave.disabled = false;
            btnSave.innerHTML = origBtnText;
            LIS.showError('Error Menyimpan Hasil Lab', err, 'Terjadi kesalahan sistem saat menyimpan hasil.');
        });
    }
</script>
