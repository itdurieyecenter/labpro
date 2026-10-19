<header class="h-16 bg-white border-b border-slate-200 px-4 sm:px-6 flex items-center justify-between sticky top-0 z-30 shadow-xs relative">

    <div class="flex items-center gap-3">
        <!-- Mobile Drawer Hamburger Toggle Button -->
        <button type="button"
                @click="openMobile()"
                class="lg:hidden text-slate-600 hover:text-emerald-800 p-2 rounded-xl hover:bg-emerald-50 focus:outline-none transition-colors"
                title="Buka Menu Navigasi">
            <i data-lucide="menu" class="w-5 h-5"></i>
        </button>

        <!-- Desktop Compact Toggle Button -->
        <button type="button"
                @click="toggleCompact()"
                class="hidden lg:flex items-center justify-center text-slate-500 hover:text-emerald-800 p-2 rounded-xl hover:bg-emerald-50 focus:outline-none transition-colors"
                :title="sidebarCompact ? 'Perluas Sidebar' : 'Kecilkan Sidebar (Compact)'">
            <i data-lucide="chevrons-left" class="w-5 h-5 transition-transform duration-300" :class="sidebarCompact ? 'rotate-180 text-amber-600' : ''"></i>
        </button>

        <div class="flex items-center gap-2 text-xs text-slate-500">
            <div class="hidden sm:flex items-center gap-2">
                <span class="font-bold text-slate-800 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-400 ring-2 ring-emerald-600 animate-pulse"></span>
                    UPT Laboratorium Kesehatan Provinsi Riau
                </span>
                <span class="text-slate-300">/</span>
            </div>
            <span class="text-emerald-800 font-bold truncate max-w-[180px] sm:max-w-none"><?= html_escape($title ?? 'Dashboard') ?></span>
        </div>
    </div>

    <div class="flex items-center gap-2 sm:gap-3">
        <!-- Demo Role Switcher (Melayu Palette) -->
        <div class="hidden xl:flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50/50 rounded-full border border-emerald-200/70 text-xs">
            <span class="text-emerald-800/80 text-[11px] font-semibold">Demo:</span>
            <a href="<?= site_url('auth/quick_login/admin') ?>" class="px-2.5 py-0.5 rounded-full text-[11px] font-bold transition-all <?= ($user['role_name'] === 'admin') ? 'bg-emerald-800 text-amber-300 shadow-xs border border-amber-400/40' : 'text-slate-600 hover:bg-emerald-100/70 hover:text-emerald-900' ?>">Admin</a>
            <a href="<?= site_url('auth/quick_login/dokter') ?>" class="px-2.5 py-0.5 rounded-full text-[11px] font-bold transition-all <?= ($user['role_name'] === 'dokter') ? 'bg-emerald-800 text-amber-300 shadow-xs border border-amber-400/40' : 'text-slate-600 hover:bg-emerald-100/70 hover:text-emerald-900' ?>">Dokter</a>
            <a href="<?= site_url('auth/quick_login/analis') ?>" class="px-2.5 py-0.5 rounded-full text-[11px] font-bold transition-all <?= ($user['role_name'] === 'analis') ? 'bg-emerald-800 text-amber-300 shadow-xs border border-amber-400/40' : 'text-slate-600 hover:bg-emerald-100/70 hover:text-emerald-900' ?>">Analis</a>
            <a href="<?= site_url('auth/quick_login/petugas') ?>" class="px-2.5 py-0.5 rounded-full text-[11px] font-bold transition-all <?= ($user['role_name'] === 'registrasi') ? 'bg-emerald-800 text-amber-300 shadow-xs border border-amber-400/40' : 'text-slate-600 hover:bg-emerald-100/70 hover:text-emerald-900' ?>">Petugas</a>
        </div>

        <!-- User Profile Dropdown -->
        <div class="relative" x-data="{ userMenu: false }">
            <button type="button" @click="userMenu = !userMenu" class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-slate-100 transition-colors focus:outline-none">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-emerald-900 via-emerald-800 to-teal-800 text-amber-300 flex items-center justify-center font-bold text-xs uppercase shadow-sm border border-amber-400/40">
                    <?= substr($user['username'] ?? 'U', 0, 2) ?>
                </div>
                <div class="hidden md:block text-left text-xs">
                    <span class="font-semibold text-slate-800 block leading-tight truncate max-w-[120px]"><?= html_escape($user['full_name'] ?? 'User') ?></span>
                    <span class="text-[10px] text-amber-700 font-medium block leading-tight capitalize"><?= html_escape($user['role_display_name'] ?? 'Role') ?></span>
                </div>
                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400"></i>
            </button>

            <div x-show="userMenu"
                 x-cloak
                 @click.away="userMenu = false"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-60 bg-white rounded-2xl shadow-2xl border border-slate-200 py-2 text-xs text-slate-700 z-50">
                <div class="px-4 py-2.5 border-b border-slate-100 bg-emerald-50/30">
                    <p class="font-bold text-slate-900 truncate"><?= html_escape($user['full_name']) ?></p>
                    <p class="text-[11px] text-slate-500 truncate"><?= html_escape($user['email'] ?? $user['username']) ?></p>
                    <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-300"><?= html_escape($user['role_display_name']) ?></span>
                </div>

                <div class="py-1">
                    <a href="<?= site_url('dashboard') ?>" class="flex items-center gap-2.5 px-4 py-2 hover:bg-emerald-50/60 text-slate-700 font-medium transition-colors">
                        <i data-lucide="layout-dashboard" class="w-4 h-4 text-emerald-700"></i>
                        <span>Dashboard Utama</span>
                    </a>
                    <a href="<?= site_url('menu') ?>" class="flex items-center gap-2.5 px-4 py-2 hover:bg-emerald-50/60 text-slate-700 font-medium transition-colors">
                        <i data-lucide="menu" class="w-4 h-4 text-emerald-700"></i>
                        <span>Manajemen Menu</span>
                    </a>
                    <a href="<?= site_url('roles') ?>" class="flex items-center gap-2.5 px-4 py-2 hover:bg-emerald-50/60 text-slate-700 font-medium transition-colors">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-700"></i>
                        <span>Hak Akses (RBAC)</span>
                    </a>
                </div>

                <div class="border-t border-slate-100 my-1"></div>
                <a href="<?= site_url('logout') ?>" class="flex items-center gap-2.5 px-4 py-2 text-rose-700 hover:bg-rose-50 font-semibold transition-colors">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span>Keluar (Logout)</span>
                </a>
            </div>
        </div>
    </div>
</header>

<main class="flex-1 p-4 sm:p-6 lg:p-8">