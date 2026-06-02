<?php
session_start();
require_once '../../config/koneksi.php';
require_once '../../config/auth.php';
cekLogin();
cekRole('admin');

$pesan = '';
$tipe_pesan = '';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int) $_GET['id'];
$q_pinjam = mysqli_query($koneksi, "
    SELECT p.*, pg.nama_lengkap, b.judul, b.id as id_buku 
    FROM peminjaman p 
    JOIN pengguna pg ON p.id_pengguna = pg.id 
    JOIN buku b ON p.id_buku = b.id 
    WHERE p.id = $id
");

if (mysqli_num_rows($q_pinjam) == 0) {
    header('Location: index.php');
    exit;
}

$pinjaman = mysqli_fetch_assoc($q_pinjam);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status_baru = $_POST['status'];
    $denda = (int) $_POST['denda'];

    if ($denda < 0) {
        $pesan = 'Denda tidak boleh negatif.';
        $tipe_pesan = 'danger';
    } else {
        $tgl_pengembalian_aktual = null;

        $status_lama = $pinjaman['status'];

        mysqli_begin_transaction($koneksi);
        try {
            if ($status_baru === 'dikembalikan' && $status_lama !== 'dikembalikan') {
                $tgl_pengembalian_aktual = date('Y-m-d');
                $koneksi->query("UPDATE buku SET stok = stok + 1 WHERE id = " . $pinjaman['id_buku']);
            } elseif ($status_baru !== 'dikembalikan' && $status_lama === 'dikembalikan') {
                // Jika dari dikembalikan diubah lagi ke dipinjam/terlambat (kurangi stok lagi)
                $koneksi->query("UPDATE buku SET stok = stok - 1 WHERE id = " . $pinjaman['id_buku']);
            }

            if ($tgl_pengembalian_aktual !== null) {
                $stmt = mysqli_prepare($koneksi, "UPDATE peminjaman SET status = ?, denda = ?, tanggal_pengembalian_aktual = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, "sisi", $status_baru, $denda, $tgl_pengembalian_aktual, $id);
            } else {
                $stmt = mysqli_prepare($koneksi, "UPDATE peminjaman SET status = ?, denda = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, "sii", $status_baru, $denda, $id);
            }

            mysqli_stmt_execute($stmt);
            mysqli_commit($koneksi);

            $_SESSION['pesan'] = 'Status peminjaman berhasil diubah.';
            $_SESSION['tipe_pesan'] = 'success';
            header('Location: index.php');
            exit;

        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            $pesan = 'Gagal mengubah status: ' . $e->getMessage();
            $tipe_pesan = 'danger';
        }
    }
}

$judul_halaman = 'Ubah Status Peminjaman';
require_once '../../includes/head.php';
require_once '../../includes/sidebar_admin.php';
?>

<div class="card shadow-sm" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Ubah Status Peminjaman</span>
        <a href="index.php" class="btn btn-secondary btn-sm">Kembali</a>
    </div>
    <div class="card-body">
        <?php if ($pesan): ?>
            <div class="alert alert-<?= $tipe_pesan ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($pesan) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="mb-4 p-3 bg-light rounded border">
            <h6 class="mb-3">Informasi Peminjaman</h6>
            <table class="table table-sm table-borderless mb-0">
                <tr>
                    <td width="35%" class="text-muted">Nama Peminjam</td>
                    <td>: <strong><?= htmlspecialchars($pinjaman['nama_lengkap']) ?></strong></td>
                </tr>
                <tr>
                    <td class="text-muted">Judul Buku</td>
                    <td>: <strong><?= htmlspecialchars($pinjaman['judul']) ?></strong></td>
                </tr>
                <tr>
                    <td class="text-muted">Tanggal Pinjam</td>
                    <td>: <?= date('d M Y', strtotime($pinjaman['tanggal_pinjam'])) ?></td>
                </tr>
                <tr>
                    <td class="text-muted">Batas Kembali</td>
                    <td>: <?= date('d M Y', strtotime($pinjaman['tanggal_kembali'])) ?></td>
                </tr>
            </table>
        </div>

        <form action="" method="POST" id="formStatus">
            <div class="mb-3">
                <label for="status" class="form-label">Status Peminjaman</label>
                <select class="form-select" id="status" name="status" required>
                    <option value="dipinjam" <?= $pinjaman['status'] == 'dipinjam' ? 'selected' : '' ?>>Dipinjam</option>
                    <option value="terlambat" <?= $pinjaman['status'] == 'terlambat' ? 'selected' : '' ?>>Terlambat
                    </option>
                    <option value="dikembalikan" <?= $pinjaman['status'] == 'dikembalikan' ? 'selected' : '' ?>>
                        Dikembalikan</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="denda" class="form-label">Denda (Rp)</label>
                <input type="number" class="form-control" id="denda" name="denda" min="0"
                    value="<?= $pinjaman['denda'] ?>" required>
                <div class="form-text">Isi 0 jika tidak ada denda.</div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Simpan Perubahan</button>
        </form>
    </div>
</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('formStatus').addEventListener('submit', function (e) {
        let denda = document.getElementById('denda');
        if (parseInt(denda.value) < 0) {
            denda.classList.add('is-invalid');
            let divError = document.createElement('div');
            divError.className = 'invalid-feedback';
            divError.textContent = 'Denda tidak boleh negatif';
            denda.parentNode.insertBefore(divError, denda.nextSibling);
            e.preventDefault();
        }
    });
</script>
</body>

</html>