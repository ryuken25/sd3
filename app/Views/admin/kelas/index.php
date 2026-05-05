<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-heading">Menu Utama</div>
<a href="<?= base_url('admin/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>

<div class="sidebar-heading mt-3">Data Master</div>
<a href="<?= base_url('admin/tahun-ajaran') ?>"><i class="bi bi-calendar3 me-2"></i> Tahun Ajaran</a>
<a href="<?= base_url('admin/kelas') ?>" class="active"><i class="bi bi-door-open me-2"></i> Kelas</a>
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
    <h4 class="mb-0 text-pastel-primary fw-bold">Manajemen Kelas</h4>
    <button type="button" class="btn btn-primary bg-pastel-primary border-0 shadow-sm" data-bs-toggle="modal"
        data-bs-target="#tambahKelasModal">
        <i class="bi bi-plus-circle me-1"></i> Tambah Kelas
    </button>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="bg-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Kelas</th>
                        <th>Tingkat</th>
                        <th>Wali Kelas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($kelas)): ?>
                        <?php $no = 1;
                        foreach ($kelas as $k): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><span class="badge bg-pastel-info fs-6"><?= esc($k['nama_kelas']) ?></span></td>
                                <td>Tingkat <?= esc($k['tingkat']) ?></td>
                                <td>
                                    <?php if ($k['wali_kelas_nama']): ?>
                                        <span class="text-success"><i
                                                class="bi bi-person-check me-1"></i><?= esc($k['wali_kelas_nama']) ?></span>
                                    <?php else: ?>
                                        <span class="text-danger fst-italic"><i class="bi bi-person-x me-1"></i>Belum ada wali
                                            kelas</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#editKelasModal<?= $k['id_kelas'] ?>"><i
                                            class="bi bi-pencil"></i></button>
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                        data-bs-target="#hapusKelasModal<?= $k['id_kelas'] ?>"><i
                                            class="bi bi-trash"></i></button>
                                </td>
                            </tr>

                            <!-- Modal Edit Kelas -->
                            <div class="modal fade" id="editKelasModal<?= $k['id_kelas'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-pastel-sidebar text-pastel-primary border-bottom-0">
                                            <h5 class="modal-title fw-bold">Edit Data Kelas</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <form action="<?= base_url('admin/kelas/update/' . $k['id_kelas']) ?>" method="post">
                                            <?= csrf_field() ?>
                                            <div class="modal-body p-4">
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Nama Kelas <span
                                                                class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" name="nama_kelas" required
                                                            value="<?= esc($k['nama_kelas']) ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Tingkat <span
                                                                class="text-danger">*</span></label>
                                                        <select class="form-select" name="tingkat" required>
                                                            <?php for ($i = 1; $i <= 6; $i++): ?>
                                                                <option value="<?= $i ?>" <?= $k['tingkat'] == $i ? 'selected' : '' ?>>
                                                                    Tingkat <?= $i ?></option>
                                                            <?php endfor; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Wali Kelas</label>
                                                    <select class="form-select" name="wali_kelas">
                                                        <option value="">-- Pilih Wali Kelas --</option>
                                                        <?php foreach ($guru as $g): ?>
                                                            <option value="<?= $g['id_user'] ?>" <?= $k['wali_kelas'] == $g['id_user'] ? 'selected' : '' ?>><?= esc($g['nama_lengkap']) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light border-top-0">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Batal</button>
                                                <button type="submit"
                                                    class="btn btn-primary bg-pastel-primary border-0 shadow-sm"><i
                                                        class="bi bi-save me-1"></i> Update Kelas</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Hapus Kelas -->
                            <div class="modal fade" id="hapusKelasModal<?= $k['id_kelas'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-sm">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-danger text-white border-bottom-0">
                                            <h5 class="modal-title fw-bold">Konfirmasi Hapus</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-center p-4">
                                            <i class="bi bi-exclamation-triangle-fill text-danger fs-1 mb-3 d-block"></i>
                                            <p>Yakin ingin menghapus kelas <strong><?= esc($k['nama_kelas']) ?></strong>?</p>
                                            <small class="text-muted">Data yang sudah dihapus tidak dapat dikembalikan.</small>
                                        </div>
                                        <div class="modal-footer border-top-0 justify-content-center">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Batal</button>
                                            <form action="<?= base_url('admin/kelas/delete/' . $k['id_kelas']) ?>" method="post"
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
                            <td colspan="5" class="text-center py-4 text-muted">Belum ada data kelas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Kelas -->
<div class="modal fade" id="tambahKelasModal" tabindex="-1" aria-labelledby="tambahKelasModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-pastel-sidebar text-pastel-primary border-bottom-0">
                <h5 class="modal-title fw-bold" id="tambahKelasModalLabel">Tambah Data Kelas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('admin/kelas/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama_kelas" required
                                placeholder="Contoh: 1A, V B">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tingkat <span class="text-danger">*</span></label>
                            <select class="form-select" name="tingkat" required>
                                <option value="1">Tingkat 1 (Satu)</option>
                                <option value="2">Tingkat 2 (Dua)</option>
                                <option value="3">Tingkat 3 (Tiga)</option>
                                <option value="4">Tingkat 4 (Empat)</option>
                                <option value="5">Tingkat 5 (Lima)</option>
                                <option value="6">Tingkat 6 (Enam)</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Wali Kelas</label>
                        <select class="form-select" name="wali_kelas">
                            <option value="">-- Pilih Wali Kelas --</option>
                            <?php foreach ($guru as $g): ?>
                                <option value="<?= $g['id_user'] ?>"><?= esc($g['nama_lengkap']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary bg-pastel-primary border-0 shadow-sm"><i
                            class="bi bi-save me-1"></i> Simpan Kelas</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
