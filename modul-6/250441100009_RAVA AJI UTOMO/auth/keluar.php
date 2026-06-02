<?php
session_start();
session_destroy();
header('Location: ../auth/login.php');
exit;
?>


<!-- keluar.php -->
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
    <title>Daftar - Pustaka Digital</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="auth-wrapper">
        <div class="auth-left d-none d-lg-flex">
            <h1>Pustaka Digital</h1>
            <p>"Jadikan membaca sebagai kebiasaan, dan pengetahuan sebagai senjatamu."</p>
        </div>
        <div class="auth-right">
            <div class="auth-card" style="max-width: 500px;">
                <h3 class="text-center mb-4 brand-text" style="color: var(--warna-utama);">Buat Akun Baru</h3>

                <?php
                $pesan = '';
                $tipe_pesan = '';

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $nama_lengkap = trim($_POST['nama_lengkap']);
                    $username = trim($_POST['username']);
                    $email = trim($_POST['email']);
                    $kata_sandi = $_POST['kata_sandi'];
                    $konfirmasi_kata_sandi = $_POST['konfirmasi_kata_sandi'];

                    if (empty($nama_lengkap) || empty($username) || empty($email) || empty($kata_sandi)) {
                        $pesan = 'Semua field wajib diisi.';
                        $tipe_pesan = 'danger';
                    } elseif ($kata_sandi !== $konfirmasi_kata_sandi) {
                        $pesan = 'Konfirmasi kata sandi tidak cocok.';
                        $tipe_pesan = 'danger';
                    } else {
                        $cek = mysqli_query($koneksi, "SELECT id FROM pengguna WHERE username='$username' OR email='$email'");
                        if (mysqli_num_rows($cek) > 0) {
                            $pesan = 'Nama pengguna atau email sudah terdaftar.';
                            $tipe_pesan = 'danger';
                        } else {
                            $hashed_password = password_hash($kata_sandi, PASSWORD_DEFAULT);
                            $insert = mysqli_query($koneksi, "INSERT INTO pengguna (nama_lengkap, username, email, kata_sandi, peran) VALUES ('$nama_lengkap', '$username', '$email', '$hashed_password', 'user')");

                            if ($insert) {
                                $_SESSION['pesan_sukses'] = 'Pendaftaran berhasil! Silakan masuk.';
                                echo "<meta http-equiv='refresh' content='0;url=login.php'>";
                                exit;
                            } else {
                                $pesan = 'Terjadi kesalahan sistem.';
                                $tipe_pesan = 'danger';
                            }
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

                <form action="" method="POST" id="formDaftar">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap"
                            placeholder="Nama Lengkap"
                            value="<?= isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : '' ?>"
                            required>
                        <label for="nama_lengkap">Nama Lengkap</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="username" name="username"
                            placeholder="Nama Pengguna"
                            value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                            required>
                        <label for="username">Nama Pengguna</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="email" name="email" placeholder="Email"
                            value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                        <label for="email">Email</label>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="form-floating">
                                <input type="password" class="form-control" id="kata_sandi" name="kata_sandi"
                                    placeholder="Kata Sandi" minlength="8" required>
                                <label for="kata_sandi">Kata Sandi</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="form-floating">
                                <input type="password" class="form-control" id="konfirmasi_kata_sandi"
                                    name="konfirmasi_kata_sandi" placeholder="Konfirmasi" minlength="8" required>
                                <label for="konfirmasi_kata_sandi">Konfirmasi Sandi</label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 mb-3">Daftar Sekarang</button>
                    <p class="text-center mb-0">Sudah memiliki akun? <a href="login.php"
                            style="color: var(--warna-kedua);">Masuk di sini</a></p>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('formDaftar').addEventListener('submit', function (e) {
            let valid = true;
            const form = this;

            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

            function tampilkanError(elemen, pesan) {
                elemen.classList.add('is-invalid');
                let divError = document.createElement('div');
                divError.className = 'invalid-feedback';
                divError.textContent = pesan;
                elemen.parentNode.insertBefore(divError, elemen.nextSibling);
                valid = false;
            }

            const nama = document.getElementById('nama_lengkap');
            if (nama.value.trim() === '') tampilkanError(nama, 'Nama lengkap wajib diisi');

            const username = document.getElementById('username');
            if (username.value.trim() === '') tampilkanError(username, 'Nama pengguna wajib diisi');

            const email = document.getElementById('email');
            if (email.value.trim() === '') tampilkanError(email, 'Email wajib diisi');
            else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) tampilkanError(email, 'Format email tidak valid');

            const kata_sandi = document.getElementById('kata_sandi');
            if (kata_sandi.value.trim() === '') tampilkanError(kata_sandi, 'Kata sandi wajib diisi');
            else if (kata_sandi.value.length < 8) tampilkanError(kata_sandi, 'Minimal 8 karakter');

            const konf = document.getElementById('konfirmasi_kata_sandi');
            if (konf.value !== kata_sandi.value) tampilkanError(konf, 'Konfirmasi tidak cocok');

            if (!valid) e.preventDefault();
        });
    </script>
</body>

</html>

<!-- login.php -->
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
