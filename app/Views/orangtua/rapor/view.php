<?= $this->extend('layout/master') ?>

<?= $this->section('sidebar') ?>
<div class="sidebar-heading">Menu Saya</div>
<a href="<?= base_url('orangtua/dashboard') ?>"><i class="bi bi-house me-2"></i> Dashboard</a>
<div class="sidebar-heading mt-3">Akademik</div>
<a href="<?= base_url('orangtua/grades/' . $siswa['id_siswa']) ?>"><i class="bi bi-bar-chart me-2"></i> Nilai &
    Rekap</a>
<a href="#" class="active"><i class="bi bi-file-earmark-text me-2"></i> E-Rapor</a>
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
<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <div>
        <h4 class="fw-bold text-pastel-primary mb-1"><i class="bi bi-file-earmark-text me-2"></i>E-Rapor Digital</h4>
        <p class="text-muted mb-0"><?= esc($siswa['nama_siswa']) ?> | <?= esc($tahun_ajaran['tahun_ajaran']) ?>
            Semester <?= esc($tahun_ajaran['semester']) ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('orangtua/grades/' . $siswa['id_siswa']) ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali</a>
        <a href="<?= base_url('orangtua/rapor/download/' . $siswa['id_siswa'] . '/' . $tahun_ajaran['id_tahun_ajaran']) ?>"
            class="btn btn-danger btn-sm fw-semibold">
            <i class="bi bi-file-earmark-pdf me-1"></i> Cetak Rapor</a>
        <button onclick="window.print()" class="btn btn-primary bg-pastel-primary border-0 btn-sm fw-semibold">
            <i class="bi bi-printer me-1"></i> Cetak Halaman</button>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4 p-md-5">
        <?= view('rapor/_full_layout', array_merge(get_defined_vars(), [
            'showBadgeOnline'  => true,
            'showAdminActions' => false,
        ])) ?>
    </div>
</div>
<?= $this->endSection() ?>
