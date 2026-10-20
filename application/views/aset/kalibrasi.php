<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700">Dashboard</a><span>/</span>
                <a href="<?= site_url('aset') ?>" class="hover:text-emerald-700">Aset</a><span>/</span>
                <span class="text-emerald-800 font-semibold">Kalibrasi</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs"><i data-lucide="ruler" class="w-6 h-6"></i></span>
                <span>Kalibrasi Instrumen</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">ISO/IEC 17025 &mdash; instrumen ukur wajib terkalibrasi oleh lembaga terakreditasi dan masih berlaku</p>
        </div>
        <?php if ($can_create): ?>
            <button type="button" onclick="openKal()" class="px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-2 border border-emerald-600">
                <i data-lucide="plus" class="w-4 h-4"></i><span>+ Catat Kalibrasi</span>
            </button>
        <?php endif; ?>
    </div>

    <?php if ($ringkasan['tidak_laik'] > 0): ?>
        <div class="bg-rose-50 border border-rose-300 rounded-2xl p-5 flex items-start gap-3">
            <i data-lucide="alert-octagon" class="w-5 h-5 text-rose-700 shrink-0 mt-0.5"></i>
            <div class="text-xs text-rose-900">
                <strong class="block mb-1"><?= (int)$ringkasan['tidak_laik'] ?> instrumen tidak laik pakai.</strong>
                Instrumen ini tidak boleh dipakai menguji sampai kalibrasi ulang dinyatakan laik.
            </div>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <?php
        $k = [
            ['Wajib Kalibrasi', $ringkasan['wajib_kalibrasi'], 'ruler', 'slate'],
            ['Tidak Laik Pakai', $ringkasan['tidak_laik'], 'x-octagon', 'rose'],
            ['Perlu Perhatian', $ringkasan['kalibrasi_perhatian'], 'alert-triangle', 'amber'],
            ['Total Aset', $ringkasan['total'], 'boxes', 'emerald'],
        ];
        foreach ($k as $c): ?>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider"><?= $c[0] ?></span>
                    <i data-lucide="<?= $c[2] ?>" class="w-4 h-4 text-<?= $c[3] ?>-600"></i>
                </div>
                <span class="text-2xl font-black text-slate-900"><?= (int)$c[1] ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600"></i> Instrumen yang Perlu Ditindaklanjuti
            </h2>
            <span class="text-[11px] text-slate-500"><?= count($perhatian) ?> instrumen</span>
        </div>
        <div class="p-4 sm:p-5 overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead><tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] font-bold bg-slate-50">
                    <th class="py-3 px-3 w-12 text-center">No</th><th class="py-3 px-3">Instrumen</th>
                    <th class="py-3 px-3">Status Kalibrasi</th><th class="py-3 px-3">Sertifikat Terakhir</th>
                    <th class="py-3 px-3 text-center w-28">Aksi</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    <?php if (empty($perhatian)): ?>
                        <tr><td colspan="5" class="text-center py-10 text-slate-400">Seluruh instrumen terkalibrasi dan masih berlaku.</td></tr>
                    <?php else: $no = 1; foreach ($perhatian as $a):
                        $kk = $a['kalibrasi'];
                        $gaya = in_array($kk['status'], ['tidak_laik','kedaluwarsa','belum_pernah'], true)
                            ? 'bg-rose-50 text-rose-700 border-rose-200'
                            : ($kk['status'] === 'kritis' ? 'bg-amber-50 text-amber-800 border-amber-300' : 'bg-yellow-50 text-yellow-800 border-yellow-200');
                    ?>
                        <tr class="hover:bg-slate-50 <?= !$a['laik_pakai'] ? 'bg-rose-50/40' : '' ?>">
                            <td class="py-3 px-3 text-center text-slate-400"><?= $no++ ?></td>
                            <td class="py-3 px-3">
                                <strong class="text-slate-900 block"><?= html_escape($a['nama_aset']) ?></strong>
                                <span class="text-[10px] text-slate-400 font-mono"><?= html_escape($a['nomor_register']) ?></span>
                                <span class="text-[10px] text-slate-500 block"><?= html_escape($a['lokasi_ruangan'] ?: '-') ?></span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold border <?= $gaya ?>"><?= html_escape($kk['label']) ?></span>
                                <?php if (!$a['laik_pakai']): ?><span class="block mt-1 text-[10px] font-bold text-rose-700">⊘ Dilarang dipakai menguji</span><?php endif; ?>
                                <?php if ($kk['sisa_hari'] !== null): ?>
                                    <span class="block text-[10px] text-slate-500 mt-0.5"><?= $kk['sisa_hari'] < 0 ? 'Lewat ' . abs($kk['sisa_hari']) . ' hari' : 'Sisa ' . $kk['sisa_hari'] . ' hari' ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-3 text-slate-600">
                                <?= html_escape($kk['sertifikat'] ?? '-') ?>
                                <span class="block text-[10px] text-slate-400"><?= html_escape($kk['lembaga'] ?? 'Belum ada') ?></span>
                                <span class="block text-[10px] text-slate-500">Berlaku s/d <?= html_escape($kk['berlaku_sampai'] ?: '-') ?></span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <?php if ($can_create): ?>
                                    <button type="button" onclick="openKal(<?= (int)$a['id'] ?>)" class="px-3 py-1.5 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white text-[10px] font-bold">Catat Kalibrasi</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="m-kal" class="lis-modal">
    <div class="lis-modal-box max-w-xl w-full">
        <div class="lis-modal-header bg-gradient-to-r from-emerald-900 to-teal-900 border-b border-amber-400/40">
            <h3 class="lis-modal-title text-white flex items-center gap-2"><i data-lucide="ruler" class="w-5 h-5 text-amber-300"></i><span>Catat Hasil Kalibrasi</span></h3>
            <button type="button" onclick="closeKal()" class="lis-modal-close"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="f-kal" onsubmit="simpanKal(event)" class="lis-modal-body p-5 space-y-3">
            <div>
                <label class="lis-form-label">Instrumen <span class="req">*</span></label>
                <select name="aset_id" id="k-aset" required class="lis-form-control">
                    <option value="">-- Pilih Instrumen --</option>
                    <?php foreach ($aset_options as $a): ?>
                        <option value="<?= (int)$a['id'] ?>" data-interval="<?= (int)$a['interval_kalibrasi_bulan'] ?>">
                            <?= html_escape($a['nama_aset']) ?> (<?= html_escape($a['nomor_register']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-2.5">
                <div><label class="lis-form-label">Tanggal Kalibrasi <span class="req">*</span></label><input type="date" name="tanggal_kalibrasi" id="k-tgl" required class="lis-form-control" value="<?= date('Y-m-d') ?>"></div>
                <div><label class="lis-form-label">Berlaku Sampai</label><input type="date" name="masa_berlaku_sampai" id="k-exp" class="lis-form-control"><p class="lis-form-hint">Kosongkan agar dihitung dari interval aset.</p></div>
            </div>
            <div>
                <label class="lis-form-label">Lembaga Kalibrasi <span class="req">*</span></label>
                <input type="text" name="lembaga_kalibrasi" id="k-lembaga" required list="l-lembaga" class="lis-form-control">
                <datalist id="l-lembaga"><?php foreach ($lembaga_ref as $l): ?><option value="<?= html_escape($l['nama']) ?>"><?php endforeach; ?></datalist>
            </div>
            <div class="grid grid-cols-2 gap-2.5">
                <div><label class="lis-form-label">Nomor Sertifikat</label><input type="text" name="nomor_sertifikat" id="k-sertifikat" class="lis-form-control font-mono"></div>
                <div>
                    <label class="lis-form-label">Hasil <span class="req">*</span></label>
                    <select name="hasil" id="k-hasil" required class="lis-form-control">
                        <option value="laik">Laik Pakai</option>
                        <option value="laik_dengan_catatan">Laik dengan Catatan</option>
                        <option value="tidak_laik">Tidak Laik</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2.5">
                <div><label class="lis-form-label">Faktor Koreksi / Ketidakpastian</label><input type="text" name="nilai_koreksi" id="k-koreksi" class="lis-form-control" placeholder="Mis. ±0,02 °C"></div>
                <div><label class="lis-form-label">Biaya</label><input type="number" step="0.01" min="0" name="biaya" id="k-biaya" class="lis-form-control font-mono" value="0"></div>
            </div>
            <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer">
                <input type="checkbox" name="terakreditasi_kan" id="k-kan" value="1" checked class="rounded border-slate-300 text-emerald-700">
                <span class="text-xs font-semibold text-slate-700">Lembaga terakreditasi KAN</span>
            </label>
            <div><label class="lis-form-label">Catatan</label><textarea name="catatan" id="k-catatan" rows="2" class="lis-form-control"></textarea></div>
            <div class="pt-3 border-t border-slate-200 flex items-center justify-between">
                <button type="button" onclick="closeKal()" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold">Simpan Kalibrasi</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openKal(asetId) {
        document.getElementById('f-kal').reset();
        document.getElementById('k-tgl').value = '<?= date('Y-m-d') ?>';
        document.getElementById('k-kan').checked = true;
        if (asetId) document.getElementById('k-aset').value = asetId;
        const m = document.getElementById('m-kal'); m.classList.remove('hidden'); m.classList.add('flex'); m.style.display='flex';
        if (window.lucide) lucide.createIcons();
    }
    function closeKal() { const m=document.getElementById('m-kal'); m.classList.remove('flex'); m.classList.add('hidden'); m.style.display='none'; }

    function simpanKal(e) {
        e.preventDefault();
        const body = new FormData(document.getElementById('f-kal'));
        if (!document.getElementById('k-kan').checked) body.set('terakreditasi_kan','0');
        LIS.fetchJson('<?= site_url('aset/simpan_kalibrasi') ?>', { method:'POST', body: body })
            .then(res => { closeKal(); LIS.alert('Kalibrasi Tersimpan', res.message, 'success'); setTimeout(()=>location.reload(), 1400); })
            .catch(err => LIS.showError('Gagal Menyimpan Kalibrasi', err, 'Data kalibrasi tidak dapat disimpan.'));
    }
</script>
