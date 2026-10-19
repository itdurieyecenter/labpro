<?php
/**
 * MASTER TEMPLATE KOP SURAT RESMI LIS PRO
 * Dipakai seragam untuk seluruh dokumen cetak, nota billing, kuitansi BLUD, 
 * formulir permintaan lab, dan lembar hasil pemeriksaan laboratorium.
 *
 * Variabel pendukung dapat dioverride melalui array $options:
 * - show_kan      : (bool) Tampilkan logo KAN / Akreditasi di kanan (default: true)
 * - show_border   : (bool) Tampilkan garis ganda kop surat di bawah (default: true)
 * - is_compact    : (bool) Mode ringkas untuk nota kecil (default: false)
 * - custom_title  : (string) Judul khusus jika bukan instansi default
 */

$kop = kop_surat_data();
$show_kan    = isset($show_kan) ? (bool)$show_kan : true;
$show_border = isset($show_border) ? (bool)$show_border : true;
$is_compact  = isset($is_compact) ? (bool)$is_compact : false;

$logo_left_size  = $is_compact ? '60px' : '75px';
$logo_right_size = $is_compact ? '55px' : '70px';
?>

<div class="lis-kop-container" style="width: 100%; margin-bottom: <?= $is_compact ? '10px' : '16px' ?>; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #0f172a;">
    <table style="width: 100%; border-collapse: collapse; border: none; margin: 0; padding: 0;">
        <tr>
            <!-- LOGO KIRI: LOGO INSTANSI / PEMDA / FASKES -->
            <td style="width: <?= $is_compact ? '75px' : '95px' ?>; vertical-align: middle; text-align: center; padding: 0 10px 0 0;">
                <img src="<?= html_escape($kop['logo_instansi_url']) ?>" 
                     alt="Logo Instansi" 
                     style="width: <?= $logo_left_size ?>; max-height: 85px; object-fit: contain; display: block; margin: 0 auto;" />
            </td>

            <!-- TEKS TENGAH: IDENTITAS RESMI & ALAMAT KOP -->
            <td style="vertical-align: middle; text-align: center; padding: 0 5px;">
                <?php if (!empty($kop['line1'])): ?>
                    <div style="font-size: <?= $is_compact ? '11px' : '13px' ?>; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #334155; line-height: 1.25;">
                        <?= html_escape($kop['line1']) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($kop['line2'])): ?>
                    <div style="font-size: <?= $is_compact ? '12px' : '14px' ?>; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #1e293b; line-height: 1.25; margin-top: 1px;">
                        <?= html_escape($kop['line2']) ?>
                    </div>
                <?php endif; ?>

                <div style="font-size: <?= $is_compact ? '14px' : '16px' ?>; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; color: #064e3b; line-height: 1.25; margin-top: 2px;">
                    <?= html_escape($kop['line3']) ?>
                </div>

                <div style="font-size: <?= $is_compact ? '9px' : '10.5px' ?>; color: #475569; line-height: 1.35; margin-top: 4px;">
                    <?= html_escape($kop['alamat']) ?>, <?= html_escape($kop['kota']) ?>, <?= html_escape($kop['provinsi']) ?> <?= html_escape($kop['kode_pos'] ? '- ' . $kop['kode_pos'] : '') ?>
                </div>

                <div style="font-size: <?= $is_compact ? '8.5px' : '10px' ?>; color: #64748b; line-height: 1.35; margin-top: 1px;">
                    <?php 
                    $contacts = [];
                    if (!empty($kop['telepon'])) $contacts[] = 'Telp: ' . html_escape($kop['telepon']);
                    if (!empty($kop['whatsapp'])) $contacts[] = 'WA: ' . html_escape($kop['whatsapp']);
                    if (!empty($kop['email'])) $contacts[] = 'Email: ' . html_escape($kop['email']);
                    if (!empty($kop['website'])) $contacts[] = 'Website: ' . html_escape($kop['website']);
                    echo implode(' &bull; ', $contacts);
                    ?>
                </div>
            </td>

            <!-- LOGO KANAN: AKREDITASI KAN / ISO / GERMAS -->
            <?php if ($show_kan): ?>
                <td style="width: <?= $is_compact ? '75px' : '95px' ?>; vertical-align: middle; text-align: center; padding: 0 0 0 10px;">
                    <img src="<?= html_escape($kop['logo_kan_url']) ?>" 
                         alt="Logo Akreditasi KAN" 
                         style="width: <?= $logo_right_size ?>; max-height: 85px; object-fit: contain; display: block; margin: 0 auto;" />
                </td>
            <?php endif; ?>
        </tr>
    </table>

    <!-- GARIS GANDA RESMI KOP SURAT PEMERINTAH (TEBAL 3px + TIPIS 1px) -->
    <?php if ($show_border): ?>
        <div style="margin-top: <?= $is_compact ? '8px' : '12px' ?>; padding-top: 2px; border-top: 3px solid #0f172a; border-bottom: 1px solid #0f172a; height: 2px;"></div>
    <?php endif; ?>
</div>
