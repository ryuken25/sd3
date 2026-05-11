<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-heading">Menu Utama</div>
<a href="<?= base_url('admin/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>

<div class="sidebar-heading mt-3">Data Master</div>
<a href="<?= base_url('admin/tahun-ajaran') ?>"><i class="bi bi-calendar3 me-2"></i> Tahun Ajaran</a>
<a href="<?= base_url('admin/kelas') ?>"><i class="bi bi-door-open me-2"></i> Kelas</a>
<a href="<?= base_url('admin/mapel') ?>" class="active"><i class="bi bi-book me-2"></i> Mata Pelajaran</a>
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
        <h4 class="mb-0 text-pastel-primary fw-bold">Manajemen Mata Pelajaran</h4>
        <p class="text-muted mb-0">Pilih kelas untuk melihat mata pelajaran yang berjalan pada kelas tersebut.</p>
    </div>
    <button type="button" class="btn btn-primary bg-pastel-primary border-0 shadow-sm" data-bs-toggle="modal"
        data-bs-target="#tambahMapelModal">
        <i class="bi bi-plus-circle me-1"></i> Tambah Mata Pelajaran
    </button>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white fw-bold"><i class="bi bi-funnel me-2"></i>Filter Kelas</div>
    <div class="card-body">
        <form method="get" action="<?= base_url('admin/mapel') ?>" class="row g-3 align-items-end">
            <div class="col-md-8">
                <label class="form-label">Kelas</label>
                <select name="id_kelas" class="form-select">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($kelas as $k): ?>
                        <option value="<?= $k['id_kelas'] ?>" <?= (int) ($filter_kelas ?? 0) === (int) $k['id_kelas'] ? 'selected' : '' ?>>
                            <?= esc($k['nama_kelas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Gunakan filter ini untuk menjawab daftar mata pelajaran yang berjalan pada kelas
                    tertentu.</div>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search me-1"></i>
                    Tampilkan</button>
                <a href="<?= base_url('admin/mapel') ?>" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="bg-light">
                    <tr>
                        <th>No</th>
                        <th>Kode/Singkatan</th>
                        <th>Nama Mata Pelajaran</th>
                        <th>Kelompok</th>
                        <th>Berlaku untuk Kelas</th>
                        <th>Guru Pengampu</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($mapel)): ?>
                        <?php $no = 1;
                        foreach ($mapel as $m): ?>
                            <?php $selectedKelas = $assigned_by_mapel[(int) $m['id_mapel']] ?? []; ?>
                            <?php $assignments = $assignments_by_mapel[(int) $m['id_mapel']] ?? []; ?>
                            <?php $guruSelection = $guru_by_mapel_kelas[(int) $m['id_mapel']] ?? []; ?>
                            <tr>
                                <td>
                                    <?= $no++ ?>
                                </td>
                                <td><span class="badge bg-secondary">
                                        <?= esc($m['kode_mapel']) ?>
                                    </span></td>
                                <td><strong>
                                        <?= esc($m['nama_mapel']) ?>
                                    </strong></td>
                                <td>Kelompok
                                    <?= esc($m['kelompok']) ?>
                                    <?= $m['kelompok'] === 'B' ? '(Muatan Lokal)' : '(Nasional)' ?>
                                </td>
                                <td>
                                    <?php if (!empty($m['daftar_kelas'])): ?>
                                        <?= esc($m['daftar_kelas']) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Belum dihubungkan ke kelas</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($assignments)): ?>
                                        <ul class="list-unstyled mb-0 small">
                                            <?php foreach ($assignments as $assignment): ?>
                                                <li class="mb-1">
                                                    <span class="fw-semibold"><?= esc($assignment['nama_kelas'] ?? '-') ?>:</span>
                                                    <?php if (!empty($assignment['nama_guru'])): ?>
                                                        <?= esc($assignment['nama_guru']) ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Belum diatur</span>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <span class="text-muted">Belum diatur</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#editMapelModal<?= $m['id_mapel'] ?>"><i
                                            class="bi bi-pencil"></i></button>
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                        data-bs-target="#hapusMapelModal<?= $m['id_mapel'] ?>"><i
                                            class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <?= !empty($filter_kelas) ? 'Belum ada mata pelajaran pada kelas ini.' : 'Belum ada data Mata Pelajaran.' ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (!empty($mapel)): ?>
    <?php foreach ($mapel as $m): ?>
        <?php $selectedKelas = $assigned_by_mapel[(int) $m['id_mapel']] ?? []; ?>
        <?php $assignments = $assignments_by_mapel[(int) $m['id_mapel']] ?? []; ?>
        <?php $guruSelection = $guru_by_mapel_kelas[(int) $m['id_mapel']] ?? []; ?>

        <div class="modal fade" id="editMapelModal<?= $m['id_mapel'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-pastel-sidebar text-pastel-primary border-bottom-0">
                        <h5 class="modal-title fw-bold">Edit Mata Pelajaran</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <form action="<?= base_url('admin/mapel/update/' . $m['id_mapel']) ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label">Kode / Singkatan <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="kode_mapel" required
                                    value="<?= esc($m['kode_mapel']) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nama Mata Pelajaran <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="nama_mapel" required
                                    value="<?= esc($m['nama_mapel']) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kelompok Mapel <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" name="kelompok" required>
                                    <option value="A" <?= $m['kelompok'] == 'A' ? 'selected' : '' ?>>Kelompok A
                                        (Nasional)</option>
                                    <option value="B" <?= $m['kelompok'] == 'B' ? 'selected' : '' ?>>Kelompok B
                                        (Muatan Lokal)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kelas & Guru Pengampu <span
                                        class="text-danger">*</span></label>
                                <div class="table-responsive border rounded">
                                    <table class="table table-sm align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 42%">Kelas</th>
                                                <th>Guru Pengampu</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($kelas as $k): ?>
                                                <?php $idKelas = (int) $k['id_kelas']; ?>
                                                <tr>
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="id_kelas[]"
                                                                value="<?= $idKelas ?>" id="edit_mapel_<?= $m['id_mapel'] ?>_kelas_<?= $idKelas ?>"
                                                                <?= in_array($idKelas, $selectedKelas, true) ? 'checked' : '' ?>>
                                                            <label class="form-check-label" for="edit_mapel_<?= $m['id_mapel'] ?>_kelas_<?= $idKelas ?>">
                                                                <?= esc($k['nama_kelas']) ?>
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <select class="form-select form-select-sm" name="id_guru[<?= $idKelas ?>]">
                                                            <option value="">Belum diatur</option>
                                                            <?php foreach ($guru as $g): ?>
                                                                <option value="<?= $g['id_user'] ?>" <?= (int) ($guruSelection[$idKelas] ?? 0) === (int) $g['id_user'] ? 'selected' : '' ?>>
                                                                    <?= esc($g['nama_lengkap']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="form-text">Centang minimal satu kelas yang memakai mapel ini, lalu pilih guru pengampu. Guru boleh dikosongkan jika belum ditentukan.</div>
                                <input type="hidden" name="id_kelas[]" value="">
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-top-0">
                            <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">Batal</button>
                            <button type="submit"
                                class="btn btn-primary bg-pastel-primary border-0 shadow-sm"><i
                                    class="bi bi-save me-1"></i> Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="hapusMapelModal<?= $m['id_mapel'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-sm">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-danger text-white border-bottom-0">
                        <h5 class="modal-title fw-bold">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center p-4">
                        <i class="bi bi-exclamation-triangle-fill text-danger fs-1 mb-3 d-block"></i>
                        <p>Yakin ingin menghapus mata pelajaran
                            <strong>
                                <?= esc($m['nama_mapel']) ?>
                            </strong>?
                        </p>
                        <small class="text-muted">Data KKM, nilai, dan relasi kelas yang terkait juga dapat
                            terhapus.</small>
                    </div>
                    <div class="modal-footer border-top-0 justify-content-center">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Batal</button>
                        <form action="<?= base_url('admin/mapel/delete/' . $m['id_mapel']) ?>" method="post"
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
<?php endif; ?>

<div class="modal fade" id="tambahMapelModal" tabindex="-1" aria-labelledby="tambahMapelLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-pastel-sidebar text-pastel-primary border-bottom-0">
                <h5 class="modal-title fw-bold" id="tambahMapelLabel">Tambah Mata Pelajaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('admin/mapel/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i> Pilih kelas terlebih dahulu agar mata pelajaran
                        tidak tercampur antar kelas.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kelas & Guru Pengampu <span class="text-danger">*</span></label>
                        <div class="table-responsive border rounded">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 42%">Kelas</th>
                                        <th>Guru Pengampu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($kelas as $k): ?>
                                        <?php $idKelas = (int) $k['id_kelas']; ?>
                                        <tr>
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="id_kelas[]"
                                                        value="<?= $idKelas ?>" id="add_mapel_kelas_<?= $idKelas ?>"
                                                        <?= (int) ($filter_kelas ?? 0) === $idKelas ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="add_mapel_kelas_<?= $idKelas ?>">
                                                        <?= esc($k['nama_kelas']) ?>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <select class="form-select form-select-sm" name="id_guru[<?= $idKelas ?>]">
                                                    <option value="">Belum diatur</option>
                                                    <?php foreach ($guru as $g): ?>
                                                        <option value="<?= $g['id_user'] ?>"><?= esc($g['nama_lengkap']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="form-text">Centang minimal satu kelas yang memakai mapel ini, lalu pilih guru pengampu. Guru boleh dikosongkan jika belum ditentukan.</div>
                        <input type="hidden" name="id_kelas[]" value="">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kode / Singkatan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="kode_mapel" required
                            placeholder="Contoh: BIND, MTK">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Mata Pelajaran <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_mapel" required
                            placeholder="Contoh: Bahasa Indonesia">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kelompok Mapel <span class="text-danger">*</span></label>
                        <select class="form-select" name="kelompok" required>
                            <option value="A">Kelompok A (Nasional)</option>
                            <option value="B">Kelompok B (Muatan Lokal)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary bg-pastel-primary border-0 shadow-sm"><i
                            class="bi bi-save me-1"></i> Simpan Mata Pelajaran</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
