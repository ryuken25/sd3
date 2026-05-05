<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold" style="color: var(--pastel-text);">Data Personal</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= base_url() ?>" class="text-decoration-none" style="color: #d97706;">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Personal</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0 mb-4 p-4 text-center" style="border-radius: 16px; background: #fffcf2;">
            <i class="fa-solid fa-address-card fa-4x mb-3" style="color: var(--pastel-primary-dark);"></i>
            <h4 class="fw-bold" style="color: var(--pastel-text);">Profil Lengkap</h4>
            <p class="text-muted">Fitur ini sedang dalam pengembangan untuk pengisian form Biodata Mahasiswa.</p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
