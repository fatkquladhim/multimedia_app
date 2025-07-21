<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}
require_once '../../includes/db_config.php';

$id_user = $_SESSION['user_id'];
$id_tugas = $_POST['id_tugas'] ?? 0;
$file_jawaban_name = '';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check if task exists and belongs to user
$stmt_check = $conn->prepare('SELECT id, status FROM tugas WHERE id = ? AND id_penerima_tugas = ?');
$stmt_check->bind_param('ii', $id_tugas, $id_user);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$task_info = $result_check->fetch_assoc();
$stmt_check->close();

if (!$task_info) {
    header('Location: ../dashboard.php?status=error&message=Tugas tidak ditemukan atau bukan tugas Anda.');
    $conn->close();
    exit;
}

// Check if task already has a submission from this user
$stmt_check_submission = $conn->prepare('SELECT id FROM tugas_jawaban WHERE id_tugas = ? AND id_user = ?');
$stmt_check_submission->bind_param('ii', $id_tugas, $id_user);
$stmt_check_submission->execute();
$result_submission = $stmt_check_submission->get_result();
if ($result_submission->num_rows > 0) {
    header('Location: ../dashboard.php?status=info&message=Anda sudah mengirimkan jawaban untuk tugas ini.');
    $stmt_check_submission->close();
    $conn->close();
    exit;
}
$stmt_check_submission->close();

// Handle upload file jawaban
if (isset($_FILES['file_jawaban']) && $_FILES['file_jawaban']['error'] === UPLOAD_ERR_OK) {
    $file_tmp_name = $_FILES['file_jawaban']['tmp_name'];
    $file_size = $_FILES['file_jawaban']['size'];
    $file_ext = strtolower(pathinfo($_FILES['file_jawaban']['name'], PATHINFO_EXTENSION));

    $allowed_ext = $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png', 'mp4', 'mov', 'avi', 'mkv', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
    $max_file_size = 40* 1024 * 1024; // 40 MB

    if (!in_array($file_ext, $allowed_ext)) {
        header('Location: tugas_kerjakan.php?id=' . $id_tugas . '&status=error&message=Format file tidak diizinkan. Hanya PDF, JPG, PNG.');
        $conn->close();
        exit;
    }

    if ($file_size > $max_file_size) {
        header('Location: tugas_kerjakan.php?id=' . $id_tugas . '&status=error&message=Ukuran file terlalu besar. Maksimal 5MB.');
        $conn->close();
        exit;
    }

    $file_jawaban_name = 'jawaban_' . $id_tugas . '_' . $id_user . '_' . time() . '.' . $file_ext;
    $upload_path = '../../uploads/tugas_jawaban/' . $file_jawaban_name;

    $upload_dir = '../../uploads/tugas_jawaban/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (!move_uploaded_file($file_tmp_name, $upload_path)) {
        header('Location: tugas_kerjakan.php?id=' . $id_tugas . '&status=error&message=Gagal mengunggah file.');
        $conn->close();
        exit;
    }
} else {
    header('Location: tugas_kerjakan.php?id=' . $id_tugas . '&status=error&message=Tidak ada file yang diunggah atau terjadi kesalahan.');
    $conn->close();
    exit;
}

// Insert into tugas_jawaban
$stmt_insert_jawaban = $conn->prepare('INSERT INTO tugas_jawaban (id_tugas, id_user, file_jawaban) VALUES (?, ?, ?)');
$stmt_insert_jawaban->bind_param('iis', $id_tugas, $id_user, $file_jawaban_name);

if ($stmt_insert_jawaban->execute()) {
    // Update status tugas menjadi 'dikirim'
    $stmt_update_tugas = $conn->prepare('UPDATE tugas SET status = "dikirim" WHERE id = ?');
    $stmt_update_tugas->bind_param('i', $id_tugas);
    $stmt_update_tugas->execute();
    $stmt_update_tugas->close();

    header('Location: ../dashboard.php?status=success&message=Jawaban berhasil dikirim.');
} else {
    // If insertion fails, try to delete the uploaded file to prevent orphaned files
    if (file_exists($upload_path)) {
        unlink($upload_path);
    }
    error_log("Error inserting task answer: " . $stmt_insert_jawaban->error);
    header('Location: ../dashboard.php?status=error&message=Gagal mengirim jawaban. Silakan coba lagi.');
}
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file_tmp_name);
finfo_close($finfo);

// Daftar MIME yang diizinkan
$allowed_mime = [
    'application/pdf',
    'image/jpeg', 'image/png',
    'video/mp4', 'video/quicktime', 'video/x-msvideo',
    'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
];

if (!in_array($mime_type, $allowed_mime)) {
    header('Location: tugas_kerjakan.php?id=' . $id_tugas . '&status=error&message=Format file tidak diizinkan.');
    $conn->close();
    exit;
}


$stmt_insert_jawaban->close();
$conn->close();
exit;
