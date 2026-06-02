<?php
session_start();
require_once '../config/koneksi.php';
require_once '../config/auth.php';
cekLogin();
cekRole('user');

$judul_halaman = 'Katalog Buku';
require_once '../includes/head.php';
require_once '../includes/sidebar_user.php';

$kat_filter = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';
$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';

$where_clause = "WHERE 1=1";
if (!empty($kat_filter)) {
    $where_clause .= " AND kategori = '" . mysqli_real_escape_string($koneksi, $kat_filter) . "'";
}
if (!empty($cari)) {
    $cari_esc = mysqli_real_escape_string($koneksi, $cari);
    $where_clause .= " AND (judul LIKE '%$cari_esc%' OR pengarang LIKE '%$cari_esc%')";
}

$limit = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$q_total = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM buku $where_clause");
$total_data = mysqli_fetch_assoc($q_total)['total'];
$total_page = ceil($total_data / $limit);

$query = "SELECT * FROM buku $where_clause ORDER BY id DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($koneksi, $query);
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="" method="GET" class="row g-3 align-items-center">
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="cari" placeholder="Cari judul atau pengarang..." value="<?= htmlspecialchars($cari) ?>">
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" name="kategori">
                            <option value="">Semua Kategori</option>
                            <?php
                            $kategori_list = ['Fiksi', 'Non-Fiksi', 'Sains', 'Sejarah', 'Teknologi', 'Sastra', 'Agama', 'Lainnya'];
                            foreach ($kategori_list as $kat) {
                                $selected = ($kat_filter == $kat) ? 'selected' : '';
                                echo "<option value=\"$kat\" $selected>$kat</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Cari & Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($buku = mysqli_fetch_assoc($result)): 
            $cover = !empty($buku['cover_buku']) ? BASE_URL . '/assets/uploads/cover/' . $buku['cover_buku'] : BASE_URL . '/assets/uploads/cover/default.jpg';
        ?>
        <div class="col-6 col-md-3 col-lg-2">
            <div class="card book-card h-100 shadow-sm border-0">
                <img src="<?= $cover ?>" class="card-img-top book-cover" alt="Cover Buku">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title text-truncate" title="<?= htmlspecialchars($buku['judul']) ?>">
                        <?= htmlspecialchars($buku['judul']) ?>
                    </h5>
                    <p class="card-text text-muted small mb-2 text-truncate">Oleh: <?= htmlspecialchars($buku['pengarang']) ?></p>
                    <div class="mb-3">
                        <span class="badge bg-info text-dark"><?= $buku['kategori'] ?></span>
                        <?php if ($buku['stok'] > 0): ?>
                            <span class="badge bg-success">Stok: <?= $buku['stok'] ?></span>
                        <?php else: ?>
                            <span class="badge bg-danger">Stok Habis</span>
                        <?php endif; ?>
                    </div>
                    <p class="card-text small flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                        <?= htmlspecialchars((string)$buku['deskripsi']) ?>
                    </p>
                    <div class="mt-auto pt-3 border-top text-center">
                        <?php if ($buku['stok'] > 0): ?>
                            <a href="peminjaman/pinjam.php?id_buku=<?= $buku['id'] ?>" class="btn btn-primary w-100">Pinjam Buku</a>
                        <?php else: ?>
                            <button class="btn btn-secondary w-100" disabled>Tidak Tersedia</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12 text-center py-5">
            <div class="text-muted">
                <i class="bi bi-journal-x" style="font-size: 3rem;"></i>
                <h5 class="mt-3">Buku tidak ditemukan</h5>
                <p>Coba gunakan kata kunci atau kategori lain.</p>
                <?php if (!empty($kat_filter) || !empty($cari)): ?>
                    <a href="katalog.php" class="btn btn-outline-primary mt-2">Reset Pencarian</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if ($total_page > 1): ?>
<div class="row mt-5">
    <div class="col-12 d-flex justify-content-center">
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page - 1 ?>&kategori=<?= urlencode($kat_filter) ?>&cari=<?= urlencode($cari) ?>">Sebelumnya</a>
                </li>
                <?php for ($i = 1; $i <= $total_page; $i++): ?>
                    <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&kategori=<?= urlencode($kat_filter) ?>&cari=<?= urlencode($cari) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $total_page) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $page + 1 ?>&kategori=<?= urlencode($kat_filter) ?>&cari=<?= urlencode($cari) ?>">Selanjutnya</a>
                </li>
            </ul>
        </nav>
    </div>
</div>
<?php endif; ?>

    </div> 
</div> 

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
