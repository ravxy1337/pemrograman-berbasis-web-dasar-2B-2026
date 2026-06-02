<?php
session_start();
require_once '../../config/koneksi.php';
require_once '../../config/auth.php';
cekLogin();
cekRole('admin');

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    $q_cover = mysqli_query($koneksi, "SELECT cover_buku FROM buku WHERE id = $id");
    if (mysqli_num_rows($q_cover) > 0) {
        $cover = mysqli_fetch_assoc($q_cover)['cover_buku'];

        $stmt = mysqli_prepare($koneksi, "DELETE FROM buku WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            // Hapus file cover jika ada
            if (!empty($cover) && file_exists('../../assets/uploads/cover/' . $cover)) {
                unlink('../../assets/uploads/cover/' . $cover);
            }
            $_SESSION['pesan'] = 'Buku berhasil dihapus.';
            $_SESSION['tipe_pesan'] = 'success';
        } else {
            $_SESSION['pesan'] = 'Gagal menghapus buku: ' . mysqli_error($koneksi);
            $_SESSION['tipe_pesan'] = 'danger';
        }
        mysqli_stmt_close($stmt);
    }
}

header('Location: index.php');
exit;
?>