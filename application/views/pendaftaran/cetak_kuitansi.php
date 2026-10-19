<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuitansi Pembayaran - <?= html_escape($reg['nomor_kuitansi'] ?: $reg['nomor_billing']) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: #f1f5f9; padding: 20px; color: #1e293b; font-size: 12px; }
        .receipt-container { max-width: 780px; margin: 0 auto; background: #fff; padding: 25px 30px; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .header { display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #047857; padding-bottom: 15px; margin-bottom: 15px; }
        .header-logo { display: flex; align-items: center; gap: 15px; }
        .header-logo img { width: 55px; height: auto; }
        .header-title h2 { font-size: 16px; font-weight: 800; color: #064e3b; letter-spacing: 0.5px; text-transform: uppercase; }
        .header-title h3 { font-size: 13px; font-weight: 700; color: #047857; }
        .header-title p { font-size: 10px; color: #64748b; line-height: 1.3; }
        .badge-status { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-paid { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .badge-guaranteed { background: #f0fdfa; color: #115e59; border: 1px solid #99f6e4; }
        
        .receipt-title { text-align: center; margin: 15px 0; }
        .receipt-title h1 { font-size: 16px; font-weight: 800; color: #0f172a; text-decoration: underline; text-underline-offset: 4px; }
        .receipt-title span { font-size: 11px; font-family: monospace; color: #475569; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; font-size: 11px; }
        .info-row { display: flex; margin-bottom: 4px; }
        .info-label { width: 130px; color: #64748b; font-weight: 600; flex-shrink: 0; }
        .info-val { font-weight: 700; color: #0f172a; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 11px; }
        th { background: #f8fafc; color: #334155; font-weight: 700; text-align: left; padding: 8px 10px; border: 1px solid #cbd5e1; }
        td { padding: 7px 10px; border: 1px solid #e2e8f0; vertical-align: middle; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .totals-table { margin-top: 10px; width: 50%; margin-left: auto; }
        .totals-table td { border: none; padding: 4px 8px; }
        .total-highlight { font-size: 14px; font-weight: 800; color: #065f46; border-top: 2px solid #047857 !important; padding-top: 6px !important; }

        .footer { margin-top: 25px; display: grid; grid-template-columns: 2fr 1fr; gap: 20px; font-size: 11px; align-items: flex-end; }
        .notes-box { background: #f8fafc; border: 1px dashed #cbd5e1; padding: 10px; border-radius: 6px; }
        .signature-box { text-align: center; }
        .signature-space { height: 50px; }

        .no-print-bar { max-width: 780px; margin: 0 auto 15px; display: flex; justify-content: flex-end; gap: 10px; }
        .btn-print { background: #047857; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 700; font-size: 12px; cursor: pointer; }
        .btn-close { background: #64748b; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; font-size: 12px; cursor: pointer; text-decoration: none; }

        @media print {
            body { background: #fff; padding: 0; }
            .receipt-container { border: none; box-shadow: none; padding: 0; max-width: 100%; }
            .no-print-bar { display: none; }
        }
    </style>
</head>
<body>

<div class="no-print-bar">
    <button class="btn-print" onclick="window.print()">🖨️ Cetak Kuitansi</button>
    <a href="javascript:window.close()" class="btn-close">Tutup</a>
</div>

<div class="receipt-container">
    <!-- KOP SURAT STANDAR LIS PRO -->
    <?= render_kop_surat(['show_kan' => true, 'show_border' => true]) ?>

    <div style="display: flex; justify-content: flex-end; margin-top: -8px; margin-bottom: 8px;">
        <?php if ($reg['status_pembayaran'] === 'PAID'): ?>
            <span class="badge-status badge-paid">LUNAS (PAID)</span>
        <?php elseif ($reg['status_pembayaran'] === 'GUARANTEED'): ?>
            <span class="badge-status badge-guaranteed">DIJAMIN PKS (GUARANTEED)</span>
        <?php else: ?>
            <span class="badge-status" style="background:#fff1f2; color:#be123c; border:1px solid #fecdd3;">BELUM LUNAS</span>
        <?php endif; ?>
    </div>

    <div class="receipt-title">
        <h1>KUITANSI PEMBAYARAN JASA LAYANAN LABORATORIUM</h1>
        <span>No. Kuitansi: <?= html_escape($reg['nomor_kuitansi'] ?: 'DRAFT-' . $reg['nomor_billing']) ?></span>
    </div>

    <div class="info-grid">
        <div>
            <div class="info-row"><span class="info-label">No. Registrasi LIS:</span><span class="info-val"><?= html_escape($reg['nomor_registrasi']) ?></span></div>
            <div class="info-row"><span class="info-label">Billing ID:</span><span class="info-val font-mono"><?= html_escape($reg['nomor_billing']) ?></span></div>
            <div class="info-row"><span class="info-label">Barcode Sampel:</span><span class="info-val font-mono"><?= html_escape($reg['barcode_sampel']) ?></span></div>
            <div class="info-row"><span class="info-label">Tgl. Pendaftaran:</span><span class="info-val"><?= date('d F Y', strtotime($reg['tanggal_pendaftaran'])) ?> <?= substr($reg['jam_pendaftaran'], 0, 5) ?> WIB</span></div>
            <div class="info-row"><span class="info-label">Alur Pembayaran:</span><span class="info-val"><?= $reg['alur_pembayaran'] === 'piutang_pks' ? 'Piutang / PKS Mitra' : 'Langsung / Mandiri' ?></span></div>
        </div>
        <div>
            <div class="info-row"><span class="info-label">Nama Pasien / Klien:</span><span class="info-val"><?= html_escape($reg['nama_pasien']) ?></span></div>
            <div class="info-row"><span class="info-label">NIK / No. RM:</span><span class="info-val"><?= html_escape($reg['nik_pasien'] ?: ($reg['nomor_rm'] ?: '-')) ?></span></div>
            <div class="info-row"><span class="info-label">Jenis Kelamin / Usia:</span><span class="info-val"><?= $reg['jenis_kelamin'] === 'L' ? 'Laki-laki' : ($reg['jenis_kelamin'] === 'P' ? 'Perempuan' : '-') ?> (<?= $reg['usia_tahun'] ? $reg['usia_tahun'] . ' Thn' : '-' ?>)</span></div>
            <div class="info-row"><span class="info-label">Instansi / Rujukan:</span><span class="info-val"><?= html_escape($reg['instansi_pengirim'] ?: 'Datang Sendiri (Mandiri)') ?></span></div>
            <?php if (!empty($reg['nomor_pks_spk'])): ?>
                <div class="info-row"><span class="info-label">No. SPK / PKS:</span><span class="info-val"><?= html_escape($reg['nomor_pks_spk']) ?></span></div>
            <?php endif; ?>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 40px;">No</th>
                <th style="width: 90px;">Kode</th>
                <th>Pemeriksaan / Parameter Uji Laboratorium</th>
                <th style="width: 110px;">Metode Uji</th>
                <th class="text-right" style="width: 120px;">Tarif BLUD (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($reg['items'])): ?>
                <?php foreach ($reg['items'] as $idx => $it): ?>
                    <tr>
                        <td class="text-center"><?= $idx + 1 ?></td>
                        <td class="font-mono"><?= html_escape($it['kode_item']) ?></td>
                        <td>
                            <strong><?= html_escape($it['nama_item']) ?></strong>
                            <?php if ($it['tipe_item'] === 'template'): ?>
                                <span style="font-size: 9px; color: #047857; background: #ecfdf5; padding: 2px 4px; border-radius: 4px; margin-left: 4px;">Paket Lab</span>
                            <?php endif; ?>
                            <div style="font-size: 10px; color: #64748b;"><?= html_escape($it['kategori_lab']) ?></div>
                        </td>
                        <td><?= html_escape($it['metode_uji'] ?: '-') ?></td>
                        <td class="text-right font-mono"><?= number_format($it['tarif'], 0, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center" style="padding: 15px; color: #94a3b8;">Tidak ada item pemeriksaan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td class="text-right font-semibold" style="width: 60%;">Subtotal Tarif:</td>
            <td class="text-right font-mono">Rp <?= number_format($reg['subtotal_tarif'], 0, ',', '.') ?></td>
        </tr>
        <?php if ((float)$reg['diskon_nominal'] > 0): ?>
            <tr>
                <td class="text-right font-semibold" style="color: #047857;">Diskon Kerjasama (<?= $reg['diskon_persen'] ?>%):</td>
                <td class="text-right font-mono" style="color: #047857;">- Rp <?= number_format($reg['diskon_nominal'], 0, ',', '.') ?></td>
            </tr>
        <?php endif; ?>
        <tr>
            <td class="text-right font-bold total-highlight">TOTAL TAGIHAN:</td>
            <td class="text-right font-mono font-bold total-highlight">Rp <?= number_format($reg['total_biaya'], 0, ',', '.') ?></td>
        </tr>
        <?php if ($reg['status_pembayaran'] === 'PAID'): ?>
            <tr>
                <td class="text-right" style="color: #64748b;">Jumlah Bayar (<?= html_escape($reg['metode_pembayaran']) ?>):</td>
                <td class="text-right font-mono">Rp <?= number_format($reg['nominal_bayar'], 0, ',', '.') ?></td>
            </tr>
            <?php if ((float)$reg['nominal_kembali'] > 0): ?>
                <tr>
                    <td class="text-right" style="color: #64748b;">Kembalian:</td>
                    <td class="text-right font-mono">Rp <?= number_format($reg['nominal_kembali'], 0, ',', '.') ?></td>
                </tr>
            <?php endif; ?>
        <?php elseif ($reg['status_pembayaran'] === 'GUARANTEED'): ?>
            <tr>
                <td class="text-right" style="color: #0f766e; font-weight: 600;">Penjamin / Mitra:</td>
                <td class="text-right font-semibold" style="color: #0f766e;"><?= html_escape($reg['instansi_penjamin'] ?: 'Mitra PKS') ?></td>
            </tr>
            <tr>
                <td class="text-right" style="color: #64748b;">Skema Piutang:</td>
                <td class="text-right font-mono text-[11px]" style="color: #0f766e;"><?= html_escape($reg['metode_pembayaran']) ?></td>
            </tr>
            <?php if (!empty($reg['nomor_pks_spk'])): ?>
                <tr>
                    <td class="text-right" style="color: #64748b;">No. Jaminan / SPK:</td>
                    <td class="text-right font-mono"><?= html_escape($reg['nomor_pks_spk']) ?></td>
                </tr>
            <?php endif; ?>
        <?php endif; ?>
    </table>

    <div class="footer">
        <div class="notes-box">
            <strong>Catatan & Ketentuan Pelayanan:</strong>
            <ul style="padding-left: 15px; margin-top: 4px; color: #475569; font-size: 10px; line-height: 1.4;">
                <li>Kuitansi ini merupakan bukti pembayaran sah dan validasi untuk pengambilan sampel darah/spesimen.</li>
                <li>Hasil pemeriksaan laboratorium (LHU) dapat diunduh online melalui Portal SIMRS atau diambil langsung dengan menunjukkan kuitansi ini.</li>
                <li><?= html_escape(get_setting('kop_footer_note', 'Dokumen ini dicetak secara komputerisasi oleh SIMRS LIS PRO.')) ?></li>
            </ul>
        </div>
        <div class="signature-box">
            <p><?= html_escape(get_setting('kota_kabupaten', 'Kota Pekanbaru')) ?>, <?= date('d F Y') ?></p>
            <p style="font-size: 10px; color: #64748b;">Petugas Kasir / Finance BLUD</p>
            <div class="signature-space"></div>
            <p style="font-weight: 700; text-decoration: underline;">Petugas Kasir SIMRS</p>
            <p style="font-size: 9px; color: #64748b;">NIP. 19850720 201001 2 015</p>
        </div>
    </div>
</div>

</body>
</html>
