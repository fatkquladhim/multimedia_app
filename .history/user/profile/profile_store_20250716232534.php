<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}
require_once '../../includes/db_config.php';

$id_user = $_SESSION['user_id'];
$nama_lengkap = $_POST['nama_lengkap'] ?? '';
$email = $_POST['email'] ?? '';
$alamat = $_POST['alamat'] ?? '';
$no_hp = $_POST['no_hp'] ?? '';
$action = $_POST['action'] ?? 'create'; // 'create' or 'edit'
$foto = '';
$upload_success = true;

// Handle upload foto
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $foto = 'profile_' . $id_user . '_' . time() . '.' . $ext;
    $upload_dir = '../../uploads/profiles/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $upload_dir . $foto)) {
        $upload_success = false;
        error_log("Failed to upload profile photo for user ID: " . $id_user);
    }
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$upload_success) {
    $conn->close();
    header('Location: profile_view.php?status=error&message=Gagal mengunggah foto profil.');
    exit;
}

// Check if profile already exists
$stmt_check = $conn->prepare('SELECT id FROM profile WHERE id_user = ?');
$stmt_check->bind_param('i', $id_user);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    // Update existing profile
    $stmt_check->close();
    if ($foto) {
        $stmt = $conn->prepare('UPDATE profile SET nama_lengkap=?, email=?, alamat=?, no_hp=?, foto=? WHERE id_user=?');
        $stmt->bind_param('sssssi', $nama_lengkap, $email, $alamat, $no_hp, $foto, $id_user);
    } else {
        $stmt = $conn->prepare('UPDATE profile SET nama_lengkap=?, email=?, alamat=?, no_hp=? WHERE id_user=?');
        $stmt->bind_param('ssssi', $nama_lengkap, $email, $alamat, $no_hp, $id_user);
    }
    if ($stmt->execute()) {
        header('Location: profile_view.php?status=success&message=Profil berhasil diperbarui!');
    } else {
        header('Location: profile_view.php?status=error&message=Gagal memperbarui profil.');
    }
    $stmt->close();
} else {
    // Insert new profile
    $stmt_check->close();
    $stmt = $conn->prepare('INSERT INTO profile (id_user, nama_lengkap, email, alamat, no_hp, foto) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('isssss', $id_user, $nama_lengkap, $email, $alamat, $no_hp, $foto);
    if ($stmt->execute()) {
        header('Location: profile_view.php?status=success&message=Profil berhasil dibuat!');
    } else {
        header('Location: profile_view.php?status=error&message=Gagal membuat profil.');
    }
    $stmt->close();
}
$conn->close();
exit;
