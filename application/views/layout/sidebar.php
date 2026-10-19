<aside class="fixed inset-y-0 left-0 z-50 flex flex-col bg-slate-950 border-r border-emerald-950/80 transition-all duration-300 ease-in-out select-none shadow-2xl lg:shadow-none"
       :class="{
           'w-64': !sidebarCompact,
           'w-20': sidebarCompact,
           'translate-x-0': sidebarOpen,
           '-translate-x-full lg:translate-x-0': !sidebarOpen
       }">

    <!-- Brand Header (Melayu Riau Theme) -->
    <div class="h-16 flex items-center bg-slate-950 border-b border-emerald-900/40 px-4 transition-all duration-300"
         :class="sidebarCompact ? 'justify-center' : 'justify-between px-5'">
        <a href="<?= site_url('dashboard') ?>" class="flex items-center gap-3 group focus:outline-none">
            <!-- Logo Icon with Lancang Kuning & Emerald Melayu Gradients -->
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-800 via-teal-700 to-amber-500 flex items-center justify-center text-amber-100 shadow-lg shadow-emerald-950/60 flex-shrink-0 group-hover:scale-105 transition-transform border border-amber-400/30">
                <i data-lucide="flask-conical" class="w-5 h-5 text-amber-200"></i>
            </div>
            <div x-show="!sidebarCompact" x-cloak class="transition-opacity duration-200 overflow-hidden whitespace-nowrap">
                <span class="text-base font-extrabold text-white tracking-wide block leading-tight">
                    LABKES <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-yellow-300 to-amber-500 font-black">RIAU</span>
                </span>
                <span class="text-[9px] text-amber-200/70 font-semibold tracking-wider uppercase block leading-tight">Provinsi Riau &bull; SIMRS LIS</span>
            </div>
        </a>

        <!-- Mobile Close Button -->
        <button type="button" @click="closeMobile()" class="lg:hidden text-slate-400 hover:text-amber-300 p-1.5 rounded-lg hover:bg-slate-900 focus:outline-none">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
    </div>

    <!-- User Profile Strip with Malay Emerald & Royal Gold Badge -->
    <div class="p-3 border-b border-emerald-900/40 bg-emerald-950/20 transition-all duration-300">
        <div class="flex items-center gap-3" :class="sidebarCompact ? 'justify-center' : ''">
            <div class="relative flex-shrink-0">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-900 to-slate-900 text-amber-400 border border-amber-500/40 flex items-center justify-center font-bold text-xs uppercase shadow-inner">
                    <?= substr($user['username'] ?? 'U', 0, 2) ?>
                </div>
                <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-amber-400 ring-2 ring-slate-950"></span>
            </div>
            <div x-show="!sidebarCompact" x-cloak class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-white truncate leading-tight"><?= html_escape($user['full_name'] ?? 'User') ?></p>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="text-[10px] font-medium text-amber-300 truncate"><?= html_escape($user['role_display_name'] ?? 'Role') ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Modul -->
    <nav class="flex-1 overflow-y-auto px-2 py-3 space-y-1.5" :class="sidebarCompact ? 'px-2' : 'px-3'">
        <div x-show="!sidebarCompact" x-cloak class="px-3 pt-1 pb-1.5 text-[9px] font-bold uppercase tracking-wider text-amber-400/80 flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
            <span>Navigasi Modul Labkes Riau</span>
        </div>

        <?php if (!empty($menu_tree)): ?>
            <?php foreach ($menu_tree as $menu): ?>
                <?php
                $hasChildren = !empty($menu['children']);
                $isActive = (isset($active_menu) && $active_menu === $menu['menu_url']);

                // Cek apakah ada child yang aktif
                $isChildActive = false;
                if ($hasChildren) {
                    foreach ($menu['children'] as $c) {
                        if (isset($active_menu) && $active_menu === $c['menu_url']) {
                            $isChildActive = true;
                            break;
                        }
                    }
                }
                ?>

                <?php if (!$hasChildren): ?>
                    <!-- Single Menu Item (Warna Adat Melayu Riau) -->
                    <div class="relative group" x-data="{ tooltip: false }">
                        <a href="<?= site_url($menu['menu_url']) ?>"
                           @click="closeMobile()"
                           @mouseenter="if (sidebarCompact) tooltip = true"
                           @mouseleave="tooltip = false"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all duration-200 <?= $isActive ? 'bg-gradient-to-r from-emerald-800 to-emerald-700 text-amber-300 font-bold border-l-4 border-amber-400 shadow-lg shadow-emerald-950/50' : 'text-slate-300 hover:bg-emerald-950/50 hover:text-amber-200' ?>"
                           :class="sidebarCompact ? 'justify-center px-0 w-full h-11' : ''">
                            <i data-lucide="<?= html_escape($menu['menu_icon']) ?>" class="w-4 h-4 flex-shrink-0 <?= $isActive ? 'text-amber-400' : 'text-emerald-400/80 group-hover:text-amber-400' ?>"></i>
                            <span x-show="!sidebarCompact" x-cloak class="truncate"><?= html_escape($menu['menu_name']) ?></span>
                        </a>

                        <!-- Compact Tooltip -->
                        <div x-show="sidebarCompact && tooltip"
                             x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-x-1"
                             x-transition:enter-end="opacity-100 translate-x-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="absolute left-full top-1/2 -translate-y-1/2 ml-3.5 px-3 py-1.5 bg-slate-900 text-amber-200 text-xs font-semibold rounded-lg shadow-xl border border-amber-500/30 whitespace-nowrap z-50 pointer-events-none">
                            <?= html_escape($menu['menu_name']) ?>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Dropdown Menu Item (Accordion in full mode, Flyout in compact mode) -->
                    <div x-data="{ open: <?= ($isActive || $isChildActive) ? 'true' : 'false' ?>, flyout: false }"
                         @mouseenter="if (sidebarCompact) flyout = true"
                         @mouseleave="flyout = false"
                         class="relative">

                        <button type="button"
                                @click="if (!sidebarCompact) { open = !open; } else { flyout = !flyout; }"
                                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-medium transition-all duration-200 <?= ($isChildActive) ? 'bg-emerald-950/60 text-amber-300 font-bold border border-amber-500/30' : 'text-slate-300 hover:bg-emerald-950/50 hover:text-amber-200' ?>"
                                :class="sidebarCompact ? 'justify-center px-0 w-full h-11' : ''">
                            <div class="flex items-center gap-3 truncate" :class="sidebarCompact ? 'justify-center' : ''">
                                <i data-lucide="<?= html_escape($menu['menu_icon']) ?>" class="w-4 h-4 flex-shrink-0 <?= ($isChildActive) ? 'text-amber-400' : 'text-emerald-400/80' ?>"></i>
                                <span x-show="!sidebarCompact" x-cloak class="truncate"><?= html_escape($menu['menu_name']) ?></span>
                            </div>
                            <i x-show="!sidebarCompact" x-cloak data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180 text-amber-400' : ''"></i>
                        </button>

                        <!-- Accordion Submenu (Full Mode) -->
                        <div x-show="!sidebarCompact && open" x-cloak class="pl-7 pr-1 space-y-1 py-1 transition-all">
                            <?php foreach ($menu['children'] as $child): ?>
                                <?php $isSubActive = (isset($active_menu) && $active_menu === $child['menu_url']); ?>
                                <a href="<?= site_url($child['menu_url']) ?>"
                                   @click="closeMobile()"
                                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-colors <?= $isSubActive ? 'bg-emerald-800/40 text-amber-300 font-bold border-l-2 border-amber-400' : 'text-slate-400 hover:bg-emerald-950/30 hover:text-amber-100' ?>">
                                    <i data-lucide="<?= html_escape($child['menu_icon']) ?>" class="w-3.5 h-3.5 flex-shrink-0 <?= $isSubActive ? 'text-amber-400' : '' ?>"></i>
                                    <span class="truncate"><?= html_escape($child['menu_name']) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>

                        <!-- Flyout Submenu (Compact Mode) -->
                        <div x-show="sidebarCompact && flyout"
                             x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-x-1"
                             x-transition:enter-end="opacity-100 translate-x-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="absolute left-full top-0 ml-3.5 w-60 bg-slate-950 border border-emerald-800/80 rounded-xl shadow-2xl p-2 z-50 text-left">
                            <div class="px-3 py-2 border-b border-emerald-900/60 text-[11px] font-bold text-amber-400 uppercase tracking-wider flex items-center gap-2 bg-emerald-950/60 rounded-t-lg">
                                <i data-lucide="<?= html_escape($menu['menu_icon']) ?>" class="w-3.5 h-3.5 text-amber-400"></i>
                                <span><?= html_escape($menu['menu_name']) ?></span>
                            </div>
                            <div class="py-1 space-y-1">
                                <?php foreach ($menu['children'] as $child): ?>
                                    <?php $isSubActive = (isset($active_menu) && $active_menu === $child['menu_url']); ?>
                                    <a href="<?= site_url($child['menu_url']) ?>"
                                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-colors <?= $isSubActive ? 'bg-emerald-800 text-amber-300 font-bold' : 'text-slate-300 hover:bg-emerald-900/40 hover:text-white' ?>">
                                        <i data-lucide="<?= html_escape($child['menu_icon']) ?>" class="w-3.5 h-3.5 flex-shrink-0 <?= $isSubActive ? 'text-amber-400' : '' ?>"></i>
                                        <span class="truncate"><?= html_escape($child['menu_name']) ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-xs text-slate-500 px-3 py-2" x-show="!sidebarCompact">Tidak ada menu aktif.</p>
        <?php endif; ?>
    </nav>

    <!-- Sidebar Bottom Controls -->
    <div class="p-2.5 border-t border-emerald-950/80 bg-slate-950/90 flex-shrink-0 space-y-1">
        <!-- Desktop Compact Toggle Button -->
        <button type="button"
                @click="toggleCompact()"
                class="hidden lg:flex w-full items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium text-slate-400 hover:bg-emerald-950/50 hover:text-amber-300 transition-colors"
                :class="sidebarCompact ? 'justify-center px-0 h-10' : ''"
                :title="sidebarCompact ? 'Perluas Menu Sidebar' : 'Kecilkan Menu Sidebar (Compact)'">
            <i data-lucide="chevrons-left" class="w-4 h-4 transition-transform duration-300" :class="sidebarCompact ? 'rotate-180 text-amber-400' : ''"></i>
            <span x-show="!sidebarCompact" x-cloak class="truncate">Kecilkan Sidebar</span>
        </button>

        <!-- Logout Button (Merah Marun Melayu) -->
        <a href="<?= site_url('logout') ?>"
           class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-medium text-rose-400 hover:bg-rose-950/40 hover:text-rose-300 transition-colors"
           :class="sidebarCompact ? 'justify-center px-0 h-10' : ''"
           title="Keluar dari Sistem">
            <i data-lucide="log-out" class="w-4 h-4 flex-shrink-0"></i>
            <span x-show="!sidebarCompact" x-cloak class="truncate">Keluar Sistem</span>
        </a>
    </div>
</aside>

<!-- Mobile Backdrop Overlay -->
<div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-40 lg:hidden"
     x-show="sidebarOpen"
     x-cloak
     x-transition:enter="transition-opacity ease-linear duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="closeMobile()"></div>

<!-- Main Content Wrapper: Responsive adjustment according to Compact / Full Sidebar -->
<div class="flex-1 flex flex-col min-w-0 transition-all duration-300 ease-in-out"
     :class="{
         'lg:pl-64': !sidebarCompact,
         'lg:pl-20': sidebarCompact
     }">
