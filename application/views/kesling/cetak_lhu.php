<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$order   = $assembled['order'];
$grouped = $assembled['grouped'];
$summary = $assembled['summary'];
$subLabels = ['fisika' => 'Fisika', 'kimia' => 'Kimia', 'mikrobiologi' => 'Mikrobiologi', 'lainnya' => 'Lainnya'];
$romanLetters = ['fisika' => 'A', 'kimia' => 'B', 'mikrobiologi' => 'C', 'lainnya' => 'D'];

function _lhu_dt($d, $withTime = TRUE) {
    if (empty($d) || $d === '0000-00-00' || $d === '0000-00-00 00:00:00') return '-';
    $ts = strtotime($d);
    if (!$ts) return '-';
    return $withTime ? date('d-m-Y / H.i', $ts) . ' Wib' : date('d-m-Y', $ts);
}
$adaAkreditasi = FALSE;
foreach ($grouped as $rows) { foreach ($rows as $r) { if (!empty($r['is_accredited'])) { $adaAkreditasi = TRUE; break 2; } } }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LHU - <?= html_escape($order['order_number']) ?> - <?= html_escape($order['customer_name']) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: #f1f5f9; padding: 20px; color: #0f172a; font-size: 11px; line-height: 1.35; }
        .sheet { max-width: 820px; margin: 0 auto; background: #fff; padding: 26px 30px; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.06); }

        .kop { text-align: center; border-bottom: 3px double #064e3b; padding-bottom: 8px; margin-bottom: 4px; }
        .kop .induk { font-size: 11px; font-weight: 700; color: #334155; text-transform: uppercase; }
        .kop .inst { font-size: 15px; font-weight: 900; color: #064e3b; text-transform: uppercase; letter-spacing: 0.3px; }
        .kop .addr { font-size: 9px; color: #64748b; margin-top: 2px; }

        .report-title { text-align: center; margin: 10px 0; }
        .report-title h1 { font-size: 14px; font-weight: 900; text-transform: uppercase; color: #064e3b; letter-spacing: 1px; }
        .report-title p { font-size: 9.5px; font-style: italic; color: #64748b; }
        .report-title .nolhu { font-size: 11px; font-weight: 800; color: #0f172a; margin-top: 3px; font-family: monospace; }

        .info { width: 100%; border-collapse: collapse; margin: 8px 0 4px; font-size: 10px; }
        .info td { padding: 2px 5px; vertical-align: top; }
        .info .lbl { color: #475569; width: 150px; }
        .info .lbl i { color: #94a3b8; font-size: 8.5px; font-style: italic; display: block; }
        .info .val { font-weight: 700; color: #0f172a; }
        .info .sep { width: 10px; }

        table.res { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 10px; }
        table.res th { background: #064e3b; color: #fff; font-weight: 800; text-align: left; padding: 6px 7px; font-size: 9.5px; border: 1px solid #064e3b; }
        table.res th i { font-weight: 400; font-style: italic; font-size: 8px; display: block; opacity: 0.85; }
        table.res th.c, table.res td.c { text-align: center; }
        table.res td { padding: 5px 7px; border: 1px solid #cbd5e1; vertical-align: middle; }
        table.res tr.grp td { background: #f1f5f9; font-weight: 900; color: #064e3b; text-transform: uppercase; font-size: 10px; border-top: 2px solid #047857; }
        .exceed { color: #b91c1c; font-weight: 900; }
        .exceed-note { font-size: 8px; color: #b91c1c; font-weight: 700; }
        .acc-star { color: #047857; font-weight: 900; }

        .notes { margin-top: 10px; font-size: 8.5px; color: #475569; border-top: 1px solid #e2e8f0; padding-top: 6px; }
        .notes ol { margin-left: 16px; }
        .notes li { margin-bottom: 1px; }
        .notes i { color: #94a3b8; font-style: italic; }

        .sign { display: flex; justify-content: space-between; margin-top: 16px; gap: 20px; }
        .sign-box { text-align: center; font-size: 10px; flex: 1; }
        .sign-space { height: 48px; display: flex; align-items: center; justify-content: center; }
        .sign-name { font-weight: 800; color: #0f172a; text-decoration: underline; }
        .sign-sub { font-size: 9px; color: #475569; }

        .ref-foot { margin-top: 12px; font-size: 9px; color: #334155; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px 10px; }

        .no-print { max-width: 820px; margin: 0 auto 12px; display: flex; justify-content: flex-end; gap: 8px; }
        .btn-print { background: #047857; color: #fff; border: none; padding: 8px 18px; border-radius: 6px; font-weight: 700; font-size: 12px; cursor: pointer; }
        .btn-close { background: #64748b; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; font-size: 12px; cursor: pointer; text-decoration: none; }
        @media print { body { background: #fff; padding: 0; } .no-print { display: none; } .sheet { border: none; box-shadow: none; max-width: 100%; } }
    </style>
</head>
<body>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">🖨️ Cetak LHU</button>
    <a href="javascript:window.close()" class="btn-close">Tutup</a>
</div>

<div class="sheet">
    <div class="kop">
        <div class="induk"><?= html_escape($profile['induk']) ?></div>
        <div class="inst"><?= html_escape($profile['instansi']) ?></div>
        <div class="addr"><?= html_escape($profile['alamat']) ?>, <?= html_escape($profile['kota']) ?>, <?= html_escape($profile['provinsi']) ?> &bull; Telp: <?= html_escape($profile['telepon']) ?> &bull; <?= html_escape($profile['email']) ?></div>
    </div>

    <div class="report-title">
        <h1>Laporan Hasil Uji</h1>
        <p>Report of Analysis</p>
        <div class="nolhu">No.: <?= html_escape($order['order_number']) ?></div>
    </div>

    <table class="info">
        <tr>
            <td class="lbl">Nama Customer <i>Customer Name</i></td><td class="sep">:</td>
            <td class="val"><?= html_escape($order['customer_name']) ?></td>
            <td class="lbl">No. FPPS</td><td class="sep">:</td>
            <td class="val"><?= html_escape($order['fpps_number'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="lbl">Alamat <i>Address</i></td><td class="sep">:</td>
            <td class="val"><?= html_escape($order['customer_address'] ?: '-') ?></td>
            <td class="lbl">No. Laboratorium <i>No. Lab</i></td><td class="sep">:</td>
            <td class="val"><?= html_escape($order['lab_number'] ?: '-') ?></td>
        </tr>
        <tr>
            <td class="lbl">Personel Dihubungi <i>Contact Person</i></td><td class="sep">:</td>
            <td class="val"><?= html_escape($order['contact_person'] ?: '-') ?><?= $order['contact_phone'] ? ' (' . html_escape($order['contact_phone']) . ')' : '' ?></td>
            <td class="lbl">Tgl. Pengujian <i>Date of Analysis</i></td><td class="sep">:</td>
            <td class="val"><?= _lhu_dt($order['analysis_start'], FALSE) ?><?= $order['analysis_end'] ? ' s/d ' . _lhu_dt($order['analysis_end'], FALSE) : '' ?></td>
        </tr>
        <tr>
            <td class="lbl">Personel Pengambil Sampel <i>Person of Sampling</i></td><td class="sep">:</td>
            <td class="val"><?= html_escape($order['sampling_by'] ?: '-') ?></td>
            <td class="lbl">Tgl/Jam Pengambilan <i>Date/time of sampling</i></td><td class="sep">:</td>
            <td class="val"><?= _lhu_dt($order['sampling_at']) ?></td>
        </tr>
        <tr>
            <td class="lbl">Personel Penerima Sampel <i>Person of sample received</i></td><td class="sep">:</td>
            <td class="val"><?= html_escape($order['received_by'] ?: '-') ?></td>
            <td class="lbl">Tgl/Jam Penerimaan <i>Date/time of received</i></td><td class="sep">:</td>
            <td class="val"><?= _lhu_dt($order['received_at']) ?></td>
        </tr>
        <tr>
            <td class="lbl">Deskripsi Sampel <i>Description of sample</i></td><td class="sep">:</td>
            <td class="val" colspan="4">
                <?= html_escape($order['sample_description'] ?: '-') ?>
                <?php if ($order['sample_location']): ?> &bull; <?= html_escape($order['sample_location']) ?><?php endif; ?>
                <?php if ($order['latitude'] && $order['longitude']): ?> <span style="color:#64748b;">(<?= html_escape($order['latitude']) ?>, <?= html_escape($order['longitude']) ?>)</span><?php endif; ?>
            </td>
        </tr>
    </table>

    <table class="res">
        <thead><tr>
            <th class="c" style="width:28px;">NO</th>
            <th>PARAMETER</th>
            <th style="width:70px;">SATUAN <i>Unit</i></th>
            <th style="width:90px;">HASIL UJI <i>Test Result</i></th>
            <th style="width:110px;">NILAI RUJUKAN <i>Threshold Value</i></th>
            <th style="width:130px;">METODE <i>Method</i></th>
        </tr></thead>
        <tbody>
            <?php if (empty($grouped)): ?>
                <tr><td colspan="6" class="c" style="padding:16px; color:#94a3b8;">Baku mutu acuan belum dipilih / belum ada parameter. Pilih standar pada order ini.</td></tr>
            <?php else: ?>
                <?php foreach ($subLabels as $key => $label): ?>
                    <?php if (empty($grouped[$key])) continue; ?>
                    <tr class="grp"><td colspan="6"><?= $romanLetters[$key] ?>. <?= $label ?></td></tr>
                    <?php $no = 1; foreach ($grouped[$key] as $r): $isExceed = ($r['status'] === 'exceed'); ?>
                    <tr>
                        <td class="c"><?= $no++ ?></td>
                        <td>
                            <?= html_escape($r['param_name']) ?><?php if (!empty($r['is_accredited'])): ?> <span class="acc-star">*</span><?php endif; ?>
                            <?php if (!empty($r['param_name_en'])): ?><i style="color:#94a3b8; font-size:8px; display:block;"><?= html_escape($r['param_name_en']) ?></i><?php endif; ?>
                        </td>
                        <td><?= html_escape($r['unit'] ?: '-') ?></td>
                        <td class="<?= $isExceed ? 'exceed' : '' ?>">
                            <?= html_escape($r['result_value'] !== null && $r['result_value'] !== '' ? $r['result_value'] : '-') ?>
                            <?php if ($isExceed): ?><span class="exceed-note" style="display:block;">melebihi baku mutu</span><?php endif; ?>
                        </td>
                        <td><?= html_escape($r['threshold']) ?></td>
                        <td style="font-size:9px;"><?= html_escape($r['method'] ?: '-') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="notes">
        <ol>
            <li>Hasil uji ini hanya berlaku untuk sampel yang diuji. <i>These analytical results are only valid for the tested sample.</i></li>
            <li>Laporan Hasil Uji ini tidak boleh digandakan, kecuali secara lengkap dan seizin tertulis UPT Labkes Prov. Riau. <i>This report shall not be reproduced except in full and with written permission.</i></li>
            <li>Pihak laboratorium tidak bertanggung jawab terhadap pengambilan sampel yang tidak dilakukan oleh petugas laboratorium. <i>The laboratory is not responsible for sampling not performed by its personnel.</i></li>
            <?php if ($adaAkreditasi): ?>
            <li><span class="acc-star">*</span> Parameter dalam ruang lingkup akreditasi. <i>Parameter within accreditation scope.</i></li>
            <?php endif; ?>
            <li>Rujukan hasil uji berdasarkan <strong><?= html_escape($order['regulation_ref'] ?: ($order['standard_name'] ?: '-')) ?></strong>. <i>Reference based on the regulation above.</i></li>
        </ol>
    </div>

    <div class="sign">
        <div class="sign-box">
            <div><?= html_escape($profile['kota']) ?>, <?= _lhu_dt($order['analysis_end'] ?: $order['updated_at'], FALSE) ?></div>
            <div class="sign-sub">Mengetahui, Manajer Teknis /<br><i>Technical Manager</i></div>
            <div class="sign-space"><?php if ($order['status'] === 'completed'): ?><span style="font-family:cursive; color:#064e3b; font-size:15px; font-weight:bold;">Validated</span><?php endif; ?></div>
            <div class="sign-name"><?= html_escape(get_setting('manajer_teknis', $profile['kepala_lab'])) ?></div>
            <div class="sign-sub">NIP. <?= html_escape(get_setting('nip_manajer_teknis', $profile['nip_kepala'])) ?></div>
        </div>
        <div class="sign-box">
            <div>&nbsp;</div>
            <div class="sign-sub">Penanggung Jawab Pengujian /<br><i>Analysis Supervisor</i></div>
            <div class="sign-space"><?php if ($order['status'] === 'completed'): ?><span style="font-family:cursive; color:#047857; font-size:14px; font-weight:bold;">Approved ✓</span><?php endif; ?></div>
            <div class="sign-name"><?= html_escape(get_setting('pj_pengujian_lingkungan', '..............................')) ?></div>
            <div class="sign-sub">NIP. <?= html_escape(get_setting('nip_pj_pengujian_lingkungan', '-')) ?></div>
        </div>
    </div>

    <div class="ref-foot">
        <strong>Ringkasan:</strong> <?= (int)$summary['total'] ?> parameter diuji
        &bull; <?= (int)$summary['ok'] ?> memenuhi baku mutu
        <?php if ($summary['exceed'] > 0): ?>&bull; <span class="exceed"><?= (int)$summary['exceed'] ?> melebihi baku mutu</span><?php endif; ?>
        <?php if ($summary['na'] > 0): ?>&bull; <?= (int)$summary['na'] ?> belum diisi/tak dievaluasi<?php endif; ?>.
        <span style="float:right; color:#94a3b8;">Dicetak: <?= date('d/m/Y H:i') ?> &bull; LIS PRO</span>
    </div>
</div>

</body>
</html>
