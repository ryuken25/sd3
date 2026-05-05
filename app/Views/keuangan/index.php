<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold" style="color: var(--pastel-text);">Tabungan & Administrasi Sekolah</h2>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?= base_url() ?>" class="text-decoration-none" style="color: #d97706;">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Administrasi</li>
        </ol>
    </nav>
</div>

<div class="row mb-4">
    <!-- Info Section -->
    <div class="col-md-4 mb-4 mb-md-0">
        <div class="card h-100 shadow border-0" style="border-radius: 16px; background: linear-gradient(135deg, var(--pastel-primary) 0%, var(--pastel-accent) 100%);">
            <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                <i class="fa-solid fa-wallet fa-4x mb-3 text-white"></i>
                <h5 class="fw-bold mb-1" style="color: var(--pastel-text);">Saldo Tagihan Anda</h5>
                <h2 class="fw-bolder my-3" style="color: #78350f;">Rp 1.500.000</h2>
                <span class="badge bg-danger rounded-pill px-3 py-2 align-self-center shadow-sm">Belum Lunas</span>
            </div>
            <div class="card-footer bg-transparent border-0 pb-4 text-center">
                <button class="btn btn-dark rounded-pill px-4 fw-bold">Bayar Sekarang &nbsp; <i class="fa-solid fa-arrow-right"></i></button>
            </div>
        </div>
    </div>
    
    <!-- Riwayat Transaksi -->
    <div class="col-md-8">
        <div class="card h-100 shadow border-0 p-4" style="border-radius: 16px;">
            <h5 class="mb-3 fw-bold" style="color: var(--pastel-text); border-bottom: 2px solid var(--pastel-primary); display: inline-block; padding-bottom: 5px;">Riwayat Transaksi Terakhir</h5>
            
            <div class="table-responsive mt-2">
                <table class="table table-borderless table-striped align-middle">
                    <thead style="background-color: var(--pastel-bg);">
                        <tr>
                            <th scope="col" class="py-3 text-muted">Tanggal</th>
                            <th scope="col" class="py-3 text-muted">Deskripsi</th>
                            <th scope="col" class="py-3 text-muted">Jumlah</th>
                            <th scope="col" class="py-3 text-muted">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-bold">12 Mar 2026</td>
                            <td>Setoran Tabungan Siswa</td>
                            <td class="text-success fw-bold">+ Rp 50.000</td>
                            <td><span class="badge bg-success rounded-pill">Sukses</span></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">15 Feb 2026</td>
                            <td>Setoran Tabungan Siswa</td>
                            <td class="text-success fw-bold">+ Rp 20.000</td>
                            <td><span class="badge bg-success rounded-pill">Sukses</span></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">02 Jan 2026</td>
                            <td>Iuran Komite & Seragam</td>
                            <td class="text-danger fw-bold">- Rp 500.000</td>
                            <td><span class="badge bg-success rounded-pill">Lunas</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="text-center mt-3">
                <a href="#" class="text-decoration-none fw-bold" style="color: #d97706;">Lihat Semua Transaksi <i class="fa-solid fa-angles-right ms-1"></i></a>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
