<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
$rp = function ($n) { return 'Rp ' . number_format((float)$n, 0, ',', '.'); };
$gaya_kal = [
    'tidak_laik'   => ['bg-rose-50 text-rose-700 border-rose-200', 'Tidak Laik'],
    'kedaluwarsa'  => ['bg-rose-50 text-rose-700 border-rose-200', 'Kedaluwarsa'],
    'belum_pernah' => ['bg-rose-50 text-rose-700 border-rose-200', 'Belum Dikalibrasi'],
    'kritis'       => ['bg-amber-50 text-amber-800 border-amber-300', 'Kritis'],
    'peringatan'   => ['bg-yellow-50 text-yellow-800 border-yellow-200', 'Perlu Dijadwalkan'],
    'laik'         => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'Terkalibrasi'],
    'tidak_wajib'  => ['bg-slate-100 text-slate-500 border-slate-200', 'Tidak Wajib'],
];
?>

<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700">Dashboard</a>
                <span>/</span><span class="text-emerald-800 font-semibold">Aset &amp; Inventaris</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="microscope" class="w-6 h-6"></i>
                </span>
                <span>Monitoring Aset &amp; Instrumen</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                KIB-B Peralatan &amp; Mesin &mdash; nilai perolehan <?= $rp($ringkasan['nilai_perolehan']) ?>,
                nilai buku <?= $rp($ringkasan['nilai_buku']) ?>
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="<?= site_url('aset/barang') ?>" class="px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-2 border border-emerald-600">
                <i data-lucide="clipboard-list" class="w-4 h-4"></i><span>Daftar Aset</span>
            </a>
            <a href="<?= site_url('aset/kalibrasi') ?>" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center gap-2 border border-slate-300">
                <i data-lucide="ruler" class="w-4 h-4"></i><span>Kalibrasi</span>
            </a>
        </div>
    </div>

    <?php if ($ringkasan['tidak_laik'] > 0): ?>
        <div class="bg-rose-50 border border-rose-300 rounded-2xl p-5 flex items-start gap-3">
            <i data-lucide="alert-octagon" class="w-5 h-5 text-rose-700 shrink-0 mt-0.5"></i>
            <div class="text-xs text-rose-900">
                <strong class="block mb-1"><?= (int)$ringkasan['tidak_laik'] ?> instrumen dinyatakan tidak laik pakai.</strong>
                Berdasarkan ISO/IEC 17025, instrumen ukur yang kalibrasinya kedaluwarsa, berhasil tidak laik, atau
                belum pernah dikalibrasi <strong>tidak boleh dipakai untuk pengujian</strong>. Hasil uji yang
                dikeluarkan memakai alat tersebut berisiko ditolak pada surveilans akreditasi.
            </div>
        </div>
    <?php endif; ?>

    <!-- Ringkasan -->
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-3">
        <?php
        $kartu = [
            ['Total Aset', $ringkasan['total'], 'boxes', 'slate'],
            ['Kondisi Baik', $ringkasan['baik'], 'check-circle', 'emerald'],
            ['Rusak Berat', $ringkasan['rusak_berat'], 'x-octagon', 'rose'],
            ['Tidak Laik Pakai', $ringkasan['tidak_laik'], 'alert-octagon', 'rose'],
            ['Kalibrasi Perlu Perhatian', $ringkasan['kalibrasi_perhatian'], 'ruler', 'amber'],
            ['Pemeliharaan Terlewat', $ringkasan['pemeliharaan_terlewat'], 'wrench', 'amber'],
        ];
        foreach ($kartu as $k): ?>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider"><?= $k[0] ?></span>
                    <i data-lucide="<?= $k[2] ?>" class="w-4 h-4 text-<?= $k[3] ?>-600"></i>
                </div>
                <span class="text-2xl font-black text-slate-900"><?= (int)$k[1] ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Nilai BMD -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Nilai Perolehan</span>
            <span class="text-lg font-black text-slate-900"><?= $rp($ringkasan['nilai_perolehan']) ?></span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Akumulasi Penyusutan</span>
            <span class="text-lg font-black text-amber-700"><?= $rp($ringkasan['akumulasi_penyusutan']) ?></span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-emerald-200 shadow-xs bg-emerald-50/40">
            <span class="text-[10px] font-bold text-emerald-700 uppercase tracking-wider block mb-1">Nilai Buku</span>
            <span class="text-lg font-black text-emerald-800"><?= $rp($ringkasan['nilai_buku']) ?></span>
            <span class="text-[10px] text-slate-500 block mt-0.5"><?= (int)$ringkasan['habis_manfaat'] ?> aset telah habis masa manfaat</span>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <!-- Kalibrasi -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="ruler" class="w-4 h-4 text-amber-600"></i> Kalibrasi Perlu Tindakan
                </h2>
                <span class="text-[11px] text-slate-500"><?= count($kalibrasi_perhatian) ?> instrumen</span>
            </div>
            <div class="p-4 overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead><tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] font-bold bg-slate-50">
                        <th class="py-2.5 px-2">Instrumen</th><th class="py-2.5 px-2">Status</th><th class="py-2.5 px-2">Berlaku Sampai</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <?php if (empty($kalibrasi_perhatian)): ?>
                            <tr><td colspan="3" class="text-center py-7 text-slate-400">Seluruh instrumen terkalibrasi dan masih berlaku.</td></tr>
                        <?php else: foreach ($kalibrasi_perhatian as $a): $g = $gaya_kal[$a['kalibrasi']['status']] ?? $gaya_kal['laik']; ?>
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5 px-2">
                                    <strong class="text-slate-900 block"><?= html_escape($a['nama_aset']) ?></strong>
                                    <span class="text-[10px] text-slate-400 font-mono"><?= html_escape($a['nomor_register']) ?></span>
                                    <span class="text-[10px] text-slate-500 block"><?= html_escape($a['lokasi_ruangan'] ?: '-') ?></span>
                                </td>
                                <td class="py-2.5 px-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border <?= $g[0] ?>"><?= $g[1] ?></span>
                                    <?php if (!$a['laik_pakai']): ?>
                                        <span class="block mt-1 text-[10px] font-bold text-rose-700">⊘ Dilarang dipakai</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2.5 px-2 text-slate-600">
                                    <?= html_escape($a['kalibrasi']['berlaku_sampai'] ?: '-') ?>
                                    <?php if ($a['kalibrasi']['sisa_hari'] !== null): ?>
                                        <span class="block text-[10px] text-slate-400">
                                            <?= $a['kalibrasi']['sisa_hari'] < 0 ? 'lewat ' . abs($a['kalibrasi']['sisa_hari']) . ' hari' : 'sisa ' . $a['kalibrasi']['sisa_hari'] . ' hari' ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pemeliharaan -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="wrench" class="w-4 h-4 text-emerald-700"></i> Pemeliharaan Jatuh Tempo
                </h2>
                <span class="text-[11px] text-slate-500"><?= count($pemeliharaan_jatuh_tempo) ?> aset</span>
            </div>
            <div class="p-4 overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead><tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] font-bold bg-slate-50">
                        <th class="py-2.5 px-2">Aset</th><th class="py-2.5 px-2">Jatuh Tempo</th><th class="py-2.5 px-2">Terakhir</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <?php if (empty($pemeliharaan_jatuh_tempo)): ?>
                            <tr><td colspan="3" class="text-center py-7 text-slate-400">Tidak ada pemeliharaan yang jatuh tempo.</td></tr>
                        <?php else: foreach ($pemeliharaan_jatuh_tempo as $a): $terlewat = $a['pemeliharaan']['status'] === 'terlewat'; ?>
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5 px-2">
                                    <strong class="text-slate-900 block"><?= html_escape($a['nama_aset']) ?></strong>
                                    <span class="text-[10px] text-slate-400 font-mono"><?= html_escape($a['nomor_register']) ?></span>
                                </td>
                                <td class="py-2.5 px-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border <?= $terlewat ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-amber-50 text-amber-800 border-amber-300' ?>">
                                        <?= $a['pemeliharaan']['label'] ?>
                                    </span>
                                    <span class="block text-[10px] text-slate-500 mt-0.5"><?= html_escape($a['pemeliharaan']['jatuh_tempo'] ?: '-') ?></span>
                                </td>
                                <td class="py-2.5 px-2 text-slate-600"><?= html_escape($a['pemeliharaan']['terakhir'] ?: 'Belum pernah') ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
