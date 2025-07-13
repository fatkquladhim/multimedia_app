<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_tugas = $_POST['id_tugas'] ?? null;
    $jawaban = $_POST['jawaban'] ?? '';

    if (!$id_tugas || empty($jawaban)) {
        header('Location: tugas_user.php?status=error&message=Data tugas tidak valid');
        exit;
    }

    $stmt = $conn->prepare('INSERT INTO tugas_jawaban (id_tugas, id_user, jawaban, tanggal_kirim) VALUES (?, ?, ?, NOW())');
    $stmt->bind_param('iis', $id_tugas, $_SESSION['user_id'], $jawaban);

    if ($stmt->execute()) {
        header('Location: tugas_user.php?status=success&message=Tugas berhasil dikirim');
    } else {
        header('Location: tugas_user.php?status=error&message=Gagal mengirim tugas');
    }
    $stmt->close();
}

$conn->close();
?>
