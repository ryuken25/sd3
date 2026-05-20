<?php
/**
 * Template PDF rapor (Dompdf). Standalone HTML — CSS di-inline karena Dompdf
 * paling andal membaca CSS inline. Konten memakai shared partial
 * rapor/_full_layout dengan showBadgeOnline=false (badge online tidak dicetak).
 *
 * Variabel: sama dengan output App\Libraries\RaporDataLoader::load().
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rapor <?= esc($siswa['nama_siswa'] ?? '') ?></title>
    <style>
        @page { margin: 1.6cm 1.8cm; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; color: #000; margin: 0; }
        .rapor-container { width: 100%; }

        .rapor-header-tbl { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        .rapor-header-tbl td { padding: 1px 3px; font-size: 11pt; vertical-align: top; }
        .rapor-header-tbl td.lbl { width: 14%; }
        .rapor-header-tbl td.sep { width: 2%; }

        .rapor-title { text-align: center; font-weight: bold; font-size: 14pt; margin: 12px 0; }

        .rapor-tbl { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .rapor-tbl th, .rapor-tbl td { border: 1px solid #000; padding: 4px 6px; vertical-align: top; font-size: 10pt; }
        .rapor-tbl thead th { background: #e5e5e5; text-align: center; font-weight: bold; }
        .rapor-row-group td { background: #f0f0f0; font-weight: bold; }

        .rapor-section-head { background: #e5e5e5; border: 1px solid #000; text-align: center; font-weight: bold; padding: 5px; font-size: 10pt; }
        .rapor-box { border: 1px solid #000; padding: 8px 10px; margin-bottom: 12px; font-size: 10pt; }
        .rapor-2col { width: 100%; border-collapse: collapse; margin-bottom: 12px; }

        .rapor-ttd { width: 100%; margin-top: 24px; }
        .rapor-ttd td { text-align: center; padding: 2px 4px; width: 33.33%; font-size: 10pt; }
        .rapor-ttd tr.ttd-space td { height: 56px; }
        .rapor-ttd tr.ttd-name td { font-weight: bold; text-decoration: underline; }
        .rapor-ttd tr.ttd-nip td { font-size: 9pt; }

        /* Badge online tidak pernah tampil di PDF. */
        .rapor-online-only { display: none; }
    </style>
</head>
<body>
    <?= view('rapor/_full_layout', array_merge(get_defined_vars(), [
        'showBadgeOnline'  => false,
        'showAdminActions' => false,
    ])) ?>
</body>
</html>
