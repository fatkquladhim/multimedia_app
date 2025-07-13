-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 12 Jul 2025 pada 18.02
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_multimedia`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `alat`
--

CREATE TABLE `alat` (
  `id` int(11) NOT NULL,
  `nama_alat` varchar(100) NOT NULL,
  `jumlah` int(11) DEFAULT 0,
  `kondisi` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `alat`
--

INSERT INTO `alat` (`id`, `nama_alat`, `jumlah`, `kondisi`) VALUES
(2, 'sapu', 1, 'Baik'),
(3, 'Kamera DSLR', 2, 'Baik'),
(4, 'Tripod', 8, 'Baik'),
(5, 'Lighting Set', 1, 'Baik'),
(6, 'Microphone', 2, 'Baik'),
(7, 'Green Screen', 0, 'Baik');

-- --------------------------------------------------------

--
-- Struktur dari tabel `anggota`
--

CREATE TABLE `anggota` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nim` varchar(30) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `anggota`
--

INSERT INTO `anggota` (`id`, `nama`, `nim`, `alamat`, `email`, `no_hp`, `id_user`) VALUES
(3, 'jaya', '12345', 'maloang', 'fatquladhim@gmail.com', '8925489', 5),
(5, 'John Doe', '123456789', 'Jl. Contoh 123', 'john@example.com', '08123456789', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `izin_malam`
--

CREATE TABLE `izin_malam` (
  `id` int(11) NOT NULL,
  `id_anggota` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `alasan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `izin_malam`
--

INSERT INTO `izin_malam` (`id`, `id_anggota`, `tanggal`, `alasan`) VALUES
(1, 3, '2025-07-11', 'sakit');

-- --------------------------------------------------------

--
-- Struktur dari tabel `izin_nugas`
--

CREATE TABLE `izin_nugas` (
  `id` int(11) NOT NULL,
  `id_anggota` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `alasan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `legalisasi_laptop`
--

CREATE TABLE `legalisasi_laptop` (
  `id` int(11) NOT NULL,
  `id_anggota` int(11) DEFAULT NULL,
  `merk` varchar(50) DEFAULT NULL,
  `tipe` varchar(50) DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL,
  `file_bukti` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `legalisasi_laptop`
--

INSERT INTO `legalisasi_laptop` (`id`, `id_anggota`, `merk`, `tipe`, `serial_number`, `status`, `file_bukti`) VALUES
(1, 3, 'asus', 'vivbobook', '1233', 'disetujui', '68712f15c7ace.png');

-- --------------------------------------------------------

--
-- Struktur dari tabel `peminjaman_barang`
--

CREATE TABLE `peminjaman_barang` (
  `id` int(11) NOT NULL,
  `id_anggota` int(11) DEFAULT NULL,
  `id_alat` int(11) DEFAULT NULL,
  `tanggal_pinjam` date DEFAULT NULL,
  `tanggal_kembali` date DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `status` enum('dipinjam','dikembalikan') DEFAULT 'dipinjam',
  `tipe_peminjam` enum('anggota','umum') DEFAULT 'anggota',
  `nama_peminjam` varchar(100) DEFAULT NULL,
  `kontak_peminjam` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `peminjaman_barang`
--

INSERT INTO `peminjaman_barang` (`id`, `id_anggota`, `id_alat`, `tanggal_pinjam`, `tanggal_kembali`, `jumlah`, `status`, `tipe_peminjam`, `nama_peminjam`, `kontak_peminjam`) VALUES
(8, NULL, 6, '2025-07-12', '2025-07-12', 1, 'dikembalikan', 'umum', 'asep', '2'),
(9, NULL, 2, '2025-07-12', '2025-07-17', 11, 'dipinjam', 'umum', 'asep', '12');

-- --------------------------------------------------------

--
-- Struktur dari tabel `penyewaan_barang`
--

CREATE TABLE `penyewaan_barang` (
  `id` int(11) NOT NULL,
  `id_anggota` int(11) DEFAULT NULL,
  `id_alat` int(11) DEFAULT NULL,
  `tanggal_sewa` date DEFAULT NULL,
  `tanggal_kembali` date DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `status` enum('disewa','dikembalikan') DEFAULT 'disewa',
  `biaya` decimal(10,2) DEFAULT NULL,
  `tipe_penyewa` enum('anggota','umum') DEFAULT 'anggota',
  `nama_penyewa` varchar(100) DEFAULT NULL,
  `kontak_penyewa` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `penyewaan_barang`
--

INSERT INTO `penyewaan_barang` (`id`, `id_anggota`, `id_alat`, `tanggal_sewa`, `tanggal_kembali`, `jumlah`, `status`, `biaya`, `tipe_penyewa`, `nama_penyewa`, `kontak_penyewa`) VALUES
(1, NULL, 7, '2025-07-11', '2025-07-11', 1, 'dikembalikan', 2000.00, 'umum', 'aku', '1'),
(2, NULL, 3, '2025-07-11', '2025-07-11', 1, 'dikembalikan', 12000.00, 'umum', 'aku', '1'),
(3, NULL, 3, '2025-07-11', '2025-07-11', 1, 'dikembalikan', 12000.00, 'umum', 'aku', '1'),
(4, NULL, 3, '2025-07-11', '2025-07-11', 1, 'dikembalikan', 12000.00, 'umum', 'aku', '1'),
(5, NULL, 3, '2025-07-11', '2025-07-11', 1, 'dikembalikan', 12000.00, 'umum', 'aku', '1'),
(6, NULL, 5, '2025-07-12', '2025-07-12', 1, 'dikembalikan', 12000.00, 'umum', 'aku', '1');

-- --------------------------------------------------------

--
-- Struktur dari tabel `profile`
--

CREATE TABLE `profile` (
  `id` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `profile`
--

INSERT INTO `profile` (`id`, `id_user`, `nama_lengkap`, `email`, `alamat`, `no_hp`, `foto`) VALUES
(1, 1, 'moch fatkqul adhim', 'fatquladhim@gmail.com', 'maloang', '8925489', 'profile_1_1752228748.png');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tugas`
--

CREATE TABLE `tugas` (
  `id` int(11) NOT NULL,
  `judul` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `id_pemberi_tugas` int(11) DEFAULT NULL,
  `id_penerima_tugas` int(11) DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tugas`
--

INSERT INTO `tugas` (`id`, `judul`, `deskripsi`, `deadline`, `id_pemberi_tugas`, `id_penerima_tugas`, `status`) VALUES
(1, 'mtk', '1 kali 3', '2025-07-18', 2, 1, 'pending');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tugas_jawaban`
--

CREATE TABLE `tugas_jawaban` (
  `id` int(11) NOT NULL,
  `id_tugas` int(11) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `file_jawaban` varchar(255) DEFAULT NULL,
  `nilai` int(11) DEFAULT NULL,
  `komentar` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `uang_keluar`
--

CREATE TABLE `uang_keluar` (
  `id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jumlah` int(11) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `saldo` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `uang_keluar`
--

INSERT INTO `uang_keluar` (`id`, `tanggal`, `jumlah`, `keterangan`, `saldo`) VALUES
(2, '2025-07-12', 20000, 'beli gas', 0),
(3, '2025-07-12', 12000, 'jajan', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `uang_masuk`
--

CREATE TABLE `uang_masuk` (
  `id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jumlah` int(11) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `uang_masuk`
--

INSERT INTO `uang_masuk` (`id`, `tanggal`, `jumlah`, `keterangan`) VALUES
(1, '2025-07-12', 20000, 'dari sekolah');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `nama_lengkap`, `email`) VALUES
(1, 'adhim', '$2y$10$blvEDWiik0MwItbuEmZyTemwBxYwhkf3mgYYFvIxSHFLgRLLKGuDO', 'user', NULL, NULL),
(2, 'asep', '$2y$10$ZvJxXAeCPWgMdfPO28SaFuzg6rU8McbByuj/jx/gDjJ/LgUoPIv6e', 'admin', NULL, NULL),
(5, 'jaya', '$2y$10$CDUdPcb4rMRaKLxzwGLyQutwJDBsEDTelNxdnvjrhyqT0N8qOAJ6m', 'user', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `alat`
--
ALTER TABLE `alat`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `anggota`
--
ALTER TABLE `anggota`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nim` (`nim`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeks untuk tabel `izin_malam`
--
ALTER TABLE `izin_malam`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_anggota` (`id_anggota`);

--
-- Indeks untuk tabel `izin_nugas`
--
ALTER TABLE `izin_nugas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_anggota` (`id_anggota`);

--
-- Indeks untuk tabel `legalisasi_laptop`
--
ALTER TABLE `legalisasi_laptop`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_anggota` (`id_anggota`);

--
-- Indeks untuk tabel `peminjaman_barang`
--
ALTER TABLE `peminjaman_barang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_anggota` (`id_anggota`),
  ADD KEY `id_alat` (`id_alat`);

--
-- Indeks untuk tabel `penyewaan_barang`
--
ALTER TABLE `penyewaan_barang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_anggota` (`id_anggota`),
  ADD KEY `id_alat` (`id_alat`);

--
-- Indeks untuk tabel `profile`
--
ALTER TABLE `profile`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeks untuk tabel `tugas`
--
ALTER TABLE `tugas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pemberi_tugas` (`id_pemberi_tugas`),
  ADD KEY `id_penerima_tugas` (`id_penerima_tugas`);

--
-- Indeks untuk tabel `tugas_jawaban`
--
ALTER TABLE `tugas_jawaban`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_tugas` (`id_tugas`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeks untuk tabel `uang_keluar`
--
ALTER TABLE `uang_keluar`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `uang_masuk`
--
ALTER TABLE `uang_masuk`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `alat`
--
ALTER TABLE `alat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `anggota`
--
ALTER TABLE `anggota`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `izin_malam`
--
ALTER TABLE `izin_malam`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `izin_nugas`
--
ALTER TABLE `izin_nugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `legalisasi_laptop`
--
ALTER TABLE `legalisasi_laptop`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `peminjaman_barang`
--
ALTER TABLE `peminjaman_barang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `penyewaan_barang`
--
ALTER TABLE `penyewaan_barang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `profile`
--
ALTER TABLE `profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `tugas`
--
ALTER TABLE `tugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `tugas_jawaban`
--
ALTER TABLE `tugas_jawaban`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `uang_keluar`
--
ALTER TABLE `uang_keluar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `uang_masuk`
--
ALTER TABLE `uang_masuk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `anggota`
--
ALTER TABLE `anggota`
  ADD CONSTRAINT `anggota_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `izin_malam`
--
ALTER TABLE `izin_malam`
  ADD CONSTRAINT `izin_malam_ibfk_1` FOREIGN KEY (`id_anggota`) REFERENCES `anggota` (`id`);

--
-- Ketidakleluasaan untuk tabel `izin_nugas`
--
ALTER TABLE `izin_nugas`
  ADD CONSTRAINT `izin_nugas_ibfk_1` FOREIGN KEY (`id_anggota`) REFERENCES `anggota` (`id`);

--
-- Ketidakleluasaan untuk tabel `legalisasi_laptop`
--
ALTER TABLE `legalisasi_laptop`
  ADD CONSTRAINT `legalisasi_laptop_ibfk_1` FOREIGN KEY (`id_anggota`) REFERENCES `anggota` (`id`);

--
-- Ketidakleluasaan untuk tabel `peminjaman_barang`
--
ALTER TABLE `peminjaman_barang`
  ADD CONSTRAINT `peminjaman_barang_ibfk_1` FOREIGN KEY (`id_anggota`) REFERENCES `anggota` (`id`),
  ADD CONSTRAINT `peminjaman_barang_ibfk_2` FOREIGN KEY (`id_alat`) REFERENCES `alat` (`id`);

--
-- Ketidakleluasaan untuk tabel `penyewaan_barang`
--
ALTER TABLE `penyewaan_barang`
  ADD CONSTRAINT `penyewaan_barang_ibfk_1` FOREIGN KEY (`id_anggota`) REFERENCES `anggota` (`id`),
  ADD CONSTRAINT `penyewaan_barang_ibfk_2` FOREIGN KEY (`id_alat`) REFERENCES `alat` (`id`);

--
-- Ketidakleluasaan untuk tabel `profile`
--
ALTER TABLE `profile`
  ADD CONSTRAINT `profile_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `tugas`
--
ALTER TABLE `tugas`
  ADD CONSTRAINT `tugas_ibfk_1` FOREIGN KEY (`id_pemberi_tugas`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tugas_ibfk_2` FOREIGN KEY (`id_penerima_tugas`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `tugas_jawaban`
--
ALTER TABLE `tugas_jawaban`
  ADD CONSTRAINT `tugas_jawaban_ibfk_1` FOREIGN KEY (`id_tugas`) REFERENCES `tugas` (`id`),
  ADD CONSTRAINT `tugas_jawaban_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
