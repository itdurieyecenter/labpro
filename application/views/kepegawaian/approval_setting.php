<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-slate-400">Kepegawaian</span>
                <span>/</span>
                <span class="text-emerald-800 font-semibold">Setting Approval</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="user-cog" class="w-6 h-6"></i>
                </span>
                <span>Hirarki Approval Cuti ASN</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Urutan pejabat yang menyetujui permohonan cuti, dari atasan langsung hingga Kepala UPT</p>
        </div>

        <?php if ($can_create): ?>
            <button type="button" onclick="openRuleModal()" class="px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-2 shadow-sm transition-all border border-emerald-600">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>+ Tambah Tahap Approval</span>
            </button>
        <?php endif; ?>
    </div>

    <?php if (empty($rules)): ?>
        <div class="bg-amber-50 border border-amber-300 rounded-2xl p-5 flex items-start gap-3">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-700 shrink-0 mt-0.5"></i>
            <div class="text-xs text-amber-900">
                <strong class="block mb-1">Hirarki approval belum dikonfigurasi.</strong>
                Selama belum ada satu pun tahap aktif, pengajuan cuti tidak dapat dikirim ke alur persetujuan.
                Tambahkan minimal satu tahap, misalnya Kepala Seksi/Instalasi, lalu Kasubbag Tata Usaha, dan terakhir Kepala UPT.
            </div>
        </div>
    <?php endif; ?>

    <!-- Rantai Approval -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="git-branch" class="w-4 h-4 text-emerald-700"></i>
                Alur Persetujuan Berjenjang
            </h2>
            <span class="text-[11px] text-slate-500"><?= count($rules) ?> tahap terdaftar</span>
        </div>

        <div class="p-4 sm:p-5 overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-bold bg-slate-50">
                        <th class="py-3 px-3 w-16 text-center">Tahap</th>
                        <th class="py-3 px-3">Pejabat Pemberi Persetujuan</th>
                        <th class="py-3 px-3">Peran dalam Alur</th>
                        <th class="py-3 px-3">Keterangan</th>
                        <th class="py-3 px-3 text-center w-24">Status</th>
                        <th class="py-3 px-3 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    <?php if (empty($rules)): ?>
                        <tr><td colspan="6" class="text-center py-8 text-slate-400">Belum ada tahap approval yang dikonfigurasi.</td></tr>
                    <?php else: ?>
                        <?php foreach ($rules as $i => $r): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="py-3 px-3 text-center">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-700 text-amber-200 font-black text-[11px]"><?= (int)$r['urutan'] ?></span>
                                </td>
                                <td class="py-3 px-3">
                                    <strong class="text-slate-900 block"><?= html_escape($r['nama_formatted']) ?></strong>
                                    <span class="text-[10px] text-slate-400 font-mono">NIP. <?= html_escape($r['nip'] ?: '-') ?></span>
                                    <span class="text-[10px] text-slate-500 block"><?= html_escape($r['pegawai_jabatan'] ?: '-') ?></span>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200"><?= html_escape($r['jabatan_approval']) ?></span>
                                    <?php if ((int)$r['is_required'] === 1): ?>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200 ml-1">Wajib</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-3 text-slate-600"><?= html_escape($r['keterangan'] ?: '-') ?></td>
                                <td class="py-3 px-3 text-center">
                                    <?php if ((int)$r['is_active'] === 1): ?>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <?php if ($can_edit): ?>
                                            <button type="button" onclick="moveRule(<?= (int)$r['id'] ?>, 'up')" <?= $i === 0 ? 'disabled' : '' ?>
                                                    class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 disabled:opacity-30 disabled:cursor-not-allowed" title="Naikkan urutan">
                                                <i data-lucide="arrow-up" class="w-3.5 h-3.5"></i>
                                            </button>
                                            <button type="button" onclick="moveRule(<?= (int)$r['id'] ?>, 'down')" <?= $i === count($rules) - 1 ? 'disabled' : '' ?>
                                                    class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 disabled:opacity-30 disabled:cursor-not-allowed" title="Turunkan urutan">
                                                <i data-lucide="arrow-down" class="w-3.5 h-3.5"></i>
                                            </button>
                                            <button type="button" onclick='editRule(<?= json_encode($r, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700" title="Ubah">
                                                <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($can_delete): ?>
                                            <button type="button" onclick="deleteRule(<?= (int)$r['id'] ?>, '<?= html_escape(addslashes($r['jabatan_approval'])) ?>')" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700" title="Hapus">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        <?php endif; ?>
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

<!-- Modal Tahap Approval -->
<div id="modal-rule" class="lis-modal">
    <div class="lis-modal-box max-w-lg w-full">
        <div class="lis-modal-header bg-gradient-to-r from-emerald-900 to-teal-900 border-b border-amber-400/40">
            <h3 class="lis-modal-title text-white flex items-center gap-2">
                <i data-lucide="user-cog" class="w-5 h-5 text-amber-300"></i>
                <span id="modal-rule-title">Tambah Tahap Approval</span>
            </h3>
            <button type="button" onclick="closeRuleModal()" class="lis-modal-close"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>

        <form id="form-rule" onsubmit="saveRule(event)" class="lis-modal-body p-5 space-y-3">
            <input type="hidden" id="rule-id" name="id">

            <div class="grid grid-cols-3 gap-2.5">
                <div>
                    <label class="lis-form-label">Urutan <span class="req">*</span></label>
                    <input type="number" name="urutan" id="rule-urutan" min="1" required class="lis-form-control font-bold text-center" value="<?= (int)$next_urutan ?>">
                </div>
                <div class="col-span-2">
                    <label class="lis-form-label">Peran dalam Alur <span class="req">*</span></label>
                    <input type="text" name="jabatan_approval" id="rule-jabatan" required list="list-peran" class="lis-form-control" placeholder="Kepala Seksi / Kasubbag TU / Kepala UPT">
                    <datalist id="list-peran">
                        <option value="Atasan Langsung / Kepala Seksi">
                        <option value="Kepala Instalasi">
                        <option value="Kasubbag Tata Usaha">
                        <option value="Kepala UPT Laboratorium Kesehatan">
                    </datalist>
                </div>
            </div>

            <div>
                <label class="lis-form-label">Pejabat Pemberi Persetujuan <span class="req">*</span></label>
                <select name="pegawai_id" id="rule-pegawai" required class="lis-form-control">
                    <option value="">-- Pilih Pegawai --</option>
                    <?php foreach ($pegawai_options as $p): ?>
                        <option value="<?= (int)$p['id'] ?>"><?= html_escape($p['nama_formatted']) ?> &mdash; <?= html_escape($p['nama_jabatan'] ?: 'Tanpa Jabatan') ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="lis-form-hint">Tanda tangan digital pegawai ini akan ikut tercetak pada surat izin cuti.</p>
            </div>

            <div>
                <label class="lis-form-label">Keterangan</label>
                <textarea name="keterangan" id="rule-keterangan" rows="2" class="lis-form-control" placeholder="Catatan opsional mengenai kewenangan tahap ini"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-2.5">
                <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer">
                    <input type="checkbox" name="is_required" id="rule-required" value="1" checked class="rounded border-slate-300 text-emerald-700">
                    <span class="text-xs font-semibold text-slate-700">Wajib disetujui</span>
                </label>
                <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer">
                    <input type="checkbox" name="is_active" id="rule-active" value="1" checked class="rounded border-slate-300 text-emerald-700">
                    <span class="text-xs font-semibold text-slate-700">Tahap aktif</span>
                </label>
            </div>

            <div class="pt-3 border-t border-slate-200 flex items-center justify-between">
                <button type="button" onclick="closeRuleModal()" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold">Simpan Tahap</button>
            </div>
        </form>
    </div>
</div>

<script>
    function showRuleModal() {
        const modal = document.getElementById('modal-rule');
        modal.classList.remove('hidden'); modal.classList.add('flex'); modal.style.display = 'flex';
        if (window.lucide) lucide.createIcons();
    }

    function openRuleModal() {
        document.getElementById('form-rule').reset();
        document.getElementById('rule-id').value = '';
        document.getElementById('rule-urutan').value = '<?= (int)$next_urutan ?>';
        document.getElementById('rule-required').checked = true;
        document.getElementById('rule-active').checked = true;
        document.getElementById('modal-rule-title').textContent = 'Tambah Tahap Approval';
        showRuleModal();
    }

    function closeRuleModal() {
        const modal = document.getElementById('modal-rule');
        modal.classList.remove('flex'); modal.classList.add('hidden'); modal.style.display = 'none';
    }

    function editRule(r) {
        document.getElementById('rule-id').value = r.id;
        document.getElementById('rule-urutan').value = r.urutan;
        document.getElementById('rule-jabatan').value = r.jabatan_approval;
        document.getElementById('rule-pegawai').value = r.pegawai_id;
        document.getElementById('rule-keterangan').value = r.keterangan || '';
        document.getElementById('rule-required').checked = String(r.is_required) === '1';
        document.getElementById('rule-active').checked = String(r.is_active) === '1';
        document.getElementById('modal-rule-title').textContent = 'Ubah Tahap Approval';
        showRuleModal();
    }

    function saveRule(e) {
        e.preventDefault();
        const form = document.getElementById('form-rule');
        const body = new FormData(form);

        // Checkbox yang tidak tercentang tidak terkirim, kirim nilai 0 secara eksplisit.
        if (!document.getElementById('rule-required').checked) body.set('is_required', '0');
        if (!document.getElementById('rule-active').checked) body.set('is_active', '0');

        LIS.fetchJson('<?= site_url('kepegawaian/rule_save') ?>', { method: 'POST', body: body })
            .then(res => {
                closeRuleModal();
                LIS.toast('success', res.message);
                setTimeout(() => location.reload(), 600);
            })
            .catch(err => LIS.showError('Gagal Menyimpan Tahap Approval', err, 'Terjadi gangguan saat menyimpan konfigurasi approval cuti.'));
    }

    function moveRule(id, direction) {
        const body = new FormData();
        body.append('id', id);
        body.append('direction', direction);

        LIS.fetchJson('<?= site_url('kepegawaian/rule_move') ?>', { method: 'POST', body: body })
            .then(() => location.reload())
            .catch(err => LIS.showError('Gagal Mengubah Urutan', err, 'Urutan tahap approval tidak dapat diubah.'));
    }

    function deleteRule(id, nama) {
        Swal.fire({
            title: 'Hapus Tahap Approval?',
            html: 'Tahap <strong>' + nama + '</strong> akan dihapus dan urutan tahap lain dirapikan ulang.<br><span class="text-xs text-slate-500">Pengajuan cuti yang sedang berjalan tidak terpengaruh.</span>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(res => {
            if (!res.isConfirmed) return;

            LIS.fetchJson('<?= site_url('kepegawaian/rule_delete') ?>/' + id, { method: 'POST' })
                .then(res => {
                    LIS.toast('success', res.message);
                    setTimeout(() => location.reload(), 600);
                })
                .catch(err => LIS.showError('Gagal Menghapus Tahap', err, 'Tahap approval tidak dapat dihapus.'));
        });
    }
</script>
