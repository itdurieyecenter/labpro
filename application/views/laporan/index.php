<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-5">

    <!-- 1. BREADCRUMB & PAGE HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-slate-400">Keuangan & Pelayanan</span>
                <span>/</span>
                <span class="text-emerald-800 font-semibold">Laporan & Rekapitulasi</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="file-spreadsheet" class="w-6 h-6"></i>
                </span>
                <span>Laporan Pelayanan LIS & Penerimaan Billing BLUD</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Rekapitulasi kunjungan pasien, realisasi kasir akun bayar, piutang penjaminan mitra, dan frekuensi uji laboratorium
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="<?= site_url('laporan/cetak?start_date=' . $start_date . '&end_date=' . $end_date . '&tipe_pasien=' . $tipe_pasien) ?>" target="_blank" class="px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-2 shadow-sm transition-all border border-emerald-600">
                <i data-lucide="printer" class="w-4 h-4 text-amber-300"></i>
                <span>Cetak Lembar Laporan</span>
            </a>
        </div>
    </div>

    <!-- 2. FILTER PERIODE & KRITERIA LAPORAN -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="<?= site_url('laporan') ?>" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
            <div>
                <label class="lis-form-label">Tanggal Mulai</label>
                <input type="date" name="start_date" value="<?= html_escape($start_date) ?>" class="lis-form-control text-xs font-medium">
            </div>
            <div>
                <label class="lis-form-label">Tanggal Sampai</label>
                <input type="date" name="end_date" value="<?= html_escape($end_date) ?>" class="lis-form-control text-xs font-medium">
            </div>
            <div>
                <label class="lis-form-label">Tipe Pelayanan</label>
                <select name="tipe_pasien" class="lis-form-control text-xs font-medium">
                    <option value="all" <?= $tipe_pasien === 'all' ? 'selected' : '' ?>>Semua Tipe Pelayanan</option>
                    <option value="mandiri" <?= $tipe_pasien === 'mandiri' ? 'selected' : '' ?>>1. Pasien Mandiri (Umum)</option>
                    <option value="rujukan_faskes" <?= $tipe_pasien === 'rujukan_faskes' ? 'selected' : '' ?>>2. Rujukan Faskes / Dokter</option>
                    <option value="perusahaan_lingkungan" <?= $tipe_pasien === 'perusahaan_lingkungan' ? 'selected' : '' ?>>3. Mitra Perusahaan / Lingkungan</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-colors flex items-center justify-center gap-1.5 shadow-xs">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Terapkan Filter</span>
                </button>
                <a href="<?= site_url('laporan') ?>" class="px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 text-xs font-medium transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- 3. KARTU STATISTIK FINANSIAL & PELAYANAN -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-800 flex items-center justify-center flex-shrink-0">
                <i data-lucide="users" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-slate-500 block truncate">Total Registrasi Pasien</span>
                <span class="text-xl font-extrabold text-slate-900 leading-tight"><?= number_format($tot_registrasi) ?></span>
                <span class="text-[10px] text-slate-400 block mt-0.5"><?= $tipe_counts['mandiri'] ?> Mandiri &bull; <?= $tipe_counts['rujukan_faskes'] ?> Rujukan &bull; <?= $tipe_counts['perusahaan_lingkungan'] ?> Mitra</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-emerald-200 bg-emerald-50/20 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center justify-center flex-shrink-0">
                <i data-lucide="banknote" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-emerald-800 block truncate">Total Tagihan Billing LIS</span>
                <span class="text-xl font-extrabold text-emerald-950 leading-tight">Rp <?= number_format($tot_omset, 0, ',', '.') ?></span>
                <span class="text-[10px] text-emerald-700 block mt-0.5">Periode Terpilih</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-teal-200 bg-teal-50/20 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-teal-100 text-teal-800 border border-teal-300 flex items-center justify-center flex-shrink-0">
                <i data-lucide="badge-check" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-teal-800 block truncate">Realisasi Kasir (PAID)</span>
                <span class="text-xl font-extrabold text-teal-950 leading-tight">Rp <?= number_format($tot_paid, 0, ',', '.') ?></span>
                <span class="text-[10px] text-teal-700 block mt-0.5">Masuk Rekening Bayar BLUD</span>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-amber-200 bg-amber-50/20 shadow-xs flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-amber-100 text-amber-800 border border-amber-300 flex items-center justify-center flex-shrink-0">
                <i data-lucide="hand-coins" class="w-5 h-5"></i>
            </div>
            <div class="min-w-0">
                <span class="text-[11px] font-semibold text-amber-800 block truncate">Piutang Terjamin (GUARANTEED)</span>
                <span class="text-xl font-extrabold text-amber-950 leading-tight">Rp <?= number_format($tot_piutang, 0, ',', '.') ?></span>
                <span class="text-[10px] text-amber-700 block mt-0.5">Klaim PKS Mitra</span>
            </div>
        </div>
    </div>

    <!-- 4. TABEL REKAPITULASI PELAYANAN & KEUANGAN -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        
        <!-- Tabel Penerimaan Kasir per Akun Bayar (1 Kolom) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden flex flex-col">
            <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="credit-card" class="w-4 h-4 text-emerald-700"></i>
                    <h3 class="font-bold text-xs text-slate-800 uppercase tracking-wider">Rekapitulasi Akun Bayar Kasir</h3>
                </div>
            </div>
            <div class="p-4 flex-1">
                <table class="w-full text-left text-xs divide-y divide-slate-100">
                    <thead>
                        <tr class="text-[10px] uppercase font-bold text-slate-400">
                            <th class="pb-2">Metode / Akun</th>
                            <th class="pb-2 text-center">Trx</th>
                            <th class="pb-2 text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <?php if (empty($rekap_kasir)): ?>
                            <tr><td colspan="3" class="text-center py-4 text-slate-400">Belum ada transaksi kasir</td></tr>
                        <?php else: ?>
                            <?php foreach ($rekap_kasir as $rk): ?>
                                <tr>
                                    <td class="py-2.5 font-bold text-slate-800"><?= html_escape($rk['akun_nama']) ?></td>
                                    <td class="py-2.5 text-center font-mono text-slate-600"><?= $rk['jumlah_trx'] ?></td>
                                    <td class="py-2.5 text-right font-mono font-bold text-emerald-800">Rp <?= number_format($rk['total_nominal'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tabel Piutang Penjamin Mitra (1 Kolom) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden flex flex-col">
            <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="building-2" class="w-4 h-4 text-amber-700"></i>
                    <h3 class="font-bold text-xs text-slate-800 uppercase tracking-wider">Rekap Piutang Penjamin Mitra</h3>
                </div>
            </div>
            <div class="p-4 flex-1">
                <table class="w-full text-left text-xs divide-y divide-slate-100">
                    <thead>
                        <tr class="text-[10px] uppercase font-bold text-slate-400">
                            <th class="pb-2">Instansi Penjamin</th>
                            <th class="pb-2 text-center">Pasien</th>
                            <th class="pb-2 text-right">Saldo Piutang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <?php if (empty($rekap_piutang)): ?>
                            <tr><td colspan="3" class="text-center py-4 text-slate-400">Tidak ada saldo piutang</td></tr>
                        <?php else: ?>
                            <?php foreach ($rekap_piutang as $rp): ?>
                                <tr>
                                    <td class="py-2.5">
                                        <span class="font-bold text-slate-800 block"><?= html_escape($rp['mitra_nama']) ?></span>
                                        <span class="text-[10px] font-mono text-slate-400"><?= html_escape($rp['nomor_pks_spk'] ?: 'Tanpa No PKS') ?></span>
                                    </td>
                                    <td class="py-2.5 text-center font-mono text-slate-600"><?= $rp['jumlah_pasien'] ?></td>
                                    <td class="py-2.5 text-right font-mono font-bold text-amber-900">Rp <?= number_format($rp['total_piutang'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 10 Parameter Uji Terbanyak (1 Kolom) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden flex flex-col">
            <div class="p-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="activity" class="w-4 h-4 text-teal-700"></i>
                    <h3 class="font-bold text-xs text-slate-800 uppercase tracking-wider">Top 10 Pemeriksaan Lab Terbanyak</h3>
                </div>
            </div>
            <div class="p-4 flex-1">
                <table class="w-full text-left text-xs divide-y divide-slate-100">
                    <thead>
                        <tr class="text-[10px] uppercase font-bold text-slate-400">
                            <th class="pb-2">Parameter Uji</th>
                            <th class="pb-2 text-center">Uji</th>
                            <th class="pb-2 text-right">Nilai Tarif</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <?php if (empty($top_params)): ?>
                            <tr><td colspan="3" class="text-center py-4 text-slate-400">Belum ada pemeriksaan</td></tr>
                        <?php else: ?>
                            <?php foreach ($top_params as $tp): ?>
                                <tr>
                                    <td class="py-2">
                                        <span class="font-bold text-slate-800 block leading-tight"><?= html_escape($tp['nama_item']) ?></span>
                                        <span class="text-[10px] text-slate-400"><?= html_escape($tp['kategori_lab'] ?: 'Lab') ?></span>
                                    </td>
                                    <td class="py-2 text-center font-mono text-slate-600"><?= $tp['frekuensi'] ?>x</td>
                                    <td class="py-2 text-right font-mono font-bold text-slate-800">Rp <?= number_format($tp['total_tarif'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- 5. DAFTAR LENGKAP REGISTRASI & BILLING PERIODE -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-200 bg-slate-50/70 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="list" class="w-4 h-4 text-emerald-700"></i>
                <h3 class="font-bold text-xs text-slate-800 uppercase tracking-wider">Rincian Data Registrasi Pasien & Billing ID</h3>
            </div>
            <span class="text-xs text-slate-500 font-medium"><?= count($registrations) ?> Transaksi</span>
        </div>

        <div class="p-4 sm:p-5 overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 uppercase text-[10px] tracking-wider font-bold bg-slate-50">
                        <th class="py-3 px-3 w-10 text-center">No</th>
                        <th class="py-3 px-3">Tanggal & Registrasi</th>
                        <th class="py-3 px-3">Nama Pasien / Subjek</th>
                        <th class="py-3 px-3">Tipe / Pengirim</th>
                        <th class="py-3 px-3 text-right">Tagihan Billing</th>
                        <th class="py-3 px-3 text-center">Status Pembayaran</th>
                        <th class="py-3 px-3 text-center">Status Order</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    <?php if (empty($registrations)): ?>
                        <tr><td colspan="7" class="text-center py-8 text-slate-400">Tidak ada data transaksi pada rentang tanggal ini.</td></tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($registrations as $rg): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="py-3 px-3 text-center text-slate-400 font-medium"><?= $no++ ?></td>
                                <td class="py-3 px-3">
                                    <span class="font-mono font-bold text-slate-800 block"><?= html_escape($rg['nomor_registrasi']) ?></span>
                                    <span class="font-mono text-[10px] text-slate-400 block"><?= html_escape($rg['nomor_billing']) ?></span>
                                    <span class="text-[10px] text-slate-400"><?= date('d/m/Y', strtotime($rg['tanggal_pendaftaran'])) ?></span>
                                </td>
                                <td class="py-3 px-3">
                                    <strong class="text-slate-900 block"><?= html_escape($rg['nama_pasien']) ?></strong>
                                    <span class="text-[10px] text-slate-500 font-mono"><?= html_escape($rg['nomor_rm'] ?: '-') ?></span>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="capitalize font-semibold text-slate-700 block"><?= str_replace('_', ' ', $rg['tipe_pasien']) ?></span>
                                    <span class="text-[10px] text-slate-400"><?= html_escape($rg['instansi_pengirim'] ?: ($rg['dokter_pengirim'] ?: 'Umum')) ?></span>
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-bold text-slate-900">
                                    Rp <?= number_format($rg['total_biaya'], 0, ',', '.') ?>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <?php if ($rg['status_pembayaran'] === 'PAID'): ?>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">PAID</span>
                                    <?php elseif ($rg['status_pembayaran'] === 'GUARANTEED'): ?>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-teal-100 text-teal-800 border border-teal-300">GUARANTEED</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300">UNPAID</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="uppercase font-mono text-[10px] font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded">
                                        <?= html_escape($rg['status_order']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
