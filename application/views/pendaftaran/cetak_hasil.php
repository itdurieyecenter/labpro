<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Lab - <?= html_escape($reg['nomor_registrasi']) ?> - <?= html_escape($reg['nama_pasien']) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: #f1f5f9; padding: 20px; color: #0f172a; font-size: 11.5px; line-height: 1.4; }
        .sheet { max-width: 820px; margin: 0 auto; background: #fff; padding: 25px 32px; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.06); }

        .report-title { text-align: center; margin: 10px 0 14px; position: relative; }
        .report-title h1 { font-size: 16px; font-weight: 900; text-transform: uppercase; color: #064e3b; letter-spacing: 0.5px; }
        .report-title p { font-size: 10px; font-weight: 700; color: #64748b; letter-spacing: 1px; text-transform: uppercase; }

        .patient-box { display: grid; grid-template-columns: 1.1fr 1fr; gap: 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 14px; margin-bottom: 14px; font-size: 11px; }
        .p-row { display: flex; margin-bottom: 3.5px; }
        .p-label { width: 130px; color: #64748b; font-weight: 600; flex-shrink: 0; }
        .p-val { font-weight: 700; color: #0f172a; }

        table.results-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 11px; }
        table.results-table th { background: #064e3b; color: #fff; font-weight: 800; text-align: left; padding: 7px 8px; font-size: 10.5px; letter-spacing: 0.3px; border: 1px solid #064e3b; }
        table.results-table th.center { text-align: center; }
        table.results-table th.right { text-align: right; }
        table.results-table td { padding: 6px 8px; border: 1px solid #cbd5e1; vertical-align: middle; }
        table.results-table tr.category-header td { background: #f1f5f9; font-weight: 900; color: #064e3b; text-transform: uppercase; font-size: 11px; padding: 6px 10px; border-top: 2px solid #047857; }

        .flag-badge { display: inline-block; padding: 1.5px 6px; border-radius: 4px; font-size: 9.5px; font-weight: 800; text-align: center; }
        .flag-normal { color: #059669; }
        .flag-low { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-weight: 900; }
        .flag-high { background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; font-weight: 900; }
        .flag-critical { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; font-weight: 900; animation: blink 1.5s infinite; }

        .conclusion-box { margin-top: 14px; background: #fafaf9; border: 1px dashed #cbd5e1; padding: 10px 14px; border-radius: 6px; font-size: 11px; }
        .conclusion-title { font-weight: 800; color: #064e3b; text-transform: uppercase; font-size: 10.5px; margin-bottom: 4px; display: flex; items-center; gap: 5px; }
        .conclusion-text { color: #1e293b; line-height: 1.45; white-space: pre-line; }

        .signatures-area { margin-top: 20px; display: grid; grid-template-columns: 1.2fr 1fr 1fr; gap: 15px; font-size: 10.5px; text-align: center; align-items: flex-end; }
        .sig-box { padding: 4px; }
        .sig-title { color: #64748b; font-weight: 600; margin-bottom: 5px; }
        .sig-space { height: 55px; display: flex; align-items: center; justify-content: center; }
        .sig-name { font-weight: 800; color: #0f172a; text-decoration: underline; text-underline-offset: 3px; }
        .sig-nip { font-size: 9.5px; color: #64748b; margin-top: 1px; }

        .qr-placeholder { border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px; background: #fff; display: inline-flex; flex-direction: column; align-items: center; justify-content: center; width: 85px; height: 85px; margin: 0 auto; }
        .qr-placeholder svg { width: 55px; height: 55px; }
        .qr-text { font-size: 7.5px; font-family: monospace; font-weight: bold; color: #475569; margin-top: 2px; }

        .footer-note { margin-top: 18px; padding-top: 8px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #64748b; display: flex; justify-content: space-between; align-items: center; }

        .no-print-bar { max-width: 820px; margin: 0 auto 12px; display: flex; justify-content: flex-end; gap: 10px; }
        .btn-print { background: #047857; color: #fff; border: none; padding: 8px 18px; border-radius: 6px; font-weight: 700; font-size: 12px; cursor: pointer; display: flex; align-items: center; gap: 6px; }
        .btn-close { background: #64748b; color: #fff; border: none; padding: 8px 18px; border-radius: 6px; font-weight: 600; font-size: 12px; cursor: pointer; text-decoration: none; display: flex; align-items: center; }

        /* WATERMARK DAN BANNER HASIL BELUM SAH */
        .watermark-belum-sah {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 38px;
            font-weight: 900;
            color: rgba(225, 29, 72, 0.16);
            border: 5px dashed rgba(225, 29, 72, 0.28);
            padding: 24px 44px;
            border-radius: 14px;
            text-transform: uppercase;
            letter-spacing: 2px;
            pointer-events: none;
            z-index: 999;
            text-align: center;
            line-height: 1.25;
            width: 75%;
            user-select: none;
        }

        .banner-belum-sah {
            background: #fff1f2;
            border: 2px solid #fda4af;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 14px;
            position: relative;
        }

        @media print {
            body { background: #fff; padding: 0; color: #000; }
            .sheet { border: none; box-shadow: none; padding: 0; max-width: 100%; width: 100%; }
            .no-print-bar { display: none; }
            table.results-table th { background: #047857 !important; color: #fff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            table.results-table tr.category-header td { background: #f1f5f9 !important; color: #047857 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .flag-low { background: #eff6ff !important; color: #1d4ed8 !important; -webkit-print-color-adjust: exact; }
            .flag-high { background: #fff7ed !important; color: #c2410c !important; -webkit-print-color-adjust: exact; }
            .flag-critical { background: #fef2f2 !important; color: #b91c1c !important; -webkit-print-color-adjust: exact; }
            .patient-box { background: #f8fafc !important; -webkit-print-color-adjust: exact; }
            .watermark-belum-sah { display: block !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; color: rgba(225, 29, 72, 0.22) !important; border-color: rgba(225, 29, 72, 0.35) !important; }
            .banner-belum-sah { background: #fff1f2 !important; border-color: #fda4af !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>

<?php $isSah = !empty($keabsahan['is_sah']); ?>

<?php if (!$isSah): ?>
<!-- WATERMARK MENYILANG HASIL BELUM SAH -->
<div class="watermark-belum-sah">
    <div><?= html_escape($keabsahan['teks_watermark'] ?? 'HASIL BELUM SAH') ?></div>
    <div style="font-size: 15px; margin-top: 8px; font-weight: 800; letter-spacing: 1px; color: rgba(225, 29, 72, 0.3);">
        BELUM LENGKAP TANDA TANGAN (<?= $keabsahan['signed_count'] ?? 0 ?> / <?= $keabsahan['total_steps'] ?? 0 ?> TTD)
    </div>
</div>
<?php endif; ?>

<div class="no-print-bar">
    <button class="btn-print" onclick="window.print()" <?= !$isSah ? 'style="background: #e11d48;"' : '' ?>>
        <span>🖨️</span> <?= $isSah ? 'Cetak Lembar Resmi Hasil Lab' : 'Cetak Draft (Hasil Belum Sah)' ?>
    </button>
    <a href="javascript:window.close()" class="btn-close">Tutup</a>
</div>

<div class="sheet">
    <!-- BANNER PERINGATAN JIKA HASIL BELUM SAH -->
    <?php if (!$isSah): ?>
    <div class="banner-belum-sah">
        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 10px;">
            <div>
                <div style="font-size: 12px; font-weight: 900; color: #be123c; text-transform: uppercase; letter-spacing: 0.5px;">
                    ⚠️ DRAFT HASIL PENGUJIAN SEMENTARA &bull; BELUM SAH SECARA HUKUM
                </div>
                <div style="font-size: 10px; color: #881337; margin-top: 2px; line-height: 1.35;">
                    Pemeriksaan ini sedang dalam <strong>Masa Approval Berjenjang (Baru <?= $keabsahan['signed_count'] ?? 0 ?> dari <?= $keabsahan['total_steps'] ?? 0 ?> tanda tangan disahkan)</strong>. Dokumen ini <u>TIDAK DAPAT</u> diserahkan ke pelanggan/pasien sebagai sertifikat hasil resmi.
                </div>
            </div>
            <div style="flex-shrink: 0; background: #ffe4e6; border: 1.5px solid #f43f5e; color: #9f1239; padding: 3px 8px; border-radius: 5px; font-weight: 900; font-size: 9.5px; text-transform: uppercase;">
                BELUM SAH
            </div>
        </div>
        <?php if (!empty($keabsahan['pending_steps'])): ?>
        <div style="margin-top: 6px; font-size: 9.5px; color: #9f1239;">
            <strong>Menunggu Pengesahan:</strong> 
            <?php foreach ($keabsahan['pending_steps'] as $pStep): ?>
                <span style="display: inline-block; background: #fff; padding: 1.5px 6px; border-radius: 4px; border: 1px solid #fecdd3; margin-right: 4px; font-weight: bold;">
                    Tingkat <?= $pStep['urutan'] ?>: <?= html_escape($pStep['jabatan']) ?> (<?= html_escape($pStep['pegawai']) ?>)
                </span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- MASTER KOP SURAT RESMI LIS PRO -->
    <?= render_kop_surat(['show_kan' => $isSah, 'show_border' => true]) ?>

    <div class="report-title">
        <h1>LEMBAR HASIL PEMERIKSAAN LABORATORIUM</h1>
        <p><?= $isSah ? 'LABORATORY EXAMINATION REPORT' : 'LABORATORY REPORT &bull; <span style="color:#e11d48; font-weight:900;">DRAFT BELUM SAH</span>' ?></p>
    </div>

    <!-- IDENTITAS PASIEN & SPESIMEN -->
    <div class="patient-box">
        <div>
            <div class="p-row"><span class="p-label">No. Registrasi:</span><span class="p-val font-mono"><?= html_escape($reg['nomor_registrasi']) ?></span></div>
            <div class="p-row"><span class="p-label">No. Rekam Medis (RM):</span><span class="p-val"><?= html_escape($reg['nomor_rm'] ?: '-') ?></span></div>
            <div class="p-row"><span class="p-label">Nama Pasien / Subjek:</span><span class="p-val" style="font-size: 12px; font-weight: 800;"><?= html_escape($reg['nama_pasien']) ?></span></div>
            <div class="p-row"><span class="p-label">NIK / Identitas:</span><span class="p-val"><?= html_escape($reg['nik_pasien'] ?: '-') ?></span></div>
            <div class="p-row"><span class="p-label">Tgl. Lahir / Umur:</span><span class="p-val"><?= $reg['tanggal_lahir'] ? date('d/m/Y', strtotime($reg['tanggal_lahir'])) : '-' ?> (<?= $reg['usia_tahun'] ? $reg['usia_tahun'] . ' Thn' : '-' ?> <?= $reg['usia_bulan'] ? $reg['usia_bulan'] . ' Bln' : '' ?>)</span></div>
            <div class="p-row"><span class="p-label">Jenis Kelamin:</span><span class="p-val"><?= $reg['jenis_kelamin'] === 'L' ? 'Laki-laki (L)' : ($reg['jenis_kelamin'] === 'P' ? 'Perempuan (P)' : '-') ?></span></div>
            <div class="p-row"><span class="p-label">Alamat / Domisili:</span><span class="p-val"><?= html_escape($reg['alamat'] ?: '-') ?>, <?= html_escape($reg['kota_kabupaten'] ?: '') ?></span></div>
        </div>
        <div>
            <div class="p-row"><span class="p-label">Barcode Sampel:</span><span class="p-val font-mono" style="background:#f1f5f9; padding:1px 6px; border-radius:3px; border:1px solid #cbd5e1;"><?= html_escape($reg['barcode_sampel']) ?></span></div>
            <div class="p-row"><span class="p-label">Tgl. Registrasi:</span><span class="p-val"><?= date('d F Y', strtotime($reg['tanggal_pendaftaran'])) ?> <?= substr($reg['jam_pendaftaran'], 0, 5) ?> WIB</span></div>
            <div class="p-row"><span class="p-label">Tgl. Hasil Selesai:</span><span class="p-val"><?= !empty($reg['waktu_hasil_selesai']) ? date('d F Y H:i', strtotime($reg['waktu_hasil_selesai'])) . ' WIB' : date('d F Y H:i') . ' WIB' ?></span></div>
            <div class="p-row"><span class="p-label">Dokter Pengirim:</span><span class="p-val"><?= html_escape($reg['dokter_pengirim'] ?: 'Datang Sendiri (Mandiri)') ?></span></div>
            <div class="p-row"><span class="p-label">Instansi / Rujukan:</span><span class="p-val"><?= html_escape($reg['instansi_pengirim'] ?: '-') ?></span></div>
            <div class="p-row"><span class="p-label">Jenis Spesimen / Sampel:</span><span class="p-val"><?= html_escape($reg['jenis_sampel'] ?: 'Darah Vena / Serum') ?></span></div>
            <div class="p-row"><span class="p-label">Status Billing / Bayar:</span>
                <span class="p-val">
                    <?php if ($reg['status_pembayaran'] === 'PAID'): ?>
                        <span style="color:#047857; font-weight:800;">LUNAS (PAID)</span>
                    <?php elseif ($reg['status_pembayaran'] === 'GUARANTEED'): ?>
                        <span style="color:#0f766e; font-weight:800;">DIJAMIN PKS (<?= html_escape($reg['instansi_penjamin'] ?: 'Mitra') ?>)</span>
                    <?php else: ?>
                        <span style="color:#b45309; font-weight:800;">BELUM LUNAS</span>
                    <?php endif; ?>
                </span>
            </div>
        </div>
    </div>

    <!-- TABEL HASIL PEMERIKSAAN LAB -->
    <table class="results-table">
        <thead>
            <tr>
                <th class="center" style="width: 32px;">No</th>
                <th style="width: 220px;">Parameter Pemeriksaan</th>
                <th class="center" style="width: 105px;">Hasil Uji</th>
                <th class="center" style="width: 65px;">Flag</th>
                <th style="width: 110px;">Nilai Rujukan</th>
                <th class="center" style="width: 70px;">Satuan</th>
                <th style="width: 125px;">Metode Analisis</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $noUrut = 1; 
            if (!empty($grouped_items)): 
                foreach ($grouped_items as $categoryName => $catItems): 
            ?>
                <tr class="category-header">
                    <td colspan="8">
                        🔬 BIDANG: <?= html_escape($categoryName) ?>
                    </td>
                </tr>
                <?php foreach ($catItems as $it): 
                    $val = trim($it['hasil_nilai'] ?? '');
                    $flag = strtolower(trim($it['hasil_flag'] ?? 'normal'));
                    $flagLabel = '-';
                    $flagClass = 'flag-normal';
                    $isAbnormal = false;

                    if ($flag === 'low') {
                        $flagLabel = 'LOW (L)';
                        $flagClass = 'flag-low';
                        $isAbnormal = true;
                    } elseif ($flag === 'high') {
                        $flagLabel = 'HIGH (H)';
                        $flagClass = 'flag-high';
                        $isAbnormal = true;
                    } elseif ($flag === 'critical') {
                        $flagLabel = 'CRITICAL (*)';
                        $flagClass = 'flag-critical';
                        $isAbnormal = true;
                    } elseif ($flag === 'normal' && !empty($val)) {
                        $flagLabel = 'NORMAL';
                        $flagClass = 'flag-normal';
                    }
                ?>
                    <tr>
                        <td class="center" style="color: #64748b;"><?= $noUrut++ ?></td>
                        <td>
                            <strong><?= html_escape($it['nama_item']) ?></strong>
                            <?php if (!empty($it['kode_item']) && $it['kode_item'] !== '-'): ?>
                                <span style="font-size: 9px; color: #64748b; font-family: monospace; display: block;"><?= html_escape($it['kode_item']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="center" style="<?= $isAbnormal ? 'font-weight: 800; font-size: 12px;' : 'font-weight: 700;' ?>">
                            <?= !empty($val) ? html_escape($val) : '<span style="color:#94a3b8; font-style:italic;">Menunggu Analisis</span>' ?>
                        </td>
                        <td class="center">
                            <?php if (!empty($val)): ?>
                                <span class="flag-badge <?= $flagClass ?>"><?= $flagLabel ?></span>
                            <?php else: ?>
                                <span style="color:#94a3b8;">-</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-weight: 600; color: #334155;"><?= html_escape($it['nilai_rujukan'] ?: '-') ?></td>
                        <td class="center" style="color: #475569;"><?= html_escape($it['satuan'] ?: '-') ?></td>
                        <td style="font-size: 10px; color: #475569;"><?= html_escape($it['metode_uji'] ?: '-') ?></td>
                        <td style="font-size: 10px; color: #64748b;"><?= html_escape($it['catatan_hasil'] ?: '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php 
                endforeach; 
            else: 
            ?>
                <tr>
                    <td colspan="8" class="center" style="padding: 20px; color: #94a3b8;">Belum ada rincian parameter pengujian untuk pendaftaran ini.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- KESIMPULAN & SARAN KLINIS -->
    <div class="conclusion-box">
        <div class="conclusion-title">
            <span>📝</span>
            <span>Kesimpulan / Catatan Klinis Patologi:</span>
        </div>
        <div class="conclusion-text">
            <?= !empty($reg['kesimpulan_saran']) 
                ? html_escape($reg['kesimpulan_saran']) 
                : 'Pemeriksaan laboratorium telah dilaksanakan sesuai Standar Operasional Prosedur (SOP) dan Kontrol Kualitas Laboratorium Internal (IQC).' ?>
        </div>
    </div>

    <!-- AREA TANDA TANGAN & PENGESAHAN DOKTER & ANALIS BERJENJANG -->
    <div class="signatures-area" style="display: flex; justify-content: space-between; gap: 12px; margin-top: 25px; flex-wrap: wrap;">
        <!-- VALIDASI ELEKTRONIK & QR CODE -->
        <div class="sig-box" style="text-align: left; flex: 1; min-width: 140px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <?php if ($isSah): ?>
                <div class="qr-placeholder">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#064e3b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                        <line x1="7" y1="7" x2="7.01" y2="7"></line>
                        <line x1="17" y1="7" x2="17.01" y2="7"></line>
                        <line x1="7" y1="17" x2="7.01" y2="17"></line>
                        <line x1="17" y1="17" x2="17.01" y2="17"></line>
                    </svg>
                    <span class="qr-text"><?= html_escape($reg['barcode_sampel']) ?></span>
                </div>
                <div style="font-size: 8.5px; color: #475569; line-height: 1.35;">
                    <strong style="color: #064e3b; display: block; font-size: 9px;">DOKUMEN RESMI ELEKTRONIK</strong>
                    Pemeriksaan diverifikasi berjenjang oleh tim laboratorium terakreditasi ISO 15189 / KAN 17025.
                </div>
                <?php else: ?>
                <div class="qr-placeholder" style="border-color: #fca5a5; background: #fff5f5;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span class="qr-text" style="color: #b91c1c;">BELUM SAH</span>
                </div>
                <div style="font-size: 8.5px; color: #991b1b; line-height: 1.35;">
                    <strong style="color: #b91c1c; display: block; font-size: 9px;">STATUS: BELUM SAH</strong>
                    Masa approval berjenjang belum selesai (<?= $keabsahan['signed_count'] ?? 0 ?>/<?= $keabsahan['total_steps'] ?? 0 ?> TTD). Barcode legalitas resmi belum aktif.
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($approvals)): ?>
            <?php foreach ($approvals as $app): ?>
            <div class="sig-box" style="flex: 1; min-width: 120px; text-align: center;">
                <div class="sig-title" style="font-weight: 700; font-size: 9.5px; color: #064e3b;">
                    <?= html_escape($app['jabatan_approval']) ?>:
                </div>
                <div class="sig-space" style="height: 50px; display: flex; align-items: center; justify-content: center;">
                    <?php if (!empty($app['tanda_tangan']) && $app['status'] === 'approved'): ?>
                        <img src="<?= base_url($app['tanda_tangan']) ?>" style="max-height: 46px; max-width: 110px; object-fit: contain;" alt="TTD">
                    <?php elseif ($app['status'] === 'approved'): ?>
                        <span style="font-family: cursive; font-size: 13px; color: #047857; font-weight: bold;">Approved ✓</span>
                    <?php else: ?>
                        <span style="font-size: 8.5px; color: #dc2626; font-weight: bold; background: #fee2e2; padding: 2px 6px; border-radius: 4px; border: 1px dashed #f87171;">⏳ Menunggu TTD</span>
                    <?php endif; ?>
                </div>
                <div class="sig-name" style="font-weight: 800; font-size: 10px; color: #0f172a; text-decoration: underline;">
                    <?= html_escape($app['pegawai_nama']) ?>
                </div>
                <div class="sig-nip" style="font-size: 8.5px; color: #475569;">
                    <?= !empty($app['pegawai_nip']) ? 'NIP: ' . html_escape($app['pegawai_nip']) : html_escape($app['pegawai_jabatan'] ?: '-') ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- ANALIS PEMERIKSA LAB -->
            <div class="sig-box">
                <div class="sig-title">Pemeriksa / Analis Lab:</div>
                <div class="sig-space">
                    <span style="font-family: cursive; font-size: 15px; color: #047857; opacity: 0.85;">Digital Signed</span>
                </div>
                <div class="sig-name"><?= html_escape($reg['analis_nama'] ?: 'Analis Laboratorium') ?></div>
                <div class="sig-nip">Pranata Laboratorium Kesehatan</div>
            </div>

            <!-- DOKTER PENANGGUNG JAWAB SP.PK -->
            <div class="sig-box">
                <div class="sig-title"><?= html_escape($profile['kota']) ?>, <?= !empty($reg['waktu_hasil_selesai']) ? date('d F Y', strtotime($reg['waktu_hasil_selesai'])) : date('d F Y') ?></div>
                <div class="sig-title" style="margin-top: -3px;">Dokter Penanggung Jawab Lab:</div>
                <div class="sig-space">
                    <span style="font-family: cursive; font-size: 16px; color: #064e3b; font-weight: bold; opacity: 0.9;">Validated</span>
                </div>
                <div class="sig-name"><?= html_escape($reg['dokter_pj'] ?: $profile['kepala_lab']) ?></div>
                <div class="sig-nip">SIP: <?= html_escape(get_setting('sip_dokter', '503/SIP-D/2023/0421')) ?></div>
            </div>
        <?php endif; ?>
    </div>

    <!-- FOOTER CATATAN HUKUM & AKREDITASI -->
    <div class="footer-note">
        <span>
            <?php if ($isSah): ?>
                <?= html_escape(get_setting('kop_footer_note', 'Hasil pemeriksaan ini sah dicetak otomatis oleh LIS PRO dan terakreditasi KAN ISO/IEC 17025')) ?>
            <?php else: ?>
                <strong style="color: #b91c1c;">PERINGATAN: DOKUMEN BELUM SAH.</strong> <?= html_escape($keabsahan['keterangan_hukum'] ?? 'Hasil pemeriksaan ini BELUM SAH secara hukum dan belum dapat diserahkan ke pelanggan karena proses verifikasi berjenjang belum lengkap bertandatangan.') ?>
            <?php endif; ?>
        </span>
        <span>Dicetak: <?= date('d/m/Y H:i:s') ?> &bull; LIS PRO RIAU</span>
    </div>
</div>

</body>
</html>
