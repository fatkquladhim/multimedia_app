-- SQL untuk database mm_app_db
CREATE DATABASE IF NOT EXISTS db_multimedia;
USE db_multimedia;

-- Tabel users
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  nama_lengkap VARCHAR(100),
  email VARCHAR(100)
);

-- Tabel anggota
CREATE TABLE IF NOT EXISTS anggota (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  nim VARCHAR(30) UNIQUE,
  alamat TEXT,
  email VARCHAR(100),
  no_hp VARCHAR(20)
);

-- Tabel alat
CREATE TABLE IF NOT EXISTS alat (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_alat VARCHAR(100) NOT NULL,
  jumlah INT DEFAULT 0,
  kondisi VARCHAR(50)
);

-- Tabel peminjaman
CREATE TABLE IF NOT EXISTS peminjaman (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_anggota INT,
  id_alat INT,
  tanggal_pinjam DATE,
  tanggal_kembali DATE,
  status VARCHAR(30),
  FOREIGN KEY (id_anggota) REFERENCES anggota(id),
  FOREIGN KEY (id_alat) REFERENCES alat(id)
);

-- Tabel penyewaan
CREATE TABLE IF NOT EXISTS penyewaan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_anggota INT,
  id_alat INT,
  tanggal_sewa DATE,
  tanggal_kembali DATE,
  status VARCHAR(30),
  FOREIGN KEY (id_anggota) REFERENCES anggota(id),
  FOREIGN KEY (id_alat) REFERENCES alat(id)
);

-- Tabel tugas
CREATE TABLE IF NOT EXISTS tugas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(100) NOT NULL,
  deskripsi TEXT,
  deadline DATE,
  id_pemberi_tugas INT,
  id_penerima_tugas INT,
  status VARCHAR(30),
  FOREIGN KEY (id_pemberi_tugas) REFERENCES users(id),
  FOREIGN KEY (id_penerima_tugas) REFERENCES users(id)
);

-- Tabel tugas_jawaban
CREATE TABLE IF NOT EXISTS tugas_jawaban (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_tugas INT,
  id_user INT,
  file_jawaban VARCHAR(255),
  nilai INT,
  komentar TEXT,
  FOREIGN KEY (id_tugas) REFERENCES tugas(id),
  FOREIGN KEY (id_user) REFERENCES users(id)
);

-- Tabel izin_malam
CREATE TABLE IF NOT EXISTS izin_malam (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_anggota INT,
  tanggal DATE,
  alasan TEXT,
  status VARCHAR(30),
  FOREIGN KEY (id_anggota) REFERENCES anggota(id)
);

-- Tabel izin_nugas
CREATE TABLE IF NOT EXISTS izin_nugas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_anggota INT,
  tanggal DATE,
  alasan TEXT,
  status VARCHAR(30),
  FOREIGN KEY (id_anggota) REFERENCES anggota(id)
);

-- Tabel legalisasi_laptop
CREATE TABLE IF NOT EXISTS legalisasi_laptop (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_anggota INT,
  merk VARCHAR(50),
  tipe VARCHAR(50),
  serial_number VARCHAR(100),
  status VARCHAR(30),
  file_bukti VARCHAR(255),
  FOREIGN KEY (id_anggota) REFERENCES anggota(id)
);

-- Tabel uang_masuk
CREATE TABLE IF NOT EXISTS uang_masuk (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tanggal DATE,
  jumlah DECIMAL(15,2),
  keterangan TEXT
);

CREATE TABLE IF NOT EXISTS uang_keluar (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tanggal DATE,
  jumlah DECIMAL(15,2),
  keterangan TEXT
);

-- Tabel profile
CREATE TABLE IF NOT EXISTS profile (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_user INT NOT NULL,
  nama_lengkap VARCHAR(100),
  email VARCHAR(100),
  alamat TEXT,
  no_hp VARCHAR(20),
  foto VARCHAR(255),
  FOREIGN KEY (id_user) REFERENCES users(id)
);
