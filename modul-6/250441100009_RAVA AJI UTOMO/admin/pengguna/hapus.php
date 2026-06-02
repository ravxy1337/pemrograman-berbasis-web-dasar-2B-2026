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
