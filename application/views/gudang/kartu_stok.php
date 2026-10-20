<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="<?= site_url('gudang') ?>" class="hover:text-emerald-700 transition-colors">Gudang</a>
                <span>/</span>
                <span class="text-emerald-800 font-semibold">Kartu Stok</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="scroll-text" class="w-6 h-6"></i>
                </span>
                <span>Kartu Stok Digital</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Seluruh pergerakan barang berurutan waktu, lengkap dengan batch asalnya</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <select id="filter-barang" class="lis-form-control py-2 text-xs w-auto min-w-[200px]" onchange="muatKartu()">
                <option value="">-- Pilih Barang --</option>
                <?php foreach ($barang_options as $b): ?>
                    <option value="<?= (int)$b['id'] ?>" <?= (int)$b['id'] === (int)$barang_id ? 'selected' : '' ?>>
                        <?= html_escape($b['nama_barang']) ?> (<?= html_escape($b['kode_barang']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="date" id="filter-mulai" value="<?= html_escape($mulai) ?>" class="lis-form-control py-2 text-xs w-auto" onchange="muatKartu()">
            <span class="text-xs text-slate-400">s/d</span>
            <input type="date" id="filter-selesai" value="<?= html_escape($selesai) ?>" class="lis-form-control py-2 text-xs w-auto" onchange="muatKartu()">
            <button type="button" onclick="window.print()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center gap-2 border border-slate-300">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Cetak</span>
            </button>
        </div>
    </div>

    <div id="kartu-info"></div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50">
            <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="arrow-left-right" class="w-4 h-4 text-emerald-700"></i>
                Riwayat Mutasi
            </h2>
        </div>
        <div class="p-4 sm:p-5 overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-bold bg-slate-50">
                        <th class="py-3 px-3">Tanggal</th>
                        <th class="py-3 px-3">Nomor</th>
                        <th class="py-3 px-3">Batch</th>
                        <th class="py-3 px-3 text-center">Tipe</th>
                        <th class="py-3 px-3 text-center">Masuk</th>
                        <th class="py-3 px-3 text-center">Keluar</th>
                        <th class="py-3 px-3 text-center">Saldo</th>
                        <th class="py-3 px-3">Tujuan / Keterangan</th>
                        <th class="py-3 px-3">Petugas</th>
                    </tr>
                </thead>
                <tbody id="kartu-body" class="divide-y divide-slate-100 font-medium">
                    <tr><td colspan="9" class="text-center py-10 text-slate-400">Pilih barang untuk menampilkan kartu stok.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const GAYA_TIPE = {
        masuk:        ['bg-emerald-50 text-emerald-700 border-emerald-200', 'Masuk'],
        keluar:       ['bg-sky-50 text-sky-700 border-sky-200', 'Keluar'],
        opname_plus:  ['bg-teal-50 text-teal-700 border-teal-200', 'Opname +'],
        opname_minus: ['bg-amber-50 text-amber-800 border-amber-200', 'Opname -'],
        rusak:        ['bg-rose-50 text-rose-700 border-rose-200', 'Rusak'],
        kedaluwarsa:  ['bg-rose-50 text-rose-700 border-rose-200', 'Kedaluwarsa'],
        retur:        ['bg-slate-100 text-slate-600 border-slate-200', 'Retur']
    };
    const TIPE_MASUK = ['masuk', 'opname_plus', 'retur'];

    function angka(n) {
        const v = Number(n);
        return Number.isInteger(v) ? v.toString() : v.toFixed(2).replace(/\.?0+$/, '');
    }

    function kartuKecil(label, nilai, warna) {
        return '<div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">' +
               '<span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">' + label + '</span>' +
               '<span class="text-2xl font-black text-' + warna + '-700">' + nilai + '</span></div>';
    }

    function muatKartu() {
        const barangId = document.getElementById('filter-barang').value;
        const body = document.getElementById('kartu-body');
        const info = document.getElementById('kartu-info');

        if (!barangId) {
            info.innerHTML = '';
            body.innerHTML = '<tr><td colspan="9" class="text-center py-10 text-slate-400">Pilih barang untuk menampilkan kartu stok.</td></tr>';
            return;
        }

        const q = new URLSearchParams({
            barang_id: barangId,
            mulai: document.getElementById('filter-mulai').value,
            selesai: document.getElementById('filter-selesai').value
        });

        body.innerHTML = '<tr><td colspan="9" class="text-center py-8 text-slate-400">Memuat kartu stok...</td></tr>';

        LIS.fetchJson('<?= site_url('gudang/fetch_kartu') ?>?' + q.toString())
            .then(res => {
                const d = res.data;
                const b = d.barang;
                const r = d.ringkasan;

                info.innerHTML =
                    '<div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs mb-4">' +
                    '<div class="flex flex-wrap items-start justify-between gap-3">' +
                    '<div><strong class="text-base text-slate-900 block">' + b.nama_barang + '</strong>' +
                    '<span class="text-[11px] text-slate-500 font-mono">' + b.kode_barang + ' &bull; ' + b.kategori + ' &bull; satuan ' + b.satuan + '</span>' +
                    (b.lokasi_simpan ? '<span class="text-[11px] text-slate-500 block">Lokasi: ' + b.lokasi_simpan + '</span>' : '') +
                    '</div></div></div>' +
                    '<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">' +
                    kartuKecil('Saldo Awal Periode', angka(r.saldo_awal), 'slate') +
                    kartuKecil('Total Masuk', angka(r.total_masuk), 'emerald') +
                    kartuKecil('Total Keluar', angka(r.total_keluar), 'sky') +
                    kartuKecil('Saldo Akhir', angka(r.saldo_akhir), 'teal') +
                    '</div>';

                if (!d.mutasi || d.mutasi.length === 0) {
                    body.innerHTML = '<tr><td colspan="9" class="text-center py-10 text-slate-400">Tidak ada mutasi pada periode ini.</td></tr>';
                    return;
                }

                let html = '';
                d.mutasi.forEach(m => {
                    const g = GAYA_TIPE[m.tipe] || GAYA_TIPE.retur;
                    const masuk = TIPE_MASUK.includes(m.tipe);

                    html += '<tr class="hover:bg-slate-50">';
                    html += '<td class="py-3 px-3 text-slate-700">' + m.tanggal + '</td>';
                    html += '<td class="py-3 px-3 font-mono text-slate-900">' + m.nomor_transaksi + '</td>';
                    html += '<td class="py-3 px-3 font-mono text-slate-600">' + (m.nomor_batch || '-') +
                            (m.tanggal_kedaluwarsa ? '<span class="text-[10px] text-slate-400 block">exp ' + m.tanggal_kedaluwarsa + '</span>' : '') + '</td>';
                    html += '<td class="py-3 px-3 text-center"><span class="px-2 py-0.5 rounded text-[10px] font-bold border ' + g[0] + '">' + g[1] + '</span></td>';
                    html += '<td class="py-3 px-3 text-center font-black text-emerald-700">' + (masuk ? angka(m.jumlah) : '<span class="text-slate-300">-</span>') + '</td>';
                    html += '<td class="py-3 px-3 text-center font-black text-sky-700">' + (!masuk ? angka(m.jumlah) : '<span class="text-slate-300">-</span>') + '</td>';
                    html += '<td class="py-3 px-3 text-center font-black text-slate-900">' + angka(m.saldo_sesudah) + '</td>';
                    html += '<td class="py-3 px-3 text-slate-600">' + (m.tujuan || '') +
                            (m.keterangan ? '<span class="text-[10px] text-slate-400 block">' + m.keterangan + '</span>' : '') + '</td>';
                    html += '<td class="py-3 px-3 text-slate-600">' + (m.user_nama || '-') + '</td>';
                    html += '</tr>';
                });

                body.innerHTML = html;
                if (window.lucide) lucide.createIcons();
            })
            .catch(err => {
                body.innerHTML = '<tr><td colspan="9" class="text-center py-8 text-rose-600">Kartu stok gagal dimuat.</td></tr>';
                LIS.showError('Gagal Memuat Kartu Stok', err, 'Riwayat mutasi tidak dapat dimuat.');
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (document.getElementById('filter-barang').value) muatKartu();
    });
</script>
