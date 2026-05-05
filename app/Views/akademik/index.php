<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold" style="color: var(--pastel-text);">Data Akademik</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= base_url() ?>" class="text-decoration-none" style="color: #d97706;">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Akademik</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0 mb-4 p-4" style="border-radius: 16px;">
            <div class="d-flex justify-content-between align-items-center border-bottom border-light pb-3 mb-4">
                <h4 class="mb-0 fw-bold" style="color: var(--pastel-text);">Daftar Mata Pelajaran & Nilai</h4>
                <button class="btn btn-pastel rounded-pill px-4 fw-bold shadow-sm"><i class="fa-solid fa-print me-2"></i>Cetak Rapor</button>
            </div>
            
            <div class="row">
                <!-- Course Cards -->
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm" style="background-color: var(--pastel-blue); border-radius: 12px; transition: transform 0.2s;">
                        <div class="card-body">
                            <span class="badge bg-white text-primary mb-2 shadow-sm">KKM: 70</span>
                            <h5 class="card-title fw-bold text-dark">Pendidikan Agama & Budi Pekerti</h5>
                            <p class="card-text text-muted mb-3"><i class="fa-solid fa-user-tie me-2"></i>Ni Komang Ayu, S.Ag.</p>
                            <div class="progress mb-3" style="height: 10px;">
                                <div class="progress-bar" role="progressbar" style="width: 85%; background-color: var(--pastel-primary-dark);" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="d-flex justify-content-between text-muted small fw-bold">
                                <span>Nilai Akhir: 85</span>
                                <span>Tuntas</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm" style="background-color: var(--pastel-secondary); border-radius: 12px;">
                        <div class="card-body">
                            <span class="badge bg-white text-warning mb-2 shadow-sm">KKM: 70</span>
                            <h5 class="card-title fw-bold text-dark">Bahasa Indonesia</h5>
                            <p class="card-text text-muted mb-3"><i class="fa-solid fa-user-tie me-2"></i>Ni Wayan Kasrinayanti, S.Pd.</p>
                            <div class="progress mb-3" style="height: 10px;">
                                <div class="progress-bar" role="progressbar" style="width: 90%; background-color: #4ade80;" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="d-flex justify-content-between text-muted small fw-bold">
                                <span>Nilai Akhir: 90</span>
                                <span class="text-success">Tuntas</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-0 shadow-sm" style="background-color: #fef1f2; border-radius: 12px;">
                        <div class="card-body">
                            <span class="badge bg-white text-danger mb-2 shadow-sm">KKM: 70</span>
                            <h5 class="card-title fw-bold text-dark">Matematika</h5>
                            <p class="card-text text-muted mb-3"><i class="fa-solid fa-user-tie me-2"></i>Ni Wayan Kasrinayanti, S.Pd.</p>
                            <div class="progress mb-3" style="height: 10px;">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: 65%;" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="d-flex justify-content-between text-muted small fw-bold">
                                <span>Nilai Akhir: 65</span>
                                <span class="text-danger">Remedial</span>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
            
            <hr class="text-muted">
            <h5 class="mt-3 mb-3 fw-bold" style="color: var(--pastel-text);">Rata-Rata Nilai Rapor Semester Ini: <span style="font-size: 1.5rem; color: #d97706;">80.00</span></h5>
            
        </div>
    </div>
</div>

<?= $this->endSection() ?>
