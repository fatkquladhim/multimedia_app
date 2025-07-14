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
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $deadline = $_POST['deadline'];
    $id_penerima = $_POST['id_penerima_tugas'];
    $id_pemberi = $_SESSION['user_id']; // ID admin yang memberi tugas
    $status = 'pending'; // Status awal tugas
    
    // Validasi input
    if (empty($judul) || empty($deskripsi) || empty($deadline) || empty($id_penerima)) {
        header("Location: beri_tugas_form.php?status=error&message=Semua field harus diisi.");
        exit;
    }

    // Pastikan deadline adalah tanggal yang valid dan di masa depan (opsional, tapi baik)
    if (strtotime($deadline) === false || strtotime($deadline) < strtotime(date('Y-m-d'))) {
        header("Location: beri_tugas_form.php?status=error&message=Deadline tidak valid atau sudah lewat.");
        exit;
    }

    // Siapkan dan jalankan query
    // Menggunakan 's' untuk deadline karena format string 'YYYY-MM-DD'
    $stmt = $conn->prepare("INSERT INTO tugas (judul, deskripsi, deadline, id_pemberi_tugas, id_penerima_tugas, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiss", $judul, $deskripsi, $deadline, $id_pemberi, $id_penerima, $status);
    
    if ($stmt->execute()) {
        header("Location: beri_tugas_form.php?status=success&message=Tugas berhasil diberikan.");
    } else {
        // Log error for debugging
        error_log("Error inserting task: " . $stmt->error);
        header("Location: beri_tugas_form.php?status=error&message=Gagal memberikan tugas. Silakan coba lagi.");
    }
    
    $stmt->close();
} else {
    header("Location: beri_tugas_form.php"); // Redirect if not a POST request
}

$conn->close();
?>
