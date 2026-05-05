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
    @media print {

        .sidebar,
        nav.navbar,
        .btn,
        .no-print {
            display: none !important;
        }

        main {
            margin: 0 !important;
            padding: 0 !important;
        }

        .print-area {
            max-width: 100% !important;
        }

        .card {
            box-shadow: none !important;
            border: 1px solid #ccc !important;
        }

        body {
            background: white !important;
        }
    }

    .rapor-header {
        border-bottom: 3px double #333;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <div>
        <h4 class="fw-bold text-pastel-primary mb-1"><i class="bi bi-file-earmark-text me-2"></i>E-Rapor Digital</h4>
        <p class="text-muted mb-0"><?= esc($siswa['nama_siswa']) ?> | <?= esc($tahun_ajaran['tahun_ajaran']) ?> Semester
            <?= esc($tahun_ajaran['semester']) ?>
        </p>
    </div>
    <div class="d-flex gap-2 no-print">
        <a href="<?= base_url('orangtua/grades/' . $siswa['id_siswa']) ?>" class="btn btn-outline-secondary btn-sm"><i
                class="bi bi-arrow-left me-1"></i> Kembali</a>
        <?php if (!empty($rapor['is_finalized'])): ?>
            <a href="<?= base_url('orangtua/rapor/download/' . $siswa['id_siswa'] . '/' . $tahun_ajaran['id_tahun_ajaran']) ?>"
                class="btn btn-danger btn-sm fw-semibold">
                <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
            </a>
            <button onclick="window.print()" class="btn btn-primary bg-pastel-primary border-0 btn-sm fw-semibold">
                <i class="bi bi-printer me-1"></i> Cetak
            </button>
        <?php endif; ?>
    </div>
</div>

<?php if (!$rapor): ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        Rapor belum difinalisasi oleh admin/wali kelas. Silakan cek kembali nanti.
    </div>
<?php else: ?>

    <div class="card border-0 shadow-sm print-area">
        <div class="card-body p-4 p-md-5">

            <!-- Rapor Header -->
            <div class="text-center mb-4 rapor-header pb-4">
                <h4 class="fw-bold mb-0">LAPORAN HASIL BELAJAR SISWA</h4>
                <h5>SDN 3 MEKARSARI</h5>
                <p class="mb-0">Tahun Pelajaran <?= esc($tahun_ajaran['tahun_ajaran']) ?> — Semester
                    <?= esc($tahun_ajaran['semester']) ?>
                </p>
            </div>

            <!-- Data Siswa -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="table table-borderless table-sm small">
                        <tr>
                            <td style="width:140px">Nama Siswa</td>
                            <td>: <strong><?= esc($siswa['nama_siswa']) ?></strong></td>
                        </tr>
                        <tr>
                            <td>NIS</td>
                            <td>: <?= esc($siswa['nis']) ?></td>
                        </tr>
                        <tr>
                            <td>NISN</td>
                            <td>: <?= esc($siswa['nisn'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td>Jenis Kelamin</td>
                            <td>: <?= $siswa['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless table-sm small">
                        <tr>
                            <td style="width:140px">Tempat/Tgl Lahir</td>
                            <td>: <?= esc($siswa['tempat_lahir'] ?? '-') ?>,
                                <?= $siswa['tanggal_lahir'] ? date('d-m-Y', strtotime($siswa['tanggal_lahir'])) : '-' ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td>: <?= esc($siswa['alamat'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td>Status Kenaikan</td>
                            <td>: <strong
                                    class="text-<?= $rapor['status_kenaikan'] === 'Naik' ? 'success' : 'danger' ?>"><?= esc($rapor['status_kenaikan'] ?? 'Belum Ditentukan') ?></strong>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Tabel Nilai -->
            <h6 class="fw-bold border-bottom pb-2 mb-3">A. REKAP NILAI MATA PELAJARAN</h6>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-sm text-center">
                    <thead class="table-light">
                        <tr>
                            <th class="text-start">No</th>
                            <th class="text-start">Mata Pelajaran</th>
                            <th>Nilai Akhir</th>
                            <th>Huruf</th>
                            <th>KKM</th>
                            <th>Predikat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $kelompokA = array_filter($nilai_akhir, fn($na) => $na['kelompok'] === 'A');
                        $kelompokB = array_filter($nilai_akhir, fn($na) => $na['kelompok'] === 'B');
                        ?>
                        <?php if (!empty($kelompokA)): ?>
                            <tr class="table-light">
                                <td colspan="6" class="text-start fw-bold">A. Kelompok Nasional</td>
                            </tr>
                            <?php $no = 1;
                            foreach ($kelompokA as $na): ?>
                                <tr>
                                    <td class="text-start"><?= $no++ ?></td>
                                    <td class="text-start"><?= esc($na['nama_mapel']) ?></td>
                                    <td><strong><?= number_format($na['nilai_akhir'], 1) ?></strong></td>
                                    <td><strong><?= esc($na['nilai_huruf']) ?></strong></td>
                                    <td><?= isset($na['nilai_kkm']) ? number_format((float) $na['nilai_kkm'], 0) : '—' ?></td>
                                    <td>
                                        <?php if ($na['status_kelulusan'] === 'Tuntas'): ?>
                                            <span class="badge bg-success">Tuntas</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Remedial</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if (!empty($kelompokB)): ?>
                            <tr class="table-light">
                                <td colspan="6" class="text-start fw-bold">B. Kelompok Muatan Lokal</td>
                            </tr>
                            <?php $no = 1;
                            foreach ($kelompokB as $na): ?>
                                <tr>
                                    <td class="text-start"><?= $no++ ?></td>
                                    <td class="text-start"><?= esc($na['nama_mapel']) ?></td>
                                    <td><strong><?= number_format($na['nilai_akhir'], 1) ?></strong></td>
                                    <td><strong><?= esc($na['nilai_huruf']) ?></strong></td>
                                    <td><?= isset($na['nilai_kkm']) ? number_format((float) $na['nilai_kkm'], 0) : '—' ?></td>
                                    <td>
                                        <?php if ($na['status_kelulusan'] === 'Tuntas'): ?>
                                            <span class="badge bg-success">Tuntas</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Remedial</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Kehadiran -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6 class="fw-bold border-bottom pb-2 mb-3">B. DATA KEHADIRAN</h6>
                    <table class="table table-bordered table-sm text-center">
                        <tr>
                            <td class="text-start">Sakit</td>
                            <td><?= $rapor['sakit'] ?? 0 ?> hari</td>
                        </tr>
                        <tr>
                            <td class="text-start">Izin</td>
                            <td><?= $rapor['izin'] ?? 0 ?> hari</td>
                        </tr>
                        <tr>
                            <td class="text-start">Alpa (Tanpa Keterangan)</td>
                            <td><?= $rapor['alpa'] ?? 0 ?> hari</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold border-bottom pb-2 mb-3">C. CATATAN WALI KELAS</h6>
                    <div class="p-3 bg-light rounded small">
                        <?= esc($rapor['catatan_wali_kelas'] ?? 'Tidak ada catatan dari wali kelas.') ?>
                    </div>
                </div>
            </div>

            <!-- Keputusan -->
            <div class="mb-4">
                <h6 class="fw-bold border-bottom pb-2 mb-3">D. KEPUTUSAN</h6>
                <p>Berdasarkan hasil yang dicapai, siswa ditetapkan:
                    <strong
                        class="text-<?= $rapor['status_kenaikan'] === 'Naik' ? 'success' : ($rapor['status_kenaikan'] === 'Lulus' ? 'primary' : 'danger') ?>">
                        <?= esc($rapor['status_kenaikan'] ?? 'Belum Ditentukan') ?>
                    </strong>
                </p>
            </div>

            <!-- Tanda Tangan -->
            <div class="row mt-5">
                <div class="col-4 text-center">
                    <p class="mb-0">Orang Tua / Wali</p>
                    <div style="height:70px"></div>
                    <p class="mb-0 fw-bold">( _________________ )</p>
                </div>
                <div class="col-4"></div>
                <div class="col-4 text-center">
                    <p class="mb-0">Wali Kelas</p>
                    <div style="height:70px"></div>
                    <p class="mb-0 fw-bold">( _________________ )</p>
                    <small>NIP. ________________</small>
                </div>
            </div>

        </div>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>