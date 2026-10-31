<?php defined('BASEPATH') OR exit('No direct script access allowed');
$barcodeVal = 'LKL-' . str_pad((int)$order['id'], 4, '0', STR_PAD_LEFT) . '-' . date('ym', strtotime($order['created_at'] ?: 'now'));
$instansiTitle = !empty($profile['brand']) ? $profile['brand'] : (!empty($profile['instansi']) ? $profile['instansi'] : 'UPT LABKES PROV. RIAU');
$containers = ['1. Fisika / Umum', '2. Kimia Logam (HNO3)', '3. Mikrobiologi (Steril)', '4. Arsip / Duplo'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label Sampel Lingkungan - <?= html_escape($order['order_number']) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Arial', sans-serif; }
        body { background: #f1f5f9; padding: 20px; }
        .no-print { max-width: 640px; margin: 0 auto 15px; display: flex; justify-content: space-between; align-items: center; gap: 10px; }
        .btn-print { background: #047857; color: #fff; border: none; padding: 8px 18px; border-radius: 6px; font-weight: 700; font-size: 12px; cursor: pointer; }
        .btn-close { background: #64748b; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; font-size: 12px; cursor: pointer; text-decoration: none; }
        .print-options { display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: bold; color: #334155; }

        .sheet { max-width: 640px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #cbd5e1; display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }

        .bottle-label {
            width: 275px;
            height: 145px;
            border: 1px dashed #64748b;
            padding: 6px 9px;
            border-radius: 4px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: #fff;
        }
        .label-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 2px;
        }
        .labkes-name { font-size: 8px; font-weight: 900; color: #047857; text-transform: uppercase; letter-spacing: 0.4px; }
        .reg-num { font-size: 8px; font-family: monospace; font-weight: bold; color: #334155; }

        .sample-info { margin: 2px 0; }
        .customer-name { font-size: 10.5px; font-weight: 900; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.2; }
        .sample-sub { font-size: 8px; color: #475569; display: flex; justify-content: space-between; margin-top: 1px; font-weight: 600; }
        .sample-loc { font-size: 7.5px; color: #047857; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; background: #f0fdf4; padding: 1.5px 4px; border-radius: 2px; }

        .barcode-section { text-align: center; margin-top: 1px; }
        .barcode-svg { height: 28px; width: 100%; display: block; }
        .barcode-text { font-size: 9px; font-family: monospace; font-weight: 900; letter-spacing: 1.2px; color: #000; margin-top: 1px; }

        .sample-footer { display: flex; justify-content: space-between; font-size: 7.5px; font-weight: bold; color: #334155; border-top: 1px solid #e2e8f0; padding-top: 2px; }

        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none; }
            .sheet { border: none; padding: 0; max-width: 100%; gap: 10px; }
            .bottle-label { border: 1px solid #000; page-break-inside: avoid; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <div class="print-options">
        <span>Jumlah Label Wadah:</span>
        <button type="button" onclick="setLabelCount(1)" style="padding:4px 8px; border:1px solid #cbd5e1; border-radius:4px; cursor:pointer;">1 Wadah</button>
        <button type="button" onclick="setLabelCount(2)" style="padding:4px 8px; border:1px solid #cbd5e1; border-radius:4px; cursor:pointer;">2 Wadah</button>
        <button type="button" onclick="setLabelCount(4)" style="padding:4px 8px; border:1px solid #047857; background:#047857; color:#fff; border-radius:4px; cursor:pointer;">4 Wadah</button>
    </div>
    <div style="display: flex; gap: 8px;">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak Label Sampel</button>
        <a href="javascript:window.close()" class="btn-close">Tutup</a>
    </div>
</div>

<div class="sheet" id="label-container">
    <?php for ($i = 0; $i < 4; $i++): ?>
        <div class="bottle-label label-item">
            <div class="label-header">
                <span class="labkes-name"><?= html_escape(substr($instansiTitle, 0, 26)) ?></span>
                <span class="reg-num">LAB: <?= html_escape($order['lab_number'] ?: $order['id']) ?></span>
            </div>

            <div class="sample-info">
                <div class="customer-name"><?= html_escape($order['customer_name']) ?></div>
                <div class="sample-sub">
                    <span><?= html_escape($order['sample_type_name'] ?: 'Lingkungan') ?></span>
                    <span><?= $order['sampling_at'] ? date('d/m/y H:i', strtotime($order['sampling_at'])) : date('d/m/y') ?></span>
                </div>
                <div class="sample-loc" title="<?= html_escape(($order['sample_description'] ?: '') . ' - ' . ($order['sample_location'] ?: '')) ?>">
                    📍 <?= html_escape(mb_strimwidth(($order['sample_description'] ?: $order['sample_type_name']) . ($order['sample_location'] ? ' (' . $order['sample_location'] . ')' : ''), 0, 42, '...')) ?>
                </div>
            </div>

            <div class="barcode-section">
                <svg class="barcode-svg" data-barcode="<?= html_escape($barcodeVal) ?>"></svg>
                <div class="barcode-text"><?= html_escape($barcodeVal) ?> &bull; <?= html_escape($order['fpps_number'] ?: $order['order_number']) ?></div>
            </div>

            <div class="sample-footer">
                <span>Wadah <?= html_escape($containers[$i]) ?></span>
                <span><?= html_escape($order['standard_code'] ?: 'KESLING') ?></span>
            </div>
        </div>
    <?php endfor; ?>
</div>

<script src="<?= base_url('assets/plugins/jsbarcode/JsBarcode.all.min.js') ?>"></script>
<script>
function renderBarcodes() {
    if (typeof JsBarcode === 'undefined') {
        document.querySelectorAll('.barcode-svg').forEach(function (svg) { svg.style.display = 'none'; });
        return;
    }
    document.querySelectorAll('.barcode-svg').forEach(function (svg) {
        var value = svg.getAttribute('data-barcode');
        if (!value) return;
        try {
            JsBarcode(svg, value, {
                format: 'CODE128',
                width: 1.4,
                height: 28,
                margin: 0,
                displayValue: false
            });
        } catch (e) {
            svg.style.display = 'none';
        }
    });
}

function setLabelCount(count) {
    document.querySelectorAll('.label-item').forEach(function (lbl, idx) {
        lbl.style.display = (idx < count) ? 'flex' : 'none';
    });
}

renderBarcodes();
</script>
</body>
</html>

