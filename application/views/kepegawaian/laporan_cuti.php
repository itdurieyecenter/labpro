<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-slate-400">Kepegawaian</span>
                <span>/</span>
                <span class="text-emerald-800 font-semibold">Laporan Cuti</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="file-bar-chart" class="w-6 h-6"></i>
                </span>
                <span>Rekapitulasi Hak Cuti Pegawai</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Sisa hak cuti seluruh pegawai tahun <?= (int)$tahun ?> &mdash;
                hak <?= (int)$config_cuti['hak_tahunan'] ?> hari/tahun, sisa N-1 maksimal <?= (int)$config_cuti['max_pakai_n1'] ?> hari,
                sisa N-2 maksimal <?= (int)$config_cuti['max_pakai_n2'] ?> hari, total maksimal <?= (int)$config_cuti['max_total'] ?> hari
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <select id="filter-tahun" class="lis-form-control py-2 text-xs w-auto" onchange="muatRekap()">
                <?php foreach ($tahun_list as $t): ?>
                    <option value="<?= (int)$t ?>" <?= (int)$t === (int)$tahun ? 'selected' : '' ?>><?= (int)$t ?></option>
                <?php endforeach; ?>
            </select>
            <select id="filter-seksi" class="lis-form-control py-2 text-xs w-auto" onchange="muatRekap()">
                <option value="all">Semua Seksi / Instalasi</option>
                <?php foreach ($seksi_ref as $s): ?>
                    <option value="<?= html_escape(is_array($s) ? $s['nama'] : $s) ?>"><?= html_escape(is_array($s) ? $s['nama'] : $s) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" onclick="window.print()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center gap-2 border border-slate-300">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak</span>
            </button>
        </div>
    </div>

    <!-- Ringkasan Tahun Berjalan -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <?php
        $kartu = [
            ['label' => 'Total Permohonan', 'value' => $summary['total'], 'icon' => 'files', 'warna' => 'slate'],
            ['label' => 'Menunggu Approval', 'value' => $summary['menunggu_approval'], 'icon' => 'clock', 'warna' => 'amber'],
            ['label' => 'Disetujui', 'value' => $summary['disetujui'], 'icon' => 'check-check', 'warna' => 'emerald'],
            ['label' => 'Ditolak / Batal', 'value' => $summary['ditolak'] + $summary['dibatalkan'], 'icon' => 'x-circle', 'warna' => 'rose'],
            ['label' => 'Hari Cuti Terpakai', 'value' => $summary['total_hari_disetujui'], 'icon' => 'calendar-check', 'warna' => 'teal'],
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

    <!-- Rekap Saldo per Pegawai -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="users" class="w-4 h-4 text-emerald-700"></i>
                Sisa Hak Cuti Tahunan per Pegawai
            </h2>
            <input type="search" id="filter-cari" class="lis-form-control py-1.5 text-xs w-auto" placeholder="Cari nama atau NIP..." oninput="debounceRekap()">
        </div>

        <div class="p-4 sm:p-5 overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-bold bg-slate-50">
                        <th class="py-3 px-3 w-12 text-center">No</th>
                        <th class="py-3 px-3">Pegawai</th>
                        <th class="py-3 px-3">Seksi / Instalasi</th>
                        <th class="py-3 px-3 text-center">Hak N</th>
                        <th class="py-3 px-3 text-center">Sisa N-2</th>
                        <th class="py-3 px-3 text-center">Sisa N-1</th>
                        <th class="py-3 px-3 text-center">Sisa N</th>
                        <th class="py-3 px-3 text-center">Terpakai</th>
                        <th class="py-3 px-3 text-center">Total Tersedia</th>
                        <th class="py-3 px-3 text-center w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody id="rekap-body" class="divide-y divide-slate-100 font-medium"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Penyesuaian Saldo -->
<div id="modal-saldo" class="lis-modal">
    <div class="lis-modal-box max-w-md w-full">
        <div class="lis-modal-header bg-gradient-to-r from-emerald-900 to-teal-900 border-b border-amber-400/40">
            <h3 class="lis-modal-title text-white flex items-center gap-2">
                <i data-lucide="sliders-horizontal" class="w-5 h-5 text-amber-300"></i>
                <span>Penyesuaian Saldo Cuti</span>
            </h3>
            <button type="button" onclick="closeSaldoModal()" class="lis-modal-close"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>

        <form id="form-saldo" onsubmit="simpanSaldo(event)" class="lis-modal-body p-5 space-y-3">
            <input type="hidden" name="pegawai_id" id="saldo-pegawai-id">
            <input type="hidden" name="tahun" id="saldo-tahun-input">

            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3">
                <strong id="saldo-nama" class="text-sm text-slate-900 block"></strong>
                <span id="saldo-info" class="text-[11px] text-slate-500"></span>
            </div>

            <div class="grid grid-cols-3 gap-2.5">
                <div>
                    <label class="lis-form-label">Hak Tahun N</label>
                    <input type="number" name="hak_tahun_berjalan" id="saldo-hak" min="0" class="lis-form-control text-center font-bold">
                </div>
                <div>
                    <label class="lis-form-label">Bawaan N-1</label>
                    <input type="number" name="saldo_n1" id="saldo-input-n1" min="0" class="lis-form-control text-center font-bold">
                </div>
                <div>
                    <label class="lis-form-label">Bawaan N-2</label>
                    <input type="number" name="saldo_n2" id="saldo-input-n2" min="0" class="lis-form-control text-center font-bold">
                </div>
            </div>

            <div>
                <label class="lis-form-label">Penyesuaian Hak Tahun Berjalan</label>
                <input type="number" name="penyesuaian" id="saldo-penyesuaian" class="lis-form-control text-center font-bold" placeholder="0">
                <p class="lis-form-hint">Boleh bernilai negatif, misalnya pengurangan hak bagi pegawai yang baru masuk pertengahan tahun.</p>
            </div>

            <div>
                <label class="lis-form-label">Catatan</label>
                <textarea name="catatan" id="saldo-catatan" rows="2" class="lis-form-control" placeholder="Dasar penyesuaian, misalnya nomor SK atau surat keterangan"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-200 flex items-center justify-between">
                <button type="button" onclick="closeSaldoModal()" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold">Simpan Penyesuaian</button>
            </div>
        </form>
    </div>
</div>

<script>
    const REKAP_URL = {
        rekap: '<?= site_url('kepegawaian/rekap') ?>',
        simpan: '<?= site_url('kepegawaian/simpan_saldo') ?>'
    };
    const CAN_EDIT_SALDO = <?= $can_edit ? 'true' : 'false' ?>;

    let timerCari = null;
    function debounceRekap() {
        clearTimeout(timerCari);
        timerCari = setTimeout(muatRekap, 350);
    }

    function muatRekap() {
        const q = new URLSearchParams({
            tahun: document.getElementById('filter-tahun').value,
            seksi_ruangan: document.getElementById('filter-seksi').value,
            search: document.getElementById('filter-cari').value
        });

        const body = document.getElementById('rekap-body');
        body.innerHTML = '<tr><td colspan="10" class="text-center py-6 text-slate-400">Memuat rekapitulasi...</td></tr>';

        LIS.fetchJson(REKAP_URL.rekap + '?' + q.toString())
            .then(res => renderRekap(res.data))
            .catch(err => {
                body.innerHTML = '<tr><td colspan="10" class="text-center py-6 text-rose-600">Rekapitulasi gagal dimuat.</td></tr>';
                LIS.showError('Gagal Memuat Rekapitulasi', err, 'Data rekap hak cuti tidak dapat dimuat.');
            });
    }

    function renderRekap(rows) {
        const body = document.getElementById('rekap-body');

        if (!rows || rows.length === 0) {
            body.innerHTML = '<tr><td colspan="10" class="text-center py-8 text-slate-400">Tidak ada pegawai yang cocok dengan filter.</td></tr>';
            return;
        }

        let html = '';
        rows.forEach((r, i) => {
            const terpakai = Number(r.total_terpakai || 0);
            const tersedia = Number(r.total_tersedia || 0);
            const warnaTersedia = tersedia === 0 ? 'rose' : (tersedia <= 3 ? 'amber' : 'emerald');

            html += '<tr class="hover:bg-slate-50">';
            html += '<td class="py-3 px-3 text-center text-slate-400">' + (i + 1) + '</td>';
            html += '<td class="py-3 px-3"><strong class="text-slate-900 block">' + r.nama_formatted + '</strong>' +
                    '<span class="text-[10px] text-slate-400 font-mono">NIP. ' + (r.nip || '-') + '</span></td>';
            html += '<td class="py-3 px-3 text-slate-600">' + (r.seksi_ruangan || '-') + '</td>';
            html += '<td class="py-3 px-3 text-center text-slate-700">' + Number(r.hak_efektif_n || 0) + '</td>';
            html += '<td class="py-3 px-3 text-center text-slate-700">' + Number(r.sisa_n2 || 0) + '</td>';
            html += '<td class="py-3 px-3 text-center text-slate-700">' + Number(r.sisa_n1 || 0) + '</td>';
            html += '<td class="py-3 px-3 text-center text-slate-700">' + Number(r.sisa_n || 0) + '</td>';
            html += '<td class="py-3 px-3 text-center"><span class="font-bold text-slate-900">' + terpakai + '</span></td>';
            html += '<td class="py-3 px-3 text-center">' +
                    '<span class="px-2.5 py-1 rounded-lg text-xs font-black bg-' + warnaTersedia + '-50 text-' + warnaTersedia + '-700 border border-' + warnaTersedia + '-200">' +
                    tersedia + ' hari</span></td>';

            html += '<td class="py-3 px-3 text-center">';
            if (CAN_EDIT_SALDO) {
                html += '<button type="button" onclick=\'bukaSaldo(' + JSON.stringify(r).replace(/'/g, '&#39;') + ')\' ' +
                        'class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700" title="Sesuaikan saldo">' +
                        '<i data-lucide="sliders-horizontal" class="w-3.5 h-3.5"></i></button>';
            }
            html += '</td>';
            html += '</tr>';
        });

        body.innerHTML = html;
        if (window.lucide) lucide.createIcons();
    }

    function bukaSaldo(r) {
        document.getElementById('saldo-pegawai-id').value = r.pegawai_id;
        document.getElementById('saldo-tahun-input').value = r.tahun;
        document.getElementById('saldo-nama').textContent = r.nama_formatted;
        document.getElementById('saldo-info').textContent = 'NIP. ' + (r.nip || '-') + ' • Tahun hak ' + r.tahun;
        document.getElementById('saldo-hak').value = Number(r.hak_tahun_berjalan || 0);
        document.getElementById('saldo-input-n1').value = Number(r.saldo_n1 || 0);
        document.getElementById('saldo-input-n2').value = Number(r.saldo_n2 || 0);
        document.getElementById('saldo-penyesuaian').value = Number(r.penyesuaian || 0);
        document.getElementById('saldo-catatan').value = r.catatan || '';

        const modal = document.getElementById('modal-saldo');
        modal.classList.remove('hidden'); modal.classList.add('flex'); modal.style.display = 'flex';
        if (window.lucide) lucide.createIcons();
    }

    function closeSaldoModal() {
        const modal = document.getElementById('modal-saldo');
        modal.classList.remove('flex'); modal.classList.add('hidden'); modal.style.display = 'none';
    }

    function simpanSaldo(e) {
        e.preventDefault();
        const form = document.getElementById('form-saldo');

        LIS.fetchJson(REKAP_URL.simpan, { method: 'POST', body: new FormData(form) })
            .then(res => {
                closeSaldoModal();
                LIS.toast('success', res.message);
                muatRekap();
            })
            .catch(err => LIS.showError('Gagal Menyimpan Saldo', err, 'Penyesuaian saldo tidak dapat disimpan.'));
    }

    document.addEventListener('DOMContentLoaded', muatRekap);
</script>
