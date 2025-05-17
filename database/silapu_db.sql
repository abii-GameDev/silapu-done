-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 17, 2025 at 09:05 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `silapu_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `berita_kegiatan`
--

CREATE TABLE `berita_kegiatan` (
  `id` int NOT NULL,
  `judul` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `konten` text NOT NULL,
  `gambar_banner` varchar(255) DEFAULT NULL,
  `penulis_id` int DEFAULT NULL,
  `status` enum('published','draft','archived') NOT NULL DEFAULT 'draft',
  `tanggal_publikasi` datetime DEFAULT NULL,
  `tanggal_dibuat` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `tanggal_diupdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `berita_kegiatan`
--

INSERT INTO `berita_kegiatan` (`id`, `judul`, `slug`, `konten`, `gambar_banner`, `penulis_id`, `status`, `tanggal_publikasi`, `tanggal_dibuat`, `tanggal_diupdate`) VALUES
(1, 'pelatihan website', 'halo-coopers-1', 'hari ini koperasi mahasiswa uin raden intan lampung lagi ngadain pelatihan website loh !', 'uploads/berita_banner/test-6826dc7c6fc1c.jpg', 2, 'published', '2025-05-16 06:40:00', '2025-05-16 06:34:36', '2025-05-16 17:52:31'),
(3, 'anniversary kopma', 'halo-coopers-3', 'hari ini koperasi mahasiswa uin raden intan lampung mengadakan anniverasry nya yang ke 33 tahun, semoga lancar terus kedepannya !!!', 'uploads/berita_banner/test2-6826de1c947f3.jpg', 2, 'published', '2025-05-16 06:41:00', '2025-05-16 06:41:32', '2025-05-16 17:51:21'),
(4, 'pelatihan kewirausahaan', 'halo-coopers', 'pada hari ini kopma mengadakan pelatihan kewirausahaan yang dilaksanakan di sekretariat kopma bersama bidang usaha.', 'uploads/berita_banner/test3-6826de36cb223.jpg', 2, 'published', '2025-05-16 06:41:00', '2025-05-16 06:41:58', '2025-05-16 17:50:12');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `author` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `publisher` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `year` int DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cover_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `publisher`, `year`, `description`, `cover_image`) VALUES
('B1', 'Matematika Dasar', 'Budi Santoso', 'Penerbit Ilmu Cendekia', 2022, 'Buku ini membahas dasar-dasar matematika seperti aljabar, geometri, dan aritmetika dengan pendekatan kontekstual.', 'https://via.placeholder.com/200x300?text=Matematika+Dasar'),
('B2', 'Bahasa Indonesia', 'Sari Dewi', 'Penerbit Bahasa', 2021, 'Buku ini membahas tata bahasa dan sastra Indonesia secara lengkap.', 'https://via.placeholder.com/200x300?text=Bahasa+Indonesia'),
('B3', 'Ilmu Pengetahuan Alam', 'Agus Wijaya', 'Penerbit Sains', 2020, 'Buku ini membahas konsep dasar ilmu pengetahuan alam untuk pelajar.', 'https://via.placeholder.com/200x300?text=Ilmu+Pengetahuan+Alam'),
('B4', 'Sejarah Indonesia', 'Rina Marlina', 'Penerbit Sejarah', 2019, 'Buku ini mengulas sejarah Indonesia dari masa ke masa.', 'https://via.placeholder.com/200x300?text=Sejarah+Indonesia'),
('B5', 'Fisika Dasar', 'Andi Wijaya', 'Penerbit Fisika', 2022, 'Buku ini membahas konsep dasar fisika dengan contoh aplikasi.', 'https://via.placeholder.com/200x300?text=Fisika+Dasar');

-- --------------------------------------------------------

--
-- Table structure for table `data_anggota`
--

CREATE TABLE `data_anggota` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `nama_lengkap` varchar(255) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `semester` varchar(10) NOT NULL,
  `program_studi` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `nomor_hp` varchar(20) DEFAULT NULL,
  `alasan_bergabung` text,
  `tanggal_pendaftaran` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status_keanggotaan` varchar(50) DEFAULT 'Menunggu Konfirmasi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `data_anggota`
--

INSERT INTO `data_anggota` (`id`, `user_id`, `nama_lengkap`, `nim`, `semester`, `program_studi`, `email`, `nomor_hp`, `alasan_bergabung`, `tanggal_pendaftaran`, `status_keanggotaan`) VALUES
(5, 6, 'eci', '2211050117', '6', 'mtk', 'eci15@gmail.com', '0812121212', 'mau jualan kaks', '2025-05-16 08:59:14', 'Aktif');

-- --------------------------------------------------------

--
-- Table structure for table `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id` int NOT NULL,
  `pesanan_id` int NOT NULL,
  `produk_id` int DEFAULT NULL,
  `nama_produk` varchar(255) NOT NULL,
  `harga_produk` decimal(10,2) NOT NULL,
  `jumlah` int NOT NULL,
  `subtotal_item` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id`, `pesanan_id`, `produk_id`, `nama_produk`, `harga_produk`, `jumlah`, `subtotal_item`) VALUES
(4, 4, 6, 'jasa jemput karpet terbang', 10000.00, 1, 10000.00),
(5, 5, 6, 'jasa jemput karpet terbang', 10000.00, 1, 10000.00),
(6, 6, 6, 'jasa jemput karpet terbang', 10000.00, 2, 20000.00),
(7, 7, 6, 'jasa jemput karpet terbang', 10000.00, 1, 10000.00);

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id` int NOT NULL,
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
  `tanggal_update` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id`, `user_id`, `nomor_pesanan`, `total_harga`, `status_pesanan`, `metode_pembayaran`, `alamat_pengiriman`, `nama_penerima`, `telepon_penerima`, `catatan_pembeli`, `tanggal_pesanan`, `tanggal_update`) VALUES
(4, 6, 'INV-20250516-4851', 10000.00, 'Menunggu Pembayaran', 'COD', 'ke surga', 'eci', '0812121212', 'jangan lama ya kak', '2025-05-16 09:05:42', '2025-05-16 09:05:42'),
(5, 6, 'INV-20250516-105B', 10000.00, 'Menunggu Pembayaran', 'E-Wallet', 'ke surga', 'eci', '0812121212', 'mantap min', '2025-05-16 09:06:52', '2025-05-16 09:06:52'),
(6, 6, 'INV-20250516-DA3D', 20000.00, 'Menunggu Pembayaran', 'Transfer Bank', 'ke surga', 'eci', '0812121212', 'gass', '2025-05-16 09:08:52', '2025-05-16 09:08:52'),
(7, 6, 'INV-20250516-BC37', 10000.00, 'Menunggu Pembayaran', 'COD', 'ke surga', 'eci', '0812121212', 'banyakin kak', '2025-05-16 17:44:53', '2025-05-16 17:44:53');

-- --------------------------------------------------------

--
-- Table structure for table `produk_layanan`
--

CREATE TABLE `produk_layanan` (
  `id` int NOT NULL,
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
  `tanggal_diupdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `produk_layanan`
--

INSERT INTO `produk_layanan` (`id`, `usaha_id`, `nama_produk_layanan`, `deskripsi_produk_layanan`, `harga`, `satuan`, `stok`, `foto_produk_layanan`, `kategori_produk_layanan`, `is_tersedia`, `tanggal_ditambahkan`, `tanggal_diupdate`) VALUES
(6, 6, 'jasa jemput karpet terbang', 'mau kemana mana ga ribet? sung aja co karpet terbang', 10000.00, 'pcs', NULL, 'uploads/produk_layanan/produk_6826ff8c1288b0.64394091.jpg', 'jasa', 1, '2025-05-16 09:04:12', '2025-05-16 09:04:12');

-- --------------------------------------------------------

--
-- Table structure for table `usaha_mahasiswa`
--

CREATE TABLE `usaha_mahasiswa` (
  `id` int NOT NULL,
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
  `tanggal_update` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `usaha_mahasiswa`
--

INSERT INTO `usaha_mahasiswa` (`id`, `user_id`, `nama_usaha`, `deskripsi_usaha`, `kategori_usaha`, `alamat_usaha`, `kontak_usaha`, `foto_produk_atau_logo`, `status_pengajuan`, `catatan_admin`, `tanggal_pengajuan`, `tanggal_update`) VALUES
(6, 6, 'karpet terbang', 'dengan karpet terbang perjalanan jadi lebih menyenangkan', 'Jasa', 'belakang uin', '0812121212', 'uploads/usaha_mahasiswa/usaha_6826fedd7de339.99781210.jpg', 'Disetujui', NULL, '2025-05-16 09:01:17', '2025-05-16 09:01:49');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(2, 'Admin', 'adminsilapu@gmail.com', '$2y$10$0nRf.5o/uVx2QrMWsShF0uBNbBhcySy0dV/iudvOqErm4kXOHUKDC', 'admin', '2025-05-10 20:59:24'),
(3, 'hafiz zanki', 'hafiz@gmail.com', '$2y$10$/ZMBYaO29x5bzC/2X5ORreNUNNOU7v.VYldrpU85fDmC3q4UBIunK', 'user', '2025-05-10 22:24:03'),
(4, 'Habib', 'habib@gmail.com', '$2y$10$Jmt69Fejb88t8u9zV49Mn.0Vzi6O6fqgri3uQe/OHIAPbO3D6QZYK', 'user', '2025-05-11 10:01:08'),
(5, 'mbappis', 'mbappis@gmail.com', '$2y$10$NCDq38zvjoBZNiqjakQ0FuL5jY3Pw9jeUSJHlmcB408GH6actcRWG', 'user', '2025-05-13 04:43:40'),
(6, 'eci', 'eci15@gmail.com', '$2y$10$djlGy1iKkqnMb6oGOG1V3eP85TFF.9sQfuh3mmdiw2fcXGEEbOB2m', 'user', '2025-05-16 08:58:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `berita_kegiatan`
--
ALTER TABLE `berita_kegiatan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `penulis_id` (`penulis_id`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `data_anggota`
--
ALTER TABLE `data_anggota`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nim` (`nim`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_anggota_user` (`user_id`);

--
-- Indexes for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pesanan_id` (`pesanan_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_pesanan` (`nomor_pesanan`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `produk_layanan`
--
ALTER TABLE `produk_layanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usaha_id` (`usaha_id`);

--
-- Indexes for table `usaha_mahasiswa`
--
ALTER TABLE `usaha_mahasiswa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `berita_kegiatan`
--
ALTER TABLE `berita_kegiatan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `data_anggota`
--
ALTER TABLE `data_anggota`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `produk_layanan`
--
ALTER TABLE `produk_layanan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `usaha_mahasiswa`
--
ALTER TABLE `usaha_mahasiswa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `berita_kegiatan`
--
ALTER TABLE `berita_kegiatan`
  ADD CONSTRAINT `berita_kegiatan_ibfk_1` FOREIGN KEY (`penulis_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `data_anggota`
--
ALTER TABLE `data_anggota`
  ADD CONSTRAINT `fk_anggota_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `detail_pesanan_ibfk_1` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_pesanan_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk_layanan` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `produk_layanan`
--
ALTER TABLE `produk_layanan`
  ADD CONSTRAINT `produk_layanan_ibfk_1` FOREIGN KEY (`usaha_id`) REFERENCES `usaha_mahasiswa` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `usaha_mahasiswa`
--
ALTER TABLE `usaha_mahasiswa`
  ADD CONSTRAINT `usaha_mahasiswa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
