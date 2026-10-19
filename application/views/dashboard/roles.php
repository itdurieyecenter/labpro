<div class="space-y-6 max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Pengaturan Hak Akses (RBAC)</h1>
            <p class="text-xs text-slate-500 mt-1">Matriks otorisasi per menu dan aksi untuk setiap peran pengguna di LIS</p>
        </div>
        <div>
            <?= ui_btn('Kembali ke Dashboard', 'outline-secondary', 'sm', ['href' => site_url('dashboard'), 'icon' => 'arrow-left']) ?>
        </div>
    </div>

    <?= ui_panel_start('Matriks Hak Akses Modul & Menu', 'shield-check') ?>
    <div class="overflow-x-auto">
        <table class="w-full text-xs text-left border-collapse">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50">
                    <th class="py-3 px-4 font-bold text-slate-700">Nama Menu / Modul</th>
                    <th class="py-3 px-4 font-bold text-slate-700">URL Route</th>
                    <th class="py-3 px-4 font-bold text-slate-700 text-center">Administrator</th>
                    <th class="py-3 px-4 font-bold text-slate-700 text-center">Dokter Sp.PK</th>
                    <th class="py-3 px-4 font-bold text-slate-700 text-center">Analis Lab</th>
                    <th class="py-3 px-4 font-bold text-slate-700 text-center">Petugas Registrasi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php
                $menusGrouped = [];
                foreach ($permissions_raw as $p) {
                    $mId = $p['menu_id'];
                    if (!isset($menusGrouped[$mId])) {
                        $menusGrouped[$mId] = [
                            'menu_name' => $p['menu_name'],
                            'menu_url' => $p['menu_url'],
                            'parent_id' => $p['parent_id'],
                            'roles' => []
                        ];
                    }
                    if ($p['role_id']) {
                        $menusGrouped[$mId]['roles'][$p['role_id']] = $p;
                    }
                }
                ?>

                <?php foreach ($menusGrouped as $mId => $m): ?>
                    <tr class="hover:bg-slate-50/50">
                        <td class="py-3 px-4 font-medium text-slate-900 <?= $m['parent_id'] ? 'pl-8 text-slate-600' : 'font-semibold' ?>">
                            <?= $m['parent_id'] ? '&bull; ' : '' ?><?= html_escape($m['menu_name']) ?>
                        </td>
                        <td class="py-3 px-4 font-mono text-[11px] text-slate-500">
                            <?= html_escape($m['menu_url']) ?>
                        </td>

                        <?php for ($r = 1; $r <= 4; $r++): ?>
                            <?php $perm = $m['roles'][$r] ?? null; ?>
                            <td class="py-3 px-4 text-center">
                                <?php if ($perm && !empty($perm['can_view'])): ?>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                        <i data-lucide="check" class="w-3 h-3"></i> Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-400">
                                        <i data-lucide="minus" class="w-3 h-3"></i> -
                                    </span>
                                <?php endif; ?>
                            </td>
                        <?php endfor; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= ui_panel_end() ?>

</div>
