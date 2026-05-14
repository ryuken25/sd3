<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-heading">Menu Utama</div>
<a href="<?= base_url('admin/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
<div class="sidebar-heading mt-3">Master Data</div>
<a href="<?= base_url('admin/siswa') ?>"><i class="bi bi-people me-2"></i> Data Siswa</a>
<a href="<?= base_url('admin/import') ?>"><i class="bi bi-file-earmark-arrow-up me-2"></i> Import Massal Siswa</a>
<a href="<?= base_url('admin/import-nilai') ?>" class="active"><i class="bi bi-file-earmark-spreadsheet me-2"></i> Import Nilai</a>
<a href="<?= base_url('admin/guru') ?>"><i class="bi bi-person-badge me-2"></i> Data Guru</a>
<a href="<?= base_url('admin/kelas') ?>"><i class="bi bi-building me-2"></i> Kelas</a>
<a href="<?= base_url('admin/mapel') ?>"><i class="bi bi-book me-2"></i> Mata Pelajaran</a>
<div class="sidebar-heading mt-3">Akademik</div>
<a href="<?= base_url('admin/tahun-ajaran') ?>"><i class="bi bi-calendar3 me-2"></i> Tahun Ajaran</a>
<a href="<?= base_url('admin/kkm') ?>"><i class="bi bi-sliders me-2"></i> KKM</a>
<a href="<?= base_url('admin/rapor') ?>"><i class="bi bi-file-earmark-text me-2"></i> Manajemen Rapor</a>
<div class="sidebar-heading mt-3">Audit</div>
<a href="<?= base_url('admin/request-buka-nilai') ?>"><i class="bi bi-unlock me-2"></i> Permintaan Buka Nilai</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-pastel-primary mb-1"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Import Nilai Siswa</h4>
        <p class="text-muted mb-0">Unggah file Excel untuk mengisi atau memperbarui nilai siswa per kelas secara massal.</p>
    </div>
</div>

<?php if (!empty($periodError)): ?>
    <div class="alert alert-danger mb-4">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Periode Tidak Aktif:</strong> <?= esc($periodError) ?>
        <a href="<?= base_url('admin/tahun-ajaran') ?>" class="alert-link ms-1">Atur Tahun Ajaran</a>
    </div>
<?php else: ?>
    <div class="alert alert-info d-flex justify-content-between align-items-center mb-4">
        <div>
            <strong><i class="bi bi-calendar-check me-1"></i>Periode Aktif:</strong>
            <?= esc($period['label']) ?>
            <br><small class="text-muted">Nilai yang diimport akan masuk ke periode ini. Tidak bisa diubah manual.</small>
        </div>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger mb-3">
        <i class="bi bi-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success mb-3">
        <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<?php if ($r = session()->getFlashdata('import_nilai_report')): ?>
    <div class="alert alert-<?= empty($r['failed']) ? 'success' : 'warning' ?> mb-4">
        <h6 class="alert-heading fw-bold"><i class="bi bi-bar-chart-steps me-1"></i>Laporan Import</h6>
        <p class="mb-1">
            Tujuan: <strong><?= esc($r['period']) ?></strong><br>
            <span class="text-success">&#10003; Berhasil: <?= $r['success'] ?></span> &nbsp;|&nbsp;
            <span class="text-danger">&#10007; Gagal: <?= count($r['failed']) ?></span> &nbsp;|&nbsp;
            <span class="text-secondary">&#9654; Dilewati: <?= $r['skipped'] ?></span>
        </p>
        <?php if (!empty($r['failed'])): ?>
            <details class="mt-2">
                <summary class="fw-semibold text-danger" style="cursor:pointer">Detail error (<?= count($r['failed']) ?> baris)</summary>
                <ul class="mt-2 mb-0 small">
                    <?php foreach ($r['failed'] as $err): ?>
                        <li><?= esc($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </details>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Step 1: Download Template -->
    <div class="col-md-5">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold">
                <span class="badge bg-primary me-1">1</span> Download Template Excel
            </div>
            <div class="card-body">
                <p class="text-muted small">Pilih kelas, lalu download template yang sudah ter-prefill daftar siswa dan mata pelajaran. Kolom periode dikunci otomatis.</p>
                <?php if (empty($periodError)): ?>
                    <form method="get" action="" id="formTemplate">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Pilih Kelas</label>
                            <select name="kelas_id" class="form-select" id="selectKelasTemplate" required>
                                <option value="">-- Pilih Kelas --</option>
                                <?php foreach ($kelas as $k): ?>
                                    <option value="<?= $k['id_kelas'] ?>">
                                        Kelas <?= esc($k['tingkat']) ?> — <?= esc($k['nama_kelas']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <a href="#" id="btnDownloadTemplate" class="btn btn-success w-100 disabled">
                            <i class="bi bi-download me-1"></i> Download Template
                        </a>
                    </form>
                    <script>
                        document.getElementById('selectKelasTemplate').addEventListener('change', function () {
                            var btn = document.getElementById('btnDownloadTemplate');
                            if (this.value) {
                                btn.href = '<?= base_url('admin/import-nilai/template') ?>/' + this.value;
                                btn.classList.remove('disabled');
                            } else {
                                btn.href = '#';
                                btn.classList.add('disabled');
                            }
                        });
                    </script>
                <?php else: ?>
                    <div class="alert alert-warning small">Tidak tersedia — tidak ada periode aktif.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Step 2: Upload -->
    <div class="col-md-7">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white fw-bold">
                <span class="badge bg-primary me-1">2</span> Upload File Excel
            </div>
            <div class="card-body">
                <?php if (!empty($periodError)): ?>
                    <div class="alert alert-warning">Aktifkan tahun ajaran terlebih dahulu sebelum mengupload.</div>
                <?php else: ?>
                    <div class="alert alert-light border small mb-3">
                        <strong>Wajib pakai template yang baru di-download.</strong> Upload file sembarangan akan ditolak.
                        <ul class="mb-0 mt-1">
                            <li>Isi nilai di kolom <strong>E</strong> (Tugas), <strong>F</strong> (Ulangan), <strong>G</strong> (UTS), <strong>H</strong> (UAS) — rentang 0–100.</li>
                            <li>Baris dengan NIS dan Kode Mapel kosong akan dilewati.</li>
                            <li>Data yang sudah ada akan di-<em>update</em>, bukan duplikat.</li>
                        </ul>
                    </div>
                    <form method="post" enctype="multipart/form-data"
                          action="<?= base_url('admin/import-nilai/upload') ?>">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">File Excel (.xlsx / .xls)</label>
                            <input type="file" name="file" accept=".xlsx,.xls" class="form-control" required>
                            <div class="form-text text-muted">Maks 5 MB. Hanya template resmi yang diterima.</div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload me-1"></i> Proses Import
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Referensi kelas -->
<div class="card shadow-sm mt-4">
    <div class="card-header bg-white fw-bold"><i class="bi bi-table me-2"></i>Kelas Tersedia</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>ID Kelas</th>
                        <th>Nama Kelas</th>
                        <th>Tingkat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kelas as $k): ?>
                        <tr>
                            <td><strong><?= esc($k['id_kelas']) ?></strong></td>
                            <td><?= esc($k['nama_kelas']) ?></td>
                            <td>Kelas <?= esc($k['tingkat']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
