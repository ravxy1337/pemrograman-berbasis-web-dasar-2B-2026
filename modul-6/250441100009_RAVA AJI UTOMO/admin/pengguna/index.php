<?php
session_start();
require_once '../../config/koneksi.php';
require_once '../../config/auth.php';
cekLogin();
cekRole('admin');

$judul_halaman = 'Data Pengguna';
require_once '../../includes/head.php';
require_once '../../includes/sidebar_admin.php';
?>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Daftar Pengguna</span>
        <a href="tambah.php" class="btn btn-primary btn-sm">
            <i class="bi bi-person-plus"></i> Tambah Pengguna
        </a>
    </div>
    <div class="card-body">
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

        <div class="table-responsive">
            <table class="table table-hover ">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Foto</th>
                        <th>Nama Lengkap</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Peran</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = mysqli_query($koneksi, "SELECT * FROM pengguna ORDER BY dibuat_pada DESC");
                    $no = 1;
                    if (mysqli_num_rows($query) > 0):
                        while ($row = mysqli_fetch_assoc($query)):
                            $foto = !empty($row['foto_profil']) ? BASE_URL . '/assets/uploads/foto/' . $row['foto_profil'] : BASE_URL . '/assets/uploads/foto/default.png';
                            $badge_class = $row['peran'] == 'admin' ? 'bg-primary' : 'bg-secondary';
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><img src="<?= $foto ?>" alt="Foto" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;"></td>
                        <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><span class="badge <?= $badge_class ?>"><?= ucfirst($row['peran']) ?></span></td>
                        <td><?= date('d M Y', strtotime($row['dibuat_pada'])) ?></td>
                        <td>
                            <?php if ($row['id'] !== $_SESSION['id']): ?>
                            <button type="button" class="btn btn-sm btn-danger" onclick="konfirmasiHapus(<?= $row['id'] ?>)" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                            <?php else: ?>
                            <button type="button" class="btn btn-sm btn-secondary" disabled title="Tidak bisa menghapus diri sendiri">
                                <i class="bi bi-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">Belum ada data pengguna.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="modalHapus" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus pengguna ini? 
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="#" id="linkHapus" class="btn btn-danger">Ya, Hapus</a>
            </div>
        </div>
    </div>
</div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function konfirmasiHapus(id) {
    document.getElementById('linkHapus').href = 'hapus.php?id=' + id;
    var modal = new bootstrap.Modal(document.getElementById('modalHapus'));
    modal.show();
}
</script>
</body>
</html>

<!-- hapus.php -->
 <?php
session_start();
require_once '../../config/koneksi.php';
require_once '../../config/auth.php';
cekLogin();
cekRole('admin');

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    if ($id === (int)$_SESSION['id']) {
        $_SESSION['pesan'] = 'Anda tidak dapat menghapus akun Anda sendiri.';
        $_SESSION['tipe_pesan'] = 'danger';
    } else {
        $q_foto = mysqli_query($koneksi, "SELECT foto_profil FROM pengguna WHERE id = $id");
        if (mysqli_num_rows($q_foto) > 0) {
            $foto = mysqli_fetch_assoc($q_foto)['foto_profil'];
            
            $stmt = mysqli_prepare($koneksi, "DELETE FROM pengguna WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            
            if (mysqli_stmt_execute($stmt)) {
                if (!empty($foto) && file_exists('../../assets/uploads/foto/' . $foto)) {
                    unlink('../../assets/uploads/foto/' . $foto);
                }
                $_SESSION['pesan'] = 'Pengguna berhasil dihapus.';
                $_SESSION['tipe_pesan'] = 'success';
            } else {
                $_SESSION['pesan'] = 'Gagal menghapus pengguna: ' . mysqli_error($koneksi);
                $_SESSION['tipe_pesan'] = 'danger';
            }
            mysqli_stmt_close($stmt);
        }
    }
}

header('Location: index.php');
exit;
?>

<!-- tambah.php -->
 <?php
session_start();
require_once '../../config/koneksi.php';
require_once '../../config/auth.php';
cekLogin();
cekRole('admin');

$pesan = '';
$tipe_pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $kata_sandi = $_POST['kata_sandi'];
    $peran = $_POST['peran'];
    $no_telp = trim($_POST['no_telp']);
    $alamat = trim($_POST['alamat']);

    if (empty($nama_lengkap) || empty($username) || empty($email) || empty($kata_sandi) || empty($peran)) {
        $pesan = 'Kolom bertanda * wajib diisi.';
        $tipe_pesan = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan = 'Format email tidak valid.';
        $tipe_pesan = 'danger';
    } elseif (strlen($kata_sandi) < 8) {
        $pesan = 'Kata sandi minimal 8 karakter.';
        $tipe_pesan = 'danger';
    } else {
        // Cek username/email exist
        $stmt_cek = mysqli_prepare($koneksi, "SELECT id FROM pengguna WHERE username = ? OR email = ?");
        mysqli_stmt_bind_param($stmt_cek, "ss", $username, $email);
        mysqli_stmt_execute($stmt_cek);
        mysqli_stmt_store_result($stmt_cek);
        
        if (mysqli_stmt_num_rows($stmt_cek) > 0) {
            $pesan = 'Nama pengguna atau email sudah terdaftar.';
            $tipe_pesan = 'danger';
        } else {
            $kata_sandi_hash = password_hash($kata_sandi, PASSWORD_DEFAULT);
            $no_telp = empty($no_telp) ? null : $no_telp;
            $alamat = empty($alamat) ? null : $alamat;
            
            $stmt = mysqli_prepare($koneksi, "INSERT INTO pengguna (nama_lengkap, username, email, kata_sandi, peran, no_telp, alamat) VALUES (?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sssssss", $nama_lengkap, $username, $email, $kata_sandi_hash, $peran, $no_telp, $alamat);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['pesan'] = 'Pengguna baru berhasil ditambahkan.';
                $_SESSION['tipe_pesan'] = 'success';
                header('Location: index.php');
                exit;
            } else {
                $pesan = 'Terjadi kesalahan saat menyimpan data: ' . mysqli_error($koneksi);
                $tipe_pesan = 'danger';
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_stmt_close($stmt_cek);
    }
}

$judul_halaman = 'Tambah Pengguna';
require_once '../../includes/head.php';
require_once '../../includes/sidebar_admin.php';
?>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Tambah Pengguna Baru</span>
        <a href="index.php" class="btn btn-secondary btn-sm">Kembali</a>
    </div>
    <div class="card-body">
        <?php if ($pesan): ?>
            <div class="alert alert-<?= $tipe_pesan ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($pesan) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="" method="POST" id="formTambah">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nama_lengkap" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?= isset($_POST['nama_lengkap']) ? htmlspecialchars($_POST['nama_lengkap']) : '' ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="username" class="form-label">Nama Pengguna <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="username" name="username" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="kata_sandi" class="form-label">Kata Sandi <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="kata_sandi" name="kata_sandi" minlength="8" required>
                    <div class="form-text">Minimal 8 karakter.</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="peran" class="form-label">Peran <span class="text-danger">*</span></label>
                    <select class="form-select" id="peran" name="peran" required>
                        <option value="">-- Pilih Peran --</option>
                        <option value="user" <?= (isset($_POST['peran']) && $_POST['peran'] == 'user') ? 'selected' : '' ?>>User / Pengguna</option>
                        <option value="admin" <?= (isset($_POST['peran']) && $_POST['peran'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="no_telp" class="form-label">No. Telepon</label>
                    <input type="text" class="form-control" id="no_telp" name="no_telp" maxlength="13" value="<?= isset($_POST['no_telp']) ? htmlspecialchars($_POST['no_telp']) : '' ?>">
                </div>
                <div class="col-md-12 mb-4">
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea class="form-control" id="alamat" name="alamat" rows="3"><?= isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : '' ?></textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Pengguna</button>
        </form>
    </div>
</div>

    </div> 
</div> 

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('formTambah').addEventListener('submit', function(e) {
    let valid = true;
    
    this.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    this.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
    
    function tampilkanError(elemen, pesan) {
        elemen.classList.add('is-invalid');
        let divError = document.createElement('div');
        divError.className = 'invalid-feedback';
        divError.textContent = pesan;
        elemen.parentNode.insertBefore(divError, elemen.nextSibling);
        valid = false;
    }

    const email = document.getElementById('email');
    if (email.value.trim() !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
        tampilkanError(email, 'Format email tidak valid');
    }

    const kata_sandi = document.getElementById('kata_sandi');
    if (kata_sandi.value.length < 8) {
        tampilkanError(kata_sandi, 'Minimal 8 karakter');
    }

    if (!valid) e.preventDefault();
});
</script>
</body>
</html>

