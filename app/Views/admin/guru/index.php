<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-heading">Menu Utama</div>
<a href="<?= base_url('admin/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>

<div class="sidebar-heading mt-3">Data Master</div>
<a href="<?= base_url('admin/tahun-ajaran') ?>"><i class="bi bi-calendar3 me-2"></i> Tahun Ajaran</a>
<a href="<?= base_url('admin/kelas') ?>"><i class="bi bi-door-open me-2"></i> Kelas</a>
<a href="<?= base_url('admin/mapel') ?>"><i class="bi bi-book me-2"></i> Mata Pelajaran</a>
<a href="<?= base_url('admin/kkm') ?>"><i class="bi bi-bar-chart-line me-2"></i> KKM</a>
<a href="<?= base_url('admin/siswa') ?>"><i class="bi bi-people me-2"></i> Data Siswa</a>
<a href="<?= base_url('admin/guru') ?>" class="active"><i class="bi bi-person-badge me-2"></i> Data Guru</a>

<div class="sidebar-heading mt-3">Alat & Laporan</div>
<a href="<?= base_url('admin/import') ?>"><i class="bi bi-upload me-2"></i> Import Data</a>
<a href="<?= base_url('admin/request-buka-nilai') ?>"><i class="bi bi-unlock me-2"></i> Permintaan Buka Nilai</a>
<a href="<?= base_url('admin/rapor') ?>"><i class="bi bi-file-earmark-text me-2"></i> Manajemen Rapor</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-pastel-primary fw-bold">Manajemen Data Guru</h4>
    <button type="button" class="btn btn-primary bg-pastel-primary border-0 shadow-sm" data-bs-toggle="modal"
        data-bs-target="#tambahGuruModal">
        <i class="bi bi-plus-circle me-1"></i> Tambah Guru Baru
    </button>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="bg-light">
                    <tr>
                        <th>No</th>
                        <th>Username (Login)</th>
                        <th>Nama Lengkap & Gelar</th>
                        <th>No Telepon</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($guru)): ?>
                        <?php $no = 1;
                        foreach ($guru as $g): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong><?= esc($g['username']) ?></strong></td>
                                <td><?= esc($g['nama_lengkap']) ?></td>
                                <td><?= esc($g['no_telp'] ?? '-') ?></td>
                                <td>
                                    <?php if ($g['status'] == 'aktif'): ?>
                                        <span class="badge bg-pastel-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= ucfirst(esc($g['status'])) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#editGuruModal<?= $g['id_user'] ?>"><i
                                            class="bi bi-pencil"></i></button>
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                        data-bs-target="#hapusGuruModal<?= $g['id_user'] ?>"><i
                                            class="bi bi-trash"></i></button>
                                </td>
                            </tr>

                            <!-- Modal Edit Guru -->
                            <div class="modal fade" id="editGuruModal<?= $g['id_user'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-pastel-sidebar text-pastel-primary border-bottom-0">
                                            <h5 class="modal-title fw-bold">Edit Data Guru</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <form action="<?= base_url('admin/guru/update/' . $g['id_user']) ?>" method="post">
                                            <?= csrf_field() ?>
                                            <div class="modal-body p-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Nama Lengkap & Gelar <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" name="nama_lengkap" required
                                                        value="<?= esc($g['nama_lengkap']) ?>">
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Username Login <span
                                                                class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" name="username" required
                                                            value="<?= esc($g['username']) ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Password Baru</label>
                                                        <input type="password" class="form-control" name="password"
                                                            placeholder="Kosongkan jika tidak diubah">
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-md-12">
                                                        <label class="form-label">No Telepon</label>
                                                        <input type="text" class="form-control" name="no_telp"
                                                            value="<?= esc($g['no_telp'] ?? '') ?>">
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Status</label>
                                                    <select class="form-select" name="status">
                                                        <option value="aktif" <?= $g['status'] == 'aktif' ? 'selected' : '' ?>>
                                                            Aktif</option>
                                                        <option value="nonaktif" <?= $g['status'] == 'nonaktif' ? 'selected' : '' ?>>Non-Aktif</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light border-top-0">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Batal</button>
                                                <button type="submit"
                                                    class="btn btn-primary bg-pastel-primary border-0 shadow-sm"><i
                                                        class="bi bi-save me-1"></i> Update Guru</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Hapus Guru -->
                            <div class="modal fade" id="hapusGuruModal<?= $g['id_user'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-sm">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-danger text-white border-bottom-0">
                                            <h5 class="modal-title fw-bold">Konfirmasi Hapus</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-center p-4">
                                            <i class="bi bi-exclamation-triangle-fill text-danger fs-1 mb-3 d-block"></i>
                                            <p>Yakin ingin menghapus guru <strong><?= esc($g['nama_lengkap']) ?></strong>?</p>
                                            <small class="text-muted">Akun login guru ini juga akan dihapus. Data yang sudah
                                                dihapus tidak dapat dikembalikan.</small>
                                        </div>
                                        <div class="modal-footer border-top-0 justify-content-center">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Batal</button>
                                            <form action="<?= base_url('admin/guru/delete/' . $g['id_user']) ?>" method="post"
                                                class="d-inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-1"></i>
                                                    Hapus</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Belum ada data guru.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Guru -->
<div class="modal fade" id="tambahGuruModal" tabindex="-1" aria-labelledby="tambahGuruModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-pastel-sidebar text-pastel-primary border-bottom-0">
                <h5 class="modal-title fw-bold" id="tambahGuruModalLabel">Tambah Data Guru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('admin/guru/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_lengkap" required
                            placeholder="Contoh: Ni Wayan, S.Pd.">
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Username Login <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="username" required placeholder="guru...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">No Telepon</label>
                            <input type="text" class="form-control" name="no_telp">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary bg-pastel-primary border-0 shadow-sm"><i
                            class="bi bi-save me-1"></i> Simpan Guru</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
