<?php defined('BASEPATH') OR exit('No direct script access allowed');
$standards = $matrix['standards'];
$params = $matrix['parameters'];
// Kelompokkan parameter per sub-kategori
$groups = [];
foreach ($params as $p) { $groups[$p['sub_category']][] = $p; }
$subLabels = ['fisika' => 'A. Fisika', 'kimia' => 'B. Kimia', 'mikrobiologi' => 'C. Mikrobiologi', 'lainnya' => 'D. Lainnya'];
?>

<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700">Dashboard</a><span>/</span>
                <a href="<?= site_url('kesling') ?>" class="hover:text-emerald-700">Kesehatan Lingkungan</a><span>/</span>
                <span class="text-emerald-800 font-semibold">Baku Mutu</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="ruler" class="w-6 h-6"></i>
                </span>
                <span>Baku Mutu &amp; Standar</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Nilai rujukan multi-standar per parameter &mdash; bersumber dari dokumen regulasi resmi klien</p>
        </div>
        <form method="get" class="flex items-center gap-2">
            <label class="text-xs font-bold text-slate-600">Tipe Sampel:</label>
            <select name="tipe" onchange="this.form.submit()" class="lis-form-control py-1.5 text-xs w-auto">
                <?php foreach ($sample_types as $t): ?>
                    <option value="<?= (int)$t['id'] ?>" <?= $selected_type == $t['id'] ? 'selected' : '' ?>>
                        <?= html_escape($t['name']) ?><?= $t['is_active'] ? '' : ' (nonaktif)' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50 flex items-center justify-between gap-3">
            <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="table" class="w-4 h-4 text-emerald-700"></i> Matriks Nilai Rujukan
            </h2>
            <span class="text-[10px] text-slate-500"><?= count($params) ?> parameter &bull; <?= count($standards) ?> standar</span>
        </div>

        <?php if (empty($standards)): ?>
            <div class="p-8 text-center text-slate-400 text-sm">Belum ada standar baku mutu untuk tipe sampel ini.</div>
        <?php else: ?>
        <div class="p-4 sm:p-5 overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b-2 border-emerald-700 text-slate-600 uppercase text-[10px] tracking-wider font-bold bg-slate-50">
                        <th class="py-3 px-3 w-10 text-center">No</th>
                        <th class="py-3 px-3">Parameter</th>
                        <th class="py-3 px-3">Satuan</th>
                        <th class="py-3 px-3">Metode</th>
                        <?php foreach ($standards as $s): ?>
                            <th class="py-3 px-3 text-center bg-emerald-50 text-emerald-800"><?= html_escape($s['code']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($subLabels as $key => $label): ?>
                        <?php if (empty($groups[$key])) continue; ?>
                        <tr class="bg-slate-100">
                            <td colspan="<?= 4 + count($standards) ?>" class="py-2 px-3 font-black text-emerald-800 uppercase text-[11px]"><?= $label ?></td>
                        </tr>
                        <?php $no = 1; foreach ($groups[$key] as $p): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5 px-3 text-center text-slate-400"><?= $no++ ?></td>
                            <td class="py-2.5 px-3">
                                <span class="font-bold text-slate-900"><?= html_escape($p['param_name']) ?></span>
                                <?php if (!empty($p['is_accredited'])): ?><span class="text-emerald-700 font-black" title="Dalam lingkup akreditasi">*</span><?php endif; ?>
                                <?php if (!empty($p['param_name_en'])): ?><span class="text-[10px] text-slate-400 block italic"><?= html_escape($p['param_name_en']) ?></span><?php endif; ?>
                            </td>
                            <td class="py-2.5 px-3 text-slate-600"><?= html_escape($p['unit'] ?: '-') ?></td>
                            <td class="py-2.5 px-3 text-slate-500 text-[11px]"><?= html_escape($p['method_default'] ?: '-') ?></td>
                            <?php foreach ($standards as $s): $val = $p['thresholds'][$s['id']] ?? null; ?>
                                <td class="py-2.5 px-3 text-center <?= $val !== null ? 'font-bold text-slate-800' : 'text-slate-300' ?>">
                                    <?= $val !== null ? html_escape($val) : '&mdash;' ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-slate-200 bg-slate-50 text-[10px] text-slate-500 space-y-0.5">
            <?php foreach ($standards as $s): ?>
                <div><strong class="text-slate-700"><?= html_escape($s['code']) ?></strong> &mdash; <?= html_escape($s['name']) ?><?= $s['regulation_ref'] ? ' (' . html_escape($s['regulation_ref']) . ')' : '' ?></div>
            <?php endforeach; ?>
            <div class="pt-1"><span class="text-emerald-700 font-black">*</span> = parameter dalam lingkup akreditasi (ditetapkan dari sertifikat akreditasi laboratorium).</div>
        </div>
        <?php endif; ?>
    </div>
</div>
