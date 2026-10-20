<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700">Dashboard</a><span>/</span>
                <a href="<?= site_url('aset') ?>" class="hover:text-emerald-700">Aset</a><span>/</span>
                <span class="text-emerald-800 font-semibold">Mutasi</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs"><i data-lucide="arrow-left-right" class="w-6 h-6"></i></span>
                <span>Mutasi &amp; Penghapusan Aset</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Seluruh perubahan penempatan, kondisi, dan status aset terekam permanen sebagai rekam jejak BMD</p>
        </div>
        <?php if ($can_create): ?>
            <button type="button" onclick="openMut()" class="px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-2 border border-emerald-600">
                <i data-lucide="plus" class="w-4 h-4"></i><span>+ Proses Mutasi</span>
            </button>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <?php
        $k = [
            ['Aset Aktif', $ringkasan['aktif'] ?? 0, 'check-circle', 'emerald'],
            ['Dipinjam', $ringkasan['dipinjam'] ?? 0, 'hand', 'amber'],
            ['Dalam Perbaikan', $ringkasan['perbaikan'] ?? 0, 'wrench', 'sky'],
            ['Dihapuskan', $ringkasan['dihapuskan'] ?? 0, 'archive', 'slate'],
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
        <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="scroll-text" class="w-4 h-4 text-emerald-700"></i> Rekam Jejak Mutasi
            </h2>
            <select id="f-tipe" class="lis-form-control py-1.5 text-xs w-auto">
                <option value="all">Semua Jenis</option>
                <option value="penempatan_awal">Penempatan Awal</option><option value="pindah_ruangan">Pindah Ruangan</option>
                <option value="peminjaman">Peminjaman</option><option value="pengembalian">Pengembalian</option>
                <option value="perubahan_kondisi">Perubahan Kondisi</option><option value="perbaikan">Perbaikan</option>
                <option value="penghapusan">Penghapusan</option>
            </select>
        </div>
        <div class="p-4 sm:p-5 overflow-x-auto">
            <table id="t-mut" class="w-full text-left text-xs border-collapse">
                <thead><tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] font-bold bg-slate-50">
                    <th class="py-3 px-3">Nomor</th><th class="py-3 px-3">Tanggal</th><th class="py-3 px-3">Aset</th>
                    <th class="py-3 px-3 text-center">Jenis</th><th class="py-3 px-3">Perubahan</th>
                    <th class="py-3 px-3">Dokumen / Pihak</th><th class="py-3 px-3">Petugas</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100 font-medium"></tbody>
            </table>
        </div>
    </div>
</div>

<div id="m-mut" class="lis-modal">
    <div class="lis-modal-box max-w-xl w-full">
        <div class="lis-modal-header bg-gradient-to-r from-emerald-900 to-teal-900 border-b border-amber-400/40">
            <h3 class="lis-modal-title text-white flex items-center gap-2"><i data-lucide="arrow-left-right" class="w-5 h-5 text-amber-300"></i><span>Proses Mutasi Aset</span></h3>
            <button type="button" onclick="closeMut()" class="lis-modal-close"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="f-mut" onsubmit="prosesMut(event)" class="lis-modal-body p-5 space-y-3">
            <div>
                <label class="lis-form-label">Aset <span class="req">*</span></label>
                <select name="aset_id" id="u-aset" required class="lis-form-control">
                    <option value="">-- Pilih Aset --</option>
                    <?php foreach ($aset_options as $a): ?>
                        <option value="<?= (int)$a['id'] ?>" data-ruangan="<?= html_escape($a['lokasi_ruangan']) ?>" data-kondisi="<?= html_escape($a['kondisi']) ?>" data-status="<?= html_escape($a['status']) ?>">
                            <?= html_escape($a['nama_aset']) ?> (<?= html_escape($a['nomor_register']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="lis-form-hint" id="u-info">&nbsp;</p>
            </div>

            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label class="lis-form-label">Jenis Mutasi <span class="req">*</span></label>
                    <select name="tipe" id="u-tipe" required class="lis-form-control" onchange="ubahTipe()">
                        <option value="pindah_ruangan">Pindah Ruangan</option>
                        <option value="peminjaman">Peminjaman</option>
                        <option value="pengembalian">Pengembalian</option>
                        <option value="perbaikan">Kirim Perbaikan</option>
                        <option value="perubahan_kondisi">Perubahan Kondisi</option>
                        <option value="penghapusan">Penghapusan BMD</option>
                    </select>
                </div>
                <div><label class="lis-form-label">Tanggal <span class="req">*</span></label><input type="date" name="tanggal" id="u-tgl" required class="lis-form-control" value="<?= date('Y-m-d') ?>"></div>
            </div>

            <div id="g-ruangan">
                <label class="lis-form-label">Ruangan Tujuan <span class="req">*</span></label>
                <select name="ke_ruangan" id="u-ruangan" class="lis-form-control">
                    <option value="">-- Pilih Ruangan --</option>
                    <?php foreach ($ruangan_ref as $r): ?><option value="<?= html_escape($r['nama']) ?>"><?= html_escape($r['nama']) ?></option><?php endforeach; ?>
                </select>
            </div>

            <div id="g-kondisi" class="hidden">
                <label class="lis-form-label">Kondisi Baru <span class="req">*</span></label>
                <select name="ke_kondisi" id="u-kondisi" class="lis-form-control">
                    <option value="baik">Baik (B)</option><option value="rusak_ringan">Rusak Ringan (RR)</option><option value="rusak_berat">Rusak Berat (RB)</option>
                </select>
            </div>

            <div id="g-pihak" class="hidden">
                <label class="lis-form-label">Peminjam / Vendor <span class="req">*</span></label>
                <input type="text" name="pihak_terkait" id="u-pihak" class="lis-form-control" placeholder="Nama peminjam atau vendor perbaikan">
            </div>

            <div id="g-dokumen">
                <label class="lis-form-label">Nomor Dokumen <span id="u-dok-wajib" class="req hidden">*</span></label>
                <input type="text" name="nomor_dokumen" id="u-dokumen" class="lis-form-control font-mono" placeholder="Nomor SK / BAST / surat peminjaman">
                <p class="lis-form-hint" id="u-dok-hint">Opsional untuk mutasi biasa.</p>
            </div>

            <div><label class="lis-form-label">Keterangan</label><textarea name="keterangan" id="u-ket" rows="2" class="lis-form-control"></textarea></div>

            <div class="pt-3 border-t border-slate-200 flex items-center justify-between">
                <button type="button" onclick="closeMut()" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold hover:bg-slate-50">Batal</button>
                <button type="submit" id="u-submit" class="px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold">Proses Mutasi</button>
            </div>
        </form>
    </div>
</div>

<script>
    const G_TIPE = {
        penempatan_awal:['bg-slate-100 text-slate-600 border-slate-200','Penempatan Awal'],
        pindah_ruangan:['bg-sky-50 text-sky-700 border-sky-200','Pindah Ruangan'],
        peminjaman:['bg-amber-50 text-amber-800 border-amber-200','Peminjaman'],
        pengembalian:['bg-emerald-50 text-emerald-700 border-emerald-200','Pengembalian'],
        perubahan_kondisi:['bg-yellow-50 text-yellow-800 border-yellow-200','Ubah Kondisi'],
        perbaikan:['bg-sky-50 text-sky-700 border-sky-200','Perbaikan'],
        penghapusan:['bg-rose-50 text-rose-700 border-rose-200','Penghapusan']
    };
    let tMut = null;

    $(function () {
        tMut = $('#t-mut').DataTable({
            processing:true, serverSide:true, order:[],
            ajax:{ url:'<?= site_url('aset/fetch_mutasi') ?>', type:'GET', data: d => { d.tipe = $('#f-tipe').val() || 'all'; }},
            columns:[
                { data:'nomor_transaksi', orderable:false, className:'py-3 font-mono text-slate-900' },
                { data:'tanggal', orderable:false, className:'py-3 text-slate-600' },
                { data:'nama_aset', orderable:false, className:'py-3', render:(d,t,r)=>
                    '<strong class="text-slate-900 block">'+d+'</strong><span class="text-[10px] text-slate-400 font-mono">'+r.nomor_register+'</span>' },
                { data:'tipe', orderable:false, className:'text-center py-3', render:d=>{
                    const g = G_TIPE[d] || G_TIPE.penempatan_awal;
                    return '<span class="px-2 py-0.5 rounded text-[10px] font-bold border '+g[0]+'">'+g[1]+'</span>'; }},
                { data:null, orderable:false, className:'py-3 text-slate-600', render:r=>{
                    let h = '';
                    if (r.dari_ruangan || r.ke_ruangan) h += (r.dari_ruangan||'-')+' → '+(r.ke_ruangan||'-');
                    if (r.dari_kondisi || r.ke_kondisi) h += '<div class="text-[10px]">kondisi: '+(r.dari_kondisi||'-').replace(/_/g,' ')+' → '+(r.ke_kondisi||'-').replace(/_/g,' ')+'</div>';
                    if (r.dari_status || r.ke_status) h += '<div class="text-[10px]">status: '+(r.dari_status||'-')+' → '+(r.ke_status||'-')+'</div>';
                    return h || '-'; }},
                { data:'nomor_dokumen', orderable:false, className:'py-3 text-slate-600', render:(d,t,r)=>
                    (d?'<span class="font-mono text-[11px]">'+d+'</span>':'-')+(r.pihak_terkait?'<div class="text-[10px] text-slate-500">'+r.pihak_terkait+'</div>':'') },
                { data:'user_nama', orderable:false, className:'py-3 text-slate-600', render:d=>d||'-' }
            ],
            drawCallback: () => { if (window.lucide) lucide.createIcons(); },
            language:{ processing:'Memuat...', emptyTable:'Belum ada mutasi tercatat.', zeroRecords:'Tidak ada yang cocok.',
                info:'Menampilkan _START_ - _END_ dari _TOTAL_ mutasi', infoEmpty:'Tidak ada data', lengthMenu:'Tampil _MENU_', search:'Cari:',
                paginate:{first:'Awal',last:'Akhir',next:'Lanjut',previous:'Kembali'} }
        });
        $('#f-tipe').on('change', () => tMut.ajax.reload());
    });

    function infoAset() {
        const o = document.getElementById('u-aset').selectedOptions[0];
        document.getElementById('u-info').textContent = (o && o.value)
            ? 'Saat ini: ' + (o.dataset.ruangan || 'tanpa ruangan') + ' • kondisi ' + (o.dataset.kondisi || '-').replace(/_/g,' ') + ' • status ' + (o.dataset.status || '-')
            : ' ';
    }

    function ubahTipe() {
        const t = document.getElementById('u-tipe').value;
        const show = (id, on) => document.getElementById(id).classList.toggle('hidden', !on);

        show('g-ruangan', t === 'pindah_ruangan');
        show('g-kondisi', t === 'perubahan_kondisi');
        show('g-pihak', t === 'peminjaman' || t === 'perbaikan');

        document.getElementById('u-ruangan').required = (t === 'pindah_ruangan');
        document.getElementById('u-kondisi').required = (t === 'perubahan_kondisi');
        document.getElementById('u-pihak').required = (t === 'peminjaman');

        const hapus = (t === 'penghapusan');
        document.getElementById('u-dok-wajib').classList.toggle('hidden', !hapus);
        document.getElementById('u-dokumen').required = hapus;
        document.getElementById('u-dok-hint').textContent = hapus
            ? 'Wajib diisi. Penghapusan BMD harus berdasar surat keputusan sebagai dasar hukum.'
            : 'Opsional untuk mutasi biasa.';
        document.getElementById('u-submit').className = hapus
            ? 'px-5 py-2.5 rounded-xl bg-rose-700 hover:bg-rose-800 text-white text-xs font-bold'
            : 'px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold';
        document.getElementById('u-submit').textContent = hapus ? 'Hapuskan Aset' : 'Proses Mutasi';
    }

    function openMut() {
        document.getElementById('f-mut').reset();
        document.getElementById('u-tgl').value = '<?= date('Y-m-d') ?>';
        document.getElementById('u-info').innerHTML = '&nbsp;';
        ubahTipe();
        const m=document.getElementById('m-mut'); m.classList.remove('hidden'); m.classList.add('flex'); m.style.display='flex';
        if (window.lucide) lucide.createIcons();
    }
    function closeMut(){ const m=document.getElementById('m-mut'); m.classList.remove('flex'); m.classList.add('hidden'); m.style.display='none'; }

    function prosesMut(e) {
        e.preventDefault();
        const t = document.getElementById('u-tipe').value;
        const kirim = () => LIS.fetchJson('<?= site_url('aset/proses_mutasi') ?>', { method:'POST', body:new FormData(document.getElementById('f-mut')) })
            .then(res => { closeMut(); LIS.toast('success', res.message); tMut.ajax.reload(null,false); setTimeout(()=>location.reload(), 1200); })
            .catch(err => LIS.showError('Gagal Memproses Mutasi', err, 'Mutasi aset tidak dapat disimpan.'));

        if (t === 'penghapusan') {
            Swal.fire({ title:'Hapuskan Aset dari Inventaris?',
                html:'Aset akan berstatus <strong>dihapuskan</strong> dan tidak dapat dimutasi lagi.<br><span class="text-xs text-slate-500">Datanya tetap tersimpan sebagai rekam jejak BMD dan tidak dihapus dari basis data.</span>',
                icon:'warning', showCancelButton:true, confirmButtonColor:'#be123c', confirmButtonText:'Ya, Hapuskan', cancelButtonText:'Batal'
            }).then(r => { if (r.isConfirmed) kirim(); });
        } else { kirim(); }
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('u-aset').addEventListener('change', infoAset);
    });
</script>
