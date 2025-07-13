<?php
session_start();
require_once '../../includes/db_config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}
$user_id = $_SESSION['user_id'];

// Ambil data dari form

$nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
$no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);

// Upload foto profil jika ada

$foto = '';
if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
    $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
    $target = '../../uploads/profiles/' . $filename;
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $target)) {
        $foto = $filename;
    }
}

if (isset($_POST['edit'])) {
    // Update profil
    $update_query = "UPDATE profile SET nama_lengkap='$nama_lengkap', email='$email', alamat='$alamat', no_hp='$no_hp'";
    if ($foto) {
        $update_query .= ", foto='$foto'";
    }
    $update_query .= " WHERE id_user='$user_id'";
    mysqli_query($conn, $update_query);
    header('Location: profile_view.php');
    exit();
} else {
    // Insert profil baru
    $insert_query = "INSERT INTO profile (id_user, nama_lengkap, email, alamat, no_hp, foto) VALUES ('$user_id', '$nama_lengkap', '$email', '$alamat', '$no_hp', '$foto') ON DUPLICATE KEY UPDATE nama_lengkap='$nama_lengkap', email='$email', alamat='$alamat', no_hp='$no_hp'";
    if ($foto) {
        $insert_query .= ", foto='$foto'";
    }
    $insert_query .= ";";
    mysqli_query($conn, $insert_query) or die(mysqli_error($conn));
    header('Location: profile_view.php');
    exit();
}
?>
