<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-5">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-slate-400">Master Data</span>
                <span>/</span>
                <span class="text-emerald-800 font-semibold">Nilai Rujukan & Kritis</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="sliders" class="w-6 h-6"></i>
                </span>
                <span>Interval Nilai Rujukan & Batas Kritis (Panic Values)</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Standarisasi rentang normal & batas kritis otomatis untuk sistem evaluasi hasil lab (ISO 15189 / KAN)</p>
        </div>

        <button type="button" onclick="openRangeModal()" class="px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-2 shadow-sm transition-all border border-emerald-600">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>+ Tambah Nilai Rujukan</span>
        </button>
    </div>

    <!-- Alert Edukasi Klinis -->
    <div class="p-4 rounded-xl bg-amber-50/70 border border-amber-200 text-amber-900 flex items-start gap-3">
        <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5"></i>
        <div class="text-xs space-y-1">
            <strong class="font-bold text-amber-950">Aturan Evaluasi Klinis LIS:</strong>
            <p class="text-amber-800">
                Sistem secara otomatis mengevaluasi hasil kuantitatif analis terhadap rentang normal (Flag <strong>L</strong> atau <strong>H</strong>). 
                Bila hasil melampaui <strong>Batas Kritis (Panic Values L* / H*)</strong>, sistem memberi alarm merah berkedip yang wajib segera dilaporkan ke Dokter Penanggung Jawab Lab (Sp.PK).
            </p>
        </div>
    </div>

    <!-- Tabel Master Nilai Rujukan -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 overflow-x-auto">
            <table id="tbl-nilai-rujukan" class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-bold bg-slate-50">
                        <th class="py-3 px-3 w-12 text-center">No</th>
                        <th class="py-3 px-3">Parameter Uji</th>
                        <th class="py-3 px-3">Kategori</th>
                        <th class="py-3 px-3">Demografi (Gender & Usia)</th>
                        <th class="py-3 px-3">Rentang Normal (Display)</th>
                        <th class="py-3 px-3">Batas Kritis (Panic Values)</th>
                        <th class="py-3 px-3">Catatan</th>
                        <th class="py-3 px-3 text-center w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    <?php if (empty($ranges)): ?>
                        <tr><td colspan="8" class="text-center py-6 text-slate-400">Belum ada data nilai rujukan.</td></tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($ranges as $r): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-3 px-3 text-center text-slate-400"><?= $no++ ?></td>
                                <td class="py-3 px-3">
                                    <strong class="text-slate-900 block font-bold"><?= html_escape($r['parameter_name']) ?></strong>
                                    <span class="text-[10px] text-slate-500 font-mono">Satuan: <?= html_escape($r['unit'] ?: '-') ?></span>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        <?= html_escape($r['test_category'] ?: 'Umum') ?>
                                    </span>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <?php if ($r['gender'] == 'L'): ?>
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">♂ Pria</span>
                                        <?php elseif ($r['gender'] == 'P'): ?>
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-pink-50 text-pink-700 border border-pink-200">♀ Wanita</span>
                                        <?php else: ?>
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">Semua Gender</span>
                                        <?php endif; ?>

                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            <?= html_escape($r['age_label'] ?: ucfirst($r['age_classification'])) ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3 px-3 font-semibold text-slate-800">
                                    <div class="flex items-center gap-1.5">
                                        <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-600 shrink-0"></i>
                                        <span><?= html_escape($r['reference_display']) ?></span>
                                    </div>
                                    <?php if ($r['min_val'] !== null && $r['max_val'] !== null): ?>
                                        <span class="text-[10px] text-slate-400 font-mono block">Numerik: <?= (float)$r['min_val'] ?> s/d <?= (float)$r['max_val'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-3">
                                    <?php if ($r['critical_low'] !== null || $r['critical_high'] !== null): ?>
                                        <div class="flex items-center gap-1 font-mono text-[11px] font-bold">
                                            <?php if ($r['critical_low'] !== null): ?>
                                                <span class="px-1.5 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200" title="Batas Bawah Kritis">
                                                    &lt; <?= (float)$r['critical_low'] ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($r['critical_high'] !== null): ?>
                                                <span class="px-1.5 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200" title="Batas Atas Kritis">
                                                    &gt; <?= (float)$r['critical_high'] ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-slate-400 text-[10px] italic">Tidak ditentukan</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-3 text-slate-500 text-[11px]">
                                    <?= html_escape($r['notes'] ?: '-') ?>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" onclick='editRange(<?= json_encode($r) ?>)' class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10px] transition-colors" title="Edit Nilai Rujukan">
                                            <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                        </button>
                                        <button type="button" onclick="deleteRange(<?= $r['id'] ?>)" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 text-[10px] transition-colors" title="Hapus">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
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

<!-- Modal Tambah/Edit Nilai Rujukan -->
<div id="modal-range" class="lis-modal">
    <div class="lis-modal-box max-w-xl w-full">
        <div class="lis-modal-header bg-gradient-to-r from-emerald-900 to-teal-900 border-b border-amber-400/40">
            <h3 class="lis-modal-title text-white flex items-center gap-2">
                <i data-lucide="sliders" class="w-5 h-5 text-amber-300"></i>
                <span id="modal-range-title">Tambah Interval Nilai Rujukan</span>
            </h3>
            <button type="button" onclick="closeRangeModal()" class="lis-modal-close"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="form-range" onsubmit="saveRange(event)" class="lis-modal-body p-5 space-y-3.5">
            <input type="hidden" id="range-id" name="id">
            
            <div>
                <label class="lis-form-label">Parameter Laboratorium <span class="req">*</span></label>
                <select name="parameter_id" id="range-parameter" required class="lis-form-control font-semibold">
                    <option value="">-- Pilih Parameter Uji --</option>
                    <?php foreach ($parameters as $p): ?>
                        <option value="<?= $p['id'] ?>" data-unit="<?= html_escape($p['unit']) ?>">
                            [<?= html_escape($p['test_category'] ?: 'Umum') ?>] <?= html_escape($p['parameter_name']) ?> (<?= html_escape($p['unit'] ?: '-') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="lis-form-label">Jenis Kelamin</label>
                    <select name="gender" id="range-gender" class="lis-form-control">
                        <option value="ALL">Semua Gender (L & P)</option>
                        <option value="L">Laki-laki (♂ Pria)</option>
                        <option value="P">Perempuan (♀ Wanita)</option>
                    </select>
                </div>
                <div>
                    <label class="lis-form-label">Klasifikasi Usia</label>
                    <select name="age_classification" id="range-age-class" class="lis-form-control">
                        <option value="dewasa">Dewasa (&ge; 18 th)</option>
                        <option value="semua_umur">Semua Kelompok Usia</option>
                        <option value="anak">Anak-anak (1 - 17 th)</option>
                        <option value="bayi">Bayi (1 - 12 bln)</option>
                        <option value="neonatus">Neonatus (&lt; 28 hari)</option>
                        <option value="lansia">Lansia (&ge; 60 th)</option>
                        <option value="hamil">Ibu Hamil</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="lis-form-label">Label Kelompok Usia / Keterangan Demografi</label>
                <input type="text" name="age_label" id="range-age-label" class="lis-form-control" placeholder="Contoh: Dewasa (Pria), Anak-anak (1-17 th)">
            </div>

            <div class="p-3 bg-emerald-50/50 rounded-xl border border-emerald-200/60 space-y-3">
                <div class="text-[11px] font-bold text-emerald-900 flex items-center gap-1.5">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-700"></i>
                    <span>Rentang Normal (Interval Rujukan Acuan)</span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[10px] text-slate-600 block mb-1">Nilai Min (Batas Bawah Normal)</label>
                        <input type="number" step="any" name="min_val" id="range-min" class="lis-form-control" placeholder="Contoh: 13.5">
                    </div>
                    <div>
                        <label class="text-[10px] text-slate-600 block mb-1">Nilai Max (Batas Atas Normal)</label>
                        <input type="number" step="any" name="max_val" id="range-max" class="lis-form-control" placeholder="Contoh: 16.5">
                    </div>
                </div>
                <div>
                    <label class="text-[10px] text-slate-600 block mb-1">Tampilan Teks Hasil di Lembar Hasil (Display)</label>
                    <input type="text" name="reference_display" id="range-display" class="lis-form-control font-semibold" placeholder="Contoh: 13.5 - 16.5 atau Negatif">
                </div>
                <div>
                    <label class="text-[10px] text-slate-600 block mb-1">Teks Normal Standar (Jika Uji Kualitatif)</label>
                    <input type="text" name="text_normal" id="range-text-normal" class="lis-form-control" placeholder="Contoh: Negatif / Non-Reaktif">
                </div>
            </div>

            <div class="p-3 bg-rose-50/50 rounded-xl border border-rose-200/60 space-y-2">
                <div class="text-[11px] font-bold text-rose-900 flex items-center gap-1.5">
                    <i data-lucide="alert-octagon" class="w-4 h-4 text-rose-700"></i>
                    <span>Batas Kritis / Panic Values (Alarm Darurat)</span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[10px] text-slate-600 block mb-1">Bawah Kritis (L*)</label>
                        <input type="number" step="any" name="critical_low" id="range-crit-low" class="lis-form-control" placeholder="Contoh: &lt; 7.0">
                    </div>
                    <div>
                        <label class="text-[10px] text-slate-600 block mb-1">Atas Kritis (H*)</label>
                        <input type="number" step="any" name="critical_high" id="range-crit-high" class="lis-form-control" placeholder="Contoh: &gt; 20.0">
                    </div>
                </div>
            </div>

            <div>
                <label class="lis-form-label">Catatan Tambahan / Keterangan Metode</label>
                <textarea name="notes" id="range-notes" rows="2" class="lis-form-control" placeholder="Catatan opsional..."></textarea>
            </div>

            <div class="lis-modal-footer pt-3 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" onclick="closeRangeModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">Batal</button>
                <button type="submit" id="btn-save-range" class="px-5 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-1.5 shadow-sm">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Nilai Rujukan</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.lucide) {
        lucide.createIcons();
    }
});

function openRangeModal() {
    document.getElementById('modal-range-title').textContent = 'Tambah Interval Nilai Rujukan';
    document.getElementById('form-range').reset();
    document.getElementById('range-id').value = '';
    document.getElementById('modal-range').classList.add('is-open');
}

function closeRangeModal() {
    document.getElementById('modal-range').classList.remove('is-open');
}

function editRange(r) {
    document.getElementById('modal-range-title').textContent = 'Edit Nilai Rujukan - ' + r.parameter_name;
    document.getElementById('range-id').value = r.id;
    document.getElementById('range-parameter').value = r.parameter_id;
    document.getElementById('range-gender').value = r.gender;
    document.getElementById('range-age-class').value = r.age_classification;
    document.getElementById('range-age-label').value = r.age_label || '';
    document.getElementById('range-min').value = r.min_val !== null ? r.min_val : '';
    document.getElementById('range-max').value = r.max_val !== null ? r.max_val : '';
    document.getElementById('range-display').value = r.reference_display || '';
    document.getElementById('range-text-normal').value = r.text_normal || '';
    document.getElementById('range-crit-low').value = r.critical_low !== null ? r.critical_low : '';
    document.getElementById('range-crit-high').value = r.critical_high !== null ? r.critical_high : '';
    document.getElementById('range-notes').value = r.notes || '';
    document.getElementById('modal-range').classList.add('is-open');
}

function saveRange(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save-range');
    btn.disabled = true;

    const fd = new FormData(document.getElementById('form-range'));

    LIS.fetchJson('<?= site_url("master/save_nilai_rujukan") ?>', {
        method: 'POST',
        body: fd
    })
    .then(res => {
        btn.disabled = false;
        if (res.status === 'success') {
            LIS.toast('success', res.message);
            setTimeout(() => window.location.reload(), 600);
        } else {
            LIS.showError('Gagal Menyimpan Nilai Rujukan', res, res.message);
        }
    })
    .catch(err => {
        btn.disabled = false;
        LIS.showError('Error Menyimpan Nilai Rujukan', err, 'Terjadi gangguan saat menyimpan rentang nilai rujukan.');
    });
}

function deleteRange(id) {
    Swal.fire({
        title: 'Hapus Nilai Rujukan?',
        text: 'Data rentang normal & batas kritis parameter ini akan dihapus dari sistem.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            LIS.fetchJson('<?= site_url("master/delete_nilai_rujukan") ?>/' + id, {
                method: 'POST'
            })
            .then(res => {
                LIS.toast('success', res.message);
                setTimeout(() => window.location.reload(), 600);
            })
            .catch(err => {
                LIS.showError('Gagal Menghapus Nilai Rujukan', err, 'Terjadi kendala saat menghapus rentang nilai rujukan.');
            });
        }
    });
}
</script>
