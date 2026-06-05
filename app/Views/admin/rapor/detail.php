<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-heading">Menu Utama</div>
<a href="<?= base_url('admin/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
<div class="sidebar-heading mt-3">Master Data</div>
<a href="<?= base_url('admin/siswa') ?>"><i class="bi bi-people me-2"></i> Data Siswa</a>
<a href="<?= base_url('admin/import') ?>"><i class="bi bi-file-earmark-arrow-up me-2"></i> Import Massal</a>
<a href="<?= base_url('admin/guru') ?>"><i class="bi bi-person-badge me-2"></i> Data Guru</a>
<a href="<?= base_url('admin/kelas') ?>"><i class="bi bi-building me-2"></i> Kelas</a>
<a href="<?= base_url('admin/mapel') ?>"><i class="bi bi-book me-2"></i> Mata Pelajaran</a>
<div class="sidebar-heading mt-3">Akademik</div>
<a href="<?= base_url('admin/tahun-ajaran') ?>"><i class="bi bi-calendar3 me-2"></i> Tahun Ajaran</a>
<a href="<?= base_url('admin/kkm') ?>"><i class="bi bi-sliders me-2"></i> KKM</a>
<a href="<?= base_url('admin/rapor') ?>" class="active"><i class="bi bi-file-earmark-text me-2"></i> Manajemen Rapor</a>
<div class="sidebar-heading mt-3">Bantuan</div>
<a href="<?= base_url('help/panduan-rapor') ?>"><i class="bi bi-question-circle me-2"></i> Panduan Penggunaan</a>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/rapor-screen.css') ?>">
<style>
    @media print {

        .sidebar,
        nav.navbar,
        .no-print {
            display: none !important;
        }

        main {
            margin: 0 !important;
            padding: 0 !important;
        }

        .card {
            box-shadow: none !important;
            border: none !important;
        }

        body {
            background: #fff !important;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $isFinalized = (int) ($rapor['is_finalized'] ?? 0) === 1;
    $idTa        = (int) ($tahun_ajaran['id_tahun_ajaran'] ?? 0);
    $idSiswa     = (int) ($siswa['id_siswa'] ?? 0);
?>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success border-0 shadow-sm no-print">
        <i class="bi bi-check-circle me-2"></i><?= esc(session()->getFlashdata('success')) ?>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger border-0 shadow-sm no-print">
        <i class="bi bi-exclamation-triangle me-2"></i><?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <div>
        <h4 class="fw-bold text-pastel-primary mb-1"><i class="bi bi-file-earmark-text me-2"></i>Detail Rapor Siswa</h4>
        <p class="text-muted mb-0"><?= esc($siswa['nama_siswa']) ?> | <?= esc($tahun_ajaran['tahun_ajaran']) ?>
            Semester <?= esc($tahun_ajaran['semester']) ?>
            <?php if ($isFinalized): ?>
                <span class="badge bg-success ms-1">Final</span>
            <?php else: ?>
                <span class="badge bg-secondary ms-1">Draft</span>
            <?php endif; ?>
        </p>
    </div>
    <a href="<?= base_url('admin/rapor?id_tahun_ajaran=' . $idTa . '&id_kelas=' . (int) ($siswa['id_kelas'] ?? 0)) ?>"
        class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
</div>

<!-- Bar aksi admin (showAdminActions) -->
<div class="card border-0 shadow-sm mb-3 no-print">
    <div class="card-body d-flex flex-wrap gap-2 align-items-center">
        <span class="fw-semibold me-2"><i class="bi bi-gear me-1"></i>Aksi Admin:</span>

        <?php if (!$isFinalized): ?>
            <form action="<?= base_url('admin/rapor/finalize/' . $idSiswa . '/' . $idTa) ?>" method="post" class="d-inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-success btn-sm"
                    onclick="return confirm('Finalisasi rapor siswa ini? Setelah final, orang tua dapat melihat e-rapor.')">
                    <i class="bi bi-check2-circle me-1"></i> Finalisasi Rapor
                </button>
            </form>
        <?php elseif (!empty($rapor['id_rapor'])): ?>
            <form action="<?= base_url('admin/rapor/unfinalize/' . (int) $rapor['id_rapor']) ?>" method="post" class="d-inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-warning btn-sm"
                    onclick="return confirm('Batalkan finalisasi? Orang tua tidak dapat melihat rapor final sampai difinalisasi ulang.')">
                    <i class="bi bi-unlock me-1"></i> Batalkan Final
                </button>
            </form>
        <?php endif; ?>

        <a href="<?= base_url('admin/rapor?id_tahun_ajaran=' . $idTa . '&id_kelas=' . (int) ($siswa['id_kelas'] ?? 0)) ?>"
            class="btn btn-outline-primary btn-sm">
            <i class="bi bi-pencil-square me-1"></i> Edit Absensi / Catatan
        </a>

        <button onclick="window.print()" class="btn btn-outline-dark btn-sm">
            <i class="bi bi-printer me-1"></i> Cetak Halaman
        </button>
    </div>
</div>

<div class="card border-0">
    <div class="card-body p-4 p-md-5">
        <?= view('rapor/_full_layout', array_merge(get_defined_vars(), [
            'showBadgeOnline'  => true,
            'showAdminActions' => true,
        ])) ?>
    </div>
</div>
<?= $this->endSection() ?>
