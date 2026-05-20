<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-heading">Menu Saya</div>
<a href="<?= base_url('orangtua/dashboard') ?>"><i class="bi bi-house me-2"></i> Dashboard</a>
<div class="sidebar-heading mt-3">Akademik</div>
<a href="<?= base_url('orangtua/grades/' . $siswa['id_siswa']) ?>"><i class="bi bi-bar-chart me-2"></i> Nilai &
    Rekap</a>
<a href="#" class="active"><i class="bi bi-file-earmark-text me-2"></i> E-Rapor</a>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    /* Tampilan e-rapor mirip lembar PDF: putih, bordered, font kecil. */
    .rapor-sheet {
        background: #fff;
        max-width: 920px;
        margin: 0 auto;
        font-size: 13px;
        color: #222;
    }

    .rapor-sheet table.tbl {
        width: 100%;
        border-collapse: collapse;
    }

    .rapor-sheet table.tbl th,
    .rapor-sheet table.tbl td {
        border: 1px solid #555;
        padding: 6px 8px;
        vertical-align: top;
    }

    .rapor-sheet table.tbl thead th {
        background: #e8eef5;
        text-align: center;
        font-weight: 700;
    }

    .rapor-sheet .row-group td {
        background: #f4f4f4;
        font-weight: 700;
    }

    .rapor-header-tbl td {
        padding: 2px 4px;
        font-size: 13px;
    }

    .rapor-title {
        text-align: center;
        font-weight: 700;
        letter-spacing: .5px;
        margin: 14px 0;
    }

    .rapor-section-head {
        background: #e8eef5;
        border: 1px solid #555;
        text-align: center;
        font-weight: 700;
        padding: 6px;
    }

    .rapor-box {
        border: 1px solid #555;
        padding: 10px 12px;
    }

    .ttd-area {
        text-align: center;
    }

    .ttd-name {
        font-weight: 700;
        text-decoration: underline;
        margin-bottom: 0;
    }

    @media print {

        .sidebar,
        nav.navbar,
        .no-print {
            display: none !important;
        }

        main {
            margin: 0 !important;
            padding: 0 !important;
        }

        .rapor-sheet {
            max-width: 100% !important;
        }

        .card {
            box-shadow: none !important;
            border: none !important;
        }

        body {
            background: #fff !important;
        }

        /* Badge "Catatan dari guru" hanya untuk layar — tidak ikut tercetak. */
        .online-only {
            display: none !important;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $semesterNum = strtolower((string) ($tahun_ajaran['semester'] ?? '')) === 'genap' ? '2' : '1';
    $tglLahir    = !empty($siswa['tanggal_lahir'])
        ? date('d-m-Y', strtotime($siswa['tanggal_lahir']))
        : '-';

    /**
     * Render baris tabel mata pelajaran.
     * Badge "Catatan dari guru" hanya tampil online (class online-only) untuk
     * nilai dengan flag_borderline_75 = 1 — tidak ikut tercetak (Pek 6.5).
     */
    $renderMapelRows = function (array $rows): string {
        if (empty($rows)) {
            return '<tr><td colspan="4" class="text-center text-muted fst-italic">Belum ada data.</td></tr>';
        }
        $html = '';
        $no = 1;
        foreach ($rows as $r) {
            $nilai  = isset($r['nilai_akhir']) ? number_format((float) $r['nilai_akhir'], 0) : '-';
            $narasi = trim((string) ($r['capaian_narasi'] ?? ''));
            $narasiHtml = $narasi !== ''
                ? nl2br(esc($narasi))
                : '<span class="text-muted fst-italic">Belum ada capaian dinilai.</span>';

            $isBorderline = (int) ($r['flag_borderline_75'] ?? 0) === 1;
            $catatan      = trim((string) ($r['catatan_remedial'] ?? ''));
            $badge = '';
            if ($isBorderline && $catatan !== '') {
                $badge = '<details class="online-only d-block mt-2">'
                    . '<summary class="badge bg-info text-dark fw-normal" style="cursor:pointer;">'
                    . '&#128221; Catatan dari guru</summary>'
                    . '<div class="small text-muted fst-italic mt-1 p-2 bg-light rounded border">'
                    . nl2br(esc($catatan)) . '</div></details>';
            }

            $html .= '<tr>'
                . '<td class="text-center">' . $no++ . '</td>'
                . '<td>' . esc((string) ($r['nama_mapel'] ?? '-')) . '</td>'
                . '<td class="text-center fw-bold">' . $nilai . '</td>'
                . '<td>' . $narasiHtml . $badge . '</td>'
                . '</tr>';
        }
        return $html;
    };
?>

<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <div>
        <h4 class="fw-bold text-pastel-primary mb-1"><i class="bi bi-file-earmark-text me-2"></i>E-Rapor Digital</h4>
        <p class="text-muted mb-0"><?= esc($siswa['nama_siswa']) ?> | <?= esc($tahun_ajaran['tahun_ajaran']) ?>
            Semester <?= esc($tahun_ajaran['semester']) ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('orangtua/grades/' . $siswa['id_siswa']) ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali</a>
        <a href="<?= base_url('orangtua/rapor/download/' . $siswa['id_siswa'] . '/' . $tahun_ajaran['id_tahun_ajaran']) ?>"
            class="btn btn-danger btn-sm fw-semibold">
            <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF</a>
        <button onclick="window.print()" class="btn btn-primary bg-pastel-primary border-0 btn-sm fw-semibold">
            <i class="bi bi-printer me-1"></i> Cetak</button>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4 p-md-5">
        <div class="rapor-sheet">

            <!-- ═══ Header siswa (2 kolom, match PDF) ═══ -->
            <table class="rapor-header-tbl" style="width:100%;">
                <tr>
                    <td style="width:13%;">Nama Murid</td>
                    <td style="width:2%;">:</td>
                    <td style="width:37%;"><strong><?= esc($siswa['nama_siswa']) ?></strong></td>
                    <td style="width:13%;">Kelas</td>
                    <td style="width:2%;">:</td>
                    <td style="width:33%;"><?= esc($kelas['nama_kelas'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td>NIS/NISN</td>
                    <td>:</td>
                    <td><?= esc($siswa['nis'] ?? '-') ?> / <?= esc($siswa['nisn'] ?? '-') ?></td>
                    <td>Fase</td>
                    <td>:</td>
                    <td><?= esc($fase) ?></td>
                </tr>
                <tr>
                    <td>Sekolah</td>
                    <td>:</td>
                    <td><?= esc($sekolah) ?></td>
                    <td>Semester</td>
                    <td>:</td>
                    <td><?= esc($semesterNum) ?></td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>:</td>
                    <td><?= esc($siswa['alamat'] ?? '-') ?></td>
                    <td>Tahun Ajaran</td>
                    <td>:</td>
                    <td><?= esc($tahun_ajaran['tahun_ajaran']) ?></td>
                </tr>
            </table>
            <hr style="border-top:1px solid #999;">

            <div class="rapor-title h5">LAPORAN HASIL BELAJAR</div>

            <!-- ═══ Tabel Mata Pelajaran Wajib ═══ -->
            <table class="tbl mb-4">
                <thead>
                    <tr>
                        <th style="width:5%;">No</th>
                        <th style="width:28%;">Mata Pelajaran</th>
                        <th style="width:10%;">Nilai Akhir</th>
                        <th style="width:57%;">Capaian Kompetensi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="row-group">
                        <td colspan="4">Mata Pelajaran Wajib</td>
                    </tr>
                    <?= $renderMapelRows($mapel['wajib'] ?? []) ?>
                    <tr class="row-group">
                        <td colspan="4">Mata Pelajaran Pilihan</td>
                    </tr>
                    <?= $renderMapelRows($mapel['pilihan'] ?? []) ?>
                </tbody>
            </table>

            <!-- ═══ Kokurikuler ═══ -->
            <div class="rapor-section-head">Kokurikuler</div>
            <div class="rapor-box mb-4">
                <?php $koko = trim((string) ($koko_narasi ?? '')); ?>
                <?php if ($koko !== ''): ?>
                    <?= nl2br(esc($koko)) ?>
                <?php else: ?>
                    <span class="text-muted fst-italic">Belum ada capaian kokurikuler dinilai.</span>
                <?php endif; ?>
            </div>

            <!-- ═══ Ekstrakurikuler ═══ -->
            <table class="tbl mb-4">
                <thead>
                    <tr>
                        <th style="width:5%;">No</th>
                        <th style="width:28%;">Ekstrakurikuler</th>
                        <th style="width:67%;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ekskul)): ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted fst-italic">Belum ada ekstrakurikuler.</td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1;
                        foreach ($ekskul as $e): ?>
                            <tr>
                                <td class="text-center"><?= $no++ ?></td>
                                <td><?= esc($e['nama'] ?? '-') ?></td>
                                <td><?= esc($e['keterangan'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- ═══ Ketidakhadiran + Catatan Wali Kelas ═══ -->
            <div class="row g-3 mb-4">
                <div class="col-md-5">
                    <div class="rapor-section-head">Ketidakhadiran</div>
                    <table class="tbl">
                        <tr>
                            <td>Sakit</td>
                            <td style="width:14%;text-align:center;">:</td>
                            <td style="width:30%;"><?= (int) ($rapor['sakit'] ?? 0) ?> hari</td>
                        </tr>
                        <tr>
                            <td>Izin</td>
                            <td style="text-align:center;">:</td>
                            <td><?= (int) ($rapor['izin'] ?? 0) ?> hari</td>
                        </tr>
                        <tr>
                            <td>Tanpa Keterangan</td>
                            <td style="text-align:center;">:</td>
                            <td><?= (int) ($rapor['alpa'] ?? 0) ?> hari</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-7">
                    <div class="rapor-section-head">Catatan Wali Kelas</div>
                    <div class="rapor-box" style="min-height:108px;">
                        <?php $catatanWali = trim((string) ($rapor['catatan_wali_kelas'] ?? '')); ?>
                        <?= $catatanWali !== ''
                            ? nl2br(esc($catatanWali))
                            : '<span class="text-muted fst-italic">Belum ada catatan dari wali kelas.</span>' ?>
                    </div>
                </div>
            </div>

            <!-- ═══ Tanggapan Orang Tua/Wali Murid ═══ -->
            <div class="rapor-section-head">Tanggapan Orang Tua/Wali Murid</div>
            <div class="rapor-box mb-4" style="min-height:80px;"></div>

            <!-- ═══ Tanda Tangan ═══ -->
            <div class="row mt-5">
                <div class="col-4 ttd-area">
                    <p class="mb-0">Orang Tua Murid</p>
                    <div style="height:72px;"></div>
                    <p class="ttd-name">......................................</p>
                </div>
                <div class="col-4 ttd-area">
                    <p class="mb-0">Kepala Sekolah</p>
                    <div style="height:72px;"></div>
                    <p class="ttd-name"><?= esc($kepsek_nama) ?></p>
                    <small>NIP. <?= esc($kepsek_nip) ?></small>
                </div>
                <div class="col-4 ttd-area">
                    <p class="mb-0">Tabanan, <?= esc($tanggal_indo) ?><br>Wali Kelas</p>
                    <div style="height:52px;"></div>
                    <p class="ttd-name"><?= esc($wali_kelas['nama_lengkap'] ?? '-') ?></p>
                    <small>NIP. <?= esc($wali_nip) ?></small>
                </div>
            </div>

        </div>
    </div>
</div>
<?= $this->endSection() ?>
