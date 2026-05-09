<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-heading">Menu Utama</div>
<a href="<?= base_url('admin/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>

<div class="sidebar-heading mt-3">Data Master</div>
<a href="<?= base_url('admin/tahun-ajaran') ?>"><i class="bi bi-calendar3 me-2"></i> Tahun Ajaran</a>
<a href="<?= base_url('admin/kelas') ?>"><i class="bi bi-door-open me-2"></i> Kelas</a>
<a href="<?= base_url('admin/mapel') ?>"><i class="bi bi-book me-2"></i> Mata Pelajaran</a>
<a href="<?= base_url('admin/kkm') ?>"><i class="bi bi-bar-chart-line me-2"></i> KKM</a>
<a href="<?= base_url('admin/siswa') ?>" class="active"><i class="bi bi-people me-2"></i> Data Siswa</a>
<a href="<?= base_url('admin/guru') ?>"><i class="bi bi-person-badge me-2"></i> Data Guru</a>

<div class="sidebar-heading mt-3">Alat & Laporan</div>
<a href="<?= base_url('admin/import') ?>"><i class="bi bi-upload me-2"></i> Import Data</a>
<a href="<?= base_url('admin/request-buka-nilai') ?>"><i class="bi bi-unlock me-2"></i> Permintaan Buka Nilai</a>
<a href="<?= base_url('admin/rapor') ?>"><i class="bi bi-file-earmark-text me-2"></i> Manajemen Rapor</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 text-pastel-primary fw-bold">Manajemen Data Siswa</h4>
        <p class="text-muted mb-0">Gunakan filter tahun ajaran dan kelas agar data siswa lebih rapi dan mudah dicek.</p>
    </div>
    <button type="button" class="btn btn-primary bg-pastel-primary border-0 shadow-sm" data-bs-toggle="modal"
        data-bs-target="#tambahSiswaModal">
        <i class="bi bi-plus-circle me-1"></i> Tambah Siswa Baru
    </button>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white fw-bold"><i class="bi bi-funnel me-2"></i>Filter Data Siswa</div>
    <div class="card-body">
        <form method="get" action="<?= base_url('admin/siswa') ?>" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Tahun Ajaran</label>
                <select name="id_tahun_ajaran" class="form-select">
                    <option value="">Gunakan Tahun Ajaran Aktif</option>
                    <?php foreach ($tahun_ajaran as $ta): ?>
                        <option value="<?= $ta['id_tahun_ajaran'] ?>" <?= (int) ($filter_ta ?? 0) === (int) $ta['id_tahun_ajaran'] ? 'selected' : '' ?>>
                            <?= esc($ta['tahun_ajaran']) ?> - Semester <?= esc($ta['semester']) ?>
                            <?= ($ta['aktif'] ?? '') === 'aktif' ? '(Aktif)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Jika belum memilih, sistem memakai tahun ajaran aktif bila tersedia.</div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Kelas</label>
                <select name="id_kelas" class="form-select">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($kelas as $k): ?>
                        <option value="<?= $k['id_kelas'] ?>" <?= (int) ($filter_kelas ?? 0) === (int) $k['id_kelas'] ? 'selected' : '' ?>>
                            <?= esc($k['nama_kelas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Pilih kelas, lalu tabel hanya menampilkan siswa pada kelas tersebut.</div>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search me-1"></i>
                    Tampilkan</button>
                <a href="<?= base_url('admin/siswa') ?>" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
        <div class="alert alert-light border mt-3 mb-0">
            <i class="bi bi-people me-1"></i>
            Jumlah siswa yang sedang tampil: <strong><?= esc($jumlah_siswa_tampil ?? count($siswa ?? [])) ?></strong>
            <?= !empty($filter_ta) ? ' pada tahun ajaran terpilih' : ' dari semua tahun ajaran' ?><?= !empty($filter_kelas) ? ' dan kelas terpilih.' : ' dan semua kelas.' ?>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="bg-light">
                    <tr>
                        <th>No</th>
                        <th>NIS/NISN</th>
                        <th>Nama Lengkap</th>
                        <th>Jenis Kelamin</th>
                        <th>Kelas</th>
                        <th>Tahun Ajaran</th>
                        <th>Orang Tua / Wali</th>
                        <th>No Telp Ortu</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($siswa)): ?>
                        <?php $no = 1;
                        foreach ($siswa as $s): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <strong><?= esc($s['nis']) ?></strong><br>
                                    <small class="text-muted"><?= esc($s['nisn'] ?? '—') ?></small>
                                </td>
                                <td><?= esc($s['nama_siswa']) ?></td>
                                <td><?= $s['jenis_kelamin'] === 'L' ? 'Laki-Laki' : 'Perempuan' ?></td>
                                <td><span class="badge bg-pastel-info"><?= esc($s['nama_kelas'] ?? 'Belum ada') ?></span></td>
                                <td>
                                    <?php if (!empty($s['id_tahun_ajaran'])): ?>
                                        <?= esc($s['tahun_ajaran'] ?? '-') ?><br>
                                        <small class="text-muted">Semester <?= esc($s['semester'] ?? '-') ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">Belum diatur</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= esc(($s['nama_ayah'] ?? '') ?: (($s['nama_ibu'] ?? '') ?: '—')) ?><br>
                                    <small class="text-muted fst-italic">Auto-Akun: ortu_<?= esc($s['nis']) ?></small>
                                </td>
                                <td><?= esc($s['no_telp_ortu'] ?? '—') ?></td>
                                <td>
                                    <?php if ($s['status'] === 'aktif'): ?>
                                        <span class="badge bg-pastel-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= ucfirst(esc($s['status'])) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#editSiswaModal<?= $s['id_siswa'] ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal"
                                        data-bs-target="#resetPasswordModal<?= $s['id_siswa'] ?>"
                                        title="Reset Password Login NIS">
                                        <i class="bi bi-key"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                        data-bs-target="#hapusSiswaModal<?= $s['id_siswa'] ?>">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal Edit Siswa -->
                            <div class="modal fade" id="editSiswaModal<?= $s['id_siswa'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-pastel-sidebar text-pastel-primary border-bottom-0">
                                            <h5 class="modal-title fw-bold">Edit Data Siswa</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <form action="<?= base_url('admin/siswa/update/' . $s['id_siswa']) ?>" method="post">
                                            <?= csrf_field() ?>
                                            <div class="modal-body p-4">
                                                <h6 class="text-muted text-uppercase fw-bold mb-3 border-bottom pb-2">Data
                                                    Akademik Siswa</h6>
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">NIS <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" name="nis" required
                                                            value="<?= esc($s['nis']) ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">NISN</label>
                                                        <input type="text" class="form-control" name="nisn"
                                                            value="<?= esc($s['nisn'] ?? '') ?>">
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Nama Lengkap Siswa <span
                                                                class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" name="nama_siswa" required
                                                            value="<?= esc($s['nama_siswa']) ?>">
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-md-4">
                                                        <label class="form-label">Jenis Kelamin</label>
                                                        <select class="form-select" name="jenis_kelamin" required>
                                                            <option value="L" <?= $s['jenis_kelamin'] === 'L' ? 'selected' : '' ?>>
                                                                Laki-Laki</option>
                                                            <option value="P" <?= $s['jenis_kelamin'] === 'P' ? 'selected' : '' ?>>
                                                                Perempuan</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Tempat Lahir</label>
                                                        <input type="text" class="form-control" name="tempat_lahir"
                                                            value="<?= esc($s['tempat_lahir'] ?? '') ?>">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">Tanggal Lahir</label>
                                                        <input type="date" class="form-control" name="tanggal_lahir"
                                                            value="<?= esc($s['tanggal_lahir'] ?? '') ?>">
                                                    </div>
                                                </div>
                                                <div class="row mb-4">
                                                     <div class="col-md-6">
                                                         <label class="form-label">Kelas <span
                                                                 class="text-danger">*</span></label>
                                                        <select class="form-select" name="id_kelas" required>
                                                            <option value="">-- Pilih Kelas --</option>
                                                            <?php foreach ($kelas as $k): ?>
                                                                <option value="<?= $k['id_kelas'] ?>"
                                                                    <?= $s['id_kelas'] == $k['id_kelas'] ? 'selected' : '' ?>>
                                                                    <?= esc($k['nama_kelas']) ?>
                                                                </option>
                                                             <?php endforeach; ?>
                                                         </select>
                                                     </div>
                                                     <div class="col-md-6">
                                                         <label class="form-label">Tahun Ajaran <span
                                                                 class="text-danger">*</span></label>
                                                         <select class="form-select" name="id_tahun_ajaran" required>
                                                             <option value="">-- Pilih Tahun Ajaran --</option>
                                                             <?php foreach ($tahun_ajaran as $ta): ?>
                                                                 <option value="<?= $ta['id_tahun_ajaran'] ?>"
                                                                     <?= (int) ($s['id_tahun_ajaran'] ?? 0) === (int) $ta['id_tahun_ajaran'] ? 'selected' : '' ?>>
                                                                     <?= esc($ta['tahun_ajaran']) ?> - Semester <?= esc($ta['semester']) ?>
                                                                     <?= ($ta['aktif'] ?? '') === 'aktif' ? '(Aktif)' : '' ?>
                                                                 </option>
                                                             <?php endforeach; ?>
                                                         </select>
                                                     </div>
                                                 </div>
                                                 <div class="row mb-4">
                                                     <div class="col-md-6">
                                                         <label class="form-label">Status</label>
                                                         <select class="form-select" name="status">
                                                            <option value="aktif" <?= $s['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                                            <option value="lulus" <?= $s['status'] === 'lulus' ? 'selected' : '' ?>>Lulus</option>
                                                            <option value="pindah" <?= $s['status'] === 'pindah' ? 'selected' : '' ?>>Pindah</option>
                                                            <option value="keluar" <?= $s['status'] === 'keluar' ? 'selected' : '' ?>>Keluar</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Alamat</label>
                                                    <textarea class="form-control" name="alamat"
                                                        rows="2"><?= esc($s['alamat'] ?? '') ?></textarea>
                                                </div>

                                                <h6 class="text-muted text-uppercase fw-bold mb-3 border-bottom pb-2">Data Orang
                                                    Tua / Wali</h6>
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Nama Ayah</label>
                                                        <input type="text" class="form-control" name="nama_ayah"
                                                            value="<?= esc($s['nama_ayah'] ?? '') ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Nama Ibu</label>
                                                        <input type="text" class="form-control" name="nama_ibu"
                                                            value="<?= esc($s['nama_ibu'] ?? '') ?>">
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">No. Telepon Aktif (Ortu)</label>
                                                        <input type="text" class="form-control" name="no_telp_ortu"
                                                            value="<?= esc($s['no_telp_ortu'] ?? '') ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light border-top-0">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Batal</button>
                                                <button type="submit"
                                                    class="btn btn-primary bg-pastel-primary border-0 shadow-sm">
                                                    <i class="bi bi-save me-1"></i> Update Siswa
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Reset Password Siswa -->
                            <div class="modal fade" id="resetPasswordModal<?= $s['id_siswa'] ?>" tabindex="-1"
                                aria-hidden="true">
                                <div class="modal-dialog modal-sm">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-warning text-dark border-bottom-0">
                                            <h5 class="modal-title fw-bold"><i class="bi bi-key me-1"></i> Reset Password Login
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <form action="<?= base_url('admin/siswa/reset-password/' . $s['id_siswa']) ?>"
                                            method="post">
                                            <?= csrf_field() ?>
                                            <div class="modal-body p-4">
                                                <p class="small">Reset password login NIS untuk
                                                    <strong><?= esc($s['nama_siswa']) ?></strong>.
                                                </p>
                                                <div class="mb-3">
                                                    <label class="form-label">Password Baru</label>
                                                    <input type="text" class="form-control" name="new_password"
                                                        placeholder="Kosongkan = reset ke NIS (<?= esc($s['nis']) ?>)">
                                                    <small class="text-muted">Biarkan kosong untuk mereset ke NIS sebagai
                                                        password default.</small>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top-0 justify-content-end">
                                                <button type="button" class="btn btn-secondary btn-sm"
                                                    data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-warning btn-sm fw-semibold"><i
                                                        class="bi bi-key me-1"></i> Reset Password</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Hapus Siswa -->
                            <div class="modal fade" id="hapusSiswaModal<?= $s['id_siswa'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-sm">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-danger text-white border-bottom-0">
                                            <h5 class="modal-title fw-bold">Konfirmasi Hapus</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-center p-4">
                                            <i class="bi bi-exclamation-triangle-fill text-danger fs-1 mb-3 d-block"></i>
                                            <p>Yakin ingin menghapus siswa <strong><?= esc($s['nama_siswa']) ?></strong>?</p>
                                            <small class="text-muted">Akun orang tua terkait juga akan dihapus jika tidak
                                                memiliki anak lain. Data yang sudah dihapus tidak dapat dikembalikan.</small>
                                        </div>
                                        <div class="modal-footer border-top-0 justify-content-center">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Batal</button>
                                            <form action="<?= base_url('admin/siswa/delete/' . $s['id_siswa']) ?>" method="post"
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
                            <td colspan="10" class="text-center py-4 text-muted">
                                <?= (!empty($filter_ta) || !empty($filter_kelas)) ? 'Belum ada siswa pada filter ini.' : 'Belum ada data siswa.' ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Siswa -->
<div class="modal fade" id="tambahSiswaModal" tabindex="-1" aria-labelledby="tambahSiswaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-pastel-sidebar text-pastel-primary border-bottom-0">
                <h5 class="modal-title fw-bold" id="tambahSiswaModalLabel">Tambah Data Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('admin/siswa/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="alert alert-info bg-pastel-info border-0 text-dark" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i> Sistem akan otomatis membuat akun Orang Tua
                        menggunakan NIS saat data siswa disimpan.
                    </div>

                    <h6 class="text-muted text-uppercase fw-bold mb-3 border-bottom pb-2">Data Akademik Siswa</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">NIS <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nis" required placeholder="Contoh: 2024001">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NISN</label>
                            <input type="text" class="form-control" name="nisn" placeholder="(Opsional)">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Nama Lengkap Siswa <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama_siswa" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Jenis Kelamin</label>
                            <select class="form-select" name="jenis_kelamin" required>
                                <option value="L">Laki-Laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tempat Lahir</label>
                            <input type="text" class="form-control" name="tempat_lahir">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" class="form-control" name="tanggal_lahir">
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Kelas <span class="text-danger">*</span></label>
                            <select class="form-select" name="id_kelas" required>
                                <option value="">-- Pilih Kelas --</option>
                                <?php foreach ($kelas as $k): ?>
                                    <option value="<?= $k['id_kelas'] ?>" <?= (int) ($filter_kelas ?? 0) === (int) $k['id_kelas'] ? 'selected' : '' ?>>
                                        <?= esc($k['nama_kelas']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                            <select class="form-select" name="id_tahun_ajaran" required>
                                <option value="">-- Pilih Tahun Ajaran --</option>
                                <?php foreach ($tahun_ajaran as $ta): ?>
                                    <option value="<?= $ta['id_tahun_ajaran'] ?>" <?= (int) ($filter_ta ?? 0) === (int) $ta['id_tahun_ajaran'] ? 'selected' : '' ?>>
                                        <?= esc($ta['tahun_ajaran']) ?> - Semester <?= esc($ta['semester']) ?>
                                        <?= ($ta['aktif'] ?? '') === 'aktif' ? '(Aktif)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Alamat</label>
                            <textarea class="form-control" name="alamat" rows="1"></textarea>
                        </div>
                    </div>

                    <h6 class="text-muted text-uppercase fw-bold mb-3 border-bottom pb-2">Data Orang Tua / Wali</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Ayah</label>
                            <input type="text" class="form-control" name="nama_ayah">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Ibu</label>
                            <input type="text" class="form-control" name="nama_ibu">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">No. Telepon Aktif (Ortu)</label>
                            <input type="text" class="form-control" name="no_telp_ortu" placeholder="08...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary bg-pastel-primary border-0 shadow-sm">
                        <i class="bi bi-save me-1"></i> Simpan Siswa dan Akun Orang Tua
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
