<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Order Pemeriksaan Lab - <?= html_escape($reg['nomor_registrasi']) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: #f1f5f9; padding: 20px; color: #1e293b; font-size: 12px; }
        .sheet { max-width: 800px; margin: 0 auto; background: #fff; padding: 25px 30px; border-radius: 8px; border: 1px solid #cbd5e1; }
        .header { display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #047857; padding-bottom: 15px; margin-bottom: 15px; }
        .header-title h2 { font-size: 15px; font-weight: 800; color: #064e3b; text-transform: uppercase; }
        .header-title h3 { font-size: 13px; font-weight: 700; color: #047857; }
        .header-title p { font-size: 10px; color: #64748b; line-height: 1.3; }
        
        .title { text-align: center; margin: 15px 0; }
        .title h1 { font-size: 15px; font-weight: 800; text-transform: uppercase; text-decoration: underline; color: #0f172a; }
        .title span { font-size: 11px; font-family: monospace; color: #475569; }

        .meta-box { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; font-size: 11px; background: #f8fafc; padding: 12px; border-radius: 6px; border: 1px solid #e2e8f0; }
        .row { display: flex; margin-bottom: 4px; }
        .label { width: 140px; color: #64748b; font-weight: 600; flex-shrink: 0; }
        .val { font-weight: 700; color: #0f172a; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 11px; }
        th { background: #f1f5f9; color: #334155; font-weight: 700; text-align: left; padding: 8px 10px; border: 1px solid #cbd5e1; }
        td { padding: 6px 10px; border: 1px solid #e2e8f0; }
        .text-center { text-align: center; }

        .signatures { margin-top: 30px; display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; text-align: center; font-size: 11px; }
        .sig-space { height: 50px; }

        .no-print { max-width: 800px; margin: 0 auto 15px; display: flex; justify-content: flex-end; gap: 10px; }
        .btn-print { background: #047857; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 700; font-size: 12px; cursor: pointer; }
        .btn-close { background: #64748b; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; font-size: 12px; cursor: pointer; text-decoration: none; }

        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none; }
            .sheet { border: none; padding: 0; max-width: 100%; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">🖨️ Cetak Formulir Order Lab</button>
    <a href="javascript:window.close()" class="btn-close">Tutup</a>
</div>

<div class="sheet">
    <!-- STANDARDIZED KOP SURAT LIS PRO -->
    <?= render_kop_surat(['show_kan' => true, 'show_border' => true]) ?>

    <div style="display: flex; justify-content: flex-end; align-items: center; gap: 8px; margin-top: -6px; margin-bottom: 10px;">
        <span style="font-size: 10px; color: #64748b;">Standar LIS SIMRS Labkes</span>
        <span style="font-family: monospace; font-size: 11px; font-weight: bold; background: #f1f5f9; padding: 2px 8px; border-radius: 4px; border: 1px solid #cbd5e1;"><?= html_escape($reg['barcode_sampel']) ?></span>
    </div>

    <div class="title">
        <h1>FORMULIR PERMINTAAN & PENERIMAAN SAMPEL LABORATORIUM</h1>
        <span>No. Registrasi: <?= html_escape($reg['nomor_registrasi']) ?> &bull; Billing ID: <?= html_escape($reg['nomor_billing']) ?></span>
    </div>

    <div class="meta-box">
        <div>
            <div class="row"><span class="label">Nama Pasien / Subjek:</span><span class="val"><?= html_escape($reg['nama_pasien']) ?></span></div>
            <div class="row"><span class="label">No. Rekam Medis:</span><span class="val"><?= html_escape($reg['nomor_rm'] ?: '-') ?></span></div>
            <div class="row"><span class="label">NIK / Identitas:</span><span class="val"><?= html_escape($reg['nik_pasien'] ?: '-') ?></span></div>
            <div class="row"><span class="label">Jenis Kelamin / Usia:</span><span class="val"><?= $reg['jenis_kelamin'] ?: '-' ?> (<?= $reg['usia_tahun'] ? $reg['usia_tahun'] . ' Tahun' : '-' ?>)</span></div>
            <div class="row"><span class="label">No. Telepon / HP:</span><span class="val"><?= html_escape($reg['telepon']) ?></span></div>
        </div>
        <div>
            <div class="row"><span class="label">Tipe Pendaftaran:</span><span class="val"><?= strtoupper(str_replace('_', ' ', $reg['tipe_pasien'])) ?></span></div>
            <div class="row"><span class="label">Dokter / Faskes Pengirim:</span><span class="val"><?= html_escape($reg['dokter_pengirim'] ?: '-') ?> / <?= html_escape($reg['instansi_pengirim'] ?: 'Mandiri') ?></span></div>
            <div class="row"><span class="label">Jenis Sampel:</span><span class="val"><?= html_escape($reg['jenis_sampel']) ?></span></div>
            <div class="row"><span class="label">Status Pembayaran:</span><span class="val"><?= html_escape($reg['status_pembayaran']) ?> (<?= html_escape($reg['alur_pembayaran'] === 'piutang_pks' ? 'PKS Mitra' : 'Mandiri') ?>)</span></div>
            <div class="row"><span class="label">Keterangan / Diagnosa:</span><span class="val"><?= html_escape($reg['diagnosa_klinis'] ?: '-') ?></span></div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 40px;">No</th>
                <th style="width: 80px;">Kode</th>
                <th>Parameter Uji Laboratorium</th>
                <th style="width: 120px;">Kategori Lab</th>
                <th style="width: 120px;">Metode Analisis</th>
                <th style="width: 80px;" class="text-center">Status Uji</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($reg['items'])): ?>
                <?php foreach ($reg['items'] as $idx => $it): ?>
                    <tr>
                        <td class="text-center"><?= $idx + 1 ?></td>
                        <td style="font-family: monospace; font-weight: bold;"><?= html_escape($it['kode_item']) ?></td>
                        <td>
                            <strong><?= html_escape($it['nama_item']) ?></strong>
                            <?php if ($it['tipe_item'] === 'template'): ?>
                                <span style="font-size: 9px; color: #047857;">[Paket Lab]</span>
                            <?php endif; ?>
                        </td>
                        <td><?= html_escape($it['kategori_lab']) ?></td>
                        <td><?= html_escape($it['metode_uji'] ?: '-') ?></td>
                        <td class="text-center"><span style="font-size: 10px; font-weight: bold; text-transform: uppercase;"><?= html_escape($it['status_pengujian']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="signatures">
        <div>
            <p>Pemohon / Pasien</p>
            <div class="sig-space"></div>
            <p style="font-weight: 700;">( <?= html_escape($reg['nama_pasien']) ?> )</p>
        </div>
        <div>
            <p>Petugas Sampling (Phlebotomist)</p>
            <div class="sig-space"></div>
            <p style="font-weight: 700;">( ......................................... )</p>
            <p style="font-size: 9px; color: #64748b;">Jam Sampling: ...................</p>
        </div>
        <div>
            <p>Analis Penguji Labkesda</p>
            <div class="sig-space"></div>
            <p style="font-weight: 700;">( ......................................... )</p>
            <p style="font-size: 9px; color: #64748b;">Validasi: Dokter PJ Lab</p>
        </div>
    </div>
</div>

</body>
</html>
