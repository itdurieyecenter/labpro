<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php $angka = function ($n) { return rtrim(rtrim(number_format((float)$n, 2, ',', '.'), '0'), ','); }; ?>

<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="<?= site_url('gudang') ?>" class="hover:text-emerald-700 transition-colors">Gudang</a>
                <span>/</span>
                <span class="text-emerald-800 font-semibold">Stock Opname</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="clipboard-check" class="w-6 h-6"></i>
                </span>
                <span>Stock Opname Gudang</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Penyesuaian persediaan fisik terhadap catatan sistem, selisihnya dibukukan sebagai mutasi</p>
        </div>

        <?php if ($can_create && !$aktif): ?>
            <button type="button" onclick="bukaSesi()" class="px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-2 shadow-sm border border-emerald-600">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>+ Buka Sesi Opname</span>
            </button>
        <?php endif; ?>
    </div>

    <?php if ($aktif): ?>
        <!-- Sesi berjalan -->
        <div class="bg-white rounded-2xl border-2 border-emerald-300 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 bg-emerald-50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">SEDANG BERJALAN</span>
                        <strong class="text-sm text-slate-900 font-mono"><?= html_escape($aktif['nomor_opname']) ?></strong>
                    </div>
                    <p class="text-[11px] text-slate-600 mt-1">
                        Tanggal <?= html_escape($aktif['tanggal']) ?> &bull; Petugas <?= html_escape($aktif['petugas_nama'] ?: '-') ?>
                        &bull; <?= (int)$aktif['total_item'] ?> item
                        <?= $aktif['catatan'] ? ' &bull; ' . html_escape($aktif['catatan']) : '' ?>
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <?php if ($can_delete): ?>
                        <button type="button" onclick="batalkanSesi(<?= (int)$aktif['id'] ?>, '<?= html_escape(addslashes($aktif['nomor_opname'])) ?>')" class="px-4 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold border border-slate-300">
                            Batalkan
                        </button>
                    <?php endif; ?>
                    <?php if ($can_edit): ?>
                        <button type="button" onclick="selesaikanSesi(<?= (int)$aktif['id'] ?>, '<?= html_escape(addslashes($aktif['nomor_opname'])) ?>')" class="px-5 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold flex items-center gap-2">
                            <i data-lucide="check-check" class="w-4 h-4"></i>
                            <span>Tutup &amp; Terapkan Selisih</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="px-5 py-3 bg-amber-50 border-b border-amber-200 text-[11px] text-amber-900">
                Isi kolom <strong>Stok Fisik</strong> sesuai hasil hitungan di gudang. Baris yang dibiarkan kosong dianggap belum dihitung
                dan tidak akan disesuaikan saat sesi ditutup.
            </div>

            <div class="p-4 sm:p-5 overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-bold bg-slate-50">
                            <th class="py-3 px-3 w-12 text-center">No</th>
                            <th class="py-3 px-3">Barang</th>
                            <th class="py-3 px-3 text-center w-28">Stok Sistem</th>
                            <th class="py-3 px-3 text-center w-32">Stok Fisik</th>
                            <th class="py-3 px-3 text-center w-28">Selisih</th>
                            <th class="py-3 px-3">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <?php foreach ($aktif['detail'] as $i => $d): ?>
                            <tr class="hover:bg-slate-50" id="row-<?= (int)$d['id'] ?>">
                                <td class="py-3 px-3 text-center text-slate-400"><?= $i + 1 ?></td>
                                <td class="py-3 px-3">
                                    <strong class="text-slate-900 block"><?= html_escape($d['nama_barang']) ?></strong>
                                    <span class="text-[10px] text-slate-400 font-mono"><?= html_escape($d['kode_barang']) ?> &bull; <?= html_escape($d['satuan']) ?></span>
                                </td>
                                <td class="py-3 px-3 text-center font-black text-slate-900" id="sistem-<?= (int)$d['id'] ?>">
                                    <?= $angka($d['stok_sistem']) ?>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <input type="number" step="0.01" min="0"
                                           class="lis-form-control py-1.5 text-center font-bold w-full"
                                           id="fisik-<?= (int)$d['id'] ?>"
                                           value="<?= $d['stok_fisik'] !== null ? $angka($d['stok_fisik']) : '' ?>"
                                           <?= $can_edit ? '' : 'disabled' ?>
                                           onchange="simpanHitungan(<?= (int)$d['id'] ?>)">
                                </td>
                                <td class="py-3 px-3 text-center" id="selisih-<?= (int)$d['id'] ?>">
                                    <?php if ($d['stok_fisik'] === null): ?>
                                        <span class="text-[11px] text-slate-400">belum dihitung</span>
                                    <?php else: ?>
                                        <?php $s = (float)$d['selisih']; ?>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold border <?= $s > 0 ? 'bg-teal-50 text-teal-700 border-teal-200' : ($s < 0 ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-slate-100 text-slate-500 border-slate-200') ?>">
                                            <?= $s > 0 ? '+' : '' ?><?= $angka($s) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-3">
                                    <input type="text" class="lis-form-control py-1.5 text-xs w-full"
                                           id="ket-<?= (int)$d['id'] ?>"
                                           value="<?= html_escape($d['keterangan'] ?: '') ?>"
                                           placeholder="Alasan selisih"
                                           <?= $can_edit ? '' : 'disabled' ?>
                                           onchange="simpanHitungan(<?= (int)$d['id'] ?>)">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-10 text-center">
            <i data-lucide="clipboard-list" class="w-10 h-10 text-slate-300 mx-auto mb-3"></i>
            <p class="text-sm font-bold text-slate-600">Tidak ada sesi opname yang sedang berjalan.</p>
            <p class="text-xs text-slate-400 mt-1">Buka sesi baru untuk mulai menghitung persediaan fisik.</p>
        </div>
    <?php endif; ?>

    <!-- Riwayat sesi -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50">
            <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="history" class="w-4 h-4 text-emerald-700"></i>
                Riwayat Sesi Opname
            </h2>
        </div>
        <div class="p-4 overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-bold bg-slate-50">
                        <th class="py-2.5 px-2">Nomor</th>
                        <th class="py-2.5 px-2">Tanggal</th>
                        <th class="py-2.5 px-2">Petugas</th>
                        <th class="py-2.5 px-2 text-center">Item</th>
                        <th class="py-2.5 px-2 text-center">Selisih Lebih</th>
                        <th class="py-2.5 px-2 text-center">Selisih Kurang</th>
                        <th class="py-2.5 px-2 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    <?php if (empty($daftar)): ?>
                        <tr><td colspan="7" class="text-center py-8 text-slate-400">Belum pernah ada sesi opname.</td></tr>
                    <?php else: ?>
                        <?php
                        $gaya_status = [
                            'draft' => ['bg-amber-50 text-amber-800 border-amber-200', 'Berjalan'],
                            'selesai' => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'Selesai'],
                            'dibatalkan' => ['bg-slate-100 text-slate-500 border-slate-200', 'Dibatalkan'],
                        ];
                        foreach ($daftar as $o):
                            $g = $gaya_status[$o['status']] ?? $gaya_status['draft'];
                        ?>
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5 px-2 font-mono text-slate-900"><?= html_escape($o['nomor_opname']) ?></td>
                                <td class="py-2.5 px-2 text-slate-600"><?= html_escape($o['tanggal']) ?></td>
                                <td class="py-2.5 px-2 text-slate-700"><?= html_escape($o['petugas_nama'] ?: '-') ?></td>
                                <td class="py-2.5 px-2 text-center text-slate-600"><?= (int)$o['total_item'] ?></td>
                                <td class="py-2.5 px-2 text-center font-bold text-teal-700"><?= $angka($o['total_selisih_lebih']) ?></td>
                                <td class="py-2.5 px-2 text-center font-bold text-rose-700"><?= $angka($o['total_selisih_kurang']) ?></td>
                                <td class="py-2.5 px-2 text-center"><span class="px-2 py-0.5 rounded text-[10px] font-bold border <?= $g[0] ?>"><?= $g[1] ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function angka(n) {
        const v = Number(n);
        return Number.isInteger(v) ? v.toString() : v.toFixed(2).replace(/\.?0+$/, '');
    }

    function bukaSesi() {
        Swal.fire({
            title: 'Buka Sesi Stock Opname?',
            html: 'Seluruh barang aktif akan dimuat beserta stok sistem saat ini sebagai pembanding.' +
                  '<br><span class="text-xs text-slate-500">Hanya boleh ada satu sesi berjalan dalam satu waktu.</span>',
            input: 'text',
            inputLabel: 'Catatan sesi (opsional)',
            inputPlaceholder: 'Contoh: Opname rutin triwulan IV',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#047857',
            confirmButtonText: 'Buka Sesi',
            cancelButtonText: 'Batal'
        }).then(res => {
            if (!res.isConfirmed) return;

            const body = new FormData();
            body.append('tanggal', new Date().toISOString().slice(0, 10));
            body.append('catatan', res.value || '');

            LIS.fetchJson('<?= site_url('gudang/buat_opname') ?>', { method: 'POST', body: body })
                .then(res => {
                    LIS.toast('success', res.message);
                    setTimeout(() => location.reload(), 800);
                })
                .catch(err => LIS.showError('Gagal Membuka Sesi', err, 'Sesi opname tidak dapat dibuka.'));
        });
    }

    function simpanHitungan(detailId) {
        const fisik = document.getElementById('fisik-' + detailId).value;
        const ket = document.getElementById('ket-' + detailId).value;

        const body = new FormData();
        body.append('detail_id', detailId);
        body.append('stok_fisik', fisik);
        body.append('keterangan', ket);

        LIS.fetchJson('<?= site_url('gudang/simpan_hitungan') ?>', { method: 'POST', body: body })
            .then(res => {
                const d = res.data;
                document.getElementById('sistem-' + detailId).textContent = angka(d.stok_sistem);

                const sel = document.getElementById('selisih-' + detailId);
                if (d.stok_fisik === null) {
                    sel.innerHTML = '<span class="text-[11px] text-slate-400">belum dihitung</span>';
                    return;
                }

                const s = Number(d.selisih);
                const gaya = s > 0 ? 'bg-teal-50 text-teal-700 border-teal-200'
                          : (s < 0 ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-slate-100 text-slate-500 border-slate-200');
                sel.innerHTML = '<span class="px-2 py-0.5 rounded text-[10px] font-bold border ' + gaya + '">' +
                                (s > 0 ? '+' : '') + angka(s) + '</span>';
            })
            .catch(err => LIS.showError('Gagal Menyimpan Hitungan', err, 'Hasil hitungan tidak dapat disimpan.'));
    }

    function selesaikanSesi(id, nomor) {
        Swal.fire({
            title: 'Tutup Sesi Opname?',
            html: 'Sesi <strong>' + nomor + '</strong> akan ditutup dan seluruh selisih dibukukan sebagai mutasi penyesuaian.' +
                  '<br><span class="text-xs text-slate-500">Selisih kurang menggerus batch mengikuti antrian FIFO, selisih lebih masuk ke batch terbaru. Tindakan ini tidak dapat dibatalkan.</span>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#047857',
            confirmButtonText: 'Ya, Tutup & Terapkan',
            cancelButtonText: 'Batal'
        }).then(res => {
            if (!res.isConfirmed) return;

            LIS.fetchJson('<?= site_url('gudang/selesaikan_opname') ?>/' + id, { method: 'POST' })
                .then(res => {
                    LIS.alert('Opname Selesai', res.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                })
                .catch(err => LIS.showError('Gagal Menutup Sesi', err, 'Penyesuaian opname tidak dapat diterapkan.'));
        });
    }

    function batalkanSesi(id, nomor) {
        Swal.fire({
            title: 'Batalkan Sesi Opname?',
            html: 'Sesi <strong>' + nomor + '</strong> dibatalkan tanpa menyesuaikan stok sama sekali.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Ya, Batalkan',
            cancelButtonText: 'Tutup'
        }).then(res => {
            if (!res.isConfirmed) return;

            LIS.fetchJson('<?= site_url('gudang/batalkan_opname') ?>/' + id, { method: 'POST' })
                .then(res => {
                    LIS.toast('success', res.message);
                    setTimeout(() => location.reload(), 800);
                })
                .catch(err => LIS.showError('Gagal Membatalkan Sesi', err, 'Sesi opname tidak dapat dibatalkan.'));
        });
    }
</script>
