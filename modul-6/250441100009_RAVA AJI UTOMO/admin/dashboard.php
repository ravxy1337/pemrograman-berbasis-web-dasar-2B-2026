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
