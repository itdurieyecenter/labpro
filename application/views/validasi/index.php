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
                <span class="text-emerald-800 font-semibold">Validasi & Otorisasi</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="check-circle-2" class="w-6 h-6 text-emerald-700"></i>
                </span>
                <span>Validasi & Otorisasi Klinis Dokter Sp.PK</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Verifikasi kesesuaian hasil uji lab, evaluasi flag kritis, ekspertise patologi klinik, dan pengesahan hasil resmi LIS
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" onclick="reloadTable()" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold flex items-center gap-2 transition-colors border border-slate-200" title="Muat Ulang Data">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Refresh</span>
            </button>
            <a href="<?= site_url('hasil_lab') ?>" class="px-3.5 py-2.5 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-800 text-xs font-bold flex items-center gap-2 transition-colors border border-teal-300">
                <i data-lucide="flask-conical" class="w-4 h-4 text-teal-700"></i>
                <span>Meja Analis Lab</span>
            </a>
        </div>
    </div>

    <!-- 2. SUMMARY STATISTIK DOKTER Sp.PK -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white p-4 rounded-2xl border border-amber-200 bg-amber-50/20 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-800 border border-amber-300 flex items-center justify-center flex-shrink-0">
                <i data-lucide="clock" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-amber-800 block truncate">Menunggu Otorisasi Sp.PK</span>
                <span class="text-xl font-extrabold text-amber-950 leading-tight"><?= number_format($stats['siap_validasi']) ?></span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-emerald-200 bg-emerald-50/20 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center justify-center flex-shrink-0">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-emerald-800 block truncate">Tervalidasi Hari Ini</span>
                <span class="text-xl font-extrabold text-emerald-950 leading-tight"><?= number_format($stats['tervalidasi_hari_ini']) ?></span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 border border-slate-300 flex items-center justify-center flex-shrink-0">
                <i data-lucide="file-check" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 block truncate">Total Hasil Selesai</span>
                <span class="text-xl font-extrabold text-slate-900 leading-tight"><?= number_format($stats['total_completed']) ?></span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-rose-200 bg-rose-50/20 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-rose-100 text-rose-800 border border-rose-300 flex items-center justify-center flex-shrink-0">
                <i data-lucide="alert-octagon" class="w-5 h-5 text-rose-600"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-rose-800 block truncate">Peringatan Nilai Kritis</span>
                <span class="text-xl font-extrabold text-rose-950 leading-tight"><?= number_format($stats['kritis_alert']) ?></span>
            </div>
        </div>
    </div>

    <!-- 3. TABEL ORDER MENUNGGU VALIDASI -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        
        <!-- Filter Bar -->
        <div class="p-4 sm:p-5 border-b border-slate-200 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-1.5 p-1 bg-slate-200/60 rounded-xl max-w-fit">
                <button type="button" onclick="setFilter('siap')" id="btn-f-siap" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all bg-emerald-800 text-white shadow-xs">
                    Menunggu Validasi Sp.PK
                </button>
                <button type="button" onclick="setFilter('completed')" id="btn-f-completed" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 transition-all">
                    Sudah Terotorisasi (Completed)
                </button>
                <button type="button" onclick="setFilter('all')" id="btn-f-all" class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 transition-all">
                    Semua
                </button>
            </div>

            <div class="flex items-center gap-2">
                <input type="date" id="filter-tanggal" onchange="reloadTable()" class="lis-form-control text-xs w-36">
                <button type="button" onclick="$('#filter-tanggal').val(''); reloadTable();" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-800">
                    Reset
                </button>
            </div>
        </div>

        <!-- Tabel Data -->
        <div class="p-4 sm:p-5 overflow-x-auto">
            <table id="table-validasi" class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-bold bg-slate-50">
                        <th class="py-3 px-3 w-12 text-center">No</th>
                        <th class="py-3 px-3">Barcode & Registrasi</th>
                        <th class="py-3 px-3">Identitas Pasien</th>
                        <th class="py-3 px-3">Dokter / Pengirim</th>
                        <th class="py-3 px-3">Parameter Uji</th>
                        <th class="py-3 px-3 text-center">Status Validasi</th>
                        <th class="py-3 px-3 text-center w-40">Aksi Otorisasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium"></tbody>
            </table>
        </div>

    </div>

</div>

<!-- ========================================================================= -->
<!-- MODAL: REVIEW HASIL & OTORISASI DOKTER SPESIALIS PATOLOGI KLINIK          -->
<!-- ========================================================================= -->
<div id="modal-validasi-review" class="lis-modal">
    <div class="lis-modal-box max-w-5xl w-full">
        <!-- Header Modal -->
        <div class="lis-modal-header bg-gradient-to-r from-slate-900 via-teal-900 to-slate-900 border-b border-amber-400/40">
            <div>
                <h3 class="lis-modal-title text-white">
                    <i data-lucide="shield-check" class="w-5 h-5 text-amber-300"></i>
                    <span>Lembar Review Hasil & Otorisasi Klinis Dokter Sp.PK</span>
                </h3>
                <p class="lis-modal-subtitle">Verifikasi Keabsahan Analitik, Evaluasi Flag Kritis & Pengesahan Hasil Uji Resmi</p>
            </div>
            <button type="button" onclick="closeValidasiModal()" class="lis-modal-close" title="Tutup">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Body Modal -->
        <div class="lis-modal-body p-5 space-y-4 bg-white max-h-[75vh] overflow-y-auto">
            
            <!-- Banner Identitas Pasien & Sampel -->
            <div class="p-3.5 rounded-xl bg-teal-50/50 border border-teal-200 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Barcode Sampel</span>
                    <span id="val-barcode" class="font-mono font-extrabold text-teal-900 text-sm">SMP-...</span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">No. Registrasi / RM</span>
                    <span id="val-reg-num" class="font-mono font-bold text-slate-800">REG-...</span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Nama Pasien / Subjek</span>
                    <span id="val-patient-name" class="font-bold text-slate-900">Nama Pasien</span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Usia / Gender / Sampel</span>
                    <span id="val-info-extra" class="font-semibold text-slate-700">-</span>
                </div>
            </div>

            <!-- Tabel Parameter & Hasil Uji Lab -->
            <div class="border border-slate-200 rounded-xl overflow-hidden shadow-2xs">
                <table class="w-full text-left text-xs divide-y divide-slate-200">
                    <thead class="bg-slate-100 text-[10px] font-bold uppercase text-slate-600 tracking-wider">
                        <tr>
                            <th class="p-3 w-10 text-center">No</th>
                            <th class="p-3">Parameter Uji</th>
                            <th class="p-3 w-36">Nilai Hasil Uji</th>
                            <th class="p-3 w-20">Satuan</th>
                            <th class="p-3 w-36">Nilai Rujukan</th>
                            <th class="p-3 w-28 text-center">Evaluasi Flag</th>
                            <th class="p-3">Keterangan Analis</th>
                        </tr>
                    </thead>
                    <tbody id="val-table-body" class="divide-y divide-slate-100"></tbody>
                </table>
            </div>

            <!-- ============================================================= -->
            <!-- 2.1 RANTAI APPROVAL & PENGESAHAN BERJENJANG (PIPELINE)           -->
            <!-- ============================================================= -->
            <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/20 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-emerald-100 pb-2.5">
                    <div class="flex items-center gap-2">
                        <span class="p-1.5 rounded-lg bg-emerald-100 text-emerald-800 border border-emerald-200 shadow-2xs">
                            <i data-lucide="git-commit" class="w-4 h-4 text-emerald-700"></i>
                        </span>
                        <div>
                            <h4 class="text-xs font-bold text-slate-900 flex items-center gap-2">
                                <span>Rantai Approval & Pengesahan Berjenjang</span>
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-teal-100 text-teal-800 border border-teal-200" id="val-pipeline-count">0 Tingkat</span>
                            </h4>
                            <p class="text-[10px] text-slate-500">Hasil resmi hanya dapat dirilis ke customer setelah disahkan berjenjang dari tingkat pertama sampai akhir lengkap dengan tanda tangan digital.</p>
                        </div>
                    </div>
                    <div id="val-approval-status-badge"></div>
                </div>

                <!-- Pipeline Grid Container -->
                <div id="val-approval-pipeline" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-1">
                    <!-- Populated dynamically via JS -->
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- 2.2 CATATAN EKSPERTISE & KESIMPULAN KLINIS DOKTER            -->
            <!-- ============================================================= -->
            <form id="form-catatan-klinis" onsubmit="submitCatatanKlinis(event)" class="space-y-4">
                <input type="hidden" id="val-reg-id" name="id">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                    <div class="space-y-2.5">
                        <div>
                            <label class="lis-form-label">Catatan Teknis Analis Lab</label>
                            <input type="text" name="catatan_hasil" id="val-catatan-teknis" class="lis-form-control text-xs" placeholder="Catatan teknis pengujian...">
                        </div>
                        <div>
                            <label class="lis-form-label font-bold text-slate-900">Ekspertise Klinis / Kesimpulan Hasil Lab</label>
                            <textarea name="kesimpulan_klinis" id="val-kesimpulan" rows="3" class="lis-form-control text-xs border-teal-300 focus:border-teal-600" placeholder="Kesan klinis, evaluasi diagnosa patologi, saran pemeriksaan lanjutan..."></textarea>
                        </div>
                    </div>

                    <div class="space-y-2.5">
                        <div>
                            <label class="lis-form-label">Analis Pemeriksa</label>
                            <input type="text" id="val-analis-nama" readonly class="lis-form-control text-xs bg-slate-100 font-medium">
                        </div>
                        <div>
                            <label class="lis-form-label font-bold text-slate-900">Dokter Penanggung Jawab Laboratorium</label>
                            <input type="text" name="dokter_pj" id="val-dokter-pj" value="<?= html_escape($dokter_sp_pk) ?>" class="lis-form-control font-bold text-xs text-slate-900 border-teal-300">
                        </div>
                        <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-[11px] text-amber-900 flex items-start gap-2">
                            <i data-lucide="info" class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5"></i>
                            <div>
                                <strong>Ketentuan Rilis Hasil:</strong> Pengesahan approval wajib dilakukan berjenjang sesuai nomor urut yang diatur di menu <em>Setting Approval</em>. Setiap tingkatan approval harus memiliki tanda tangan digital resmi.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Modal -->
                <div class="pt-3 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <button type="button" onclick="closeValidasiModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-100 transition-colors">
                        Tutup Lembar Review
                    </button>
                    
                    <div class="flex items-center gap-2">
                        <button type="submit" id="btn-save-catatan-klinis" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold border border-slate-300 transition-all flex items-center gap-2">
                            <i data-lucide="save" class="w-4 h-4 text-slate-600"></i>
                            <span>Simpan Ekspertise Klinis</span>
                        </button>
                        
                        <a id="btn-cetak-resmi-modal" href="#" target="_blank" class="hidden px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold shadow-md shadow-emerald-900/20 transition-all flex items-center gap-2 border border-emerald-600">
                            <i data-lucide="printer" class="w-4 h-4 text-amber-300"></i>
                            <span>Cetak Hasil Resmi (Lengkap TTD)</span>
                        </a>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: PENGESAHAN APPROVAL BERJENJANG DENGAN TANDA TANGAN DIGITAL        -->
<!-- ========================================================================= -->
<div id="modal-sign-step" class="lis-modal" style="z-index: 60;">
    <div class="lis-modal-box max-w-lg w-full">
        <!-- Header Modal -->
        <div class="lis-modal-header bg-gradient-to-r from-emerald-900 via-teal-900 to-slate-900 border-b border-amber-400/30">
            <div>
                <h3 class="lis-modal-title text-white">
                    <i data-lucide="stamp" class="w-5 h-5 text-amber-300"></i>
                    <span id="sign-modal-title">Pengesahan Approval Berjenjang</span>
                </h3>
                <p class="lis-modal-subtitle">Goreskan tanda tangan digital dan bubuhkan catatan pengesahan</p>
            </div>
            <button type="button" onclick="closeSignModal()" class="lis-modal-close" title="Tutup">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Form Tanda Tangan Step -->
        <form id="form-sign-step" onsubmit="submitStepApproval(event)" class="lis-modal-body p-5 space-y-4 bg-white">
            <input type="hidden" id="sign-pendaftaran-id" name="pendaftaran_id">
            <input type="hidden" id="sign-urutan" name="urutan">
            <input type="hidden" id="sign-signature-base64" name="signature_base64">

            <!-- Banner Pegawai Penandatangan -->
            <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center justify-center font-black text-base flex-shrink-0" id="sign-step-badge">
                    1
                </div>
                <div class="min-w-0">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-800 block" id="sign-jabatan-approval">Jabatan Approval</span>
                    <strong class="text-xs font-bold text-slate-900 block truncate" id="sign-pegawai-nama">Nama Pegawai</strong>
                    <span class="text-[10px] text-slate-500 font-mono" id="sign-pegawai-nip">NIP: -</span>
                </div>
            </div>

            <!-- Catatan Pengesahan -->
            <div>
                <label class="lis-form-label font-bold text-slate-900">Catatan Verifikasi / Keterangan Approval</label>
                <input type="text" name="catatan" id="sign-catatan" class="lis-form-control text-xs" placeholder="Contoh: Hasil sesuai SOP analitik & terverifikasi valid">
            </div>

            <!-- Tanda Tangan Digital Area -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="lis-form-label font-bold text-slate-900 mb-0">
                        Tanda Tangan Digital <span class="req">*</span>
                    </label>
                    <div id="sign-ttd-options-toggle" class="flex items-center gap-1.5 text-[11px]"></div>
                </div>

                <!-- Box Tanda Tangan Terdaftar (Jika Pegawai Sudah Punya TTD di Master) -->
                <div id="sign-existing-ttd-box" class="p-3 bg-teal-50/50 border border-teal-200 rounded-xl hidden">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-semibold text-teal-900 flex items-center gap-1.5">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5 text-teal-700"></i>
                            <span>Tanda Tangan Pegawai Terdaftar Tersedia</span>
                        </span>
                        <button type="button" onclick="switchToStepCanvas()" class="text-[10px] text-emerald-800 font-bold hover:underline">
                            ✍️ Goreskan TTD Baru di Canvas
                        </button>
                    </div>
                    <div class="h-24 bg-white border border-teal-200 rounded-lg flex items-center justify-center p-2 shadow-2xs">
                        <img id="sign-existing-ttd-img" src="" alt="TTD Master" class="max-h-full object-contain">
                    </div>
                    <span class="text-[10px] text-teal-800 mt-1 block">Tanda tangan master ini akan digunakan untuk pengesahan lembar hasil resmi.</span>
                </div>

                <!-- Box Canvas Signature Pad -->
                <div id="sign-canvas-box" class="space-y-2">
                    <div class="relative border-2 border-dashed border-slate-300 rounded-xl bg-slate-50 overflow-hidden">
                        <canvas id="step-signature-canvas" width="450" height="150" class="w-full bg-white cursor-crosshair touch-none"></canvas>
                        <div class="absolute bottom-2 left-2 text-[10px] text-slate-400 select-none pointer-events-none">
                            ✍️ Goreskan tanda tangan di area canvas
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] text-slate-500 font-medium">Tinta:</span>
                            <button type="button" onclick="setStepSignColor('#0f172a')" class="w-4 h-4 rounded-full bg-slate-900 border border-slate-300 shadow-2xs" title="Hitam"></button>
                            <button type="button" onclick="setStepSignColor('#1e40af')" class="w-4 h-4 rounded-full bg-blue-800 border border-slate-300 shadow-2xs" title="Biru"></button>
                        </div>
                        <button type="button" onclick="clearStepSignatureCanvas()" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10px] font-semibold border border-slate-200 transition-colors">
                            Bersihkan Canvas
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footer Action -->
            <div class="pt-3 border-t border-slate-200 flex items-center justify-between">
                <button type="button" onclick="closeSignModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-100 transition-colors">
                    Batal
                </button>
                <button type="submit" id="btn-submit-step-approval" class="px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold shadow-md shadow-emerald-900/20 transition-all flex items-center gap-2 border border-emerald-600">
                    <i data-lucide="check-check" class="w-4 h-4 text-amber-300"></i>
                    <span id="btn-submit-step-label">Sahkan & Tandatangani Approval</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let tableVal = null;
    let currentValFilter = 'siap';
    let currentReviewId = null;

    // Canvas State untuk Modal Step Approval
    let stepCanvas = null;
    let stepCtx = null;
    let isStepDrawing = false;
    let hasStepDrawn = false;
    let currentStepExistingTtd = null;
    let useExistingTtd = false;

    document.addEventListener('DOMContentLoaded', function() {
        initTable();
        initStepSignatureCanvas();
    });

    function initTable() {
        tableVal = $('#table-validasi').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?= site_url('validasi/fetch') ?>',
                type: 'GET',
                data: function(d) {
                    d.filter_status = currentValFilter;
                    d.tanggal = $('#filter-tanggal').val();
                }
            },
            columns: [
                { data: 'no_urut', orderable: false, className: 'text-center text-slate-400 font-medium py-3' },
                {
                    data: null,
                    render: function(d) {
                        return `<div>
                            <span class="inline-block font-mono text-[10px] font-bold px-2 py-0.5 rounded bg-teal-50 text-teal-900 border border-teal-200">${d.barcode_sampel}</span>
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
                            <span class="text-slate-800 font-medium block">${d.dokter_pengirim || d.instansi_pengirim || 'Umum'}</span>
                            <span class="text-[10px] text-slate-500">${d.jenis_sampel || 'Darah'}</span>
                        </div>`;
                    }
                },
                {
                    data: null,
                    render: function(d) {
                        let panicBadge = '';
                        if (d.has_panic) {
                            panicBadge = `<span class="inline-block mt-0.5 px-2 py-0.5 rounded bg-rose-100 text-rose-900 border border-rose-300 font-black text-[9px] animate-pulse">
                                ⚠️ NILAI KRITIS: ${d.panic_items}
                            </span>`;
                        }
                        return `<div>
                            <span class="text-slate-800 font-semibold block">${d.filled_items}/${d.total_items} parameter diisi</span>
                            ${panicBadge}
                        </div>`;
                    }
                },
                {
                    data: null,
                    className: 'text-center py-3',
                    render: function(d) {
                        const total = d.total_approval_steps || 0;
                        const approved = d.approved_steps || 0;

                        if (d.status_order === 'completed') {
                            return `<div class="space-y-1">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 inline-flex items-center justify-center gap-1">
                                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-700"></i>
                                    <span>Terotorisasi (Selesai)</span>
                                </span>
                                <span class="text-[9px] text-emerald-700 block font-semibold">Approval Lengkap (${approved}/${total} Tingkat)</span>
                            </div>`;
                        } else if (d.status_order === 'validasi') {
                            return `<div class="space-y-1">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300 inline-flex items-center justify-center gap-1">
                                    <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-700"></i>
                                    <span>Approval: ${approved}/${total} Tingkat</span>
                                </span>
                                <span class="text-[9px] text-amber-950 font-bold block truncate max-w-[130px] mx-auto" title="Menunggu approval: ${d.current_pending_role || '-'}">
                                    Menunggu: ${d.current_pending_role || '-'}
                                </span>
                            </div>`;
                        } else {
                            return `<span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-300 inline-flex items-center justify-center gap-1">
                                <i data-lucide="flask-conical" class="w-3.5 h-3.5"></i>
                                <span>Draft Analis</span>
                            </span>`;
                        }
                    }
                },
                {
                    data: null,
                    orderable: false,
                    className: 'text-center py-3',
                    render: function(d) {
                        const btnReview = `<button type="button" onclick="openReviewValidasi(${d.id})" class="px-2.5 py-1.5 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white text-[10px] font-bold transition-all shadow-xs flex items-center gap-1 border border-emerald-600" title="Review Hasil & Approval Berjenjang">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5 text-amber-300"></i>
                            <span>${d.status_order === 'completed' ? 'Lihat Ekspertise' : 'Review & Approval'}</span>
                        </button>`;

                        let btnPrint = '';
                        if (d.status_order === 'completed') {
                            btnPrint = `<a href="<?= site_url('pendaftaran/cetak_hasil') ?>/${d.id}" target="_blank" class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-300 text-[10px] font-bold transition-all shadow-2xs" title="Cetak Lembar Resmi Hasil Lab Lengkap Tanda Tangan">
                                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                            </a>`;
                        }

                        return `<div class="flex items-center justify-center gap-1.5">
                            ${btnReview}
                            ${btnPrint}
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
        if (tableVal) tableVal.ajax.reload(null, false);
    }

    function setFilter(filter) {
        currentValFilter = filter;
        ['siap', 'completed', 'all'].forEach(f => {
            const btn = document.getElementById('btn-f-' + f);
            if (btn) {
                if (f === filter) {
                    btn.className = 'px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all bg-emerald-800 text-white shadow-xs';
                } else {
                    btn.className = 'px-3.5 py-1.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 transition-all';
                }
            }
        });
        reloadTable();
    }

    function openReviewValidasi(id) {
        currentReviewId = id;

        LIS.fetchJson('<?= site_url('validasi/get_detail_validasi') ?>/' + id)
            .then(res => {
                if (res.status === 'success') {
                    const reg = res.data.pendaftaran;
                    const items = res.data.items;
                    const approvals = res.data.approvals || [];

                    document.getElementById('val-reg-id').value = reg.id;
                    document.getElementById('val-barcode').textContent = reg.barcode_sampel;
                    document.getElementById('val-reg-num').textContent = reg.nomor_registrasi;
                    document.getElementById('val-patient-name').textContent = reg.nama_pasien;
                    document.getElementById('val-info-extra').textContent = (reg.jenis_kelamin || '-') + ' / ' + (reg.usia_tahun || '-') + ' Thn / ' + (reg.jenis_sampel || 'Darah');

                    document.getElementById('val-catatan-teknis').value = reg.catatan_hasil || '';
                    document.getElementById('val-kesimpulan').value = reg.kesimpulan_saran || reg.kesimpulan_klinis || 'Dalam batas normal / sesuai rujukan klinis.';
                    document.getElementById('val-analis-nama').value = reg.analis_nama || '-';
                    if (reg.dokter_pj) document.getElementById('val-dokter-pj').value = reg.dokter_pj;

                    // Tombol Cetak Hasil di Modal
                    const btnCetak = document.getElementById('btn-cetak-resmi-modal');
                    const keabsahan = res.data.keabsahan || { is_sah: false, mode_belum_lengkap: 'watermark' };
                    btnCetak.onclick = null;

                    if (keabsahan.is_sah) {
                        btnCetak.href = '<?= site_url('pendaftaran/cetak_hasil') ?>/' + reg.id;
                        btnCetak.className = 'px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold shadow-md shadow-emerald-900/20 transition-all flex items-center gap-2 border border-emerald-600';
                        btnCetak.innerHTML = '<i data-lucide="printer" class="w-4 h-4 text-amber-300"></i><span>Cetak Lembar Resmi (SAH)</span>';
                        btnCetak.classList.remove('hidden');
                    } else {
                        if (keabsahan.mode_belum_lengkap === 'block') {
                            btnCetak.href = 'javascript:void(0)';
                            btnCetak.className = 'px-4 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-800 text-xs font-bold border border-rose-200 transition-all flex items-center gap-2 cursor-pointer';
                            btnCetak.innerHTML = '<i data-lucide="lock" class="w-4 h-4 text-rose-600"></i><span>Hasil Belum Sah (Masa Berjenjang)</span>';
                            btnCetak.onclick = function() {
                                Swal.fire({
                                    title: 'Hasil Lab Belum Sah!',
                                    html: 'Pemeriksaan ini belum dapat dicetak sebagai hasil sah karena proses verifikasi berjenjang masih berjalan dan belum semua pihak membubuhkan tanda tangan digital.<br><br><span class="text-xs text-slate-500">Silakan selesaikan pengesahan approval berjenjang terlebih dahulu.</span>',
                                    icon: 'warning',
                                    confirmButtonText: 'Mengerti',
                                    confirmButtonColor: '#b91c1c'
                                });
                            };
                            btnCetak.classList.remove('hidden');
                        } else {
                            btnCetak.href = '<?= site_url('pendaftaran/cetak_hasil') ?>/' + reg.id;
                            btnCetak.className = 'px-4 py-2.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-900 text-xs font-bold border border-amber-300 transition-all flex items-center gap-2';
                            btnCetak.innerHTML = '<i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600"></i><span>Cetak Draft (Belum Sah - Watermark)</span>';
                            btnCetak.classList.remove('hidden');
                        }
                    }

                    // Render Tabel Hasil Uji
                    const tbody = document.getElementById('val-table-body');
                    let html = '';
                    items.forEach((it, idx) => {
                        const valHasil = (it.hasil_nilai !== null && it.hasil_nilai !== undefined && it.hasil_nilai !== '') ? it.hasil_nilai : (it.hasil || '-');
                        let flagVal = (it.hasil_flag || it.flag_kritis || 'normal').toLowerCase();

                        let flagBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">Normal</span>';
                        if (flagVal === 'l' || flagVal === 'low') {
                            flagBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300">Low (L)</span>';
                        } else if (flagVal === 'h' || flagVal === 'high') {
                            flagBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300">High (H)</span>';
                        } else if (flagVal === 'critical' || flagVal === 'l*' || flagVal === 'h*') {
                            flagBadge = '<span class="px-2 py-0.5 rounded text-[10px] font-black bg-rose-100 text-rose-900 border border-rose-300 animate-pulse">KRITIS (Panic)</span>';
                        }

                        const catatanVal = it.catatan_hasil || it.catatan_pemeriksaan || '-';

                        html += `<tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-3 text-center text-slate-400 font-medium">${idx + 1}</td>
                            <td class="p-3">
                                <strong class="text-slate-900 block">${it.nama_item}</strong>
                                <span class="text-[10px] text-slate-500">${it.kategori_lab || '-'}</span>
                            </td>
                            <td class="p-3 font-mono font-bold text-sm text-slate-900">${valHasil}</td>
                            <td class="p-3 font-mono text-slate-600">${it.satuan || '-'}</td>
                            <td class="p-3 font-mono text-slate-600">${it.nilai_rujukan || '-'}</td>
                            <td class="p-3 text-center">${flagBadge}</td>
                            <td class="p-3 text-[11px] text-slate-500">${catatanVal}</td>
                        </tr>`;
                    });
                    tbody.innerHTML = html;

                    // Render Approval Pipeline Berjenjang
                    renderApprovalPipeline(approvals, reg);

                    const modal = document.getElementById('modal-validasi-review');
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    modal.style.display = 'flex';
                    if (window.lucide) lucide.createIcons();
                } else {
                    LIS.showError('Gagal Memuat Detail Validasi', res, res.message || 'Gagal memuat rincian hasil pengujian.');
                }
            })
            .catch(err => {
                LIS.showError('Gagal Memuat Detail Validasi', err, 'Terjadi kendala saat mengambil detail pemeriksaan.');
            });
    }

    function renderApprovalPipeline(approvals, reg) {
        const container = document.getElementById('val-approval-pipeline');
        const badgeContainer = document.getElementById('val-approval-status-badge');
        const countBadge = document.getElementById('val-pipeline-count');

        if (!approvals || approvals.length === 0) {
            countBadge.textContent = '0 Tingkat';
            badgeContainer.innerHTML = '<span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600">Belum ada aturan approval</span>';
            container.innerHTML = `<div class="sm:col-span-4 p-4 text-center text-slate-400 text-xs bg-white rounded-xl border border-slate-200">
                Aturan approval belum dikonfigurasi. Silakan atur di menu <strong>Pengaturan Sistem &gt; Setting Approval</strong>.
            </div>`;
            return;
        }

        countBadge.textContent = approvals.length + ' Tingkat Approval';

        let approvedCount = 0;
        let htmlCards = '';

        approvals.forEach((app) => {
            const isApproved = (app.status === 'approved');
            const isPending = (app.status === 'pending');
            const isWaiting = (app.status === 'waiting');

            if (isApproved) approvedCount++;

            let cardBorder = 'border-slate-200 bg-slate-50/50 opacity-80';
            let statusBadge = `<span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-slate-200 text-slate-600">Menunggu</span>`;
            let ttdArea = '';
            let actionBtn = '';

            if (isApproved) {
                cardBorder = 'border-emerald-300 bg-emerald-50/40 shadow-2xs';
                statusBadge = `<span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1">
                    <i data-lucide="check" class="w-3 h-3 text-emerald-700"></i>
                    <span>Disahkan</span>
                </span>`;

                let imgTtd = '';
                if (app.tanda_tangan) {
                    imgTtd = `<img src="<?= base_url() ?>${app.tanda_tangan}" alt="TTD" class="max-h-full object-contain">`;
                } else {
                    imgTtd = `<span class="text-[10px] text-emerald-700 font-bold italic">Tervalidasi Digital</span>`;
                }

                ttdArea = `<div class="h-16 bg-white rounded-lg border border-emerald-200 flex items-center justify-center p-1.5 my-1.5 shadow-2xs">
                    ${imgTtd}
                </div>
                <div class="text-[9px] text-slate-500 space-y-0.5">
                    <div>Waktu: <strong class="text-slate-700">${app.waktu_approval ? app.waktu_approval.substring(0, 16) : '-'}</strong></div>
                    ${app.catatan ? `<div class="truncate text-slate-600 italic" title="${app.catatan}">"${app.catatan}"</div>` : ''}
                </div>`;
            } else if (isPending) {
                cardBorder = 'border-amber-400 bg-amber-50/50 shadow-xs ring-2 ring-amber-300/50';
                statusBadge = `<span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-100 text-amber-900 border border-amber-300 flex items-center gap-1 animate-pulse">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                    <span>Giliran Aktif</span>
                </span>`;

                actionBtn = `<button type="button" onclick="openSignStepModal(${app.pendaftaran_id}, ${app.urutan}, '${escapeHtml(app.jabatan_approval)}', '${escapeHtml(app.pegawai_nama)}', '${escapeHtml(app.pegawai_nip || '-')}', '${escapeHtml(app.tanda_tangan || '')}')" class="w-full mt-2 py-1.5 px-3 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white text-[11px] font-bold shadow-xs flex items-center justify-center gap-1.5 border border-emerald-600 transition-all">
                    <i data-lucide="stamp" class="w-3.5 h-3.5 text-amber-300"></i>
                    <span>Sahkan Tingkat ${app.urutan}</span>
                </button>`;

                ttdArea = `<div class="h-16 rounded-lg border border-dashed border-amber-300 bg-amber-50/40 flex flex-col items-center justify-center p-1.5 my-1.5 text-center">
                    <span class="text-[10px] text-amber-900 font-semibold">Menunggu Tanda Tangan</span>
                    <span class="text-[9px] text-amber-700">Wajib bertanda tangan</span>
                </div>`;
            } else if (isWaiting) {
                cardBorder = 'border-slate-200 bg-slate-50/40 opacity-70';
                statusBadge = `<span class="px-2 py-0.5 rounded-full text-[9px] font-medium bg-slate-100 text-slate-500 border border-slate-200">
                    Antrean Urutan ${app.urutan}
                </span>`;

                ttdArea = `<div class="h-16 rounded-lg border border-slate-200 bg-slate-100/60 flex items-center justify-center p-1.5 my-1.5 text-slate-400 text-[10px] font-medium gap-1">
                    <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                    <span>Terkunci</span>
                </div>`;
            }

            htmlCards += `<div class="p-3 rounded-xl border ${cardBorder} flex flex-col justify-between transition-all">
                <div>
                    <div class="flex items-center justify-between gap-1 mb-1.5">
                        <span class="font-mono text-[10px] font-black px-1.5 py-0.5 rounded bg-slate-900 text-white">#${app.urutan}</span>
                        ${statusBadge}
                    </div>
                    <strong class="text-xs text-slate-900 block font-bold leading-tight truncate" title="${app.jabatan_approval}">${app.jabatan_approval}</strong>
                    <div class="text-[10px] text-slate-600 truncate mt-0.5" title="${app.pegawai_nama}">${app.pegawai_nama}</div>
                    <div class="text-[9px] text-slate-400 font-mono">NIP: ${app.pegawai_nip || '-'}</div>
                    ${ttdArea}
                </div>
                ${actionBtn}
            </div>`;
        });

        container.innerHTML = htmlCards;

        // Render Status Badge Utama & Keabsahan
        let signedCount = 0;
        approvals.forEach(app => {
            if (app.status === 'approved' && app.tanda_tangan) signedCount++;
        });
        const isSah = (approvals.length > 0 && signedCount === approvals.length);

        if (isSah) {
            badgeContainer.innerHTML = `<span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1.5 shadow-2xs">
                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-700"></i>
                <span>HASIL RESMI SAH &bull; Seluruh ${approvals.length} Approval Lengkap Bertandatangan</span>
            </span>`;
        } else {
            badgeContainer.innerHTML = `<span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-800 border border-rose-300 flex items-center gap-1.5 shadow-2xs">
                <i data-lucide="shield-alert" class="w-4 h-4 text-rose-600"></i>
                <span>BELUM SAH &bull; ${signedCount} dari ${approvals.length} Tingkat Bertandatangan</span>
            </span>`;
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function closeValidasiModal() {
        const modal = document.getElementById('modal-validasi-review');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        modal.style.display = 'none';
    }

    // =========================================================================
    // CANVAS SIGNATURE STEP APPROVAL
    // =========================================================================
    function initStepSignatureCanvas() {
        stepCanvas = document.getElementById('step-signature-canvas');
        if (!stepCanvas) return;
        stepCtx = stepCanvas.getContext('2d');
        stepCtx.strokeStyle = '#0f172a';
        stepCtx.lineWidth = 2.5;
        stepCtx.lineCap = 'round';
        stepCtx.lineJoin = 'round';

        function getPos(e) {
            const rect = stepCanvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            const scaleX = stepCanvas.width / rect.width;
            const scaleY = stepCanvas.height / rect.height;
            return {
                x: (clientX - rect.left) * scaleX,
                y: (clientY - rect.top) * scaleY
            };
        }

        function startDraw(e) {
            isStepDrawing = true;
            hasStepDrawn = true;
            const pos = getPos(e);
            stepCtx.beginPath();
            stepCtx.moveTo(pos.x, pos.y);
            if (e.cancelable) e.preventDefault();
        }

        function draw(e) {
            if (!isStepDrawing) return;
            const pos = getPos(e);
            stepCtx.lineTo(pos.x, pos.y);
            stepCtx.stroke();
            if (e.cancelable) e.preventDefault();
        }

        function stopDraw() {
            if (isStepDrawing) {
                isStepDrawing = false;
                document.getElementById('sign-signature-base64').value = stepCanvas.toDataURL('image/png');
            }
        }

        stepCanvas.addEventListener('mousedown', startDraw);
        stepCanvas.addEventListener('mousemove', draw);
        stepCanvas.addEventListener('mouseup', stopDraw);
        stepCanvas.addEventListener('mouseleave', stopDraw);

        stepCanvas.addEventListener('touchstart', startDraw, { passive: false });
        stepCanvas.addEventListener('touchmove', draw, { passive: false });
        stepCanvas.addEventListener('touchend', stopDraw);
    }

    function clearStepSignatureCanvas() {
        if (!stepCtx || !stepCanvas) return;
        stepCtx.clearRect(0, 0, stepCanvas.width, stepCanvas.height);
        hasStepDrawn = false;
        document.getElementById('sign-signature-base64').value = '';
    }

    function setStepSignColor(color) {
        if (stepCtx) stepCtx.strokeStyle = color;
    }

    function switchToStepCanvas() {
        useExistingTtd = false;
        document.getElementById('sign-existing-ttd-box').classList.add('hidden');
        document.getElementById('sign-canvas-box').classList.remove('hidden');
        clearStepSignatureCanvas();
    }

    function openSignStepModal(pendaftaranId, urutan, jabatanApproval, pegawaiNama, pegawaiNip, existingTtd) {
        document.getElementById('form-sign-step').reset();
        document.getElementById('sign-pendaftaran-id').value = pendaftaranId;
        document.getElementById('sign-urutan').value = urutan;
        document.getElementById('sign-signature-base64').value = '';

        document.getElementById('sign-step-badge').textContent = urutan;
        document.getElementById('sign-jabatan-approval').textContent = jabatanApproval;
        document.getElementById('sign-pegawai-nama').textContent = pegawaiNama;
        document.getElementById('sign-pegawai-nip').textContent = 'NIP: ' + pegawaiNip;
        document.getElementById('sign-modal-title').textContent = 'Pengesahan Approval Tingkat #' + urutan;
        document.getElementById('btn-submit-step-label').textContent = 'Sahkan Tingkat ' + urutan + ' & Lanjutkan';

        clearStepSignatureCanvas();

        currentStepExistingTtd = existingTtd;
        const boxExisting = document.getElementById('sign-existing-ttd-box');
        const boxCanvas = document.getElementById('sign-canvas-box');

        if (existingTtd && existingTtd.trim() !== '') {
            useExistingTtd = true;
            document.getElementById('sign-existing-ttd-img').src = '<?= base_url() ?>' + existingTtd;
            boxExisting.classList.remove('hidden');
            boxCanvas.classList.add('hidden');
        } else {
            useExistingTtd = false;
            boxExisting.classList.add('hidden');
            boxCanvas.classList.remove('hidden');
        }

        const modal = document.getElementById('modal-sign-step');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.style.display = 'flex';
        if (window.lucide) lucide.createIcons();
    }

    function closeSignModal() {
        const modal = document.getElementById('modal-sign-step');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        modal.style.display = 'none';
    }

    function submitStepApproval(e) {
        e.preventDefault();
        const pendaftaranId = document.getElementById('sign-pendaftaran-id').value;
        const urutan = document.getElementById('sign-urutan').value;
        const catatan = document.getElementById('sign-catatan').value;
        const btnSubmit = document.getElementById('btn-submit-step-approval');
        const origBtnText = btnSubmit.innerHTML;

        // Validasi tanda tangan wajib
        let signatureBase64 = document.getElementById('sign-signature-base64').value;
        if (!useExistingTtd && (!hasStepDrawn || !signatureBase64)) {
            LIS.showError('Tanda Tangan Wajib Diisi', null, 'Setiap approval wajib memiliki tanda tangan digital! Silakan goreskan tanda tangan pada canvas.');
            return;
        }

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="inline-block animate-spin mr-1">⌛</span> Memproses Pengesahan...';

        const fd = new FormData();
        fd.append('pendaftaran_id', pendaftaranId);
        fd.append('urutan', urutan);
        fd.append('catatan', catatan);

        // Sertakan kesimpulan klinis jika diisi di form review
        const kesimpulanVal = document.getElementById('val-kesimpulan').value;
        if (kesimpulanVal && kesimpulanVal.trim() !== '') {
            fd.append('kesimpulan_klinis', kesimpulanVal);
        }

        if (!useExistingTtd && signatureBase64) {
            fd.append('signature_base64', signatureBase64);
        }

        LIS.fetchJson('<?= site_url('validasi/approve_step') ?>', {
            method: 'POST',
            body: fd
        })
        .then(res => {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = origBtnText;

            if (res.status === true || res.status === 'success') {
                closeSignModal();
                LIS.toast('success', res.message || 'Approval tingkat #' + urutan + ' berhasil disahkan.');
                
                // Refresh modal review & table
                openReviewValidasi(pendaftaranId);
                reloadTable();
            } else {
                LIS.showError('Gagal Approval', res, res.message || 'Gagal memproses approval.');
            }
        })
        .catch(err => {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = origBtnText;
            LIS.showError('Error Approval', err, 'Terjadi kendala sistem saat memproses approval berjenjang.');
        });
    }

    function submitCatatanKlinis(e) {
        e.preventDefault();
        const form = document.getElementById('form-catatan-klinis');
        const btnSave = document.getElementById('btn-save-catatan-klinis');
        const origBtnText = btnSave.innerHTML;

        btnSave.disabled = true;
        btnSave.innerHTML = '<span class="inline-block animate-spin mr-1">⌛</span> Menyimpan...';

        const formData = new FormData(form);

        LIS.fetchJson('<?= site_url('validasi/save_catatan_klinis') ?>', {
            method: 'POST',
            body: formData
        })
        .then(res => {
            btnSave.disabled = false;
            btnSave.innerHTML = origBtnText;

            if (res.status === 'success' || res.status === true) {
                LIS.toast('success', res.message || 'Catatan & ekspertise klinis berhasil diperbarui.');
            } else {
                LIS.showError('Gagal Menyimpan', res, res.message || 'Gagal menyimpan ekspertise klinis.');
            }
        })
        .catch(err => {
            btnSave.disabled = false;
            btnSave.innerHTML = origBtnText;
            LIS.showError('Error Simpan', err, 'Terjadi kesalahan sistem saat menyimpan ekspertise klinis.');
        });
    }
</script>

