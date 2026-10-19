<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? html_escape($title) . ' &bull; Labkes Prov. Riau' : 'UPT Laboratorium Kesehatan Provinsi Riau &bull; LIS PRO SIMRS' ?></title>
    <link rel="stylesheet" href="<?= asset_url('css/tailwind.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('plugins/sweetalert2/sweetalert2.min.css') ?>">
    <link rel="stylesheet" href="<?= asset_url('plugins/datatables/jquery.dataTables.min.css') ?>">
    <script src="<?= asset_url('plugins/datatables/jquery-3.7.1.min.js') ?>"></script>
    <script src="<?= asset_url('plugins/sweetalert2/sweetalert2.all.min.js') ?>"></script>
    <script src="<?= asset_url('plugins/datatables/jquery.dataTables.min.js') ?>"></script>
    <script src="<?= asset_url('js/alpine.min.js') ?>" defer></script>
    <script src="<?= asset_url('js/lucide.min.js') ?>"></script>
    <script src="<?= asset_url('js/app.js') ?>?v=<?= filemtime(FCPATH . 'assets/js/app.js') ?>"></script>
</head>
<body class="h-full text-slate-800 antialiased font-sans bg-slate-50"
      x-data="{
          sidebarOpen: false,
          sidebarCompact: localStorage.getItem('lis_sidebar_compact') === 'true',
          toggleCompact() {
              this.sidebarCompact = !this.sidebarCompact;
              localStorage.setItem('lis_sidebar_compact', this.sidebarCompact);
              this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
          },
          openMobile() { this.sidebarOpen = true; },
          closeMobile() { this.sidebarOpen = false; }
      }"
      :class="{ 'overflow-hidden lg:overflow-auto': sidebarOpen }">
<div class="min-h-screen bg-slate-50 flex flex-col">
