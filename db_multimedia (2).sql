-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 25 Jul 2025 pada 05.16
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
  `kondisi` varchar(50) DEFAULT NULL,
  `kelompok` varchar(255) DEFAULT NULL,
  `milik` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `alat`
--

INSERT INTO `alat` (`id`, `nama_alat`, `jumlah`, `kondisi`, `kelompok`, `milik`) VALUES
(2, 'sapu', 1, 'Baik', NULL, NULL),
(3, 'Kamera DSLR', 1, 'Baik', NULL, NULL),
(4, 'Tripod', 8, 'Baik', NULL, NULL),
(5, 'Lighting Set', 1, 'Baik', NULL, NULL),
(6, 'Microphone', 2, 'Baik', NULL, NULL),
(7, 'Green Screen', 10, 'Baik', 'kamera', 'umum');

-- --------------------------------------------------------

--
-- Struktur dari tabel `anggota`
--

CREATE TABLE `anggota` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `foto` varchar(255) NOT NULL,
  `alamat` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `no_hp` varchar(15) NOT NULL,
  `id_user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `anggota`
--

INSERT INTO `anggota` (`id`, `nama`, `foto`, `alamat`, `email`, `no_hp`, `id_user`) VALUES
(21, 'adhim1', 'Feed IG Zapen (Harlah).jpg', 'maloang', 'fatquladhim@gmail.com', '8925489', 21),
(22, 'jarot', '1.png', 'turen malang', 'adhim@gmail.com', '085854641569', 22);

-- --------------------------------------------------------

--
-- Struktur dari tabel `izin_malam`
--

CREATE TABLE `izin_malam` (
  `id` int(11) NOT NULL,
  `id_anggota` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `jam_izin` time DEFAULT NULL,
  `jam_selesai_izin` time DEFAULT NULL,
  `alasan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `izin_nugas`
--

CREATE TABLE `izin_nugas` (
  `id` int(11) NOT NULL,
  `id_anggota` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `jam_izin` time DEFAULT NULL,
  `jam_selesai_izin` time DEFAULT NULL,
  `alasan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `izin_nugas`
--

INSERT INTO `izin_nugas` (`id`, `id_anggota`, `tanggal`, `jam_izin`, `jam_selesai_izin`, `alasan`) VALUES
(2, 21, '2025-07-18', '11:21:00', '12:21:00', 'makan');

-- --------------------------------------------------------

--
-- Struktur dari tabel `keuangan`
--

CREATE TABLE `keuangan` (
  `id` int(11) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `pemasukan` int(11) DEFAULT NULL,
  `pengeluaran` int(11) DEFAULT NULL,
  `saldo` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `keuangan`
--

INSERT INTO `keuangan` (`id`, `tanggal`, `keterangan`, `pemasukan`, `pengeluaran`, `saldo`, `created_at`) VALUES
(115, '2025-07-21', 'beli gas', 100000, 0, 100000, '2025-07-21 15:11:33'),
(117, '2025-07-21', 'beli gas', 0, 1212, -1212, '2025-07-21 15:19:49');

-- --------------------------------------------------------

--
-- Struktur dari tabel `legalisasi_laptop`
--

CREATE TABLE `legalisasi_laptop` (
  `id` int(11) NOT NULL,
  `id_anggota` int(11) NOT NULL,
  `merk` varchar(100) NOT NULL,
  `tipe` varchar(100) NOT NULL,
  `serial_number` varchar(100) NOT NULL,
  `status` enum('Baik','Rusak','Perlu Perbaikan') NOT NULL,
  `file_bukti` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `legalisasi_laptop`
--

INSERT INTO `legalisasi_laptop` (`id`, `id_anggota`, `merk`, `tipe`, `serial_number`, `status`, `file_bukti`, `created_at`, `updated_at`) VALUES
(1, 21, 'asus', 'vivbobook', '1233', 'Baik', '68751c74803cd.png', '2025-07-14 15:04:20', '2025-07-14 15:56:16');

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
(9, NULL, 2, '2025-07-12', '2025-07-13', 11, 'dikembalikan', 'umum', 'asep', '12');

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
(2, 21, 'moch fatkqul adhim', 'fatquladhim@gmail.com', 'turen malang', '085854641569', 'profile_21_1752769537.png'),
(3, 22, 'dwi jarot', 'adhim@gmail.com', 'turen malang', '085854641569', 'profile_22_1752851388.png'),
(4, 2, 'moch fathul adhim', 'fatquladhim@gmail.com', 'turen malang', '085854641569', 'profile_2_1752993248.png');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tugas`
--

CREATE TABLE `tugas` (
  `id` int(11) NOT NULL,
  `judul` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `id_pemberi_tugas` int(11) NOT NULL,
  `id_penerima_tugas` int(11) NOT NULL,
  `status` enum('pending','selesai','diperiksa','dikirim') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tugas`
--

INSERT INTO `tugas` (`id`, `judul`, `deskripsi`, `deadline`, `id_pemberi_tugas`, `id_penerima_tugas`, `status`) VALUES
(7, 'mtk', '1 kali 2', '2025-07-21', 2, 21, 'selesai'),
(8, 'mtk', 'jdghngu', '2025-07-21', 2, 21, 'selesai'),
(9, 'nahwu', 'apa itu huruf', '2025-07-24', 2, 21, 'dikirim'),
(10, 'jbefuejb', 'gasuyhfgb', '2025-07-24', 2, 21, 'dikirim'),
(11, 'usul', 'kenapa ada wajib', '2025-07-22', 2, 21, 'pending');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tugas_jawaban`
--

CREATE TABLE `tugas_jawaban` (
  `id` int(11) NOT NULL,
  `id_tugas` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `file_jawaban` varchar(255) DEFAULT NULL,
  `nilai` int(11) DEFAULT 0,
  `komentar` text DEFAULT NULL,
  `tanggal_submit` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tugas_jawaban`
--

INSERT INTO `tugas_jawaban` (`id`, `id_tugas`, `id_user`, `file_jawaban`, `nilai`, `komentar`, `tanggal_submit`) VALUES
(7, 7, 21, 'jawaban_7_21_1753080339.png', 100, 'fgjfgj', '2025-07-21 13:45:39'),
(8, 8, 21, 'jawaban_8_21_1753080645.png', 100, '', '2025-07-21 13:50:45'),
(9, 10, 21, 'jawaban_10_21_1753081323.png', 0, NULL, '2025-07-21 14:02:03'),
(10, 9, 21, 'jawaban_9_21_1753115002.docx', 0, NULL, '2025-07-21 23:23:22');

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
(2, 'asep', '$2y$10$ZvJxXAeCPWgMdfPO28SaFuzg6rU8McbByuj/jx/gDjJ/LgUoPIv6e', 'admin', NULL, NULL),
(21, 'adhim1', '$2y$10$Sgg2LTHZ3XN5OLY.A.l08OzMzGIgpysnSio8kzO0Nz54LZ/q.LUKy', 'user', 'moch fatkqul adhim', 'fatquladhim@gmail.com'),
(22, 'jarot', '$2y$10$P61WSyN6oEG7ojQuj05FcuGoV0cT4vwoCONiDEuGX.u4i/XjZ7q2q', 'user', NULL, NULL);

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
-- Indeks untuk tabel `keuangan`
--
ALTER TABLE `keuangan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `legalisasi_laptop`
--
ALTER TABLE `legalisasi_laptop`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `serial_number` (`serial_number`),
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `anggota`
--
ALTER TABLE `anggota`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT untuk tabel `izin_malam`
--
ALTER TABLE `izin_malam`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `izin_nugas`
--
ALTER TABLE `izin_nugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `keuangan`
--
ALTER TABLE `keuangan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT untuk tabel `legalisasi_laptop`
--
ALTER TABLE `legalisasi_laptop`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `peminjaman_barang`
--
ALTER TABLE `peminjaman_barang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `penyewaan_barang`
--
ALTER TABLE `penyewaan_barang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `profile`
--
ALTER TABLE `profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `tugas`
--
ALTER TABLE `tugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `tugas_jawaban`
--
ALTER TABLE `tugas_jawaban`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

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
  ADD CONSTRAINT `legalisasi_laptop_ibfk_1` FOREIGN KEY (`id_anggota`) REFERENCES `anggota` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `tugas_ibfk_1` FOREIGN KEY (`id_pemberi_tugas`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tugas_ibfk_2` FOREIGN KEY (`id_penerima_tugas`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tugas_jawaban`
--
ALTER TABLE `tugas_jawaban`
  ADD CONSTRAINT `tugas_jawaban_ibfk_1` FOREIGN KEY (`id_tugas`) REFERENCES `tugas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tugas_jawaban_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
