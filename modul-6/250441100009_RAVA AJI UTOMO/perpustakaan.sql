-- Database: `perpustakaan`
DROP DATABASE IF EXISTS `perpustakaan`;
CREATE DATABASE `perpustakaan`;
USE `perpustakaan`;

-- --------------------------------------------------------

-- Table structure for table `pengguna`
CREATE TABLE IF NOT EXISTS `pengguna` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `kata_sandi` varchar(255) NOT NULL,
  `peran` enum('admin','user') NOT NULL,
  `no_telp` varchar(15) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `dibuat_pada` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `pengguna`
-- Password for all users is 'password123'
INSERT INTO `pengguna` (`nama_lengkap`, `username`, `email`, `kata_sandi`, `peran`, `no_telp`, `alamat`, `foto_profil`) VALUES
('Administrator', 'admin', 'admin@mail.com', '$2y$12$pQ.mCD1sfkVPZ0CipioDFe8cLrqGCHaZISOvP4zk9mH6IJsnJPLFa', 'admin', '08123456789', 'Jl. Admin No. 1', NULL),
('User Biasa', 'user', 'user@mail.com', '$2y$12$pQ.mCD1sfkVPZ0CipioDFe8cLrqGCHaZISOvP4zk9mH6IJsnJPLFa', 'user', '08987654321', 'Jl. User No. 2', NULL);

-- --------------------------------------------------------

-- Table structure for table `buku`
CREATE TABLE IF NOT EXISTS `buku` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) NOT NULL,
  `pengarang` varchar(100) NOT NULL,
  `penerbit` varchar(100) NOT NULL,
  `tahun_terbit` int(11) NOT NULL,
  `isbn` varchar(13) DEFAULT NULL,
  `kategori` varchar(50) NOT NULL,
  `stok` int(11) DEFAULT 0,
  `deskripsi` text DEFAULT NULL,
  `cover_buku` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `isbn` (`isbn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Table structure for table `peminjaman`
CREATE TABLE IF NOT EXISTS `peminjaman` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_pengguna` int(11) NOT NULL,
  `id_buku` int(11) NOT NULL,
  `tanggal_pinjam` date NOT NULL,
  `tanggal_kembali` date NOT NULL,
  `tanggal_pengembalian_aktual` date DEFAULT NULL,
  `denda` decimal(10,2) DEFAULT 0.00,
  `status` enum('dipinjam','dikembalikan','terlambat') DEFAULT 'dipinjam',
  PRIMARY KEY (`id`),
  KEY `id_pengguna` (`id_pengguna`),
  KEY `id_buku` (`id_buku`),
  CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE,
  CONSTRAINT `peminjaman_ibfk_2` FOREIGN KEY (`id_buku`) REFERENCES `buku` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
