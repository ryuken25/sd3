<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-heading">Menu Saya</div>
<a href="<?= base_url('orangtua/dashboard') ?>" class="active"><i class="bi bi-house me-2"></i> Dashboard</a>
<div class="sidebar-heading mt-3">Akademik Anak</div>
<?php foreach ($siswa_data as $s): ?>
    <a href="<?= base_url('orangtua/grades/' . $s['id_siswa']) ?>">
        <i class="bi bi-bar-chart me-2"></i> <?= esc($s['nama_siswa']) ?>
    </a>
<?php endforeach; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card border-0 shadow-sm mb-4 overflow-hidden">
    <div class="card-body p-4 p-lg-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge bg-pastel-primary mb-3"><i class="bi bi-person-heart me-1"></i>Akun Orang Tua</span>
                <h4 class="fw-bold text-pastel-primary mb-2"><i class="bi bi-house me-2"></i>Dashboard Orang Tua</h4>
                <p class="text-muted mb-0">Selamat datang,
                    <strong><?= esc((string) session()->get('nama_lengkap')) ?></strong>. Pantau nilai, status
                    ketuntasan, dan ketersediaan e-rapor anak secara ringkas dari satu halaman.
                </p>
            </div>
            <div class="col-lg-4">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="rounded-4 border bg-light p-3 h-100">
                            <div class="small text-muted mb-1">Total Anak</div>
                            <div class="fs-4 fw-bold text-pastel-primary"><?= count($siswa_data) ?></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="rounded-4 border bg-light p-3 h-100">
                            <div class="small text-muted mb-1">Rapor Tersedia</div>
                            <div class="fs-4 fw-bold text-success">
                                <?= count(array_filter($student_overview ?? [], static fn($item) => !empty($item['rapor_tersedia']))) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($tahun_ajaran_aktif)): ?>
    <div class="alert alert-info d-flex align-items-center mb-4">
        <i class="bi bi-calendar-check me-2 fs-5"></i>
        <div>Semester Aktif: <strong><?= esc($tahun_ajaran_aktif['tahun_ajaran']) ?> — Semester
                <?= $tahun_ajaran_aktif['semester'] ?></strong></div>
    </div>
<?php endif; ?>

<div class="row g-4">
    <?php foreach ($siswa_data as $s): ?>
        <?php $overview = $student_overview[$s['id_siswa']] ?? ['total_mapel' => 0, 'jumlah_tuntas' => 0, 'jumlah_remedial' => 0, 'rapor_tersedia' => false]; ?>
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($s['nama_siswa']) ?>&background=AEC6CF&color=1a5276&bold=true&size=60"
                            class="rounded-circle me-3" width="60" height="60" alt="Foto">
                        <div>
                            <h6 class="mb-0 fw-bold"><?= esc($s['nama_siswa']) ?></h6>
                            <small class="text-muted">NIS: <?= esc($s['nis']) ?></small>
                        </div>
                    </div>

                    <div class="mb-3 small">
                        <div class="row g-1">
                            <div class="col-5 text-muted">NISN</div>
                            <div class="col-7"><strong><?= esc($s['nisn'] ?? '-') ?></strong></div>
                            <div class="col-5 text-muted">Jenis Kelamin</div>
                            <div class="col-7">
                                <strong><?= $s['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></strong>
                            </div>
                            <div class="col-5 text-muted">Status</div>
                            <div class="col-7"><span class="badge bg-pastel-success"><?= ucfirst($s['status']) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <div class="rounded-3 bg-light border p-2 text-center h-100">
                                <div class="small text-muted">Mapel</div>
                                <div class="fw-bold"><?= $overview['total_mapel'] ?></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="rounded-3 bg-light border p-2 text-center h-100">
                                <div class="small text-muted">Tuntas</div>
                                <div class="fw-bold text-success"><?= $overview['jumlah_tuntas'] ?></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="rounded-3 bg-light border p-2 text-center h-100">
                                <div class="small text-muted">Remedial</div>
                                <div class="fw-bold text-danger"><?= $overview['jumlah_remedial'] ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between rounded-3 border px-3 py-2 mb-3">
                        <span class="small text-muted">Status e-Rapor</span>
                        <?php if (!empty($overview['rapor_tersedia'])): ?>
                            <span class="badge bg-success">Tersedia</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Belum tersedia</span>
                        <?php endif; ?>
                    </div>

                    <a href="<?= base_url('orangtua/grades/' . $s['id_siswa']) ?>"
                        class="btn btn-primary bg-pastel-primary border-0 w-100 fw-semibold">
                        <i class="bi bi-bar-chart-line me-1"></i> Lihat Nilai dan Rapor
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($siswa_data)): ?>
        <div class="col-12">
            <div class="text-center py-5 text-muted">
                <i class="bi bi-person-x fs-1 d-block mb-3"></i>
                <h6>Tidak ada data siswa yang terhubung ke akun Anda.</h6>
                <p class="small">Hubungi Administrator sekolah jika terjadi kesalahan.</p>
            </div>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>