<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-slate-400">Kepegawaian</span>
                <span>/</span>
                <span class="text-emerald-800 font-semibold">Kredensial STR & SIP</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="badge-check" class="w-6 h-6"></i>
                </span>
                <span>Pemantauan Masa Berlaku Kredensial</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Peringatan <?= (int)$config_kredensial['peringatan_hari'] ?> hari sebelum habis, kritis di bawah <?= (int)$config_kredensial['kritis_hari'] ?> hari
                &mdash; syarat kompetensi personel laboratorium terakreditasi
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <select id="filter-seksi" class="lis-form-control py-2 text-xs w-auto" onchange="muatKredensial()">
                <option value="all">Semua Seksi / Instalasi</option>
                <?php foreach ($seksi_ref as $s): ?>
                    <?php $nama = is_array($s) ? $s['nama'] : $s; ?>
                    <option value="<?= html_escape($nama) ?>"><?= html_escape($nama) ?></option>
                <?php endforeach; ?>
            </select>
            <select id="filter-status" class="lis-form-control py-2 text-xs w-auto" onchange="muatKredensial()">
                <option value="all">Semua Status</option>
                <option value="kedaluwarsa">Kedaluwarsa</option>
                <option value="kritis">Kritis</option>
                <option value="peringatan">Perlu Perhatian</option>
                <option value="kosong">Belum Diisi</option>
                <option value="aman">Aman</option>
            </select>
            <button type="button" onclick="window.print()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center gap-2 border border-slate-300">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak</span>
            </button>
        </div>
    </div>

    <?php if ($ringkasan['perlu_tindakan'] > 0): ?>
        <div class="bg-rose-50 border border-rose-300 rounded-2xl p-5 flex items-start gap-3">
            <i data-lucide="alert-octagon" class="w-5 h-5 text-rose-700 shrink-0 mt-0.5"></i>
            <div class="text-xs text-rose-900">
                <strong class="block mb-1"><?= (int)$ringkasan['perlu_tindakan'] ?> kredensial perlu tindakan segera.</strong>
                Personel dengan SIP kedaluwarsa tidak boleh menandatangani hasil uji. Kondisi ini menjadi temuan pada surveilans akreditasi
                ISO 15189 / 17025 dan perlu segera ditindaklanjuti ke DPMPTSP.
            </div>
        </div>
    <?php endif; ?>

    <!-- Ringkasan -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <?php
        $kartu = [
            ['label' => 'Kedaluwarsa', 'value' => $ringkasan['kedaluwarsa'], 'icon' => 'x-octagon', 'warna' => 'rose'],
            ['label' => 'Kritis', 'value' => $ringkasan['kritis'], 'icon' => 'alert-triangle', 'warna' => 'amber'],
            ['label' => 'Perlu Perhatian', 'value' => $ringkasan['peringatan'], 'icon' => 'clock', 'warna' => 'yellow'],
            ['label' => 'Belum Diisi', 'value' => $ringkasan['kosong'], 'icon' => 'help-circle', 'warna' => 'slate'],
            ['label' => 'Aman', 'value' => $ringkasan['aman'], 'icon' => 'shield-check', 'warna' => 'emerald'],
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
        <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="id-card" class="w-4 h-4 text-emerald-700"></i>
                Kredensial Personel Laboratorium
            </h2>
            <input type="search" id="filter-cari" class="lis-form-control py-1.5 text-xs w-auto" placeholder="Cari nama, NIP, nomor STR/SIP..." oninput="debounceKredensial()">
        </div>

        <div class="p-4 sm:p-5 overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-bold bg-slate-50">
                        <th class="py-3 px-3 w-12 text-center">No</th>
                        <th class="py-3 px-3">Pegawai</th>
                        <th class="py-3 px-3">Pendidikan</th>
                        <th class="py-3 px-3">STR</th>
                        <th class="py-3 px-3">SIP</th>
                        <th class="py-3 px-3 text-center w-28">Tindakan</th>
                    </tr>
                </thead>
                <tbody id="kredensial-body" class="divide-y divide-slate-100 font-medium"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const KREDENSIAL_URL = '<?= site_url('kepegawaian/fetch_kredensial') ?>';
    const PEGAWAI_URL = '<?= site_url('pegawai') ?>';

    const GAYA_STATUS = {
        kedaluwarsa:   ['bg-rose-50 text-rose-700 border-rose-200', 'Kedaluwarsa'],
        kritis:        ['bg-amber-50 text-amber-800 border-amber-300', 'Kritis'],
        peringatan:    ['bg-yellow-50 text-yellow-800 border-yellow-200', 'Perlu Perhatian'],
        aman:          ['bg-emerald-50 text-emerald-700 border-emerald-200', 'Aman'],
        seumur_hidup:  ['bg-sky-50 text-sky-700 border-sky-200', 'Seumur Hidup'],
        kosong:        ['bg-slate-100 text-slate-500 border-slate-200', 'Belum Diisi'],
        tidak_terbaca: ['bg-slate-100 text-slate-500 border-slate-200', 'Format Tidak Dikenali']
    };

    let timerCari = null;
    function debounceKredensial() {
        clearTimeout(timerCari);
        timerCari = setTimeout(muatKredensial, 350);
    }

    function selKredensial(nomor, info) {
        const gaya = GAYA_STATUS[info.status] || GAYA_STATUS.kosong;
        let html = '<div class="font-mono text-[11px] text-slate-900">' + (nomor || '-') + '</div>';
        html += '<span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold border ' + gaya[0] + '">' + gaya[1] + '</span>';

        if (info.tanggal) {
            const sisa = Number(info.sisa_hari);
            const teks = sisa < 0
                ? 'Habis ' + Math.abs(sisa) + ' hari lalu'
                : 'Sisa ' + sisa + ' hari';
            html += '<div class="text-[10px] text-slate-500 mt-0.5">' + info.label + ' &bull; ' + teks + '</div>';
        } else if (info.status !== 'kosong') {
            html += '<div class="text-[10px] text-slate-500 mt-0.5">' + info.label + '</div>';
        }
        return html;
    }

    function muatKredensial() {
        const q = new URLSearchParams({
            seksi_ruangan: document.getElementById('filter-seksi').value,
            status: document.getElementById('filter-status').value,
            search: document.getElementById('filter-cari').value
        });

        const body = document.getElementById('kredensial-body');
        body.innerHTML = '<tr><td colspan="6" class="text-center py-6 text-slate-400">Memuat data kredensial...</td></tr>';

        LIS.fetchJson(KREDENSIAL_URL + '?' + q.toString())
            .then(res => {
                const rows = res.data;
                if (!rows || rows.length === 0) {
                    body.innerHTML = '<tr><td colspan="6" class="text-center py-8 text-slate-400">Tidak ada pegawai yang cocok dengan filter.</td></tr>';
                    return;
                }

                let html = '';
                rows.forEach((r, i) => {
                    const mendesak = ['kedaluwarsa', 'kritis'].includes(r.status_terburuk);
                    html += '<tr class="hover:bg-slate-50' + (mendesak ? ' bg-rose-50/40' : '') + '">';
                    html += '<td class="py-3 px-3 text-center text-slate-400">' + (i + 1) + '</td>';
                    html += '<td class="py-3 px-3"><strong class="text-slate-900 block">' + r.nama_formatted + '</strong>' +
                            '<span class="text-[10px] text-slate-400 font-mono">NIP. ' + (r.nip || '-') + '</span>' +
                            '<span class="text-[10px] text-slate-500 block">' + (r.seksi_ruangan || '-') + '</span></td>';
                    html += '<td class="py-3 px-3 text-slate-600">' + (r.tingkat_pendidikan || '-') +
                            '<span class="text-[10px] text-slate-400 block">' + (r.jurusan || '-') + '</span></td>';
                    html += '<td class="py-3 px-3">' + selKredensial(r.nomor_str, r.str) + '</td>';
                    html += '<td class="py-3 px-3">' + selKredensial(r.nomor_sip, r.sip) + '</td>';
                    html += '<td class="py-3 px-3 text-center">' +
                            '<a href="' + PEGAWAI_URL + '" class="p-1.5 inline-flex rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700" title="Perbarui di Data Pegawai">' +
                            '<i data-lucide="pencil" class="w-3.5 h-3.5"></i></a></td>';
                    html += '</tr>';
                });

                body.innerHTML = html;
                if (window.lucide) lucide.createIcons();
            })
            .catch(err => {
                body.innerHTML = '<tr><td colspan="6" class="text-center py-6 text-rose-600">Data kredensial gagal dimuat.</td></tr>';
                LIS.showError('Gagal Memuat Kredensial', err, 'Data kredensial pegawai tidak dapat dimuat.');
            });
    }

    document.addEventListener('DOMContentLoaded', muatKredensial);
</script>
