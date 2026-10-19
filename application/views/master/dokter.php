<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-slate-400">Master Data</span>
                <span>/</span>
                <span class="text-emerald-800 font-semibold">Dokter Pengirim</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="stethoscope" class="w-6 h-6"></i>
                </span>
                <span>Database Dokter Pengirim Pasien</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Master data dokter rujukan, rumah sakit, puskesmas, dan klinik mitra Labkesda Riau</p>
        </div>

        <button type="button" onclick="openDokterModal()" class="px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-2 shadow-sm transition-all border border-emerald-600">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            <span>+ Tambah Dokter Pengirim</span>
        </button>
    </div>

    <!-- Tabel Dokter -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-bold bg-slate-50">
                        <th class="py-3 px-3 w-12 text-center">No</th>
                        <th class="py-3 px-3">Nama Dokter & Gelar</th>
                        <th class="py-3 px-3">Spesialisasi</th>
                        <th class="py-3 px-3">Faskes / Rumah Sakit Asal</th>
                        <th class="py-3 px-3">No. SIP / NIP</th>
                        <th class="py-3 px-3">Kontak / HP</th>
                        <th class="py-3 px-3 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    <?php if (empty($dokters)): ?>
                        <tr><td colspan="7" class="text-center py-6 text-slate-400">Belum ada data dokter pengirim.</td></tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($dokters as $d): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="py-3 px-3 text-center text-slate-400"><?= $no++ ?></td>
                                <td class="py-3 px-3">
                                    <strong class="text-slate-900 block"><?= html_escape($d['nama_dokter']) ?></strong>
                                    <span class="text-[10px] text-slate-400"><?= html_escape($d['email'] ?: '-') ?></span>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200"><?= html_escape($d['spesialisasi']) ?></span>
                                </td>
                                <td class="py-3 px-3 text-slate-700"><?= html_escape($d['faskes_asal'] ?: '-') ?></td>
                                <td class="py-3 px-3 font-mono text-slate-600"><?= html_escape($d['sip_nip'] ?: '-') ?></td>
                                <td class="py-3 px-3 font-mono text-slate-600"><?= html_escape($d['telepon'] ?: '-') ?></td>
                                <td class="py-3 px-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" onclick='editDokter(<?= json_encode($d) ?>)' class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10px]">
                                            <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                        </button>
                                        <button type="button" onclick="deleteDokter(<?= $d['id'] ?>)" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 text-[10px]">
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

<!-- Modal Dokter -->
<div id="modal-dokter" class="lis-modal">
    <div class="lis-modal-box max-w-lg w-full">
        <div class="lis-modal-header bg-gradient-to-r from-emerald-900 to-teal-900 border-b border-amber-400/40">
            <h3 class="lis-modal-title text-white flex items-center gap-2">
                <i data-lucide="stethoscope" class="w-5 h-5 text-amber-300"></i>
                <span id="modal-dokter-title">Tambah Dokter Pengirim</span>
            </h3>
            <button type="button" onclick="closeDokterModal()" class="lis-modal-close"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="form-dokter" onsubmit="saveDokter(event)" class="lis-modal-body p-5 space-y-3">
            <input type="hidden" id="dok-id" name="id">
            <div>
                <label class="lis-form-label">Nama Lengkap Dokter & Gelar <span class="req">*</span></label>
                <input type="text" name="nama_dokter" id="dok-nama" required class="lis-form-control font-bold" placeholder="dr. ... Sp....">
            </div>
            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label class="lis-form-label">Spesialisasi</label>
                    <input type="text" name="spesialisasi" id="dok-spesialisasi" class="lis-form-control" placeholder="Dokter Umum / Sp.PD...">
                </div>
                <div>
                    <label class="lis-form-label">No. SIP / NIP</label>
                    <input type="text" name="sip_nip" id="dok-sip" class="lis-form-control font-mono" placeholder="SIP/440/...">
                </div>
            </div>
            <div>
                <label class="lis-form-label">Asal Faskes / Rumah Sakit / Klinik</label>
                <input type="text" name="faskes_asal" id="dok-faskes" class="lis-form-control" placeholder="RSUD / Puskesmas / Klinik...">
            </div>
            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label class="lis-form-label">No. HP / WhatsApp</label>
                    <input type="tel" name="telepon" id="dok-telepon" class="lis-form-control font-mono" placeholder="08...">
                </div>
                <div>
                    <label class="lis-form-label">Email</label>
                    <input type="email" name="email" id="dok-email" class="lis-form-control" placeholder="dokter@email.com">
                </div>
            </div>
            <div class="pt-3 border-t border-slate-200 flex items-center justify-between">
                <button type="button" onclick="closeDokterModal()" class="px-4 py-2 rounded-xl border text-xs">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold">Simpan Dokter</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openDokterModal() {
        document.getElementById('form-dokter').reset();
        document.getElementById('dok-id').value = '';
        document.getElementById('modal-dokter-title').textContent = 'Tambah Dokter Pengirim';
        const modal = document.getElementById('modal-dokter');
        modal.classList.remove('hidden'); modal.classList.add('flex'); modal.style.display = 'flex';
        if (window.lucide) lucide.createIcons();
    }
    function closeDokterModal() {
        const modal = document.getElementById('modal-dokter');
        modal.classList.remove('flex'); modal.classList.add('hidden'); modal.style.display = 'none';
    }
    function editDokter(d) {
        document.getElementById('dok-id').value = d.id;
        document.getElementById('dok-nama').value = d.nama_dokter;
        document.getElementById('dok-spesialisasi').value = d.spesialisasi || '';
        document.getElementById('dok-sip').value = d.sip_nip || '';
        document.getElementById('dok-faskes').value = d.faskes_asal || '';
        document.getElementById('dok-telepon').value = d.telepon || '';
        document.getElementById('dok-email').value = d.email || '';
        document.getElementById('modal-dokter-title').textContent = 'Edit Data Dokter';
        const modal = document.getElementById('modal-dokter');
        modal.classList.remove('hidden'); modal.classList.add('flex'); modal.style.display = 'flex';
        if (window.lucide) lucide.createIcons();
    }
    function saveDokter(e) {
        e.preventDefault();
        const form = document.getElementById('form-dokter');
        LIS.fetchJson('<?= site_url('master/save_dokter') ?>', { method: 'POST', body: new FormData(form) })
            .then(res => {
                if (res.status === 'success') {
                    closeDokterModal();
                    LIS.toast('success', res.message);
                    setTimeout(() => location.reload(), 600);
                } else {
                    LIS.showError('Gagal Menyimpan Dokter', res, res.message);
                }
            })
            .catch(err => {
                LIS.showError('Error Simpan Dokter', err, 'Terjadi gangguan saat menyimpan data dokter pengirim.');
            });
    }
    function deleteDokter(id) {
        Swal.fire({
            title: 'Hapus Dokter Pengirim?',
            text: 'Data dokter ini akan dihapus dari sistem.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Ya, Hapus'
        }).then(res => {
            if (res.isConfirmed) {
                LIS.fetchJson('<?= site_url('master/delete_dokter') ?>/' + id, { method: 'POST' })
                    .then(res => {
                        LIS.toast('success', res.message);
                        setTimeout(() => location.reload(), 600);
                    })
                    .catch(err => {
                        LIS.showError('Gagal Menghapus Dokter', err, 'Terjadi kendala saat menghapus dokter.');
                    });
            }
        });
    }
</script>
