<?php
session_start();
require_once '../config/koneksi.php';
require_once '../config/auth.php';
cekLogin();
cekRole('user');

$judul_halaman = 'Dashboard Pengguna';
$id_user = $_SESSION['id'];

// Statistik User
$q_aktif = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE id_pengguna = $id_user AND status = 'dipinjam'");
$pinjaman_aktif = mysqli_fetch_assoc($q_aktif)['total'];

$q_selesai = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE id_pengguna = $id_user AND status = 'dikembalikan'");
$pinjaman_selesai = mysqli_fetch_assoc($q_selesai)['total'];

$q_denda = mysqli_query($koneksi, "SELECT SUM(denda) as total FROM peminjaman WHERE id_pengguna = $id_user");
$total_denda = mysqli_fetch_assoc($q_denda)['total'] ?? 0;

require_once '../includes/head.php';
require_once '../includes/sidebar_user.php';
?>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><?= $pinjaman_aktif ?></h3>
                    <p class="mb-0">Buku Sedang Dipinjam</p>
                </div>
                <i class="bi bi-book-half fs-1 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0"><?= $pinjaman_selesai ?></h3>
                    <p class="mb-0">Peminjaman Selesai</p>
                </div>
                <i class="bi bi-check-circle fs-1 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger text-white shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-0">Rp <?= number_format($total_denda, 0, ',', '.') ?></h3>
                    <p class="mb-0">Total Denda</p>
                </div>
                <i class="bi bi-cash-coin fs-1 opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        Pinjaman Aktif Anda
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Judul Buku</th>
                        <th>Tanggal Pinjam</th>
                        <th>Batas Kembali</th>
                        <th>Sisa Hari</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $q_pinjaman = mysqli_query($koneksi, "
                        SELECT b.judul, p.tanggal_pinjam, p.tanggal_kembali 
                        FROM peminjaman p 
                        JOIN buku b ON p.id_buku = b.id 
                        WHERE p.id_pengguna = $id_user AND p.status = 'dipinjam'
                        ORDER BY p.tanggal_kembali ASC
                    ");
                    
                    if (mysqli_num_rows($q_pinjaman) > 0):
                        while ($row = mysqli_fetch_assoc($q_pinjaman)):
                            $tgl_kembali = strtotime($row['tanggal_kembali']);
                            $sekarang = strtotime(date('Y-m-d'));
                            $sisa_hari = ($tgl_kembali - $sekarang) / (60 * 60 * 24);
                            
                            $badge_class = 'bg-success';
                            if ($sisa_hari < 0) {
                                $badge_class = 'bg-danger';
                                $sisa_hari = 'Terlambat ' . abs($sisa_hari) . ' hari';
                            } elseif ($sisa_hari <= 1) {
                                $badge_class = 'bg-warning text-dark';
                                $sisa_hari = $sisa_hari == 0 ? 'Hari ini' : $sisa_hari . ' hari lagi';
                            } else {
                                $sisa_hari = $sisa_hari . ' hari lagi';
                            }
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['judul']) ?></td>
                        <td><?= date('d M Y', strtotime($row['tanggal_pinjam'])) ?></td>
                        <td><?= date('d M Y', strtotime($row['tanggal_kembali'])) ?></td>
                        <td><span class="badge <?= $badge_class ?>"><?= $sisa_hari ?></span></td>
                    </tr>
                    <?php 
                        endwhile;
                    else:
                    ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">Anda tidak memiliki pinjaman aktif saat ini.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-3 text-end">
            <a href="<?= BASE_URL ?>/user/katalog.php" class="btn btn-primary">Cari Buku untuk Dipinjam</a>
        </div>
    </div>
</div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
