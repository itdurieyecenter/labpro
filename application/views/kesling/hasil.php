<?php defined('BASEPATH') OR exit('No direct script access allowed');
$order = $assembled['order'];
$grouped = $assembled['grouped'];
$summary = $assembled['summary'];
$subLabels = ['fisika' => 'A. Fisika', 'kimia' => 'B. Kimia', 'mikrobiologi' => 'C. Mikrobiologi', 'lainnya' => 'D. Lainnya'];
$hasStandard = !empty($order['standard_id']);
$badge = function($status) {
    switch ($status) {
        case 'exceed': return '<span class="px-2 py-0.5 rounded text-[10px] font-bold border bg-rose-50 text-rose-700 border-rose-200">Lewat Ambang</span>';
        case 'ok':     return '<span class="px-2 py-0.5 rounded text-[10px] font-bold border bg-emerald-50 text-emerald-700 border-emerald-200">Memenuhi</span>';
        default:       return '<span class="px-2 py-0.5 rounded text-[10px] font-bold border bg-slate-100 text-slate-400 border-slate-200">&mdash;</span>';
    }
};
// peta ambang utk evaluasi live di JS
?>

<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700">Dashboard</a><span>/</span>
                <a href="<?= site_url('kesling') ?>" class="hover:text-emerald-700">Kesehatan Lingkungan</a><span>/</span>
                <span class="text-emerald-800 font-semibold">Entri Hasil</span>
            </div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs"><i data-lucide="flask-conical" class="w-6 h-6"></i></span>
                <span>Entri Hasil Uji</span>
            </h1>
            <p class="text-xs text-slate-600 mt-1 font-mono"><?= html_escape($order['order_number']) ?> &bull; <?= html_escape($order['customer_name']) ?></p>
            <p class="text-xs text-slate-500">
                <?= html_escape($order['sample_type_name']) ?>
                <?= $hasStandard ? '&bull; Acuan: <strong class="text-emerald-700">' . html_escape($order['standard_name']) . '</strong>' : '' ?>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= site_url('kesling/cetak_lhu/' . $order['id']) ?>" target="_blank" class="px-3 py-2 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-700 text-xs font-bold flex items-center gap-1.5 border border-sky-200">
                <i data-lucide="printer" class="w-4 h-4"></i> Cetak LHU
            </a>
            <a href="<?= site_url('kesling') ?>" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center gap-1.5">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
            </a>
        </div>
    </div>

    <?php if (!$hasStandard): ?>
        <div class="bg-amber-50 border border-amber-300 rounded-2xl p-5 text-sm text-amber-900 flex items-start gap-3">
            <i data-lucide="alert-triangle" class="w-5 h-5 flex-shrink-0 mt-0.5"></i>
            <div>
                <strong>Baku mutu acuan belum dipilih.</strong><br>
                Pilih standar baku mutu pada order ini terlebih dahulu (lewat tombol Ubah di daftar order) agar parameter uji dan nilai rujukannya muncul.
            </div>
        </div>
    <?php else: ?>

    <form id="f-hasil" onsubmit="saveHasil(event)">
        <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
            <?php
            $cards = [
                ['Total Parameter', $summary['total'], 'slate'],
                ['Memenuhi', $summary['ok'], 'emerald'],
                ['Lewat Ambang', $summary['exceed'], 'rose'],
                ['Belum Diisi', $summary['na'], 'amber'],
            ];
            foreach ($cards as $c): ?>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider"><?= $c[0] ?></span>
                <div class="text-2xl font-black text-<?= $c[2] ?>-700 mt-1" id="sum-<?= $c[2] ?>"><?= (int)$c[1] ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50 flex items-center justify-between gap-3">
                <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2"><i data-lucide="list-checks" class="w-4 h-4 text-emerald-700"></i> Parameter Uji</h2>
                <div class="flex items-center gap-2">
                    <select name="status" class="lis-form-control py-1.5 text-xs w-auto">
                        <?php $st = $order['status']; foreach (['testing'=>'Pengujian','validasi'=>'Validasi','completed'=>'Selesai'] as $k=>$v): ?>
                            <option value="<?= $k ?>" <?= $st === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($can_edit): ?>
                    <button type="submit" class="px-4 py-1.5 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-1.5">
                        <i data-lucide="save" class="w-4 h-4"></i> Simpan Hasil
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="p-4 sm:p-5 overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead><tr class="border-b-2 border-emerald-700 text-slate-600 uppercase text-[10px] tracking-wider font-bold bg-slate-50">
                        <th class="py-3 px-3 w-10 text-center">No</th>
                        <th class="py-3 px-3">Parameter</th>
                        <th class="py-3 px-3 w-20">Satuan</th>
                        <th class="py-3 px-3 w-28">Hasil Uji</th>
                        <th class="py-3 px-3 w-28">Nilai Rujukan</th>
                        <th class="py-3 px-3 w-28 text-center">Status</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($subLabels as $key => $label): ?>
                            <?php if (empty($grouped[$key])) continue; ?>
                            <tr class="bg-slate-100"><td colspan="6" class="py-2 px-3 font-black text-emerald-800 uppercase text-[11px]"><?= $label ?></td></tr>
                            <?php $no = 1; foreach ($grouped[$key] as $row): ?>
                            <tr class="hover:bg-slate-50" id="prow-<?= $row['parameter_id'] ?>">
                                <td class="py-2 px-3 text-center text-slate-400"><?= $no++ ?></td>
                                <td class="py-2 px-3">
                                    <span class="font-bold text-slate-900"><?= html_escape($row['param_name']) ?></span>
                                    <?php if (!empty($row['is_accredited'])): ?><span class="text-emerald-700 font-black">*</span><?php endif; ?>
                                    <span class="text-[10px] text-slate-400 block"><?= html_escape($row['method'] ?: '-') ?></span>
                                </td>
                                <td class="py-2 px-3 text-slate-600"><?= html_escape($row['unit'] ?: '-') ?></td>
                                <td class="py-2 px-3">
                                    <input type="text" name="results[<?= $row['parameter_id'] ?>][value]"
                                        value="<?= html_escape($row['result_value'] ?? '') ?>"
                                        class="lis-form-control py-1.5 text-xs font-bold hasil-input"
                                        data-pid="<?= $row['parameter_id'] ?>"
                                        data-op="<?= html_escape($row['op'] ?? 'none') ?>"
                                        data-min="<?= html_escape($row['min'] ?? '') ?>"
                                        data-max="<?= html_escape($row['max'] ?? '') ?>"
                                        data-text="<?= html_escape($row['text_expected'] ?? '') ?>"
                                        oninput="evalRow(this)">
                                </td>
                                <td class="py-2 px-3 font-semibold text-slate-700"><?= html_escape($row['threshold']) ?></td>
                                <td class="py-2 px-3 text-center" id="stat-<?= $row['parameter_id'] ?>"><?= $badge($row['status']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </form>
    <?php endif; ?>
</div>

<?php if ($hasStandard): ?>
<script>
    const KU_SAVE = '<?= site_url('kesling/save_hasil') ?>';

    function toNum(v) {
        if (v === null || v === undefined) return null;
        let s = String(v).trim();
        if (s === '') return null;
        s = s.replace(/^[<>≤≥\s]+/, '').replace(/\./g,'').replace(/\s/g,'').replace(',', '.');
        return isNaN(parseFloat(s)) ? null : parseFloat(s);
    }
    function statusHtml(st) {
        if (st === 'exceed') return '<span class="px-2 py-0.5 rounded text-[10px] font-bold border bg-rose-50 text-rose-700 border-rose-200">Lewat Ambang</span>';
        if (st === 'ok')     return '<span class="px-2 py-0.5 rounded text-[10px] font-bold border bg-emerald-50 text-emerald-700 border-emerald-200">Memenuhi</span>';
        return '<span class="px-2 py-0.5 rounded text-[10px] font-bold border bg-slate-100 text-slate-400 border-slate-200">&mdash;</span>';
    }
    function evalStatus(el) {
        const op = el.dataset.op, val = el.value.trim();
        if (op === 'none') return 'na';
        if (val === '') return 'na';
        if (op === 'text') {
            const exp = (el.dataset.text || '').trim().toLowerCase();
            return (exp !== '' && val.toLowerCase() === exp) ? 'ok' : 'exceed';
        }
        const num = toNum(val);
        if (num === null) return 'na';
        const min = el.dataset.min !== '' ? parseFloat(el.dataset.min) : null;
        const max = el.dataset.max !== '' ? parseFloat(el.dataset.max) : null;
        if (op === 'max')   return (max !== null && num > max) ? 'exceed' : 'ok';
        if (op === 'min')   return (min !== null && num < min) ? 'exceed' : 'ok';
        if (op === 'range') return ((min !== null && num < min) || (max !== null && num > max)) ? 'exceed' : 'ok';
        return 'na';
    }
    function evalRow(el) {
        const st = evalStatus(el);
        document.getElementById('stat-' + el.dataset.pid).innerHTML = statusHtml(st);
        recountSummary();
    }
    function recountSummary() {
        let ok=0, ex=0, na=0, total=0;
        document.querySelectorAll('.hasil-input').forEach(el => {
            total++;
            const st = evalStatus(el);
            if (st === 'ok') ok++; else if (st === 'exceed') ex++; else na++;
        });
        document.getElementById('sum-slate').textContent = total;
        document.getElementById('sum-emerald').textContent = ok;
        document.getElementById('sum-rose').textContent = ex;
        document.getElementById('sum-amber').textContent = na;
    }

    function saveHasil(e) {
        e.preventDefault();
        const body = new FormData(document.getElementById('f-hasil'));
        LIS.fetchJson(KU_SAVE, { method:'POST', body: body })
            .then(res => LIS.toast('success', res.message))
            .catch(err => LIS.showError('Gagal Menyimpan', err, 'Hasil uji tidak dapat disimpan.'));
    }
</script>
<?php endif; ?>
