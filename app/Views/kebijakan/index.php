<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold" style="color: var(--pastel-text);">Kebijakan dan Privasi</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= base_url() ?>" class="text-decoration-none" style="color: #d97706;">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Kebijakan</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0 mb-4 p-4" style="border-radius: 16px; background: #fffcf2;">
            <i class="fa-solid fa-shield-halved fa-4x mb-3 text-center" style="color: var(--pastel-primary-dark);"></i>
            <h4 class="fw-bold text-center mb-4" style="color: var(--pastel-text);">Kebijakan Penggunaan Layanan</h4>
            <div class="mx-auto text-muted" style="max-width: 800px; line-height: 1.8;">
                <p>Seluruh civitas akademika diwajibkan untuk mematuhi peraturan dan tata tertib yang berlaku selama menggunakan fasilitas digital kampus ini.</p>
                <ul>
                    <li>Tidak membagikan kredensial (username/password) kepada pihak lain.</li>
                    <li>Menjaga kesopanan dalam setiap interaksi digital yang mencakup website forum.</li>
                    <li>Menggunakan fasilitas ini semata-mata untuk kepentingan pendidikan.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
