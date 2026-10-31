<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Closing Kasir - <?= html_escape($c['nomor_closing']) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: #f1f5f9; padding: 20px; color: #1e293b; font-size: 12px; }
        .sheet { max-width: 780px; margin: 0 auto; background: #fff; padding: 25px 30px; border: 1px solid #cbd5e1; border-radius: 8px; }
        .head { text-align: center; border-bottom: 2px solid #047857; padding-bottom: 12px; margin-bottom: 14px; }
        .head h2 { font-size: 15px; font-weight: 800; color: #064e3b; text-transform: uppercase; }
        .head h1 { font-size: 14px; margin-top: 6px; text-decoration: underline; text-underline-offset: 4px; }
        .meta { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 20px; margin-bottom: 14px; font-size: 11px; }
        .meta b { display: inline-block; min-width: 110px; color: #475569; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; font-size: 11px; }
        th, td { border: 1px solid #cbd5e1; padding: 5px 7px; text-align: left; }
        th { background: #ecfdf5; color: #065f46; }
        .r { text-align: right; font-family: monospace; }
        .sum td { font-weight: 700; background: #f8fafc; }
        .diff-ok { color: #047857; } .diff-bad { color: #be123c; }
        .sign { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 30px; text-align: center; font-size: 11px; }
        .sign .line { height: 55px; }
        .btn { position: fixed; top: 14px; right: 14px; padding: 8px 14px; background: #047857; color: #fff; border: 0; border-radius: 8px; font-weight: 700; cursor: pointer; }
        @media print { body { background: #fff; padding: 0; } .sheet { border: 0; } .btn { display: none; } }
    </style>
</head>
<body>
<button class="btn" onclick="window.print()">Cetak</button>
<div class="sheet">
    <div class="head">
        <h2><?= html_escape($instansi) ?></h2>
        <h1>LEMBAR CLOSING KASIR HARIAN</h1>
    </div>

    <div class="meta">
        <div><b>No. Closing</b>: <?= html_escape($c['nomor_closing']) ?></div>
        <div><b>Tanggal Transaksi</b>: <?= html_escape(date('d/m/Y', strtotime($c['tanggal']))) ?></div>
        <div><b>Kasir</b>: <?= html_escape($c['kasir_nama']) ?></div>
        <div><b>Waktu Closing</b>: <?= html_escape(date('d/m/Y H:i', strtotime($c['created_at']))) ?></div>
    </div>

    <table>
        <tr><th>Akun Bayar</th><th>Tipe</th><th class="r">Jumlah</th><th class="r">Total (Rp)</th></tr>
        <?php foreach ($c['rincian'] as $a): ?>
        <tr>
            <td><?= html_escape($a['akun']) ?></td>
            <td><?= html_escape(strtoupper($a['tipe'])) ?></td>
            <td class="r"><?= (int)$a['jumlah'] ?></td>
            <td class="r"><?= number_format($a['total'], 0, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
        <tr class="sum"><td colspan="2">TOTAL PENDAPATAN</td><td class="r"><?= (int)$c['jumlah_transaksi'] ?></td><td class="r"><?= number_format($c['total_pendapatan'], 0, ',', '.') ?></td></tr>
    </table>

    <table>
        <tr><td>Total tunai seharusnya</td><td class="r"><?= number_format($c['total_tunai'], 0, ',', '.') ?></td></tr>
        <tr><td>Uang tunai fisik</td><td class="r"><?= number_format($c['uang_fisik_tunai'], 0, ',', '.') ?></td></tr>
        <tr class="sum"><td>Selisih</td><td class="r <?= (float)$c['selisih_tunai'] == 0 ? 'diff-ok' : 'diff-bad' ?>"><?= (float)$c['selisih_tunai'] == 0 ? 'Sesuai' : number_format($c['selisih_tunai'], 0, ',', '.') ?></td></tr>
        <tr><td>Total non-tunai</td><td class="r"><?= number_format($c['total_non_tunai'], 0, ',', '.') ?></td></tr>
    </table>

    <?php if (!empty($c['catatan'])): ?>
        <p style="margin-bottom:14px"><b>Catatan:</b> <?= nl2br(html_escape($c['catatan'])) ?></p>
    <?php endif; ?>

    <table>
        <tr><th>No. Kuitansi</th><th>Pasien</th><th>Channel</th><th class="r">Nominal</th></tr>
        <?php foreach ($c['transaksi'] as $t): ?>
        <tr>
            <td><?= html_escape($t['nomor_kuitansi']) ?></td>
            <td><?= html_escape($t['nama_pasien']) ?></td>
            <td><?= html_escape($t['metode_pembayaran']) ?></td>
            <td class="r"><?= number_format($t['total_biaya'], 0, ',', '.') ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <div class="sign">
        <div>Kasir<div class="line"></div><b><?= html_escape($c['kasir_nama']) ?></b></div>
        <div>Bendahara Penerimaan<div class="line"></div><b>( ............................ )</b></div>
    </div>
</div>
</body>
</html>
