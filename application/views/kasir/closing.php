<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="space-y-5">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="<?= site_url('dashboard') ?>" class="hover:text-emerald-700 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-slate-400">Kasir</span>
                <span>/</span>
                <span class="text-emerald-800 font-semibold">Closing Harian</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="calculator" class="w-6 h-6"></i>
                </span>
                <span>Closing Pendapatan Kasir Harian</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Rekap penerimaan per akun bayar, rekonsiliasi uang tunai fisik, lalu tutup kasir</p>
        </div>
        <a href="<?= site_url('kasir') ?>" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-bold flex items-center gap-2">
            <i data-lucide="wallet-cards" class="w-4 h-4"></i><span>Antrian Kasir</span>
        </a>
    </div>

    <!-- FILTER TANGGAL -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="<?= site_url('kasir/closing') ?>" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="lis-form-label">Tanggal Transaksi</label>
                <input type="date" name="tanggal" value="<?= html_escape($tanggal) ?>" max="<?= date('Y-m-d') ?>" class="lis-form-control text-xs font-medium">
            </div>
            <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold flex items-center gap-1.5">
                <i data-lucide="filter" class="w-3.5 h-3.5"></i><span>Tampilkan</span>
            </button>
        </form>
    </div>

    <!-- KARTU RINGKASAN -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-semibold text-slate-500 block">Transaksi Belum Closing</span>
            <span class="text-xl font-extrabold text-slate-900"><?= number_format($preview['jumlah']) ?></span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-emerald-200 bg-emerald-50/20 shadow-xs">
            <span class="text-[11px] font-semibold text-emerald-800 block">Total Pendapatan</span>
            <span class="text-xl font-extrabold text-emerald-950">Rp <?= number_format($preview['total'], 0, ',', '.') ?></span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-amber-200 bg-amber-50/20 shadow-xs">
            <span class="text-[11px] font-semibold text-amber-800 block">Tunai (Harus di Laci)</span>
            <span class="text-xl font-extrabold text-amber-950">Rp <?= number_format($preview['tunai'], 0, ',', '.') ?></span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-teal-200 bg-teal-50/20 shadow-xs">
            <span class="text-[11px] font-semibold text-teal-800 block">Non-Tunai</span>
            <span class="text-xl font-extrabold text-teal-950">Rp <?= number_format($preview['non_tunai'], 0, ',', '.') ?></span>
        </div>
    </div>

    <?php if ($preview['sudah_closing'] > 0): ?>
    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-300 text-xs text-slate-700">
        Tanggal ini sudah pernah di-closing <?= (int)$preview['sudah_closing'] ?>x. Hanya transaksi baru setelah closing terakhir yang tampil di bawah.
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- REKAP PER AKUN + DETAIL -->
        <div class="lg:col-span-2 space-y-5">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 font-bold text-sm text-slate-900">Rekap per Akun Bayar</div>
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="text-left px-4 py-2.5 font-bold">Akun Bayar</th>
                            <th class="text-left px-4 py-2.5 font-bold">Tipe</th>
                            <th class="text-right px-4 py-2.5 font-bold">Jumlah</th>
                            <th class="text-right px-4 py-2.5 font-bold">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    <?php if (empty($preview['per_akun'])): ?>
                        <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">Belum ada transaksi lunas yang menunggu closing.</td></tr>
                    <?php else: foreach ($preview['per_akun'] as $a): ?>
                        <tr>
                            <td class="px-4 py-2.5 font-semibold text-slate-800"><?= html_escape($a['akun']) ?></td>
                            <td class="px-4 py-2.5 text-slate-500 uppercase"><?= html_escape($a['tipe']) ?></td>
                            <td class="px-4 py-2.5 text-right"><?= (int)$a['jumlah'] ?></td>
                            <td class="px-4 py-2.5 text-right font-mono font-bold">Rp <?= number_format($a['total'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 font-bold text-sm text-slate-900">Rincian Transaksi</div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="text-left px-4 py-2.5 font-bold">Kuitansi</th>
                                <th class="text-left px-4 py-2.5 font-bold">Pasien</th>
                                <th class="text-left px-4 py-2.5 font-bold">Jam</th>
                                <th class="text-left px-4 py-2.5 font-bold">Channel</th>
                                <th class="text-right px-4 py-2.5 font-bold">Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                        <?php foreach ($preview['rows'] as $r): ?>
                            <tr>
                                <td class="px-4 py-2 font-mono text-slate-700"><?= html_escape($r['nomor_kuitansi']) ?></td>
                                <td class="px-4 py-2 font-semibold text-slate-800"><?= html_escape($r['nama_pasien']) ?></td>
                                <td class="px-4 py-2 text-slate-500"><?= html_escape(substr($r['waktu_bayar'], 11, 5)) ?></td>
                                <td class="px-4 py-2 text-slate-600"><?= html_escape($r['nama_akun']) ?></td>
                                <td class="px-4 py-2 text-right font-mono font-bold">Rp <?= number_format($r['total_biaya'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- FORM CLOSING -->
        <div class="space-y-5">
            <div class="bg-white p-5 rounded-2xl border border-emerald-200 shadow-xs space-y-3.5">
                <h2 class="font-bold text-sm text-slate-900">Tutup Kasir</h2>
                <div class="p-2.5 bg-amber-50/60 rounded-xl border border-amber-200 text-xs flex justify-between">
                    <span class="text-slate-600">Tunai seharusnya:</span>
                    <span class="font-mono font-black text-amber-950" id="cls-expected" data-val="<?= (float)$preview['tunai'] ?>">Rp <?= number_format($preview['tunai'], 0, ',', '.') ?></span>
                </div>
                <div>
                    <label class="lis-form-label">Uang Tunai Fisik di Laci (Rp) <span class="req">*</span></label>
                    <input type="number" id="cls-fisik" min="0" oninput="calcSelisih()" class="lis-form-control text-sm font-mono font-black">
                </div>
                <div class="p-2.5 rounded-xl border border-slate-200 text-xs flex justify-between" id="cls-selisih-box">
                    <span class="text-slate-600">Selisih:</span>
                    <span class="font-mono font-black" id="cls-selisih">-</span>
                </div>
                <div>
                    <label class="lis-form-label">Catatan (wajib bila selisih)</label>
                    <textarea id="cls-catatan" rows="2" class="lis-form-control text-xs" placeholder="Penyebab selisih, dll."></textarea>
                </div>
                <?php if ($can_create): ?>
                <button type="button" id="cls-btn" onclick="submitClosing()" <?= $preview['jumlah'] === 0 ? 'disabled' : '' ?>
                        class="w-full py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-bold flex items-center justify-center gap-2">
                    <i data-lucide="lock" class="w-4 h-4 text-amber-300"></i><span>Simpan Closing Kasir</span>
                </button>
                <?php endif; ?>
            </div>

            <div class="bg-teal-50/40 p-4 rounded-2xl border border-teal-200 text-xs space-y-1">
                <span class="font-bold text-teal-900 block">Info Piutang Mitra (tidak masuk kas)</span>
                <span class="text-teal-800"><?= (int)$preview['piutang_count'] ?> tagihan dijamin PKS pada tanggal ini &bull; Rp <?= number_format($preview['piutang_total'], 0, ',', '.') ?></span>
            </div>
        </div>
    </div>

    <!-- RIWAYAT CLOSING -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100 font-bold text-sm text-slate-900">Riwayat Closing</div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="text-left px-4 py-2.5 font-bold">No. Closing</th>
                        <th class="text-left px-4 py-2.5 font-bold">Tanggal</th>
                        <th class="text-left px-4 py-2.5 font-bold">Kasir</th>
                        <th class="text-right px-4 py-2.5 font-bold">Trx</th>
                        <th class="text-right px-4 py-2.5 font-bold">Pendapatan</th>
                        <th class="text-right px-4 py-2.5 font-bold">Selisih Tunai</th>
                        <th class="text-right px-4 py-2.5 font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                <?php if (empty($history)): ?>
                    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">Belum ada riwayat closing.</td></tr>
                <?php else: foreach ($history as $h): ?>
                    <tr>
                        <td class="px-4 py-2.5 font-mono font-bold text-slate-800"><?= html_escape($h['nomor_closing']) ?></td>
                        <td class="px-4 py-2.5"><?= html_escape($h['tanggal']) ?></td>
                        <td class="px-4 py-2.5"><?= html_escape($h['kasir_nama']) ?></td>
                        <td class="px-4 py-2.5 text-right"><?= (int)$h['jumlah_transaksi'] ?></td>
                        <td class="px-4 py-2.5 text-right font-mono font-bold">Rp <?= number_format($h['total_pendapatan'], 0, ',', '.') ?></td>
                        <td class="px-4 py-2.5 text-right font-mono font-bold <?= (float)$h['selisih_tunai'] == 0 ? 'text-emerald-700' : 'text-rose-700' ?>">
                            <?= (float)$h['selisih_tunai'] == 0 ? 'Sesuai' : 'Rp ' . number_format($h['selisih_tunai'], 0, ',', '.') ?>
                        </td>
                        <td class="px-4 py-2.5 text-right">
                            <a href="<?= site_url('kasir/cetak_closing/' . (int)$h['id']) ?>" target="_blank" class="px-3 py-1.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 text-[11px] font-bold inline-flex items-center gap-1.5">
                                <i data-lucide="printer" class="w-3.5 h-3.5"></i><span>Cetak</span>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const rp = n => (n < 0 ? '-Rp ' : 'Rp ') + Math.abs(Math.round(n)).toLocaleString('id-ID');

function calcSelisih() {
    const expected = parseFloat(document.getElementById('cls-expected').dataset.val) || 0;
    const raw = document.getElementById('cls-fisik').value;
    const el = document.getElementById('cls-selisih');
    if (raw === '') { el.textContent = '-'; el.className = 'font-mono font-black'; return; }
    const diff = parseFloat(raw) - expected;
    el.textContent = diff === 0 ? 'Sesuai (Rp 0)' : rp(diff);
    el.className = 'font-mono font-black ' + (diff === 0 ? 'text-emerald-700' : 'text-rose-700');
}

function submitClosing() {
    const fisik = document.getElementById('cls-fisik').value;
    const catatan = document.getElementById('cls-catatan').value.trim();
    const expected = parseFloat(document.getElementById('cls-expected').dataset.val) || 0;

    if (fisik === '' || parseFloat(fisik) < 0) {
        LIS.alert('Peringatan', 'Isi uang tunai fisik di laci kasir (boleh 0).', 'warning');
        return;
    }
    if (parseFloat(fisik) !== expected && !catatan) {
        LIS.alert('Peringatan', 'Ada selisih tunai. Isi catatan penyebab selisih.', 'warning');
        return;
    }

    Swal.fire({
        title: 'Simpan closing kasir?',
        text: 'Transaksi yang masuk closing tidak dapat dibatalkan lagi.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Simpan',
        cancelButtonText: 'Batal'
    }).then(r => {
        if (!r.isConfirmed) return;
        const fd = new FormData();
        fd.append('tanggal', '<?= html_escape($tanggal) ?>');
        fd.append('uang_fisik_tunai', fisik);
        fd.append('catatan', catatan);
        document.getElementById('cls-btn').disabled = true;

        fetch('<?= site_url('kasir/simpan_closing') ?>', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json())
            .then(res => {
                if (res.status) {
                    LIS.toast('success', res.message);
                    window.open('<?= site_url('kasir/cetak_closing') ?>/' + res.closing_id, '_blank');
                    setTimeout(() => location.reload(), 900);
                } else {
                    document.getElementById('cls-btn').disabled = false;
                    LIS.alert('Gagal', res.message, 'error');
                }
            })
            .catch(() => {
                document.getElementById('cls-btn').disabled = false;
                LIS.alert('Error', 'Terjadi kesalahan jaringan.', 'error');
            });
    });
}
</script>
