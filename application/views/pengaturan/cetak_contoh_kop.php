<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= html_escape($title) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: #f1f5f9; padding: 25px 20px; color: #1e293b; font-size: 12px; }
        .doc-sheet { max-width: 820px; margin: 0 auto; background: #fff; padding: 35px 40px; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.06); }
        
        .no-print-bar { max-width: 820px; margin: 0 auto 15px; display: flex; justify-content: space-between; align-items: center; }
        .badge-preview { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 5px 12px; border-radius: 20px; font-weight: 700; font-size: 11px; }
        .btn-print { background: #047857; color: #fff; border: none; padding: 9px 18px; border-radius: 6px; font-weight: 700; font-size: 12px; cursor: pointer; transition: background 0.15s; }
        .btn-print:hover { background: #065f46; }
        .btn-close { background: #64748b; color: #fff; border: none; padding: 9px 16px; border-radius: 6px; font-weight: 600; font-size: 12px; cursor: pointer; text-decoration: none; }

        .doc-title { text-align: center; margin: 20px 0 15px; }
        .doc-title h1 { font-size: 15px; font-weight: 800; text-transform: uppercase; color: #0f172a; letter-spacing: 0.5px; text-decoration: underline; text-underline-offset: 4px; }
        .doc-title span { font-size: 11px; color: #64748b; margin-top: 3px; display: block; font-family: monospace; }

        .info-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px 16px; margin-bottom: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 11px; }
        .info-row { display: flex; margin-bottom: 3px; }
        .info-label { width: 140px; color: #64748b; font-weight: 600; flex-shrink: 0; }
        .info-val { font-weight: 700; color: #0f172a; }

        table.data-table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 11px; }
        table.data-table th { background: #f1f5f9; color: #334155; font-weight: 700; padding: 8px 10px; border: 1px solid #cbd5e1; text-align: left; }
        table.data-table td { padding: 7px 10px; border: 1px solid #e2e8f0; vertical-align: middle; }

        .signature-section { margin-top: 35px; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; text-align: center; font-size: 11px; }
        .sig-space { height: 60px; }

        .doc-footer { margin-top: 25px; padding-top: 10px; border-top: 1px dashed #cbd5e1; font-size: 9.5px; color: #64748b; display: flex; justify-content: space-between; align-items: center; }

        @media print {
            body { background: #fff; padding: 0; }
            .no-print-bar { display: none; }
            .doc-sheet { border: none; box-shadow: none; padding: 0; max-width: 100%; }
        }
    </style>
</head>
<body>

<div class="no-print-bar">
    <div class="badge-preview">
        📄 Uji Cetak Standar Kop Surat Resmi (Sesuai Pengaturan Sistem)
    </div>
    <div style="display: flex; gap: 8px;">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
        <a href="javascript:window.close()" class="btn-close">Kembali</a>
    </div>
</div>

<div class="doc-sheet">
    <!-- STANDARDIZED KOP SURAT BERSIFAT REUSABLE -->
    <?= render_kop_surat(['show_kan' => true, 'show_border' => true]) ?>

    <div class="doc-title">
        <h1>SURAT KETERANGAN HASIL PENGUJIAN LABORATORIUM</h1>
        <span>No. Registrasi: LIS-20261017-0099 &bull; Billing ID: BL-20261017-901</span>
    </div>

    <div class="info-box">
        <div>
            <div class="info-row"><span class="info-label">Nama Pasien / Klien:</span><span class="info-val">Tn. Mohammad Zulkarnain</span></div>
            <div class="info-row"><span class="info-label">NIK / Identitas:</span><span class="info-val">1471081504910002</span></div>
            <div class="info-row"><span class="info-label">Jenis Kelamin / Usia:</span><span class="info-val">Laki-laki (35 Tahun)</span></div>
            <div class="info-row"><span class="info-label">Dokter Pengirim:</span><span class="info-val">dr. Ahmad Faisal, Sp.PD</span></div>
        </div>
        <div>
            <div class="info-row"><span class="info-label">Tgl. Sampling:</span><span class="info-val"><?= date('d F Y') ?> 08:30 WIB</span></div>
            <div class="info-row"><span class="info-label">Tgl. Selesai Hasil:</span><span class="info-val"><?= date('d F Y') ?> 11:15 WIB</span></div>
            <div class="info-row"><span class="info-label">Jenis Spesimen:</span><span class="info-val">Darah Vena (Serum &amp; EDTA)</span></div>
            <div class="info-row"><span class="info-label">Status Verifikasi:</span><span class="info-val" style="color: #047857;">TERVERIFIKASI DOKTER (FINAL)</span></div>
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 35px; text-align: center;">No</th>
                <th>Parameter Pemeriksaan</th>
                <th style="width: 110px; text-align: center;">Hasil Uji</th>
                <th style="width: 90px; text-align: center;">Satuan</th>
                <th style="width: 140px; text-align: center;">Nilai Rujukan Standar</th>
                <th style="width: 110px;">Metode Pengujian</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center;">1</td>
                <td><strong>Hemoglobin (Hb)</strong></td>
                <td style="text-align: center; font-weight: bold; color: #047857;">14.8</td>
                <td style="text-align: center;">g/dL</td>
                <td style="text-align: center;">13.2 - 17.3</td>
                <td>SLS-Hemoglobin</td>
            </tr>
            <tr>
                <td style="text-align: center;">2</td>
                <td><strong>Leukosit</strong></td>
                <td style="text-align: center; font-weight: bold; color: #047857;">6,800</td>
                <td style="text-align: center;">/µL</td>
                <td style="text-align: center;">4,000 - 10,000</td>
                <td>Flowcytometry</td>
            </tr>
            <tr>
                <td style="text-align: center;">3</td>
                <td><strong>Trombosit</strong></td>
                <td style="text-align: center; font-weight: bold; color: #047857;">245,000</td>
                <td style="text-align: center;">/µL</td>
                <td style="text-align: center;">150,000 - 450,000</td>
                <td>Electrical Impedance</td>
            </tr>
            <tr>
                <td style="text-align: center;">4</td>
                <td><strong>Glukosa Darah Puasa</strong></td>
                <td style="text-align: center; font-weight: bold; color: #047857;">94</td>
                <td style="text-align: center;">mg/dL</td>
                <td style="text-align: center;">70 - 100</td>
                <td>Hexokinase / UV</td>
            </tr>
            <tr>
                <td style="text-align: center;">5</td>
                <td><strong>Kolesterol Total</strong></td>
                <td style="text-align: center; font-weight: bold; color: #d97706;">198</td>
                <td style="text-align: center;">mg/dL</td>
                <td style="text-align: center;">&lt; 200 (Normal)</td>
                <td>CHOD-PAP</td>
            </tr>
        </tbody>
    </table>

    <div class="signature-section">
        <div>
            <p>Analis Pemeriksa Lab,</p>
            <div class="sig-space"></div>
            <p style="font-weight: 700; text-decoration: underline;"><?= html_escape($kop['kasi_pelayanan'] ?? 'Ahmad Zulkarnain, S.Tr.Kes') ?></p>
            <p style="font-size: 10px; color: #64748b;">NIP. <?= html_escape($kop['nip_kasi_pelayanan'] ?? '19850314 200902 1 002') ?></p>
        </div>
        <div>
            <p><?= html_escape($kop['kota']) ?>, <?= date('d F Y') ?></p>
            <p>Dokter Penanggung Jawab Teknis,</p>
            <div class="sig-space"></div>
            <p style="font-weight: 700; text-decoration: underline;"><?= html_escape($kop['dokter_penanggung_jawab']) ?></p>
            <p style="font-size: 10px; color: #64748b;">SIP: <?= html_escape($kop['sip_dokter']) ?></p>
        </div>
    </div>

    <div class="doc-footer">
        <div>
            <strong>Catatan:</strong> <?= html_escape($kop['footer_note']) ?>
        </div>
        <div style="font-family: monospace;">
            Halaman 1 dari 1 &bull; LIS PRO
        </div>
    </div>
</div>

</body>
</html>
