<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold" style="color: var(--pastel-text);">Dashboard</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= base_url() ?>" class="text-decoration-none" style="color: #d97706;">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
        </ol>
    </nav>
</div>

<!-- Informasi dan Panduan -->
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-info-pastel shadow-sm mb-3" role="alert">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-circle-info fa-2x me-3 opacity-75"></i>
                <div>
                    <strong>Panduan Penggunaan!</strong> Berikut adalah panduan dari penggunaan Website Sekolah. Untuk panduannya bisa <a href="#" class="alert-link">Download Disini</a>.
                </div>
            </div>
        </div>
        
        <div class="alert alert-success-pastel shadow-sm mb-3" role="alert">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-book-open fa-2x me-3 opacity-75"></i>
                <div>
                    <strong>Panduan Elearning!</strong> Berikut adalah panduan dari penggunaan Website Elearning. Untuk panduannya bisa <a href="#" class="alert-link">Download Disini</a>.
                </div>
            </div>
        </div>

        <div class="alert alert-warning-pastel shadow-sm mb-3" role="alert">
            <div class="d-flex align-items-center">
                <i class="fa-regular fa-comments fa-2x me-3 opacity-75"></i>
                <div>
                    <strong>Tanya Jawab (FAQ)!</strong> Berikut adalah pertanyaan yang biasa ditanya ketika menggunakan Microsoft Teams. Untuk FAQ bisa <a href="#" class="alert-link">Download Disini</a> atau melihat di <a href="#" class="alert-link">Presentasi Berikut</a>.
                </div>
            </div>
        </div>
        
        <div class="card shadow-sm border-0 mb-4" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); color: #78350f;">
            <div class="card-body py-4 d-flex align-items-center">
                <i class="fa-solid fa-quote-left fa-2x opacity-50 me-3"></i>
                <div class="fst-italic" style="font-size: 1.1rem;">
                    "Setiap hari adalah kesempatan baru untuk belajar dan tumbuh. Gunakan waktumu sebaik mungkin karena masa depan adalah milik mereka yang menyiapkannya hari ini." <br>
                    <small class="fw-bold mt-2 d-block text-uppercase" style="letter-spacing: 1px;">- Pendidikan Karakter</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- User Profile Section -->
<div class="row mb-5">
    <div class="col-md-4 mb-4 mb-md-0 position-relative">
        <!-- Avatar card mimicking image -->
        <div class="card h-100 border-0 shadow text-center" style="border-radius: 16px; background-color: #ffffff;">
            <div class="position-relative" style="height: 120px; background: linear-gradient(135deg, var(--pastel-primary) 0%, var(--pastel-accent) 100%); border-radius: 16px 16px 0 0;">
                <!-- Background Pattern -->
            </div>
            <!-- Avatar positioned above the edge -->
            <div style="margin-top: -50px; position: relative; z-index: 10;">
                <img src="https://ui-avatars.com/api/?name=Siswa&background=bae6fd&color=0369a1&size=120&bold=true" alt="Avatar" class="rounded-circle shadow" style="width: 100px; height: 100px; object-fit: cover; border: 4px solid #fff;">
            </div>
            <div class="card-body pt-3 pb-4">
                <h4 class="card-title fw-bold mb-1" style="color: var(--pastel-text);">Siswa Aktif SDN 3 Mekarsari</h4>
                <p class="text-muted mb-3"><small>Menjadi luar biasa adalah pilihan</small></p>
                <button class="btn btn-pastel w-100 rounded-pill shadow-sm">Lihat Profil Siswa</button>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <!-- Info Card -->
        <div class="card h-100 border-0 shadow" style="border-radius: 16px; background: linear-gradient(135deg, #ffffff 0%, #fdfbf7 100%);">
            <div class="card-header bg-transparent border-0 pt-4 pb-0 px-4">
                <h5 class="fw-bold mb-0" style="color: var(--pastel-text); border-bottom: 2px solid var(--pastel-primary); display: inline-block; padding-bottom: 5px;">Data Akademik Anda</h5>
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-center" style="color: #4a5568;">
                <div class="row py-2 border-bottom border-light">
                    <div class="col-5 col-md-4 fw-bold text-muted"><i class="fa-solid fa-school me-2" style="color: #f59e0b;"></i>Sekolah</div>
                    <div class="col-7 col-md-8 fw-semibold">SDN 3 Mekarsari</div>
                </div>
                <div class="row py-2 border-bottom border-light">
                    <div class="col-5 col-md-4 fw-bold text-muted"><i class="fa-solid fa-id-card me-2" style="color: #f59e0b;"></i>NIS / NISN</div>
                    <div class="col-7 col-md-8 fw-semibold">2024001 / 1234567890</div>
                </div>
                <div class="row py-2 border-bottom border-light">
                    <div class="col-5 col-md-4 fw-bold text-muted"><i class="fa-solid fa-chalkboard-user me-2" style="color: #f59e0b;"></i>Kelas</div>
                    <div class="col-7 col-md-8 fw-semibold">Kelas 5A</div>
                </div>
                <div class="row py-2 border-bottom border-light">
                    <div class="col-5 col-md-4 fw-bold text-muted"><i class="fa-solid fa-calendar-days me-2" style="color: #f59e0b;"></i>Tahun Ajaran</div>
                    <div class="col-7 col-md-8 fw-semibold">2024/2025</div>
                </div>
                <div class="row py-2 border-bottom border-light">
                    <div class="col-5 col-md-4 fw-bold text-muted"><i class="fa-solid fa-user-tie me-2" style="color: #f59e0b;"></i>Wali Kelas</div>
                    <div class="col-7 col-md-8 fw-semibold"><span class="badge bg-success rounded-pill px-3 py-2">Ni Wayan Kasrinayanti, S.Pd.</span></div>
                </div>
                <div class="row py-2 border-bottom border-light">
                    <div class="col-5 col-md-4 fw-bold text-muted"><i class="fa-solid fa-check-circle me-2" style="color: #f59e0b;"></i>Status Siswa</div>
                    <div class="col-7 col-md-8 fw-semibold">Aktif</div>
                </div>
                <div class="row py-2">
                    <div class="col-5 col-md-4 fw-bold text-muted"><i class="fa-solid fa-stairs me-2" style="color: #f59e0b;"></i>Semester</div>
                    <div class="col-7 col-md-8 fw-bold" style="color: #d97706; font-size: 1.1rem;">Ganjil</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
