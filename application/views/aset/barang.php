<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700">Dashboard</a><span>/</span>
                <a href="<?= site_url('aset') ?>" class="hover:text-emerald-700">Aset</a><span>/</span>
                <span class="text-emerald-800 font-semibold">Daftar Aset</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="clipboard-list" class="w-6 h-6"></i>
                </span>
                <span>Kartu Inventaris Barang B</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Peralatan dan Mesin &mdash; kodefikasi BMD sesuai Permendagri 108/2016, penyusutan garis lurus</p>
        </div>
        <?php if ($can_create): ?>
            <button type="button" onclick="openAsetModal()" class="px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-2 border border-emerald-600">
                <i data-lucide="plus" class="w-4 h-4"></i><span>+ Tambah Aset</span>
            </button>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="list" class="w-4 h-4 text-emerald-700"></i> Daftar Aset
            </h2>
            <div class="flex items-center gap-2 flex-wrap">
                <select id="f-golongan" class="lis-form-control py-1.5 text-xs w-auto">
                    <option value="all">Semua Golongan</option>
                    <?php foreach ($golongan_ref as $g): ?><option value="<?= html_escape($g['nama']) ?>"><?= html_escape($g['nama']) ?></option><?php endforeach; ?>
                </select>
                <select id="f-ruangan" class="lis-form-control py-1.5 text-xs w-auto">
                    <option value="all">Semua Ruangan</option>
                    <?php foreach ($ruangan_ref as $r): ?><option value="<?= html_escape($r['nama']) ?>"><?= html_escape($r['nama']) ?></option><?php endforeach; ?>
                </select>
                <select id="f-kondisi" class="lis-form-control py-1.5 text-xs w-auto">
                    <option value="all">Semua Kondisi</option>
                    <option value="baik">Baik</option><option value="rusak_ringan">Rusak Ringan</option><option value="rusak_berat">Rusak Berat</option>
                </select>
                <select id="f-kalibrasi" class="lis-form-control py-1.5 text-xs w-auto">
                    <option value="all">Semua Status Kalibrasi</option>
                    <option value="tidak_laik">Tidak Laik</option><option value="kedaluwarsa">Kedaluwarsa</option>
                    <option value="belum_pernah">Belum Dikalibrasi</option><option value="kritis">Kritis</option><option value="laik">Terkalibrasi</option>
                </select>
            </div>
        </div>
        <div class="p-4 sm:p-5 overflow-x-auto">
            <table id="t-aset" class="w-full text-left text-xs border-collapse">
                <thead><tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-bold bg-slate-50">
                    <th class="py-3 px-3 w-12 text-center">No</th>
                    <th class="py-3 px-3">Nomor Register / Nama Aset</th>
                    <th class="py-3 px-3">Penempatan</th>
                    <th class="py-3 px-3 text-center">Kondisi</th>
                    <th class="py-3 px-3">Kalibrasi</th>
                    <th class="py-3 px-3 text-right">Nilai Buku</th>
                    <th class="py-3 px-3 text-center w-32">Aksi</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100 font-medium"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Aset -->
<div id="m-aset" class="lis-modal">
    <div class="lis-modal-box max-w-3xl w-full">
        <div class="lis-modal-header bg-gradient-to-r from-emerald-900 to-teal-900 border-b border-amber-400/40">
            <h3 class="lis-modal-title text-white flex items-center gap-2">
                <i data-lucide="clipboard-list" class="w-5 h-5 text-amber-300"></i><span id="m-aset-title">Tambah Aset</span>
            </h3>
            <button type="button" onclick="closeAsetModal()" class="lis-modal-close"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="f-aset" onsubmit="saveAset(event)" class="lis-modal-body p-5 space-y-3">
            <input type="hidden" id="a-id" name="id">

            <div class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider border-b border-slate-200 pb-1">Identitas BMD</div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="lis-form-label">Kode BMD <span class="req">*</span></label>
                    <input type="text" name="kode_bmd" id="a-kode" required class="lis-form-control font-mono" placeholder="02.09.01.01.001" onchange="ambilNup()">
                    <p class="lis-form-hint">Kodefikasi Permendagri 108/2016</p>
                </div>
                <div>
                    <label class="lis-form-label">NUP</label>
                    <input type="number" name="nup" id="a-nup" min="1" class="lis-form-control font-bold text-center" placeholder="Otomatis">
                    <p class="lis-form-hint" id="a-reg">&nbsp;</p>
                </div>
                <div>
                    <label class="lis-form-label">Golongan BMD <span class="req">*</span></label>
                    <select name="golongan" id="a-golongan" required class="lis-form-control" onchange="isiMasaManfaat()">
                        <?php foreach ($golongan_ref as $g): ?>
                            <option value="<?= html_escape($g['nama']) ?>" data-kode="<?= html_escape($g['kode']) ?>" data-manfaat="<?= html_escape($g['nilai_bantu']) ?>">
                                <?= html_escape($g['kode']) ?> &mdash; <?= html_escape($g['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider border-b border-slate-200 pb-1 pt-2">Identitas Fisik</div>
            <div>
                <label class="lis-form-label">Nama Aset <span class="req">*</span></label>
                <input type="text" name="nama_aset" id="a-nama" required class="lis-form-control font-bold" placeholder="Contoh: Hematology Analyzer 5-Diff">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div><label class="lis-form-label">Merk / Tipe</label><input type="text" name="merk_tipe" id="a-merk" class="lis-form-control" placeholder="Sysmex XN-550"></div>
                <div><label class="lis-form-label">Nomor Seri</label><input type="text" name="nomor_seri" id="a-seri" class="lis-form-control font-mono" placeholder="Serial pabrikan"></div>
                <div><label class="lis-form-label">Bahan</label><input type="text" name="bahan" id="a-bahan" class="lis-form-control" placeholder="Logam / Plastik"></div>
            </div>
            <div><label class="lis-form-label">Ukuran / Spesifikasi</label><input type="text" name="ukuran_spesifikasi" id="a-spek" class="lis-form-control"></div>

            <div class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider border-b border-slate-200 pb-1 pt-2">Perolehan &amp; Penyusutan</div>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div><label class="lis-form-label">Tanggal Perolehan</label><input type="date" name="tanggal_perolehan" id="a-tgl" class="lis-form-control"></div>
                <div>
                    <label class="lis-form-label">Sumber Dana</label>
                    <select name="cara_perolehan" id="a-dana" class="lis-form-control">
                        <option value="">-- Pilih --</option>
                        <?php foreach ($sumber_dana_ref as $s): ?><option value="<?= html_escape($s['nama']) ?>"><?= html_escape($s['nama']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div><label class="lis-form-label">Nilai Perolehan</label><input type="number" step="0.01" min="0" name="nilai_perolehan" id="a-nilai" class="lis-form-control font-mono" value="0"></div>
                <div><label class="lis-form-label">Masa Manfaat (tahun)</label><input type="number" min="1" name="masa_manfaat_tahun" id="a-manfaat" class="lis-form-control font-bold text-center" value="8"></div>
            </div>
            <div><label class="lis-form-label">Nomor Dokumen Perolehan</label><input type="text" name="nomor_dokumen_perolehan" id="a-dok" class="lis-form-control font-mono" placeholder="SPK / kontrak / BAST"></div>

            <div class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider border-b border-slate-200 pb-1 pt-2">Penempatan &amp; Kondisi</div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="lis-form-label">Lokasi Ruangan</label>
                    <select name="lokasi_ruangan" id="a-ruangan" class="lis-form-control">
                        <option value="">-- Pilih Ruangan --</option>
                        <?php foreach ($ruangan_ref as $r): ?><option value="<?= html_escape($r['nama']) ?>"><?= html_escape($r['nama']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="lis-form-label">Penanggung Jawab</label>
                    <select name="penanggung_jawab_id" id="a-pj" class="lis-form-control">
                        <option value="">-- Pilih Pegawai --</option>
                        <?php foreach ($pegawai_options as $p): ?><option value="<?= (int)$p['id'] ?>"><?= html_escape($p['nama_formatted']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="lis-form-label">Kondisi <span class="req">*</span></label>
                    <select name="kondisi" id="a-kondisi" required class="lis-form-control">
                        <option value="baik">Baik (B)</option><option value="rusak_ringan">Rusak Ringan (RR)</option><option value="rusak_berat">Rusak Berat (RB)</option>
                    </select>
                </div>
            </div>

            <div class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider border-b border-slate-200 pb-1 pt-2">Kalibrasi &amp; Pemeliharaan</div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="space-y-2">
                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer">
                        <input type="checkbox" name="wajib_kalibrasi" id="a-wkal" value="1" class="rounded border-slate-300 text-emerald-700">
                        <span class="text-xs font-semibold text-slate-700">Instrumen ukur, wajib kalibrasi</span>
                    </label>
                    <div><label class="lis-form-label">Interval Kalibrasi (bulan)</label><input type="number" min="1" name="interval_kalibrasi_bulan" id="a-ikal" class="lis-form-control text-center" value="12"></div>
                </div>
                <div class="space-y-2">
                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer">
                        <input type="checkbox" name="wajib_pemeliharaan" id="a-wpel" value="1" checked class="rounded border-slate-300 text-emerald-700">
                        <span class="text-xs font-semibold text-slate-700">Dijadwalkan pemeliharaan berkala</span>
                    </label>
                    <div><label class="lis-form-label">Interval Pemeliharaan (bulan)</label><input type="number" min="1" name="interval_pemeliharaan_bulan" id="a-ipel" class="lis-form-control text-center" value="6"></div>
                </div>
            </div>

            <div><label class="lis-form-label">Keterangan</label><textarea name="keterangan" id="a-ket" rows="2" class="lis-form-control"></textarea></div>

            <div class="pt-3 border-t border-slate-200 flex items-center justify-between">
                <button type="button" onclick="closeAsetModal()" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold">Simpan Aset</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Detail -->
<div id="m-detail" class="lis-modal">
    <div class="lis-modal-box max-w-2xl w-full">
        <div class="lis-modal-header bg-gradient-to-r from-slate-800 to-slate-900 border-b border-amber-400/40">
            <h3 class="lis-modal-title text-white flex items-center gap-2"><i data-lucide="file-search" class="w-5 h-5 text-amber-300"></i><span>Detail Aset</span></h3>
            <button type="button" onclick="closeDetail()" class="lis-modal-close"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <div id="detail-body" class="lis-modal-body p-5 space-y-3"></div>
    </div>
</div>

<script>
    const AU = {
        fetch: '<?= site_url('aset/fetch') ?>', get: '<?= site_url('aset/get') ?>',
        save: '<?= site_url('aset/save') ?>', hapus: '<?= site_url('aset/hapus') ?>',
        nup: '<?= site_url('aset/next_nup') ?>', kib: '<?= site_url('aset/cetak_kib') ?>'
    };
    const G_KAL = {
        tidak_laik:['bg-rose-50 text-rose-700 border-rose-200','Tidak Laik'],
        kedaluwarsa:['bg-rose-50 text-rose-700 border-rose-200','Kedaluwarsa'],
        belum_pernah:['bg-rose-50 text-rose-700 border-rose-200','Belum Dikalibrasi'],
        kritis:['bg-amber-50 text-amber-800 border-amber-300','Kritis'],
        peringatan:['bg-yellow-50 text-yellow-800 border-yellow-200','Perlu Dijadwalkan'],
        laik:['bg-emerald-50 text-emerald-700 border-emerald-200','Terkalibrasi'],
        tidak_wajib:['bg-slate-100 text-slate-500 border-slate-200','Tidak Wajib']
    };
    const G_KON = { baik:['bg-emerald-50 text-emerald-700 border-emerald-200','Baik'], rusak_ringan:['bg-amber-50 text-amber-800 border-amber-300','Rusak Ringan'], rusak_berat:['bg-rose-50 text-rose-700 border-rose-200','Rusak Berat'] };
    const G_STAT = { aktif:'Aktif', dipinjam:'Dipinjam', perbaikan:'Perbaikan', dihapuskan:'Dihapuskan' };

    let tAset = null;
    const rp = n => 'Rp ' + Number(n || 0).toLocaleString('id-ID');

    $(function () {
        tAset = $('#t-aset').DataTable({
            processing: true, serverSide: true, order: [],
            ajax: { url: AU.fetch, type: 'GET', data: d => {
                d.golongan = $('#f-golongan').val() || 'all';
                d.lokasi_ruangan = $('#f-ruangan').val() || 'all';
                d.kondisi = $('#f-kondisi').val() || 'all';
                d.status_kalibrasi = $('#f-kalibrasi').val() || 'all';
            }},
            columns: [
                { data: null, orderable: false, className: 'text-center text-slate-400 py-3', render: (d,t,r,m) => m.row + m.settings._iDisplayStart + 1 },
                { data: 'nama_aset', orderable: false, className: 'py-3', render: (d,t,r) =>
                    '<strong class="text-slate-900 block">' + d + '</strong>' +
                    '<span class="text-[10px] text-slate-400 font-mono">' + r.nomor_register + '</span>' +
                    (r.merk_tipe ? '<span class="text-[10px] text-slate-500 block">' + r.merk_tipe + '</span>' : '') +
                    (r.nomor_seri ? '<span class="text-[10px] text-slate-400 font-mono block">SN: ' + r.nomor_seri + '</span>' : '')
                },
                { data: 'lokasi_ruangan', orderable: false, className: 'py-3 text-slate-600', render: (d,t,r) =>
                    (d || '-') + (r.penanggung_jawab_nama ? '<span class="text-[10px] text-slate-400 block">PJ: ' + r.penanggung_jawab_nama + '</span>' : '') +
                    '<span class="text-[10px] text-slate-500 block">' + (G_STAT[r.status] || r.status) + '</span>'
                },
                { data: 'kondisi', orderable: false, className: 'text-center py-3', render: d => {
                    const g = G_KON[d] || G_KON.baik;
                    return '<span class="px-2 py-0.5 rounded text-[10px] font-bold border ' + g[0] + '">' + g[1] + '</span>';
                }},
                { data: 'kalibrasi', orderable: false, className: 'py-3', render: (d,t,r) => {
                    const g = G_KAL[d.status] || G_KAL.tidak_wajib;
                    let h = '<span class="px-2 py-0.5 rounded text-[10px] font-bold border ' + g[0] + '">' + g[1] + '</span>';
                    if (d.berlaku_sampai) h += '<div class="text-[10px] text-slate-500 mt-0.5">s/d ' + d.berlaku_sampai +
                        (d.sisa_hari !== null ? ' &bull; ' + (d.sisa_hari < 0 ? 'lewat ' + Math.abs(d.sisa_hari) + ' hari' : 'sisa ' + d.sisa_hari + ' hari') : '') + '</div>';
                    if (!r.laik_pakai) h += '<div class="text-[10px] font-bold text-rose-700 mt-0.5">⊘ Dilarang dipakai menguji</div>';
                    return h;
                }},
                { data: 'penyusutan', orderable: false, className: 'text-right py-3', render: (d,t,r) =>
                    '<strong class="text-slate-900">' + rp(d.nilai_buku) + '</strong>' +
                    '<div class="text-[10px] text-slate-400">dari ' + rp(r.nilai_perolehan) + '</div>' +
                    '<div class="text-[10px] text-amber-700">susut ' + d.persen_susut + '% &bull; umur ' + d.umur_tahun + '/' + r.masa_manfaat_tahun + ' th</div>'
                },
                { data: 'id', orderable: false, className: 'text-center py-3', render: d => {
                    let b = '<div class="flex items-center justify-center gap-1.5">';
                    b += '<button type="button" onclick="lihatAset(' + d + ')" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700" title="Detail"><i data-lucide="eye" class="w-3.5 h-3.5"></i></button>';
                    b += '<a href="' + AU.kib + '/' + d + '" target="_blank" class="p-1.5 rounded-lg bg-sky-50 hover:bg-sky-100 text-sky-700" title="Cetak KIB-B"><i data-lucide="printer" class="w-3.5 h-3.5"></i></a>';
                    <?php if ($can_edit): ?>b += '<button type="button" onclick="editAset(' + d + ')" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700" title="Ubah"><i data-lucide="edit" class="w-3.5 h-3.5"></i></button>';<?php endif; ?>
                    <?php if ($can_delete): ?>b += '<button type="button" onclick="hapusAset(' + d + ')" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700" title="Hapus"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>';<?php endif; ?>
                    return b + '</div>';
                }}
            ],
            drawCallback: () => { if (window.lucide) lucide.createIcons(); },
            language: { processing:'Memuat...', emptyTable:'Belum ada aset terdaftar.', zeroRecords:'Tidak ada aset yang cocok.',
                info:'Menampilkan _START_ - _END_ dari _TOTAL_ aset', infoEmpty:'Tidak ada data', lengthMenu:'Tampil _MENU_', search:'Cari:',
                paginate:{first:'Awal',last:'Akhir',next:'Lanjut',previous:'Kembali'} }
        });
        $('#f-golongan,#f-ruangan,#f-kondisi,#f-kalibrasi').on('change', () => tAset.ajax.reload());
    });

    function showModal(id){ const m=document.getElementById(id); m.classList.remove('hidden'); m.classList.add('flex'); m.style.display='flex'; if(window.lucide) lucide.createIcons(); }
    function hideModal(id){ const m=document.getElementById(id); m.classList.remove('flex'); m.classList.add('hidden'); m.style.display='none'; }
    function closeAsetModal(){ hideModal('m-aset'); }
    function closeDetail(){ hideModal('m-detail'); }

    function isiMasaManfaat() {
        const o = document.getElementById('a-golongan').selectedOptions[0];
        if (o && o.dataset.manfaat && !document.getElementById('a-id').value) document.getElementById('a-manfaat').value = o.dataset.manfaat;
    }

    function ambilNup() {
        const kode = document.getElementById('a-kode').value.trim();
        if (!kode || document.getElementById('a-id').value) return;
        LIS.fetchJson(AU.nup + '?kode_bmd=' + encodeURIComponent(kode))
            .then(res => {
                document.getElementById('a-nup').value = res.data.nup;
                document.getElementById('a-reg').textContent = 'Nomor register: ' + res.data.nomor_register;
            })
            .catch(() => { document.getElementById('a-reg').innerHTML = '&nbsp;'; });
    }

    function openAsetModal() {
        document.getElementById('f-aset').reset();
        document.getElementById('a-id').value = '';
        document.getElementById('a-reg').innerHTML = '&nbsp;';
        document.getElementById('a-wpel').checked = true;
        document.getElementById('m-aset-title').textContent = 'Tambah Aset';
        isiMasaManfaat();
        showModal('m-aset');
    }

    function editAset(id) {
        LIS.fetchJson(AU.get + '/' + id).then(res => {
            const d = res.data;
            const set = (k,v) => document.getElementById(k).value = v ?? '';
            set('a-id',d.id); set('a-kode',d.kode_bmd); set('a-nup',d.nup); set('a-golongan',d.golongan);
            set('a-nama',d.nama_aset); set('a-merk',d.merk_tipe); set('a-seri',d.nomor_seri);
            set('a-bahan',d.bahan); set('a-spek',d.ukuran_spesifikasi); set('a-tgl',d.tanggal_perolehan);
            set('a-dana',d.cara_perolehan); set('a-nilai',d.nilai_perolehan); set('a-manfaat',d.masa_manfaat_tahun);
            set('a-dok',d.nomor_dokumen_perolehan); set('a-ruangan',d.lokasi_ruangan); set('a-pj',d.penanggung_jawab_id);
            set('a-kondisi',d.kondisi); set('a-ikal',d.interval_kalibrasi_bulan); set('a-ipel',d.interval_pemeliharaan_bulan);
            set('a-ket',d.keterangan);
            document.getElementById('a-wkal').checked = String(d.wajib_kalibrasi) === '1';
            document.getElementById('a-wpel').checked = String(d.wajib_pemeliharaan) === '1';
            document.getElementById('a-reg').textContent = 'Nomor register: ' + d.nomor_register;
            document.getElementById('m-aset-title').textContent = 'Ubah ' + d.nama_aset;
            showModal('m-aset');
        }).catch(err => LIS.showError('Gagal Memuat Aset', err, 'Data aset tidak dapat dimuat.'));
    }

    function saveAset(e) {
        e.preventDefault();
        const body = new FormData(document.getElementById('f-aset'));
        if (!document.getElementById('a-wkal').checked) body.set('wajib_kalibrasi','0');
        if (!document.getElementById('a-wpel').checked) body.set('wajib_pemeliharaan','0');
        LIS.fetchJson(AU.save, { method:'POST', body: body })
            .then(res => { closeAsetModal(); LIS.toast('success', res.message); tAset.ajax.reload(null,false); })
            .catch(err => LIS.showError('Gagal Menyimpan Aset', err, 'Data aset tidak dapat disimpan.'));
    }

    function hapusAset(id) {
        Swal.fire({ title:'Hapus Aset?', html:'Aset hanya dapat dihapus bila belum memiliki riwayat apa pun.<br><span class="text-xs text-slate-500">Aset yang sudah bermutasi harus dihapuskan lewat menu Mutasi &amp; Penghapusan dengan dasar surat keputusan.</span>',
            icon:'warning', showCancelButton:true, confirmButtonColor:'#e11d48', confirmButtonText:'Ya, Hapus', cancelButtonText:'Batal'
        }).then(r => { if(!r.isConfirmed) return;
            LIS.fetchJson(AU.hapus + '/' + id, { method:'POST' })
                .then(res => { LIS.toast('success', res.message); tAset.ajax.reload(null,false); })
                .catch(err => LIS.showError('Gagal Menghapus Aset', err, 'Aset tidak dapat dihapus.'));
        });
    }

    function lihatAset(id) {
        LIS.fetchJson(AU.get + '/' + id).then(res => {
            const d = res.data, p = d.penyusutan, k = d.kalibrasi;
            const baris = (l,v) => '<div class="flex justify-between gap-3"><span class="text-slate-500">' + l + '</span><strong class="text-right">' + (v ?? '-') + '</strong></div>';
            let h = '<div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-1.5 text-xs">';
            h += baris('Nomor Register','<span class="font-mono">' + d.nomor_register + '</span>');
            h += baris('Nama Aset', d.nama_aset) + baris('Golongan BMD', d.golongan);
            h += baris('Merk / Tipe', d.merk_tipe) + baris('Nomor Seri','<span class="font-mono">' + (d.nomor_seri||'-') + '</span>');
            h += baris('Lokasi', d.lokasi_ruangan) + baris('Penanggung Jawab', d.penanggung_jawab_nama);
            h += baris('Kondisi', (G_KON[d.kondisi]||['','-'])[1]) + baris('Status', G_STAT[d.status]||d.status);
            h += '</div>';

            h += '<div class="bg-white border border-slate-200 rounded-xl p-4 space-y-1.5 text-xs">';
            h += '<div class="text-[10px] font-bold text-emerald-800 uppercase mb-1">Penyusutan Garis Lurus</div>';
            h += baris('Nilai Perolehan', rp(d.nilai_perolehan)) + baris('Masa Manfaat', d.masa_manfaat_tahun + ' tahun');
            h += baris('Penyusutan per Tahun', rp(p.per_tahun)) + baris('Umur', p.umur_tahun + ' tahun');
            h += baris('Akumulasi Penyusutan', rp(p.akumulasi)) + baris('Nilai Buku','<span class="text-emerald-800">' + rp(p.nilai_buku) + '</span>');
            h += '</div>';

            h += '<div><span class="text-[11px] font-bold text-slate-600 uppercase">Riwayat Kalibrasi</span><div class="mt-2 space-y-1.5">';
            if (!d.riwayat_kalibrasi || !d.riwayat_kalibrasi.length) h += '<p class="text-xs text-slate-400">Belum ada riwayat kalibrasi.</p>';
            else d.riwayat_kalibrasi.forEach(c => {
                const w = c.hasil === 'tidak_laik' ? 'rose' : (c.hasil === 'laik_dengan_catatan' ? 'amber' : 'emerald');
                h += '<div class="p-2.5 rounded-lg border border-' + w + '-200 bg-' + w + '-50 text-xs">';
                h += '<strong>' + c.tanggal_kalibrasi + '</strong> s/d ' + c.masa_berlaku_sampai + ' &bull; ' + c.hasil.replace(/_/g,' ');
                h += '<div class="text-[10px] text-slate-600">' + c.lembaga_kalibrasi + (c.nomor_sertifikat ? ' &bull; ' + c.nomor_sertifikat : '') + '</div></div>';
            });
            h += '</div></div>';

            h += '<div><span class="text-[11px] font-bold text-slate-600 uppercase">Rekam Jejak Mutasi</span><div class="mt-2 space-y-1.5">';
            if (!d.riwayat_mutasi || !d.riwayat_mutasi.length) h += '<p class="text-xs text-slate-400">Belum ada mutasi.</p>';
            else d.riwayat_mutasi.forEach(m => {
                h += '<div class="p-2.5 rounded-lg border border-slate-200 bg-slate-50 text-xs">';
                h += '<strong class="font-mono">' + m.nomor_transaksi + '</strong> &bull; ' + m.tanggal + ' &bull; ' + m.tipe.replace(/_/g,' ');
                if (m.dari_ruangan || m.ke_ruangan) h += '<div class="text-[10px] text-slate-600">' + (m.dari_ruangan||'-') + ' → ' + (m.ke_ruangan||'-') + '</div>';
                if (m.nomor_dokumen) h += '<div class="text-[10px] text-slate-500 font-mono">' + m.nomor_dokumen + '</div>';
                h += '</div>';
            });
            h += '</div></div>';

            document.getElementById('detail-body').innerHTML = h;
            showModal('m-detail');
        }).catch(err => LIS.showError('Gagal Memuat Detail', err, 'Detail aset tidak dapat dimuat.'));
    }
</script>
