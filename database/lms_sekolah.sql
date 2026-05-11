-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 11, 2026 at 12:26 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lms_sekolah`
--

-- --------------------------------------------------------

--
-- Table structure for table `forum_balasan`
--

CREATE TABLE `forum_balasan` (
  `id_balasan` int(11) NOT NULL,
  `id_topik` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `id_user` int(11) NOT NULL,
  `isi_balasan` text NOT NULL,
  `tgl_balas` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forum_balasan`
--

INSERT INTO `forum_balasan` (`id_balasan`, `id_topik`, `parent_id`, `id_user`, `isi_balasan`, `tgl_balas`) VALUES
(1, 1, NULL, 10, 'woyyy', '2026-04-19 09:23:34'),
(2, 1, NULL, 10, 'nnnnnn', '2026-04-19 09:39:37'),
(3, 1, NULL, 10, 'haiii', '2026-04-19 09:43:43'),
(4, 1, NULL, 10, '<p><img src=\"data:image/png;base64,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AAAAwISwCAAAAAAwISwCAAAAAEwIiwAAAAAAE8IiAAAAAMCEsAgAAAAAMCEsAgAAAABMCIsAAAAAABPCIgAAAADAhLAIAAAAADAhLAIAAAAATAiLAAAAAAATwiIAAAAAwISwCAAAAAAwISwCAAAAAEwIiwAAAAAAE8IiAAAAAMCEsAgAAAAAMCEsAgAAAABMCIsAAAAAABPCIgAAAADAhLAIAAAAADAhLAIAAAAATAiLAAAAAAATwiIAAAAAwISwCAAAAAAwISwCAAAAAEwIiwAAAAAAE8IiAAAAAMCEsAgAAAAAMCEsAgAAAABMCIsAAAAAABPCIgAAAADAhLAIAAAAADAhLAIAAAAATAiLAAAAAAATwiIAAAAAwISwCAAAAAAwISwCAAAAAEwIiwAAAAAAE8IiAAAAAMCEsAgAAAAAMCEsAgAAAABMCIsAAAAAABPCIgAAAADAhLAIAAAAADAhLAIAAAAATAiLAAAAAAATwiIAAAAAwISwCAAAAAAwISwCAAAAAEwIiwAAAAAAE8IiAAAAAMCEsAgAAAAAMCEsAgAAAABMCIsAAAAAABPCIgAAAADAhLAIAAAAADAhLAIAAAAATAiLAAAAAAATwiIAAAAAwISwCAAAAAAwISwCAAAAAEwIiwAAAAAAE8IiAAAAAMCEsAgAAAAAMCEsAgAAAABMCIsAAAAAABPCIgAAAADAhLAIAAAAADD5f1hW53nvClNFAAAAAElFTkSuQmCC\" width=\"371\" height=\"148\"></p>', '2026-04-19 11:27:24'),
(5, 1, 4, 10, 'p', '2026-04-19 11:27:35'),
(6, 1, 4, 4, 'pak', '2026-04-19 16:15:47');

-- --------------------------------------------------------

--
-- Table structure for table `forum_topik`
--

CREATE TABLE `forum_topik` (
  `id_topik` int(11) NOT NULL,
  `judul_topik` varchar(200) NOT NULL,
  `deskripsi` text NOT NULL,
  `id_kelas` int(11) NOT NULL,
  `id_mapel` int(11) NOT NULL,
  `id_guru` int(11) NOT NULL,
  `tgl_buat` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forum_topik`
--

INSERT INTO `forum_topik` (`id_topik`, `judul_topik`, `deskripsi`, `id_kelas`, `id_mapel`, `id_guru`, `tgl_buat`) VALUES
(1, 'Pertemuan 1', 'silahkan berdiskusi di forum ini', 2, 4, 10, '2026-04-19 09:19:01');

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE `kelas` (
  `id_kelas` int(11) NOT NULL,
  `nama_kelas` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kelas`
--

INSERT INTO `kelas` (`id_kelas`, `nama_kelas`) VALUES
(1, 'X-IPA1'),
(2, 'XI-IPA2'),
(5, 'XII-IPA1');

-- --------------------------------------------------------

--
-- Table structure for table `mapel`
--

CREATE TABLE `mapel` (
  `id_mapel` int(11) NOT NULL,
  `nama_mapel` varchar(100) NOT NULL,
  `id_kelas` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mapel`
--

INSERT INTO `mapel` (`id_mapel`, `nama_mapel`, `id_kelas`) VALUES
(1, 'Biologi', 2),
(4, 'Bahasa Indonesia', NULL),
(5, 'Matematika Wajib', NULL),
(6, 'Fisika', NULL),
(7, 'Sejarah', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `materi`
--

CREATE TABLE `materi` (
  `id_materi` int(11) NOT NULL,
  `judul_materi` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `file_materi` varchar(255) NOT NULL,
  `id_kelas` int(11) NOT NULL,
  `id_mapel` int(11) NOT NULL,
  `id_guru` int(11) NOT NULL,
  `tgl_upload` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `materi`
--

INSERT INTO `materi` (`id_materi`, `judul_materi`, `deskripsi`, `file_materi`, `id_kelas`, `id_mapel`, `id_guru`, `tgl_upload`) VALUES
(1, 'Materi Pertemuan 1', '', '1776543589_3914-Article_Text-8683-1-10-20240102.pdf', 2, 4, 10, '2026-04-18 20:19:49'),
(2, 'Pertemuan 2', NULL, '', 5, 4, 10, '2026-04-26 15:31:44');

-- --------------------------------------------------------

--
-- Table structure for table `nilai_ujian`
--

CREATE TABLE `nilai_ujian` (
  `id_nilai` int(11) NOT NULL,
  `id_ujian` int(11) NOT NULL,
  `id_siswa` int(11) NOT NULL,
  `waktu_mulai` datetime NOT NULL,
  `waktu_selesai` datetime DEFAULT NULL,
  `nilai_pg` decimal(5,2) DEFAULT 0.00,
  `nilai_essay` decimal(5,2) DEFAULT 0.00,
  `status_koreksi` enum('Otomatis Selesai','Menunggu Koreksi Essay','Koreksi Selesai') DEFAULT 'Otomatis Selesai',
  `nilai` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id_notif` int(11) NOT NULL,
  `judul` varchar(100) NOT NULL,
  `pesan` text NOT NULL,
  `jenis` enum('info','success','warning','system') NOT NULL,
  `status` enum('belum_dibaca','sudah_dibaca') DEFAULT 'belum_dibaca',
  `waktu` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifikasi`
--

INSERT INTO `notifikasi` (`id_notif`, `judul`, `pesan`, `jenis`, `status`, `waktu`) VALUES
(1, 'Sistem Siap', 'Sistem notifikasi real-time berhasil diaktifkan!', 'success', 'belum_dibaca', '2026-04-18 12:38:39');

-- --------------------------------------------------------

--
-- Table structure for table `pengumpulan_tugas`
--

CREATE TABLE `pengumpulan_tugas` (
  `id_pengumpulan` int(11) NOT NULL,
  `id_tugas` int(11) NOT NULL,
  `id_siswa` int(11) NOT NULL,
  `file_siswa` varchar(255) DEFAULT NULL,
  `catatan_siswa` text DEFAULT NULL,
  `tgl_kumpul` datetime DEFAULT current_timestamp(),
  `nilai` int(11) DEFAULT NULL,
  `feedback` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengumpulan_tugas`
--

INSERT INTO `pengumpulan_tugas` (`id_pengumpulan`, `id_tugas`, `id_siswa`, `file_siswa`, `catatan_siswa`, `tgl_kumpul`, `nilai`, `feedback`) VALUES
(3, 1, 4, NULL, '<p>jhhsbhjbhjsx</p>', '2026-04-19 22:26:16', 80, '');

-- --------------------------------------------------------

--
-- Table structure for table `penugasan_guru`
--

CREATE TABLE `penugasan_guru` (
  `id_penugasan` int(11) NOT NULL,
  `id_guru` int(11) NOT NULL,
  `id_kelas` int(11) NOT NULL,
  `id_tahun` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penugasan_guru`
--

INSERT INTO `penugasan_guru` (`id_penugasan`, `id_guru`, `id_kelas`, `id_tahun`) VALUES
(1, 11, 1, '2025/2026'),
(3, 11, 2, '2025/2026'),
(6, 11, 5, '2025/2026'),
(7, 10, 1, '2025/2026'),
(8, 10, 5, '2025/2026');

-- --------------------------------------------------------

--
-- Table structure for table `soal_ujian`
--

CREATE TABLE `soal_ujian` (
  `id_soal` int(11) NOT NULL,
  `id_ujian` int(11) NOT NULL,
  `jenis_soal` enum('Pilihan Ganda','Essay') NOT NULL,
  `pertanyaan` text NOT NULL,
  `opsi_a` text DEFAULT NULL,
  `opsi_b` text DEFAULT NULL,
  `opsi_c` text DEFAULT NULL,
  `opsi_d` text DEFAULT NULL,
  `opsi_e` text DEFAULT NULL,
  `kunci_jawaban` text NOT NULL,
  `bobot` int(11) DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tahun_ajaran`
--

CREATE TABLE `tahun_ajaran` (
  `id_tahun` int(11) NOT NULL,
  `nama_tahun` varchar(20) NOT NULL,
  `semester` enum('Ganjil','Genap') NOT NULL,
  `status` enum('Aktif','Tidak Aktif') DEFAULT 'Tidak Aktif',
  `status_aktif` varchar(1) DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tahun_ajaran`
--

INSERT INTO `tahun_ajaran` (`id_tahun`, `nama_tahun`, `semester`, `status`, `status_aktif`) VALUES
(1, '2025/2026', 'Ganjil', 'Aktif', 'Y'),
(2, '2024/2025', 'Genap', 'Tidak Aktif', 'N'),
(3, '2023/2024', 'Ganjil', 'Tidak Aktif', 'N'),
(4, '2020/2021', 'Genap', 'Tidak Aktif', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tugas`
--

CREATE TABLE `tugas` (
  `id_tugas` int(11) NOT NULL,
  `judul_tugas` varchar(150) NOT NULL,
  `deskripsi` text NOT NULL,
  `file_tugas` varchar(255) DEFAULT NULL,
  `tgl_mulai` datetime NOT NULL,
  `tgl_selesai` datetime NOT NULL,
  `id_kelas` int(11) NOT NULL,
  `id_mapel` int(11) NOT NULL,
  `id_guru` int(11) NOT NULL,
  `tgl_buat` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tugas`
--

INSERT INTO `tugas` (`id_tugas`, `judul_tugas`, `deskripsi`, `file_tugas`, `tgl_mulai`, `tgl_selesai`, `id_kelas`, `id_mapel`, `id_guru`, `tgl_buat`) VALUES
(1, 'LATIHAN SOAL', 'kerjakan latihan pada modul pertemuan pertama', '', '2026-04-19 10:21:00', '2026-04-20 00:00:00', 1, 4, 10, '2026-04-19 03:21:52'),
(2, 'Latihan Soal 2', '', NULL, '2026-04-27 09:00:00', '2026-04-27 10:30:00', 5, 4, 10, '2026-04-26 17:03:14');

-- --------------------------------------------------------

--
-- Table structure for table `ujian`
--

CREATE TABLE `ujian` (
  `id_ujian` int(11) NOT NULL,
  `judul_ujian` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `jenis_evaluasi` enum('Kuis','Ulangan Harian','UTS','UAS') DEFAULT 'Kuis',
  `durasi` int(11) NOT NULL,
  `tgl_mulai` datetime NOT NULL,
  `tgl_selesai` datetime NOT NULL,
  `id_kelas` int(11) NOT NULL,
  `id_mapel` int(11) NOT NULL,
  `id_guru` int(11) NOT NULL,
  `tgl_buat` timestamp NOT NULL DEFAULT current_timestamp(),
  `waktu` int(11) NOT NULL DEFAULT 60
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ujian`
--

INSERT INTO `ujian` (`id_ujian`, `judul_ujian`, `deskripsi`, `jenis_evaluasi`, `durasi`, `tgl_mulai`, `tgl_selesai`, `id_kelas`, `id_mapel`, `id_guru`, `tgl_buat`, `waktu`) VALUES
(1, 'Bahasa Indonesia', '', 'Kuis', 60, '2026-04-19 14:00:00', '2026-04-19 15:00:00', 1, 4, 10, '2026-04-19 06:04:27', 60),
(2, 'Kuis Bahasa Indonesia', '', 'Kuis', 60, '2026-04-20 09:00:00', '2026-04-20 10:00:00', 2, 4, 10, '2026-04-19 15:53:18', 60);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `nip` varchar(50) DEFAULT NULL,
  `tahun_masuk` varchar(20) DEFAULT NULL,
  `tahun_keluar` varchar(20) DEFAULT 'Sekarang',
  `nisn` varchar(20) DEFAULT NULL,
  `nis` varchar(20) DEFAULT NULL,
  `status_aktif` enum('Aktif','Non-Aktif') DEFAULT 'Aktif',
  `role` enum('admin','guru','siswa') NOT NULL,
  `id_kelas` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'aktif',
  `id_mapel` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `nama_lengkap`, `nip`, `tahun_masuk`, `tahun_keluar`, `nisn`, `nis`, `status_aktif`, `role`, `id_kelas`, `status`, `id_mapel`) VALUES
(1, 'admin_ananda', '$2y$10$cC9yaR0DlPMmZafhtbiskOKPN.gm.PQ/.PywMvih2ipIitYrUbsuS', 'Ananda Abdul Majid', NULL, NULL, 'Sekarang', NULL, NULL, 'Aktif', 'admin', NULL, 'aktif', NULL),
(2, 'admin', '$2y$10$C88yfbFHnrEPSccKBPSql.rkWXqPV2bkVz8.QsaEnbaaT34LKL2tq', 'Admin Pertama', NULL, NULL, 'Sekarang', NULL, NULL, 'Aktif', 'admin', NULL, 'aktif', NULL),
(3, 'guru_sitia', '$2y$10$Ie2RSuEVRNgl2Zk2I0pXAeyEE39wNn/IAhDHiIfsWnvMG2afpG2rG', 'Siti Aminah, M.Pd', '62187219982', '2024/2025', 'Sekarang', NULL, NULL, 'Aktif', 'guru', NULL, 'aktif', 1),
(4, '00123456', '$2y$10$KGnk8/VY5aUdHcrGoaWUhuyTNvUz1.cXuVTgIe2wR.yS0K2EoGyzu', 'Nur Siti', NULL, NULL, 'Sekarang', NULL, NULL, 'Aktif', 'siswa', 2, 'aktif', NULL),
(9, '2008913754', '$2y$10$WZjTldz54WiaKHEacw0GXu.kZCIEmUH2TruzAvGEmkzDa5ebBvLoe', 'Martin Siregar', NULL, '2024/2025', 'Sekarang', '2008913754', '102825375', 'Aktif', 'siswa', 1, 'aktif', NULL),
(10, 'guru_budi', '$2y$10$TVwdo0gkleFg9dSl/l9byuYzDOsO24nGw1kqbS.6wFjkc/pYavJh.', 'Budi Santoso, S.pd', '192016785463490', '2020/2021', 'Sekarang', NULL, NULL, 'Aktif', 'guru', NULL, 'aktif', 4),
(11, 'guru_mark', '$2y$10$jmCJDqjAbxEClJdwIIOFY.moJveUcUvKaCejq86ivChaO.sjqJEWi', 'Markie, S.T', '1982936200136', '2025/2026', 'Sekarang', NULL, NULL, 'Aktif', 'guru', NULL, 'aktif', 6),
(14, '2009367102', '$2y$10$mNZlKO5Pqp6ILZ1b7HS9NemNWXzMJDL8dhs9CqPIpt82DKvRKkwmi', 'Ihan Saputra', NULL, '2020/2021', '2023/2024', '2009367102', '63785376158', 'Aktif', 'siswa', 1, 'nonaktif', NULL),
(15, '2001986543', '$2y$10$YrA2kB180xqVUMueC7SRx.6wXATxPCICWPfaPYWTBgoTVHPJTzcVW', 'David ', NULL, '2024/2025', 'Sekarang', '2001986543', '102787437', 'Aktif', 'siswa', 1, 'aktif', NULL),
(16, '2001826492', '$2y$10$xEjhRmdDgT.9abkPxeLI1eA7aqTLPlSoCCetXf7alc4s5pzWcAC9u', 'Gracia ', NULL, '2024/2025', 'Sekarang', '2001826492', '2102837162', 'Aktif', 'siswa', 2, 'aktif', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `forum_balasan`
--
ALTER TABLE `forum_balasan`
  ADD PRIMARY KEY (`id_balasan`),
  ADD KEY `id_topik` (`id_topik`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `forum_topik`
--
ALTER TABLE `forum_topik`
  ADD PRIMARY KEY (`id_topik`);

--
-- Indexes for table `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id_kelas`);

--
-- Indexes for table `mapel`
--
ALTER TABLE `mapel`
  ADD PRIMARY KEY (`id_mapel`),
  ADD KEY `id_kelas` (`id_kelas`);

--
-- Indexes for table `materi`
--
ALTER TABLE `materi`
  ADD PRIMARY KEY (`id_materi`);

--
-- Indexes for table `nilai_ujian`
--
ALTER TABLE `nilai_ujian`
  ADD PRIMARY KEY (`id_nilai`),
  ADD KEY `id_ujian` (`id_ujian`),
  ADD KEY `id_siswa` (`id_siswa`);

--
-- Indexes for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id_notif`);

--
-- Indexes for table `pengumpulan_tugas`
--
ALTER TABLE `pengumpulan_tugas`
  ADD PRIMARY KEY (`id_pengumpulan`),
  ADD KEY `id_tugas` (`id_tugas`),
  ADD KEY `id_siswa` (`id_siswa`);

--
-- Indexes for table `penugasan_guru`
--
ALTER TABLE `penugasan_guru`
  ADD PRIMARY KEY (`id_penugasan`);

--
-- Indexes for table `soal_ujian`
--
ALTER TABLE `soal_ujian`
  ADD PRIMARY KEY (`id_soal`),
  ADD KEY `id_ujian` (`id_ujian`);

--
-- Indexes for table `tahun_ajaran`
--
ALTER TABLE `tahun_ajaran`
  ADD PRIMARY KEY (`id_tahun`);

--
-- Indexes for table `tugas`
--
ALTER TABLE `tugas`
  ADD PRIMARY KEY (`id_tugas`);

--
-- Indexes for table `ujian`
--
ALTER TABLE `ujian`
  ADD PRIMARY KEY (`id_ujian`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `fk_user_kelas` (`id_kelas`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `forum_balasan`
--
ALTER TABLE `forum_balasan`
  MODIFY `id_balasan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `forum_topik`
--
ALTER TABLE `forum_topik`
  MODIFY `id_topik` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id_kelas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `mapel`
--
ALTER TABLE `mapel`
  MODIFY `id_mapel` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `materi`
--
ALTER TABLE `materi`
  MODIFY `id_materi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `nilai_ujian`
--
ALTER TABLE `nilai_ujian`
  MODIFY `id_nilai` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id_notif` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pengumpulan_tugas`
--
ALTER TABLE `pengumpulan_tugas`
  MODIFY `id_pengumpulan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `penugasan_guru`
--
ALTER TABLE `penugasan_guru`
  MODIFY `id_penugasan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `soal_ujian`
--
ALTER TABLE `soal_ujian`
  MODIFY `id_soal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tahun_ajaran`
--
ALTER TABLE `tahun_ajaran`
  MODIFY `id_tahun` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tugas`
--
ALTER TABLE `tugas`
  MODIFY `id_tugas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ujian`
--
ALTER TABLE `ujian`
  MODIFY `id_ujian` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `forum_balasan`
--
ALTER TABLE `forum_balasan`
  ADD CONSTRAINT `forum_balasan_ibfk_1` FOREIGN KEY (`id_topik`) REFERENCES `forum_topik` (`id_topik`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_balasan_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `mapel`
--
ALTER TABLE `mapel`
  ADD CONSTRAINT `mapel_ibfk_2` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE CASCADE;

--
-- Constraints for table `nilai_ujian`
--
ALTER TABLE `nilai_ujian`
  ADD CONSTRAINT `nilai_ujian_ibfk_1` FOREIGN KEY (`id_ujian`) REFERENCES `ujian` (`id_ujian`) ON DELETE CASCADE,
  ADD CONSTRAINT `nilai_ujian_ibfk_2` FOREIGN KEY (`id_siswa`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `pengumpulan_tugas`
--
ALTER TABLE `pengumpulan_tugas`
  ADD CONSTRAINT `pengumpulan_tugas_ibfk_1` FOREIGN KEY (`id_tugas`) REFERENCES `tugas` (`id_tugas`) ON DELETE CASCADE,
  ADD CONSTRAINT `pengumpulan_tugas_ibfk_2` FOREIGN KEY (`id_siswa`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `soal_ujian`
--
ALTER TABLE `soal_ujian`
  ADD CONSTRAINT `soal_ujian_ibfk_1` FOREIGN KEY (`id_ujian`) REFERENCES `ujian` (`id_ujian`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
