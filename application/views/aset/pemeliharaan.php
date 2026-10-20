<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700">Dashboard</a><span>/</span>
                <a href="<?= site_url('aset') ?>" class="hover:text-emerald-700">Aset</a><span>/</span>
                <span class="text-emerald-800 font-semibold">Pemeliharaan</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs"><i data-lucide="wrench" class="w-6 h-6"></i></span>
                <span>Kartu Pemeliharaan Aset</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Jadwal berikutnya dihitung dari realisasi terakhir ditambah interval pemeliharaan tiap aset</p>
        </div>
        <?php if ($can_create): ?>
            <button type="button" onclick="openPel()" class="px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-2 border border-emerald-600">
                <i data-lucide="plus" class="w-4 h-4"></i><span>+ Catat Pemeliharaan</span>
            </button>
        <?php endif; ?>
    </div>

    <?php if (!empty($jatuh_tempo)): ?>
        <div class="bg-white rounded-2xl border border-amber-300 shadow-xs overflow-hidden">
            <div class="px-5 py-3.5 border-b border-amber-200 bg-amber-50 flex items-center justify-between">
                <h2 class="text-xs font-bold text-amber-900 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="alarm-clock" class="w-4 h-4"></i> Jatuh Tempo &amp; Terlewat
                </h2>
                <span class="text-[11px] text-amber-800"><?= count($jatuh_tempo) ?> aset</span>
            </div>
            <div class="p-4 overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead><tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] font-bold bg-slate-50">
                        <th class="py-2.5 px-2">Aset</th><th class="py-2.5 px-2">Status</th>
                        <th class="py-2.5 px-2">Jatuh Tempo</th><th class="py-2.5 px-2">Terakhir Dipelihara</th>
                        <th class="py-2.5 px-2 text-center w-24">Aksi</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <?php foreach ($jatuh_tempo as $a): $t = $a['pemeliharaan']['status'] === 'terlewat'; ?>
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5 px-2">
                                    <strong class="text-slate-900 block"><?= html_escape($a['nama_aset']) ?></strong>
                                    <span class="text-[10px] text-slate-400 font-mono"><?= html_escape($a['nomor_register']) ?></span>
                                </td>
                                <td class="py-2.5 px-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border <?= $t ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-amber-50 text-amber-800 border-amber-300' ?>">
                                        <?= html_escape($a['pemeliharaan']['label']) ?>
                                    </span>
                                </td>
                                <td class="py-2.5 px-2 text-slate-600">
                                    <?= html_escape($a['pemeliharaan']['jatuh_tempo'] ?: '-') ?>
                                    <span class="block text-[10px] text-slate-400"><?= (int)abs($a['pemeliharaan']['sisa_hari']) ?> hari <?= $t ? 'terlewat' : 'lagi' ?></span>
                                </td>
                                <td class="py-2.5 px-2 text-slate-600"><?= html_escape($a['pemeliharaan']['terakhir'] ?: 'Belum pernah') ?></td>
                                <td class="py-2.5 px-2 text-center">
                                    <?php if ($can_create): ?>
                                        <button type="button" onclick="openPel(<?= (int)$a['id'] ?>)" class="px-3 py-1.5 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white text-[10px] font-bold">Catat</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="history" class="w-4 h-4 text-emerald-700"></i> Riwayat Pemeliharaan
            </h2>
            <div class="flex items-center gap-2 flex-wrap">
                <select id="f-status" class="lis-form-control py-1.5 text-xs w-auto">
                    <option value="all">Semua Status</option><option value="terjadwal">Terjadwal</option>
                    <option value="selesai">Selesai</option><option value="dibatalkan">Dibatalkan</option>
                </select>
                <select id="f-jenis" class="lis-form-control py-1.5 text-xs w-auto">
                    <option value="all">Semua Jenis</option><option value="preventif">Preventif</option>
                    <option value="korektif">Korektif</option><option value="perbaikan">Perbaikan</option>
                </select>
            </div>
        </div>
        <div class="p-4 sm:p-5 overflow-x-auto">
            <table id="t-pel" class="w-full text-left text-xs border-collapse">
                <thead><tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] font-bold bg-slate-50">
                    <th class="py-3 px-3 w-12 text-center">No</th><th class="py-3 px-3">Aset</th>
                    <th class="py-3 px-3 text-center">Jenis</th><th class="py-3 px-3">Jadwal / Realisasi</th>
                    <th class="py-3 px-3">Pelaksana</th><th class="py-3 px-3 text-right">Biaya</th>
                    <th class="py-3 px-3 text-center">Status</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100 font-medium"></tbody>
            </table>
        </div>
    </div>
</div>

<div id="m-pel" class="lis-modal">
    <div class="lis-modal-box max-w-xl w-full">
        <div class="lis-modal-header bg-gradient-to-r from-emerald-900 to-teal-900 border-b border-amber-400/40">
            <h3 class="lis-modal-title text-white flex items-center gap-2"><i data-lucide="wrench" class="w-5 h-5 text-amber-300"></i><span>Catat Pemeliharaan</span></h3>
            <button type="button" onclick="closePel()" class="lis-modal-close"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="f-pel" onsubmit="simpanPel(event)" class="lis-modal-body p-5 space-y-3">
            <div>
                <label class="lis-form-label">Aset <span class="req">*</span></label>
                <select name="aset_id" id="p-aset" required class="lis-form-control">
                    <option value="">-- Pilih Aset --</option>
                    <?php foreach ($aset_options as $a): ?>
                        <option value="<?= (int)$a['id'] ?>"><?= html_escape($a['nama_aset']) ?> (<?= html_escape($a['nomor_register']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-3 gap-2.5">
                <div>
                    <label class="lis-form-label">Jenis <span class="req">*</span></label>
                    <select name="jenis" id="p-jenis" required class="lis-form-control">
                        <option value="preventif">Preventif</option><option value="korektif">Korektif</option><option value="perbaikan">Perbaikan</option>
                    </select>
                </div>
                <div><label class="lis-form-label">Tgl Rencana</label><input type="date" name="tanggal_rencana" id="p-rencana" class="lis-form-control"></div>
                <div><label class="lis-form-label">Tgl Realisasi</label><input type="date" name="tanggal_realisasi" id="p-realisasi" class="lis-form-control"></div>
            </div>
            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label class="lis-form-label">Pelaksana</label>
                    <select name="pelaksana" id="p-pelaksana" class="lis-form-control"><option value="internal">Internal</option><option value="vendor">Vendor</option></select>
                </div>
                <div><label class="lis-form-label">Nama Pelaksana</label><input type="text" name="nama_pelaksana" id="p-nama" class="lis-form-control"></div>
            </div>
            <div><label class="lis-form-label">Uraian Pekerjaan</label><textarea name="uraian_pekerjaan" id="p-uraian" rows="2" class="lis-form-control"></textarea></div>
            <div class="grid grid-cols-2 gap-2.5">
                <div><label class="lis-form-label">Suku Cadang</label><input type="text" name="suku_cadang" id="p-suku" class="lis-form-control"></div>
                <div><label class="lis-form-label">Biaya</label><input type="number" step="0.01" min="0" name="biaya" id="p-biaya" class="lis-form-control font-mono" value="0"></div>
            </div>
            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label class="lis-form-label">Hasil</label>
                    <select name="hasil" id="p-hasil" class="lis-form-control">
                        <option value="">-- Belum ada --</option><option value="normal">Normal</option>
                        <option value="perlu_tindak_lanjut">Perlu Tindak Lanjut</option><option value="gagal">Gagal</option>
                    </select>
                </div>
                <div>
                    <label class="lis-form-label">Status <span class="req">*</span></label>
                    <select name="status" id="p-status" required class="lis-form-control">
                        <option value="terjadwal">Terjadwal</option><option value="selesai">Selesai</option><option value="dibatalkan">Dibatalkan</option>
                    </select>
                    <p class="lis-form-hint">Status selesai wajib mengisi tanggal realisasi.</p>
                </div>
            </div>
            <div><label class="lis-form-label">Catatan</label><textarea name="catatan" id="p-catatan" rows="2" class="lis-form-control"></textarea></div>
            <div class="pt-3 border-t border-slate-200 flex items-center justify-between">
                <button type="button" onclick="closePel()" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    const G_PS = { terjadwal:['bg-amber-50 text-amber-800 border-amber-200','Terjadwal'], selesai:['bg-emerald-50 text-emerald-700 border-emerald-200','Selesai'], dibatalkan:['bg-slate-100 text-slate-500 border-slate-200','Dibatalkan'] };
    const rp = n => 'Rp ' + Number(n||0).toLocaleString('id-ID');
    let tPel = null;

    $(function () {
        tPel = $('#t-pel').DataTable({
            processing:true, serverSide:true, order:[],
            ajax:{ url:'<?= site_url('aset/fetch_pemeliharaan') ?>', type:'GET', data: d => {
                d.status = $('#f-status').val() || 'all'; d.jenis = $('#f-jenis').val() || 'all';
            }},
            columns: [
                { data:null, orderable:false, className:'text-center text-slate-400 py-3', render:(d,t,r,m)=>m.row+m.settings._iDisplayStart+1 },
                { data:'nama_aset', orderable:false, className:'py-3', render:(d,t,r)=>
                    '<strong class="text-slate-900 block">'+d+'</strong><span class="text-[10px] text-slate-400 font-mono">'+r.nomor_register+'</span>'+
                    (r.lokasi_ruangan?'<span class="text-[10px] text-slate-500 block">'+r.lokasi_ruangan+'</span>':'') },
                { data:'jenis', orderable:false, className:'text-center py-3', render:d=>'<span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">'+d+'</span>' },
                { data:'tanggal_rencana', orderable:false, className:'py-3 text-slate-600', render:(d,t,r)=>
                    'Rencana: '+(d||'-')+'<span class="block text-[10px] text-slate-400">Realisasi: '+(r.tanggal_realisasi||'-')+'</span>' },
                { data:'nama_pelaksana', orderable:false, className:'py-3 text-slate-600', render:(d,t,r)=>
                    (d||'-')+'<span class="block text-[10px] text-slate-400">'+r.pelaksana+'</span>' },
                { data:'biaya', orderable:false, className:'text-right py-3 text-slate-700', render:d=>rp(d) },
                { data:'status', orderable:false, className:'text-center py-3', render:(d,t,r)=>{
                    const g = G_PS[d] || G_PS.terjadwal;
                    return '<span class="px-2 py-0.5 rounded text-[10px] font-bold border '+g[0]+'">'+g[1]+'</span>'+
                           (r.hasil?'<div class="text-[10px] text-slate-500 mt-0.5">'+r.hasil.replace(/_/g,' ')+'</div>':''); }}
            ],
            drawCallback: () => { if (window.lucide) lucide.createIcons(); },
            language:{ processing:'Memuat...', emptyTable:'Belum ada catatan pemeliharaan.', zeroRecords:'Tidak ada yang cocok.',
                info:'Menampilkan _START_ - _END_ dari _TOTAL_ catatan', infoEmpty:'Tidak ada data', lengthMenu:'Tampil _MENU_', search:'Cari:',
                paginate:{first:'Awal',last:'Akhir',next:'Lanjut',previous:'Kembali'} }
        });
        $('#f-status,#f-jenis').on('change', () => tPel.ajax.reload());
    });

    function openPel(asetId) {
        document.getElementById('f-pel').reset();
        if (asetId) document.getElementById('p-aset').value = asetId;
        const m=document.getElementById('m-pel'); m.classList.remove('hidden'); m.classList.add('flex'); m.style.display='flex';
        if (window.lucide) lucide.createIcons();
    }
    function closePel(){ const m=document.getElementById('m-pel'); m.classList.remove('flex'); m.classList.add('hidden'); m.style.display='none'; }

    function simpanPel(e) {
        e.preventDefault();
        LIS.fetchJson('<?= site_url('aset/simpan_pemeliharaan') ?>', { method:'POST', body:new FormData(document.getElementById('f-pel')) })
            .then(res => { closePel(); LIS.toast('success', res.message); tPel.ajax.reload(null,false); })
            .catch(err => LIS.showError('Gagal Menyimpan', err, 'Catatan pemeliharaan tidak dapat disimpan.'));
    }
</script>
