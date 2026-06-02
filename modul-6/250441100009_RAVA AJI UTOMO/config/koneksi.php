<?php
ob_start(); 
$host = 'localhost';
$pengguna_db = 'root';
$kata_sandi_db = '';
$nama_db = 'perpustakaan';

$koneksi = mysqli_connect($host, $pengguna_db, $kata_sandi_db, $nama_db);

if (!$koneksi) {
    die('Koneksi gagal: ' . mysqli_connect_error());
}

mysqli_set_charset($koneksi, 'utf8mb4');
define('BASE_URL', 'http://localhost/modul6');
?>
