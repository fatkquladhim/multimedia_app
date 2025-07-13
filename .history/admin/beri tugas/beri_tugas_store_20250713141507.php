<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $deadline = $_POST['deadline'];
    $id_penerima = $_POST['id_penerima_tugas'];
    $id_pemberi = $_SESSION['user_id']; // ID admin yang memberi tugas
    $status = 'pending'; // Status awal tugas
    
    // Siapkan dan jalankan query
    $stmt = $conn->prepare("INSERT INTO tugas (judul, deskripsi, deadline, id_pemberi_tugas, id_penerima_tugas, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssis", $judul, $deskripsi, $deadline, $id_pemberi, $id_penerima, $status);
    
    if ($stmt->execute()) {
        header("Location: beri_tugas_form.php?status=success&message=Tugas berhasil diberikan");
    } else {
        header("Location: beri_tugas_form.php?status=error&message=Gagal memberikan tugas");
    }
    
    $stmt->close();
}

$conn->close();
?>
