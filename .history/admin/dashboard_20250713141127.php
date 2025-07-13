<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Statistik
$user_count = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$anggota_count = $conn->query("SELECT COUNT(*) FROM anggota")->fetch_row()[0];
$tugas_count = $conn->query("SELECT COUNT(*) FROM tugas")->fetch_row()[0];
$izin_malam_count = $conn->query("SELECT COUNT(*) FROM izin_malam")->fetch_row()[0];
$izin_nugas_count = $conn->query("SELECT COUNT(*) FROM izin_nugas")->fetch_row()[0];

$conn->close();
?>
<h2>Dashboard Admin</h2>
<ul>
    <li>Jumlah User: <?php echo $user_count; ?></li>
    <li>Jumlah Anggota: <?php echo $anggota_count; ?></li>
    <li>Jumlah Tugas: <?php echo $tugas_count; ?></li>
    <li>Izin Malam: <?php echo $izin_malam_count; ?></li>
    <li>Izin Nugas: <?php echo $izin_nugas_count; ?></li>
</ul>
<ul>
    <li><a href="anggota/anggota.php">Manajemen Anggota</a></li>
    <li><a href="daftar alat/daftar-alat.php">Manajemen Alat</a></li>
    <li><a href="peminjaman/peminjaman-barang.php">Peminjaman Barang</a></li>
    <li><a href="penyewaan/penyewaan-barang.php">Penyewaan Barang</a></li>
    <li><a href="beri tugas/beri_tugas_form.php">Beri Tugas</a></li>
    <li><a href="tugas/tugas_user_review.php">Review Tugas User</a></li>
    <li><a href="izin_malam/izin-malam.php">Izin Malam</a></li>
    <li><a href="izin_nugas/izin-nugas.php">Izin Nugas</a></li>
    <li><a href="legalisasi laptop/legalisasi_list.php">Legalisasi Laptop</a></li>
    <li><a href="uang masuk/masuk.php">Uang Masuk</a></li>
    <li><a href="uang keluar/keluar.php">Uang Keluar</a></li>
    <li><a href="../auth/logout.php">Logout</a></li>
</ul>
