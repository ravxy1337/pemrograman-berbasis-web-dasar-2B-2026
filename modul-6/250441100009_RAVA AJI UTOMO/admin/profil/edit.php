<?php
session_start();
require_once '../../config/koneksi.php';
require_once '../../config/auth.php';
cekLogin();
cekRole('admin');

$pesan = '';
$tipe_pesan = '';
$id_user = $_SESSION['id'];

$q_user = mysqli_query($koneksi, "SELECT * FROM pengguna WHERE id = $id_user");
$user = mysqli_fetch_assoc($q_user);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profil'])) {
        $nama_lengkap = trim($_POST['nama_lengkap']);
        $email = trim($_POST['email']);
        $no_telp = trim($_POST['no_telp']);
        $alamat = trim($_POST['alamat']);
        $foto_profil = $user['foto_profil'];

        if (empty($nama_lengkap) || empty($email)) {
            $pesan = 'Nama lengkap dan email wajib diisi.';
            $tipe_pesan = 'danger';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $pesan = 'Format email tidak valid.';
            $tipe_pesan = 'danger';
        } else {
            // Cek email exist for other user
            $stmt_cek = mysqli_prepare($koneksi, "SELECT id FROM pengguna WHERE email = ? AND id != ?");
            mysqli_stmt_bind_param($stmt_cek, "si", $email, $id_user);
            mysqli_stmt_execute($stmt_cek);
            mysqli_stmt_store_result($stmt_cek);
            
            if (mysqli_stmt_num_rows($stmt_cek) > 0) {
                $pesan = 'Email sudah digunakan oleh pengguna lain.';
                $tipe_pesan = 'danger';
            } else {
                // Upload Foto
                if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
                    $ekstensi_diizinkan = ['jpg', 'jpeg', 'png', 'webp'];
                    $nama_file = $_FILES['foto_profil']['name'];
                    $ukuran = $_FILES['foto_profil']['size'];
                    $tmp = $_FILES['foto_profil']['tmp_name'];
                    
                    $ekstensi = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
                    
                    if (!in_array($ekstensi, $ekstensi_diizinkan)) {
                        $pesan = 'Ekstensi file tidak diizinkan.';
                        $tipe_pesan = 'danger';
                    } elseif ($ukuran > 2097152) {
                        $pesan = 'Ukuran file maksimal 2MB.';
                        $tipe_pesan = 'danger';
                    } else {
                        $nama_baru = uniqid() . '_' . preg_replace("/[^a-zA-Z0-9.-]/", "_", $nama_file);
                        $path = '../../assets/uploads/foto/' . $nama_baru;
                        
                        if (move_uploaded_file($tmp, $path)) {
                            if (!empty($user['foto_profil']) && file_exists('../../assets/uploads/foto/' . $user['foto_profil'])) {
                                unlink('../../assets/uploads/foto/' . $user['foto_profil']);
                            }
                            $foto_profil = $nama_baru;
                            $_SESSION['foto_profil'] = $nama_baru; // update session
                        }
                    }
                }
                
                if (empty($pesan)) {
                    $stmt = mysqli_prepare($koneksi, "UPDATE pengguna SET nama_lengkap=?, email=?, no_telp=?, alamat=?, foto_profil=? WHERE id=?");
                    mysqli_stmt_bind_param($stmt, "sssssi", $nama_lengkap, $email, $no_telp, $alamat, $foto_profil, $id_user);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $_SESSION['nama_lengkap'] = $nama_lengkap; // update session
                        $_SESSION['pesan'] = 'Profil berhasil diperbarui.';
                        $_SESSION['tipe_pesan'] = 'success';
                        header('Location: edit.php');
                        exit;
                    } else {
                        $pesan = 'Gagal memperbarui profil: ' . mysqli_error($koneksi);
                        $tipe_pesan = 'danger';
                    }
                    mysqli_stmt_close($stmt);
                }
            }
            mysqli_stmt_close($stmt_cek);
        }
    } elseif (isset($_POST['update_password'])) {
        $kata_sandi_lama = $_POST['kata_sandi_lama'];
        $kata_sandi_baru = $_POST['kata_sandi_baru'];
        $konfirmasi = $_POST['konfirmasi_kata_sandi_baru'];
        
        if (empty($kata_sandi_lama) || empty($kata_sandi_baru) || empty($konfirmasi)) {
            $pesan = 'Semua kolom kata sandi wajib diisi.';
            $tipe_pesan = 'danger';
        } elseif (!password_verify($kata_sandi_lama, $user['kata_sandi'])) {
            $pesan = 'Kata sandi lama salah.';
            $tipe_pesan = 'danger';
        } elseif (strlen($kata_sandi_baru) < 8) {
            $pesan = 'Kata sandi baru minimal 8 karakter.';
            $tipe_pesan = 'danger';
        } elseif ($kata_sandi_baru !== $konfirmasi) {
            $pesan = 'Konfirmasi kata sandi tidak cocok.';
            $tipe_pesan = 'danger';
        } else {
            $hash = password_hash($kata_sandi_baru, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($koneksi, "UPDATE pengguna SET kata_sandi=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "si", $hash, $id_user);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['pesan'] = 'Kata sandi berhasil diubah.';
                $_SESSION['tipe_pesan'] = 'success';
                header('Location: edit.php');
                exit;
            } else {
                $pesan = 'Gagal mengubah kata sandi.';
                $tipe_pesan = 'danger';
            }
            mysqli_stmt_close($stmt);
        }
    }
}

$judul_halaman = 'Edit Profil Admin';
require_once '../../includes/head.php';
require_once '../../includes/sidebar_admin.php';
?>

<div class="row">
    <div class="col-md-12 mb-3">
        <?php if (isset($_SESSION['pesan'])): ?>
            <div class="alert alert-<?= $_SESSION['tipe_pesan'] ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['pesan']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php 
                unset($_SESSION['pesan']);
                unset($_SESSION['tipe_pesan']);
            ?>
        <?php endif; ?>
        <?php if ($pesan): ?>
            <div class="alert alert-<?= $tipe_pesan ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($pesan) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                Informasi Profil
            </div>
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="update_profil" value="1">
                    <div class="row mb-3">
                        <div class="col-md-4 text-center mb-3 mb-md-0">
                            <?php $foto = !empty($user['foto_profil']) ? BASE_URL . '/assets/uploads/foto/' . $user['foto_profil'] : BASE_URL . '/assets/uploads/foto/default.jpg'; ?>
                            <img src="<?= $foto ?>" alt="Foto Profil" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover; border: 3px solid var(--warna-latar);">
                            <div class="mb-3">
                                <label for="foto_profil" class="form-label d-block text-start">Ganti Foto</label>
                                <input class="form-control form-control-sm" type="file" id="foto_profil" name="foto_profil" accept=".jpg,.jpeg,.png,.webp">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="username" class="form-label">Nama Pengguna</label>
                                <input type="text" class="form-control" id="username" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                                <div class="form-text">Nama pengguna tidak dapat diubah.</div>
                            </div>
                            <div class="mb-3">
                                <label for="nama_lengkap" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?= htmlspecialchars($user['nama_lengkap']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="no_telp" class="form-label">No. Telepon</label>
                            <input type="text" class="form-control" id="no_telp" name="no_telp" value="<?= htmlspecialchars((string)$user['no_telp']) ?>">
                        </div>
                        <div class="col-md-12 mb-4">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3"><?= htmlspecialchars((string)$user['alamat']) ?></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan Profil</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header">
                Ganti Kata Sandi
            </div>
            <div class="card-body">
                <form action="" method="POST" id="formPassword">
                    <input type="hidden" name="update_password" value="1">
                    <div class="mb-3">
                        <label for="kata_sandi_lama" class="form-label">Kata Sandi Lama <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="kata_sandi_lama" name="kata_sandi_lama" required>
                    </div>
                    <div class="mb-3">
                        <label for="kata_sandi_baru" class="form-label">Kata Sandi Baru <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="kata_sandi_baru" name="kata_sandi_baru" minlength="8" required>
                    </div>
                    <div class="mb-4">
                        <label for="konfirmasi_kata_sandi_baru" class="form-label">Konfirmasi Sandi Baru <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="konfirmasi_kata_sandi_baru" name="konfirmasi_kata_sandi_baru" minlength="8" required>
                    </div>
                    <button type="submit" class="btn btn-warning w-100">Ganti Kata Sandi</button>
                </form>
            </div>
        </div>
    </div>
</div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('formPassword').addEventListener('submit', function(e) {
    const baru = document.getElementById('kata_sandi_baru');
    const konf = document.getElementById('konfirmasi_kata_sandi_baru');
    
    if (baru.value !== konf.value) {
        konf.classList.add('is-invalid');
        let divError = konf.nextElementSibling;
        if (!divError || !divError.classList.contains('invalid-feedback')) {
            divError = document.createElement('div');
            divError.className = 'invalid-feedback';
            konf.parentNode.insertBefore(divError, konf.nextSibling);
        }
        divError.textContent = 'Konfirmasi kata sandi tidak cocok';
        e.preventDefault();
    }
});
</script>
</body>
</html>


<!-- dashboard.php -->
 <?php
session_start();
require_once '../config/koneksi.php';
require_once '../config/auth.php';
cekLogin();
cekRole('admin');

$judul_halaman = 'Dashboard Admin';

// Ambil statistik
$q_buku = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM buku");
$total_buku = mysqli_fetch_assoc($q_buku)['total'];

$q_pengguna = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pengguna WHERE peran = 'user'");
$total_pengguna = mysqli_fetch_assoc($q_pengguna)['total'];

$q_peminjaman = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dipinjam'");
$total_peminjaman = mysqli_fetch_assoc($q_peminjaman)['total'];

$q_terlambat = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'terlambat' OR (status = 'dipinjam' AND tanggal_kembali < CURDATE())");
$total_terlambat = mysqli_fetch_assoc($q_terlambat)['total'];

require_once '../includes/head.php';
require_once '../includes/sidebar_admin.php';
?>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><?= $total_buku ?></h3>
                    <p class="mb-0">Total Buku</p>
                </div>
                <i class="bi bi-book fs-1 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><?= $total_pengguna ?></h3>
                    <p class="mb-0">Pengguna Terdaftar</p>
                </div>
                <i class="bi bi-people fs-1 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><?= $total_peminjaman ?></h3>
                    <p class="mb-0">Peminjaman Aktif</p>
                </div>
                <i class="bi bi-journal-arrow-up fs-1 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><?= $total_terlambat ?></h3>
                    <p class="mb-0">Terlambat</p>
                </div>
                <i class="bi bi-exclamation-triangle fs-1 opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        Peminjaman Terbaru
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Peminjam</th>
                        <th>Buku</th>
                        <th>Tgl Pinjam</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $q_terbaru = mysqli_query($koneksi, "
                        SELECT p.nama_lengkap, b.judul, pm.tanggal_pinjam, pm.status 
                        FROM peminjaman pm 
                        JOIN pengguna p ON pm.id_pengguna = p.id 
                        JOIN buku b ON pm.id_buku = b.id 
                        ORDER BY pm.id DESC LIMIT 5
                    ");
                    if (mysqli_num_rows($q_terbaru) > 0):
                        while ($row = mysqli_fetch_assoc($q_terbaru)):
                            $badge_class = 'bg-warning text-dark';
                            if ($row['status'] == 'dikembalikan') $badge_class = 'bg-success';
                            elseif ($row['status'] == 'terlambat') $badge_class = 'bg-danger';
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($row['judul']) ?></td>
                        <td><?= date('d M Y', strtotime($row['tanggal_pinjam'])) ?></td>
                        <td><span class="badge <?= $badge_class ?>"><?= ucfirst($row['status']) ?></span></td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">Belum ada data peminjaman</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
