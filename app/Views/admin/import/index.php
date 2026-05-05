<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-heading">Menu Utama</div>
<a href="<?= base_url('admin/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
<div class="sidebar-heading mt-3">Master Data</div>
<a href="<?= base_url('admin/siswa') ?>"><i class="bi bi-people me-2"></i> Data Siswa</a>
<a href="<?= base_url('admin/import') ?>" class="active"><i class="bi bi-file-earmark-arrow-up me-2"></i> Import
    Massal</a>
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
        <h4 class="fw-bold text-pastel-primary mb-1"><i class="bi bi-file-earmark-arrow-up me-2"></i>Import Data Siswa
            (Massal)</h4>
        <p class="text-muted mb-0">Unggah file Excel untuk menambah data siswa sekaligus. Data hasil import langsung
            masuk ke menu Data Siswa.</p>
    </div>
    <a href="<?= base_url('admin/import/template') ?>" class="btn btn-success btn-sm">
        <i class="bi bi-download me-1"></i> Download Template Excel
    </a>
</div>

<?php if (session()->getFlashdata('import_result')): ?>
    <?php $result = session()->getFlashdata('import_result'); ?>
    <?php $skippedDuplicate = (int) ($result['skipped_duplicate'] ?? 0); ?>
    <div class="alert alert-<?= ($result['failed'] > 0 || $skippedDuplicate > 0) ? 'warning' : 'success' ?> mb-4">
        <h5 class="alert-heading"><i class="bi bi-info-circle me-2"></i>Hasil Import</h5>
        <p class="mb-1">Total Baris: <strong><?= $result['total'] ?></strong> |
            Berhasil: <strong class="text-success"><?= $result['success'] ?></strong> |
            Gagal: <strong class="text-danger"><?= $result['failed'] ?></strong> |
            Dilewati karena NIS duplikat: <strong class="text-warning"><?= $skippedDuplicate ?></strong>
        </p>
        <?php if ((int) $result['success'] > 0): ?>
            <p class="mb-0 small">Data yang berhasil diimpor sudah tersimpan pada tabel siswa dan dapat langsung dilihat di menu
                <a href="<?= base_url('admin/siswa') ?>" class="alert-link">Data Siswa</a>.
            </p>
        <?php endif; ?>
        <?php if (!empty($result['logs'])): ?>
            <hr>
            <div style="max-height: 200px; overflow-y: auto; font-size: 0.85rem;">
                <?php foreach ($result['logs'] as $log): ?>
                    <?php
                    $isSuccess = str_contains($log, 'Berhasil');
                    $isSkipped = str_contains($log, 'Dilewati');
                    ?>
                    <div class="<?= $isSuccess ? 'text-success' : ($isSkipped ? 'text-warning' : 'text-danger') ?>">
                        <i
                            class="bi bi-<?= $isSuccess ? 'check-circle' : ($isSkipped ? 'skip-forward-circle' : 'x-circle') ?> me-1"></i>
                        <?= esc($log) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger mb-3"><i
            class="bi bi-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white fw-bold"><i class="bi bi-upload me-2"></i>Upload File Excel</div>
    <div class="card-body">
        <div class="alert alert-info">
            <strong><i class="bi bi-lightbulb me-1"></i>Petunjuk:</strong>
            <p class="mb-2 mt-1"><strong>Data yang berhasil diimport akan langsung tersimpan dan tampil di menu Data Siswa.</strong></p>
            <ol class="mb-0 mt-1">
                <li>Unduh template Excel terlebih dahulu menggunakan tombol di kanan atas.</li>
                <li>Isi data siswa sesuai kolom yang tersedia. <strong>Jangan ubah baris header (baris
                        pertama).</strong></li>
                <li>Kolom wajib: <strong>NIS, Nama Siswa, ID Kelas</strong>. Kolom lainnya opsional.</li>
                <li>Jika NIS sudah ada di database, baris tersebut akan <strong>dilewati (Duplikat)</strong>. Baris
                    lainnya tetap diproses.</li>
                <li>Akun orang tua akan dibuat otomatis dengan username <code>ortu_NIS</code> dan password awal sesuai
                    NIS.</li>
                <li>Setelah import berhasil, siswa langsung tampil pada menu Data Siswa sesuai kelasnya.</li>
            </ol>
        </div>
        <form action="<?= base_url('admin/import/process') ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="excel_file" class="form-label fw-semibold">Pilih File Excel (.xlsx, .xls)</label>
                <input type="file" class="form-control" id="excel_file" name="excel_file" accept=".xlsx,.xls,.csv"
                    required>
                <div class="form-text text-muted">Maks ukuran file: 5MB. Format yang diterima: .xlsx, .xls, .csv</div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-upload me-1"></i> Proses Import
            </button>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white fw-bold"><i class="bi bi-table me-2"></i>Kelas yang Tersedia (Referensi ID Kelas)
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
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
