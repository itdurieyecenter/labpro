<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
$gaya_stok = [
    'habis'   => ['bg-rose-50 text-rose-700 border-rose-200', 'Habis'],
    'kritis'  => ['bg-amber-50 text-amber-800 border-amber-300', 'Kritis'],
    'menipis' => ['bg-yellow-50 text-yellow-800 border-yellow-200', 'Menipis'],
    'aman'    => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'Aman'],
];
$gaya_exp = [
    'kedaluwarsa'   => ['bg-rose-50 text-rose-700 border-rose-200', 'Kedaluwarsa'],
    'kritis'        => ['bg-amber-50 text-amber-800 border-amber-300', 'Kritis'],
    'peringatan'    => ['bg-yellow-50 text-yellow-800 border-yellow-200', 'Perlu Perhatian'],
    'aman'          => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'Aman'],
    'tanpa_tanggal' => ['bg-slate-100 text-slate-500 border-slate-200', 'Tanpa Tanggal'],
];
$angka = function ($n) { return rtrim(rtrim(number_format((float)$n, 2, ',', '.'), '0'), ','); };
?>

<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-emerald-800 font-semibold">Gudang &amp; Logistik</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="package" class="w-6 h-6"></i>
                </span>
                <span>Monitoring Stok Reagen &amp; BHP</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Metode pengeluaran persediaan: <strong><?= html_escape($ringkasan['metode_keluar']) ?></strong>
                &mdash; nilai persediaan Rp <?= number_format($ringkasan['nilai_persediaan'], 0, ',', '.') ?>
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="<?= site_url('gudang/penerimaan') ?>" class="px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-2 shadow-sm border border-emerald-600">
                <i data-lucide="package-plus" class="w-4 h-4"></i>
                <span>Terima Barang</span>
            </a>
            <a href="<?= site_url('gudang/pemakaian') ?>" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center gap-2 border border-slate-300">
                <i data-lucide="package-minus" class="w-4 h-4"></i>
                <span>Keluarkan</span>
            </a>
        </div>
    </div>

    <?php if ($ringkasan['batch_kedaluwarsa'] > 0 || $ringkasan['stok_habis'] > 0): ?>
        <div class="bg-rose-50 border border-rose-300 rounded-2xl p-5 flex items-start gap-3">
            <i data-lucide="alert-octagon" class="w-5 h-5 text-rose-700 shrink-0 mt-0.5"></i>
            <div class="text-xs text-rose-900">
                <strong class="block mb-1">Perlu tindakan segera.</strong>
                <?php if ($ringkasan['batch_kedaluwarsa'] > 0): ?>
                    <?= (int)$ringkasan['batch_kedaluwarsa'] ?> batch sudah melewati masa kedaluwarsa dan masih tercatat bersisa &mdash;
                    reagen kedaluwarsa tidak boleh dipakai dan perlu dikeluarkan sebagai pemusnahan.
                <?php endif; ?>
                <?php if ($ringkasan['stok_habis'] > 0): ?>
                    <?= (int)$ringkasan['stok_habis'] ?> jenis barang stoknya habis.
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Ringkasan -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <?php
        $kartu = [
            ['label' => 'Jenis Barang Aktif', 'value' => $ringkasan['total_barang'], 'icon' => 'boxes', 'warna' => 'slate'],
            ['label' => 'Perlu Dipesan Ulang', 'value' => $ringkasan['perlu_pesan'], 'icon' => 'shopping-cart', 'warna' => 'amber'],
            ['label' => 'Stok Habis', 'value' => $ringkasan['stok_habis'], 'icon' => 'package-x', 'warna' => 'rose'],
            ['label' => 'Batch Kedaluwarsa', 'value' => $ringkasan['batch_kedaluwarsa'], 'icon' => 'calendar-x', 'warna' => 'rose'],
            ['label' => 'Mutasi Bulan Ini', 'value' => $ringkasan['mutasi_bulan_ini'], 'icon' => 'arrow-left-right', 'warna' => 'emerald'],
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

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <!-- Titik pemesanan ulang -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="shopping-cart" class="w-4 h-4 text-amber-600"></i>
                    Di Bawah Titik Pemesanan Ulang
                </h2>
                <span class="text-[11px] text-slate-500"><?= count($stok_kritis) ?> item</span>
            </div>
            <div class="p-4 overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-bold bg-slate-50">
                            <th class="py-2.5 px-2">Barang</th>
                            <th class="py-2.5 px-2 text-center">Stok</th>
                            <th class="py-2.5 px-2 text-center">Minimum</th>
                            <th class="py-2.5 px-2 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <?php if (empty($stok_kritis)): ?>
                            <tr><td colspan="4" class="text-center py-7 text-slate-400">Seluruh stok di atas titik pemesanan ulang.</td></tr>
                        <?php else: ?>
                            <?php foreach ($stok_kritis as $s): ?>
                                <?php $g = $gaya_stok[$s['status_stok']] ?? $gaya_stok['aman']; ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="py-2.5 px-2">
                                        <strong class="text-slate-900 block"><?= html_escape($s['nama_barang']) ?></strong>
                                        <span class="text-[10px] text-slate-400 font-mono"><?= html_escape($s['kode_barang']) ?></span>
                                    </td>
                                    <td class="py-2.5 px-2 text-center font-black text-slate-900"><?= $angka($s['stok']) ?> <span class="text-[10px] font-normal text-slate-400"><?= html_escape($s['satuan']) ?></span></td>
                                    <td class="py-2.5 px-2 text-center text-slate-600"><?= $angka($s['stok_minimum']) ?></td>
                                    <td class="py-2.5 px-2 text-center">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold border <?= $g[0] ?>"><?= $g[1] ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Kedaluwarsa -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="calendar-x" class="w-4 h-4 text-rose-600"></i>
                    Batch Mendekati Kedaluwarsa
                </h2>
                <span class="text-[11px] text-slate-500"><?= count($kedaluwarsa) ?> batch</span>
            </div>
            <div class="p-4 overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-bold bg-slate-50">
                            <th class="py-2.5 px-2">Barang / Batch</th>
                            <th class="py-2.5 px-2 text-center">Sisa</th>
                            <th class="py-2.5 px-2">Kedaluwarsa</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <?php if (empty($kedaluwarsa)): ?>
                            <tr><td colspan="3" class="text-center py-7 text-slate-400">Tidak ada batch yang mendekati kedaluwarsa.</td></tr>
                        <?php else: ?>
                            <?php foreach ($kedaluwarsa as $b): ?>
                                <?php
                                $st = $b['status_kedaluwarsa'];
                                $g = $gaya_exp[$st['status']] ?? $gaya_exp['aman'];
                                $sisa = $st['sisa_hari'];
                                ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="py-2.5 px-2">
                                        <strong class="text-slate-900 block"><?= html_escape($b['nama_barang']) ?></strong>
                                        <span class="text-[10px] text-slate-400 font-mono">Batch <?= html_escape($b['nomor_batch'] ?: '-') ?></span>
                                    </td>
                                    <td class="py-2.5 px-2 text-center font-black text-slate-900"><?= $angka($b['jumlah_sisa']) ?> <span class="text-[10px] font-normal text-slate-400"><?= html_escape($b['satuan']) ?></span></td>
                                    <td class="py-2.5 px-2">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold border <?= $g[0] ?>"><?= $g[1] ?></span>
                                        <div class="text-[10px] text-slate-500 mt-0.5">
                                            <?= html_escape($b['tanggal_kedaluwarsa']) ?>
                                            <?= $sisa !== null ? ($sisa < 0 ? ' &bull; lewat ' . abs($sisa) . ' hari' : ' &bull; sisa ' . $sisa . ' hari') : '' ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Mutasi terakhir -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="arrow-left-right" class="w-4 h-4 text-emerald-700"></i>
                Mutasi Terakhir
            </h2>
            <a href="<?= site_url('gudang/kartu_stok') ?>" class="text-[11px] text-emerald-700 font-bold hover:underline">Lihat Kartu Stok &rarr;</a>
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
                        <th class="py-2.5 px-2">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    <?php if (empty($mutasi_terakhir)): ?>
                        <tr><td colspan="7" class="text-center py-7 text-slate-400">Belum ada mutasi tercatat.</td></tr>
                    <?php else: ?>
                        <?php
                        $gaya_tipe = [
                            'masuk' => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'Masuk'],
                            'keluar' => ['bg-sky-50 text-sky-700 border-sky-200', 'Keluar'],
                            'opname_plus' => ['bg-teal-50 text-teal-700 border-teal-200', 'Opname +'],
                            'opname_minus' => ['bg-amber-50 text-amber-800 border-amber-200', 'Opname -'],
                            'rusak' => ['bg-rose-50 text-rose-700 border-rose-200', 'Rusak'],
                            'kedaluwarsa' => ['bg-rose-50 text-rose-700 border-rose-200', 'Kedaluwarsa'],
                            'retur' => ['bg-slate-100 text-slate-600 border-slate-200', 'Retur'],
                        ];
                        foreach ($mutasi_terakhir as $m):
                            $g = $gaya_tipe[$m['tipe']] ?? $gaya_tipe['retur'];
                        ?>
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5 px-2 font-mono text-slate-900"><?= html_escape($m['nomor_transaksi']) ?></td>
                                <td class="py-2.5 px-2 text-slate-600"><?= html_escape($m['tanggal']) ?></td>
                                <td class="py-2.5 px-2 text-slate-700"><?= html_escape($m['nama_barang']) ?></td>
                                <td class="py-2.5 px-2 text-center"><span class="px-2 py-0.5 rounded text-[10px] font-bold border <?= $g[0] ?>"><?= $g[1] ?></span></td>
                                <td class="py-2.5 px-2 text-center font-black text-slate-900"><?= $angka($m['jumlah']) ?></td>
                                <td class="py-2.5 px-2 text-center text-slate-600"><?= $angka($m['saldo_sesudah']) ?></td>
                                <td class="py-2.5 px-2 text-slate-600"><?= html_escape($m['user_nama'] ?: '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
