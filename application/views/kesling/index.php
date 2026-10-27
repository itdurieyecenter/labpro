<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700">Dashboard</a><span>/</span>
                <span class="text-emerald-800 font-semibold">Kesehatan Lingkungan</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="droplets" class="w-6 h-6"></i>
                </span>
                <span>Order Pengujian Lingkungan</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Pengujian sampel lingkungan &mdash; FPPS, titik sampling berkoordinat, dan baku mutu multi-standar</p>
        </div>
        <?php if ($can_create): ?>
            <button type="button" onclick="openOrderModal()" class="px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-2 border border-emerald-600">
                <i data-lucide="plus" class="w-4 h-4"></i><span>+ Order Baru</span>
            </button>
        <?php endif; ?>
    </div>

    <!-- KPI -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <?php
        $kpi = [
            ['Total Order', $stats['total'], 'clipboard-list', 'emerald'],
            ['Terdaftar', $stats['registered'], 'file-plus', 'sky'],
            ['Dalam Proses', $stats['testing'], 'flask-conical', 'amber'],
            ['Selesai', $stats['completed'], 'check-circle', 'teal'],
        ];
        foreach ($kpi as $k): ?>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider"><?= $k[0] ?></span>
                <i data-lucide="<?= $k[2] ?>" class="w-4 h-4 text-<?= $k[3] ?>-600"></i>
            </div>
            <div class="text-2xl font-black text-slate-900 mt-1"><?= (int)$k[1] ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="list" class="w-4 h-4 text-emerald-700"></i> Daftar Order
            </h2>
            <div class="flex items-center gap-2 flex-wrap">
                <select id="f-status" class="lis-form-control py-1.5 text-xs w-auto">
                    <option value="all">Semua Status</option>
                    <option value="registered">Terdaftar</option>
                    <option value="sampling">Sampling</option>
                    <option value="testing">Pengujian</option>
                    <option value="validasi">Validasi</option>
                    <option value="completed">Selesai</option>
                    <option value="cancelled">Batal</option>
                </select>
                <a href="<?= site_url('kesling/baku_mutu') ?>" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center gap-1.5">
                    <i data-lucide="ruler" class="w-3.5 h-3.5"></i> Baku Mutu
                </a>
            </div>
        </div>
        <div class="p-4 sm:p-5 overflow-x-auto">
            <table id="t-order" class="w-full text-left text-xs border-collapse">
                <thead><tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-bold bg-slate-50">
                    <th class="py-3 px-3 w-12 text-center">No</th>
                    <th class="py-3 px-3">No. LHU / FPPS &amp; Customer</th>
                    <th class="py-3 px-3">Tipe &amp; Standar</th>
                    <th class="py-3 px-3">Sampel &amp; Lokasi</th>
                    <th class="py-3 px-3 text-center">Status</th>
                    <th class="py-3 px-3 text-center w-36">Aksi</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100 font-medium"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Order -->
<div id="m-order" class="lis-modal">
    <div class="lis-modal-box max-w-3xl w-full">
        <div class="lis-modal-header bg-gradient-to-r from-emerald-900 to-teal-900 border-b border-amber-400/40">
            <h3 class="lis-modal-title text-white flex items-center gap-2">
                <i data-lucide="droplets" class="w-5 h-5 text-amber-300"></i><span id="m-order-title">Order Baru</span>
            </h3>
            <button type="button" onclick="closeOrderModal()" class="lis-modal-close"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="f-order" onsubmit="saveOrder(event)" class="lis-modal-body p-5 space-y-3">
            <input type="hidden" id="o-id" name="id">

            <div class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider border-b border-slate-200 pb-1">Penomoran &amp; Standar</div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="lis-form-label">No. FPPS</label>
                    <input type="text" name="fpps_number" id="o-fpps" class="lis-form-control font-mono text-xs" placeholder="Otomatis">
                    <p class="lis-form-hint" id="o-nomor-hint">Nomor LHU dibuat otomatis saat simpan</p>
                </div>
                <div>
                    <label class="lis-form-label">Tipe Sampel <span class="req">*</span></label>
                    <select name="sample_type_id" id="o-tipe" required class="lis-form-control">
                        <?php foreach ($sample_types as $t): ?>
                            <option value="<?= (int)$t['id'] ?>"><?= html_escape($t['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="lis-form-label">Baku Mutu Acuan</label>
                    <select name="standard_id" id="o-standar" class="lis-form-control">
                        <option value="">-- Pilih Standar --</option>
                        <?php foreach ($standards as $s): ?>
                            <option value="<?= (int)$s['id'] ?>" data-tipe="<?= (int)$s['sample_type_id'] ?>"><?= html_escape($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider border-b border-slate-200 pb-1 pt-2">Customer / Pelanggan</div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div><label class="lis-form-label">Nama Customer <span class="req">*</span></label><input type="text" name="customer_name" id="o-customer" required class="lis-form-control font-bold" placeholder="Nama instansi / perusahaan"></div>
                <div><label class="lis-form-label">Personel yang Dihubungi</label><input type="text" name="contact_person" id="o-contact" class="lis-form-control"></div>
                <div><label class="lis-form-label">Alamat</label><input type="text" name="customer_address" id="o-address" class="lis-form-control"></div>
                <div><label class="lis-form-label">Telepon / Fax</label><input type="text" name="contact_phone" id="o-phone" class="lis-form-control"></div>
            </div>

            <div class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider border-b border-slate-200 pb-1 pt-2">Sampel &amp; Titik Sampling</div>
            <div><label class="lis-form-label">Deskripsi Sampel</label><input type="text" name="sample_description" id="o-desc" class="lis-form-control" placeholder="Contoh: Air Minum Merk DAMIU ..."></div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div><label class="lis-form-label">Lokasi / Titik Sampling</label><input type="text" name="sample_location" id="o-loc" class="lis-form-control"></div>
                <div><label class="lis-form-label">Latitude</label><input type="text" name="latitude" id="o-lat" class="lis-form-control font-mono" placeholder="0.5071"></div>
                <div><label class="lis-form-label">Longitude</label><input type="text" name="longitude" id="o-lng" class="lis-form-control font-mono" placeholder="101.4478"></div>
            </div>

            <div class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider border-b border-slate-200 pb-1 pt-2">Pengambilan &amp; Penerimaan Sampel</div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div><label class="lis-form-label">Personel Pengambil Sampel</label><input type="text" name="sampling_by" id="o-sby" class="lis-form-control"></div>
                <div><label class="lis-form-label">Tgl/Jam Pengambilan</label><input type="datetime-local" name="sampling_at" id="o-sat" class="lis-form-control"></div>
                <div><label class="lis-form-label">Personel Penerima Sampel</label><input type="text" name="received_by" id="o-rby" class="lis-form-control"></div>
                <div><label class="lis-form-label">Tgl/Jam Penerimaan</label><input type="datetime-local" name="received_at" id="o-rat" class="lis-form-control"></div>
            </div>

            <div class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider border-b border-slate-200 pb-1 pt-2">Pengujian</div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div><label class="lis-form-label">Tgl Mulai Uji</label><input type="date" name="analysis_start" id="o-as" class="lis-form-control"></div>
                <div><label class="lis-form-label">Tgl Selesai Uji</label><input type="date" name="analysis_end" id="o-ae" class="lis-form-control"></div>
                <div>
                    <label class="lis-form-label">Status</label>
                    <select name="status" id="o-status" class="lis-form-control">
                        <option value="registered">Terdaftar</option>
                        <option value="sampling">Sampling</option>
                        <option value="testing">Pengujian</option>
                        <option value="validasi">Validasi</option>
                        <option value="completed">Selesai</option>
                        <option value="cancelled">Batal</option>
                    </select>
                </div>
            </div>
            <div><label class="lis-form-label">Catatan</label><textarea name="notes" id="o-notes" rows="2" class="lis-form-control"></textarea></div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeOrderModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-1.5">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Order
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const AU = {
        fetch:  '<?= site_url('kesling/fetch') ?>',
        get:    '<?= site_url('kesling/get') ?>',
        save:   '<?= site_url('kesling/save_order') ?>',
        hapus:  '<?= site_url('kesling/delete_order') ?>',
        hasil:  '<?= site_url('kesling/hasil') ?>',
        cetak:  '<?= site_url('kesling/cetak_lhu') ?>'
    };
    const G_ST = {
        registered:['bg-sky-50 text-sky-700 border-sky-200','Terdaftar'],
        sampling:['bg-indigo-50 text-indigo-700 border-indigo-200','Sampling'],
        testing:['bg-amber-50 text-amber-800 border-amber-300','Pengujian'],
        validasi:['bg-purple-50 text-purple-700 border-purple-200','Validasi'],
        completed:['bg-emerald-50 text-emerald-700 border-emerald-200','Selesai'],
        cancelled:['bg-rose-50 text-rose-700 border-rose-200','Batal']
    };

    let tOrder = null;
    $(function () {
        tOrder = $('#t-order').DataTable({
            processing: true, serverSide: true, order: [],
            ajax: { url: AU.fetch, type: 'GET', data: d => { d.status = $('#f-status').val() || 'all'; } },
            columns: [
                { data: null, orderable:false, className:'text-center text-slate-400 py-3', render:(d,t,r,m)=>m.row+m.settings._iDisplayStart+1 },
                { data: 'order_number', orderable:false, className:'py-3', render:(d,t,r)=>
                    '<strong class="text-slate-900 block font-mono text-[11px]">' + (d||'-') + '</strong>' +
                    (r.fpps_number ? '<span class="text-[10px] text-slate-400 font-mono block">FPPS: ' + r.fpps_number + '</span>' : '') +
                    '<span class="text-[11px] text-slate-600 block font-semibold">' + (r.customer_name||'-') + '</span>'
                },
                { data: 'sample_type_name', orderable:false, className:'py-3 text-slate-600', render:(d,t,r)=>
                    (d||'-') + (r.standard_code ? '<span class="text-[10px] text-emerald-700 block font-semibold">' + r.standard_code + '</span>' : '<span class="text-[10px] text-rose-500 block">standar belum dipilih</span>')
                },
                { data: 'sample_description', orderable:false, className:'py-3 text-slate-600', render:(d,t,r)=>
                    (d||'-') +
                    (r.sample_location ? '<span class="text-[10px] text-slate-500 block">📍 ' + r.sample_location + '</span>' : '') +
                    (r.latitude && r.longitude ? '<span class="text-[10px] text-slate-400 font-mono block">' + r.latitude + ', ' + r.longitude + '</span>' : '')
                },
                { data: 'status', orderable:false, className:'text-center py-3', render:d=>{
                    const g = G_ST[d] || ['bg-slate-100 text-slate-500 border-slate-200', d];
                    return '<span class="px-2 py-0.5 rounded text-[10px] font-bold border ' + g[0] + '">' + g[1] + '</span>';
                }},
                { data: 'id', orderable:false, className:'text-center py-3', render:d=>{
                    let b = '<div class="flex items-center justify-center gap-1.5">';
                    b += '<a href="' + AU.hasil + '/' + d + '" class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700" title="Entri Hasil"><i data-lucide="flask-conical" class="w-3.5 h-3.5"></i></a>';
                    b += '<a href="' + AU.cetak + '/' + d + '" target="_blank" class="p-1.5 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700" title="Cetak LHU"><i data-lucide="printer" class="w-3.5 h-3.5"></i></a>';
                    <?php if ($can_edit): ?>b += '<button type="button" onclick="editOrder(' + d + ')" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700" title="Ubah"><i data-lucide="edit" class="w-3.5 h-3.5"></i></button>';<?php endif; ?>
                    <?php if ($can_delete): ?>b += '<button type="button" onclick="hapusOrder(' + d + ')" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700" title="Hapus"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>';<?php endif; ?>
                    return b + '</div>';
                }}
            ],
            drawCallback: () => { if (window.lucide) lucide.createIcons(); },
            language: { processing:'Memuat...', emptyTable:'Belum ada order lingkungan.', zeroRecords:'Tidak ada order yang cocok.',
                info:'Menampilkan _START_ - _END_ dari _TOTAL_ order', infoEmpty:'Tidak ada data', lengthMenu:'Tampil _MENU_', search:'Cari:',
                paginate:{first:'Awal',last:'Akhir',next:'Lanjut',previous:'Kembali'} }
        });
        $('#f-status').on('change', () => tOrder.ajax.reload());
        filterStandar();
        document.getElementById('o-tipe').addEventListener('change', filterStandar);
    });

    function showModal(id){ const m=document.getElementById(id); m.classList.remove('hidden'); m.classList.add('flex'); m.style.display='flex'; if(window.lucide) lucide.createIcons(); }
    function hideModal(id){ const m=document.getElementById(id); m.classList.remove('flex'); m.classList.add('hidden'); m.style.display='none'; }
    function closeOrderModal(){ hideModal('m-order'); }

    // Tampilkan hanya standar yang sesuai tipe sampel terpilih
    function filterStandar() {
        const tipe = document.getElementById('o-tipe').value;
        const sel = document.getElementById('o-standar');
        let firstVisible = '';
        Array.from(sel.options).forEach(o => {
            if (!o.value) return;
            const show = o.dataset.tipe === tipe;
            o.hidden = !show;
            if (show && !firstVisible) firstVisible = o.value;
        });
        if (sel.selectedOptions[0] && sel.selectedOptions[0].hidden) sel.value = '';
    }

    function openOrderModal() {
        document.getElementById('f-order').reset();
        document.getElementById('o-id').value = '';
        document.getElementById('o-status').value = 'registered';
        document.getElementById('m-order-title').textContent = 'Order Baru';
        filterStandar();
        showModal('m-order');
    }

    function editOrder(id) {
        LIS.fetchJson(AU.get + '/' + id).then(res => {
            const d = res.data;
            const set = (k,v) => { const el=document.getElementById(k); if(el) el.value = v ?? ''; };
            set('o-id',d.id); set('o-fpps',d.fpps_number); set('o-tipe',d.sample_type_id);
            filterStandar();
            set('o-standar',d.standard_id);
            set('o-customer',d.customer_name); set('o-contact',d.contact_person); set('o-address',d.customer_address); set('o-phone',d.contact_phone);
            set('o-desc',d.sample_description); set('o-loc',d.sample_location); set('o-lat',d.latitude); set('o-lng',d.longitude);
            set('o-sby',d.sampling_by); set('o-sat', d.sampling_at ? d.sampling_at.replace(' ','T').slice(0,16) : '');
            set('o-rby',d.received_by); set('o-rat', d.received_at ? d.received_at.replace(' ','T').slice(0,16) : '');
            set('o-as',d.analysis_start); set('o-ae',d.analysis_end); set('o-status',d.status); set('o-notes',d.notes);
            document.getElementById('o-nomor-hint').textContent = 'No. LHU: ' + (d.order_number || '-');
            document.getElementById('m-order-title').textContent = 'Ubah ' + (d.order_number || 'Order');
            showModal('m-order');
        }).catch(err => LIS.showError('Gagal Memuat Order', err, 'Data order tidak dapat dimuat.'));
    }

    function saveOrder(e) {
        e.preventDefault();
        const body = new FormData(document.getElementById('f-order'));
        LIS.fetchJson(AU.save, { method:'POST', body: body })
            .then(res => { closeOrderModal(); LIS.toast('success', res.message); tOrder.ajax.reload(null,false); })
            .catch(err => LIS.showError('Gagal Menyimpan Order', err, 'Data order tidak dapat disimpan.'));
    }

    function hapusOrder(id) {
        Swal.fire({ title:'Hapus Order?', text:'Order dan seluruh hasil ujinya akan dihapus permanen.',
            icon:'warning', showCancelButton:true, confirmButtonColor:'#e11d48', confirmButtonText:'Ya, Hapus', cancelButtonText:'Batal'
        }).then(r => { if(!r.isConfirmed) return;
            LIS.fetchJson(AU.hapus + '/' + id, { method:'POST' })
                .then(res => { LIS.toast('success', res.message); tOrder.ajax.reload(null,false); })
                .catch(err => LIS.showError('Gagal Menghapus', err, 'Order tidak dapat dihapus.'));
        });
    }
</script>
