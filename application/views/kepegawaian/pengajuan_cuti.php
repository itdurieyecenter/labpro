<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-slate-400">Kepegawaian</span>
                <span>/</span>
                <span class="text-emerald-800 font-semibold">Pengajuan Cuti</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="calendar-days" class="w-6 h-6"></i>
                </span>
                <span>Permohonan Cuti Pegawai</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Diinput oleh Tata Usaha atas nama pegawai, lalu diteruskan ke alur persetujuan berjenjang</p>
        </div>

        <?php if ($can_create): ?>
            <button type="button" onclick="openCutiModal()" class="px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-2 shadow-sm transition-all border border-emerald-600">
                <i data-lucide="file-plus" class="w-4 h-4"></i>
                <span>+ Buat Permohonan Cuti</span>
            </button>
        <?php endif; ?>
    </div>

    <?php if ($jumlah_tahap_approval === 0): ?>
        <div class="bg-amber-50 border border-amber-300 rounded-2xl p-5 flex items-start gap-3">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-700 shrink-0 mt-0.5"></i>
            <div class="text-xs text-amber-900">
                <strong class="block mb-1">Hirarki approval belum dikonfigurasi.</strong>
                Permohonan masih bisa disimpan sebagai draft, tetapi belum dapat dikirim ke persetujuan.
                <a href="<?= site_url('kepegawaian/approval_setting') ?>" class="underline font-bold">Atur hirarki approval lebih dulu</a>.
            </div>
        </div>
    <?php endif; ?>

    <!-- Ringkasan -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <?php
        $kartu = [
            ['label' => 'Draft', 'value' => $summary['draft'], 'icon' => 'file-text', 'warna' => 'slate'],
            ['label' => 'Menunggu Approval', 'value' => $summary['menunggu_approval'], 'icon' => 'clock', 'warna' => 'amber'],
            ['label' => 'Disetujui', 'value' => $summary['disetujui'], 'icon' => 'check-check', 'warna' => 'emerald'],
            ['label' => 'Ditolak', 'value' => $summary['ditolak'], 'icon' => 'x-circle', 'warna' => 'rose'],
            ['label' => 'Total Hari Disetujui', 'value' => $summary['total_hari_disetujui'], 'icon' => 'calendar-check', 'warna' => 'teal'],
        ];
        foreach ($kartu as $k): ?>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider"><?= $k['label'] ?></span>
                    <i data-lucide="<?= $k['icon'] ?>" class="w-4 h-4 text-<?= $k['warna'] ?>-600"></i>
                </div>
                <span class="text-2xl font-black text-slate-900"><?= (int)$k['value'] ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Daftar Pengajuan -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center gap-3 justify-between">
            <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="list" class="w-4 h-4 text-emerald-700"></i>
                Daftar Permohonan Cuti
            </h2>
            <div class="flex items-center gap-2 flex-wrap">
                <select id="filter-status" class="lis-form-control py-1.5 text-xs w-auto">
                    <option value="all">Semua Status</option>
                    <option value="draft">Draft</option>
                    <option value="diajukan">Diajukan</option>
                    <option value="proses">Dalam Proses</option>
                    <option value="disetujui">Disetujui</option>
                    <option value="ditolak">Ditolak</option>
                    <option value="dibatalkan">Dibatalkan</option>
                </select>
                <select id="filter-tahun" class="lis-form-control py-1.5 text-xs w-auto">
                    <?php foreach ($tahun_list as $t): ?>
                        <option value="<?= (int)$t ?>" <?= (int)$t === (int)date('Y') ? 'selected' : '' ?>><?= (int)$t ?></option>
                    <?php endforeach; ?>
                    <option value="all">Semua Tahun</option>
                </select>
                <select id="filter-jenis" class="lis-form-control py-1.5 text-xs w-auto">
                    <option value="all">Semua Jenis</option>
                    <?php foreach ($jenis_cuti as $j): ?>
                        <option value="<?= (int)$j['id'] ?>"><?= html_escape($j['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="p-4 sm:p-5 overflow-x-auto">
            <table id="table-cuti" class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-bold bg-slate-50">
                        <th class="py-3 px-3 w-12 text-center">No</th>
                        <th class="py-3 px-3">Nomor / Pegawai</th>
                        <th class="py-3 px-3">Jenis Cuti</th>
                        <th class="py-3 px-3">Periode</th>
                        <th class="py-3 px-3 text-center">Hari</th>
                        <th class="py-3 px-3">Status & Progres</th>
                        <th class="py-3 px-3 text-center w-40">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Permohonan Cuti -->
<div id="modal-cuti" class="lis-modal">
    <div class="lis-modal-box max-w-3xl w-full">
        <div class="lis-modal-header bg-gradient-to-r from-emerald-900 to-teal-900 border-b border-amber-400/40">
            <h3 class="lis-modal-title text-white flex items-center gap-2">
                <i data-lucide="calendar-days" class="w-5 h-5 text-amber-300"></i>
                <span id="modal-cuti-title">Buat Permohonan Cuti</span>
            </h3>
            <button type="button" onclick="closeCutiModal()" class="lis-modal-close"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>

        <form id="form-cuti" onsubmit="saveCuti(event)" class="lis-modal-body p-5 space-y-4">
            <input type="hidden" id="cuti-id" name="id">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="lis-form-label">Pegawai Pemohon <span class="req">*</span></label>
                    <select name="pegawai_id" id="cuti-pegawai" required class="lis-form-control" onchange="muatSaldo()">
                        <option value="">-- Pilih Pegawai --</option>
                        <?php foreach ($pegawai_options as $p): ?>
                            <option value="<?= (int)$p['id'] ?>"><?= html_escape($p['nama_formatted']) ?> &mdash; <?= html_escape($p['nip'] ?: 'Tanpa NIP') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="lis-form-label">Jenis Cuti <span class="req">*</span></label>
                    <select name="jenis_cuti_id" id="cuti-jenis" required class="lis-form-control" onchange="hitungHari()">
                        <option value="">-- Pilih Jenis Cuti --</option>
                        <?php foreach ($jenis_cuti as $j): ?>
                            <option value="<?= (int)$j['id'] ?>"
                                    data-saldo="<?= (int)$j['mengurangi_saldo'] ?>"
                                    data-lampiran="<?= (int)$j['wajib_lampiran'] ?>"
                                    data-max="<?= (int)$j['max_hari'] ?>">
                                <?= html_escape($j['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="lis-form-hint" id="hint-jenis">&nbsp;</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="lis-form-label">Tanggal Mulai <span class="req">*</span></label>
                    <input type="date" name="tanggal_mulai" id="cuti-mulai" required class="lis-form-control" onchange="hitungHari()">
                </div>
                <div>
                    <label class="lis-form-label">Tanggal Selesai <span class="req">*</span></label>
                    <input type="date" name="tanggal_selesai" id="cuti-selesai" required class="lis-form-control" onchange="hitungHari()">
                </div>
                <div>
                    <label class="lis-form-label">Tahun Hak Cuti</label>
                    <select name="tahun_hak" id="cuti-tahun" class="lis-form-control" onchange="muatSaldo()">
                        <?php for ($t = (int)date('Y') + 1; $t >= (int)date('Y') - 2; $t--): ?>
                            <option value="<?= $t ?>" <?= $t === (int)date('Y') ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <!-- Panel Saldo & Perhitungan -->
            <div id="panel-saldo" class="bg-slate-50 border border-slate-200 rounded-xl p-4 hidden">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Hak Cuti Tahunan & Perhitungan</span>
                    <span id="saldo-tahun" class="text-[10px] text-slate-500"></span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 mb-3">
                    <div class="bg-white p-2.5 rounded-lg border border-slate-200 text-center">
                        <span class="block text-[10px] text-slate-500 font-semibold">Sisa N-2</span>
                        <span id="saldo-n2" class="text-lg font-black text-slate-900">0</span>
                    </div>
                    <div class="bg-white p-2.5 rounded-lg border border-slate-200 text-center">
                        <span class="block text-[10px] text-slate-500 font-semibold">Sisa N-1</span>
                        <span id="saldo-n1" class="text-lg font-black text-slate-900">0</span>
                    </div>
                    <div class="bg-white p-2.5 rounded-lg border border-slate-200 text-center">
                        <span class="block text-[10px] text-slate-500 font-semibold">Tahun Berjalan</span>
                        <span id="saldo-n" class="text-lg font-black text-slate-900">0</span>
                    </div>
                    <div class="bg-emerald-50 p-2.5 rounded-lg border border-emerald-200 text-center">
                        <span class="block text-[10px] text-emerald-700 font-semibold">Total Tersedia</span>
                        <span id="saldo-total" class="text-lg font-black text-emerald-800">0</span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 text-xs border-t border-slate-200 pt-3">
                    <span class="font-semibold text-slate-600">Lama cuti:</span>
                    <span id="hasil-hari" class="px-2.5 py-1 rounded-lg bg-emerald-700 text-amber-200 font-black">0 hari kerja</span>
                    <span id="hasil-alokasi" class="text-slate-500"></span>
                </div>

                <p id="hasil-libur" class="text-[10px] text-slate-500 mt-2"></p>
                <p id="hasil-peringatan" class="text-[11px] text-rose-700 font-semibold mt-2 hidden"></p>
            </div>

            <div>
                <label class="lis-form-label">Alasan Permohonan Cuti <span class="req">*</span></label>
                <textarea name="alasan" id="cuti-alasan" rows="2" required maxlength="500" class="lis-form-control" placeholder="Contoh: Keperluan keluarga di luar kota"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                    <label class="lis-form-label">Alamat Selama Menjalankan Cuti</label>
                    <input type="text" name="alamat_selama_cuti" id="cuti-alamat" class="lis-form-control" placeholder="Alamat lengkap tempat pegawai dapat dihubungi">
                </div>
                <div>
                    <label class="lis-form-label">Telepon Selama Cuti</label>
                    <input type="tel" name="telepon_selama_cuti" id="cuti-telepon" class="lis-form-control font-mono" placeholder="08...">
                </div>
            </div>

            <div>
                <label class="lis-form-label">Lampiran Pendukung <span id="lampiran-wajib" class="req hidden">*</span></label>
                <input type="file" name="lampiran" id="cuti-lampiran" accept=".pdf,.jpg,.jpeg,.png" class="lis-form-control">
                <p class="lis-form-hint">Format PDF/JPG/PNG maksimal 4 MB. Wajib untuk cuti sakit, melahirkan, dan CLTN.</p>
                <p id="lampiran-existing" class="text-[11px] text-emerald-700 font-semibold mt-1 hidden"></p>
            </div>

            <div class="pt-3 border-t border-slate-200 flex items-center justify-between">
                <button type="button" onclick="closeCutiModal()" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan sebagai Draft</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Detail -->
<div id="modal-detail" class="lis-modal">
    <div class="lis-modal-box max-w-2xl w-full">
        <div class="lis-modal-header bg-gradient-to-r from-slate-800 to-slate-900 border-b border-amber-400/40">
            <h3 class="lis-modal-title text-white flex items-center gap-2">
                <i data-lucide="file-search" class="w-5 h-5 text-amber-300"></i>
                <span>Detail Permohonan Cuti</span>
            </h3>
            <button type="button" onclick="closeDetailModal()" class="lis-modal-close"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <div id="detail-body" class="lis-modal-body p-5 space-y-4"></div>
    </div>
</div>

<script>
    const CUTI_URL = {
        fetch: '<?= site_url('kepegawaian/fetch') ?>',
        get: '<?= site_url('kepegawaian/get') ?>',
        save: '<?= site_url('kepegawaian/save') ?>',
        hitung: '<?= site_url('kepegawaian/hitung_hari') ?>',
        saldo: '<?= site_url('kepegawaian/saldo') ?>',
        ajukan: '<?= site_url('kepegawaian/ajukan') ?>',
        batalkan: '<?= site_url('kepegawaian/batalkan') ?>',
        hapus: '<?= site_url('kepegawaian/hapus') ?>',
        cetak: '<?= site_url('kepegawaian/cetak_surat') ?>'
    };

    const BADGE_STATUS = {
        draft:      ['bg-slate-100 text-slate-600 border-slate-200', 'Draft'],
        diajukan:   ['bg-amber-50 text-amber-700 border-amber-200', 'Diajukan'],
        proses:     ['bg-sky-50 text-sky-700 border-sky-200', 'Dalam Proses'],
        disetujui:  ['bg-emerald-50 text-emerald-700 border-emerald-200', 'Disetujui'],
        ditolak:    ['bg-rose-50 text-rose-700 border-rose-200', 'Ditolak'],
        dibatalkan: ['bg-slate-100 text-slate-500 border-slate-200', 'Dibatalkan']
    };

    let tableCuti = null;

    function formatTanggal(iso) {
        if (!iso) return '-';
        const bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const d = new Date(iso + 'T00:00:00');
        if (isNaN(d)) return iso;
        return d.getDate() + ' ' + bulan[d.getMonth()] + ' ' + d.getFullYear();
    }

    function badgeStatus(status) {
        const b = BADGE_STATUS[status] || ['bg-slate-100 text-slate-600 border-slate-200', status];
        return '<span class="px-2 py-0.5 rounded text-[10px] font-bold border ' + b[0] + '">' + b[1] + '</span>';
    }

    // ---------- Tabel ----------
    $(function () {
        tableCuti = $('#table-cuti').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: CUTI_URL.fetch,
                type: 'GET',
                data: function (d) {
                    d.status = $('#filter-status').val() || 'all';
                    d.tahun = $('#filter-tahun').val() || 'all';
                    d.jenis_cuti_id = $('#filter-jenis').val() || 'all';
                }
            },
            order: [],
            columns: [
                {
                    data: null, orderable: false, className: 'text-center text-slate-400 py-3',
                    render: (d, t, r, meta) => meta.row + meta.settings._iDisplayStart + 1
                },
                {
                    data: 'nomor_pengajuan', className: 'py-3',
                    render: (data, type, row) =>
                        '<div class="font-mono font-bold text-slate-900">' + data + '</div>' +
                        '<div class="text-[11px] text-slate-700 font-semibold">' + row.pegawai_nama + '</div>' +
                        '<div class="text-[10px] text-slate-400 font-mono">NIP. ' + (row.pegawai_nip || '-') + '</div>'
                },
                {
                    data: 'jenis_cuti_nama', className: 'py-3',
                    render: (data, type, row) =>
                        '<span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">' + data + '</span>' +
                        '<div class="text-[10px] text-slate-400 mt-0.5">Hak tahun ' + row.tahun_hak + '</div>'
                },
                {
                    data: 'tanggal_mulai', className: 'py-3',
                    render: (data, type, row) =>
                        '<div class="text-slate-700">' + formatTanggal(data) + '</div>' +
                        '<div class="text-[10px] text-slate-400">s/d ' + formatTanggal(row.tanggal_selesai) + '</div>'
                },
                {
                    data: 'jumlah_hari', className: 'text-center py-3',
                    render: (data) => '<span class="font-black text-slate-900">' + data + '</span><span class="text-[10px] text-slate-400 block">hari</span>'
                },
                {
                    data: 'status', orderable: false, className: 'py-3',
                    render: function (data, type, row) {
                        let html = badgeStatus(data);
                        if (row.progress && row.progress.total > 0) {
                            html += '<div class="text-[10px] text-slate-500 mt-1">Approval ' + row.progress.disetujui + '/' + row.progress.total + '</div>';
                        }
                        if (data === 'ditolak' && row.catatan_penolakan) {
                            html += '<div class="text-[10px] text-rose-600 mt-0.5">' + row.catatan_penolakan + '</div>';
                        }
                        return html;
                    }
                },
                {
                    data: 'id', orderable: false, className: 'text-center py-3',
                    render: function (data, type, row) {
                        let btn = '<div class="flex items-center justify-center gap-1.5 flex-wrap">';
                        btn += '<button type="button" onclick="lihatDetail(' + data + ')" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700" title="Detail"><i data-lucide="eye" class="w-3.5 h-3.5"></i></button>';

                        if (row.status === 'draft') {
                            <?php if ($can_edit): ?>
                            btn += '<button type="button" onclick="editCuti(' + data + ')" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700" title="Ubah draft"><i data-lucide="edit" class="w-3.5 h-3.5"></i></button>';
                            btn += '<button type="button" onclick="ajukanCuti(' + data + ', \'' + row.nomor_pengajuan + '\')" class="p-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white" title="Kirim ke approval"><i data-lucide="send" class="w-3.5 h-3.5"></i></button>';
                            <?php endif; ?>
                            <?php if ($can_delete): ?>
                            btn += '<button type="button" onclick="hapusCuti(' + data + ', \'' + row.nomor_pengajuan + '\')" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700" title="Hapus draft"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>';
                            <?php endif; ?>
                        }

                        if (row.status === 'disetujui') {
                            btn += '<a href="' + CUTI_URL.cetak + '/' + data + '" target="_blank" class="p-1.5 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700" title="Cetak surat izin"><i data-lucide="printer" class="w-3.5 h-3.5"></i></a>';
                        }

                        <?php if ($can_edit): ?>
                        if (['diajukan', 'proses', 'disetujui'].includes(row.status)) {
                            btn += '<button type="button" onclick="batalkanCuti(' + data + ', \'' + row.nomor_pengajuan + '\')" class="p-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700" title="Batalkan"><i data-lucide="ban" class="w-3.5 h-3.5"></i></button>';
                        }
                        <?php endif; ?>

                        return btn + '</div>';
                    }
                }
            ],
            drawCallback: () => { if (window.lucide) lucide.createIcons(); },
            language: {
                processing: 'Memuat data...',
                emptyTable: 'Belum ada permohonan cuti.',
                zeroRecords: 'Tidak ada permohonan yang cocok.',
                info: 'Menampilkan _START_ - _END_ dari _TOTAL_ permohonan',
                infoEmpty: 'Tidak ada data',
                lengthMenu: 'Tampil _MENU_',
                search: 'Cari:',
                paginate: { first: 'Awal', last: 'Akhir', next: 'Lanjut', previous: 'Kembali' }
            }
        });

        $('#filter-status, #filter-tahun, #filter-jenis').on('change', () => tableCuti.ajax.reload());
    });

    // ---------- Modal Form ----------
    function showCutiModal() {
        const modal = document.getElementById('modal-cuti');
        modal.classList.remove('hidden'); modal.classList.add('flex'); modal.style.display = 'flex';
        if (window.lucide) lucide.createIcons();
    }

    function openCutiModal() {
        document.getElementById('form-cuti').reset();
        document.getElementById('cuti-id').value = '';
        document.getElementById('modal-cuti-title').textContent = 'Buat Permohonan Cuti';
        document.getElementById('panel-saldo').classList.add('hidden');
        document.getElementById('lampiran-existing').classList.add('hidden');
        document.getElementById('hint-jenis').innerHTML = '&nbsp;';
        showCutiModal();
    }

    function closeCutiModal() {
        const modal = document.getElementById('modal-cuti');
        modal.classList.remove('flex'); modal.classList.add('hidden'); modal.style.display = 'none';
    }

    function closeDetailModal() {
        const modal = document.getElementById('modal-detail');
        modal.classList.remove('flex'); modal.classList.add('hidden'); modal.style.display = 'none';
    }

    function editCuti(id) {
        LIS.fetchJson(CUTI_URL.get + '/' + id)
            .then(res => {
                const d = res.data;
                document.getElementById('form-cuti').reset();
                document.getElementById('cuti-id').value = d.id;
                document.getElementById('cuti-pegawai').value = d.pegawai_id;
                document.getElementById('cuti-jenis').value = d.jenis_cuti_id;
                document.getElementById('cuti-mulai').value = d.tanggal_mulai;
                document.getElementById('cuti-selesai').value = d.tanggal_selesai;
                document.getElementById('cuti-tahun').value = d.tahun_hak;
                document.getElementById('cuti-alasan').value = d.alasan;
                document.getElementById('cuti-alamat').value = d.alamat_selama_cuti || '';
                document.getElementById('cuti-telepon').value = d.telepon_selama_cuti || '';
                document.getElementById('modal-cuti-title').textContent = 'Ubah Draft ' + d.nomor_pengajuan;

                const lampiran = document.getElementById('lampiran-existing');
                if (d.lampiran) {
                    lampiran.textContent = 'Lampiran tersimpan: ' + d.lampiran.split('/').pop() + ' (unggah ulang untuk mengganti)';
                    lampiran.classList.remove('hidden');
                } else {
                    lampiran.classList.add('hidden');
                }

                showCutiModal();
                hitungHari();
            })
            .catch(err => LIS.showError('Gagal Memuat Draft', err, 'Data permohonan cuti tidak dapat dimuat.'));
    }

    // ---------- Perhitungan ----------
    function muatSaldo() {
        const pegawaiId = document.getElementById('cuti-pegawai').value;
        if (!pegawaiId) {
            document.getElementById('panel-saldo').classList.add('hidden');
            return;
        }
        hitungHari();
    }

    function hitungHari() {
        const pegawaiId = document.getElementById('cuti-pegawai').value;
        const jenisId = document.getElementById('cuti-jenis').value;
        const mulai = document.getElementById('cuti-mulai').value;
        const selesai = document.getElementById('cuti-selesai').value;
        const tahun = document.getElementById('cuti-tahun').value;

        // Petunjuk jenis cuti dan status wajib lampiran.
        const opt = document.getElementById('cuti-jenis').selectedOptions[0];
        const wajibLampiran = opt && opt.dataset.lampiran === '1';
        document.getElementById('lampiran-wajib').classList.toggle('hidden', !wajibLampiran);
        if (opt && opt.value) {
            const maxHari = parseInt(opt.dataset.max || '0', 10);
            document.getElementById('hint-jenis').textContent =
                (opt.dataset.saldo === '1' ? 'Memotong saldo cuti tahunan. ' : 'Tidak memotong saldo cuti tahunan. ') +
                (maxHari > 0 ? 'Maksimal ' + maxHari + ' hari.' : '');
        }

        if (!pegawaiId || !mulai || !selesai) {
            document.getElementById('panel-saldo').classList.add('hidden');
            return;
        }

        const q = new URLSearchParams({
            pegawai_id: pegawaiId, jenis_cuti_id: jenisId || 0,
            tanggal_mulai: mulai, tanggal_selesai: selesai, tahun_hak: tahun
        });

        LIS.fetchJson(CUTI_URL.hitung + '?' + q.toString())
            .then(res => {
                const d = res.data;
                document.getElementById('panel-saldo').classList.remove('hidden');
                document.getElementById('saldo-tahun').textContent = 'Tahun hak ' + tahun;

                if (d.saldo) {
                    document.getElementById('saldo-n2').textContent = d.saldo.sisa_n2;
                    document.getElementById('saldo-n1').textContent = d.saldo.sisa_n1;
                    document.getElementById('saldo-n').textContent = d.saldo.sisa_n;
                    document.getElementById('saldo-total').textContent = d.saldo.total_tersedia;
                }

                document.getElementById('hasil-hari').textContent = d.jumlah_hari + ' hari kerja';

                const alokasi = document.getElementById('hasil-alokasi');
                if (d.mengurangi_saldo && d.alokasi) {
                    alokasi.textContent = 'Dipotong dari — N-2: ' + d.alokasi.pakai_n2 +
                                          ' | N-1: ' + d.alokasi.pakai_n1 +
                                          ' | tahun berjalan: ' + d.alokasi.pakai_n;
                } else {
                    alokasi.textContent = 'Jenis cuti ini tidak memotong saldo cuti tahunan.';
                }

                const libur = Object.entries(d.hari_libur || {});
                document.getElementById('hasil-libur').textContent = libur.length
                    ? 'Hari libur dalam rentang ini tidak dihitung: ' + libur.map(([tgl, ket]) => ket + ' (' + formatTanggal(tgl) + ')').join(', ')
                    : '';

                const peringatan = document.getElementById('hasil-peringatan');
                if (d.peringatan) {
                    peringatan.textContent = d.peringatan;
                    peringatan.classList.remove('hidden');
                } else {
                    peringatan.classList.add('hidden');
                }
            })
            .catch(err => LIS.showError('Gagal Menghitung', err, 'Perhitungan hari cuti tidak dapat dilakukan.'));
    }

    // ---------- Aksi ----------
    function saveCuti(e) {
        e.preventDefault();
        const form = document.getElementById('form-cuti');

        LIS.fetchJson(CUTI_URL.save, { method: 'POST', body: new FormData(form) })
            .then(res => {
                closeCutiModal();
                LIS.toast('success', res.message);
                tableCuti.ajax.reload(null, false);
            })
            .catch(err => LIS.showError('Gagal Menyimpan Permohonan', err, 'Permohonan cuti tidak dapat disimpan.'));
    }

    function ajukanCuti(id, nomor) {
        Swal.fire({
            title: 'Kirim ke Alur Approval?',
            html: 'Permohonan <strong>' + nomor + '</strong> akan dikirim ke pejabat penyetuju.<br><span class="text-xs text-slate-500">Saldo hak cuti langsung dipotong agar tidak terpakai ganda, dan draft tidak dapat diubah lagi.</span>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#047857',
            confirmButtonText: 'Ya, Kirim',
            cancelButtonText: 'Batal'
        }).then(res => {
            if (!res.isConfirmed) return;

            LIS.fetchJson(CUTI_URL.ajukan + '/' + id, { method: 'POST' })
                .then(res => {
                    LIS.toast('success', res.message);
                    tableCuti.ajax.reload(null, false);
                })
                .catch(err => LIS.showError('Gagal Mengirim Permohonan', err, 'Permohonan tidak dapat dikirim ke approval.'));
        });
    }

    function batalkanCuti(id, nomor) {
        Swal.fire({
            title: 'Batalkan Permohonan?',
            input: 'textarea',
            inputLabel: 'Alasan pembatalan ' + nomor,
            inputPlaceholder: 'Tuliskan alasan pembatalan...',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d97706',
            confirmButtonText: 'Batalkan Permohonan',
            cancelButtonText: 'Tutup',
            inputValidator: (value) => !value && 'Alasan pembatalan wajib diisi.'
        }).then(res => {
            if (!res.isConfirmed) return;

            const body = new FormData();
            body.append('alasan', res.value);

            LIS.fetchJson(CUTI_URL.batalkan + '/' + id, { method: 'POST', body: body })
                .then(res => {
                    LIS.toast('success', res.message);
                    tableCuti.ajax.reload(null, false);
                })
                .catch(err => LIS.showError('Gagal Membatalkan', err, 'Permohonan tidak dapat dibatalkan.'));
        });
    }

    function hapusCuti(id, nomor) {
        Swal.fire({
            title: 'Hapus Draft?',
            html: 'Draft <strong>' + nomor + '</strong> akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(res => {
            if (!res.isConfirmed) return;

            LIS.fetchJson(CUTI_URL.hapus + '/' + id, { method: 'POST' })
                .then(res => {
                    LIS.toast('success', res.message);
                    tableCuti.ajax.reload(null, false);
                })
                .catch(err => LIS.showError('Gagal Menghapus Draft', err, 'Draft tidak dapat dihapus.'));
        });
    }

    function lihatDetail(id) {
        LIS.fetchJson(CUTI_URL.get + '/' + id)
            .then(res => {
                const d = res.data;
                let html = '';

                html += '<div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-1.5 text-xs">';
                html += '<div class="flex justify-between"><span class="text-slate-500">Nomor Permohonan</span><strong class="font-mono">' + d.nomor_pengajuan + '</strong></div>';
                if (d.nomor_surat_izin) {
                    html += '<div class="flex justify-between"><span class="text-slate-500">Nomor Surat Izin</span><strong class="font-mono">' + d.nomor_surat_izin + '</strong></div>';
                }
                html += '<div class="flex justify-between"><span class="text-slate-500">Pegawai</span><strong>' + d.pegawai_nama + '</strong></div>';
                html += '<div class="flex justify-between"><span class="text-slate-500">NIP</span><span class="font-mono">' + (d.pegawai_nip || '-') + '</span></div>';
                html += '<div class="flex justify-between"><span class="text-slate-500">Jabatan</span><span>' + (d.pegawai_jabatan || '-') + '</span></div>';
                html += '<div class="flex justify-between"><span class="text-slate-500">Jenis Cuti</span><strong>' + d.jenis_cuti_nama + '</strong></div>';
                html += '<div class="flex justify-between"><span class="text-slate-500">Periode</span><span>' + formatTanggal(d.tanggal_mulai) + ' s/d ' + formatTanggal(d.tanggal_selesai) + '</span></div>';
                html += '<div class="flex justify-between"><span class="text-slate-500">Lama Cuti</span><strong>' + d.jumlah_hari + ' hari kerja</strong></div>';
                html += '<div class="flex justify-between"><span class="text-slate-500">Pemotongan Saldo</span><span>N-2: ' + d.pakai_n2 + ' | N-1: ' + d.pakai_n1 + ' | N: ' + d.pakai_n + '</span></div>';
                html += '<div class="flex justify-between"><span class="text-slate-500">Status</span>' + badgeStatus(d.status) + '</div>';
                html += '</div>';

                html += '<div><span class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Alasan</span><p class="text-xs text-slate-700 mt-1">' + d.alasan + '</p></div>';

                if (d.alamat_selama_cuti) {
                    html += '<div><span class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Alamat Selama Cuti</span><p class="text-xs text-slate-700 mt-1">' + d.alamat_selama_cuti + ' &bull; ' + (d.telepon_selama_cuti || '-') + '</p></div>';
                }

                if (d.lampiran) {
                    html += '<div><a href="<?= base_url() ?>' + d.lampiran + '" target="_blank" class="text-xs text-sky-700 underline font-semibold">Lihat lampiran pendukung</a></div>';
                }

                html += '<div><span class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Rantai Approval</span><div class="mt-2 space-y-2">';
                if (!d.approvals || d.approvals.length === 0) {
                    html += '<p class="text-xs text-slate-400">Belum dikirim ke alur approval.</p>';
                } else {
                    d.approvals.forEach(a => {
                        const warna = a.status === 'approved' ? 'emerald' : (a.status === 'rejected' ? 'rose' : (a.status === 'pending' ? 'amber' : 'slate'));
                        html += '<div class="flex items-start gap-2.5 p-2.5 rounded-lg border border-' + warna + '-200 bg-' + warna + '-50">';
                        html += '<span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-' + warna + '-600 text-white font-black text-[10px] shrink-0">' + a.urutan + '</span>';
                        html += '<div class="text-xs flex-1"><strong class="block">' + a.pegawai_nama + '</strong>';
                        html += '<span class="text-[10px] text-slate-500">' + a.jabatan_approval + '</span>';
                        if (a.waktu_approval) html += '<span class="text-[10px] text-slate-500 block">' + a.waktu_approval + '</span>';
                        if (a.catatan) html += '<span class="text-[10px] text-slate-700 block mt-0.5">Catatan: ' + a.catatan + '</span>';
                        html += '</div>';
                        html += '<span class="text-[10px] font-bold text-' + warna + '-700 uppercase">' + a.status + '</span>';
                        html += '</div>';
                    });
                }
                html += '</div></div>';

                document.getElementById('detail-body').innerHTML = html;
                const modal = document.getElementById('modal-detail');
                modal.classList.remove('hidden'); modal.classList.add('flex'); modal.style.display = 'flex';
                if (window.lucide) lucide.createIcons();
            })
            .catch(err => LIS.showError('Gagal Memuat Detail', err, 'Detail permohonan tidak dapat dimuat.'));
    }
</script>
