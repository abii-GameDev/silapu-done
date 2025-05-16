-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table silapu_db.berita_kegiatan
DROP TABLE IF EXISTS `berita_kegiatan`;
CREATE TABLE IF NOT EXISTS `berita_kegiatan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `konten` text NOT NULL,
  `gambar_banner` varchar(255) DEFAULT NULL,
  `penulis_id` int DEFAULT NULL,
  `status` enum('published','draft','archived') NOT NULL DEFAULT 'draft',
  `tanggal_publikasi` datetime DEFAULT NULL,
  `tanggal_dibuat` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `tanggal_diupdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `penulis_id` (`penulis_id`),
  CONSTRAINT `berita_kegiatan_ibfk_1` FOREIGN KEY (`penulis_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table silapu_db.berita_kegiatan: ~3 rows (approximately)
REPLACE INTO `berita_kegiatan` (`id`, `judul`, `slug`, `konten`, `gambar_banner`, `penulis_id`, `status`, `tanggal_publikasi`, `tanggal_dibuat`, `tanggal_diupdate`) VALUES
	(1, 'test', 'test', 'testt', 'uploads/berita_banner/test-6826dc7c6fc1c.jpg', 2, 'published', '2025-05-16 06:40:00', '2025-05-16 06:34:36', '2025-05-16 06:41:09'),
	(3, 'sample2', 'test2', 'test', 'uploads/berita_banner/test2-6826de1c947f3.jpg', 2, 'published', '2025-05-16 06:41:32', '2025-05-16 06:41:32', '2025-05-16 06:41:32'),
	(4, 'sample3', 'test3', 'test', 'uploads/berita_banner/test3-6826de36cb223.jpg', 2, 'published', '2025-05-16 06:41:58', '2025-05-16 06:41:58', '2025-05-16 06:41:58');

-- Dumping structure for table silapu_db.books
DROP TABLE IF EXISTS `books`;
CREATE TABLE IF NOT EXISTS `books` (
  `id` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `author` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `publisher` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `year` int DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cover_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table silapu_db.books: ~5 rows (approximately)
REPLACE INTO `books` (`id`, `title`, `author`, `publisher`, `year`, `description`, `cover_image`) VALUES
	('B1', 'Matematika Dasar', 'Budi Santoso', 'Penerbit Ilmu Cendekia', 2022, 'Buku ini membahas dasar-dasar matematika seperti aljabar, geometri, dan aritmetika dengan pendekatan kontekstual.', 'https://via.placeholder.com/200x300?text=Matematika+Dasar'),
	('B2', 'Bahasa Indonesia', 'Sari Dewi', 'Penerbit Bahasa', 2021, 'Buku ini membahas tata bahasa dan sastra Indonesia secara lengkap.', 'https://via.placeholder.com/200x300?text=Bahasa+Indonesia'),
	('B3', 'Ilmu Pengetahuan Alam', 'Agus Wijaya', 'Penerbit Sains', 2020, 'Buku ini membahas konsep dasar ilmu pengetahuan alam untuk pelajar.', 'https://via.placeholder.com/200x300?text=Ilmu+Pengetahuan+Alam'),
	('B4', 'Sejarah Indonesia', 'Rina Marlina', 'Penerbit Sejarah', 2019, 'Buku ini mengulas sejarah Indonesia dari masa ke masa.', 'https://via.placeholder.com/200x300?text=Sejarah+Indonesia'),
	('B5', 'Fisika Dasar', 'Andi Wijaya', 'Penerbit Fisika', 2022, 'Buku ini membahas konsep dasar fisika dengan contoh aplikasi.', 'https://via.placeholder.com/200x300?text=Fisika+Dasar');

-- Dumping structure for table silapu_db.data_anggota
DROP TABLE IF EXISTS `data_anggota`;
CREATE TABLE IF NOT EXISTS `data_anggota` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `nama_lengkap` varchar(255) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `semester` varchar(10) NOT NULL,
  `program_studi` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `nomor_hp` varchar(20) DEFAULT NULL,
  `alasan_bergabung` text,
  `tanggal_pendaftaran` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status_keanggotaan` varchar(50) DEFAULT 'Menunggu Konfirmasi',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nim` (`nim`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_anggota_user` (`user_id`),
  CONSTRAINT `fk_anggota_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table silapu_db.data_anggota: ~0 rows (approximately)

-- Dumping structure for table silapu_db.detail_pesanan
DROP TABLE IF EXISTS `detail_pesanan`;
CREATE TABLE IF NOT EXISTS `detail_pesanan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pesanan_id` int NOT NULL,
  `produk_id` int DEFAULT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `harga_produk` decimal(10,2) NOT NULL,
  `jumlah` int NOT NULL,
  `subtotal_item` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pesanan_id` (`pesanan_id`),
  KEY `produk_id` (`produk_id`),
  CONSTRAINT `detail_pesanan_ibfk_1` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detail_pesanan_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk_layanan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table silapu_db.detail_pesanan: ~0 rows (approximately)

-- Dumping structure for table silapu_db.pesanan
DROP TABLE IF EXISTS `pesanan`;
CREATE TABLE IF NOT EXISTS `pesanan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `nomor_pesanan` varchar(50) NOT NULL,
  `total_harga` decimal(12,2) NOT NULL,
  `status_pesanan` varchar(50) NOT NULL DEFAULT 'Menunggu Pembayaran',
  `metode_pembayaran` varchar(100) DEFAULT NULL,
  `alamat_pengiriman` text,
  `nama_penerima` varchar(255) DEFAULT NULL,
  `telepon_penerima` varchar(20) DEFAULT NULL,
  `catatan_pembeli` text,
  `tanggal_pesanan` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `tanggal_update` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_pesanan` (`nomor_pesanan`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table silapu_db.pesanan: ~0 rows (approximately)

-- Dumping structure for table silapu_db.produk_layanan
DROP TABLE IF EXISTS `produk_layanan`;
CREATE TABLE IF NOT EXISTS `produk_layanan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usaha_id` int NOT NULL,
  `nama_produk_layanan` varchar(255) NOT NULL,
  `deskripsi_produk_layanan` text,
  `harga` decimal(10,2) NOT NULL,
  `satuan` varchar(50) DEFAULT 'pcs',
  `stok` int DEFAULT NULL,
  `foto_produk_layanan` varchar(255) DEFAULT NULL,
  `kategori_produk_layanan` varchar(100) DEFAULT NULL,
  `is_tersedia` tinyint(1) DEFAULT '1',
  `tanggal_ditambahkan` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `tanggal_diupdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usaha_id` (`usaha_id`),
  CONSTRAINT `produk_layanan_ibfk_1` FOREIGN KEY (`usaha_id`) REFERENCES `usaha_mahasiswa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table silapu_db.produk_layanan: ~0 rows (approximately)

-- Dumping structure for table silapu_db.usaha_mahasiswa
DROP TABLE IF EXISTS `usaha_mahasiswa`;
CREATE TABLE IF NOT EXISTS `usaha_mahasiswa` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `nama_usaha` varchar(255) NOT NULL,
  `deskripsi_usaha` text,
  `kategori_usaha` varchar(100) DEFAULT NULL,
  `alamat_usaha` varchar(255) DEFAULT NULL,
  `kontak_usaha` varchar(100) DEFAULT NULL,
  `foto_produk_atau_logo` varchar(255) DEFAULT NULL,
  `status_pengajuan` varchar(50) NOT NULL DEFAULT 'Menunggu Persetujuan',
  `catatan_admin` text,
  `tanggal_pengajuan` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `tanggal_update` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `usaha_mahasiswa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table silapu_db.usaha_mahasiswa: ~0 rows (approximately)

-- Dumping structure for table silapu_db.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table silapu_db.users: ~5 rows (approximately)
REPLACE INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
	(1, 'fredli', 'fredlifourqoni35@gmail.com', '$2y$10$XKJ7JW1hvlruXEhNq8KGdO4hipHhcSXhYkmLw0qtsjTW0b/aL0psO', 'user', '2025-05-10 10:55:31'),
	(2, 'Admin', 'adminsilapu@gmail.com', '$2y$10$0nRf.5o/uVx2QrMWsShF0uBNbBhcySy0dV/iudvOqErm4kXOHUKDC', 'admin', '2025-05-10 20:59:24'),
	(3, 'hafiz zanki', 'hafiz@gmail.com', '$2y$10$/ZMBYaO29x5bzC/2X5ORreNUNNOU7v.VYldrpU85fDmC3q4UBIunK', 'user', '2025-05-10 22:24:03'),
	(4, 'Habib', 'habib@gmail.com', '$2y$10$Jmt69Fejb88t8u9zV49Mn.0Vzi6O6fqgri3uQe/OHIAPbO3D6QZYK', 'user', '2025-05-11 10:01:08'),
	(5, 'mbappis', 'mbappis@gmail.com', '$2y$10$NCDq38zvjoBZNiqjakQ0FuL5jY3Pw9jeUSJHlmcB408GH6actcRWG', 'user', '2025-05-13 04:43:40');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
