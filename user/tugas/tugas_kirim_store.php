<?php
session_start();
require_once '../../includes/db_config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data dari form
$judul = mysqli_real_escape_string($conn, $_POST['judul']);
$deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);

// Upload file tugas jika ada
$file = '';
if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $filename = 'jawaban_' . $user_id . '_' . time() . '.' . $ext;
    $target = '../../uploads/tugas_jawaban/' . $filename;
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
        $file = $filename;
    }
}

// Simpan tugas ke database
$query = "INSERT INTO tugas_kirim (user_id, judul, deskripsi, file) VALUES ('$user_id', '$judul', '$deskripsi', '$file')";
mysqli_query($conn, $query) or die(mysqli_error($conn));

header('Location: riwayat_tugas.php');
exit();
?>