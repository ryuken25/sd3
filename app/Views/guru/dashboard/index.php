<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-heading">Menu Utama</div>
<a href="<?= base_url('guru/dashboard') ?>" class="active"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
<div class="sidebar-heading mt-3">Input Nilai</div>
<a href="<?= base_url('guru/penilaian-agregat') ?>"><i class="bi bi-files me-2"></i> Penilaian Agregat</a>
<a href="<?= base_url('guru/capaian-kompetensi') ?>"><i class="bi bi-bookmark-check me-2"></i> Capaian Kompetensi</a>
<a href="<?= base_url('guru/template-capaian') ?>"><i class="bi bi-card-list me-2"></i> Template Capaian</a>
<a href="<?= base_url('guru/nilai-akhir') ?>"><i class="bi bi-calculator me-2"></i> Nilai Akhir</a>
<a href="<?= base_url('guru/nilai-akhir/rekap-remedial') ?>"><i class="bi bi-list-check me-2"></i> Rekap Remedial</a>
<div class="sidebar-heading mt-3">Wali Kelas</div>
<a href="<?= base_url('guru/wali-kelas') ?>"><i class="bi bi-people-fill me-2"></i> Anak Wali Kelas</a>
<div class="sidebar-heading mt-3">Lainnya</div>
<a href="<?= base_url('guru/request-buka-nilai') ?>"><i class="bi bi-unlock me-2"></i> Permintaan Buka Nilai</a>
<a href="<?= base_url('help/panduan-rapor') ?>"><i class="bi bi-question-circle me-2"></i> Panduan Penggunaan</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-pastel-primary mb-1"><i class="bi bi-speedometer2 me-2"></i>Dashboard Guru</h4>
        <p class="text-muted mb-0">Selamat datang! Kelola nilai dan remedial siswa dari sini.</p>
    </div>
    <?php if ($tahun_ajaran_aktif): ?>
        <div class="text-end">
            <span class="badge bg-pastel-success fs-6 px-3 py-2">
                <i class="bi bi-calendar-check me-1"></i>
                <?= esc($tahun_ajaran_aktif['tahun_ajaran']) ?> — Sem. <?= esc($tahun_ajaran_aktif['semester']) ?>
            </span>
            <br>
            <small class="text-muted mt-1 d-block">
                Status:
                <?php if ($tahun_ajaran_aktif['status_pengisian'] === 'Buka'): ?>
                    <span class="badge bg-pastel-warning"><i class="bi bi-unlock me-1"></i>Terbuka</span>
                <?php else: ?>
                    <span class="badge bg-pastel-danger"><i class="bi bi-lock me-1"></i>Terkunci</span>
                <?php endif; ?>
            </small>
        </div>
    <?php endif; ?>
</div>

<?php if ($tahun_ajaran_aktif && $tahun_ajaran_aktif['status_pengisian'] === 'Kunci'): ?>
    <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-lock-fill me-2 fs-5"></i>
        <div>
            <strong>Semester Dikunci!</strong> Input nilai sedang tidak diperbolehkan.
            Jika perlu mengubah nilai, <a href="<?= base_url('guru/request-buka-nilai') ?>" class="alert-link">ajukan
                permintaan buka nilai</a> ke admin.
        </div>
    </div>
<?php endif; ?>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #6baed6 !important;">
            <div class="card-body p-3">
                <p class="text-muted small mb-1 text-uppercase fw-semibold">Total Nilai Diinput</p>
                <h2 class="mb-0 fw-bold text-pastel-primary"><?= $total_nilai_input ?></h2>
                <small class="text-muted">semester ini</small>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #ff6b6b !important;">
            <div class="card-body p-3">
                <p class="text-muted small mb-1 text-uppercase fw-semibold">Remedial Belum Ditangani</p>
                <h2 class="mb-0 fw-bold <?= $total_remedial > 0 ? 'text-danger' : 'text-success' ?>">
                    <?= $total_remedial ?>
                </h2>
                <small class="text-muted">perlu tindak lanjut</small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3 h-100 bg-pastel-primary">
            <div class="card-body p-3 d-flex align-items-center">
                <i class="bi bi-info-circle fs-3 me-3" style="color:#1a5276"></i>
                <div>
                    <p class="mb-1 fw-semibold">Alur Input Nilai:</p>
                    <p class="mb-0 small">
                        1. Input semua komponen via <strong>Penilaian Agregat</strong> →
                        2. Hitung <strong>Nilai Akhir</strong> & isi tindak lanjut remedial
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Menu Cards -->
<div class="row g-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100"
            style="border-left: 4px solid var(--pastel-blue) !important;">
            <div class="card-body p-4 text-center">
                <div class="rounded-circle bg-pastel-info d-flex justify-content-center align-items-center mx-auto mb-3"
                    style="width:60px;height:60px;">
                    <i class="bi bi-files fs-3" style="color:#663d00"></i>
                </div>
                <h5 class="fw-bold mb-2">Penilaian Agregat</h5>
                <p class="text-muted small mb-3">Input Tugas, Ulangan, UTS, dan UAS dalam satu halaman sekaligus per
                    kelas dan mata pelajaran.</p>
                <a href="<?= base_url('guru/penilaian-agregat') ?>"
                    class="btn btn-primary bg-pastel-primary border-0 w-100">
                    <i class="bi bi-arrow-right-circle me-1"></i> Buka
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100"
            style="<?= $total_remedial > 0 ? 'border: 2px solid #ff6b6b !important;' : '' ?>">
            <div class="card-body p-4 text-center">
                <?php if ($total_remedial > 0): ?>
                    <div class="badge bg-danger position-absolute top-0 end-0 mt-2 me-2"><?= $total_remedial ?> perlu tindak
                        lanjut</div>
                <?php endif; ?>
                <div class="rounded-circle <?= $total_remedial > 0 ? 'bg-pastel-danger' : 'bg-pastel-success' ?> d-flex justify-content-center align-items-center mx-auto mb-3"
                    style="width:60px;height:60px;">
                    <i class="bi bi-calculator fs-3"></i>
                </div>
                <h5 class="fw-bold mb-2">Nilai Akhir</h5>
                <p class="text-muted small mb-3">Hitung nilai akhir otomatis dan isi tindak lanjut remedial siswa.</p>
                <a href="<?= base_url('guru/nilai-akhir') ?>"
                    class="btn <?= $total_remedial > 0 ? 'btn-danger' : 'btn-success' ?> border-0 w-100">
                    <i class="bi bi-arrow-right-circle me-1"></i> Buka
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body p-4 text-center">
                <div class="rounded-circle bg-pastel-warning d-flex justify-content-center align-items-center mx-auto mb-3"
                    style="width:60px;height:60px;">
                    <i class="bi bi-unlock fs-3" style="color:#7d4e00"></i>
                </div>
                <h5 class="fw-bold mb-2">Permintaan Buka Nilai</h5>
                <p class="text-muted small mb-3">Ajukan permintaan ke admin untuk membuka sementara nilai yang sudah
                    dikunci.</p>
                <a href="<?= base_url('guru/request-buka-nilai') ?>" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-arrow-right-circle me-1"></i> Buka
                </a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>