<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-5">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-slate-400">Pengaturan Sistem</span>
                <span>/</span>
                <span class="text-emerald-800 font-semibold">Manajemen Pengguna</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </span>
                <span>Manajemen Staf & Pengguna Laboratorium</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Otoritas akun klinis, dokter penanggung jawab, analis ATLM, dan petugas administrasi loket</p>
        </div>

        <button type="button" onclick="openUserModal()" class="px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-2 shadow-sm transition-all border border-emerald-600">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            <span>+ Tambah Pengguna Baru</span>
        </button>
    </div>

    <!-- Statistik Pengguna Per Peran -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <?php
        $totalUsers = count($users_list);
        $totalDokter = 0;
        $totalAnalis = 0;
        $totalRegistrasi = 0;
        foreach ($users_list as $u) {
            if ($u['role_id'] == 2) $totalDokter++;
            elseif ($u['role_id'] == 3) $totalAnalis++;
            elseif ($u['role_id'] == 4) $totalRegistrasi++;
        }
        ?>
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center shrink-0">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Staf</p>
                <p class="text-xl font-black text-slate-900"><?= $totalUsers ?></p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-800 border border-sky-200 flex items-center justify-center shrink-0">
                <i data-lucide="stethoscope" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Dokter Sp.PK</p>
                <p class="text-xl font-black text-sky-900"><?= $totalDokter ?></p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-800 border border-teal-200 flex items-center justify-center shrink-0">
                <i data-lucide="flask-conical" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Analis ATLM</p>
                <p class="text-xl font-black text-teal-900"><?= $totalAnalis ?></p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-800 border border-amber-200 flex items-center justify-center shrink-0">
                <i data-lucide="clipboard-list" class="w-5 h-5"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Petugas Loket</p>
                <p class="text-xl font-black text-amber-900"><?= $totalRegistrasi ?></p>
            </div>
        </div>
    </div>

    <!-- Tabel Pengguna -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-bold bg-slate-50">
                        <th class="py-3 px-3 w-12 text-center">No</th>
                        <th class="py-3 px-3">Nama Lengkap & Username</th>
                        <th class="py-3 px-3">Peran / Otoritas Klinis</th>
                        <th class="py-3 px-3">Kontak (Email / Telepon)</th>
                        <th class="py-3 px-3">Login Terakhir</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-3 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    <?php if (empty($users_list)): ?>
                        <tr><td colspan="7" class="text-center py-6 text-slate-400">Belum ada data pengguna.</td></tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($users_list as $u): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-3 px-3 text-center text-slate-400"><?= $no++ ?></td>
                                <td class="py-3 px-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-xs uppercase border border-slate-200">
                                            <?= substr($u['username'], 0, 2) ?>
                                        </div>
                                        <div>
                                            <strong class="text-slate-900 block font-bold"><?= html_escape($u['full_name']) ?></strong>
                                            <span class="text-[10px] text-slate-400 font-mono">@<?= html_escape($u['username']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-3">
                                    <?php if ($u['role_id'] == 1): ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200 flex items-center gap-1.5 w-max">
                                            <i data-lucide="shield" class="w-3 h-3"></i>
                                            <?= html_escape($u['role_display_name']) ?>
                                        </span>
                                    <?php elseif ($u['role_id'] == 2): ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200 flex items-center gap-1.5 w-max">
                                            <i data-lucide="stethoscope" class="w-3 h-3"></i>
                                            <?= html_escape($u['role_display_name']) ?>
                                        </span>
                                    <?php elseif ($u['role_id'] == 3): ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200 flex items-center gap-1.5 w-max">
                                            <i data-lucide="flask-conical" class="w-3 h-3"></i>
                                            <?= html_escape($u['role_display_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 flex items-center gap-1.5 w-max">
                                            <i data-lucide="clipboard-signature" class="w-3 h-3"></i>
                                            <?= html_escape($u['role_display_name']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="text-[11px] text-slate-700"><?= html_escape($u['email'] ?: '-') ?></div>
                                    <div class="text-[10px] text-slate-400 font-mono"><?= html_escape($u['phone'] ?: '-') ?></div>
                                </td>
                                <td class="py-3 px-3 text-slate-500 text-[11px]">
                                    <?= !empty($u['last_login']) ? date('d/m/Y H:i', strtotime($u['last_login'])) : '<span class="text-slate-400 italic">Belum pernah</span>' ?>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <button type="button" onclick="toggleStatus(<?= $u['id'] ?>)" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold cursor-pointer transition-colors <?= $u['is_active'] == 1 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200 hover:bg-slate-200' ?>">
                                        <span class="w-1.5 h-1.5 rounded-full <?= $u['is_active'] == 1 ? 'bg-emerald-500' : 'bg-slate-400' ?>"></span>
                                        <span><?= $u['is_active'] == 1 ? 'Aktif' : 'Nonaktif' ?></span>
                                    </button>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" onclick='editUser(<?= json_encode($u) ?>)' class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10px] transition-colors" title="Edit Profil Pengguna">
                                            <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                        </button>
                                        <button type="button" onclick="resetPasswordModal(<?= $u['id'] ?>, '<?= html_escape($u['full_name']) ?>')" class="p-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 text-[10px] transition-colors" title="Reset Kata Sandi">
                                            <i data-lucide="key" class="w-3.5 h-3.5"></i>
                                        </button>
                                        <?php if ($u['id'] != 1 && $u['id'] != $user['id']): ?>
                                            <button type="button" onclick="deleteUser(<?= $u['id'] ?>)" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 text-[10px] transition-colors" title="Hapus Pengguna">
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

<!-- Modal Tambah/Edit Pengguna -->
<div id="modal-user" class="lis-modal">
    <div class="lis-modal-box max-w-lg w-full">
        <div class="lis-modal-header bg-gradient-to-r from-emerald-900 to-teal-900 border-b border-amber-400/40">
            <h3 class="lis-modal-title text-white flex items-center gap-2">
                <i data-lucide="user" class="w-5 h-5 text-amber-300"></i>
                <span id="modal-user-title">Tambah Pengguna Baru</span>
            </h3>
            <button type="button" onclick="closeUserModal()" class="lis-modal-close"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="form-user" onsubmit="saveUser(event)" class="lis-modal-body p-5 space-y-3.5">
            <input type="hidden" id="user-id" name="id">

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="lis-form-label">Username Akun <span class="req">*</span></label>
                    <input type="text" name="username" id="user-username" required class="lis-form-control font-mono font-bold" placeholder="username_staf">
                </div>
                <div>
                    <label class="lis-form-label">Peran / Role RBAC <span class="req">*</span></label>
                    <select name="role_id" id="user-role" required class="lis-form-control font-bold">
                        <option value="">-- Pilih Peran --</option>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= html_escape($r['display_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="lis-form-label">Nama Lengkap & Gelar <span class="req">*</span></label>
                <input type="text" name="full_name" id="user-fullname" required class="lis-form-control font-bold" placeholder="Contoh: dr. Ahmad, Sp.PK atau Siti, A.Md.AK">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="lis-form-label">Email Dinas / Kontak</label>
                    <input type="email" name="email" id="user-email" class="lis-form-control" placeholder="email@labkesda.riau.go.id">
                </div>
                <div>
                    <label class="lis-form-label">Nomor HP / WhatsApp</label>
                    <input type="text" name="phone" id="user-phone" class="lis-form-control" placeholder="0812xxxxxxxx">
                </div>
            </div>

            <div>
                <label class="lis-form-label" id="lbl-password">Kata Sandi <span class="text-slate-400 font-normal text-[10px]">(Biarkan kosong untuk default: password123)</span></label>
                <input type="password" name="password" id="user-password" class="lis-form-control" placeholder="Minimal 6 karakter">
            </div>

            <div class="lis-modal-footer pt-3 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" onclick="closeUserModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">Batal</button>
                <button type="submit" id="btn-save-user" class="px-5 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-1.5 shadow-sm">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Pengguna</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Reset Password -->
<div id="modal-reset" class="lis-modal">
    <div class="lis-modal-box max-w-sm w-full">
        <div class="lis-modal-header bg-slate-900 border-b border-amber-400/40">
            <h3 class="lis-modal-title text-white flex items-center gap-2">
                <i data-lucide="key" class="w-5 h-5 text-amber-300"></i>
                <span>Reset Kata Sandi</span>
            </h3>
            <button type="button" onclick="closeResetModal()" class="lis-modal-close"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="form-reset" onsubmit="saveResetPassword(event)" class="lis-modal-body p-5 space-y-3.5">
            <input type="hidden" id="reset-id" name="id">
            <p class="text-xs text-slate-600">Reset kata sandi untuk pengguna: <strong id="reset-user-name" class="text-slate-900 font-bold block mt-0.5"></strong></p>
            <div>
                <label class="lis-form-label">Kata Sandi Baru</label>
                <input type="text" name="new_password" id="reset-password-val" class="lis-form-control font-mono font-bold" value="password123" placeholder="Masukkan sandi baru">
                <span class="text-[10px] text-slate-400 mt-1 block">Default: password123</span>
            </div>
            <div class="lis-modal-footer pt-3 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" onclick="closeResetModal()" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold flex items-center gap-1.5 shadow-sm">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Terapkan Sandi Baru</span>
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

function openUserModal() {
    document.getElementById('modal-user-title').textContent = 'Tambah Pengguna Baru';
    document.getElementById('form-user').reset();
    document.getElementById('user-id').value = '';
    document.getElementById('user-username').readOnly = false;
    document.getElementById('lbl-password').innerHTML = 'Kata Sandi <span class="text-slate-400 font-normal text-[10px]">(Biarkan kosong untuk default: password123)</span>';
    document.getElementById('modal-user').classList.add('is-open');
}

function closeUserModal() {
    document.getElementById('modal-user').classList.remove('is-open');
}

function editUser(u) {
    document.getElementById('modal-user-title').textContent = 'Edit Pengguna: ' + u.full_name;
    document.getElementById('user-id').value = u.id;
    document.getElementById('user-username').value = u.username;
    document.getElementById('user-username').readOnly = true;
    document.getElementById('user-role').value = u.role_id;
    document.getElementById('user-fullname').value = u.full_name;
    document.getElementById('user-email').value = u.email || '';
    document.getElementById('user-phone').value = u.phone || '';
    document.getElementById('user-password').value = '';
    document.getElementById('lbl-password').innerHTML = 'Kata Sandi Baru <span class="text-slate-400 font-normal text-[10px]">(Kosongkan jika tidak ingin mengubah)</span>';
    document.getElementById('modal-user').classList.add('is-open');
}

function saveUser(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save-user');
    btn.disabled = true;

    const fd = new FormData(document.getElementById('form-user'));

    LIS.fetchJson('<?= site_url("users/save_user") ?>', {
        method: 'POST',
        body: fd
    })
    .then(res => {
        btn.disabled = false;
        if (res.status === 'success') {
            LIS.toast('success', res.message);
            setTimeout(() => window.location.reload(), 600);
        } else {
            LIS.showError('Gagal Menyimpan Pengguna', res, res.message);
        }
    })
    .catch(err => {
        btn.disabled = false;
        LIS.showError('Error Menyimpan Pengguna', err, 'Terjadi gangguan saat menyimpan data pengguna.');
    });
}

function toggleStatus(id) {
    LIS.fetchJson('<?= site_url("users/toggle_status") ?>/' + id, {
        method: 'POST'
    })
    .then(res => {
        LIS.toast('success', res.message);
        setTimeout(() => window.location.reload(), 600);
    })
    .catch(err => {
        LIS.showError('Gagal Memperbarui Status Pengguna', err, 'Terjadi kendala saat mengubah status akun.');
    });
}

function resetPasswordModal(id, name) {
    document.getElementById('reset-id').value = id;
    document.getElementById('reset-user-name').textContent = name;
    document.getElementById('reset-password-val').value = 'password123';
    document.getElementById('modal-reset').classList.add('is-open');
}

function closeResetModal() {
    document.getElementById('modal-reset').classList.remove('is-open');
}

function saveResetPassword(e) {
    e.preventDefault();
    const fd = new FormData(document.getElementById('form-reset'));

    LIS.fetchJson('<?= site_url("users/reset_password") ?>', {
        method: 'POST',
        body: fd
    })
    .then(res => {
        LIS.toast('success', res.message);
        closeResetModal();
    })
    .catch(err => {
        LIS.showError('Gagal Reset Password', err, 'Terjadi kendala saat mereset kata sandi.');
    });
}

function deleteUser(id) {
    Swal.fire({
        title: 'Hapus Pengguna?',
        text: 'Pengguna ini tidak akan dapat login lagi ke sistem LIS.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            LIS.fetchJson('<?= site_url("users/delete_user") ?>/' + id, {
                method: 'POST'
            })
            .then(res => {
                LIS.toast('success', res.message);
                setTimeout(() => window.location.reload(), 600);
            })
            .catch(err => {
                LIS.showError('Gagal Menghapus Pengguna', err, 'Terjadi kendala saat menghapus akun pengguna.');
            });
        }
    });
}
</script>
