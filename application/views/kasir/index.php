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
                <span class="text-emerald-800 font-semibold">Antrian Kasir</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                    <i data-lucide="wallet-cards" class="w-6 h-6"></i>
                </span>
                <span>Antrian Kasir Pembayaran Jasa Laboratorium</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Daftar tagihan pasien &amp; perusahaan yang sudah terdaftar, siap dibayar atau dijamin piutang</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="<?= site_url('kasir/closing') ?>" class="px-4 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-amber-200 text-xs font-bold flex items-center gap-2 shadow-sm transition-all border border-emerald-600">
                <i data-lucide="calculator" class="w-4 h-4 text-amber-300"></i>
                <span>Closing Harian</span>
            </a>
        </div>
    </div>

    <?php if ($summary['belum_closing_lampau'] > 0): ?>
    <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-300 text-xs text-amber-900 flex items-center gap-2.5">
        <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600 flex-shrink-0"></i>
        <span><strong><?= (int)$summary['belum_closing_lampau'] ?> transaksi</strong> dari hari sebelumnya belum di-closing. <a href="<?= site_url('kasir/closing') ?>" class="font-bold underline">Lakukan closing sekarang</a>.</span>
    </div>
    <?php endif; ?>

    <!-- KARTU RINGKASAN -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white p-4 rounded-2xl border border-amber-200 bg-amber-50/20 shadow-xs">
            <span class="text-[11px] font-semibold text-amber-800 block">Menunggu Kasir (UNPAID)</span>
            <span class="text-xl font-extrabold text-amber-950 leading-tight"><?= number_format($summary['unpaid_count']) ?></span>
            <span class="text-[10px] text-amber-700 block mt-0.5">Rp <?= number_format($summary['unpaid_total'], 0, ',', '.') ?></span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-emerald-200 bg-emerald-50/20 shadow-xs">
            <span class="text-[11px] font-semibold text-emerald-800 block">Lunas Hari Ini (Saya)</span>
            <span class="text-xl font-extrabold text-emerald-950 leading-tight"><?= number_format($summary['paid_count']) ?></span>
            <span class="text-[10px] text-emerald-700 block mt-0.5">Rp <?= number_format($summary['paid_total'], 0, ',', '.') ?></span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-teal-200 bg-teal-50/20 shadow-xs">
            <span class="text-[11px] font-semibold text-teal-800 block">Piutang Mitra Hari Ini</span>
            <span class="text-xl font-extrabold text-teal-950 leading-tight"><?= number_format($summary['piutang_count']) ?></span>
            <span class="text-[10px] text-teal-700 block mt-0.5">Rp <?= number_format($summary['piutang_total'], 0, ',', '.') ?></span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <span class="text-[11px] font-semibold text-slate-600 block">Belum Closing (Hari Lalu)</span>
            <span class="text-xl font-extrabold text-slate-900 leading-tight"><?= number_format($summary['belum_closing_lampau']) ?></span>
            <span class="text-[10px] text-slate-400 block mt-0.5">transaksi lunas</span>
        </div>
    </div>

    <!-- FILTER -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-xs">
        <form method="GET" action="<?= site_url('kasir') ?>" class="grid grid-cols-1 sm:grid-cols-4 lg:grid-cols-5 gap-3 items-end">
            <div>
                <label class="lis-form-label">Status</label>
                <select name="status" class="lis-form-control text-xs font-medium">
                    <option value="UNPAID" <?= $status === 'UNPAID' ? 'selected' : '' ?>>Menunggu Kasir (UNPAID)</option>
                    <option value="PAID" <?= $status === 'PAID' ? 'selected' : '' ?>>Lunas (PAID)</option>
                    <option value="GUARANTEED" <?= $status === 'GUARANTEED' ? 'selected' : '' ?>>Dijamin PKS (GUARANTEED)</option>
                    <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>Semua Status</option>
                </select>
            </div>
            <div>
                <label class="lis-form-label">Tanggal Daftar</label>
                <input type="date" name="tanggal" value="<?= html_escape($tanggal) ?>" class="lis-form-control text-xs font-medium">
            </div>
            <div>
                <label class="lis-form-label">Tipe Pelayanan</label>
                <select name="tipe_pasien" class="lis-form-control text-xs font-medium">
                    <option value="all" <?= $tipe_pasien === 'all' ? 'selected' : '' ?>>Semua Tipe</option>
                    <option value="mandiri" <?= $tipe_pasien === 'mandiri' ? 'selected' : '' ?>>Pasien Mandiri</option>
                    <option value="rujukan_faskes" <?= $tipe_pasien === 'rujukan_faskes' ? 'selected' : '' ?>>Rujukan Faskes</option>
                    <option value="perusahaan_lingkungan" <?= $tipe_pasien === 'perusahaan_lingkungan' ? 'selected' : '' ?>>Perusahaan / Lingkungan</option>
                </select>
            </div>
            <div>
                <label class="lis-form-label">Cari</label>
                <input type="text" name="q" value="<?= html_escape($search) ?>" placeholder="Nama / No. Reg / Billing" class="lis-form-control text-xs">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-colors flex items-center justify-center gap-1.5 shadow-xs">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Terapkan</span>
                </button>
                <a href="<?= site_url('kasir') ?>" class="px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-100 text-xs font-medium transition-colors">Reset</a>
            </div>
        </form>
    </div>

    <!-- TABEL -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600">
                    <tr>
                        <th class="text-left px-4 py-3 font-bold">Registrasi</th>
                        <th class="text-left px-4 py-3 font-bold">Pasien / Perusahaan</th>
                        <th class="text-left px-4 py-3 font-bold">Tipe</th>
                        <th class="text-right px-4 py-3 font-bold">Tagihan</th>
                        <th class="text-left px-4 py-3 font-bold">Status</th>
                        <th class="text-right px-4 py-3 font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                <?php if (empty($rows)): ?>
                    <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">Tidak ada tagihan untuk filter ini.</td></tr>
                <?php else: foreach ($rows as $r): ?>
                    <?php
                    $tipeLabel = ['mandiri' => 'Mandiri', 'rujukan_faskes' => 'Rujukan Faskes', 'perusahaan_lingkungan' => 'Perusahaan'][$r['tipe_pasien']] ?? $r['tipe_pasien'];
                    $badge = [
                        'UNPAID' => 'bg-amber-50 text-amber-800 border-amber-300',
                        'PAID' => 'bg-emerald-50 text-emerald-800 border-emerald-300',
                        'GUARANTEED' => 'bg-teal-50 text-teal-800 border-teal-300',
                    ][$r['status_pembayaran']] ?? 'bg-slate-50 text-slate-700 border-slate-300';
                    ?>
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-4 py-3">
                            <span class="font-mono font-bold text-slate-800 block"><?= html_escape($r['nomor_registrasi']) ?></span>
                            <span class="text-[10px] text-slate-400"><?= html_escape($r['tanggal_pendaftaran']) ?> <?= html_escape(substr($r['jam_pendaftaran'], 0, 5)) ?></span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-bold text-slate-900 block"><?= html_escape($r['nama_pasien']) ?></span>
                            <?php if ($r['kategori_pemeriksaan'] === 'lingkungan'): ?>
                                <span class="inline-block px-1.5 py-0.5 rounded bg-sky-50 text-sky-800 border border-sky-200 text-[10px] font-bold">Uji Lingkungan &bull; <?= html_escape($r['nomor_surat_pengantar']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($r['instansi_pengirim'])): ?>
                                <span class="text-[10px] text-slate-500"><?= html_escape($r['instansi_pengirim']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-slate-600"><?= html_escape($tipeLabel) ?></td>
                        <td class="px-4 py-3 text-right font-mono font-bold text-slate-900">Rp <?= number_format($r['total_biaya'], 0, ',', '.') ?></td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full border text-[10px] font-bold <?= $badge ?>"><?= html_escape($r['status_pembayaran']) ?></span>
                            <?php if ($r['status_pembayaran'] === 'PAID'): ?>
                                <span class="block text-[10px] text-slate-400 mt-0.5 font-mono"><?= html_escape($r['nomor_kuitansi']) ?></span>
                            <?php elseif ($r['status_pembayaran'] === 'GUARANTEED' && !empty($r['instansi_penjamin'])): ?>
                                <span class="block text-[10px] text-slate-400 mt-0.5"><?= html_escape($r['instansi_penjamin']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <?php if ($r['status_pembayaran'] === 'UNPAID' && $can_edit): ?>
                                <button type="button"
                                        data-id="<?= (int)$r['id'] ?>"
                                        data-reg="<?= html_escape($r['nomor_registrasi'] . ' / ' . $r['nomor_billing']) ?>"
                                        data-nama="<?= html_escape($r['nama_pasien']) ?>"
                                        data-total="<?= (float)$r['total_biaya'] ?>"
                                        data-piutang="<?= $r['alur_pembayaran'] === 'piutang_pks' ? '1' : '0' ?>"
                                        data-instansi="<?= html_escape($r['instansi_pengirim']) ?>"
                                        onclick="openKasirModal(this)"
                                        class="px-3 py-1.5 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white text-[11px] font-bold inline-flex items-center gap-1.5">
                                    <i data-lucide="banknote" class="w-3.5 h-3.5 text-amber-300"></i><span>Proses</span>
                                </button>
                            <?php elseif ($r['status_pembayaran'] === 'PAID'): ?>
                                <a href="<?= site_url('kasir/cetak_kuitansi/' . (int)$r['id']) ?>" target="_blank" class="px-3 py-1.5 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 text-[11px] font-bold inline-flex items-center gap-1.5">
                                    <i data-lucide="printer" class="w-3.5 h-3.5"></i><span>Kuitansi</span>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (count($rows) >= 500): ?>
            <div class="px-4 py-2 text-[11px] text-slate-400 border-t border-slate-100">Menampilkan 500 data pertama. Persempit filter untuk hasil lebih spesifik.</div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL KASIR -->
<div id="modal-kasir" class="lis-modal">
    <div class="lis-modal-box max-w-xl w-full">
        <div class="lis-modal-header bg-gradient-to-r from-emerald-900 via-emerald-800 to-teal-900 border-b border-amber-400/40">
            <div>
                <h3 class="lis-modal-title text-white">
                    <i data-lucide="receipt" class="w-5 h-5 text-amber-300"></i>
                    <span>Proses Tagihan</span>
                </h3>
                <p class="lis-modal-subtitle">Pembayaran Akun Bayar (Kas &amp; Bank) atau Penjaminan Piutang Mitra</p>
            </div>
            <button type="button" onclick="closeKasirModal()" class="lis-modal-close"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>

        <div class="lis-modal-body p-5 space-y-4 bg-white">
            <input type="hidden" id="kasir-reg-id">
            <input type="hidden" id="kasir-current-tab" value="bayar">
            <input type="hidden" id="kasir-total-raw" value="0">

            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 p-3.5 rounded-xl border border-emerald-200/80 space-y-1.5 text-xs">
                <div class="flex justify-between"><span class="text-slate-500">No. Registrasi &amp; Billing:</span><span id="kasir-reg-num" class="font-mono font-bold text-slate-800"></span></div>
                <div class="flex justify-between"><span class="text-slate-500">Nama Pasien / Subjek:</span><span id="kasir-patient-name" class="font-bold text-slate-900"></span></div>
                <div class="flex justify-between border-t border-emerald-200/70 pt-1.5 mt-1 text-sm font-bold">
                    <span class="text-emerald-950">Total Tagihan:</span>
                    <span id="kasir-total-tagihan" class="text-emerald-800 font-black font-mono text-base"></span>
                </div>
            </div>

            <div class="flex p-1 bg-slate-100 rounded-xl border border-slate-200">
                <button type="button" id="tab-btn-kasir-bayar" onclick="switchKasirTab('bayar')" class="flex-1 py-2 px-3 rounded-lg text-xs font-bold transition-all flex items-center justify-center gap-1.5 bg-emerald-700 text-white shadow-xs">
                    <i data-lucide="wallet-cards" class="w-4 h-4 text-amber-300"></i><span>Akun Bayar (Kas &amp; Bank)</span>
                </button>
                <button type="button" id="tab-btn-kasir-piutang" onclick="switchKasirTab('piutang')" class="flex-1 py-2 px-3 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 transition-all flex items-center justify-center gap-1.5">
                    <i data-lucide="handshake" class="w-4 h-4"></i><span>Akun Piutang (Mitra / PKS)</span>
                </button>
            </div>

            <!-- BAYAR -->
            <div id="panel-kasir-bayar" class="space-y-3.5">
                <div>
                    <label class="lis-form-label">Channel Akun Bayar <span class="req">*</span></label>
                    <select id="kasir-akun-bayar-id" class="lis-form-control text-xs font-bold text-slate-800">
                        <?php if (!empty($akun_bayar_list)): foreach ($akun_bayar_list as $ab): ?>
                            <option value="<?= (int)$ab['id'] ?>" data-tipe="<?= html_escape($ab['tipe_transaksi']) ?>">
                                <?= html_escape($ab['nama_bayar']) ?> &bull; COA: <?= html_escape($ab['kode_rekening']) ?><?= !empty($ab['nama_bank']) ? ' (' . html_escape($ab['nama_bank']) . ')' : '' ?>
                            </option>
                        <?php endforeach; else: ?>
                            <option value="">-- Belum ada master akun bayar aktif --</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <label class="lis-form-label mb-0">Uang Diterima (Rp) <span class="req">*</span></label>
                        <div class="flex gap-1">
                            <button type="button" onclick="setNominalPas()" class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200">Uang Pas</button>
                            <button type="button" onclick="addNominal(50000)" class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200">+50rb</button>
                            <button type="button" onclick="addNominal(100000)" class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200">+100rb</button>
                        </div>
                    </div>
                    <div class="relative">
                        <input type="number" id="kasir-nominal-bayar" oninput="calcKembalian()" class="lis-form-control text-sm font-mono font-black pl-9 text-emerald-950">
                        <span class="absolute left-3 top-2 font-bold text-slate-400 text-xs">Rp</span>
                    </div>
                    <div class="flex justify-between items-center p-2.5 bg-emerald-50/60 rounded-xl border border-emerald-200 text-xs">
                        <span class="font-semibold text-slate-600">Uang Kembalian:</span>
                        <span id="kasir-disp-kembalian" class="font-mono font-black text-emerald-900 text-sm">Rp 0</span>
                    </div>
                </div>
            </div>

            <!-- PIUTANG -->
            <div id="panel-kasir-piutang" class="space-y-3.5 hidden">
                <div>
                    <label class="lis-form-label">Akun Piutang / Penjamin <span class="req">*</span></label>
                    <select id="kasir-akun-piutang-id" class="lis-form-control text-xs font-bold text-slate-800">
                        <?php if (!empty($akun_piutang_list)): foreach ($akun_piutang_list as $ap): ?>
                            <option value="<?= (int)$ap['id'] ?>" data-nama="<?= html_escape($ap['nama_piutang']) ?>">
                                <?= html_escape($ap['nama_piutang']) ?> &bull; COA: <?= html_escape($ap['kode_rekening']) ?> (Tempo: <?= (int)$ap['jatuh_tempo_hari'] ?> Hari)
                            </option>
                        <?php endforeach; else: ?>
                            <option value="">-- Belum ada master akun piutang aktif --</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div>
                    <label class="lis-form-label">Nomor Kontrak PKS / Surat Jaminan / SPK <span class="req">*</span></label>
                    <input type="text" id="kasir-pks-nomor" class="lis-form-control font-mono font-bold text-xs" placeholder="PKS/LABKES/2026/088">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="lis-form-label">PIC Penjamin</label>
                        <input type="text" id="kasir-pks-pic" class="lis-form-control text-xs">
                    </div>
                    <div>
                        <label class="lis-form-label">Catatan Penagihan</label>
                        <input type="text" id="kasir-pks-catatan" class="lis-form-control text-xs">
                    </div>
                </div>
            </div>
        </div>

        <div class="lis-modal-footer px-5 py-3 border-t border-slate-200 bg-slate-50 flex items-center justify-between">
            <button type="button" onclick="closeKasirModal()" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-100">Batal</button>
            <button type="button" id="kasir-btn-submit" onclick="submitKasirPayment()" class="px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold shadow-md flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4 text-amber-300"></i>
                <span id="kasir-btn-submit-text">Konfirmasi Lunas (PAID)</span>
            </button>
        </div>
    </div>
</div>

<script>
const rupiah = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');

function openKasirModal(btn) {
    const d = btn.dataset;
    document.getElementById('kasir-reg-id').value = d.id;
    document.getElementById('kasir-total-raw').value = d.total;
    document.getElementById('kasir-reg-num').textContent = d.reg;
    document.getElementById('kasir-patient-name').textContent = d.nama;
    document.getElementById('kasir-total-tagihan').textContent = rupiah(parseFloat(d.total));
    document.getElementById('kasir-nominal-bayar').value = '';
    document.getElementById('kasir-pks-nomor').value = '';
    document.getElementById('kasir-pks-pic').value = '';
    document.getElementById('kasir-pks-catatan').value = '';
    calcKembalian();
    switchKasirTab(d.piutang === '1' ? 'piutang' : 'bayar');
    document.getElementById('modal-kasir').classList.add('flex');
    if (window.lucide) lucide.createIcons();
}

function closeKasirModal() {
    document.getElementById('modal-kasir').classList.remove('flex');
}

function switchKasirTab(tab) {
    document.getElementById('kasir-current-tab').value = tab;
    const bayar = tab === 'bayar';
    document.getElementById('panel-kasir-bayar').classList.toggle('hidden', !bayar);
    document.getElementById('panel-kasir-piutang').classList.toggle('hidden', bayar);
    const on = ['bg-emerald-700', 'text-white', 'shadow-xs', 'font-bold'];
    const off = ['text-slate-600', 'hover:text-slate-900', 'font-semibold'];
    [['tab-btn-kasir-bayar', bayar], ['tab-btn-kasir-piutang', !bayar]].forEach(([id, active]) => {
        const el = document.getElementById(id);
        on.forEach(c => el.classList.toggle(c, active));
        off.forEach(c => el.classList.toggle(c, !active));
    });
    document.getElementById('kasir-btn-submit-text').textContent = bayar ? 'Konfirmasi Lunas (PAID)' : 'Verifikasi Jaminan (GUARANTEED)';
}

function setNominalPas() {
    document.getElementById('kasir-nominal-bayar').value = document.getElementById('kasir-total-raw').value;
    calcKembalian();
}

function addNominal(n) {
    const el = document.getElementById('kasir-nominal-bayar');
    el.value = (parseFloat(el.value) || 0) + n;
    calcKembalian();
}

function calcKembalian() {
    const total = parseFloat(document.getElementById('kasir-total-raw').value) || 0;
    const bayar = parseFloat(document.getElementById('kasir-nominal-bayar').value) || 0;
    document.getElementById('kasir-disp-kembalian').textContent = rupiah(Math.max(0, bayar - total));
}

function submitKasirPayment() {
    const tab = document.getElementById('kasir-current-tab').value;
    const fd = new FormData();
    fd.append('id', document.getElementById('kasir-reg-id').value);
    fd.append('jenis_transaksi', tab);

    if (tab === 'piutang') {
        const akun = document.getElementById('kasir-akun-piutang-id');
        const noPks = document.getElementById('kasir-pks-nomor').value.trim();
        if (!akun.value) { LIS.alert('Peringatan', 'Silakan pilih Akun Piutang Penjamin.', 'warning'); return; }
        if (!noPks) { LIS.alert('Peringatan', 'Nomor PKS / SPK / Surat Jaminan wajib diisi.', 'warning'); return; }
        fd.append('akun_piutang_id', akun.value);
        fd.append('nomor_pks_spk', noPks);
        fd.append('pic_penjamin', document.getElementById('kasir-pks-pic').value.trim());
        fd.append('catatan_penjamin', document.getElementById('kasir-pks-catatan').value.trim());
    } else {
        const akun = document.getElementById('kasir-akun-bayar-id');
        const nominal = document.getElementById('kasir-nominal-bayar').value;
        if (!akun.value) { LIS.alert('Peringatan', 'Silakan pilih Akun Bayar Kas/Bank.', 'warning'); return; }
        if (!nominal) { LIS.alert('Peringatan', 'Nominal uang diterima wajib diisi.', 'warning'); return; }
        fd.append('akun_bayar_id', akun.value);
        fd.append('nominal_bayar', nominal);
    }

    const btn = document.getElementById('kasir-btn-submit');
    btn.disabled = true;

    fetch('<?= site_url('kasir/proses') ?>', { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(res => {
            if (res.status) {
                closeKasirModal();
                LIS.toast('success', res.message);
                setTimeout(() => location.reload(), 900);
            } else {
                LIS.alert('Gagal', res.message || 'Transaksi gagal diproses.', 'error');
            }
        })
        .catch(() => LIS.alert('Error', 'Terjadi kesalahan jaringan.', 'error'))
        .finally(() => { btn.disabled = false; });
}
</script>
