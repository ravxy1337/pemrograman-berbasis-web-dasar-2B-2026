<?php
session_start();
require_once '../../config/koneksi.php';
require_once '../../config/auth.php';
cekLogin();
cekRole('user');

$judul_halaman = 'Riwayat Peminjaman';
require_once '../../includes/head.php';
require_once '../../includes/sidebar_user.php';

$id_user = $_SESSION['id'];

$koneksi->query("UPDATE peminjaman SET status = 'terlambat' WHERE id_pengguna = $id_user AND status = 'dipinjam' AND tanggal_kembali < CURDATE()");
?>

<div class="card shadow-sm">
    <div class="card-header">
        Riwayat Peminjaman Anda
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
                        <th>Judul Buku</th>
                        <th>Tgl Pinjam</th>
                        <th>Batas Kembali</th>
                        <th>Tgl Dikembalikan</th>
                        <th>Denda</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = mysqli_query($koneksi, "
                        SELECT p.*, b.judul 
                        FROM peminjaman p 
                        JOIN buku b ON p.id_buku = b.id 
                        WHERE p.id_pengguna = $id_user
                        ORDER BY p.id DESC
                    ");
                    $no = 1;
                    if (mysqli_num_rows($query) > 0):
                        while ($row = mysqli_fetch_assoc($query)):
                            $badge_class = 'bg-warning text-dark';
                            if ($row['status'] == 'dikembalikan')
                                $badge_class = 'bg-success';
                            elseif ($row['status'] == 'terlambat')
                                $badge_class = 'bg-danger';

                            $tgl_kembali_aktual = $row['tanggal_pengembalian_aktual'] ? date('d M Y', strtotime($row['tanggal_pengembalian_aktual'])) : '-';
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($row['judul']) ?></td>
                                <td><?= date('d M Y', strtotime($row['tanggal_pinjam'])) ?></td>
                                <td><?= date('d M Y', strtotime($row['tanggal_kembali'])) ?></td>
                                <td><?= $tgl_kembali_aktual ?></td>
                                <td>Rp <?= number_format($row['denda'], 0, ',', '.') ?></td>
                                <td><span class="badge <?= $badge_class ?>"><?= ucfirst($row['status']) ?></span></td>
                            </tr>
                        <?php
                        endwhile;
                    else:
                        ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Belum ada riwayat peminjaman.</td>
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


<!-- pinjam.php -->
 