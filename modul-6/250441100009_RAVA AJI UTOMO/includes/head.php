<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($judul_halaman) ? $judul_halaman . ' - Pustaka Digital' : 'Pustaka Digital' ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<!-- sidebar_admin.php -->
 <div class="sidebar">
    <div class="sidebar-header">
        <h3 class="brand-text">Pustaka Digital</h3>
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="<?= BASE_URL ?>/admin/dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/admin/buku/index.php" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/buku/') !== false ? 'active' : '' ?>">
                <i class="bi bi-book-fill"></i> Manajemen Buku
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/admin/peminjaman/index.php" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/peminjaman/') !== false ? 'active' : '' ?>">
                <i class="bi bi-journal-arrow-up"></i> Data Peminjaman
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/admin/pengguna/index.php" class="<?= strpos($_SERVER['REQUEST_URI'], '/admin/pengguna/') !== false ? 'active' : '' ?>">
                <i class="bi bi-people-fill"></i> Data Pengguna
            </a>
        </li>
    </ul>
    <div class="sidebar-footer">
        <?php
        $foto_profil = isset($_SESSION['foto_profil']) && !empty($_SESSION['foto_profil']) 
            ? BASE_URL . '/assets/uploads/foto/' . htmlspecialchars($_SESSION['foto_profil']) 
            : BASE_URL . '/assets/uploads/foto/default.png';
        ?>
        <img src="<?= $foto_profil?>" alt="Profil">
        <div class="user-info">
            <p><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></p>
            <small class="badge bg-primary">Admin</small>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="topbar">
        <h4><?= isset($judul_halaman) ? $judul_halaman : 'Dashboard Admin' ?></h4>
        <div class="d-flex align-items-center">
            <a href="<?= BASE_URL ?>/admin/profil/edit.php" class="btn btn-sm btn-outline-secondary me-2">
                <i class="bi bi-person-fill"></i> Profil
            </a>
            <a href="<?= BASE_URL ?>/auth/keluar.php" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-box-arrow-right"></i> Keluar
            </a>
        </div>
    </div>
    <div class="page-content">
