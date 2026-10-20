<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="<?= site_url('gudang') ?>" class="hover:text-emerald-700 transition-colors">Gudang</a>
                <span>/</span>
                <span class="text-emerald-800 font-semibold">Master Barang</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="boxes" class="w-6 h-6"></i>
                </span>
                <span>Master Barang, Reagen &amp; BHP</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Stok dihitung otomatis dari sisa seluruh batch, tidak diinput manual</p>
        </div>

        <?php if ($can_create): ?>
            <button type="button" onclick="openBarangModal()" class="px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-2 shadow-sm border border-emerald-600">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>+ Tambah Barang</span>
            </button>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h2 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="list" class="w-4 h-4 text-emerald-700"></i>
                Daftar Barang
            </h2>
            <div class="flex items-center gap-2 flex-wrap">
                <select id="filter-kategori" class="lis-form-control py-1.5 text-xs w-auto">
                    <option value="all">Semua Kategori</option>
                    <?php foreach ($kategori_ref as $k): ?>
                        <option value="<?= html_escape($k['nama']) ?>"><?= html_escape($k['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="filter-stok" class="lis-form-control py-1.5 text-xs w-auto">
                    <option value="all">Semua Status Stok</option>
                    <option value="habis">Habis</option>
                    <option value="kritis">Kritis</option>
                    <option value="menipis">Menipis</option>
                    <option value="aman">Aman</option>
                </select>
                <select id="filter-aktif" class="lis-form-control py-1.5 text-xs w-auto">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                    <option value="all">Semua</option>
                </select>
            </div>
        </div>

        <div class="p-4 sm:p-5 overflow-x-auto">
            <table id="table-barang" class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-bold bg-slate-50">
                        <th class="py-3 px-3 w-12 text-center">No</th>
                        <th class="py-3 px-3">Kode / Nama Barang</th>
                        <th class="py-3 px-3">Kategori</th>
                        <th class="py-3 px-3 text-center">Stok</th>
                        <th class="py-3 px-3 text-center">Min</th>
                        <th class="py-3 px-3">Batch Terdekat Kedaluwarsa</th>
                        <th class="py-3 px-3 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Barang -->
<div id="modal-barang" class="lis-modal">
    <div class="lis-modal-box max-w-2xl w-full">
        <div class="lis-modal-header bg-gradient-to-r from-emerald-900 to-teal-900 border-b border-amber-400/40">
            <h3 class="lis-modal-title text-white flex items-center gap-2">
                <i data-lucide="boxes" class="w-5 h-5 text-amber-300"></i>
                <span id="modal-barang-title">Tambah Barang</span>
            </h3>
            <button type="button" onclick="closeBarangModal()" class="lis-modal-close"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>

        <form id="form-barang" onsubmit="saveBarang(event)" class="lis-modal-body p-5 space-y-3">
            <input type="hidden" id="brg-id" name="id">

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="lis-form-label">Kode Barang</label>
                    <input type="text" name="kode_barang" id="brg-kode" class="lis-form-control font-mono" placeholder="Otomatis">
                    <p class="lis-form-hint">Kosongkan untuk dibuat otomatis.</p>
                </div>
                <div class="sm:col-span-2">
                    <label class="lis-form-label">Nama Barang <span class="req">*</span></label>
                    <input type="text" name="nama_barang" id="brg-nama" required class="lis-form-control font-bold" placeholder="Contoh: Reagen Glukosa GOD-PAP">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="lis-form-label">Kategori <span class="req">*</span></label>
                    <select name="kategori" id="brg-kategori" required class="lis-form-control">
                        <?php foreach ($kategori_ref as $k): ?>
                            <option value="<?= html_escape($k['nama']) ?>"><?= html_escape($k['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="lis-form-label">Satuan <span class="req">*</span></label>
                    <select name="satuan" id="brg-satuan" required class="lis-form-control">
                        <?php foreach ($satuan_ref as $s): ?>
                            <option value="<?= html_escape($s['nama']) ?>"><?= html_escape($s['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="lis-form-label">Merk</label>
                    <input type="text" name="merk" id="brg-merk" class="lis-form-control" placeholder="Diasys / Roche / ...">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="lis-form-label">Kemasan</label>
                    <input type="text" name="kemasan" id="brg-kemasan" class="lis-form-control" placeholder="Box isi 100 test">
                </div>
                <div>
                    <label class="lis-form-label">Suhu Penyimpanan</label>
                    <input type="text" name="suhu_penyimpanan" id="brg-suhu" class="lis-form-control" placeholder="2-8 C / Suhu ruang">
                </div>
                <div>
                    <label class="lis-form-label">Lokasi Simpan</label>
                    <select name="lokasi_simpan" id="brg-lokasi" class="lis-form-control">
                        <option value="">-- Pilih Lokasi --</option>
                        <?php foreach ($lokasi_ref as $l): ?>
                            <option value="<?= html_escape($l['nama']) ?>"><?= html_escape($l['nama']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="lis-form-label">Stok Minimum (titik pesan ulang)</label>
                    <input type="number" step="0.01" min="0" name="stok_minimum" id="brg-min" class="lis-form-control font-bold" value="0">
                    <p class="lis-form-hint">Sistem memperingatkan bila stok mencapai angka ini.</p>
                </div>
                <div>
                    <label class="lis-form-label">Stok Maksimum</label>
                    <input type="number" step="0.01" min="0" name="stok_maksimum" id="brg-max" class="lis-form-control font-bold" value="0">
                </div>
            </div>

            <div>
                <label class="lis-form-label">Keterangan</label>
                <textarea name="keterangan" id="brg-keterangan" rows="2" class="lis-form-control" placeholder="Catatan tambahan"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer">
                    <input type="checkbox" name="wajib_batch" id="brg-batch" value="1" checked class="rounded border-slate-300 text-emerald-700">
                    <span class="text-xs font-semibold text-slate-700">Wajib nomor batch &amp; kedaluwarsa</span>
                </label>
                <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer">
                    <input type="checkbox" name="is_active" id="brg-aktif" value="1" checked class="rounded border-slate-300 text-emerald-700">
                    <span class="text-xs font-semibold text-slate-700">Barang aktif</span>
                </label>
            </div>

            <div class="pt-3 border-t border-slate-200 flex items-center justify-between">
                <button type="button" onclick="closeBarangModal()" class="px-4 py-2 rounded-xl border border-slate-300 text-xs font-semibold hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold">Simpan Barang</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Detail Batch -->
<div id="modal-batch" class="lis-modal">
    <div class="lis-modal-box max-w-2xl w-full">
        <div class="lis-modal-header bg-gradient-to-r from-slate-800 to-slate-900 border-b border-amber-400/40">
            <h3 class="lis-modal-title text-white flex items-center gap-2">
                <i data-lucide="layers" class="w-5 h-5 text-amber-300"></i>
                <span id="modal-batch-title">Rincian Batch</span>
            </h3>
            <button type="button" onclick="closeBatchModal()" class="lis-modal-close"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <div id="batch-body" class="lis-modal-body p-5"></div>
    </div>
</div>

<script>
    const BARANG_URL = {
        fetch: '<?= site_url('gudang/fetch_barang') ?>',
        get: '<?= site_url('gudang/get_barang') ?>',
        save: '<?= site_url('gudang/save_barang') ?>',
        hapus: '<?= site_url('gudang/hapus_barang') ?>'
    };

    const GAYA_STOK = {
        habis:   ['bg-rose-50 text-rose-700 border-rose-200', 'Habis'],
        kritis:  ['bg-amber-50 text-amber-800 border-amber-300', 'Kritis'],
        menipis: ['bg-yellow-50 text-yellow-800 border-yellow-200', 'Menipis'],
        aman:    ['bg-emerald-50 text-emerald-700 border-emerald-200', 'Aman']
    };
    const GAYA_EXP = {
        kedaluwarsa:   ['bg-rose-50 text-rose-700 border-rose-200', 'Kedaluwarsa'],
        kritis:        ['bg-amber-50 text-amber-800 border-amber-300', 'Kritis'],
        peringatan:    ['bg-yellow-50 text-yellow-800 border-yellow-200', 'Perhatian'],
        aman:          ['bg-emerald-50 text-emerald-700 border-emerald-200', 'Aman'],
        tanpa_tanggal: ['bg-slate-100 text-slate-500 border-slate-200', 'Tanpa Tanggal']
    };

    let tableBarang = null;

    function angka(n) {
        const v = Number(n);
        return Number.isInteger(v) ? v.toString() : v.toFixed(2).replace(/\.?0+$/, '');
    }

    $(function () {
        tableBarang = $('#table-barang').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: BARANG_URL.fetch,
                type: 'GET',
                data: function (d) {
                    d.kategori = $('#filter-kategori').val() || 'all';
                    d.status_stok = $('#filter-stok').val() || 'all';
                    d.is_active = $('#filter-aktif').val() || 'all';
                }
            },
            order: [],
            columns: [
                { data: null, orderable: false, className: 'text-center text-slate-400 py-3',
                  render: (d, t, r, meta) => meta.row + meta.settings._iDisplayStart + 1 },
                { data: 'nama_barang', orderable: false, className: 'py-3',
                  render: (d, t, r) =>
                      '<strong class="text-slate-900 block">' + d + '</strong>' +
                      '<span class="text-[10px] text-slate-400 font-mono">' + r.kode_barang + '</span>' +
                      (r.merk ? '<span class="text-[10px] text-slate-500 block">' + r.merk + '</span>' : '') +
                      (Number(r.is_active) === 0 ? '<span class="text-[10px] text-rose-600 font-bold">NONAKTIF</span>' : '')
                },
                { data: 'kategori', orderable: false, className: 'py-3',
                  render: (d, t, r) =>
                      '<span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">' + d + '</span>' +
                      (r.lokasi_simpan ? '<div class="text-[10px] text-slate-500 mt-0.5">' + r.lokasi_simpan + '</div>' : '')
                },
                { data: 'stok', orderable: false, className: 'text-center py-3',
                  render: function (d, t, r) {
                      const g = GAYA_STOK[r.status_stok] || GAYA_STOK.aman;
                      return '<span class="font-black text-slate-900 text-sm">' + angka(d) + '</span>' +
                             '<span class="text-[10px] text-slate-400 block">' + r.satuan + '</span>' +
                             '<span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold border ' + g[0] + '">' + g[1] + '</span>';
                  }
                },
                { data: 'stok_minimum', orderable: false, className: 'text-center py-3 text-slate-600',
                  render: (d) => angka(d) },
                { data: 'batch_terdekat', orderable: false, className: 'py-3',
                  render: function (d, t, r) {
                      if (!d) return '<span class="text-[11px] text-slate-400">Tidak ada batch bertanggal</span>';
                      const g = GAYA_EXP[r.status_kedaluwarsa.status] || GAYA_EXP.aman;
                      const sisa = r.status_kedaluwarsa.sisa_hari;
                      const teks = sisa === null ? '' : (sisa < 0 ? 'lewat ' + Math.abs(sisa) + ' hari' : 'sisa ' + sisa + ' hari');
                      return '<span class="px-2 py-0.5 rounded text-[10px] font-bold border ' + g[0] + '">' + g[1] + '</span>' +
                             '<div class="text-[10px] text-slate-500 mt-0.5">' + d.tanggal_kedaluwarsa + ' &bull; ' + teks + '</div>' +
                             '<div class="text-[10px] text-slate-400 font-mono">Batch ' + (d.nomor_batch || '-') + ' &bull; ' + r.jml_batch + ' batch aktif</div>';
                  }
                },
                { data: 'id', orderable: false, className: 'text-center py-3',
                  render: function (d) {
                      let b = '<div class="flex items-center justify-center gap-1.5">';
                      b += '<button type="button" onclick="lihatBatch(' + d + ')" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700" title="Rincian batch"><i data-lucide="layers" class="w-3.5 h-3.5"></i></button>';
                      <?php if ($can_edit): ?>
                      b += '<button type="button" onclick="editBarang(' + d + ')" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700" title="Ubah"><i data-lucide="edit" class="w-3.5 h-3.5"></i></button>';
                      <?php endif; ?>
                      <?php if ($can_delete): ?>
                      b += '<button type="button" onclick="hapusBarang(' + d + ')" class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700" title="Hapus"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>';
                      <?php endif; ?>
                      return b + '</div>';
                  }
                }
            ],
            drawCallback: () => { if (window.lucide) lucide.createIcons(); },
            language: {
                processing: 'Memuat data...',
                emptyTable: 'Belum ada barang terdaftar.',
                zeroRecords: 'Tidak ada barang yang cocok.',
                info: 'Menampilkan _START_ - _END_ dari _TOTAL_ barang',
                infoEmpty: 'Tidak ada data',
                lengthMenu: 'Tampil _MENU_',
                search: 'Cari:',
                paginate: { first: 'Awal', last: 'Akhir', next: 'Lanjut', previous: 'Kembali' }
            }
        });

        $('#filter-kategori, #filter-stok, #filter-aktif').on('change', () => tableBarang.ajax.reload());
    });

    function showBarangModal() {
        const m = document.getElementById('modal-barang');
        m.classList.remove('hidden'); m.classList.add('flex'); m.style.display = 'flex';
        if (window.lucide) lucide.createIcons();
    }
    function closeBarangModal() {
        const m = document.getElementById('modal-barang');
        m.classList.remove('flex'); m.classList.add('hidden'); m.style.display = 'none';
    }
    function closeBatchModal() {
        const m = document.getElementById('modal-batch');
        m.classList.remove('flex'); m.classList.add('hidden'); m.style.display = 'none';
    }

    function openBarangModal() {
        document.getElementById('form-barang').reset();
        document.getElementById('brg-id').value = '';
        document.getElementById('brg-batch').checked = true;
        document.getElementById('brg-aktif').checked = true;
        document.getElementById('modal-barang-title').textContent = 'Tambah Barang';
        showBarangModal();
    }

    function editBarang(id) {
        LIS.fetchJson(BARANG_URL.get + '/' + id)
            .then(res => {
                const d = res.data;
                document.getElementById('brg-id').value = d.id;
                document.getElementById('brg-kode').value = d.kode_barang;
                document.getElementById('brg-nama').value = d.nama_barang;
                document.getElementById('brg-kategori').value = d.kategori;
                document.getElementById('brg-satuan').value = d.satuan;
                document.getElementById('brg-merk').value = d.merk || '';
                document.getElementById('brg-kemasan').value = d.kemasan || '';
                document.getElementById('brg-suhu').value = d.suhu_penyimpanan || '';
                document.getElementById('brg-lokasi').value = d.lokasi_simpan || '';
                document.getElementById('brg-min').value = d.stok_minimum;
                document.getElementById('brg-max').value = d.stok_maksimum;
                document.getElementById('brg-keterangan').value = d.keterangan || '';
                document.getElementById('brg-batch').checked = String(d.wajib_batch) === '1';
                document.getElementById('brg-aktif').checked = String(d.is_active) === '1';
                document.getElementById('modal-barang-title').textContent = 'Ubah ' + d.nama_barang;
                showBarangModal();
            })
            .catch(err => LIS.showError('Gagal Memuat Barang', err, 'Data barang tidak dapat dimuat.'));
    }

    function saveBarang(e) {
        e.preventDefault();
        const body = new FormData(document.getElementById('form-barang'));
        if (!document.getElementById('brg-batch').checked) body.set('wajib_batch', '0');
        if (!document.getElementById('brg-aktif').checked) body.set('is_active', '0');

        LIS.fetchJson(BARANG_URL.save, { method: 'POST', body: body })
            .then(res => {
                closeBarangModal();
                LIS.toast('success', res.message);
                tableBarang.ajax.reload(null, false);
            })
            .catch(err => LIS.showError('Gagal Menyimpan Barang', err, 'Data barang tidak dapat disimpan.'));
    }

    function hapusBarang(id) {
        Swal.fire({
            title: 'Hapus Barang?',
            html: 'Barang hanya dapat dihapus bila belum pernah bermutasi.<br><span class="text-xs text-slate-500">Bila sudah ada riwayat, nonaktifkan saja agar kartu stok tetap utuh.</span>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(res => {
            if (!res.isConfirmed) return;
            LIS.fetchJson(BARANG_URL.hapus + '/' + id, { method: 'POST' })
                .then(res => {
                    LIS.toast('success', res.message);
                    tableBarang.ajax.reload(null, false);
                })
                .catch(err => LIS.showError('Gagal Menghapus Barang', err, 'Barang tidak dapat dihapus.'));
        });
    }

    function lihatBatch(id) {
        LIS.fetchJson(BARANG_URL.get + '/' + id)
            .then(res => {
                const d = res.data;
                let html = '<div class="bg-slate-50 border border-slate-200 rounded-xl p-4 mb-4 text-xs">';
                html += '<strong class="text-sm text-slate-900 block">' + d.nama_barang + '</strong>';
                html += '<span class="text-[11px] text-slate-500 font-mono">' + d.kode_barang + '</span>';
                html += '<div class="mt-2 flex gap-4"><span>Stok total: <strong>' + angka(d.stok) + ' ' + d.satuan + '</strong></span>';
                html += '<span>Minimum: <strong>' + angka(d.stok_minimum) + '</strong></span>';
                if (d.suhu_penyimpanan) html += '<span>Suhu: <strong>' + d.suhu_penyimpanan + '</strong></span>';
                html += '</div></div>';

                if (!d.batch || d.batch.length === 0) {
                    html += '<p class="text-xs text-slate-400 text-center py-6">Belum ada batch tercatat untuk barang ini.</p>';
                } else {
                    html += '<table class="w-full text-left text-xs border-collapse">';
                    html += '<thead><tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] font-bold bg-slate-50">';
                    html += '<th class="py-2 px-2">Batch</th><th class="py-2 px-2">Terima</th><th class="py-2 px-2">Kedaluwarsa</th>';
                    html += '<th class="py-2 px-2 text-center">Masuk</th><th class="py-2 px-2 text-center">Sisa</th></tr></thead><tbody>';

                    d.batch.forEach(b => {
                        const g = GAYA_EXP[b.status_kedaluwarsa.status] || GAYA_EXP.aman;
                        const habis = Number(b.jumlah_sisa) <= 0;
                        html += '<tr class="border-b border-slate-100' + (habis ? ' opacity-50' : '') + '">';
                        html += '<td class="py-2 px-2 font-mono">' + (b.nomor_batch || '-') + '</td>';
                        html += '<td class="py-2 px-2 text-slate-600">' + b.tanggal_terima + '</td>';
                        html += '<td class="py-2 px-2">' + (b.tanggal_kedaluwarsa || '<span class="text-slate-400">-</span>') +
                                '<span class="ml-1 px-1.5 py-0.5 rounded text-[9px] font-bold border ' + g[0] + '">' + g[1] + '</span></td>';
                        html += '<td class="py-2 px-2 text-center text-slate-600">' + angka(b.jumlah_masuk) + '</td>';
                        html += '<td class="py-2 px-2 text-center font-black">' + angka(b.jumlah_sisa) + '</td>';
                        html += '</tr>';
                    });
                    html += '</tbody></table>';
                    html += '<p class="text-[10px] text-slate-400 mt-3">Baris pudar menandakan batch yang sudah habis.</p>';
                }

                document.getElementById('modal-batch-title').textContent = 'Rincian Batch';
                document.getElementById('batch-body').innerHTML = html;
                const m = document.getElementById('modal-batch');
                m.classList.remove('hidden'); m.classList.add('flex'); m.style.display = 'flex';
                if (window.lucide) lucide.createIcons();
            })
            .catch(err => LIS.showError('Gagal Memuat Batch', err, 'Rincian batch tidak dapat dimuat.'));
    }
</script>
