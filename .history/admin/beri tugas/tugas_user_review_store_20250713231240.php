<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_jawaban = $_POST['id_jawaban'];
    $nilai = $_POST['nilai'];
    $komentar = $_POST['komentar'];
    
    // Update nilai dan komentar di tabel tugas_jawaban
    $stmt = $conn->prepare("UPDATE tugas_jawaban SET nilai = ?, komentar = ? WHERE id = ?");
    $stmt->bind_param("isi", $nilai, $komentar, $id_jawaban);
    
    if ($stmt->execute()) {
        header("Location: tugas_selesai_riwayat.php?status=success&message=Nilai berhasil diberikan");
    } else {
        header("Location: tugas_selesai_riwayat.php?status=error&message=Gagal memberikan nilai");
    }
    
    $stmt->close();
}

$conn->close();
?>
