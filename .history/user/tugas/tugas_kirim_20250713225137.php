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

    // Handle file upload
    $file_jawaban = '';
    if (isset($_FILES['file_jawaban']) && $_FILES['file_jawaban']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($_FILES['file_jawaban']['type'], $allowed_types)) {
            header('Location: tugas_user.php?status=error&message=Tipe file tidak diizinkan');
            exit;
        }

        if ($_FILES['file_jawaban']['size'] > $max_size) {
            header('Location: tugas_user.php?status=error&message=Ukuran file terlalu besar');
            exit;
        }

        $file_jawaban = 'uploads/tugas_jawaban/' . basename($_FILES['file_jawaban']['name']);
        if (!move_uploaded_file($_FILES['file_jawaban']['tmp_name'], '../../' . $file_jawaban)) {
            header('Location: tugas_user.php?status=error&message=Gagal mengunggah file');
            exit;
        }
    }

    $stmt = $conn->prepare('INSERT INTO tugas_jawaban (id_tugas, id_user, jawaban, file_jawaban, tanggal_kirim) VALUES (?, ?, ?, ?, NOW())');
    $stmt->bind_param('iiss', $id_tugas, $_SESSION['user_id'], $jawaban, $file_jawaban);

    if ($stmt->execute()) {
        header('Location: tugas_user.php?status=success&message=Tugas berhasil dikirim');
    } else {
        header('Location: tugas_user.php?status=error&message=Gagal mengirim tugas');
    }
    $stmt->close();
}

$conn->close();
?>
