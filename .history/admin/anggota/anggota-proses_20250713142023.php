<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create user account first
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt = $conn->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, "user")');
    $stmt->bind_param('ss', $username, $password);
    $stmt->execute();
    $id_user = $conn->insert_id;
    $stmt->close();

    // Then create anggota
    $nama = $_POST['nama'] ?? '';
    $nim = $_POST['nim'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $email = $_POST['email'] ?? '';
    $no_hp = $_POST['no_hp'] ?? '';
    
    // Periksa apakah nim sudah ada
    $check_stmt = $conn->prepare('SELECT COUNT(*) FROM anggota WHERE nim = ?');
    $check_stmt->bind_param('s', $nim);
    $check_stmt->execute();
    $check_stmt->bind_result($count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($count > 0) {
        header('Location: anggota.php?status=error&message=NIM sudah terdaftar');
        exit;
    }

    $stmt = $conn->prepare('INSERT INTO anggota (nama, nim, alamat, email, no_hp, id_user) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('sssssi', $nama, $nim, $alamat, $email, $no_hp, $id_user);
    
    if ($stmt->execute()) {
        header('Location: anggota.php?status=success&message=Anggota berhasil ditambahkan');
    } else {
        header('Location: anggota.php?status=error&message=Gagal menambahkan anggota');
    }
    $stmt->close();
}

$conn->close();
?>
