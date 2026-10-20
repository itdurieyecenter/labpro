<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="<?= site_url('gudang') ?>" class="hover:text-emerald-700 transition-colors">Gudang</a>
                <span>/</span>
                <span class="text-emerald-800 font-semibold">Penerimaan</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="package-plus" class="w-6 h-6"></i>
                </span>
                <span>Penerimaan Barang &amp; Reagen</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Setiap penerimaan membentuk satu batch tersendiri agar masa kedaluwarsa dapat dilacak per lot</p>
        </div>

        <a href="<?= site_url('gudang/barang') ?>" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center gap-2 border border-slate-300">
            <i data-lucide="boxes" class="w-4 h-4"></i>
            <span>Master Barang</span>
        </a>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <!-- Form -->
        <div class="xl:col-span-1">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden sticky top-4">
                <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50">
                    <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="file-plus" class="w-4 h-4 text-emerald-700"></i>
                        Formulir Penerimaan
                    </h2>
                </div>

                <form id="form-terima" onsubmit="simpanTerima(event)" class="p-5 space-y-3">
                    <div>
                        <label class="lis-form-label">Barang <span class="req">*</span></label>
                        <select name="barang_id" id="tr-barang" required class="lis-form-control" onchange="pilihBarang()">
                            <option value="">-- Pilih Barang --</option>
                            <?php foreach ($barang_options as $b): ?>
                                <option value="<?= (int)$b['id'] ?>"
                                        data-satuan="<?= html_escape($b['satuan']) ?>"
                                        data-batch="<?= (int)$b['wajib_batch'] ?>">
                                    <?= html_escape($b['nama_barang']) ?> (<?= html_escape($b['kode_barang']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="lis-form-hint" id="tr-hint">&nbsp;</p>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="lis-form-label">Jumlah <span class="req">*</span></label>
                            <div class="flex items-center gap-1.5">
                                <input type="number" step="0.01" min="0.01" name="jumlah" id="tr-jumlah" required class="lis-form-control font-bold">
                                <span id="tr-satuan" class="text-xs text-slate-500 font-semibold w-12">&mdash;</span>
                            </div>
                        </div>
                        <div>
                            <label class="lis-form-label">Tanggal Terima <span class="req">*</span></label>
                            <input type="date" name="tanggal_terima" id="tr-tanggal" required class="lis-form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <div>
                            <label class="lis-form-label">Nomor Batch <span class="req tr-wajib hidden">*</span></label>
                            <input type="text" name="nomor_batch" id="tr-batch" class="lis-form-control font-mono" placeholder="Contoh: LOT-2026A">
                        </div>
                        <div>
                            <label class="lis-form-label">Kedaluwarsa <span class="req tr-wajib hidden">*</span></label>
                            <input type="date" name="tanggal_kedaluwarsa" id="tr-exp" class="lis-form-control">
                        </div>
                    </div>

                    <div>
                        <label class="lis-form-label">Harga Satuan</label>
                        <input type="number" step="0.01" min="0" name="harga_satuan" id="tr-harga" class="lis-form-control font-mono" placeholder="0">
                        <p class="lis-form-hint">Dipakai menghitung nilai persediaan.</p>
                    </div>

                    <div>
                        <label class="lis-form-label">Pemasok</label>
                        <input type="text" name="pemasok" id="tr-pemasok" class="lis-form-control" placeholder="PT / CV penyedia">
                    </div>

                    <div>
                        <label class="lis-form-label">Nomor Dokumen</label>
                        <input type="text" name="nomor_dokumen" id="tr-dokumen" class="lis-form-control font-mono" placeholder="Faktur / BAST / SPK">
                    </div>

                    <div>
                        <label class="lis-form-label">Keterangan</label>
                        <textarea name="keterangan" id="tr-keterangan" rows="2" class="lis-form-control" placeholder="Catatan penerimaan"></textarea>
                    </div>

                    <button type="submit" class="w-full px-5 py-3 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold flex items-center justify-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Simpan Penerimaan</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Riwayat -->
        <div class="xl:col-span-2">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                    <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="history" class="w-4 h-4 text-emerald-700"></i>
                        Mutasi Terakhir
                    </h2>
                    <a href="<?= site_url('gudang/kartu_stok') ?>" class="text-[11px] text-emerald-700 font-bold hover:underline">Kartu Stok &rarr;</a>
                </div>

                <div class="p-4 overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-bold bg-slate-50">
                                <th class="py-2.5 px-2">Nomor</th>
                                <th class="py-2.5 px-2">Tanggal</th>
                                <th class="py-2.5 px-2">Barang</th>
                                <th class="py-2.5 px-2 text-center">Tipe</th>
                                <th class="py-2.5 px-2 text-center">Jumlah</th>
                                <th class="py-2.5 px-2 text-center">Saldo</th>
                            </tr>
                        </thead>
                        <tbody id="riwayat-body" class="divide-y divide-slate-100 font-medium">
                            <?php if (empty($riwayat)): ?>
                                <tr><td colspan="6" class="text-center py-8 text-slate-400">Belum ada mutasi tercatat.</td></tr>
                            <?php else: ?>
                                <?php
                                $gaya = [
                                    'masuk' => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'Masuk'],
                                    'keluar' => ['bg-sky-50 text-sky-700 border-sky-200', 'Keluar'],
                                    'opname_plus' => ['bg-teal-50 text-teal-700 border-teal-200', 'Opname +'],
                                    'opname_minus' => ['bg-amber-50 text-amber-800 border-amber-200', 'Opname -'],
                                    'rusak' => ['bg-rose-50 text-rose-700 border-rose-200', 'Rusak'],
                                    'kedaluwarsa' => ['bg-rose-50 text-rose-700 border-rose-200', 'Kedaluwarsa'],
                                    'retur' => ['bg-slate-100 text-slate-600 border-slate-200', 'Retur'],
                                ];
                                foreach ($riwayat as $m):
                                    $g = $gaya[$m['tipe']] ?? $gaya['retur'];
                                ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="py-2.5 px-2 font-mono text-slate-900"><?= html_escape($m['nomor_transaksi']) ?></td>
                                        <td class="py-2.5 px-2 text-slate-600"><?= html_escape($m['tanggal']) ?></td>
                                        <td class="py-2.5 px-2 text-slate-700"><?= html_escape($m['nama_barang']) ?></td>
                                        <td class="py-2.5 px-2 text-center"><span class="px-2 py-0.5 rounded text-[10px] font-bold border <?= $g[0] ?>"><?= $g[1] ?></span></td>
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
    function pilihBarang() {
        const opt = document.getElementById('tr-barang').selectedOptions[0];
        const wajib = opt && opt.dataset.batch === '1';

        document.getElementById('tr-satuan').textContent = (opt && opt.dataset.satuan) ? opt.dataset.satuan : '—';
        document.querySelectorAll('.tr-wajib').forEach(el => el.classList.toggle('hidden', !wajib));
        document.getElementById('tr-batch').required = wajib;
        document.getElementById('tr-exp').required = wajib;
        document.getElementById('tr-hint').textContent = wajib
            ? 'Barang ini wajib mencantumkan nomor batch dan tanggal kedaluwarsa.'
            : 'Nomor batch dan kedaluwarsa opsional untuk barang ini.';
    }

    function simpanTerima(e) {
        e.preventDefault();
        const form = document.getElementById('form-terima');

        LIS.fetchJson('<?= site_url('gudang/simpan_penerimaan') ?>', { method: 'POST', body: new FormData(form) })
            .then(res => {
                LIS.alert('Penerimaan Tersimpan', res.message, 'success');
                form.reset();
                document.getElementById('tr-tanggal').value = '<?= date('Y-m-d') ?>';
                pilihBarang();
                setTimeout(() => location.reload(), 1200);
            })
            .catch(err => LIS.showError('Gagal Menyimpan Penerimaan', err, 'Penerimaan barang tidak dapat disimpan.'));
    }

    document.addEventListener('DOMContentLoaded', pilihBarang);
</script>
