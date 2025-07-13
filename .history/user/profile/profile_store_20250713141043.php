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
$foto = '';

// Handle upload foto
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $foto = 'profile_' . $id_user . '_' . time() . '.' . $ext;
    move_uploaded_file($_FILES['foto']['tmp_name'], '../../uploads/profiles/' . $foto);
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
// Cek apakah profil sudah ada
$stmt = $conn->prepare('SELECT id FROM profile WHERE id_user = ?');
$stmt->bind_param('i', $id_user);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    // Update
    $stmt->close();
    if ($foto) {
        $stmt = $conn->prepare('UPDATE profile SET nama_lengkap=?, email=?, alamat=?, no_hp=?, foto=? WHERE id_user=?');
        $stmt->bind_param('sssssi', $nama_lengkap, $email, $alamat, $no_hp, $foto, $id_user);
    } else {
        $stmt = $conn->prepare('UPDATE profile SET nama_lengkap=?, email=?, alamat=?, no_hp=? WHERE id_user=?');
        $stmt->bind_param('ssssi', $nama_lengkap, $email, $alamat, $no_hp, $id_user);
    }
    $stmt->execute();
    $stmt->close();
} else {
    // Insert
    $stmt->close();
    $stmt = $conn->prepare('INSERT INTO profile (id_user, nama_lengkap, email, alamat, no_hp, foto) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('isssss', $id_user, $nama_lengkap, $email, $alamat, $no_hp, $foto);
    $stmt->execute();
    $stmt->close();
}
$conn->close();
header('Location: profile_view.php');
exit;
