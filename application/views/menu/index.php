<div class="space-y-5">

    <!-- 1. BREADCRUMB & PAGE HEADER (Clean Melayu Riau Theme) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-slate-400">Pengaturan Sistem</span>
                <span>/</span>
                <span class="text-emerald-800 font-semibold">Manajemen Menu</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </span>
                <span>Manajemen Menu & Navigasi SIMRS</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Konfigurasi struktur hierarki menu, rute sistem, dan visibilitas modul UPT Laboratorium Kesehatan Provinsi Riau</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="<?= site_url('roles') ?>" class="px-3.5 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-semibold transition-colors flex items-center gap-2 shadow-2xs">
                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-700"></i>
                <span>Matriks Hak Akses (RBAC)</span>
            </a>
            <button type="button" onclick="openMenuModal('create')" class="px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold transition-all shadow-sm border border-emerald-600 shadow-emerald-900/10 flex items-center gap-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Tambah Menu Baru</span>
            </button>
        </div>
    </div>

    <!-- 2. QUICK STATS METRIC CARDS (Melayu Riau Theme) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-center flex-shrink-0">
                <i data-lucide="layout-grid" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Total Menu</span>
                <span class="text-xl sm:text-2xl font-black text-slate-900 leading-none"><?= $stats['total'] ?></span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Semua Item Navigasi</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 flex items-center justify-center flex-shrink-0">
                <i data-lucide="folder" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Menu Utama</span>
                <span class="text-xl sm:text-2xl font-black text-slate-900 leading-none"><?= $stats['root'] ?></span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Modul Level Tertinggi</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-teal-50 border border-teal-200 text-teal-800 flex items-center justify-center flex-shrink-0">
                <i data-lucide="git-branch" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Submenu</span>
                <span class="text-xl sm:text-2xl font-black text-slate-900 leading-none"><?= $stats['submenu'] ?></span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Cabang Sub-Modul</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 border border-emerald-300 text-emerald-900 flex items-center justify-center flex-shrink-0">
                <i data-lucide="check-circle-2" class="w-6 h-6"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Menu Aktif</span>
                <span class="text-xl sm:text-2xl font-black text-emerald-800 leading-none"><?= $stats['active'] ?></span>
                <span class="text-[10px] text-slate-400 block mt-0.5">Tampil di Sidebar</span>
            </div>
        </div>
    </div>

    <!-- Main Table Container -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-200/80 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <i data-lucide="list-tree" class="w-4 h-4 text-emerald-700"></i>
                    Daftar Struktur Menu & Rute Sistem
                </h2>
                <p class="text-[11px] text-slate-500">Urutan tampilan mengikuti konfigurasi sort_order dan parent hierarki</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="relative w-56 sm:w-64" id="menu-search-wrapper">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="text" id="search-menu-table" oninput="filterMenuTable(this.value)" placeholder="Cari nama atau URL..." class="h-9 pl-9 pr-8 rounded-xl border border-slate-300 text-xs text-slate-800 bg-white placeholder-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/20 focus:outline-none w-full">
                    <button type="button" id="btn-clear-menu-search" onclick="clearMenuSearch()" class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-0.5" title="Hapus pencarian">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                    <!-- Autocomplete Suggestions -->
                    <div id="menu-search-suggestions" class="hidden absolute z-30 left-0 right-0 mt-1 bg-white rounded-xl shadow-2xl border border-emerald-200 overflow-hidden divide-y divide-slate-100 max-h-56 overflow-y-auto"></div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse" id="table-menus">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-[10px] font-bold text-slate-600 uppercase tracking-wider">
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4 w-14 text-center">Ikon</th>
                        <th class="py-3 px-4 font-bold">Nama Menu & Hirarki</th>
                        <th class="py-3 px-4">URL Route</th>
                        <th class="py-3 px-4">Menu Induk (Parent)</th>
                        <th class="py-3 px-4 w-20 text-center">Urutan</th>
                        <th class="py-3 px-4 w-28 text-center">Status</th>
                        <th class="py-3 px-4 w-28 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="menu-tbody">
                    <?php if (!empty($all_menus)): ?>
                        <?php $no = 1; foreach ($all_menus as $m): ?>
                            <?php $isSub = !empty($m['parent_id']); ?>
                            <tr class="hover:bg-slate-50/70 transition-colors <?= $isSub ? 'bg-slate-50/25' : '' ?>">
                                <td class="py-3 px-4 text-center text-slate-400 font-mono text-[11px]">
                                    <?= $no++ ?>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center mx-auto border border-slate-200 shadow-2xs">
                                        <i data-lucide="<?= html_escape($m['menu_icon']) ?>" class="w-4 h-4"></i>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2 <?= $isSub ? 'pl-6' : '' ?>">
                                        <?php if ($isSub): ?>
                                            <span class="text-slate-300 font-mono text-xs select-none">└─</span>
                                        <?php endif; ?>
                                        <span class="font-bold <?= $isSub ? 'text-slate-700' : 'text-slate-900 text-[13px]' ?>">
                                            <?= html_escape($m['menu_name']) ?>
                                        </span>
                                        <?php if (!$isSub && empty($m['parent_id'])): ?>
                                            <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-amber-50 text-amber-800 border border-amber-300">Root</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <code class="px-2 py-1 rounded bg-slate-100 text-slate-700 font-mono text-[11px] border border-slate-200/60">
                                        <?= html_escape($m['menu_url']) ?>
                                    </code>
                                </td>
                                <td class="py-3 px-4">
                                    <?php if ($m['parent_name']): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            <i data-lucide="folder" class="w-3 h-3 text-emerald-600"></i>
                                            <?= html_escape($m['parent_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-400 italic text-[11px]">- Menu Utama -</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 text-center font-mono font-bold text-slate-600">
                                    <?= (int)$m['sort_order'] ?>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <button type="button" onclick="toggleMenuStatus(<?= $m['id'] ?>)"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold transition-all <?= ((int)$m['is_active'] === 1) ? 'bg-emerald-50 text-emerald-800 border border-emerald-300 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200 hover:bg-slate-200' ?>"
                                            title="Klik untuk mengubah status aktif/nonaktif">
                                        <span class="w-1.5 h-1.5 rounded-full <?= ((int)$m['is_active'] === 1) ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' ?>"></span>
                                        <span><?= ((int)$m['is_active'] === 1) ? 'Aktif' : 'Non-aktif' ?></span>
                                    </button>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" onclick="openMenuModal('edit', <?= $m['id'] ?>)" class="p-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-colors border border-emerald-200" title="Edit Menu">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                        </button>
                                        <button type="button" onclick="deleteMenu(<?= $m['id'] ?>, '<?= html_escape($m['menu_name']) ?>')" class="p-1.5 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 transition-colors border border-rose-200" title="Hapus Menu">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">Belum ada data menu terdaftar.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah / Edit Menu SIMRS (Melayu Riau Theme) -->
<div id="modal-menu" class="lis-modal">
    <div class="lis-modal-box max-w-xl">
        <div class="lis-modal-header bg-gradient-to-r from-emerald-900 via-emerald-800 to-teal-900 border-b border-amber-400/30">
            <div>
                <h3 class="lis-modal-title">
                    <i data-lucide="menu" class="w-5 h-5 text-amber-300"></i>
                    <span id="modal-menu-title">Tambah Menu Sistem</span>
                </h3>
                <p class="lis-modal-subtitle">Pengaturan rute dan visibilitas pada sidebar UPT Labkesda Prov. Riau</p>
            </div>
            <button type="button" onclick="closeMenuModal()" class="lis-modal-close">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="form-menu" onsubmit="saveMenu(event)" class="lis-modal-body space-y-4">
            <input type="hidden" name="id" id="menu-id">

            <div class="space-y-4 bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
                <div>
                    <label class="lis-form-label">Menu Induk (Parent Menu) <span class="req">*</span></label>
                    <select name="parent_id" id="menu-parent-id" class="lis-form-control font-medium">
                        <option value="root">-- Menu Utama (Root / Tanpa Induk) --</option>
                        <?php foreach ($root_menus as $rm): ?>
                            <option value="<?= $rm['id'] ?>"><?= html_escape($rm['menu_name']) ?> (<?= html_escape($rm['menu_url']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <span class="lis-form-hint">Pilih jika menu ini merupakan submenu dari modul yang sudah ada.</span>
                </div>

                <div>
                    <label class="lis-form-label">Nama Menu / Label <span class="req">*</span></label>
                    <input type="text" name="menu_name" id="menu-name" class="lis-form-control font-bold text-slate-800" placeholder="e.g. Pemeriksaan Hemodialisa, Order Lab" required>
                </div>

                <div>
                    <label class="lis-form-label">URL / Rute Controller <span class="req">*</span></label>
                    <input type="text" name="menu_url" id="menu-url" class="lis-form-control font-mono text-emerald-900" placeholder="e.g. hemodialisa, master/dokter, #settings" required>
                    <span class="lis-form-hint">Gunakan tanda '#' jika hanya sebagai container induk tanpa halaman rute langsung (contoh: #master).</span>
                </div>

                <div>
                    <label class="lis-form-label">Ikon Lucide <span class="req">*</span></label>
                    <div class="flex items-center gap-3">
                        <div class="relative flex-1">
                            <input type="text" name="menu_icon" id="menu-icon" oninput="previewIcon(this.value)" class="lis-form-control font-mono" placeholder="e.g. activity, flask-conical, database" required>
                        </div>
                        <div id="icon-preview-box" class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-700 flex items-center justify-center flex-shrink-0 shadow-inner">
                            <i data-lucide="circle" id="preview-icon-elem" class="w-5 h-5"></i>
                        </div>
                    </div>
                    <!-- Quick Icon Picker Pills -->
                    <div class="mt-2 flex flex-wrap gap-1.5 text-[11px]">
                        <span class="text-[10px] text-slate-400 font-semibold self-center">Pilihan cepat:</span>
                        <button type="button" onclick="setIconPreset('layout-dashboard')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-emerald-100 hover:text-emerald-800 text-slate-600 text-[10px]">layout-dashboard</button>
                        <button type="button" onclick="setIconPreset('flask-conical')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-emerald-100 hover:text-emerald-800 text-slate-600 text-[10px]">flask-conical</button>
                        <button type="button" onclick="setIconPreset('clipboard-list')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-emerald-100 hover:text-emerald-800 text-slate-600 text-[10px]">clipboard-list</button>
                        <button type="button" onclick="setIconPreset('database')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-emerald-100 hover:text-emerald-800 text-slate-600 text-[10px]">database</button>
                        <button type="button" onclick="setIconPreset('stethoscope')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-emerald-100 hover:text-emerald-800 text-slate-600 text-[10px]">stethoscope</button>
                        <button type="button" onclick="setIconPreset('activity')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-emerald-100 hover:text-emerald-800 text-slate-600 text-[10px]">activity</button>
                        <button type="button" onclick="setIconPreset('settings')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-emerald-100 hover:text-emerald-800 text-slate-600 text-[10px]">settings</button>
                        <button type="button" onclick="setIconPreset('users')" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-emerald-100 hover:text-emerald-800 text-slate-600 text-[10px]">users</button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="lis-form-label">Urutan Tampil (Sort Order)</label>
                        <input type="number" name="sort_order" id="menu-sort-order" class="lis-form-control font-mono" value="0" min="0">
                    </div>
                    <div>
                        <label class="lis-form-label">Status Menu</label>
                        <div class="flex items-center gap-2 mt-2">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" id="menu-is-active" value="1" checked class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                                <span class="ml-2.5 text-xs font-semibold text-slate-700">Aktifkan Menu</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="lis-modal-footer">
            <button type="button" onclick="closeMenuModal()" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-100 transition-colors">
                Batal
            </button>
            <button type="button" onclick="document.getElementById('form-menu').requestSubmit()" class="px-5 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-semibold shadow-sm shadow-emerald-700/30 transition-all flex items-center gap-2 border border-emerald-600/40">
                <i data-lucide="save" class="w-4 h-4 text-amber-300"></i>
                <span>Simpan Menu</span>
            </button>
        </div>
    </div>
</div>

<script>
function previewIcon(iconName) {
    iconName = iconName ? iconName.trim().toLowerCase() : 'circle';
    const box = document.getElementById('icon-preview-box');
    box.innerHTML = `<i data-lucide="${iconName}" class="w-5 h-5"></i>`;
    if (window.lucide) {
        lucide.createIcons();
    }
}

function setIconPreset(iconName) {
    document.getElementById('menu-icon').value = iconName;
    previewIcon(iconName);
}

function openMenuModal(mode, id = null) {
    const modal = document.getElementById('modal-menu');
    const form = document.getElementById('form-menu');
    form.reset();
    document.getElementById('menu-id').value = '';
    document.getElementById('menu-is-active').checked = true;
    document.getElementById('modal-menu-title').textContent = mode === 'create' ? 'Tambah Menu Sistem Baru' : 'Edit Menu Sistem';

    if (mode === 'edit' && id) {
        fetch('<?= site_url('menu/get') ?>/' + id)
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    const m = res.data;
                    document.getElementById('menu-id').value = m.id;
                    document.getElementById('menu-parent-id').value = m.parent_id ? m.parent_id : 'root';
                    document.getElementById('menu-name').value = m.menu_name;
                    document.getElementById('menu-url').value = m.menu_url;
                    document.getElementById('menu-icon').value = m.menu_icon;
                    document.getElementById('menu-sort-order').value = m.sort_order;
                    document.getElementById('menu-is-active').checked = parseInt(m.is_active) === 1;
                    previewIcon(m.menu_icon);
                } else {
                    LIS.alert('Gagal', res.message, 'error');
                }
            });
    } else {
        previewIcon('circle');
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
    if (window.lucide) lucide.createIcons();
}

function closeMenuModal() {
    const modal = document.getElementById('modal-menu');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
}

function saveMenu(e) {
    e.preventDefault();
    const form = document.getElementById('form-menu');
    const fd = new FormData(form);

    fetch('<?= site_url('menu/save') ?>', {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            LIS.toast('success', res.message);
            closeMenuModal();
            setTimeout(() => { window.location.reload(); }, 600);
        } else {
            LIS.alert('Gagal Menyimpan', res.message, 'error');
        }
    })
    .catch(() => {
        LIS.alert('Error', 'Terjadi kesalahan sistem saat menyimpan menu.', 'error');
    });
}

function toggleMenuStatus(id) {
    fetch('<?= site_url('menu/toggle_status') ?>/' + id, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            LIS.toast('success', res.message);
            setTimeout(() => { window.location.reload(); }, 500);
        } else {
            LIS.alert('Gagal', res.message, 'error');
        }
    });
}

function deleteMenu(id, name) {
    LIS.confirm('Hapus Menu', 'Yakin menghapus menu <strong>' + name + '</strong>? Jika memiliki submenu, seluruh submenu juga akan dihapus.', 'Ya, Hapus')
        .then(res => {
            if (res.isConfirmed) {
                fetch('<?= site_url('menu/delete') ?>/' + id, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(rjson => {
                    if (rjson.status === 'success') {
                        LIS.toast('success', rjson.message);
                        setTimeout(() => { window.location.reload(); }, 600);
                    } else {
                        LIS.alert('Gagal', rjson.message, 'error');
                    }
                });
            }
        });
}

function filterMenuTable(query) {
    query = (query || '').toLowerCase().trim();
    const btnClear = document.getElementById('btn-clear-menu-search');
    const suggestBox = document.getElementById('menu-search-suggestions');
    if (btnClear) btnClear.classList.toggle('hidden', !query);

    const rows = document.querySelectorAll('#menu-tbody tr');
    const matches = [];

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        const isMatch = text.includes(query);
        row.style.display = isMatch ? '' : 'none';
        if (query && isMatch && matches.length < 6) {
            const nameEl = row.querySelector('.font-bold');
            const urlEl = row.querySelector('.font-mono');
            if (nameEl) {
                matches.push({
                    name: nameEl.innerText.trim(),
                    url: urlEl ? urlEl.innerText.trim() : ''
                });
            }
        }
    });

    if (suggestBox) {
        if (!query || matches.length === 0) {
            suggestBox.classList.add('hidden');
            suggestBox.innerHTML = '';
        } else {
            let html = '<div class="px-3 py-1.5 bg-slate-50 text-[10px] font-bold text-slate-500 flex justify-between"><span>Saran Menu</span><span class="text-emerald-700">' + matches.length + ' saran</span></div>';
            matches.forEach(m => {
                html += '<button type="button" onclick="selectMenuSuggestion(\'' + m.name.replace(/'/g, "\\'") + '\')" class="w-full text-left px-3 py-2 text-xs hover:bg-emerald-50 flex items-center justify-between group transition-colors">';
                html += '<span class="font-bold text-slate-800 truncate">' + m.name + '</span>';
                if (m.url) html += '<span class="font-mono text-[10px] text-emerald-800 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 ml-2">' + m.url + '</span>';
                html += '</button>';
            });
            suggestBox.innerHTML = html;
            suggestBox.classList.remove('hidden');
        }
    }
}

function selectMenuSuggestion(val) {
    const input = document.getElementById('search-menu-table');
    if (input) {
        input.value = val;
        filterMenuTable(val);
    }
    const suggestBox = document.getElementById('menu-search-suggestions');
    if (suggestBox) suggestBox.classList.add('hidden');
}

function clearMenuSearch() {
    const input = document.getElementById('search-menu-table');
    if (input) {
        input.value = '';
        filterMenuTable('');
    }
}

document.addEventListener('click', function(e) {
    const wrapper = document.getElementById('menu-search-wrapper');
    const suggestBox = document.getElementById('menu-search-suggestions');
    if (wrapper && suggestBox && !wrapper.contains(e.target)) {
        suggestBox.classList.add('hidden');
    }
});
</script>
