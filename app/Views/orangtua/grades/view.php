<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-heading">Menu Saya</div>
<a href="<?= base_url('orangtua/dashboard') ?>"><i class="bi bi-house me-2"></i> Dashboard</a>
<div class="sidebar-heading mt-3">Akademik</div>
<a href="<?= base_url('orangtua/grades/' . $siswa['id_siswa']) ?>" class="active"><i class="bi bi-bar-chart me-2"></i>
    Nilai <?= esc($siswa['nama_siswa']) ?></a>
<a href="<?= base_url('orangtua/rapor/' . $siswa['id_siswa'] . '/' . ($tahun_ajaran['id_tahun_ajaran'] ?? '')) ?>"><i
        class="bi bi-file-earmark-text me-2"></i> E-Rapor</a>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .nilai-card {
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: default;
    }

    .nilai-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1) !important;
    }

    .remedial-detail {
        display: none;
    }

    .nilai-card.remedial {
        border-left: 5px solid #FF6961 !important;
        background: #fff5f5;
        cursor: pointer;
    }

    .nilai-card.tuntas {
        border-left: 5px solid #77DD77 !important;
        background: #f0fff0;
    }

    .nilai-card.belum {
        border-left: 5px solid #ccc !important;
        background: #fafafa;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-pastel-primary mb-1"><i class="bi bi-bar-chart-line me-2"></i>Rekap Nilai Akademik</h4>
        <p class="text-muted mb-0">
            <strong><?= esc($siswa['nama_siswa']) ?></strong> |
            <?= esc($tahun_ajaran['tahun_ajaran'] ?? '-') ?> Semester <?= $tahun_ajaran['semester'] ?? '-' ?>
        </p>
    </div>
    <a href="<?= base_url('orangtua/dashboard') ?>" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<?php
$remedialCount = count(array_filter(
    $nilai_data,
    fn($n) =>
    isset($n['nilai_akhir']) && $n['nilai_akhir'] && $n['nilai_akhir']['status_kelulusan'] === 'Remedial'
));
?>

<?php if (session()->getFlashdata('info')): ?>
    <div class="alert alert-info mb-4"><i class="bi bi-info-circle me-2"></i><?= session()->getFlashdata('info') ?></div>
<?php endif; ?>

<?php if (empty($rapor_tersedia)): ?>
    <div class="alert alert-warning mb-4">
        <i class="bi bi-lock me-2"></i>Rapor belum difinalisasi oleh admin/wali kelas. Nilai dan e-rapor akan tampil setelah
        proses finalisasi selesai.
    </div>
<?php endif; ?>

<?php if ($remedialCount > 0): ?>
    <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <div>
            <strong>Perhatian!</strong> <?= esc($siswa['nama_siswa']) ?> memiliki
            <strong><?= $remedialCount ?> mata pelajaran</strong> dengan nilai di bawah KKM. Klik kartu merah untuk melihat
            catatan guru.
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($rapor_tersedia)): ?>
    <div class="row g-3">
        <?php foreach ($nilai_data as $item):
            $na = $item['nilai_akhir'];
            $kkm = $item['kkm'];
            $remedial = $item['remedial'];
            $mapelItem = $item['mapel'];

            if ($na && $na['status_kelulusan'] === 'Tuntas') {
                $isRemedial = false;
                $cardClass = 'tuntas';
                $badgeClass = 'bg-pastel-success';
                $badgeText = '✓ Tuntas';
            } elseif ($na && $na['status_kelulusan'] === 'Remedial') {
                $isRemedial = true;
                $cardClass = 'remedial';
                $badgeClass = 'bg-pastel-danger';
                $badgeText = '⚠ Remedial';
            } else {
                $isRemedial = false;
                $cardClass = 'belum';
                $badgeClass = 'bg-secondary';
                $badgeText = 'Belum dinilai';
            }
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm nilai-card <?= $cardClass ?>" <?= $isRemedial ? 'onclick="toggleRemedial(this)"' : '' ?>>
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="fw-bold mb-0"><?= esc($mapelItem['nama_mapel']) ?></h6>
                                <small class="text-muted">Kelompok <?= esc($mapelItem['kelompok']) ?></small>
                            </div>
                            <span class="badge <?= $badgeClass ?>"><?= $badgeText ?></span>
                        </div>

                        <?php if ($na): ?>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <span class="fs-4 fw-bold <?= $isRemedial ? 'text-danger' : 'text-success' ?>">
                                        <?= number_format($na['nilai_akhir'], 1) ?>
                                    </span>
                                    <small class="text-muted"> / KKM:
                                        <?= $kkm ? number_format($kkm['nilai_kkm'], 0) : '—' ?></small>
                                </div>
                                <span class="badge bg-light text-dark border fs-6"><?= esc($na['nilai_huruf']) ?></span>
                            </div>
                        <?php else: ?>
                            <p class="text-muted small mt-2 mb-0">Nilai akhir belum dihitung oleh guru.</p>
                        <?php endif; ?>

                        <?php if ($isRemedial && $remedial): ?>
                            <div class="remedial-detail mt-3 p-3 rounded" style="background:#fff0f0; border:1px solid #ffb3b0;">
                                <p class="fw-bold text-danger mb-1 small"><i class="bi bi-chat-left-text me-1"></i>Catatan Guru /
                                    Tindak Lanjut:</p>
                                <p class="mb-0 small"><?= esc($remedial['tindak_lanjut'] ?? 'Belum ada catatan.') ?></p>
                                <p class="text-muted mb-0 small mt-1">Status: <?= esc($remedial['status_remedial']) ?></p>
                            </div>
                        <?php elseif ($isRemedial): ?>
                            <div class="remedial-detail mt-3 p-3 rounded" style="background:#fff0f0; border:1px solid #ffb3b0;">
                                <p class="text-muted small mb-0"><i class="bi bi-clock me-1"></i>Catatan tindak lanjut belum
                                    ditambahkan guru.</p>
                            </div>
                        <?php endif; ?>

                        <?php if ($isRemedial): ?>
                            <p class="text-danger small mt-2 mb-0"><i class="bi bi-hand-index me-1"></i>Klik untuk
                                melihat/sembunyikan catatan guru</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="mt-4">
    <?php if (!empty($rapor_tersedia)): ?>
        <a href="<?= base_url('orangtua/rapor/' . $siswa['id_siswa'] . '/' . $tahun_ajaran['id_tahun_ajaran']) ?>"
            class="btn btn-outline-primary">
            <i class="bi bi-file-earmark-text me-1"></i> Lihat E-Rapor
        </a>
        <a href="<?= base_url('orangtua/rapor/download/' . $siswa['id_siswa'] . '/' . $tahun_ajaran['id_tahun_ajaran']) ?>"
            class="btn btn-primary bg-pastel-primary border-0 ms-2">
            <i class="bi bi-download me-1"></i> Download PDF Rapor
        </a>
    <?php else: ?>
        <div class="alert alert-info d-inline-block">
            <i class="bi bi-info-circle me-1"></i> Rapor belum difinalisasi oleh admin/wali kelas.
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function toggleRemedial(card) {
        const detail = card.querySelector('.remedial-detail');
        if (detail) {
            detail.style.display = detail.style.display === 'block' ? 'none' : 'block';
        }
    }
</script>
<?= $this->endSection() ?>