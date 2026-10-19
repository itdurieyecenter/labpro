<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label Barcode Sampel - <?= html_escape($reg['barcode_sampel']) ?> - <?= html_escape($reg['nama_pasien']) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Arial', sans-serif; }
        body { background: #f1f5f9; padding: 20px; }
        .no-print { max-width: 620px; margin: 0 auto 15px; display: flex; justify-content: space-between; align-items: center; gap: 10px; }
        .btn-print { background: #047857; color: #fff; border: none; padding: 8px 18px; border-radius: 6px; font-weight: 700; font-size: 12px; cursor: pointer; }
        .btn-close { background: #64748b; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; font-size: 12px; cursor: pointer; text-decoration: none; }
        .print-options { display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: bold; color: #334155; }

        .sheet { max-width: 620px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #cbd5e1; display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }

        /* Ukuran standar label tabung spesimen darah / sampel (50mm x 25mm / 70mm x 35mm) */
        .tube-label {
            width: 260px;
            height: 135px;
            border: 1px dashed #64748b;
            padding: 6px 9px;
            border-radius: 4px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: #fff;
            position: relative;
        }

        .label-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 2px;
        }
        .labkes-name { font-size: 8px; font-weight: 900; color: #047857; text-transform: uppercase; letter-spacing: 0.5px; }
        .reg-num { font-size: 8px; font-family: monospace; font-weight: bold; color: #334155; }

        .patient-info { margin: 2px 0; }
        .patient-name { font-size: 11px; font-weight: 900; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.2; }
        .patient-sub { font-size: 8px; color: #475569; display: flex; justify-content: space-between; margin-top: 1px; font-weight: 600; }

        .tests-summary { font-size: 7.5px; color: #047857; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 1px; background: #f0fdf4; padding: 1px 4px; border-radius: 2px; }

        .barcode-section { text-align: center; margin-top: 1px; }
        .barcode-bars {
            height: 28px;
            width: 100%;
            background: repeating-linear-gradient(
                90deg,
                #000 0, #000 1.5px,
                #fff 1.5px, #fff 3px,
                #000 3px, #000 5px,
                #fff 5px, #fff 6px,
                #000 6px, #000 8.5px,
                #fff 8.5px, #fff 10px,
                #000 10px, #000 11px,
                #fff 11px, #fff 13px,
                #000 13px, #000 15px,
                #fff 15px, #fff 17px,
                #000 17px, #000 19px,
                #fff 19px, #fff 20px,
                #000 20px, #000 23px,
                #fff 23px, #fff 24px,
                #000 24px, #000 26px
            );
        }
        .barcode-text { font-size: 9.5px; font-family: monospace; font-weight: 900; letter-spacing: 1.5px; color: #000; margin-top: 1px; }

        .sample-footer { display: flex; justify-content: space-between; font-size: 7.5px; font-weight: bold; color: #334155; border-top: 1px solid #e2e8f0; padding-top: 2px; }

        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none; }
            .sheet { border: none; padding: 0; max-width: 100%; gap: 10px; }
            .tube-label { border: 1px solid #000; page-break-inside: avoid; }
        }
    </style>
</head>
<body>

<?php
$instansiTitle = !empty($setting['brand']) ? $setting['brand'] : (!empty($setting['instansi']) ? $setting['instansi'] : 'UPT LABKES PROV. RIAU');

// Bangun ringkasan nama pemeriksaan
$testSummary = [];
if (!empty($reg['items'])) {
    foreach (array_slice($reg['items'], 0, 4) as $it) {
        $testSummary[] = $it['nama_item'];
    }
}
$testSummaryStr = !empty($testSummary) ? implode(', ', $testSummary) : ($reg['kategori_pemeriksaan'] ?? 'Laboratorium');
?>

<div class="no-print">
    <div class="print-options">
        <span>Jumlah Label:</span>
        <button type="button" onclick="setLabelCount(1)" style="padding:4px 8px; border:1px solid #cbd5e1; border-radius:4px; cursor:pointer;">1 Label</button>
        <button type="button" onclick="setLabelCount(2)" style="padding:4px 8px; border:1px solid #cbd5e1; border-radius:4px; cursor:pointer;">2 Label</button>
        <button type="button" onclick="setLabelCount(4)" style="padding:4px 8px; border:1px solid #047857; background:#047857; color:#fff; border-radius:4px; cursor:pointer;">4 Label</button>
    </div>
    <div style="display: flex; gap: 8px;">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak Stiker Tabung</button>
        <a href="javascript:window.close()" class="btn-close">Tutup</a>
    </div>
</div>

<div class="sheet" id="label-container">
    <?php for ($i = 1; $i <= 4; $i++): ?>
        <div class="tube-label label-item">
            <div class="label-header">
                <span class="labkes-name"><?= html_escape(substr($instansiTitle, 0, 26)) ?></span>
                <span class="reg-num"><?= html_escape($reg['nomor_registrasi']) ?></span>
            </div>

            <div class="patient-info">
                <div class="patient-name"><?= html_escape($reg['nama_pasien']) ?></div>
                <div class="patient-sub">
                    <span>RM: <?= html_escape($reg['nomor_rm'] ?: '-') ?></span>
                    <span><?= $reg['jenis_kelamin'] ?: '-' ?> / <?= $reg['usia_tahun'] ? $reg['usia_tahun'] . 'Th' : '-' ?></span>
                    <span><?= date('d/m/y', strtotime($reg['tanggal_pendaftaran'])) ?></span>
                </div>
                <div class="tests-summary" title="<?= html_escape($testSummaryStr) ?>">
                    📋 <?= html_escape(mb_strimwidth($testSummaryStr, 0, 36, '...')) ?>
                </div>
            </div>

            <div class="barcode-section">
                <div class="barcode-bars"></div>
                <div class="barcode-text"><?= html_escape($reg['barcode_sampel']) ?></div>
            </div>

            <div class="sample-footer">
                <span>Tabung <?= $i ?>: <?= html_escape($reg['jenis_sampel']) ?></span>
                <span><?= $reg['status_pembayaran'] === 'PAID' ? 'PAID' : ($reg['status_pembayaran'] === 'GUARANTEED' ? 'PKS' : 'UNPAID') ?></span>
            </div>
        </div>
    <?php endfor; ?>
</div>

<script>
function setLabelCount(count) {
    const labels = document.querySelectorAll('.label-item');
    labels.forEach((lbl, idx) => {
        if (idx < count) {
            lbl.style.display = 'flex';
        } else {
            lbl.style.display = 'none';
        }
    });
}
</script>

</body>
</html>
