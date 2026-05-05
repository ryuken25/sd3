<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
    <div class="sidebar-heading">Menu Utama</div>
    <a href="<?= base_url('admin/dashboard') ?>" class="active"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>

    <div class="sidebar-heading mt-3">Data Master</div>
    <a href="<?= base_url('admin/tahun-ajaran') ?>"><i class="bi bi-calendar3 me-2"></i> Tahun Ajaran</a>
    <a href="<?= base_url('admin/kelas') ?>"><i class="bi bi-door-open me-2"></i> Kelas</a>
    <a href="<?= base_url('admin/mapel') ?>"><i class="bi bi-book me-2"></i> Mata Pelajaran</a>
    <a href="<?= base_url('admin/kkm') ?>"><i class="bi bi-bar-chart-line me-2"></i> KKM</a>
    <a href="<?= base_url('admin/siswa') ?>"><i class="bi bi-people me-2"></i> Data Siswa</a>
    <a href="<?= base_url('admin/guru') ?>"><i class="bi bi-person-badge me-2"></i> Data Guru</a>

    <div class="sidebar-heading mt-3">Alat & Laporan</div>
    <a href="<?= base_url('admin/import') ?>"><i class="bi bi-upload me-2"></i> Import Data</a>
    <a href="<?= base_url('admin/request-buka-nilai') ?>"><i class="bi bi-unlock me-2"></i> Permintaan Buka Nilai</a>
    <a href="<?= base_url('admin/rapor') ?>"><i class="bi bi-file-earmark-text me-2"></i> Manajemen Rapor</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-pastel-primary mb-1"><i class="bi bi-speedometer2 me-2"></i>Dashboard Admin</h4>
        <p class="text-muted mb-0">Selamat datang di Panel Administrasi SDN 3 Mekarsari</p>
    </div>
    <?php if ($tahun_ajaran_aktif): ?>
    <div class="text-end">
        <span class="badge bg-pastel-success fs-6 px-3 py-2">
            <i class="bi bi-calendar-check me-1"></i>
            <?= esc($tahun_ajaran_aktif['tahun_ajaran']) ?> — Sem. <?= esc($tahun_ajaran_aktif['semester']) ?>
        </span>
        <br>
        <small class="text-muted">Tahun Ajaran Aktif</small>
    </div>
    <?php else: ?>
    <a href="<?= base_url('admin/tahun-ajaran') ?>" class="btn btn-warning btn-sm">
        <i class="bi bi-exclamation-triangle me-1"></i> Set Tahun Ajaran Aktif
    </a>
    <?php endif; ?>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #6baed6 !important;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1 text-uppercase fw-semibold">Total Siswa Aktif</p>
                        <h2 class="mb-0 fw-bold text-pastel-primary"><?= $total_siswa ?></h2>
                    </div>
                    <div class="rounded-circle bg-pastel-primary d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                        <i class="bi bi-people-fill fs-4" style="color:#1a5276"></i>
                    </div>
                </div>
                <a href="<?= base_url('admin/siswa') ?>" class="text-decoration-none small text-muted mt-2 d-block">
                    <i class="bi bi-arrow-right me-1"></i>Lihat semua siswa
                </a>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #74c476 !important;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1 text-uppercase fw-semibold">Total Guru</p>
                        <h2 class="mb-0 fw-bold text-pastel-success"><?= $total_guru ?></h2>
                    </div>
                    <div class="rounded-circle bg-pastel-success d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                        <i class="bi bi-person-badge-fill fs-4" style="color:#145a14"></i>
                    </div>
                </div>
                <a href="<?= base_url('admin/guru') ?>" class="text-decoration-none small text-muted mt-2 d-block">
                    <i class="bi bi-arrow-right me-1"></i>Lihat semua guru
                </a>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #fdae6b !important;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1 text-uppercase fw-semibold">Total Kelas</p>
                        <h2 class="mb-0 fw-bold text-pastel-warning"><?= $total_kelas ?></h2>
                    </div>
                    <div class="rounded-circle bg-pastel-warning d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                        <i class="bi bi-door-open-fill fs-4" style="color:#7d4e00"></i>
                    </div>
                </div>
                <a href="<?= base_url('admin/kelas') ?>" class="text-decoration-none small text-muted mt-2 d-block">
                    <i class="bi bi-arrow-right me-1"></i>Lihat semua kelas
                </a>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #ff6b6b !important;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1 text-uppercase fw-semibold">Remedial Belum Selesai</p>
                        <h2 class="mb-0 fw-bold <?= $total_remedial > 0 ? 'text-danger' : 'text-pastel-success' ?>"><?= $total_remedial ?></h2>
                    </div>
                    <div class="rounded-circle <?= $total_remedial > 0 ? 'bg-pastel-danger' : 'bg-pastel-success' ?> d-flex align-items-center justify-content-center" style="width:50px;height:50px;">
                        <i class="bi bi-exclamation-triangle-fill fs-4 <?= $total_remedial > 0 ? 'text-danger' : '' ?>"></i>
                    </div>
                </div>
                <span class="text-muted small mt-2 d-block">
                    <?= $total_remedial > 0 ? '<i class="bi bi-clock me-1"></i>Perlu tindak lanjut guru' : '<i class="bi bi-check-circle me-1"></i>Semua tuntas' ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row g-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-transparent border-0 pt-3 pb-0">
                <h6 class="fw-bold mb-0"><i class="bi bi-lightning-charge me-2 text-warning"></i>Aksi Cepat</h6>
            </div>
            <div class="card-body pt-2">
                <div class="row g-2">
                    <div class="col-6">
                        <a href="<?= base_url('admin/siswa') ?>" class="btn btn-outline-primary w-100 text-start">
                            <i class="bi bi-person-plus me-2"></i>Tambah Siswa
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?= base_url('admin/guru') ?>" class="btn btn-outline-success w-100 text-start">
                            <i class="bi bi-person-badge me-2"></i>Tambah Guru
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?= base_url('admin/import') ?>" class="btn btn-outline-info w-100 text-start">
                            <i class="bi bi-upload me-2"></i>Import Data
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?= base_url('admin/rapor') ?>" class="btn btn-outline-warning w-100 text-start">
                            <i class="bi bi-file-earmark-text me-2"></i>Manajemen Rapor
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?= base_url('admin/request-buka-nilai') ?>" class="btn btn-outline-danger w-100 text-start">
                            <i class="bi bi-unlock me-2"></i>Permintaan Buka Nilai
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="<?= base_url('admin/tahun-ajaran') ?>" class="btn btn-outline-secondary w-100 text-start">
                            <i class="bi bi-calendar3 me-2"></i>Tahun Ajaran
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-transparent border-0 pt-3 pb-0">
                <h6 class="fw-bold mb-0"><i class="bi bi-info-circle me-2 text-info"></i>Status Sistem</h6>
            </div>
            <div class="card-body pt-2">
                <?php if ($tahun_ajaran_aktif): ?>
                <table class="table table-borderless table-sm small mb-0">
                    <tr>
                        <td class="text-muted" style="width:160px">Tahun Ajaran</td>
                        <td>: <strong><?= esc($tahun_ajaran_aktif['tahun_ajaran']) ?></strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Semester</td>
                        <td>: <?= esc($tahun_ajaran_aktif['semester']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status Pengisian</td>
                        <td>:
                            <?php if ($tahun_ajaran_aktif['status_pengisian'] === 'Buka'): ?>
                                <span class="badge bg-pastel-warning"><i class="bi bi-unlock me-1"></i>Terbuka</span>
                            <?php else: ?>
                                <span class="badge bg-pastel-danger"><i class="bi bi-lock me-1"></i>Terkunci</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Periode</td>
                        <td>: <small><?= date('d/m/Y', strtotime($tahun_ajaran_aktif['tanggal_mulai'])) ?> — <?= date('d/m/Y', strtotime($tahun_ajaran_aktif['tanggal_selesai'])) ?></small></td>
                    </tr>
                </table>
                <a href="<?= base_url('admin/tahun-ajaran') ?>" class="btn btn-sm btn-outline-secondary mt-2">
                    <i class="bi bi-gear me-1"></i>Kelola Tahun Ajaran
                </a>
                <?php else: ?>
                <div class="alert alert-warning mb-0">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Belum ada Tahun Ajaran yang aktif.
                    <a href="<?= base_url('admin/tahun-ajaran') ?>" class="alert-link">Set sekarang →</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
