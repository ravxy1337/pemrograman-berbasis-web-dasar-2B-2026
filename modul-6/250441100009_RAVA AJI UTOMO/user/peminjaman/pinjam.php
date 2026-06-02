<?php
session_start();
require_once '../../config/koneksi.php';
require_once '../../config/auth.php';
cekLogin();
cekRole('user');

$pesan = '';
$tipe_pesan = '';

if (!isset($_GET['id_buku'])) {
    header('Location: ../katalog.php');
    exit;
}

$id_buku = (int) $_GET['id_buku'];
$id_user = $_SESSION['id'];

// Ambil info buku
$q_buku = mysqli_query($koneksi, "SELECT * FROM buku WHERE id = $id_buku");
if (mysqli_num_rows($q_buku) == 0) {
    header('Location: ../katalog.php');
    exit;
}
$buku = mysqli_fetch_assoc($q_buku);

$q_cek = mysqli_query($koneksi, "SELECT id FROM peminjaman WHERE id_pengguna = $id_user AND id_buku = $id_buku AND status IN ('dipinjam', 'terlambat')");
$sedang_dipinjam = mysqli_num_rows($q_cek) > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($buku['stok'] <= 0) {
        $pesan = 'Maaf, stok buku sedang kosong.';
        $tipe_pesan = 'danger';
    } elseif ($sedang_dipinjam) {
        $pesan = 'Anda sedang meminjam buku ini dan belum mengembalikannya.';
        $tipe_pesan = 'warning';
    } else {
        $tgl_pinjam = $_POST['tanggal_pinjam'];
        $tgl_kembali = $_POST['tanggal_kembali'];
        $catatan = trim($_POST['catatan']);

        // Validasi tanggal
        $tgl_pinjam_obj = new DateTime($tgl_pinjam);
        $tgl_kembali_obj = new DateTime($tgl_kembali);
        $diff = $tgl_pinjam_obj->diff($tgl_kembali_obj)->days;
        $is_future = $tgl_kembali_obj > $tgl_pinjam_obj;

        if (!$is_future) {
            $pesan = 'Tanggal kembali harus lebih besar dari tanggal pinjam.';
            $tipe_pesan = 'danger';
        } elseif ($diff > 14) {
            $pesan = 'Batas maksimal peminjaman adalah 14 hari.';
            $tipe_pesan = 'danger';
        } else {
            mysqli_begin_transaction($koneksi);
            try {

                $stmt = mysqli_prepare($koneksi, "INSERT INTO peminjaman (id_pengguna, id_buku, tanggal_pinjam, tanggal_kembali, catatan) VALUES (?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "iisss", $id_user, $id_buku, $tgl_pinjam, $tgl_kembali, $catatan);
                mysqli_stmt_execute($stmt);

                // Kurangi stok buku
                $koneksi->query("UPDATE buku SET stok = stok - 1 WHERE id = $id_buku");

                mysqli_commit($koneksi);

                $_SESSION['pesan'] = 'Peminjaman berhasil. Silakan ambil buku di perpustakaan.';
                $_SESSION['tipe_pesan'] = 'success';
                header('Location: index.php');
                exit;

            } catch (Exception $e) {
                mysqli_rollback($koneksi);
                $pesan = 'Gagal meminjam buku: ' . $e->getMessage();
                $tipe_pesan = 'danger';
            }
        }
    }
}

$judul_halaman = 'Pinjam Buku';
require_once '../../includes/head.php';
require_once '../../includes/sidebar_user.php';

$tgl_hari_ini = date('Y-m-d');
$tgl_kembali_default = date('Y-m-d', strtotime('+3 days'));
$tgl_kembali_max = date('Y-m-d', strtotime('+14 days'));
?>

<div class="row">
    <div class="col-md-12 mb-3">
        <?php if ($pesan): ?>
            <div class="alert alert-<?= $tipe_pesan ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($pesan) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($sedang_dipinjam && empty($pesan)): ?>
            <div class="alert alert-warning">
                Anda sudah meminjam buku ini dan belum mengembalikannya. Anda tidak dapat meminjam buku yang sama sebelum
                buku dikembalikan.
            </div>
        <?php endif; ?>
    </div>

    <div class="col-md-4">
        <div class="card book-card border-0 shadow-sm mb-4">
            <?php $cover = !empty($buku['cover_buku']) ? BASE_URL . '/assets/uploads/cover/' . $buku['cover_buku'] : BASE_URL . '/assets/uploads/cover/default.jpg'; ?>
            <img src="<?= $cover ?>" class="card-img-top book-cover" alt="Cover" style="height: auto;">
            <div class="card-body bg-light">
                <h5 class="card-title"><?= htmlspecialchars($buku['judul']) ?></h5>
                <p class="mb-1 text-muted">Pengarang: <?= htmlspecialchars($buku['pengarang']) ?></p>
                <p class="mb-1 text-muted">Kategori: <?= htmlspecialchars($buku['kategori']) ?></p>
                <p class="mb-0 text-muted">Tersedia: <strong><?= $buku['stok'] ?> buku</strong></p>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header">
                Form Peminjaman
            </div>
            <div class="card-body">
                <form action="" method="POST" id="formPinjam">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_pinjam" class="form-label">Tanggal Pinjam</label>
                            <input type="date" class="form-control" id="tanggal_pinjam" name="tanggal_pinjam"
                                value="<?= $tgl_hari_ini ?>" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_kembali" class="form-label">Tanggal Kembali</label>
                            <input type="date" class="form-control" id="tanggal_kembali" name="tanggal_kembali"
                                value="<?= $tgl_kembali_default ?>" min="<?= date('Y-m-d', strtotime('+1 days')) ?>"
                                max="<?= $tgl_kembali_max ?>" required <?= $buku['stok'] <= 0 || $sedang_dipinjam ? 'disabled' : '' ?>>
                            <div class="form-text">Maksimal 14 hari dari tanggal pinjam. Default: 3 hari.</div>
                        </div>
                        <div class="col-md-12 mb-4">
                            <label for="catatan" class="form-label">Catatan (Opsional)</label>
                            <textarea class="form-control" id="catatan" name="catatan" rows="3" <?= $buku['stok'] <= 0 || $sedang_dipinjam ? 'disabled' : '' ?>></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="../katalog.php" class="btn btn-secondary">Batal / Kembali</a>
                        <?php if ($buku['stok'] > 0 && !$sedang_dipinjam): ?>
                            <button type="submit" class="btn btn-primary">Proses Peminjaman</button>
                        <?php else: ?>
                            <button type="button" class="btn btn-primary" disabled>Proses Peminjaman</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('formPinjam').addEventListener('submit', function (e) {
        let tgl_pinjam = new Date(document.getElementById('tanggal_pinjam').value);
        let tgl_kembali = new Date(document.getElementById('tanggal_kembali').value);

        // Reset
        document.getElementById('tanggal_kembali').classList.remove('is-invalid');
        const elErr = document.getElementById('tanggal_kembali').nextElementSibling;
        if (elErr && elErr.classList.contains('invalid-feedback')) elErr.remove();

        if (tgl_kembali <= tgl_pinjam) {
            document.getElementById('tanggal_kembali').classList.add('is-invalid');
            let divError = document.createElement('div');
            divError.className = 'invalid-feedback';
            divError.textContent = 'Tanggal kembali harus lebih besar dari tanggal pinjam';
            document.getElementById('tanggal_kembali').parentNode.insertBefore(divError, document.getElementById('tanggal_kembali').nextSibling);
            e.preventDefault();
            return;
        }

        // Cek selisih hari
        let timeDiff = tgl_kembali.getTime() - tgl_pinjam.getTime();
        let diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));

        if (diffDays > 14) {
            document.getElementById('tanggal_kembali').classList.add('is-invalid');
            let divError = document.createElement('div');
            divError.className = 'invalid-feedback';
            divError.textContent = 'Batas maksimal peminjaman adalah 14 hari';
            document.getElementById('tanggal_kembali').parentNode.insertBefore(divError, document.getElementById('tanggal_kembali').nextSibling);
            e.preventDefault();
        }
    });
</script>
</body>

</html>