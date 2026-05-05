<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Informasi Nilai Siswa SDN 3 Mekarsari</title>
    <!-- Add Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(174, 198, 207, 0.35), transparent 32%),
                linear-gradient(135deg, #f4f7fb 0%, #eef4fb 100%);
            font-family: 'Inter', sans-serif;
        }

        .login-box {
            width: min(100%, 1080px);
            margin: 0 auto;
        }

        .login-logo {
            margin-bottom: 24px;
        }

        .brand-badge {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(145deg, #e2eef9, #d5e7f7);
            color: #1a5276;
            box-shadow: 0 16px 35px rgba(30, 69, 108, 0.12);
        }

        .login-logo h4 {
            font-weight: 800;
            color: #1f3555;
            margin-top: 16px;
        }

        .login-logo p {
            color: #66768c;
            font-size: 14px;
        }

        .hero-card,
        .login-card {
            border: 1px solid #d9e4f0;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.94);
            box-shadow: 0 25px 60px rgba(31, 53, 85, 0.10);
        }

        .login-card {
            overflow: hidden;
        }

        .form-control {
            min-height: 50px;
            border-radius: 14px;
            border-color: #d6e0eb;
            padding-left: 46px;
        }

        .form-control:focus {
            border-color: #8bb8d8;
            box-shadow: 0 0 0 .25rem rgba(63, 137, 191, .12);
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #789;
        }

        .btn-login {
            min-height: 50px;
            border-radius: 14px;
            background: linear-gradient(135deg, #356a96, #204a75);
            border: 0;
            font-weight: 700;
        }

        .feature-chip {
            border-radius: 999px;
            padding: .55rem .85rem;
            background: #f7fbff;
            border: 1px solid #deebf5;
            font-size: .9rem;
            color: #33506d;
            font-weight: 600;
        }

        @media (max-width: 991.98px) {
            .login-box {
                padding: 24px 16px;
            }
        }
    </style>
</head>

<body>

    <div class="container min-vh-100 d-flex align-items-center py-4">
        <div class="login-box">
            <div class="row g-4 align-items-stretch">
                <div class="col-lg-6">
                    <div class="hero-card h-100 p-4 p-lg-5 d-flex flex-column justify-content-between">
                        <div>
                            <div class="brand-badge mb-3">
                                <i class="bi bi-mortarboard-fill fs-2"></i>
                            </div>
                            <div class="login-logo text-start">
                                <span class="badge text-bg-light border mb-3">SDN 3 Mekarsari</span>
                                <h4>Sistem Informasi Manajemen Nilai Siswa</h4>
                                <p class="mb-0">Platform akademik untuk admin, guru, dan orang tua dalam mengelola
                                    nilai, remedial, serta e-rapor digital secara aman dan rapi.</p>
                            </div>
                        </div>


                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="login-card h-100">
                        <div class="card-body p-4 p-lg-5">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div>
                                    <h5 class="fw-bold mb-1">Masuk ke Sistem</h5>
                                    <p class="text-muted mb-0">Gunakan akun sesuai peran masing-masing.</p>
                                </div>
                                <span class="badge bg-pastel-primary">CI4 + Bootstrap 5</span>
                            </div>

                            <?php if (session()->getFlashdata('error')): ?>
                                <div class="alert alert-danger border-0 shadow-sm" role="alert">
                                    <i
                                        class="bi bi-exclamation-triangle-fill me-2"></i><?= session()->getFlashdata('error') ?>
                                </div>
                            <?php endif; ?>

                            <?php if (session()->getFlashdata('info')): ?>
                                <div class="alert alert-info border-0 shadow-sm" role="alert">
                                    <i class="bi bi-info-circle-fill me-2"></i><?= session()->getFlashdata('info') ?>
                                </div>
                            <?php endif; ?>

                            <form action="<?= base_url('auth/process') ?>" method="post" class="needs-validation"
                                novalidate>
                                <?= csrf_field() ?>
                                <div class="mb-3">
                                    <label for="username" class="form-label fw-semibold">Username / NIS</label>
                                    <div class="position-relative">
                                        <i class="bi bi-person-badge input-icon"></i>
                                        <input type="text" class="form-control" name="username" id="username"
                                            placeholder="Masukkan username admin/guru atau NIS siswa" required
                                            autofocus>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label for="password" class="form-label fw-semibold">Password</label>
                                    <div class="position-relative">
                                        <i class="bi bi-shield-lock input-icon"></i>
                                        <input type="password" class="form-control" name="password" id="password"
                                            placeholder="Masukkan password" required>
                                    </div>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-login">Login Sistem</button>
                                </div>
                            </form>

                            <div class="rounded-4 bg-light border p-3 mt-4 small text-muted">
                                <div class="fw-semibold text-dark mb-2">Petunjuk cepat</div>
                                <ul class="mb-0 ps-3">
                                    <li>Orang tua login menggunakan NIS siswa.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>