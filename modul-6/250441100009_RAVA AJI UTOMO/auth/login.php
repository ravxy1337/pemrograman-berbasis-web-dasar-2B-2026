<?php
session_start();
require_once '../config/koneksi.php';

if (isset($_SESSION['id'])) {
    if ($_SESSION['peran'] === 'admin') {
        header('Location: ' . BASE_URL . '/admin/dashboard.php');
    } else {
        header('Location: ' . BASE_URL . '/user/dashboard.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Pustaka Digital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-left d-none d-lg-flex">
            <h1>Pustaka Digital</h1>
            <p>"Membaca adalah petualangan tanpa batas yang menuntunmu pada dunia pengetahuan."</p>
        </div>
        <div class="auth-right">
            <div class="auth-card">
                <h3 class="text-center mb-4 brand-text" style="color: var(--warna-utama);">Selamat Datang</h3>
                
                <?php
                $pesan = '';
                $tipe_pesan = '';

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $username = trim($_POST['username']);
                    $kata_sandi = $_POST['kata_sandi'];
                    
                    if (empty($username) || empty($kata_sandi)) {
                        $pesan = 'Nama pengguna dan kata sandi wajib diisi.';
                        $tipe_pesan = 'danger';
                    } else {
                        $query = mysqli_query($koneksi, "SELECT * FROM pengguna WHERE username = '$username'");
                        if (mysqli_num_rows($query) > 0) {
                            $baris = mysqli_fetch_assoc($query);
                            if (password_verify($kata_sandi, $baris['kata_sandi'])) {
                                $_SESSION['id'] = $baris['id'];
                                $_SESSION['username'] = $baris['username'];
                                $_SESSION['nama_lengkap'] = $baris['nama_lengkap'];
                                $_SESSION['peran'] = $baris['peran'];
                                
                                $redirect = $baris['peran'] === 'admin' ? '/admin/dashboard.php' : '/user/dashboard.php';
                                echo "<meta http-equiv='refresh' content='0;url=" . BASE_URL . $redirect . "'>";
                                exit;
                            } else {
                                $pesan = 'Kata sandi salah.';
                                $tipe_pesan = 'danger';
                            }
                        } else {
                            $pesan = 'Nama pengguna tidak ditemukan.';
                            $tipe_pesan = 'danger';
                        }
                    }
                }
                ?>
                
                <?php if ($pesan): ?>
                    <div class="alert alert-<?= $tipe_pesan ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($pesan) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['pesan_sukses'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['pesan_sukses']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['pesan_sukses']); ?>
                <?php endif; ?>
                
                <form action="" method="POST" id="formLogin">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="username" name="username" placeholder="Nama Pengguna" required>
                        <label for="username">Nama Pengguna</label>
                    </div>
                    <div class="form-floating mb-4">
                        <input type="password" class="form-control" id="kata_sandi" name="kata_sandi" placeholder="Kata Sandi" required>
                        <label for="kata_sandi">Kata Sandi</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2 mb-3">Masuk</button>
                    <p class="text-center mb-0">Belum memiliki akun? <a href="daftar.php" style="color: var(--warna-kedua);">Daftar di sini</a></p>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('formLogin').addEventListener('submit', function(e) {
        let valid = true;
        const username = document.getElementById('username');
        const kata_sandi = document.getElementById('kata_sandi');
        
        username.classList.remove('is-invalid');
        kata_sandi.classList.remove('is-invalid');
        
        if (username.value.trim() === '') {
            username.classList.add('is-invalid');
            valid = false;
        }
        
        if (kata_sandi.value.trim() === '') {
            kata_sandi.classList.add('is-invalid');
            valid = false;
        }
        
        if (!valid) e.preventDefault();
    });
    </script>
</body>
</html>
