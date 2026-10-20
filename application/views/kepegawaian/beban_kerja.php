<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-slate-400">Kepegawaian</span>
                <span>/</span>
                <span class="text-emerald-800 font-semibold">Beban Kerja</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="activity" class="w-6 h-6"></i>
                </span>
                <span>Monitoring Beban Kerja Pegawai</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Dihitung dari jumlah hasil uji yang diinput dan pengesahan yang dilakukan, dibagi hari kerja efektif setelah dikurangi cuti
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <input type="date" id="filter-mulai" value="<?= html_escape($mulai) ?>" class="lis-form-control py-2 text-xs w-auto" onchange="muatBeban()">
            <span class="text-xs text-slate-400">s/d</span>
            <input type="date" id="filter-selesai" value="<?= html_escape($selesai) ?>" class="lis-form-control py-2 text-xs w-auto" onchange="muatBeban()">
            <select id="filter-seksi" class="lis-form-control py-2 text-xs w-auto" onchange="muatBeban()">
                <option value="all">Semua Seksi</option>
                <?php foreach ($seksi_ref as $s): ?>
                    <?php $nama = is_array($s) ? $s['nama'] : $s; ?>
                    <option value="<?= html_escape($nama) ?>"><?= html_escape($nama) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" onclick="window.print()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center gap-2 border border-slate-300">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak</span>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3" id="ringkas-beban"></div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="bar-chart-3" class="w-4 h-4 text-emerald-700"></i>
                Distribusi Beban Kerja
            </h2>
            <input type="search" id="filter-cari" class="lis-form-control py-1.5 text-xs w-auto" placeholder="Cari nama atau NIP..." oninput="debounceBeban()">
        </div>

        <div class="p-4 sm:p-5 overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-bold bg-slate-50">
                        <th class="py-3 px-3 w-12 text-center">No</th>
                        <th class="py-3 px-3">Pegawai</th>
                        <th class="py-3 px-3 text-center">Hasil Diinput</th>
                        <th class="py-3 px-3 text-center">Pengesahan</th>
                        <th class="py-3 px-3 text-center">Hari Cuti</th>
                        <th class="py-3 px-3 text-center">Hari Kerja Efektif</th>
                        <th class="py-3 px-3 text-center">Rata-rata / Hari</th>
                        <th class="py-3 px-3">Capaian thd Target</th>
                    </tr>
                </thead>
                <tbody id="beban-body" class="divide-y divide-slate-100 font-medium"></tbody>
            </table>
        </div>
    </div>

    <p class="text-[11px] text-slate-500 px-1">
        Catatan: "Hasil Diinput" dihitung dari parameter pemeriksaan yang hasilnya direkam atas nama pegawai tersebut,
        dan "Pengesahan" dari tahap validasi yang disetujuinya. Pegawai non-teknis wajar bernilai nol.
    </p>
</div>

<script>
    const BEBAN_URL = '<?= site_url('kepegawaian/fetch_beban_kerja') ?>';

    let timerCari = null;
    function debounceBeban() {
        clearTimeout(timerCari);
        timerCari = setTimeout(muatBeban, 350);
    }

    function kartu(label, nilai, ikon, warna) {
        return '<div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">' +
               '<div class="flex items-center justify-between mb-1.5">' +
               '<span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">' + label + '</span>' +
               '<i data-lucide="' + ikon + '" class="w-4 h-4 text-' + warna + '-600"></i></div>' +
               '<span class="text-2xl font-black text-slate-900">' + nilai + '</span></div>';
    }

    function muatBeban() {
        const q = new URLSearchParams({
            mulai: document.getElementById('filter-mulai').value,
            selesai: document.getElementById('filter-selesai').value,
            seksi_ruangan: document.getElementById('filter-seksi').value,
            search: document.getElementById('filter-cari').value
        });

        const body = document.getElementById('beban-body');
        body.innerHTML = '<tr><td colspan="8" class="text-center py-6 text-slate-400">Menghitung beban kerja...</td></tr>';

        LIS.fetchJson(BEBAN_URL + '?' + q.toString())
            .then(res => {
                const hasil = res.data;
                const rows = hasil.data;

                const totalPeriksa = rows.reduce((a, r) => a + Number(r.jml_pemeriksaan), 0);
                const totalValidasi = rows.reduce((a, r) => a + Number(r.jml_validasi), 0);
                const aktif = rows.filter(r => r.total_aktivitas > 0).length;

                document.getElementById('ringkas-beban').innerHTML =
                    kartu('Hari Kerja Periode', hasil.periode.hari_kerja, 'calendar-range', 'slate') +
                    kartu('Total Hasil Diinput', totalPeriksa, 'flask-conical', 'emerald') +
                    kartu('Total Pengesahan', totalValidasi, 'check-check', 'teal') +
                    kartu('Pegawai Beraktivitas', aktif + ' / ' + rows.length, 'users', 'amber');

                if (!rows || rows.length === 0) {
                    body.innerHTML = '<tr><td colspan="8" class="text-center py-8 text-slate-400">Tidak ada pegawai yang cocok dengan filter.</td></tr>';
                    if (window.lucide) lucide.createIcons();
                    return;
                }

                let html = '';
                rows.forEach((r, i) => {
                    const persen = Number(r.capaian_persen);
                    const warna = persen >= 100 ? 'emerald' : (persen >= 50 ? 'amber' : (r.total_aktivitas > 0 ? 'rose' : 'slate'));
                    const lebar = Math.min(100, persen);

                    html += '<tr class="hover:bg-slate-50">';
                    html += '<td class="py-3 px-3 text-center text-slate-400">' + (i + 1) + '</td>';
                    html += '<td class="py-3 px-3"><strong class="text-slate-900 block">' + r.nama_formatted + '</strong>' +
                            '<span class="text-[10px] text-slate-500">' + (r.nama_jabatan || '-') + '</span></td>';
                    html += '<td class="py-3 px-3 text-center font-black text-slate-900">' + r.jml_pemeriksaan + '</td>';
                    html += '<td class="py-3 px-3 text-center font-black text-slate-900">' + r.jml_validasi + '</td>';
                    html += '<td class="py-3 px-3 text-center text-slate-600">' + r.hari_cuti + '</td>';
                    html += '<td class="py-3 px-3 text-center text-slate-600">' + r.hari_kerja_efektif + '</td>';
                    html += '<td class="py-3 px-3 text-center"><span class="font-black text-slate-900">' + r.rata_harian + '</span>' +
                            '<span class="text-[10px] text-slate-400 block">target ' + r.target_harian + '</span></td>';
                    html += '<td class="py-3 px-3">' +
                            '<div class="flex items-center gap-2">' +
                            '<div class="flex-1 h-2 rounded-full bg-slate-100 overflow-hidden min-w-[60px]">' +
                            '<div class="h-full bg-' + warna + '-500" style="width:' + lebar + '%"></div></div>' +
                            '<span class="text-[11px] font-bold text-' + warna + '-700 w-10 text-right">' + persen + '%</span>' +
                            '</div></td>';
                    html += '</tr>';
                });

                body.innerHTML = html;
                if (window.lucide) lucide.createIcons();
            })
            .catch(err => {
                body.innerHTML = '<tr><td colspan="8" class="text-center py-6 text-rose-600">Perhitungan beban kerja gagal.</td></tr>';
                LIS.showError('Gagal Menghitung Beban Kerja', err, 'Data beban kerja tidak dapat dimuat.');
            });
    }

    document.addEventListener('DOMContentLoaded', muatBeban);
</script>
