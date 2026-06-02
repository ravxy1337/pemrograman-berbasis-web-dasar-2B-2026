<?php
session_start();
require_once '../../config/koneksi.php';
require_once '../../config/auth.php';
cekLogin();
cekRole('admin');

$judul_halaman = 'Manajemen Buku';
require_once '../../includes/head.php';
require_once '../../includes/sidebar_admin.php';
?>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Daftar Buku</span>
        <a href="tambah.php" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Tambah Buku
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
                        <th>Cover</th>
                        <th>Judul</th>
                        <th>Pengarang</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = mysqli_query($koneksi, "SELECT * FROM buku ORDER BY id DESC");
                    $no = 1;
                    if (mysqli_num_rows($query) > 0):
                        while ($row = mysqli_fetch_assoc($query)):
                            $cover = !empty($row['cover_buku']) ? BASE_URL . '/assets/uploads/cover/' . $row['cover_buku'] : BASE_URL . '/assets/uploads/cover/default.jpg';
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><img src="<?= $cover ?>" alt="Cover" style="width: 50px; height: 70px; object-fit: cover;">
                                </td>
                                <td><?= htmlspecialchars($row['judul']) ?></td>
                                <td><?= htmlspecialchars($row['pengarang']) ?></td>
                                <td><?= htmlspecialchars($row['kategori']) ?></td>
                                <td>
                                    <?php if ($row['stok'] > 0): ?>
                                        <span class="badge bg-success"><?= $row['stok'] ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Habis</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger"
                                        onclick="konfirmasiHapus(<?= $row['id'] ?>)" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php
                        endwhile;
                    else:
                        ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Belum ada data buku.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalHapus" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus buku ini?
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