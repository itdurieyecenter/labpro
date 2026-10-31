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
            <p class="text-xs text-slate-500 mt-1">Nilai rujukan multi-standar per parameter &mdash; klik sel nilai rujukan untuk mengatur ambang batas evaluasi otomatis</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <form method="get" class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-600">Tipe Sampel:</label>
                <select name="tipe" onchange="this.form.submit()" class="lis-form-control py-1.5 text-xs w-auto font-bold">
                    <?php foreach ($sample_types as $t): ?>
                        <option value="<?= (int)$t['id'] ?>" <?= $selected_type == $t['id'] ? 'selected' : '' ?>>
                            <?= html_escape($t['name']) ?><?= $t['is_active'] ? '' : ' (nonaktif)' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <?php if (!empty($can_create)): ?>
                <button type="button" onclick="openStdModal()" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center gap-1.5 border border-slate-300">
                    <i data-lucide="book-open" class="w-3.5 h-3.5"></i> + Standar
                </button>
                <button type="button" onclick="openParamModal()" class="px-3.5 py-1.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-1.5 border border-emerald-600">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> + Parameter
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Daftar Standar Regulasi pada Tipe Sampel Terpilih -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-<?= min(4, max(2, count($standards))) ?> gap-3">
        <?php foreach ($standards as $s): ?>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 flex items-start justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] font-black font-mono"><?= html_escape($s['code']) ?></span>
                    <?php if (empty($s['is_active'])): ?>
                        <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 text-[9px] font-bold">Nonaktif</span>
                    <?php endif; ?>
                </div>
                <div class="text-xs font-bold text-slate-900 mt-1.5"><?= html_escape($s['name']) ?></div>
                <?php if (!empty($s['regulation_ref'])): ?>
                    <div class="text-[11px] text-slate-500 mt-0.5">Acuan LHU: <?= html_escape($s['regulation_ref']) ?></div>
                <?php endif; ?>
            </div>
            <?php if (!empty($can_edit) || !empty($can_delete)): ?>
            <div class="flex items-center gap-1 flex-shrink-0">
                <?php if (!empty($can_edit)): ?>
                <button type="button" onclick="editStd(<?= (int)$s['id'] ?>)" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700" title="Ubah Standar">
                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                </button>
                <?php endif; ?>
                <?php if (!empty($can_delete)): ?>
                <button type="button" onclick="hapusStd(<?= (int)$s['id'] ?>, '<?= html_escape(addslashes($s['name'])) ?>')" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700" title="Hapus Standar">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50 flex items-center justify-between gap-3">
            <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="table" class="w-4 h-4 text-emerald-700"></i> Matriks Nilai Rujukan
            </h2>
            <span class="text-[10px] text-slate-500"><?= count($params) ?> parameter &bull; <?= count($standards) ?> standar</span>
        </div>

        <?php if (empty($standards) && empty($params)): ?>
            <div class="p-8 text-center text-slate-400 text-sm">Belum ada standar dan parameter baku mutu untuk tipe sampel ini.</div>
        <?php else: ?>
        <div class="p-4 sm:p-5 overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b-2 border-emerald-700 text-slate-600 uppercase text-[10px] tracking-wider font-bold bg-slate-50">
                        <th class="py-3 px-3 w-10 text-center">No</th>
                        <th class="py-3 px-3">Parameter</th>
                        <th class="py-3 px-3">Satuan</th>
                        <th class="py-3 px-3">Metode Default</th>
                        <?php foreach ($standards as $s): ?>
                            <th class="py-3 px-3 text-center bg-emerald-50 text-emerald-800"><?= html_escape($s['code']) ?></th>
                        <?php endforeach; ?>
                        <?php if (!empty($can_edit) || !empty($can_delete)): ?>
                            <th class="py-3 px-3 w-20 text-center">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($subLabels as $key => $label): ?>
                        <?php if (empty($groups[$key])) continue; ?>
                        <tr class="bg-slate-100">
                            <td colspan="<?= 4 + count($standards) + ((!empty($can_edit) || !empty($can_delete)) ? 1 : 0) ?>" class="py-2 px-3 font-black text-emerald-800 uppercase text-[11px]"><?= $label ?></td>
                        </tr>
                        <?php $no = 1; foreach ($groups[$key] as $p): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="py-2.5 px-3 text-center text-slate-400"><?= $no++ ?></td>
                            <td class="py-2.5 px-3">
                                <span class="font-bold text-slate-900"><?= html_escape($p['param_name']) ?></span>
                                <?php if (!empty($p['is_accredited'])): ?><span class="text-emerald-700 font-black" title="Dalam lingkup akreditasi KAN">*</span><?php endif; ?>
                                <?php if (empty($p['is_active'])): ?><span class="ml-1 px-1.5 py-0.5 rounded bg-slate-200 text-slate-600 text-[9px]">Nonaktif</span><?php endif; ?>
                                <?php if (!empty($p['param_name_en'])): ?><span class="text-[10px] text-slate-400 block italic"><?= html_escape($p['param_name_en']) ?></span><?php endif; ?>
                            </td>
                            <td class="py-2.5 px-3 text-slate-600"><?= html_escape($p['unit'] ?: '-') ?></td>
                            <td class="py-2.5 px-3 text-slate-500 text-[11px]"><?= html_escape($p['method_default'] ?: '-') ?></td>
                            <?php foreach ($standards as $s):
                                $sid = (int)$s['id'];
                                $val = $p['thresholds'][$sid] ?? null;
                                $tRow = $p['threshold_rows'][$sid] ?? null;
                                $tJson = htmlspecialchars(json_encode([
                                    'standard_id' => $sid,
                                    'standard_name' => $s['name'],
                                    'parameter_id' => (int)$p['id'],
                                    'param_name' => $p['param_name'],
                                    'threshold_display' => $tRow['threshold_display'] ?? '',
                                    'operator' => $tRow['operator'] ?? ($p['data_type'] === 'text' ? 'text' : 'max'),
                                    'min_val' => $tRow['min_val'] ?? '',
                                    'max_val' => $tRow['max_val'] ?? '',
                                    'text_expected' => $tRow['text_expected'] ?? '',
                                    'unit_override' => $tRow['unit_override'] ?? '',
                                    'method_override' => $tRow['method_override'] ?? '',
                                ]), ENT_QUOTES, 'UTF-8');
                            ?>
                                <td class="py-2.5 px-3 text-center <?= $val !== null ? 'font-bold text-slate-800' : 'text-slate-300' ?>">
                                    <?php if (!empty($can_edit)): ?>
                                        <button type="button" onclick='openThrModal(<?= $tJson ?>)' class="px-2 py-1 rounded hover:bg-emerald-50 hover:text-emerald-800 border border-transparent hover:border-emerald-200 transition cursor-pointer w-full" title="Klik untuk atur nilai rujukan">
                                            <?= $val !== null ? html_escape($val) : '<span class="text-[10px] text-slate-400 font-normal">+ Atur</span>' ?>
                                        </button>
                                    <?php else: ?>
                                        <?= $val !== null ? html_escape($val) : '&mdash;' ?>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                            <?php if (!empty($can_edit) || !empty($can_delete)): ?>
                            <td class="py-2.5 px-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <?php if (!empty($can_edit)): ?>
                                    <button type="button" onclick="editParam(<?= (int)$p['id'] ?>)" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700" title="Ubah Parameter">
                                        <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <?php endif; ?>
                                    <?php if (!empty($can_delete)): ?>
                                    <button type="button" onclick="hapusParam(<?= (int)$p['id'] ?>, '<?= html_escape(addslashes($p['param_name'])) ?>')" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700" title="Hapus Parameter">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <?php endif; ?>
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
            <div class="pt-1"><span class="text-emerald-700 font-black">*</span> = parameter dalam lingkup akreditasi KAN (ditetapkan dari sertifikat akreditasi laboratorium).</div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Standar Baku Mutu -->
<div id="m-std" class="lis-modal">
    <div class="lis-modal-box max-w-lg w-full">
        <div class="lis-modal-header bg-gradient-to-r from-emerald-900 to-teal-900 border-b border-amber-400/40">
            <h3 class="lis-modal-title text-white flex items-center gap-2">
                <i data-lucide="book-open" class="w-5 h-5 text-amber-300"></i><span id="m-std-title">Standar Baku Mutu</span>
            </h3>
            <button type="button" onclick="hideModal('m-std')" class="lis-modal-close"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="f-std" onsubmit="saveStd(event)" class="lis-modal-body p-5 space-y-3">
            <input type="hidden" name="id" id="std-id">
            <input type="hidden" name="sample_type_id" id="std-tipe" value="<?= (int)$selected_type ?>">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="lis-form-label">Kode Standar <span class="req">*</span></label>
                    <input type="text" name="code" id="std-code" required class="lis-form-control font-mono uppercase" placeholder="PERMENKES_2_2023">
                </div>
                <div>
                    <label class="lis-form-label">Urutan &amp; Status</label>
                    <div class="flex items-center gap-2">
                        <input type="number" name="sort_order" id="std-sort" value="1" class="lis-form-control w-20">
                        <select name="is_active" id="std-active" class="lis-form-control">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>
            </div>
            <div>
                <label class="lis-form-label">Nama Standar / Regulasi <span class="req">*</span></label>
                <input type="text" name="name" id="std-name" required class="lis-form-control font-bold" placeholder="Permenkes RI No. 2 Tahun 2023 (Air Minum)">
            </div>
            <div>
                <label class="lis-form-label">Teks Rujukan di Kaki LHU</label>
                <input type="text" name="regulation_ref" id="std-ref" class="lis-form-control" placeholder="Permen Kes RI No. 2 Tahun 2023">
            </div>
            <div>
                <label class="lis-form-label">Keterangan</label>
                <input type="text" name="description" id="std-desc" class="lis-form-control">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="hideModal('m-std')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold">Simpan Standar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Parameter Uji -->
<div id="m-param" class="lis-modal">
    <div class="lis-modal-box max-w-xl w-full">
        <div class="lis-modal-header bg-gradient-to-r from-emerald-900 to-teal-900 border-b border-amber-400/40">
            <h3 class="lis-modal-title text-white flex items-center gap-2">
                <i data-lucide="flask-conical" class="w-5 h-5 text-amber-300"></i><span id="m-param-title">Parameter Uji Lingkungan</span>
            </h3>
            <button type="button" onclick="hideModal('m-param')" class="lis-modal-close"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="f-param" onsubmit="saveParam(event)" class="lis-modal-body p-5 space-y-3">
            <input type="hidden" name="id" id="p-id">
            <input type="hidden" name="sample_type_id" id="p-tipe" value="<?= (int)$selected_type ?>">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="lis-form-label">Kode Parameter <span class="req">*</span></label>
                    <input type="text" name="param_code" id="p-code" required class="lis-form-control font-mono uppercase" placeholder="AM-PH">
                </div>
                <div>
                    <label class="lis-form-label">Sub-Kategori <span class="req">*</span></label>
                    <select name="sub_category" id="p-sub" class="lis-form-control">
                        <option value="fisika">A. Fisika</option>
                        <option value="kimia">B. Kimia</option>
                        <option value="mikrobiologi">C. Mikrobiologi</option>
                        <option value="lainnya">D. Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="lis-form-label">Tipe Data</label>
                    <select name="data_type" id="p-dtype" class="lis-form-control">
                        <option value="numeric">Numerik (Angka)</option>
                        <option value="text">Teks (Kualitatif)</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="lis-form-label">Nama Parameter (ID) <span class="req">*</span></label>
                    <input type="text" name="param_name" id="p-name" required class="lis-form-control font-bold" placeholder="Kekeruhan">
                </div>
                <div>
                    <label class="lis-form-label">Nama Parameter (EN - Bilingual LHU)</label>
                    <input type="text" name="param_name_en" id="p-name-en" class="lis-form-control italic" placeholder="Turbidity">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="lis-form-label">Satuan Default</label>
                    <input type="text" name="unit" id="p-unit" class="lis-form-control" placeholder="mg/L">
                </div>
                <div class="sm:col-span-2">
                    <label class="lis-form-label">Metode Uji Default (SNI / APHA / USEPA)</label>
                    <input type="text" name="method_default" id="p-method" class="lis-form-control" placeholder="SNI 06-6989.11-2019">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-center pt-1">
                <div>
                    <label class="lis-form-label">No. Urut</label>
                    <input type="number" name="sort_order" id="p-sort" value="10" class="lis-form-control">
                </div>
                <div>
                    <label class="lis-form-label">Status</label>
                    <select name="is_active" id="p-active" class="lis-form-control">
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
                <div class="pt-4">
                    <label class="inline-flex items-center gap-2 text-xs font-bold text-emerald-800 cursor-pointer">
                        <input type="checkbox" name="is_accredited" id="p-acc" value="1" class="rounded border-slate-300 text-emerald-700">
                        <span>Terakreditasi KAN (*)</span>
                    </label>
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="hideModal('m-param')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold">Simpan Parameter</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Ambang Batas / Nilai Rujukan (Threshold) -->
<div id="m-thr" class="lis-modal">
    <div class="lis-modal-box max-w-md w-full">
        <div class="lis-modal-header bg-gradient-to-r from-emerald-900 to-teal-900 border-b border-amber-400/40">
            <h3 class="lis-modal-title text-white flex items-center gap-2">
                <i data-lucide="sliders" class="w-5 h-5 text-amber-300"></i><span>Atur Nilai Rujukan</span>
            </h3>
            <button type="button" onclick="hideModal('m-thr')" class="lis-modal-close"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="f-thr" onsubmit="saveThr(event)" class="lis-modal-body p-5 space-y-3">
            <input type="hidden" name="standard_id" id="t-std-id">
            <input type="hidden" name="parameter_id" id="t-param-id">
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs">
                <div class="text-slate-500">Standar: <strong class="text-slate-800" id="t-std-label"></strong></div>
                <div class="text-slate-500 mt-0.5">Parameter: <strong class="text-emerald-800" id="t-param-label"></strong></div>
            </div>
            <div>
                <label class="lis-form-label">Teks Nilai Rujukan di LHU (Verbatim)</label>
                <input type="text" name="threshold_display" id="t-disp" class="lis-form-control font-bold" placeholder="Contoh: < 300 atau 6,5 - 8,5 atau Tidak Berbau">
                <p class="lis-form-hint">Kosongkan lalu simpan jika parameter ini tidak berlaku pada standar terpilih.</p>
            </div>
            <div>
                <label class="lis-form-label">Rumus Evaluasi Otomatis</label>
                <select name="operator" id="t-op" onchange="toggleThrFields()" class="lis-form-control">
                    <option value="max">Maksimum (Hasil &le; Batas Maks)</option>
                    <option value="min">Minimum (Hasil &ge; Batas Min)</option>
                    <option value="range">Rentang (Batas Min &le; Hasil &le; Batas Maks)</option>
                    <option value="equal">Sama Dengan (= Nilai)</option>
                    <option value="text">Teks Kualitatif (Sama persis teks)</option>
                    <option value="none">Tanpa Flag Otomatis (Misal: Suhu Udara &plusmn; 3)</option>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3" id="t-num-box">
                <div>
                    <label class="lis-form-label">Batas Minimum</label>
                    <input type="text" name="min_val" id="t-min" class="lis-form-control font-mono" placeholder="6.5">
                </div>
                <div>
                    <label class="lis-form-label">Batas Maksimum</label>
                    <input type="text" name="max_val" id="t-max" class="lis-form-control font-mono" placeholder="8.5">
                </div>
            </div>
            <div id="t-txt-box" class="hidden">
                <label class="lis-form-label">Teks Normal yang Diharapkan</label>
                <input type="text" name="text_expected" id="t-text" class="lis-form-control" placeholder="Tidak Berbau">
            </div>
            <div class="grid grid-cols-2 gap-3 pt-1">
                <div>
                    <label class="lis-form-label">Override Satuan (Opsional)</label>
                    <input type="text" name="unit_override" id="t-unit" class="lis-form-control text-xs" placeholder="Sesuai parameter">
                </div>
                <div>
                    <label class="lis-form-label">Override Metode (Opsional)</label>
                    <input type="text" name="method_override" id="t-method" class="lis-form-control text-xs" placeholder="Sesuai parameter">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="hideModal('m-thr')" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold">Simpan Rujukan</button>
            </div>
        </form>
    </div>
</div>

<script>
    const BM = {
        getStd:    '<?= site_url('kesling/get_standard') ?>',
        saveStd:   '<?= site_url('kesling/save_standard') ?>',
        delStd:    '<?= site_url('kesling/delete_standard') ?>',
        getParam:  '<?= site_url('kesling/get_parameter') ?>',
        saveParam: '<?= site_url('kesling/save_parameter') ?>',
        delParam:  '<?= site_url('kesling/delete_parameter') ?>',
        saveThr:   '<?= site_url('kesling/save_threshold') ?>'
    };

    function showModal(id){ const m=document.getElementById(id); m.classList.remove('hidden'); m.classList.add('flex'); m.style.display='flex'; if(window.lucide) lucide.createIcons(); }
    function hideModal(id){ const m=document.getElementById(id); m.classList.remove('flex'); m.classList.add('hidden'); m.style.display='none'; }

    function openStdModal() {
        document.getElementById('f-std').reset();
        document.getElementById('std-id').value = '';
        document.getElementById('m-std-title').textContent = 'Tambah Standar Baku Mutu';
        showModal('m-std');
    }
    function editStd(id) {
        LIS.fetchJson(BM.getStd + '/' + id).then(res => {
            const d = res.data;
            document.getElementById('std-id').value = d.id;
            document.getElementById('std-tipe').value = d.sample_type_id;
            document.getElementById('std-code').value = d.code || '';
            document.getElementById('std-name').value = d.name || '';
            document.getElementById('std-ref').value = d.regulation_ref || '';
            document.getElementById('std-desc').value = d.description || '';
            document.getElementById('std-sort').value = d.sort_order || 1;
            document.getElementById('std-active').value = d.is_active ?? 1;
            document.getElementById('m-std-title').textContent = 'Ubah Standar: ' + d.code;
            showModal('m-std');
        }).catch(err => LIS.showError('Gagal Memuat Standar', err));
    }
    function saveStd(e) {
        e.preventDefault();
        LIS.fetchJson(BM.saveStd, { method:'POST', body: new FormData(document.getElementById('f-std')) })
            .then(res => { hideModal('m-std'); LIS.toast('success', res.message); setTimeout(()=>location.reload(), 400); })
            .catch(err => LIS.showError('Gagal Menyimpan Standar', err));
    }
    function hapusStd(id, name) {
        Swal.fire({ title:'Hapus Standar?', text:'Standar "' + name + '" beserta nilai rujukannya akan dihapus.', icon:'warning', showCancelButton:true, confirmButtonColor:'#e11d48', confirmButtonText:'Ya, Hapus', cancelButtonText:'Batal' })
            .then(r => {
                if (!r.isConfirmed) return;
                LIS.fetchJson(BM.delStd + '/' + id, { method:'POST' })
                    .then(res => { LIS.toast('success', res.message); setTimeout(()=>location.reload(), 400); })
                    .catch(err => LIS.showError('Gagal Menghapus', err));
            });
    }

    function openParamModal() {
        document.getElementById('f-param').reset();
        document.getElementById('p-id').value = '';
        document.getElementById('p-acc').checked = true;
        document.getElementById('m-param-title').textContent = 'Tambah Parameter Uji';
        showModal('m-param');
    }
    function editParam(id) {
        LIS.fetchJson(BM.getParam + '/' + id).then(res => {
            const d = res.data;
            document.getElementById('p-id').value = d.id;
            document.getElementById('p-tipe').value = d.sample_type_id;
            document.getElementById('p-code').value = d.param_code || '';
            document.getElementById('p-sub').value = d.sub_category || 'kimia';
            document.getElementById('p-dtype').value = d.data_type || 'numeric';
            document.getElementById('p-name').value = d.param_name || '';
            document.getElementById('p-name-en').value = d.param_name_en || '';
            document.getElementById('p-unit').value = d.unit || '';
            document.getElementById('p-method').value = d.method_default || '';
            document.getElementById('p-sort').value = d.sort_order || 10;
            document.getElementById('p-active').value = d.is_active ?? 1;
            document.getElementById('p-acc').checked = parseInt(d.is_accredited) === 1;
            document.getElementById('m-param-title').textContent = 'Ubah Parameter: ' + d.param_name;
            showModal('m-param');
        }).catch(err => LIS.showError('Gagal Memuat Parameter', err));
    }
    function saveParam(e) {
        e.preventDefault();
        LIS.fetchJson(BM.saveParam, { method:'POST', body: new FormData(document.getElementById('f-param')) })
            .then(res => { hideModal('m-param'); LIS.toast('success', res.message); setTimeout(()=>location.reload(), 400); })
            .catch(err => LIS.showError('Gagal Menyimpan Parameter', err));
    }
    function hapusParam(id, name) {
        Swal.fire({ title:'Hapus Parameter?', text:'Parameter "' + name + '" akan dihapus permanen.', icon:'warning', showCancelButton:true, confirmButtonColor:'#e11d48', confirmButtonText:'Ya, Hapus', cancelButtonText:'Batal' })
            .then(r => {
                if (!r.isConfirmed) return;
                LIS.fetchJson(BM.delParam + '/' + id, { method:'POST' })
                    .then(res => { LIS.toast('success', res.message); setTimeout(()=>location.reload(), 400); })
                    .catch(err => LIS.showError('Gagal Menghapus', err));
            });
    }

    function toggleThrFields() {
        const op = document.getElementById('t-op').value;
        document.getElementById('t-num-box').classList.toggle('hidden', op === 'text' || op === 'none');
        document.getElementById('t-txt-box').classList.toggle('hidden', op !== 'text');
    }
    function openThrModal(data) {
        document.getElementById('t-std-id').value = data.standard_id;
        document.getElementById('t-param-id').value = data.parameter_id;
        document.getElementById('t-std-label').textContent = data.standard_name;
        document.getElementById('t-param-label').textContent = data.param_name;
        document.getElementById('t-disp').value = data.threshold_display || '';
        document.getElementById('t-op').value = data.operator || 'max';
        document.getElementById('t-min').value = data.min_val !== null && data.min_val !== '' ? parseFloat(data.min_val) : '';
        document.getElementById('t-max').value = data.max_val !== null && data.max_val !== '' ? parseFloat(data.max_val) : '';
        document.getElementById('t-text').value = data.text_expected || '';
        document.getElementById('t-unit').value = data.unit_override || '';
        document.getElementById('t-method').value = data.method_override || '';
        toggleThrFields();
        showModal('m-thr');
    }
    function saveThr(e) {
        e.preventDefault();
        LIS.fetchJson(BM.saveThr, { method:'POST', body: new FormData(document.getElementById('f-thr')) })
            .then(res => { hideModal('m-thr'); LIS.toast('success', res.message); setTimeout(()=>location.reload(), 350); })
            .catch(err => LIS.showError('Gagal Menyimpan Nilai Rujukan', err));
    }
</script>
