</main>

<footer class="bg-white border-t border-slate-200 px-6 py-4 text-center text-xs text-slate-500">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-2 max-w-7xl mx-auto">
        <p>&copy; <?= date('Y') ?> <strong class="text-emerald-900 font-bold">UPT Laboratorium Kesehatan Provinsi Riau</strong> &mdash; Dinas Kesehatan Pemprov Riau &bull; LIS PRO</p>
        <div class="flex items-center gap-3 text-slate-400 text-[11px]">
            <span>Hak Akses: <strong class="text-emerald-800 font-bold bg-amber-50 px-2 py-0.5 rounded border border-amber-300"><?= html_escape($user['role_display_name'] ?? $user['role_name'] ?? '-') ?></strong></span>
            <span>&bull;</span>
            <span class="text-emerald-700 font-semibold flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                RBAC Active
            </span>
        </div>
    </div>
</footer>

</div>
</div>

<script>
    if (window.lucide) {
        lucide.createIcons();
    }
</script>
</body>
</html>
