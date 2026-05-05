<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISTEM INFORMASI SEKOLAH</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --pastel-bg: #fffbf0;
            --pastel-primary: #f6e05e; /* Pastel yellow from logo */
            --pastel-primary-dark: #ecc94b;
            --pastel-secondary: #fefcbf;
            --pastel-text: #5f370e;
            --pastel-accent: #fbd38d;
            --pastel-blue: #e0f2fe;    /* Soft blue contrast */
            --pastel-blue-dark: #bae6fd;
        }
        
        body {
            background-color: var(--pastel-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--pastel-text);
        }
        
        /* Custom Header Colors */
        .bg-pastel-header {
            background: linear-gradient(135deg, var(--pastel-primary) 0%, var(--pastel-accent) 100%);
            color: var(--pastel-text);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        
        .topbar {
            height: 80px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 30px;
        }
        
        .header-logo {
            height: 50px;
            width: auto;
            object-fit: contain;
        }
        
        .navbar-custom {
            background-color: #ffffff;
            border-bottom: 2px solid var(--pastel-secondary);
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .nav-link {
            color: #718096;
            font-weight: 600;
            padding: 0.5rem 1rem !important;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .nav-link:hover, .nav-link.active {
            color: var(--pastel-text);
            background-color: var(--pastel-secondary);
        }
        .nav-icon {
            margin-right: 6px;
        }
        
        /* Pastel Card Styles */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.04), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
            overflow: hidden;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
        }
        
        .alert-info-pastel {
            background-color: #e0f2fe;
            color: #0369a1;
            border-left: 4px solid #38bdf8;
        }
        
        .alert-success-pastel {
            background-color: #dcfce7;
            color: #15803d;
            border-left: 4px solid #4ade80;
        }
        
        .alert-warning-pastel {
            background-color: #fef9c3;
            color: #854d0e;
            border-left: 4px solid #facc15;
        }
        
        .btn-pastel {
            background-color: var(--pastel-primary);
            color: var(--pastel-text);
            border: none;
            font-weight: 600;
        }
        
        .btn-pastel:hover {
            background-color: var(--pastel-primary-dark);
        }
    </style>
</head>
<body>

    <!-- Header / Topbar Pastel -->
    <div class="bg-pastel-header topbar">
        <div class="d-flex align-items-center">
            <!-- LOGO KIRI ATAS -->
            <img src="<?= base_url('assets/images/logo.png') ?>" alt="Logo Kiri" class="header-logo me-3 drop-shadow">
            <h4 class="mb-0 fw-bold d-none d-md-block" style="text-shadow: 1px 1px 2px rgba(255,255,255,0.5);">SISTEM INFORMASI SEKOLAH <span class="badge bg-white text-dark ms-2 shadow-sm rounded-pill" style="font-size: 0.7rem; vertical-align: middle;">v 2.0</span></h4>
        </div>
        
        <div class="d-flex align-items-center">
            <!-- User Dropdown & LOGO KANAN ATAS -->
            <div class="dropdown me-4">
                <a href="#" class="text-decoration-none dropdown-toggle d-flex align-items-center" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false" style="color: var(--pastel-text);">
                    <img src="https://ui-avatars.com/api/?name=User&background=fbd38d&color=5f370e&bold=true" alt="User Profile" class="rounded-circle shadow-sm border border-2 border-white" width="45" height="45">
                    <span class="ms-2 fw-bold d-none d-lg-block">Halo, Siswa</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 radius-4" aria-labelledby="dropdownUser">
                    <li><a class="dropdown-item py-2" href="#"><i class="fa-solid fa-user me-2 text-muted"></i> Profile</a></li>
                    <li><a class="dropdown-item py-2" href="#"><i class="fa-solid fa-gear me-2 text-muted"></i> Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item py-2 text-danger fw-bold" href="#"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a></li>
                </ul>
            </div>
            
            <!-- LOGO KANAN ATAS -->
            <img src="<?= base_url('assets/images/logo.png') ?>" alt="Logo Kanan" class="header-logo drop-shadow">
        </div>
    </div>

    <!-- Navigation Bar White -->
    <nav class="navbar navbar-expand-lg navbar-custom py-2 sticky-top">
        <div class="container-fluid">
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-toggle="target" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse d-flex justify-content-center" id="navbarNav">
                <ul class="navbar-nav">
                    <!-- Dynamic Active Navigation Logic -->
                    <?php 
                        $uri = service('uri')->getSegment(1); 
                    ?>
                    <li class="nav-item mx-1">
                        <a class="nav-link <?= ($uri == '' || $uri == 'home') ? 'active' : '' ?>" href="<?= base_url('/') ?>"><i class="fa-solid fa-home nav-icon"></i> Home</a>
                    </li>
                    <li class="nav-item mx-1">
                        <a class="nav-link <?= ($uri == 'civitas') ? 'active' : '' ?>" href="<?= base_url('/civitas') ?>"><i class="fa-solid fa-users nav-icon"></i> Data Guru</a>
                    </li>
                    <li class="nav-item mx-1">
                        <a class="nav-link <?= ($uri == 'personal') ? 'active' : '' ?>" href="<?= base_url('/personal') ?>"><i class="fa-solid fa-user nav-icon"></i> Personal</a>
                    </li>
                    <li class="nav-item mx-1">
                        <a class="nav-link <?= ($uri == 'keuangan') ? 'active' : '' ?>" href="<?= base_url('/keuangan') ?>"><i class="fa-solid fa-wallet nav-icon"></i> Administrasi</a>
                    </li>
                    <li class="nav-item mx-1">
                        <a class="nav-link <?= ($uri == 'akademik') ? 'active' : '' ?>" href="<?= base_url('/akademik') ?>"><i class="fa-solid fa-book nav-icon"></i> Nilai & Rapor</a>
                    </li>
                    <li class="nav-item mx-1">
                        <a class="nav-link <?= ($uri == 'kebijakan') ? 'active' : '' ?>" href="<?= base_url('/kebijakan') ?>"><i class="fa-solid fa-shield-halved nav-icon"></i> Kebijakan & Privasi</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4 mb-5 pb-5">
        <?= $this->renderSection('content') ?>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
