<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-5">
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700">Dashboard</a><span>/</span>
                <span class="text-slate-400">Kesehatan Lingkungan</span><span>/</span>
                <span class="text-emerald-800 font-semibold">Verifikasi Hasil</span>
            </div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200"><i data-lucide="shield-check" class="w-6 h-6"></i></span>
                <span>Verifikasi Berjenjang Hasil Uji Lingkungan</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">LHU baru sah dan bisa dicetak setelah <strong>semua tingkat</strong> menandatangani, berurutan dari tingkat pertama.</p>
        </div>
        <select id="v-filter" class="lis-form-control w-auto text-xs" onchange="loadVerifikasi()">
            <option value="validasi">Menunggu verifikasi</option>
            <option value="completed">Sudah sah (selesai)</option>
            <option value="all">Semua</option>
        </select>
    </div>

    <div id="v-list" class="space-y-3"><div class="text-center text-xs text-slate-400 py-10">Memuat...</div></div>
</div>

<script>
    const V_URL = {
        fetch: '<?= site_url('kesling/verifikasi_fetch') ?>',
        approve: '<?= site_url('kesling/approve_step') ?>',
        tolak: '<?= site_url('kesling/tolak') ?>',
        cetak: '<?= site_url('kesling/cetak_lhu') ?>',
        hasil: '<?= site_url('kesling/hasil') ?>'
    };
    const esc = s => String(s == null ? '' : s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    function loadVerifikasi() {
        const f = document.getElementById('v-filter').value;
        LIS.fetchJson(V_URL.fetch + '?filter=' + f).then(res => {
            const box = document.getElementById('v-list');
            if (!res.data.length) {
                box.innerHTML = '<div class="bg-white rounded-2xl border border-slate-200 p-10 text-center text-xs text-slate-400">Tidak ada order pada filter ini.</div>';
                return;
            }
            box.innerHTML = res.data.map(o => {
                const steps = o.steps.map(s => {
                    const ok = s.status === 'approved', pend = s.status === 'pending';
                    let act = ok ? '<span class="text-emerald-700 font-bold">Tertanda tangan ' + esc(s.waktu_approval || '') + '</span>'
                        : (pend && res.can_edit ? '<button onclick="approve(' + o.id + ',' + s.urutan + ')" class="px-3 py-1.5 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white font-bold">Sahkan &amp; Tanda Tangan</button>'
                        : '<span class="text-slate-400">' + (pend ? 'Menunggu' : 'Antre') + '</span>');
                    return '<li class="flex items-center justify-between gap-3 p-2.5 rounded-lg border ' + (ok ? 'border-emerald-200 bg-emerald-50/50' : (pend ? 'border-amber-300 bg-amber-50' : 'border-slate-200 bg-slate-50')) + '">' +
                        '<span><strong>' + s.urutan + '.</strong> ' + esc(s.jabatan_approval) + ' <span class="text-slate-500">&mdash; ' + esc(s.pegawai_nama) + '</span></span>' + act + '</li>';
                }).join('');
                const done = o.status === 'completed';
                return '<div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4">' +
                    '<div class="flex flex-wrap items-center justify-between gap-2 mb-3">' +
                    '<div><div class="font-mono font-bold text-sm text-slate-900">' + esc(o.order_number) + '</div>' +
                    '<div class="text-xs text-slate-500">' + esc(o.customer_name) + ' &bull; ' + esc(o.sample_type_name) + '</div></div>' +
                    '<div class="flex items-center gap-2 text-xs"><span class="font-bold ' + (done ? 'text-emerald-700' : 'text-amber-700') + '">' + o.signed + '/' + o.steps.length + ' tingkat</span>' +
                    '<a href="' + V_URL.hasil + '/' + o.id + '" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 font-bold text-slate-700">Lihat Hasil</a>' +
                    (done ? '<a href="' + V_URL.cetak + '/' + o.id + '" target="_blank" class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-bold">Cetak LHU</a>'
                          : (res.can_edit ? '<button onclick="tolak(' + o.id + ')" class="px-3 py-1.5 rounded-lg border border-rose-300 text-rose-700 hover:bg-rose-50 font-bold">Kembalikan</button>' : '')) +
                    '</div></div><ol class="space-y-1.5 text-xs">' + steps + '</ol></div>';
            }).join('');
        }).catch(e => LIS.showError('Gagal memuat', e, 'Tidak dapat memuat daftar verifikasi.'));
    }

    function approve(orderId, urutan) {
        Swal.fire({ title: 'Sahkan tingkat ' + urutan + '?', input: 'text', inputLabel: 'Catatan (opsional)', showCancelButton: true, confirmButtonText: 'Sahkan', confirmButtonColor: '#047857' })
        .then(r => {
            if (!r.isConfirmed) return;
            const fd = new FormData(); fd.append('order_id', orderId); fd.append('urutan', urutan); fd.append('catatan', r.value || '');
            LIS.fetchJson(V_URL.approve, { method: 'POST', body: fd }).then(res => { LIS.toast('success', res.message); loadVerifikasi(); })
                .catch(e => LIS.showError('Gagal', e, 'Verifikasi gagal.'));
        });
    }

    function tolak(orderId) {
        Swal.fire({ title: 'Kembalikan ke analis?', input: 'textarea', inputLabel: 'Alasan (wajib)', text: 'Semua tanda tangan dibatalkan.', showCancelButton: true,
            confirmButtonText: 'Kembalikan', confirmButtonColor: '#be123c', inputValidator: v => (!v || !v.trim()) ? 'Alasan wajib diisi' : null })
        .then(r => {
            if (!r.isConfirmed) return;
            const fd = new FormData(); fd.append('order_id', orderId); fd.append('alasan', r.value);
            LIS.fetchJson(V_URL.tolak, { method: 'POST', body: fd }).then(res => { LIS.toast('success', res.message); loadVerifikasi(); })
                .catch(e => LIS.showError('Gagal', e, 'Gagal mengembalikan.'));
        });
    }

    document.addEventListener('DOMContentLoaded', () => { loadVerifikasi(); if (window.lucide) lucide.createIcons(); });
</script>
