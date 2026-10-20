<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-slate-400">Kepegawaian</span>
                <span>/</span>
                <span class="text-emerald-800 font-semibold">Audit Trail</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="history" class="w-6 h-6"></i>
                </span>
                <span>Riwayat Aktivitas Pengguna</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Setiap aksi tulis tercatat otomatis. Isi formulir tidak pernah disimpan, hanya identitas pelaku, modul, waktu, dan alamat IP.
            </p>
        </div>

        <?php if ($can_delete): ?>
            <button type="button" onclick="pangkasAudit()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center gap-2 border border-slate-300">
                <i data-lucide="eraser" class="w-4 h-4"></i>
                <span>Pangkas Log Lama</span>
            </button>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <?php
        $kartu = [
            ['label' => 'Aktivitas Hari Ini', 'value' => $ringkasan['hari_ini'], 'icon' => 'calendar-check', 'warna' => 'emerald'],
            ['label' => '7 Hari Terakhir', 'value' => $ringkasan['minggu_ini'], 'icon' => 'calendar-range', 'warna' => 'teal'],
            ['label' => 'Pengguna Aktif (7 hari)', 'value' => $ringkasan['pengguna_aktif'], 'icon' => 'users', 'warna' => 'amber'],
            ['label' => 'Total Tercatat', 'value' => $ringkasan['total'], 'icon' => 'database', 'warna' => 'slate'],
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

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="scroll-text" class="w-4 h-4 text-emerald-700"></i>
                Catatan Aktivitas
            </h2>
            <div class="flex items-center gap-2 flex-wrap">
                <select id="filter-modul" class="lis-form-control py-1.5 text-xs w-auto">
                    <option value="all">Semua Modul</option>
                    <?php foreach ($modul_list as $m): ?>
                        <option value="<?= html_escape($m['modul']) ?>"><?= html_escape(ucfirst($m['modul'])) ?> (<?= (int)$m['jumlah'] ?>)</option>
                    <?php endforeach; ?>
                </select>
                <select id="filter-user" class="lis-form-control py-1.5 text-xs w-auto">
                    <option value="all">Semua Pengguna</option>
                    <?php foreach ($user_list as $u): ?>
                        <option value="<?= (int)$u['id'] ?>"><?= html_escape($u['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="date" id="filter-mulai" class="lis-form-control py-1.5 text-xs w-auto" title="Dari tanggal">
                <input type="date" id="filter-selesai" class="lis-form-control py-1.5 text-xs w-auto" title="Sampai tanggal">
            </div>
        </div>

        <div class="p-4 sm:p-5 overflow-x-auto">
            <table id="table-audit" class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-bold bg-slate-50">
                        <th class="py-3 px-3 w-16 text-center">ID</th>
                        <th class="py-3 px-3 w-40">Waktu</th>
                        <th class="py-3 px-3">Pengguna</th>
                        <th class="py-3 px-3">Modul / Aksi</th>
                        <th class="py-3 px-3">Keterangan</th>
                        <th class="py-3 px-3 w-32">Alamat IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium"></tbody>
            </table>
        </div>
    </div>

    <p class="text-[11px] text-slate-500 px-1">
        Catatan lebih lama dari <?= (int)$retensi_hari ?> hari dapat dipangkas lewat tombol di atas. Ambang retensi diatur pada Pengaturan Sistem.
    </p>
</div>

<script>
    const AUDIT_URL = {
        fetch: '<?= site_url('kepegawaian/fetch_audit') ?>',
        pangkas: '<?= site_url('kepegawaian/pangkas_audit') ?>'
    };

    const WARNA_AKSI = {
        save: 'emerald', rule_save: 'emerald', simpan_saldo: 'emerald',
        delete: 'rose', hapus: 'rose', rule_delete: 'rose', pangkas_audit: 'rose',
        ajukan: 'sky', proses_approval: 'amber', batalkan: 'amber',
        login: 'teal', logout: 'slate'
    };

    let tableAudit = null;

    $(function () {
        tableAudit = $('#table-audit').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: AUDIT_URL.fetch,
                type: 'GET',
                data: function (d) {
                    d.modul = $('#filter-modul').val() || 'all';
                    d.user_id = $('#filter-user').val() || 'all';
                    d.tanggal_mulai = $('#filter-mulai').val() || '';
                    d.tanggal_selesai = $('#filter-selesai').val() || '';
                }
            },
            order: [],
            pageLength: 25,
            columns: [
                { data: 'id', orderable: false, className: 'text-center text-slate-400 py-3 font-mono' },
                {
                    data: 'created_at', orderable: false, className: 'py-3 text-slate-700',
                    render: (d) => d ? '<span class="font-mono text-[11px]">' + d + '</span>' : '-'
                },
                {
                    data: 'user_nama', orderable: false, className: 'py-3',
                    render: (d, t, r) =>
                        '<strong class="text-slate-900 block">' + (d || '-') + '</strong>' +
                        '<span class="text-[10px] text-slate-400">' + (r.user_role || 'tanpa peran') + '</span>'
                },
                {
                    data: 'modul', orderable: false, className: 'py-3',
                    render: function (d, t, r) {
                        const warna = WARNA_AKSI[r.aksi] || 'slate';
                        return '<span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-' + warna + '-50 text-' + warna + '-800 border border-' + warna + '-200">' + r.aksi + '</span>' +
                               '<div class="text-[10px] text-slate-500 mt-0.5">' + d + '</div>';
                    }
                },
                {
                    data: 'deskripsi', orderable: false, className: 'py-3 text-slate-700',
                    render: (d, t, r) =>
                        (d || '-') + (r.url ? '<div class="text-[10px] text-slate-400 font-mono mt-0.5">/' + r.url + '</div>' : '')
                },
                {
                    data: 'ip_address', orderable: false, className: 'py-3 font-mono text-slate-600 text-[11px]',
                    render: (d) => d || '-'
                }
            ],
            drawCallback: () => { if (window.lucide) lucide.createIcons(); },
            language: {
                processing: 'Memuat catatan...',
                emptyTable: 'Belum ada aktivitas tercatat.',
                zeroRecords: 'Tidak ada catatan yang cocok.',
                info: 'Menampilkan _START_ - _END_ dari _TOTAL_ catatan',
                infoEmpty: 'Tidak ada data',
                lengthMenu: 'Tampil _MENU_',
                search: 'Cari:',
                paginate: { first: 'Awal', last: 'Akhir', next: 'Lanjut', previous: 'Kembali' }
            }
        });

        $('#filter-modul, #filter-user, #filter-mulai, #filter-selesai').on('change', () => tableAudit.ajax.reload());
    });

    function pangkasAudit() {
        Swal.fire({
            title: 'Pangkas Log Audit Lama?',
            html: 'Catatan yang lebih tua dari <strong><?= (int)$retensi_hari ?> hari</strong> akan dihapus permanen.<br>' +
                  '<span class="text-xs text-slate-500">Catatan dalam masa retensi tidak tersentuh.</span>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Ya, Pangkas',
            cancelButtonText: 'Batal'
        }).then(res => {
            if (!res.isConfirmed) return;

            LIS.fetchJson(AUDIT_URL.pangkas, { method: 'POST' })
                .then(res => {
                    LIS.toast('success', res.message);
                    tableAudit.ajax.reload();
                })
                .catch(err => LIS.showError('Gagal Memangkas Log', err, 'Log audit tidak dapat dipangkas.'));
        });
    }
</script>
