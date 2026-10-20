<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-slate-400">Kepegawaian</span>
                <span>/</span>
                <span class="text-emerald-800 font-semibold">Approval Cuti</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="check-check" class="w-6 h-6"></i>
                </span>
                <span>Antrean Persetujuan Cuti</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Permohonan yang menunggu keputusan pada tahap approval yang sedang aktif</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <select id="filter-approver" class="lis-form-control py-2 text-xs w-auto" onchange="muatAntrean()">
                <option value="all">Semua Pejabat Penyetuju</option>
                <?php foreach ($rules as $r): ?>
                    <option value="<?= (int)$r['pegawai_id'] ?>">Tahap <?= (int)$r['urutan'] ?> &mdash; <?= html_escape($r['nama_formatted']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" onclick="muatAntrean()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center gap-2 border border-slate-300">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                <span>Muat Ulang</span>
            </button>
        </div>
    </div>

    <?php if (empty($rules)): ?>
        <div class="bg-amber-50 border border-amber-300 rounded-2xl p-5 flex items-start gap-3">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-700 shrink-0 mt-0.5"></i>
            <div class="text-xs text-amber-900">
                <strong class="block mb-1">Hirarki approval belum dikonfigurasi.</strong>
                <a href="<?= site_url('kepegawaian/approval_setting') ?>" class="underline font-bold">Atur tahap approval</a> agar permohonan dapat diproses.
            </div>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Menunggu Approval</span>
                <i data-lucide="clock" class="w-4 h-4 text-amber-600"></i>
            </div>
            <span class="text-2xl font-black text-slate-900"><?= (int)$summary['menunggu_approval'] ?></span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Disetujui</span>
                <i data-lucide="check-check" class="w-4 h-4 text-emerald-600"></i>
            </div>
            <span class="text-2xl font-black text-slate-900"><?= (int)$summary['disetujui'] ?></span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Ditolak</span>
                <i data-lucide="x-circle" class="w-4 h-4 text-rose-600"></i>
            </div>
            <span class="text-2xl font-black text-slate-900"><?= (int)$summary['ditolak'] ?></span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Tahap Approval</span>
                <i data-lucide="git-branch" class="w-4 h-4 text-teal-600"></i>
            </div>
            <span class="text-2xl font-black text-slate-900"><?= count($rules) ?></span>
        </div>
    </div>

    <div id="antrean-container" class="space-y-3"></div>
</div>

<script>
    const APPROVAL_URL = {
        antrean: '<?= site_url('kepegawaian/antrean') ?>',
        proses: '<?= site_url('kepegawaian/proses_approval') ?>',
        get: '<?= site_url('kepegawaian/get') ?>'
    };
    const CAN_APPROVE = <?= $can_edit ? 'true' : 'false' ?>;

    function formatTanggal(iso) {
        if (!iso) return '-';
        const bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        const d = new Date(iso + 'T00:00:00');
        if (isNaN(d)) return iso;
        return d.getDate() + ' ' + bulan[d.getMonth()] + ' ' + d.getFullYear();
    }

    function muatAntrean() {
        const approver = document.getElementById('filter-approver').value;
        const container = document.getElementById('antrean-container');
        container.innerHTML = '<div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-xs text-slate-400">Memuat antrean...</div>';

        LIS.fetchJson(APPROVAL_URL.antrean + '?approver_id=' + encodeURIComponent(approver))
            .then(res => renderAntrean(res.data))
            .catch(err => {
                container.innerHTML = '<div class="bg-rose-50 rounded-2xl border border-rose-200 p-6 text-center text-xs text-rose-700">Antrean gagal dimuat.</div>';
                LIS.showError('Gagal Memuat Antrean', err, 'Daftar antrean approval tidak dapat dimuat.');
            });
    }

    function renderAntrean(rows) {
        const container = document.getElementById('antrean-container');

        if (!rows || rows.length === 0) {
            container.innerHTML =
                '<div class="bg-white rounded-2xl border border-slate-200 p-10 text-center">' +
                '<i data-lucide="inbox" class="w-10 h-10 text-slate-300 mx-auto mb-3"></i>' +
                '<p class="text-sm font-bold text-slate-600">Tidak ada permohonan yang menunggu persetujuan.</p>' +
                '<p class="text-xs text-slate-400 mt-1">Seluruh permohonan cuti sudah diproses.</p>' +
                '</div>';
            if (window.lucide) lucide.createIcons();
            return;
        }

        let html = '';
        rows.forEach(r => {
            html += '<div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">';

            html += '<div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-2">';
            html += '<div class="flex items-center gap-3">';
            html += '<span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-amber-500 text-white font-black text-xs shrink-0">' + r.step_urutan + '</span>';
            html += '<div><strong class="text-sm text-slate-900 block">' + r.pegawai_nama + '</strong>';
            html += '<span class="text-[11px] text-slate-500 font-mono">' + r.nomor_pengajuan + ' &bull; NIP. ' + (r.pegawai_nip || '-') + '</span></div>';
            html += '</div>';
            html += '<div class="text-right"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Menunggu: ' + r.jabatan_approval + '</span>';
            html += '<div class="text-[10px] text-slate-500 mt-1">Penyetuju: ' + r.approver_nama + '</div></div>';
            html += '</div>';

            html += '<div class="p-5 grid grid-cols-1 lg:grid-cols-3 gap-4">';

            html += '<div class="lg:col-span-2 space-y-2 text-xs">';
            html += '<div class="grid grid-cols-2 gap-2">';
            html += '<div><span class="text-slate-500 block text-[10px] uppercase font-bold">Jenis Cuti</span><strong class="text-slate-900">' + r.jenis_cuti_nama + '</strong></div>';
            html += '<div><span class="text-slate-500 block text-[10px] uppercase font-bold">Lama Cuti</span><strong class="text-slate-900">' + r.jumlah_hari + ' hari kerja</strong></div>';
            html += '<div><span class="text-slate-500 block text-[10px] uppercase font-bold">Periode</span><span class="text-slate-700">' + formatTanggal(r.tanggal_mulai) + ' s/d ' + formatTanggal(r.tanggal_selesai) + '</span></div>';
            html += '<div><span class="text-slate-500 block text-[10px] uppercase font-bold">Unit Kerja</span><span class="text-slate-700">' + (r.pegawai_seksi || '-') + '</span></div>';
            html += '</div>';
            html += '<div><span class="text-slate-500 block text-[10px] uppercase font-bold">Alasan</span><p class="text-slate-700">' + r.alasan + '</p></div>';
            if (r.pakai_n2 > 0 || r.pakai_n1 > 0 || r.pakai_n > 0) {
                html += '<div class="text-[10px] text-slate-500">Pemotongan saldo &mdash; N-2: ' + r.pakai_n2 + ' | N-1: ' + r.pakai_n1 + ' | tahun berjalan: ' + r.pakai_n + '</div>';
            }
            if (r.lampiran) {
                html += '<div><a href="<?= base_url() ?>' + r.lampiran + '" target="_blank" class="text-xs text-sky-700 underline font-semibold">Lihat lampiran pendukung</a></div>';
            }
            html += '</div>';

            // Jejak tahap approval
            html += '<div class="bg-slate-50 rounded-xl border border-slate-200 p-3">';
            html += '<span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider block mb-2">Rantai Approval</span>';
            html += '<div class="space-y-1.5">';
            (r.approvals || []).forEach(a => {
                const warna = a.status === 'approved' ? 'emerald' : (a.status === 'rejected' ? 'rose' : (a.status === 'pending' ? 'amber' : 'slate'));
                const ikon = a.status === 'approved' ? 'check' : (a.status === 'rejected' ? 'x' : (a.status === 'pending' ? 'clock' : 'minus'));
                html += '<div class="flex items-center gap-2 text-[11px]">';
                html += '<span class="inline-flex items-center justify-center w-5 h-5 rounded bg-' + warna + '-100 text-' + warna + '-700 shrink-0"><i data-lucide="' + ikon + '" class="w-3 h-3"></i></span>';
                html += '<span class="flex-1 text-slate-700">' + a.jabatan_approval + '</span>';
                html += '</div>';
            });
            html += '</div></div>';

            html += '</div>';

            if (CAN_APPROVE) {
                html += '<div class="px-5 py-3.5 border-t border-slate-200 bg-slate-50 flex items-center justify-end gap-2">';
                html += '<button type="button" onclick="putuskan(' + r.id + ', ' + r.step_urutan + ', \'reject\', \'' + r.nomor_pengajuan + '\')" class="px-4 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold border border-rose-200 flex items-center gap-2">';
                html += '<i data-lucide="x" class="w-4 h-4"></i><span>Tolak</span></button>';
                html += '<button type="button" onclick="putuskan(' + r.id + ', ' + r.step_urutan + ', \'approve\', \'' + r.nomor_pengajuan + '\')" class="px-5 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold flex items-center gap-2">';
                html += '<i data-lucide="check" class="w-4 h-4"></i><span>Setujui Tahap ' + r.step_urutan + '</span></button>';
                html += '</div>';
            }

            html += '</div>';
        });

        container.innerHTML = html;
        if (window.lucide) lucide.createIcons();
    }

    function putuskan(cutiId, urutan, keputusan, nomor) {
        const menolak = keputusan === 'reject';

        Swal.fire({
            title: menolak ? 'Tolak Permohonan?' : 'Setujui Tahap ' + urutan + '?',
            html: 'Permohonan <strong>' + nomor + '</strong>' +
                  (menolak
                      ? '<br><span class="text-xs text-slate-500">Permohonan langsung berstatus ditolak dan saldo hak cuti dikembalikan.</span>'
                      : '<br><span class="text-xs text-slate-500">Persetujuan diteruskan ke tahap berikutnya.</span>'),
            input: 'textarea',
            inputLabel: menolak ? 'Alasan penolakan (wajib)' : 'Catatan persetujuan (opsional)',
            inputPlaceholder: menolak ? 'Tuliskan alasan penolakan...' : 'Catatan tambahan...',
            icon: menolak ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: menolak ? '#e11d48' : '#047857',
            confirmButtonText: menolak ? 'Tolak Permohonan' : 'Setujui',
            cancelButtonText: 'Batal',
            inputValidator: (value) => (menolak && !value) ? 'Alasan penolakan wajib diisi.' : undefined
        }).then(res => {
            if (!res.isConfirmed) return;

            const body = new FormData();
            body.append('cuti_id', cutiId);
            body.append('urutan', urutan);
            body.append('keputusan', keputusan);
            body.append('catatan', res.value || '');

            LIS.fetchJson(APPROVAL_URL.proses, { method: 'POST', body: body })
                .then(res => {
                    LIS.toast('success', res.message);
                    muatAntrean();
                })
                .catch(err => LIS.showError('Gagal Memproses Persetujuan', err, 'Keputusan approval tidak dapat disimpan.'));
        });
    }

    document.addEventListener('DOMContentLoaded', muatAntrean);
</script>
