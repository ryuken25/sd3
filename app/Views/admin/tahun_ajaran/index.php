<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-heading">Menu Utama</div>
<a href="<?= base_url('admin/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>

<div class="sidebar-heading mt-3">Data Master</div>
<a href="<?= base_url('admin/tahun-ajaran') ?>" class="active"><i class="bi bi-calendar3 me-2"></i> Tahun Ajaran</a>
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
<?= $this->section('styles') ?>
<style>
    .btn-cta-primary {
        background-color: #2d5f8f !important;
        border-color: #2d5f8f !important;
        color: #fff !important;
    }

    .btn-cta-primary:hover,
    .btn-cta-primary:focus {
        background-color: #244d73 !important;
        border-color: #244d73 !important;
        color: #fff !important;
    }
</style>
<?= $this->endSection() ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-pastel-primary fw-bold">Manajemen Tahun Ajaran</h4>
    <button type="button" class="btn btn-cta-primary border-0 shadow-sm" data-bs-toggle="modal"
        data-bs-target="#tambahTaModal">
        <i class="bi bi-plus-circle me-1"></i> Tambah Baru
    </button>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="bg-light">
                    <tr>
                        <th>No</th>
                        <th>Tahun Ajaran</th>
                        <th>Semester</th>
                        <th>Periode</th>
                        <th>Status Editor</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($ta)): ?>
                        <?php $no = 1;
                        foreach ($ta as $t): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong><?= esc($t['tahun_ajaran']) ?></strong></td>
                                <td>Semester <?= esc($t['semester']) ?></td>
                                <td><small><?= date('d/m/Y', strtotime($t['tanggal_mulai'])) ?> —
                                        <?= date('d/m/Y', strtotime($t['tanggal_selesai'])) ?></small></td>
                                <td>
                                    <?php if ($t['status_pengisian'] === 'Buka'): ?>
                                        <span class="badge bg-pastel-warning"><i class="bi bi-unlock me-1"></i>Buka</span>
                                    <?php else: ?>
                                        <span class="badge bg-pastel-danger"><i class="bi bi-lock me-1"></i>Kunci</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($t['aktif'] === 'aktif'): ?>
                                        <span class="badge bg-pastel-success">Aktif Saat Ini</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Non-Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                            data-bs-target="#editTaModal<?= $t['id_tahun_ajaran'] ?>" title="Edit tahun ajaran">
                                            <i class="bi bi-pencil me-1"></i>Edit
                                        </button>
                                        <?php if ($t['aktif'] !== 'aktif'): ?>
                                            <form action="<?= base_url('admin/tahun-ajaran/set-aktif/' . $t['id_tahun_ajaran']) ?>"
                                                method="post" class="d-inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-sm btn-outline-success"
                                                    title="Set sebagai tahun ajaran aktif">
                                                    <i class="bi bi-check-circle me-1"></i>Set Aktif
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <form
                                            action="<?= base_url('admin/tahun-ajaran/toggle-kunci/' . $t['id_tahun_ajaran']) ?>"
                                            method="post" class="d-inline">
                                            <?= csrf_field() ?>
                                            <?php if ($t['status_pengisian'] === 'Buka'): ?>
                                                <button type="submit" class="btn btn-sm btn-outline-warning"
                                                    title="Kunci semester - menutup input nilai"
                                                    onclick="return confirm('Kunci semester ini? Guru tidak dapat mengubah nilai.')">
                                                    <i class="bi bi-lock me-1"></i>Kunci
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-sm btn-outline-primary"
                                                    title="Buka kembali semester - izinkan input nilai"
                                                    onclick="return confirm('Buka kembali semester ini? Guru dapat mengubah nilai lagi.')">
                                                    <i class="bi bi-unlock me-1"></i>Buka
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <div class="modal fade" id="editTaModal<?= $t['id_tahun_ajaran'] ?>" tabindex="-1"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header border-bottom-0">
                                            <h5 class="modal-title fw-bold">Edit Tahun Ajaran</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <form action="<?= base_url('admin/tahun-ajaran/update/' . $t['id_tahun_ajaran']) ?>"
                                            method="post">
                                            <?= csrf_field() ?>
                                            <div class="modal-body p-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Tahun Ajaran <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="tahun_ajaran" required
                                                        pattern="[0-9]{4}/[0-9]{4}" maxlength="9"
                                                        value="<?= esc($t['tahun_ajaran']) ?>" placeholder="Contoh: 2026/2027">
                                                    <div class="form-text">Gunakan format empat digit tahun, garis miring, empat
                                                        digit tahun. Contoh: 2026/2027.</div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Semester <span
                                                            class="text-danger">*</span></label>
                                                    <select class="form-select" name="semester" required>
                                                        <option value="Ganjil" <?= $t['semester'] === 'Ganjil' ? 'selected' : '' ?>>Semester 1 (Ganjil)</option>
                                                        <option value="Genap" <?= $t['semester'] === 'Genap' ? 'selected' : '' ?>>
                                                            Semester 2 (Genap)</option>
                                                    </select>
                                                </div>
                                                <div class="row">
                                                    <div class="col-6">
                                                        <label class="form-label">Tanggal Mulai <span
                                                                class="text-danger">*</span></label>
                                                        <input type="date" class="form-control" name="tanggal_mulai"
                                                            value="<?= esc($t['tanggal_mulai']) ?>" required>
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label">Tanggal Selesai <span
                                                                class="text-danger">*</span></label>
                                                        <input type="date" class="form-control" name="tanggal_selesai"
                                                            value="<?= esc($t['tanggal_selesai']) ?>" required>
                                                    </div>
                                                </div>
                                                <div class="form-text mt-2">Tanggal mulai harus lebih awal dari tanggal selesai.
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light border-top-0">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-cta-primary border-0 shadow-sm"><i
                                                        class="bi bi-save me-1"></i> Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Belum ada data Tahun Ajaran.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah TA -->
<div class="modal fade" id="tambahTaModal" tabindex="-1" aria-labelledby="tambahTaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold" id="tambahTaLabel">Tambah Tahun Ajaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('admin/tahun-ajaran/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="tahun_ajaran" required pattern="[0-9]{4}/[0-9]{4}"
                            maxlength="9" placeholder="Contoh: 2026/2027">
                        <div class="form-text">Format wajib mengikuti contoh: 2026/2027.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Semester <span class="text-danger">*</span></label>
                        <select class="form-select" name="semester" required>
                            <option value="Ganjil">Semester 1 (Ganjil)</option>
                            <option value="Genap">Semester 2 (Genap)</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal_mulai" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal_selesai" required>
                        </div>
                    </div>
                    <div class="form-text mt-2">Tanggal mulai tidak boleh sama atau lebih besar dari tanggal selesai.
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-cta-primary border-0 shadow-sm"><i class="bi bi-save me-1"></i>
                        Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>