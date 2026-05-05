<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> - SDN 3 Mekarsari</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --pastel-blue: #d9e9f5;
            --pastel-green: #dcf7e8;
            --pastel-yellow: #fff4cc;
            --pastel-orange: #ffe2c7;
            --pastel-red: #ffe0de;
            --pastel-gray: #f4f7fb;
            --pastel-dark: #1f3555;
            --pastel-sidebar: #ffffff;
            --surface: #ffffff;
            --border-soft: #dbe5f0;
            --text-muted: #6b7a90;
            --shadow-soft: 0 18px 45px rgba(28, 57, 90, 0.08);
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--pastel-gray);
            color: #24364b;
        }

        .sidebar {
            min-height: 100vh;
            background-color: var(--pastel-sidebar);
            color: #333;
            padding-top: 20px;
            border-right: 1px solid var(--border-soft);
        }

        .sidebar a {
            color: #4b5d73;
            text-decoration: none;
            padding: 12px 18px;
            display: block;
            margin: 0 12px 6px;
            border-radius: 12px;
            font-weight: 600;
            transition: all .2s ease;
        }

        .sidebar a:hover,
        .sidebar a.active {
            color: #163d63;
            background-color: var(--pastel-blue);
            box-shadow: inset 0 0 0 1px rgba(26, 82, 118, 0.08);
        }

        .sidebar-heading {
            padding: 12px 20px 8px;
            font-size: 0.85rem;
            text-transform: uppercase;
            color: #8091a7;
            font-weight: 700;
            letter-spacing: .08em;
        }

        .content {
            padding: 28px;
        }

        .navbar {
            background-color: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-soft);
        }

        .text-pastel-primary {
            color: #1a5276 !important;
        }

        .bg-pastel-primary {
            background-color: var(--pastel-blue) !important;
            color: #1a5276 !important;
        }

        /* High-contrast CTA for admin action buttons (Tambah/Simpan/Update) */
        .btn.btn-primary.bg-pastel-primary,
        .btn-cta-primary {
            background: linear-gradient(135deg, #356a96, #204a75) !important;
            border-color: #204a75 !important;
            color: #ffffff !important;
        }

        .btn.btn-primary.bg-pastel-primary:hover,
        .btn.btn-primary.bg-pastel-primary:focus,
        .btn.btn-primary.bg-pastel-primary:active,
        .btn-cta-primary:hover,
        .btn-cta-primary:focus,
        .btn-cta-primary:active {
            background: linear-gradient(135deg, #2e5c83, #1b3f63) !important;
            border-color: #1b3f63 !important;
            color: #ffffff !important;
        }

        .bg-pastel-success {
            background-color: var(--pastel-green) !important;
            color: #145a14 !important;
        }

        .bg-pastel-warning {
            background-color: var(--pastel-yellow) !important;
            color: #7a5a00 !important;
        }

        .bg-pastel-danger {
            background-color: var(--pastel-red) !important;
            color: #8a2d23 !important;
        }

        .bg-pastel-info {
            background-color: var(--pastel-orange) !important;
            color: #8a4d0f !important;
        }

        .app-shell {
            min-height: 100vh;
        }

        .brand-mark {
            width: 84px;
            height: 84px;
            background: linear-gradient(145deg, #e6f2fb, #cfe4f5);
            border: 1px solid var(--border-soft);
        }

        .page-surface,
        .card,
        .modal-content,
        .dropdown-menu {
            border: 1px solid var(--border-soft);
            box-shadow: var(--shadow-soft);
        }

        .card,
        .modal-content {
            border-radius: 18px;
            overflow: hidden;
        }

        .card-header,
        .modal-header,
        .modal-footer {
            background-color: #fff;
            border-color: var(--border-soft);
        }

        .table thead th {
            border-bottom-width: 1px;
            background: #f8fbff;
            color: #4b6078;
            font-size: .82rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .table> :not(caption)>*>* {
            padding: .9rem .85rem;
            border-color: #e6edf5;
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background: #f8fbff;
        }

        .form-control,
        .form-select {
            min-height: 46px;
            border-radius: 12px;
            border-color: #d6e0eb;
        }

        textarea.form-control {
            min-height: unset;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #8bb8d8;
            box-shadow: 0 0 0 .25rem rgba(63, 137, 191, 0.12);
        }

        .btn {
            border-radius: 12px;
            font-weight: 600;
        }

        .btn-primary {
            background: linear-gradient(135deg, #356a96, #204a75);
            border-color: #204a75;
        }

        .alert {
            border: 1px solid var(--border-soft);
            border-radius: 16px;
        }

        .badge {
            border-radius: 999px;
            padding: .5rem .75rem;
            font-weight: 700;
        }

        .topbar-meta {
            color: var(--text-muted);
            font-size: .95rem;
        }

        .mobile-sidebar-toggle {
            border-radius: 12px;
        }

        .offcanvas.offcanvas-start {
            width: 290px;
        }

        @media (max-width: 991.98px) {
            .content {
                padding: 20px 16px 28px;
            }
        }
    </style>
    <?= $this->renderSection('styles') ?>
</head>

<body>

    <div class="container-fluid app-shell">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-none d-md-block sidebar px-0">
                <div class="text-center mb-4">
                    <?php
                    $logoPath = FCPATH . 'assets/images/logo.png';
                    if (file_exists($logoPath)):
                        ?>
                        <img src="<?= base_url('assets/images/logo.png') ?>" alt="Logo SDN 3" class="img-fluid px-4 mb-2"
                            style="max-height: 100px;">
                    <?php else: ?>
                        <div
                            class="brand-mark rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center">
                            <i class="bi bi-building fs-1" style="color: #1a5276;"></i>
                        </div>
                    <?php endif; ?>
                    <h6 class="fw-bold px-2 text-pastel-primary">SDN 3 MEKARSARI</h6>
                    <small class="text-muted">Sistem Informasi Nilai Siswa</small>
                </div>

                <?= $this->renderSection('sidebar') ?>

                <hr class="border-secondary mx-3">
                <a href="<?= base_url('logout') ?>" class="text-danger"><i class="bi bi-box-arrow-right me-2"></i>
                    Logout</a>
            </nav>

            <div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="mobileSidebar"
                aria-labelledby="mobileSidebarLabel">
                <div class="offcanvas-header border-bottom">
                    <div>
                        <h5 class="offcanvas-title text-pastel-primary fw-bold mb-0" id="mobileSidebarLabel">SDN 3
                            Mekarsari</h5>
                        <small class="text-muted">Navigasi utama</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body sidebar pt-3">
                    <?= $this->renderSection('sidebar') ?>
                    <hr class="border-secondary mx-3">
                    <a href="<?= base_url('logout') ?>" class="text-danger"><i class="bi bi-box-arrow-right me-2"></i>
                        Logout</a>
                </div>
            </div>

            <!-- Main Content -->
            <main class="col-md-10 ms-sm-auto px-0">
                <nav class="navbar navbar-expand-lg navbar-light px-4 py-3">
                    <div class="container-fluid">
                        <div class="d-flex align-items-center gap-3">
                            <button class="btn btn-outline-secondary mobile-sidebar-toggle d-md-none" type="button"
                                data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar"
                                aria-controls="mobileSidebar">
                                <i class="bi bi-list"></i>
                            </button>
                            <div>
                                <span class="navbar-brand mb-0 h1 d-block"><?= esc($title) ?></span>
                                <small class="topbar-meta">Panel akademik SDN 3 Mekarsari</small>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="text-end d-none d-sm-block">
                                <div class="fw-semibold">Halo, <?= esc((string) session()->get('nama_lengkap')) ?></div>
                                <small class="topbar-meta">Akses
                                    <?= esc(str_replace('_', ' ', (string) session()->get('role'))) ?></small>
                            </div>
                            <span
                                class="badge bg-primary text-uppercase"><?= esc((string) session()->get('role')) ?></span>
                        </div>
                    </div>
                </nav>

                <div class="content">
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= session()->getFlashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= session()->getFlashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('info')): ?>
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <?= session()->getFlashdata('info') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?= $this->renderSection('content') ?>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?= $this->renderSection('scripts') ?>
</body>

</html>
