<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-heading">Menu Utama</div>
<a href="<?= base_url('admin/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>

<div class="sidebar-heading mt-3">Data Master</div>
<a href="<?= base_url('admin/tahun-ajaran') ?>"><i class="bi bi-calendar3 me-2"></i> Tahun Ajaran</a>
<a href="<?= base_url('admin/kelas') ?>"><i class="bi bi-door-open me-2"></i> Kelas</a>
<a href="<?= base_url('admin/mapel') ?>"><i class="bi bi-book me-2"></i> Mata Pelajaran</a>
<a href="<?= base_url('admin/kkm') ?>" class="active"><i class="bi bi-bar-chart-line me-2"></i> KKM</a>
<a href="<?= base_url('admin/siswa') ?>"><i class="bi bi-people me-2"></i> Data Siswa</a>
<a href="<?= base_url('admin/guru') ?>"><i class="bi bi-person-badge me-2"></i> Data Guru</a>

<div class="sidebar-heading mt-3">Alat & Laporan</div>
<a href="<?= base_url('admin/import') ?>"><i class="bi bi-upload me-2"></i> Import Data</a>
<a href="<?= base_url('admin/request-buka-nilai') ?>"><i class="bi bi-unlock me-2"></i> Permintaan Buka Nilai</a>
<a href="<?= base_url('admin/rapor') ?>"><i class="bi bi-file-earmark-text me-2"></i> Manajemen Rapor</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-pastel-primary fw-bold">Konfigurasi Kriteria Ketuntasan Minimal (KKM)</h4>
    <button type="button" class="btn btn-primary bg-pastel-primary border-0 shadow-sm" data-bs-toggle="modal"
        data-bs-target="#tambahKkmModal">
        <i class="bi bi-plus-circle me-1"></i> Atur KKM Baru
    </button>
</div>

<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white fw-bold"><i class="bi bi-funnel me-2"></i>Filter</div>
    <div class="card-body">
        <form method="get" action="<?= base_url('admin/kkm') ?>" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Tahun Ajaran</label>
                <select name="id_tahun_ajaran" class="form-select">
                    <?php foreach ($ta as $t_item): ?>
                        <?php $isAktif = ($t_item['aktif'] ?? '') === 'aktif'; ?>
                        <option value="<?= $t_item['id_tahun_ajaran'] ?>" <?= (int) ($filter_ta ?? 0) === (int) $t_item['id_tahun_ajaran'] ? 'selected' : '' ?>>
                            <?= esc($t_item['tahun_ajaran']) ?> — Semester <?= esc($t_item['semester']) ?><?= $isAktif ? ' [AKTIF]' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Kelas</label>
                <select name="id_kelas" class="form-select">
                    <option value="">Semua Kelas</option>
                    <?php foreach ($kelas as $k_item): ?>
                        <option value="<?= $k_item['id_kelas'] ?>" <?= (int) ($filter_kelas ?? 0) === (int) $k_item['id_kelas'] ? 'selected' : '' ?>>
                            <?= esc($k_item['nama_kelas']) ?> (Tingkat <?= esc($k_item['tingkat']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-search me-1"></i>Tampilkan</button>
                <a href="<?= base_url('admin/kkm') ?>" class="btn btn-outline-secondary">Reset</a>
            </div>
            <div class="col-12">
                <div class="form-text mt-0">Default tampil TA yang sedang <strong>aktif</strong>. Pilih TA lain untuk melihat KKM lama.</div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle border">
                <thead class="bg-light">
                    <tr>
                        <th>No</th>
                        <th>Tahun Ajaran/Semester</th>
                        <th>Kelas</th>
                        <th>Mata Pelajaran</th>
                        <th class="text-center">Standar KKM</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($kkm)): ?>
                        <?php $no = 1;
                        foreach ($kkm as $k): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><span class="badge bg-secondary"><?= esc($k['tahun_ajaran']) ?> (SMT
                                        <?= esc($k['semester']) ?>)</span></td>
                                <td><strong><?= esc($k['nama_kelas']) ?></strong></td>
                                <td><?= esc($k['nama_mapel']) ?></td>
                                <td class="text-center">
                                    <span
                                        class="badge bg-pastel-primary fs-6 p-2 w-100"><?= number_format($k['nilai_kkm'], 2) ?></span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#editKkmModal<?= $k['id_kkm'] ?>"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                        data-bs-target="#hapusKkmModal<?= $k['id_kkm'] ?>"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>

                            <!-- Modal Edit KKM -->
                            <div class="modal fade" id="editKkmModal<?= $k['id_kkm'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-pastel-sidebar text-pastel-primary border-bottom-0">
                                            <h5 class="modal-title fw-bold">Edit KKM</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <form action="<?= base_url('admin/kkm/update/' . $k['id_kkm']) ?>" method="post">
                                            <?= csrf_field() ?>
                                            <div class="modal-body p-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Tahun Ajaran <span
                                                            class="text-danger">*</span></label>
                                                    <select class="form-select" name="id_tahun_ajaran" required>
                                                        <?php foreach ($ta as $t): ?>
                                                            <option value="<?= $t['id_tahun_ajaran'] ?>"
                                                                <?= $k['id_tahun_ajaran'] == $t['id_tahun_ajaran'] ? 'selected' : '' ?>><?= esc($t['tahun_ajaran']) ?> - Semester
                                                                <?= esc($t['semester']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Kelas <span class="text-danger">*</span></label>
                                                    <select class="form-select" name="id_kelas" required>
                                                        <?php foreach ($kelas as $k_item): ?>
                                                            <option value="<?= $k_item['id_kelas'] ?>"
                                                                <?= $k['id_kelas'] == $k_item['id_kelas'] ? 'selected' : '' ?>>
                                                                <?= esc($k_item['nama_kelas']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Mata Pelajaran <span
                                                            class="text-danger">*</span></label>
                                                    <select class="form-select" name="id_mapel" required>
                                                        <?php foreach ($mapel as $m): ?>
                                                            <?php $mapelKelasIds = array_filter(array_map('intval', explode(',', (string) ($m['kelas_ids'] ?? '')))); ?>
                                                            <option value="<?= $m['id_mapel'] ?>" data-kelas="<?= esc(implode(',', $mapelKelasIds)) ?>" <?= $k['id_mapel'] == $m['id_mapel'] ? 'selected' : '' ?>><?= esc($m['kode_mapel']) ?> -
                                                                <?= esc($m['nama_mapel']) ?> (<?= esc($m['daftar_kelas'] ?? '-') ?>)
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="form-label">Standar Ketuntasan / KKM <span
                                                            class="text-danger">*</span></label>
                                                    <input type="number" step="0.01"
                                                        class="form-control form-control-lg fw-bold text-center text-primary"
                                                        name="nilai_kkm" required
                                                        value="<?= number_format($k['nilai_kkm'], 2) ?>" min="0" max="100">
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light border-top-0">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Batal</button>
                                                <button type="submit"
                                                    class="btn btn-primary bg-pastel-primary border-0 shadow-sm"><i
                                                        class="bi bi-save me-1"></i> Update KKM</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Hapus KKM -->
                            <div class="modal fade" id="hapusKkmModal<?= $k['id_kkm'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-sm">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-danger text-white border-bottom-0">
                                            <h5 class="modal-title fw-bold">Konfirmasi Hapus</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-center p-4">
                                            <i class="bi bi-exclamation-triangle-fill text-danger fs-1 mb-3 d-block"></i>
                                            <p>Yakin ingin menghapus KKM untuk <strong><?= esc($k['nama_mapel']) ?></strong>
                                                kelas <strong><?= esc($k['nama_kelas']) ?></strong>?</p>
                                            <small class="text-muted">Data yang sudah dihapus tidak dapat dikembalikan.</small>
                                        </div>
                                        <div class="modal-footer border-top-0 justify-content-center">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Batal</button>
                                            <form action="<?= base_url('admin/kkm/delete/' . $k['id_kkm']) ?>" method="post"
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
                            <td colspan="6" class="text-center py-4 text-muted">Belum ada pengaturan KKM.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Atur KKM -->
<div class="modal fade" id="tambahKkmModal" tabindex="-1" aria-labelledby="tambahKkmLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-pastel-sidebar text-pastel-primary border-bottom-0">
                <h5 class="modal-title fw-bold" id="tambahKkmLabel">Atur Standar KKM</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('admin/kkm/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="alert alert-info bg-pastel-info border-0 text-dark" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i> Jika kombinasi Kelas, Mapel, dan Tahun Ajaran sudah
                        pernah diatur, sistem akan memperbarui nilai KKM sebelumnya.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                        <select class="form-select" name="id_tahun_ajaran" required>
                            <?php foreach ($ta as $t): ?>
                                <option value="<?= $t['id_tahun_ajaran'] ?>"><?= esc($t['tahun_ajaran']) ?> - Semester
                                    <?= esc($t['semester']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kelas <span class="text-danger">*</span></label>
                        <select class="form-select" name="id_kelas" required>
                            <?php foreach ($kelas as $k_item): ?>
                                <option value="<?= $k_item['id_kelas'] ?>"><?= esc($k_item['nama_kelas']) ?> (Tingkat
                                    <?= esc($k_item['tingkat']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mata Pelajaran <span class="text-danger">*</span></label>
                        <select class="form-select" name="id_mapel" required>
                            <?php foreach ($mapel as $m): ?>
                                <?php $mapelKelasIds = array_filter(array_map('intval', explode(',', (string) ($m['kelas_ids'] ?? '')))); ?>
                                <option value="<?= $m['id_mapel'] ?>" data-kelas="<?= esc(implode(',', $mapelKelasIds)) ?>"><?= esc($m['kode_mapel']) ?> -
                                    <?= esc($m['nama_mapel']) ?> (<?= esc($m['daftar_kelas'] ?? '-') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Standar Ketuntasan / KKM <span class="text-danger">*</span></label>
                        <input type="number" step="0.01"
                            class="form-control form-control-lg fw-bold text-center text-primary" name="nilai_kkm"
                            required value="75.00" min="0" max="100">
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary bg-pastel-primary border-0 shadow-sm"><i
                            class="bi bi-save me-1"></i> Simpan KKM</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.querySelectorAll('form').forEach(function (form) {
    const kelasSelect = form.querySelector('select[name="id_kelas"]');
    const mapelSelect = form.querySelector('select[name="id_mapel"]');
    if (!kelasSelect || !mapelSelect) return;

    const syncMapel = function () {
        const selectedKelas = kelasSelect.value;
        Array.from(mapelSelect.options).forEach(function (option) {
            if (!option.value) return;
            const kelasList = (option.dataset.kelas || '').split(',').filter(Boolean);
            option.hidden = selectedKelas && !kelasList.includes(selectedKelas);
        });
        if (mapelSelect.selectedOptions[0] && mapelSelect.selectedOptions[0].hidden) {
            mapelSelect.value = '';
        }
    };

    kelasSelect.addEventListener('change', syncMapel);
    syncMapel();
});
</script>
<?= $this->endSection() ?>
