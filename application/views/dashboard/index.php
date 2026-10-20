<?php
/**
 * Dashboard Eksekutif
 *
 * Semua angka di halaman ini berasal dari query agregat di Dashboard_model;
 * tidak ada nilai contoh yang ditulis tetap di view. Bila satu kotak kosong,
 * artinya data transaksinya memang belum ada.
 *
 * Warna grafik memakai palet kategorikal yang sudah lolos uji keterbacaan
 * bagi pengguna buta warna (slot: aqua, biru, oranye, ungu). Tahapan status
 * order memakai satu rona biru bertingkat karena sifatnya berurutan, bukan
 * identitas yang setara. Aplikasi ini hanya punya mode terang, sehingga
 * token warna di bawah tidak menyediakan varian gelap.
 */

$rp = function ($n) {
    return 'Rp ' . number_format((float) $n, 0, ',', '.');
};
$rpPendek = function ($n) {
    $n = (float) $n;
    if (abs($n) >= 1000000000) return 'Rp ' . rtrim(rtrim(number_format($n / 1000000000, 1, ',', '.'), '0'), ',') . ' M';
    if (abs($n) >= 1000000) return 'Rp ' . rtrim(rtrim(number_format($n / 1000000, 1, ',', '.'), '0'), ',') . ' jt';
    if (abs($n) >= 1000) return 'Rp ' . rtrim(rtrim(number_format($n / 1000, 1, ',', '.'), '0'), ',') . ' rb';
    return 'Rp ' . number_format($n, 0, ',', '.');
};
$angka = function ($n) {
    return number_format((float) $n, 0, ',', '.');
};
$desimal = function ($n, $d = 1) {
    return number_format((float) $n, $d, ',', '.');
};
$jam = function ($n) {
    if ($n === null) return '&mdash;';
    return $n >= 48
        ? rtrim(rtrim(number_format($n / 24, 1, ',', '.'), '0'), ',') . ' hari'
        : rtrim(rtrim(number_format($n, 1, ',', '.'), '0'), ',') . ' jam';
};
$tglId = function ($t, $panjang = false) {
    $pendek = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $lengkap = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $s = strtotime($t);
    $bulan = $panjang ? $lengkap[(int) date('n', $s)] : $pendek[(int) date('n', $s)];
    return date('j', $s) . ' ' . $bulan . ' ' . date('Y', $s);
};

$hariPeriode = max(1, (int) ((strtotime($selesai) - strtotime($mulai)) / 86400) + 1);
$rataHarian = $kpi['kunjungan'] / $hariPeriode;

$pintasan = ['7' => '7 hari', '30' => '30 hari', '90' => '90 hari'];
?>

<style>
/* ============================================================
   Dashboard Eksekutif - gaya khusus halaman ini.
   Ditulis di sini, bukan di Tailwind, karena hanya dipakai di
   satu halaman: gradien kop, kartu, dan tabel angka.
   ============================================================ */
#dash { --dash-garis: #e6eaf0; }

#dash .dash-kop {
    background: linear-gradient(130deg, #052e22 0%, #064e3b 42%, #047857 100%);
    position: relative;
    overflow: hidden;
}
#dash .dash-kop::after {
    content: "";
    position: absolute;
    inset: 0;
    background-image: radial-gradient(circle at 1px 1px, rgba(255, 255, 255, .13) 1px, transparent 0);
    background-size: 18px 18px;
    opacity: .55;
    pointer-events: none;
}
#dash .dash-kop::before {
    content: "";
    position: absolute;
    right: -120px;
    top: -140px;
    width: 420px;
    height: 420px;
    border-radius: 9999px;
    background: radial-gradient(circle, rgba(251, 191, 36, .22) 0%, rgba(251, 191, 36, 0) 68%);
    pointer-events: none;
}
#dash .dash-kop-isi { position: relative; z-index: 1; }

#dash .dash-kartu {
    background: #fff;
    border: 1px solid var(--dash-garis);
    border-radius: 1rem;
    box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    transition: box-shadow .18s ease, transform .18s ease;
}
#dash .dash-kartu-hover:hover {
    box-shadow: 0 10px 24px -12px rgba(15, 23, 42, .18);
    transform: translateY(-1px);
}

#dash .dash-kpi-label {
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: .09em;
    text-transform: uppercase;
    color: #64748b;
}
#dash .dash-kpi-nilai {
    font-size: 1.65rem;
    font-weight: 800;
    line-height: 1.05;
    letter-spacing: -.025em;
    color: #0f172a;
    font-variant-numeric: tabular-nums;
}
#dash .dash-kpi-ikon {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: .75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

#dash .dash-chip {
    display: inline-flex;
    align-items: center;
    gap: .25rem;
    padding: .1rem .45rem;
    border-radius: 9999px;
    font-size: 10.5px;
    font-weight: 700;
    line-height: 1.5;
}

#dash .dash-judul {
    font-size: .875rem;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -.01em;
}
#dash .dash-subjudul { font-size: 11.5px; color: #64748b; line-height: 1.45; }

/* Kepala kartu: ikon + judul + keterangan di kanan, dipisah garis tipis */
#dash .dash-kepala {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: .75rem;
    flex-wrap: wrap;
    padding-bottom: .75rem;
    border-bottom: 1px solid #f1f5f9;
    margin-bottom: 1rem;
}
#dash .dash-kepala-kiri { display: flex; align-items: center; gap: .625rem; min-width: 0; }
#dash .dash-kepala-ikon {
    width: 2rem;
    height: 2rem;
    border-radius: .625rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #d1fae5;
}

/* Label pengelompokan antar bagian halaman */
#dash .dash-seksi {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding-top: .35rem;
}
#dash .dash-seksi span {
    font-size: 10.5px;
    font-weight: 800;
    letter-spacing: .13em;
    text-transform: uppercase;
    color: #94a3b8;
    white-space: nowrap;
}
#dash .dash-seksi::after {
    content: "";
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, #e2e8f0, rgba(226, 232, 240, 0));
}

#dash .dash-tabel { width: 100%; border-collapse: collapse; font-size: 12px; }
#dash .dash-tabel th {
    text-align: left;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #94a3b8;
    padding: .45rem .75rem;
    border-bottom: 1px solid var(--dash-garis);
    white-space: nowrap;
}
#dash .dash-tabel td {
    padding: .6rem .75rem;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    vertical-align: middle;
}
#dash .dash-tabel tbody tr:last-child td { border-bottom: 0; }
#dash .dash-tabel tbody tr:hover td { background: #f8fafc; }
#dash .dash-angka { text-align: right; font-variant-numeric: tabular-nums; }

#dash .dash-meter {
    height: .4rem;
    border-radius: 9999px;
    background: #eef2f7;
    overflow: hidden;
    min-width: 64px;
}
#dash .dash-meter > span { display: block; height: 100%; border-radius: 9999px; }

#dash .dash-kosong {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: .4rem;
    padding: 2rem 1rem;
    color: #94a3b8;
    font-size: 12px;
    text-align: center;
}

#dash details > summary { list-style: none; }
#dash details > summary::-webkit-details-marker { display: none; }
#dash .dash-rincian {
    font-size: 11px;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    user-select: none;
}
#dash .dash-rincian:hover { color: #0f172a; }
#dash details[open] .dash-rincian-ikon { transform: rotate(180deg); }
#dash .dash-rincian-ikon { transition: transform .15s ease; }

#dash .dash-gulir { max-height: 15rem; overflow: auto; }
#dash .dash-gulir::-webkit-scrollbar { width: 8px; height: 8px; }
#dash .dash-gulir::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 9999px; }

@media print {
    #dash .dash-sembunyi-cetak { display: none !important; }
}
</style>

<div id="dash" class="space-y-5">

    <!-- =============================================================
         KOP HALAMAN
         ============================================================= -->
    <div class="dash-kop rounded-2xl px-5 py-5 sm:px-7 sm:py-6 shadow-lg">
        <div class="dash-kop-isi flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
            <div class="min-w-0">
                <div class="flex items-center gap-2 text-[11px] font-semibold text-emerald-200/90 uppercase tracking-widest mb-2">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-300"></span>
                        UPT Laboratorium Kesehatan Provinsi Riau
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight leading-tight">
                    Dashboard Eksekutif
                </h1>
                <p class="text-[13px] text-emerald-100/85 mt-1.5">
                    <?= $tglId($mulai, true) ?> &ndash; <?= $tglId($selesai, true) ?>
                    <span class="text-emerald-200/60">&bull;</span> <?= $angka($hariPeriode) ?> hari
                    <span class="text-emerald-200/60">&bull;</span> <?= html_escape($user['full_name']) ?>
                </p>
                <p class="text-[11px] text-emerald-200/60 mt-1">
                    Dihitung <?= date('d/m/Y H:i') ?> WIB &bull; <?= html_escape($user['role_display_name'] ?? $user['role_name'] ?? '-') ?>
                </p>
            </div>

            <div class="flex items-stretch gap-3 flex-wrap dash-sembunyi-cetak">
                <div class="rounded-xl bg-white/10 border border-white/15 px-4 py-3 min-w-[132px]">
                    <span class="block text-[10px] font-bold uppercase tracking-widest text-emerald-200/80">Kunjungan</span>
                    <span class="block text-xl font-black text-white leading-tight mt-0.5"><?= $angka($kpi['kunjungan']) ?></span>
                    <span class="block text-[11px] text-emerald-100/70"><?= $desimal($rataHarian) ?> / hari</span>
                </div>
                <div class="rounded-xl bg-white/10 border border-white/15 px-4 py-3 min-w-[132px]">
                    <span class="block text-[10px] font-bold uppercase tracking-widest text-emerald-200/80">Terealisasi</span>
                    <span class="block text-xl font-black text-white leading-tight mt-0.5" title="<?= $rp($kpi['realisasi']) ?>"><?= $rpPendek($kpi['realisasi']) ?></span>
                    <span class="block text-[11px] text-emerald-100/70">piutang <?= $rpPendek($kpi['piutang']) ?></span>
                </div>
                <div class="flex flex-col gap-2 justify-center">
                    <a href="<?= site_url('dashboard/roles') ?>"
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-white/10 hover:bg-white/20 border border-white/20 text-white text-[11px] font-bold transition">
                        <i data-lucide="shield" class="w-3.5 h-3.5"></i> Hak Akses
                    </a>
                    <a href="<?= site_url('logout') ?>"
                       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-rose-500/90 hover:bg-rose-500 text-white text-[11px] font-bold transition">
                        <i data-lucide="log-out" class="w-3.5 h-3.5"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- =============================================================
         PENYARING PERIODE
         ============================================================= -->
    <form method="get" action="<?= site_url('dashboard') ?>"
          class="dash-kartu px-4 py-3 flex flex-col sm:flex-row sm:items-center gap-3 flex-wrap dash-sembunyi-cetak">
        <div class="flex items-center gap-2 text-slate-500">
            <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
            <span class="text-[11px] font-bold uppercase tracking-widest">Periode</span>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <input type="date" id="mulai" name="mulai" value="<?= html_escape($mulai) ?>" aria-label="Tanggal mulai"
                   class="h-9 px-3 rounded-lg border border-slate-200 text-xs text-slate-700 bg-white focus:outline-none focus:border-emerald-500">
            <span class="text-slate-400 text-xs">&ndash;</span>
            <input type="date" id="selesai" name="selesai" value="<?= html_escape($selesai) ?>" aria-label="Tanggal selesai"
                   class="h-9 px-3 rounded-lg border border-slate-200 text-xs text-slate-700 bg-white focus:outline-none focus:border-emerald-500">
            <button type="submit"
                    class="h-9 inline-flex items-center gap-1.5 px-4 rounded-lg bg-emerald-800 hover:bg-emerald-900 text-white text-xs font-bold transition">
                <i data-lucide="check" class="w-3.5 h-3.5"></i> Terapkan
            </button>
        </div>

        <div class="flex items-center gap-1.5 sm:ml-auto flex-wrap">
            <?php foreach ($pintasan as $hari => $label):
                $pMulai = date('Y-m-d', strtotime('-' . ((int) $hari - 1) . ' days'));
                $aktif = ($mulai === $pMulai && $selesai === date('Y-m-d'));
            ?>
                <a href="<?= site_url('dashboard') . '?mulai=' . $pMulai . '&selesai=' . date('Y-m-d') ?>"
                   class="h-8 inline-flex items-center px-3 rounded-lg text-[11px] font-bold border transition <?= $aktif ? 'bg-emerald-50 border-emerald-300 text-emerald-800' : 'bg-white border-slate-200 text-slate-500 hover:border-slate-300 hover:text-slate-700' ?>">
                    <?= $label ?>
                </a>
            <?php endforeach; ?>
            <?php $aktifBulan = ($mulai === date('Y-m-01') && $selesai === date('Y-m-t')); ?>
            <a href="<?= site_url('dashboard') . '?mulai=' . date('Y-m-01') . '&selesai=' . date('Y-m-t') ?>"
               class="h-8 inline-flex items-center px-3 rounded-lg text-[11px] font-bold border transition <?= $aktifBulan ? 'bg-emerald-50 border-emerald-300 text-emerald-800' : 'bg-white border-slate-200 text-slate-500 hover:border-slate-300 hover:text-slate-700' ?>">
                Bulan ini
            </a>
        </div>
    </form>

    <?php if ($this->session->flashdata('success')): ?>
        <?= ui_alert($this->session->flashdata('success'), 'success') ?>
    <?php endif; ?>

    <!-- =============================================================
         KARTU RINGKASAN
         ============================================================= -->
    <div class="dash-seksi"><span>Ringkasan Periode</span></div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

        <div class="dash-kartu dash-kartu-hover p-4">
            <div class="flex items-start justify-between gap-3">
                <span class="dash-kpi-label">Kunjungan Hari Ini</span>
                <span class="dash-kpi-ikon bg-emerald-50 text-emerald-700">
                    <i data-lucide="calendar-check" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="dash-kpi-nilai mt-2.5"><?= $angka($kpi['kunjungan_hari_ini']) ?></div>
            <div class="mt-2 flex items-center gap-2">
                <?php if ($kpi['delta_harian'] === null): ?>
                    <span class="text-[11px] text-slate-500">Kemarin tidak ada kunjungan</span>
                <?php else: ?>
                    <span class="dash-chip <?= $kpi['delta_harian'] >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' ?>">
                        <i data-lucide="<?= $kpi['delta_harian'] >= 0 ? 'trending-up' : 'trending-down' ?>" class="w-3 h-3"></i>
                        <?= $desimal(abs($kpi['delta_harian'])) ?>%
                    </span>
                    <span class="text-[11px] text-slate-500">vs kemarin (<?= $angka($kpi['kunjungan_kemarin']) ?>)</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="dash-kartu dash-kartu-hover p-4">
            <div class="flex items-start justify-between gap-3">
                <span class="dash-kpi-label">Parameter Diuji</span>
                <span class="dash-kpi-ikon bg-sky-50 text-sky-700">
                    <i data-lucide="flask-conical" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="dash-kpi-nilai mt-2.5"><?= $angka($kpi['parameter_diuji']) ?></div>
            <div class="mt-2 text-[11px] text-slate-500">
                <?= $kpi['kunjungan'] > 0 ? $desimal($kpi['parameter_diuji'] / $kpi['kunjungan']) . ' parameter per kunjungan' : 'Belum ada data periode ini' ?>
            </div>
        </div>

        <div class="dash-kartu dash-kartu-hover p-4">
            <div class="flex items-start justify-between gap-3">
                <span class="dash-kpi-label">TAT Rata-rata</span>
                <span class="dash-kpi-ikon bg-violet-50 text-violet-700">
                    <i data-lucide="timer" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="dash-kpi-nilai mt-2.5"><?= $kpi['tat_selesai'] > 0 ? $jam($kpi['tat_rata_jam']) : '&mdash;' ?></div>
            <div class="mt-2 flex items-center gap-2 flex-wrap">
                <?php if ($kpi['tat_ketepatan'] === null): ?>
                    <span class="text-[11px] text-slate-500">Belum ada order selesai</span>
                <?php else:
                    $kelasTat = $kpi['tat_ketepatan'] >= 90 ? 'bg-emerald-50 text-emerald-700'
                        : ($kpi['tat_ketepatan'] >= 75 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700');
                ?>
                    <span class="dash-chip <?= $kelasTat ?>">
                        <i data-lucide="target" class="w-3 h-3"></i> <?= $desimal($kpi['tat_ketepatan']) ?>% tepat
                    </span>
                    <span class="text-[11px] text-slate-500">dari <?= $angka($kpi['tat_selesai']) ?> order selesai</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="dash-kartu dash-kartu-hover p-4">
            <div class="flex items-start justify-between gap-3">
                <span class="dash-kpi-label">Order Berjalan</span>
                <span class="dash-kpi-ikon bg-amber-50 text-amber-700">
                    <i data-lucide="activity" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="dash-kpi-nilai mt-2.5"><?= $angka($kpi['order_aktif']) ?></div>
            <div class="mt-2 text-[11px] text-slate-500">
                Belum selesai, dihitung dari seluruh periode
            </div>
        </div>
    </div>

    <!-- =============================================================
         TREN KUNJUNGAN + KOMPOSISI KATEGORI
         ============================================================= -->
    <div class="dash-seksi"><span>Tren Kunjungan</span></div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

        <div class="xl:col-span-2 dash-kartu p-5 min-w-0">
            <div class="dash-kepala">
                <div class="dash-kepala-kiri">
                    <span class="dash-kepala-ikon"><i data-lucide="trending-up" class="w-4 h-4"></i></span>
                    <div class="min-w-0">
                        <h2 class="dash-judul">Tren Kunjungan Harian</h2>
                        <p class="dash-subjudul mt-0.5">Order terdaftar per hari &bull; order batal tidak dihitung</p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="block text-lg font-black text-slate-900 leading-none" style="font-variant-numeric: tabular-nums"><?= $angka($kpi['kunjungan']) ?></span>
                    <span class="block text-[11px] text-slate-500 mt-0.5">total periode</span>
                </div>
            </div>

            <div class="h-64"><canvas id="grafikTren"></canvas></div>

            <details class="mt-3 pt-3 border-t border-slate-100 dash-sembunyi-cetak">
                <summary class="dash-rincian">
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 dash-rincian-ikon"></i> Lihat angka harian
                </summary>
                <div class="dash-gulir mt-2 rounded-xl border border-slate-200">
                    <table class="dash-tabel">
                        <thead class="bg-slate-50 sticky top-0">
                            <tr><th>Tanggal</th><th class="dash-angka">Kunjungan</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach (array_reverse($tren_kunjungan) as $t): ?>
                            <tr>
                                <td><?= $tglId($t['tanggal']) ?></td>
                                <td class="dash-angka font-semibold text-slate-900"><?= $angka($t['jumlah']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </details>
        </div>

        <div class="dash-kartu p-5 min-w-0 flex flex-col">
            <div class="dash-kepala">
                <div class="dash-kepala-kiri">
                    <span class="dash-kepala-ikon"><i data-lucide="layers" class="w-4 h-4"></i></span>
                    <div class="min-w-0">
                        <h2 class="dash-judul">Kategori Pemeriksaan</h2>
                        <p class="dash-subjudul mt-0.5">Komposisi order periode terpilih</p>
                    </div>
                </div>
            </div>

            <?php
            $totalKategori = array_sum(array_column($komposisi_kategori, 'jumlah'));
            $warnaKategori = ['#1baf7a', '#2a78d6', '#eb6834', '#6250d6'];
            ?>
            <?php if ($totalKategori === 0): ?>
                <div class="dash-kosong">
                    <i data-lucide="layers" class="w-6 h-6"></i>
                    <span>Belum ada order pada periode ini.</span>
                </div>
            <?php else: ?>
                <div class="space-y-3.5">
                    <?php foreach ($komposisi_kategori as $i => $k):
                        $persen = ($k['jumlah'] / $totalKategori) * 100;
                    ?>
                        <div>
                            <div class="flex items-center gap-2.5 text-xs mb-1.5">
                                <span class="w-2.5 h-2.5 rounded flex-shrink-0" style="background: <?= $warnaKategori[$i % 4] ?>"></span>
                                <span class="text-slate-600 flex-1 min-w-0 truncate"><?= html_escape($k['nama']) ?></span>
                                <span class="font-bold text-slate-900" style="font-variant-numeric: tabular-nums"><?= $angka($k['jumlah']) ?></span>
                                <span class="text-slate-400 w-12 text-right" style="font-variant-numeric: tabular-nums"><?= $desimal($persen) ?>%</span>
                            </div>
                            <div class="dash-meter">
                                <span style="width: <?= $persen ?>%; background: <?= $warnaKategori[$i % 4] ?>"></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-auto pt-4 border-t border-slate-100 flex items-center justify-between text-[11px]">
                    <span class="text-slate-500">Total order periode</span>
                    <span class="font-bold text-slate-900" style="font-variant-numeric: tabular-nums"><?= $angka($totalKategori) ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- =============================================================
         PERFORMA PENDAPATAN
         ============================================================= -->
    <div class="dash-seksi"><span>Performa Keuangan</span></div>

    <div class="dash-kartu p-5 min-w-0">
        <div class="dash-kepala">
            <div class="dash-kepala-kiri">
                <span class="dash-kepala-ikon"><i data-lucide="wallet" class="w-4 h-4"></i></span>
                <div class="min-w-0">
                    <h2 class="dash-judul">Performa Pendapatan <?= (int) $cfg['tren_bulan'] ?> Bulan Terakhir</h2>
                    <p class="dash-subjudul mt-0.5">
                        Dikelompokkan menurut tanggal registrasi &bull; terealisasi = pembayaran PAID,
                        piutang = tagihan penjamin dan belum bayar
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-5 flex-wrap">
                <div>
                    <span class="dash-kpi-label">Tagihan periode</span>
                    <span class="block text-sm font-black text-slate-900 mt-0.5" title="<?= $rp($kpi['tagihan']) ?>"><?= $rpPendek($kpi['tagihan']) ?></span>
                </div>
                <div>
                    <span class="dash-kpi-label">Komponen jasa</span>
                    <span class="block text-sm font-black text-slate-900 mt-0.5" title="<?= $rp($kpi['jasa']) ?>"><?= $rpPendek($kpi['jasa']) ?></span>
                </div>
                <div>
                    <span class="dash-kpi-label">BHP + KSO</span>
                    <span class="block text-sm font-black text-slate-900 mt-0.5" title="<?= $rp($kpi['beban']) ?>"><?= $rpPendek($kpi['beban']) ?></span>
                </div>
            </div>
        </div>

        <div class="h-64"><canvas id="grafikPendapatan"></canvas></div>

        <?php if (!empty($pendapatan_tipe)): ?>
            <div class="mt-4 pt-4 border-t border-slate-100">
                <span class="dash-kpi-label">Tagihan menurut kelompok tarif</span>
                <div class="mt-2.5 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                    <?php
                    $labelTarif = ['umum' => 'Umum', 'perusahaan' => 'Perusahaan', 'bpjs' => 'BPJS', 'asuransi' => 'Asuransi'];
                    foreach ($pendapatan_tipe as $kt):
                    ?>
                        <div class="rounded-xl border border-slate-200 bg-slate-50/70 px-3.5 py-2.5">
                            <span class="block text-[11px] font-semibold text-slate-500"><?= html_escape($labelTarif[$kt['kelompok_tarif']] ?? ucfirst((string) $kt['kelompok_tarif'])) ?></span>
                            <span class="block text-sm font-black text-slate-900 mt-0.5" title="<?= $rp($kt['nilai']) ?>"><?= $rpPendek($kt['nilai']) ?></span>
                            <span class="block text-[11px] text-slate-500 mt-0.5">
                                <?= $angka($kt['kunjungan']) ?> kunjungan<?= (float) $kt['diskon'] > 0 ? ' &bull; diskon ' . $rpPendek($kt['diskon']) : '' ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <details class="mt-4 pt-3 border-t border-slate-100 dash-sembunyi-cetak">
            <summary class="dash-rincian">
                <i data-lucide="chevron-down" class="w-3.5 h-3.5 dash-rincian-ikon"></i> Lihat angka per bulan
            </summary>
            <div class="mt-2 rounded-xl border border-slate-200 overflow-auto">
                <table class="dash-tabel">
                    <thead class="bg-slate-50">
                        <tr>
                            <th>Bulan</th>
                            <th class="dash-angka">Kunjungan</th>
                            <th class="dash-angka">Terealisasi</th>
                            <th class="dash-angka">Piutang</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($tren_pendapatan as $b): ?>
                        <tr>
                            <td class="font-semibold text-slate-700"><?= html_escape($b['label']) ?></td>
                            <td class="dash-angka"><?= $angka($b['kunjungan']) ?></td>
                            <td class="dash-angka font-semibold text-slate-900"><?= $rp($b['realisasi']) ?></td>
                            <td class="dash-angka"><?= $rp($b['piutang']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </details>
    </div>

    <!-- =============================================================
         UTILISASI PEMERIKSAAN + TAHAPAN ORDER
         ============================================================= -->
    <div class="dash-seksi"><span>Utilisasi &amp; Alur Kerja</span></div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">

        <div class="dash-kartu p-5 min-w-0">
            <div class="dash-kepala">
                <div class="dash-kepala-kiri">
                    <span class="dash-kepala-ikon"><i data-lucide="bar-chart-3" class="w-4 h-4"></i></span>
                    <div class="min-w-0">
                        <h2 class="dash-judul">Pemeriksaan Paling Sering Diminta</h2>
                        <p class="dash-subjudul mt-0.5">Peringkat <?= (int) $cfg['top_limit'] ?> teratas pada periode terpilih</p>
                    </div>
                </div>
            </div>
            <?php if (empty($top_pemeriksaan)): ?>
                <div class="dash-kosong">
                    <i data-lucide="bar-chart-3" class="w-6 h-6"></i>
                    <span>Belum ada parameter yang diuji pada periode ini.</span>
                </div>
            <?php else: ?>
                <div style="height: <?= max(190, count($top_pemeriksaan) * 30) ?>px"><canvas id="grafikTop"></canvas></div>
            <?php endif; ?>
        </div>

        <div class="dash-kartu p-5 min-w-0">
            <div class="dash-kepala">
                <div class="dash-kepala-kiri">
                    <span class="dash-kepala-ikon"><i data-lucide="git-branch" class="w-4 h-4"></i></span>
                    <div class="min-w-0">
                        <h2 class="dash-judul">Tahapan Order &amp; Waktu Antar Tahap</h2>
                        <p class="dash-subjudul mt-0.5">Posisi seluruh order yang terdaftar pada periode terpilih</p>
                    </div>
                </div>
            </div>

            <div class="h-40"><canvas id="grafikStatus"></canvas></div>

            <div class="mt-4 pt-4 border-t border-slate-100 grid grid-cols-2 gap-x-4 gap-y-3">
                <div>
                    <span class="dash-kpi-label">Registrasi &rarr; sampling</span>
                    <span class="block text-base font-black text-slate-900 mt-0.5"><?= $jam($tat_tahap['registrasi_sampling']) ?></span>
                </div>
                <div>
                    <span class="dash-kpi-label">Sampling &rarr; hasil selesai</span>
                    <span class="block text-base font-black text-slate-900 mt-0.5"><?= $jam($tat_tahap['sampling_hasil']) ?></span>
                </div>
                <div>
                    <span class="dash-kpi-label">Belum dicatat sampling</span>
                    <span class="block text-base font-black mt-0.5 <?= $tat_tahap['tanpa_sampling'] > 0 ? 'text-amber-700' : 'text-slate-900' ?>">
                        <?= $angka($tat_tahap['tanpa_sampling']) ?> <span class="text-xs font-bold text-slate-400">order</span>
                    </span>
                </div>
                <div>
                    <span class="dash-kpi-label">Belum ada hasil</span>
                    <span class="block text-base font-black mt-0.5 <?= $tat_tahap['tanpa_hasil'] > 0 ? 'text-amber-700' : 'text-slate-900' ?>">
                        <?= $angka($tat_tahap['tanpa_hasil']) ?> <span class="text-xs font-bold text-slate-400">order</span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- =============================================================
         TAT PER KATEGORI
         ============================================================= -->
    <div class="dash-seksi"><span>Mutu Layanan</span></div>

    <div class="dash-kartu p-5 min-w-0">
        <div class="dash-kepala">
            <div class="dash-kepala-kiri">
                <span class="dash-kepala-ikon"><i data-lucide="timer" class="w-4 h-4"></i></span>
                <div class="min-w-0">
                    <h2 class="dash-judul">Ketepatan Turn Around Time per Kategori</h2>
                    <p class="dash-subjudul mt-0.5">Dihitung dari registrasi sampai hasil selesai, hanya order yang sudah punya waktu hasil</p>
                </div>
            </div>
            <a href="<?= site_url('pengaturan') ?>" class="text-[11px] font-bold text-emerald-800 hover:text-emerald-900 inline-flex items-center gap-1 dash-sembunyi-cetak">
                <i data-lucide="settings-2" class="w-3.5 h-3.5"></i> Ubah target TAT
            </a>
        </div>

        <?php if (empty($tat_kategori)): ?>
            <div class="dash-kosong">
                <i data-lucide="timer-off" class="w-6 h-6"></i>
                <span>Belum ada order yang selesai pada periode ini, sehingga TAT belum dapat dihitung.</span>
            </div>
        <?php else: ?>
            <div class="mt-3 overflow-auto">
                <table class="dash-tabel">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th class="dash-angka">Order Selesai</th>
                            <th class="dash-angka">TAT Rata-rata</th>
                            <th class="dash-angka">Target</th>
                            <th style="width: 34%">Ketepatan</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($tat_kategori as $t):
                        $warna = $t['ketepatan'] >= 90 ? '#047857' : ($t['ketepatan'] >= 75 ? '#b45309' : '#b91c1c');
                        $ikon = $t['ketepatan'] >= 90 ? 'check-circle-2' : ($t['ketepatan'] >= 75 ? 'alert-triangle' : 'alert-octagon');
                    ?>
                        <tr>
                            <td class="font-bold text-slate-900"><?= html_escape($t['nama']) ?></td>
                            <td class="dash-angka"><?= $angka($t['selesai']) ?></td>
                            <td class="dash-angka font-semibold text-slate-900"><?= $jam($t['rata_jam']) ?></td>
                            <td class="dash-angka text-slate-400"><?= $jam($t['target_jam']) ?></td>
                            <td>
                                <div class="flex items-center gap-2.5">
                                    <div class="dash-meter flex-1">
                                        <span style="width: <?= min(100, (float) $t['ketepatan']) ?>%; background: <?= $warna ?>"></span>
                                    </div>
                                    <i data-lucide="<?= $ikon ?>" class="w-3.5 h-3.5 flex-shrink-0" style="color: <?= $warna ?>"></i>
                                    <span class="font-bold flex-shrink-0" style="color: <?= $warna ?>; font-variant-numeric: tabular-nums"><?= $desimal($t['ketepatan']) ?>%</span>
                                    <span class="text-slate-400 text-[11px] flex-shrink-0" style="font-variant-numeric: tabular-nums"><?= $angka($t['tepat']) ?>/<?= $angka($t['selesai']) ?></span>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- =============================================================
         ORDER TERTUNDA
         ============================================================= -->
    <div class="dash-kartu p-5 min-w-0">
        <div class="dash-kepala">
            <div class="dash-kepala-kiri">
                <span class="dash-kepala-ikon"><i data-lucide="hourglass" class="w-4 h-4"></i></span>
                <div class="min-w-0">
                    <h2 class="dash-judul">Order Paling Lama Menunggu</h2>
                    <p class="dash-subjudul mt-0.5">Order yang belum selesai, diurutkan dari yang paling lama sejak registrasi</p>
                </div>
            </div>
            <a href="<?= site_url('pendaftaran') ?>" class="text-[11px] font-bold text-emerald-800 hover:text-emerald-900 inline-flex items-center gap-1 dash-sembunyi-cetak">
                <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Buka Pendaftaran
            </a>
        </div>

        <?php if (empty($order_tertunda)): ?>
            <div class="mt-3 flex items-center gap-2.5 text-xs text-emerald-800 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3">
                <i data-lucide="check-circle-2" class="w-4 h-4 flex-shrink-0"></i>
                <span>Tidak ada order yang menggantung &mdash; seluruh order sudah selesai atau dibatalkan.</span>
            </div>
        <?php else: ?>
            <div class="mt-3 overflow-auto">
                <table class="dash-tabel">
                    <thead>
                        <tr>
                            <th>No. Registrasi</th>
                            <th>Pasien / Sampel</th>
                            <th>Tahap</th>
                            <th class="dash-angka">Menunggu</th>
                            <th class="dash-angka">Target</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $labelStatus = [
                        'registered' => 'Terdaftar', 'sampling' => 'Sampling',
                        'processing' => 'Pengujian', 'validasi' => 'Validasi',
                    ];
                    $gayaTat = [
                        'terlewat'  => ['bg-rose-50 text-rose-700', '#b91c1c', 'alert-octagon', 'Lewat target'],
                        'mendekati' => ['bg-amber-50 text-amber-700', '#b45309', 'alert-triangle', 'Mendekati'],
                        'aman'      => ['bg-emerald-50 text-emerald-700', '#047857', 'check-circle-2', 'Dalam target'],
                    ];
                    foreach ($order_tertunda as $o):
                        list($kelas, $warna, $ikon, $teks) = $gayaTat[$o['status_tat']];
                    ?>
                        <tr>
                            <td class="font-mono text-[11px] font-bold text-slate-900 whitespace-nowrap"><?= html_escape($o['nomor_registrasi']) ?></td>
                            <td>
                                <span class="block font-bold text-slate-900"><?= html_escape($o['nama_pasien']) ?></span>
                                <span class="block text-[11px] text-slate-500">
                                    <?= html_escape(ucfirst((string) $o['kategori_pemeriksaan'])) ?><?= $o['jenis_sampel'] ? ' &bull; ' . html_escape($o['jenis_sampel']) : '' ?>
                                </span>
                            </td>
                            <td class="whitespace-nowrap"><?= html_escape($labelStatus[$o['status_order']] ?? $o['status_order']) ?></td>
                            <td class="dash-angka font-bold text-slate-900 whitespace-nowrap"><?= $jam($o['umur_jam']) ?></td>
                            <td class="dash-angka text-slate-400 whitespace-nowrap"><?= $jam($o['target_jam']) ?></td>
                            <td class="whitespace-nowrap">
                                <span class="dash-chip <?= $kelas ?>">
                                    <i data-lucide="<?= $ikon ?>" class="w-3 h-3"></i>
                                    <?= $teks ?> &middot; <?= $angka($o['persen_target']) ?>%
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- =============================================================
         PERINGATAN LOGISTIK, ALAT, DAN MUTU HASIL
         ============================================================= -->
    <div class="dash-seksi"><span>Peringatan Operasional</span></div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

        <!-- Stok kritis -->
        <div class="dash-kartu p-5 min-w-0">
            <div class="dash-kepala">
                <div class="dash-kepala-kiri">
                    <span class="dash-kepala-ikon"><i data-lucide="package" class="w-4 h-4"></i></span>
                    <div class="min-w-0">
                        <h2 class="dash-judul">Stok Reagen &amp; BHP Kritis</h2>
                        <?php if ($peringatan['gudang']): ?>
                            <p class="dash-subjudul mt-0.5">
                                <?= $angka($peringatan['gudang']['perlu_pesan']) ?> barang perlu dipesan &bull;
                                <?= $angka($peringatan['gudang']['batch_perlu_perhatian']) ?> batch perlu perhatian
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
                <a href="<?= site_url('gudang') ?>" class="text-[11px] font-bold text-emerald-800 hover:text-emerald-900 flex-shrink-0 dash-sembunyi-cetak">Gudang</a>
            </div>

            <?php if (empty($peringatan['stok_kritis'])): ?>
                <div class="dash-kosong">
                    <i data-lucide="package-check" class="w-6 h-6"></i>
                    <span>Tidak ada stok di bawah batas minimum.</span>
                </div>
            <?php else: ?>
                <ul class="mt-3 divide-y divide-slate-100">
                    <?php
                    $gayaStok = [
                        'habis'   => ['bg-rose-50 text-rose-700', '#b91c1c', 'Habis'],
                        'kritis'  => ['bg-orange-50 text-orange-700', '#c2410c', 'Kritis'],
                        'menipis' => ['bg-amber-50 text-amber-700', '#b45309', 'Menipis'],
                    ];
                    foreach ($peringatan['stok_kritis'] as $s):
                        list($kelas, $warna, $teks) = $gayaStok[$s['status_stok']] ?? ['bg-amber-50 text-amber-700', '#b45309', 'Perhatian'];
                    ?>
                        <li class="flex items-center gap-2.5 py-2.5 first:pt-0 last:pb-0">
                            <span class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 <?= $kelas ?>">
                                <i data-lucide="package" class="w-3.5 h-3.5"></i>
                            </span>
                            <div class="flex-1 min-w-0">
                                <span class="block text-xs font-bold text-slate-900 truncate"><?= html_escape($s['nama_barang']) ?></span>
                                <span class="block text-[11px] text-slate-500">
                                    Sisa <?= $angka($s['stok']) ?> <?= html_escape($s['satuan']) ?> &bull; minimum <?= $angka($s['stok_minimum']) ?>
                                </span>
                            </div>
                            <span class="dash-chip <?= $kelas ?> flex-shrink-0"><?= $teks ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <!-- Kalibrasi alat -->
        <div class="dash-kartu p-5 min-w-0">
            <div class="dash-kepala">
                <div class="dash-kepala-kiri">
                    <span class="dash-kepala-ikon"><i data-lucide="microscope" class="w-4 h-4"></i></span>
                    <div class="min-w-0">
                        <h2 class="dash-judul">Kalibrasi Alat</h2>
                        <?php if ($peringatan['aset']): ?>
                            <p class="dash-subjudul mt-0.5">
                                <?= $angka($peringatan['aset']['wajib_kalibrasi']) ?> alat wajib kalibrasi &bull;
                                <?= $angka($peringatan['aset']['tidak_laik']) ?> tidak laik pakai
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
                <a href="<?= site_url('aset/kalibrasi') ?>" class="text-[11px] font-bold text-emerald-800 hover:text-emerald-900 flex-shrink-0 dash-sembunyi-cetak">Aset</a>
            </div>

            <?php if (empty($peringatan['kalibrasi'])): ?>
                <div class="dash-kosong">
                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                    <span>Seluruh alat wajib kalibrasi masih dalam masa berlaku.</span>
                </div>
            <?php else: ?>
                <ul class="mt-3 divide-y divide-slate-100">
                    <?php
                    $gayaKal = [
                        'tidak_laik'   => ['bg-rose-50 text-rose-700', 'Tidak laik'],
                        'kedaluwarsa'  => ['bg-rose-50 text-rose-700', 'Kedaluwarsa'],
                        'belum_pernah' => ['bg-orange-50 text-orange-700', 'Belum pernah'],
                        'kritis'       => ['bg-orange-50 text-orange-700', 'Kritis'],
                        'peringatan'   => ['bg-amber-50 text-amber-700', 'Segera'],
                    ];
                    foreach ($peringatan['kalibrasi'] as $a):
                        $st = $a['kalibrasi']['status'];
                        list($kelas, $teks) = $gayaKal[$st] ?? ['bg-amber-50 text-amber-700', 'Perhatian'];
                    ?>
                        <li class="flex items-center gap-2.5 py-2.5 first:pt-0 last:pb-0">
                            <span class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 <?= $kelas ?>">
                                <i data-lucide="microscope" class="w-3.5 h-3.5"></i>
                            </span>
                            <div class="flex-1 min-w-0">
                                <span class="block text-xs font-bold text-slate-900 truncate"><?= html_escape($a['nama_aset']) ?></span>
                                <span class="block text-[11px] text-slate-500 truncate">
                                    <?= html_escape($a['nomor_register']) ?><?= !empty($a['lokasi_ruangan']) ? ' &bull; ' . html_escape($a['lokasi_ruangan']) : '' ?>
                                </span>
                            </div>
                            <span class="dash-chip <?= $kelas ?> flex-shrink-0"><?= $teks ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <!-- Mutu hasil -->
        <div class="dash-kartu p-5 min-w-0">
            <div class="dash-kepala">
                <div class="dash-kepala-kiri">
                    <span class="dash-kepala-ikon"><i data-lucide="file-check-2" class="w-4 h-4"></i></span>
                    <div class="min-w-0">
                        <h2 class="dash-judul">Sebaran Hasil terhadap Nilai Rujukan</h2>
                        <p class="dash-subjudul mt-0.5">Parameter yang sudah terisi hasilnya pada periode terpilih</p>
                    </div>
                </div>
            </div>

            <?php
            $totalFlag = array_sum(array_column($hasil_abnormal, 'jumlah'));
            $gayaFlag = [
                'normal'   => ['#047857', 'check-circle-2'],
                'low'      => ['#b45309', 'arrow-down-circle'],
                'high'     => ['#c2410c', 'arrow-up-circle'],
                'critical' => ['#b91c1c', 'alert-octagon'],
            ];
            ?>
            <?php if ($totalFlag === 0): ?>
                <div class="dash-kosong">
                    <i data-lucide="file-search" class="w-6 h-6"></i>
                    <span>Belum ada hasil pemeriksaan yang diinput pada periode ini.</span>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($hasil_abnormal as $f):
                        list($warna, $ikon) = $gayaFlag[$f['flag']];
                        $persen = ($f['jumlah'] / $totalFlag) * 100;
                    ?>
                        <div>
                            <div class="flex items-center gap-2 text-xs mb-1.5">
                                <i data-lucide="<?= $ikon ?>" class="w-3.5 h-3.5 flex-shrink-0" style="color: <?= $warna ?>"></i>
                                <span class="text-slate-600 flex-1 min-w-0 truncate"><?= html_escape($f['nama']) ?></span>
                                <span class="font-bold text-slate-900" style="font-variant-numeric: tabular-nums"><?= $angka($f['jumlah']) ?></span>
                                <span class="text-slate-400 w-12 text-right" style="font-variant-numeric: tabular-nums"><?= $desimal($persen) ?>%</span>
                            </div>
                            <div class="dash-meter">
                                <span style="width: <?= $persen ?>%; background: <?= $warna ?>"></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <p class="text-[11px] text-slate-400 text-center pt-1 pb-2">
        Seluruh angka dihitung ulang setiap halaman dibuka &bull;
        target TAT dan ambang peringatan dapat diubah di Pengaturan Sistem
    </p>
</div>

<script src="<?= asset_url('plugins/chartjs/chart.umd.min.js') ?>"></script>
<script>
(function () {
    if (typeof Chart === 'undefined') { return; }

    // Token warna. Aplikasi hanya bermode terang, jadi satu set nilai.
    var INK = '#0f172a', INK_2 = '#475569', INK_MUTED = '#94a3b8';
    var GRID = '#f1f5f9', SURFACE = '#ffffff';
    var SERI_UTAMA = '#047857';                 // satu seri: emerald tua, kontras 5,5:1
    var KATEGORI = ['#1baf7a', '#2a78d6', '#eb6834', '#6250d6'];
    var TAHAPAN = ['#86b6ef', '#5598e7', '#3987e5', '#2a78d6', '#256abf', '#cbd5e1'];

    Chart.defaults.font.family = '"Segoe UI", system-ui, sans-serif';
    Chart.defaults.font.size = 11;
    Chart.defaults.color = INK_MUTED;

    var tooltip = {
        backgroundColor: 'rgba(15, 23, 42, .94)',
        padding: 10,
        cornerRadius: 10,
        titleFont: { weight: '700', size: 11 },
        bodyFont: { size: 11 },
        bodySpacing: 4,
        displayColors: true,
        boxWidth: 8,
        boxHeight: 8,
        boxPadding: 4,
        usePointStyle: true
    };

    var sumbuY = {
        grid: { color: GRID, drawTicks: false },
        border: { display: false },
        ticks: { padding: 8 }
    };
    var sumbuX = {
        grid: { display: false },
        border: { color: '#e2e8f0' },
        ticks: { padding: 6, maxRotation: 0, autoSkipPadding: 14 }
    };

    function ribuan(n) { return new Intl.NumberFormat('id-ID').format(n); }
    function rupiahPendek(n) {
        if (Math.abs(n) >= 1e9) return 'Rp ' + (n / 1e9).toFixed(1).replace('.', ',') + ' M';
        if (Math.abs(n) >= 1e6) return 'Rp ' + (n / 1e6).toFixed(1).replace('.', ',') + ' jt';
        if (Math.abs(n) >= 1e3) return 'Rp ' + (n / 1e3).toFixed(0) + ' rb';
        return 'Rp ' + ribuan(n);
    }
    function rupiah(n) { return 'Rp ' + ribuan(Math.round(n)); }

    // Label langsung di ujung batang: wajib ada karena beberapa warna batang
    // berada di bawah rasio kontras 3:1 terhadap latar putih.
    var labelUjung = {
        id: 'labelUjung',
        afterDatasetsDraw: function (chart) {
            var ctx = chart.ctx;
            ctx.save();
            ctx.font = '700 10px "Segoe UI", system-ui, sans-serif';
            ctx.fillStyle = INK_2;
            ctx.textBaseline = 'middle';
            chart.data.datasets.forEach(function (ds, di) {
                var meta = chart.getDatasetMeta(di);
                if (meta.hidden) { return; }
                meta.data.forEach(function (el, i) {
                    var nilai = ds.data[i];
                    if (!nilai) { return; }
                    if (chart.options.indexAxis === 'y') {
                        ctx.textAlign = 'left';
                        ctx.fillText(ribuan(nilai), el.x + 7, el.y);
                    } else {
                        ctx.textAlign = 'center';
                        ctx.fillText(ribuan(nilai), el.x, el.y - 8);
                    }
                });
            });
            ctx.restore();
        }
    };

    // Garis bantu vertikal mengikuti kursor pada grafik garis.
    var garisBantu = {
        id: 'garisBantu',
        afterDraw: function (chart) {
            var aktif = chart.tooltip && chart.tooltip.getActiveElements ? chart.tooltip.getActiveElements() : [];
            if (!aktif.length) { return; }
            var x = aktif[0].element.x;
            var area = chart.chartArea;
            var ctx = chart.ctx;
            ctx.save();
            ctx.strokeStyle = '#cbd5e1';
            ctx.lineWidth = 1;
            ctx.setLineDash([4, 4]);
            ctx.beginPath();
            ctx.moveTo(x, area.top);
            ctx.lineTo(x, area.bottom);
            ctx.stroke();
            ctx.restore();
        }
    };

    // ---------------------------------------------------------------
    // 1. Tren kunjungan harian - satu seri, tanpa legenda
    // ---------------------------------------------------------------
    var tren = <?= json_encode($tren_kunjungan) ?>;
    var elTren = document.getElementById('grafikTren');
    if (elTren && tren.length) {
        var ctxTren = elTren.getContext('2d');
        var gradasi = ctxTren.createLinearGradient(0, 0, 0, 240);
        gradasi.addColorStop(0, 'rgba(4, 120, 87, .18)');
        gradasi.addColorStop(1, 'rgba(4, 120, 87, 0)');

        new Chart(elTren, {
            type: 'line',
            data: {
                labels: tren.map(function (t) {
                    var d = new Date(t.tanggal + 'T00:00:00');
                    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
                }),
                datasets: [{
                    label: 'Kunjungan',
                    data: tren.map(function (t) { return t.jumlah; }),
                    borderColor: SERI_UTAMA,
                    backgroundColor: gradasi,
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBorderWidth: 2,
                    pointHoverBorderColor: SURFACE,
                    pointHoverBackgroundColor: SERI_UTAMA
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: Object.assign({}, tooltip, {
                        callbacks: { label: function (c) { return ' ' + ribuan(c.parsed.y) + ' kunjungan'; } }
                    })
                },
                scales: {
                    x: sumbuX,
                    y: Object.assign({}, sumbuY, { beginAtZero: true, ticks: { precision: 0, padding: 8 } })
                }
            },
            plugins: [garisBantu]
        });
    }

    // Catatan: komposisi kategori digambar sebagai meter HTML di dalam kartunya,
    // bukan kanvas, supaya kategori bernilai nol tetap terlihat sebagai baris.

    // ---------------------------------------------------------------
    // 2. Performa pendapatan - dua seri satuan rupiah, satu sumbu
    // ---------------------------------------------------------------
    var pendapatan = <?= json_encode($tren_pendapatan) ?>;
    var elPdp = document.getElementById('grafikPendapatan');
    if (elPdp && pendapatan.length) {
        new Chart(elPdp, {
            type: 'bar',
            data: {
                labels: pendapatan.map(function (p) { return p.label; }),
                datasets: [
                    {
                        label: 'Terealisasi',
                        data: pendapatan.map(function (p) { return p.realisasi; }),
                        backgroundColor: '#047857',
                        borderRadius: { topLeft: 4, topRight: 4 },
                        borderSkipped: false,
                        borderColor: SURFACE,
                        borderWidth: { top: 0, left: 1, right: 1, bottom: 0 },
                        barPercentage: 0.92,
                        categoryPercentage: 0.66
                    },
                    {
                        label: 'Piutang',
                        data: pendapatan.map(function (p) { return p.piutang; }),
                        backgroundColor: '#eda100',
                        borderRadius: { topLeft: 4, topRight: 4 },
                        borderSkipped: false,
                        borderColor: SURFACE,
                        borderWidth: { top: 0, left: 1, right: 1, bottom: 0 },
                        barPercentage: 0.92,
                        categoryPercentage: 0.66
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: { boxWidth: 8, boxHeight: 8, usePointStyle: true, pointStyle: 'rectRounded', color: INK_2, padding: 16 }
                    },
                    tooltip: Object.assign({}, tooltip, {
                        callbacks: {
                            label: function (c) { return ' ' + c.dataset.label + ': ' + rupiah(c.parsed.y); },
                            footer: function (items) {
                                var total = items.reduce(function (a, i) { return a + i.parsed.y; }, 0);
                                return 'Total tagihan: ' + rupiah(total);
                            }
                        }
                    })
                },
                scales: {
                    x: sumbuX,
                    y: Object.assign({}, sumbuY, {
                        beginAtZero: true,
                        ticks: { padding: 8, callback: function (v) { return rupiahPendek(v); } }
                    })
                }
            }
        });
    }

    // ---------------------------------------------------------------
    // 4. Pemeriksaan terpopuler - satu rona, label langsung
    // ---------------------------------------------------------------
    var top = <?= json_encode($top_pemeriksaan) ?>;
    var elTop = document.getElementById('grafikTop');
    if (elTop && top.length) {
        new Chart(elTop, {
            type: 'bar',
            data: {
                labels: top.map(function (t) { return t.nama_item; }),
                datasets: [{
                    label: 'Permintaan',
                    data: top.map(function (t) { return Number(t.jumlah); }),
                    backgroundColor: '#2a78d6',
                    borderRadius: { topRight: 4, bottomRight: 4 },
                    borderSkipped: false,
                    barPercentage: 0.6,
                    categoryPercentage: 0.88
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { right: 34 } },
                plugins: {
                    legend: { display: false },
                    tooltip: Object.assign({}, tooltip, {
                        callbacks: {
                            label: function (c) {
                                var baris = top[c.dataIndex];
                                return [' ' + ribuan(c.parsed.x) + ' permintaan', ' Nilai: ' + rupiah(Number(baris.nilai))];
                            },
                            title: function (items) {
                                var baris = top[items[0].dataIndex];
                                return baris.nama_item + (baris.kategori_lab ? ' (' + baris.kategori_lab + ')' : '');
                            }
                        }
                    })
                },
                scales: {
                    x: { display: false, beginAtZero: true },
                    y: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: {
                            color: INK_2,
                            callback: function (v) {
                                var t = this.getLabelForValue(v);
                                return t.length > 28 ? t.slice(0, 27) + '…' : t;
                            }
                        }
                    }
                }
            },
            plugins: [labelUjung]
        });
    }

    // ---------------------------------------------------------------
    // 5. Tahapan order - rona biru bertingkat (urutan proses)
    // ---------------------------------------------------------------
    var status = <?= json_encode($distribusi_status) ?>;
    var elSts = document.getElementById('grafikStatus');
    if (elSts && status.length) {
        new Chart(elSts, {
            type: 'bar',
            data: {
                labels: status.map(function (s) { return s.nama; }),
                datasets: [{
                    label: 'Order',
                    data: status.map(function (s) { return s.jumlah; }),
                    backgroundColor: status.map(function (s, i) { return TAHAPAN[i % TAHAPAN.length]; }),
                    borderRadius: { topRight: 4, bottomRight: 4 },
                    borderSkipped: false,
                    barPercentage: 0.62,
                    categoryPercentage: 0.88
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { right: 34 } },
                plugins: {
                    legend: { display: false },
                    tooltip: Object.assign({}, tooltip, {
                        callbacks: { label: function (c) { return ' ' + ribuan(c.parsed.x) + ' order'; } }
                    })
                },
                scales: {
                    x: { display: false, beginAtZero: true },
                    y: { grid: { display: false }, border: { display: false }, ticks: { color: INK_2 } }
                }
            },
            plugins: [labelUjung]
        });
    }

    if (window.lucide) { lucide.createIcons(); }
})();
</script>
