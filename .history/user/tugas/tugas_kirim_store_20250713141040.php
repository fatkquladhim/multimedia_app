<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}
require_once '../../includes/db_config.php';

$id_user = $_SESSION['user_id'];
$id_tugas = $_POST['id_tugas'] ?? 0;
$file_jawaban = '';

// Handle upload file jawaban
if (isset($_FILES['jawaban']) && $_FILES['jawaban']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['jawaban']['name'], PATHINFO_EXTENSION);
    $file_jawaban = 'jawaban_' . $id_tugas . '_' . $id_user . '_' . time() . '.' . $ext;
    move_uploaded_file($_FILES['jawaban']['tmp_name'], '../../uploads/tugas_jawaban/' . $file_jawaban);
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$stmt = $conn->prepare('INSERT INTO tugas_jawaban (id_tugas, id_user, file_jawaban) VALUES (?, ?, ?)');
$stmt->bind_param('iis', $id_tugas, $id_user, $file_jawaban);
$stmt->execute();
$stmt->close();
$conn->close();
header('Location: tugas_user.php');
exit;
