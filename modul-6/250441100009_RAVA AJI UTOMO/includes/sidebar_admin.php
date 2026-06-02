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

<!-- sidebar_user.php -->
 <div class="sidebar">
    <div class="sidebar-header">
        <h3 class="brand-text">Pustaka Digital</h3>
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="<?= BASE_URL ?>/user/dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                <i class="bi bi-grid-1x2-fill"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/user/katalog.php" class="<?= basename($_SERVER['PHP_SELF']) == 'katalog.php' ? 'active' : '' ?>">
                <i class="bi bi-book-half"></i> Katalog Buku
            </a>
        </li>
        <li>
            <a href="<?= BASE_URL ?>/user/peminjaman/index.php" class="<?= strpos($_SERVER['REQUEST_URI'], '/user/peminjaman/') !== false && basename($_SERVER['PHP_SELF']) != 'pinjam.php' ? 'active' : '' ?>">
                <i class="bi bi-clock-history"></i> Riwayat Peminjaman
            </a>
        </li>
    </ul>
    <div class="sidebar-footer">
        <?php
        $foto_profil = isset($_SESSION['foto_profil']) && !empty($_SESSION['foto_profil']) 
            ? BASE_URL . '/assets/uploads/foto/' . htmlspecialchars($_SESSION['foto_profil']) 
            : BASE_URL . '/assets/uploads/foto/default.jpg';
        ?>
        <img src="<?= $foto_profil ?>" alt="Profil">
        <div class="user-info">
            <p><?= htmlspecialchars($_SESSION['nama_lengkap']) ?></p>
            <small class="badge bg-secondary">Pengguna</small>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="topbar">
        <h4><?= isset($judul_halaman) ? $judul_halaman : 'Dashboard Pengguna' ?></h4>
        <div class="d-flex align-items-center">
            <a href="<?= BASE_URL ?>/user/profil/edit.php" class="btn btn-sm btn-outline-secondary me-2">
                <i class="bi bi-person-fill"></i> Profil
            </a>
            <a href="<?= BASE_URL ?>/auth/keluar.php" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-box-arrow-right"></i> Keluar
            </a>
        </div>
    </div>
    <div class="page-content">
