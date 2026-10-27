<?php
$valid = !empty($valid);
$isSah = $valid && !empty($is_sah);
$inst  = $profile['instansi'] ?? 'UPT Laboratorium Kesehatan Provinsi Riau';
$induk = $profile['induk'] ?? '';
function _vfmt_tgl($d) {
    if (empty($d) || $d === '0000-00-00' || $d === '0000-00-00 00:00:00') return '-';
    $ts = strtotime($d);
    return $ts ? date('d F Y', $ts) : '-';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Keaslian Dokumen - <?= $valid ? html_escape($reg['nomor_registrasi']) : 'LIS PRO' ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: #f1f5f9; color: #0f172a; padding: 18px; line-height: 1.45; }
        .card { max-width: 460px; margin: 10px auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; box-shadow: 0 10px 25px -10px rgba(0,0,0,0.18); }
        .head { padding: 18px 20px; color: #fff; text-align: center; }
        .head.ok    { background: linear-gradient(135deg, #065f46, #10b981); }
        .head.warn  { background: linear-gradient(135deg, #92400e, #f59e0b); }
        .head.bad   { background: linear-gradient(135deg, #7f1d1d, #ef4444); }
        .head .inst { font-size: 11px; font-weight: 700; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.4px; }
        .head .induk { font-size: 9.5px; opacity: 0.75; margin-top: 1px; }
        .icon { width: 62px; height: 62px; border-radius: 50%; background: rgba(255,255,255,0.18); display: flex; align-items: center; justify-content: center; margin: 12px auto 8px; font-size: 32px; }
        .status-title { font-size: 17px; font-weight: 900; letter-spacing: 0.3px; }
        .status-sub { font-size: 11px; opacity: 0.92; margin-top: 2px; }
        .body { padding: 18px 20px 22px; }
        .row { display: flex; justify-content: space-between; gap: 12px; padding: 9px 0; border-bottom: 1px dashed #e2e8f0; font-size: 13px; }
        .row:last-child { border-bottom: none; }
        .row .k { color: #64748b; font-weight: 600; flex-shrink: 0; }
        .row .v { font-weight: 800; color: #0f172a; text-align: right; word-break: break-word; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 800; }
        .badge.ok   { background: #d1fae5; color: #065f46; }
        .badge.warn { background: #fef3c7; color: #92400e; }
        .note { margin-top: 14px; font-size: 10.5px; color: #64748b; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px; line-height: 1.5; }
        .foot { text-align: center; font-size: 10px; color: #94a3b8; margin-top: 14px; }
        .bad-msg { text-align: center; padding: 6px 4px 2px; font-size: 13px; color: #991b1b; font-weight: 700; }
    </style>
</head>
<body>

<?php if (!$valid): ?>
    <div class="card">
        <div class="head bad">
            <div class="inst"><?= html_escape($inst) ?></div>
            <div class="icon">&#10007;</div>
            <div class="status-title">TIDAK TERVERIFIKASI</div>
            <div class="status-sub">Keaslian dokumen tidak dapat dipastikan</div>
        </div>
        <div class="body">
            <p class="bad-msg"><?= html_escape($alasan ?? 'Dokumen tidak valid.') ?></p>
            <div class="note">
                Jika Anda memindai QR dari lembar hasil uji resmi dan melihat pesan ini, pastikan QR terbaca
                penuh dan tidak rusak. Untuk konfirmasi, hubungi laboratorium melalui saluran resmi.
            </div>
            <div class="foot">LIS PRO &bull; Sistem Informasi Laboratorium</div>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="head <?= $isSah ? 'ok' : 'warn' ?>">
            <div class="inst"><?= html_escape($inst) ?></div>
            <?php if ($induk): ?><div class="induk"><?= html_escape($induk) ?></div><?php endif; ?>
            <div class="icon"><?= $isSah ? '&#10003;' : '&#9888;' ?></div>
            <div class="status-title"><?= $isSah ? 'DOKUMEN SAH' : 'BELUM SAH' ?></div>
            <div class="status-sub">
                <?= $isSah
                    ? 'Dokumen ini terdaftar dan telah divalidasi berjenjang'
                    : 'Dokumen terdaftar, namun proses validasi berjenjang belum lengkap' ?>
            </div>
        </div>
        <div class="body">
            <div class="row">
                <span class="k">No. Registrasi</span>
                <span class="v"><?= html_escape($reg['nomor_registrasi']) ?></span>
            </div>
            <div class="row">
                <span class="k">Kode Sampel</span>
                <span class="v"><?= html_escape($reg['barcode_sampel']) ?></span>
            </div>
            <div class="row">
                <span class="k">Pasien</span>
                <span class="v"><?= html_escape($reg['nama_mask']) ?></span>
            </div>
            <div class="row">
                <span class="k">Tanggal Daftar</span>
                <span class="v"><?= _vfmt_tgl($reg['tanggal']) ?></span>
            </div>
            <?php if (!empty($reg['waktu_selesai'])): ?>
            <div class="row">
                <span class="k">Tanggal Selesai</span>
                <span class="v"><?= _vfmt_tgl($reg['waktu_selesai']) ?></span>
            </div>
            <?php endif; ?>
            <div class="row">
                <span class="k">Status</span>
                <span class="v">
                    <span class="badge <?= $isSah ? 'ok' : 'warn' ?>">
                        <?= $isSah ? '&#10003; SAH / TERVALIDASI' : '&#9888; BELUM SAH' ?>
                    </span>
                </span>
            </div>
            <div class="row">
                <span class="k">Tanda Tangan</span>
                <span class="v"><?= (int)$signed_count ?> / <?= (int)$total_steps ?> lengkap</span>
            </div>
            <?php if ($isSah && !empty($reg['dokter_pj'])): ?>
            <div class="row">
                <span class="k">Penanggung Jawab</span>
                <span class="v"><?= html_escape($reg['dokter_pj']) ?></span>
            </div>
            <?php endif; ?>

            <div class="note">
                Halaman ini hanya menampilkan <strong>metadata keaslian dokumen</strong> dan sengaja
                <strong>tidak memuat nilai hasil pemeriksaan</strong> demi kerahasiaan medis pasien.
                Nama pasien ditampilkan tersamar. Hasil lengkap hanya tersedia pada lembar resmi yang
                diserahkan laboratorium.
            </div>
            <div class="foot">Diverifikasi oleh LIS PRO &bull; <?= date('d/m/Y H:i') ?> WIB</div>
        </div>
    </div>
<?php endif; ?>

</body>
</html>
