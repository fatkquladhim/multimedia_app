<?php
// Konfigurasi database
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // ganti jika user database Anda berbeda
define('DB_PASS', '');     // ganti jika ada password database
define('DB_NAME', 'db_multimedia'); // nama database

$servername = "localhost";
$username = "root";
$password = "";
$database = "db_multimedia";

// Membuat koneksi
$conn = mysqli_connect($servername, $username, $password, $database);

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
