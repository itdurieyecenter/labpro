<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="<?= site_url('gudang') ?>" class="hover:text-emerald-700 transition-colors">Gudang</a>
                <span>/</span>
                <span class="text-emerald-800 font-semibold">Pemakaian</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="package-minus" class="w-6 h-6"></i>
                </span>
                <span>Pemakaian &amp; Pengeluaran Barang</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Batch digerus otomatis dengan metode <strong><?= html_escape($config_gudang['metode_keluar']) ?></strong>
                <?= $config_gudang['metode_keluar'] === 'FIFO' ? '(batch yang paling dahulu diterima)' : '(batch yang paling dahulu kedaluwarsa)' ?>
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <!-- Form -->
        <div class="xl:col-span-1">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden sticky top-4">
                <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50">
                    <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="file-minus" class="w-4 h-4 text-emerald-700"></i>
                        Formulir Pengeluaran
                    </h2>
                </div>

                <form id="form-keluar" onsubmit="simpanKeluar(event)" class="p-5 space-y-3">
                    <div>
                        <label class="lis-form-label">Barang <span class="req">*</span></label>
                        <select name="barang_id" id="kl-barang" required class="lis-form-control" onchange="hitungSimulasi()">
                            <option value="">-- Pilih Barang --</option>
                            <?php foreach ($barang_options as $b): ?>
                                <option value="<?= (int)$b['id'] ?>" data-satuan="<?= html_escape($b['satuan']) ?>">
                                    <?= html_escape($b['nama_barang']) ?> (<?= html_escape($b['kode_barang']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="lis-form-label">Jumlah <span class="req">*</span></label>
                            <div class="flex items-center gap-1.5">
                                <input type="number" step="0.01" min="0.01" name="jumlah" id="kl-jumlah" required class="lis-form-control font-bold" oninput="debounceSimulasi()">
                                <span id="kl-satuan" class="text-xs text-slate-500 font-semibold w-12">&mdash;</span>
                            </div>
                        </div>
                        <div>
                            <label class="lis-form-label">Tanggal <span class="req">*</span></label>
                            <input type="date" name="tanggal" id="kl-tanggal" required class="lis-form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>

                    <div>
                        <label class="lis-form-label">Jenis Pengeluaran</label>
                        <select name="tipe" id="kl-tipe" class="lis-form-control">
                            <option value="keluar">Pemakaian Rutin</option>
                            <option value="rusak">Rusak / Pecah</option>
                            <option value="kedaluwarsa">Pemusnahan Kedaluwarsa</option>
                        </select>
                    </div>

                    <div>
                        <label class="lis-form-label">Tujuan / Unit Peminta</label>
                        <input type="text" name="tujuan" id="kl-tujuan" class="lis-form-control" placeholder="Contoh: Instalasi Kimia Klinik">
                    </div>

                    <div>
                        <label class="lis-form-label">Keterangan</label>
                        <textarea name="keterangan" id="kl-keterangan" rows="2" class="lis-form-control" placeholder="Catatan pengeluaran"></textarea>
                    </div>

                    <button type="submit" id="kl-submit" class="w-full px-5 py-3 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold flex items-center justify-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Simpan Pengeluaran</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Pratinjau & riwayat -->
        <div class="xl:col-span-2 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                    <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="layers" class="w-4 h-4 text-emerald-700"></i>
                        Pratinjau Batch yang Akan Terpakai
                    </h2>
                    <span id="sim-metode" class="text-[11px] font-bold text-emerald-700"><?= html_escape($config_gudang['metode_keluar']) ?></span>
                </div>
                <div id="sim-body" class="p-5">
                    <p class="text-xs text-slate-400 text-center py-6">Pilih barang dan isi jumlah untuk melihat batch mana yang akan terpakai.</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50">
                    <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="history" class="w-4 h-4 text-emerald-700"></i>
                        Mutasi Terakhir
                    </h2>
                </div>
                <div class="p-4 overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-bold bg-slate-50">
                                <th class="py-2.5 px-2">Nomor</th>
                                <th class="py-2.5 px-2">Tanggal</th>
                                <th class="py-2.5 px-2">Barang</th>
                                <th class="py-2.5 px-2">Tujuan</th>
                                <th class="py-2.5 px-2 text-center">Jumlah</th>
                                <th class="py-2.5 px-2 text-center">Saldo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium">
                            <?php if (empty($riwayat)): ?>
                                <tr><td colspan="6" class="text-center py-8 text-slate-400">Belum ada mutasi tercatat.</td></tr>
                            <?php else: ?>
                                <?php foreach ($riwayat as $m): ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="py-2.5 px-2 font-mono text-slate-900"><?= html_escape($m['nomor_transaksi']) ?></td>
                                        <td class="py-2.5 px-2 text-slate-600"><?= html_escape($m['tanggal']) ?></td>
                                        <td class="py-2.5 px-2 text-slate-700"><?= html_escape($m['nama_barang']) ?></td>
                                        <td class="py-2.5 px-2 text-slate-600"><?= html_escape($m['tujuan'] ?: '-') ?></td>
                                        <td class="py-2.5 px-2 text-center font-black text-slate-900"><?= rtrim(rtrim(number_format((float)$m['jumlah'], 2, ',', '.'), '0'), ',') ?></td>
                                        <td class="py-2.5 px-2 text-center text-slate-600"><?= rtrim(rtrim(number_format((float)$m['saldo_sesudah'], 2, ',', '.'), '0'), ',') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const GAYA_EXP = {
        kedaluwarsa:   ['bg-rose-50 text-rose-700 border-rose-200', 'Kedaluwarsa'],
        kritis:        ['bg-amber-50 text-amber-800 border-amber-300', 'Kritis'],
        peringatan:    ['bg-yellow-50 text-yellow-800 border-yellow-200', 'Perhatian'],
        aman:          ['bg-emerald-50 text-emerald-700 border-emerald-200', 'Aman'],
        tanpa_tanggal: ['bg-slate-100 text-slate-500 border-slate-200', 'Tanpa Tanggal']
    };

    function angka(n) {
        const v = Number(n);
        return Number.isInteger(v) ? v.toString() : v.toFixed(2).replace(/\.?0+$/, '');
    }

    let timerSim = null;
    function debounceSimulasi() {
        clearTimeout(timerSim);
        timerSim = setTimeout(hitungSimulasi, 350);
    }

    function hitungSimulasi() {
        const opt = document.getElementById('kl-barang').selectedOptions[0];
        document.getElementById('kl-satuan').textContent = (opt && opt.dataset.satuan) ? opt.dataset.satuan : '—';

        const barangId = document.getElementById('kl-barang').value;
        const jumlah = document.getElementById('kl-jumlah').value;
        const body = document.getElementById('sim-body');

        if (!barangId || !jumlah || Number(jumlah) <= 0) {
            body.innerHTML = '<p class="text-xs text-slate-400 text-center py-6">Pilih barang dan isi jumlah untuk melihat batch mana yang akan terpakai.</p>';
            return;
        }

        LIS.fetchJson('<?= site_url('gudang/simulasi') ?>?barang_id=' + barangId + '&jumlah=' + jumlah)
            .then(res => {
                const d = res.data;
                document.getElementById('sim-metode').textContent = d.metode;

                let html = '<div class="flex flex-wrap items-center gap-3 mb-4 text-xs">';
                html += '<span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-semibold">Stok tersedia: <strong>' + angka(d.stok_tersedia) + '</strong></span>';
                html += '<span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-semibold">Diminta: <strong>' + angka(d.diminta) + '</strong></span>';
                if (d.cukup) {
                    html += '<span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">Stok mencukupi</span>';
                } else {
                    html += '<span class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 font-bold border border-rose-200">Kurang ' + angka(d.kekurangan) + '</span>';
                }
                html += '</div>';

                if (!d.rencana || d.rencana.length === 0) {
                    html += '<p class="text-xs text-rose-600 text-center py-4">Tidak ada batch tersedia untuk barang ini.</p>';
                } else {
                    html += '<table class="w-full text-left text-xs border-collapse">';
                    html += '<thead><tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] font-bold bg-slate-50">';
                    html += '<th class="py-2 px-2 w-10 text-center">#</th><th class="py-2 px-2">Batch</th><th class="py-2 px-2">Kedaluwarsa</th>';
                    html += '<th class="py-2 px-2 text-center">Tersedia</th><th class="py-2 px-2 text-center">Diambil</th><th class="py-2 px-2 text-center">Sisa</th></tr></thead><tbody>';

                    d.rencana.forEach((r, i) => {
                        const g = GAYA_EXP[r.status_kedaluwarsa.status] || GAYA_EXP.aman;
                        const sisaBatch = Number(r.tersedia) - Number(r.diambil);
                        html += '<tr class="border-b border-slate-100">';
                        html += '<td class="py-2 px-2 text-center"><span class="inline-flex items-center justify-center w-5 h-5 rounded bg-emerald-700 text-amber-200 font-black text-[10px]">' + (i + 1) + '</span></td>';
                        html += '<td class="py-2 px-2 font-mono text-slate-900">' + (r.nomor_batch || '-') + '</td>';
                        html += '<td class="py-2 px-2">' + (r.tanggal_kedaluwarsa || '<span class="text-slate-400">-</span>') +
                                ' <span class="px-1.5 py-0.5 rounded text-[9px] font-bold border ' + g[0] + '">' + g[1] + '</span></td>';
                        html += '<td class="py-2 px-2 text-center text-slate-600">' + angka(r.tersedia) + '</td>';
                        html += '<td class="py-2 px-2 text-center font-black text-emerald-800">' + angka(r.diambil) + '</td>';
                        html += '<td class="py-2 px-2 text-center text-slate-600">' + angka(sisaBatch) + '</td>';
                        html += '</tr>';
                    });
                    html += '</tbody></table>';

                    const adaExp = d.rencana.some(r => r.status_kedaluwarsa.status === 'kedaluwarsa');
                    if (adaExp) {
                        html += '<div class="mt-3 p-3 rounded-xl bg-rose-50 border border-rose-200 text-[11px] text-rose-800">';
                        html += '<strong>Perhatian:</strong> antrian ini memuat batch yang sudah kedaluwarsa. Reagen kedaluwarsa tidak boleh dipakai untuk pemeriksaan &mdash; keluarkan dengan jenis "Pemusnahan Kedaluwarsa".';
                        html += '</div>';
                    }
                }

                body.innerHTML = html;
                if (window.lucide) lucide.createIcons();
            })
            .catch(err => {
                body.innerHTML = '<p class="text-xs text-rose-600 text-center py-4">' + (err.message || 'Simulasi gagal.') + '</p>';
            });
    }

    function simpanKeluar(e) {
        e.preventDefault();
        const form = document.getElementById('form-keluar');

        LIS.fetchJson('<?= site_url('gudang/simpan_pemakaian') ?>', { method: 'POST', body: new FormData(form) })
            .then(res => {
                LIS.alert('Pengeluaran Tersimpan', res.message, 'success');
                form.reset();
                document.getElementById('kl-tanggal').value = '<?= date('Y-m-d') ?>';
                setTimeout(() => location.reload(), 1200);
            })
            .catch(err => LIS.showError('Gagal Menyimpan Pengeluaran', err, 'Pengeluaran barang tidak dapat disimpan.'));
    }
</script>
