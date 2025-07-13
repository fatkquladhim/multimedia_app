<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Periksa apakah username sudah ada
    $check_user_stmt = $conn->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
    $check_user_stmt->bind_param('s', $username);
    $check_user_stmt->execute();
    $check_user_stmt->bind_result($user_count);
    $check_user_stmt->fetch();
    $check_user_stmt->close();

    if ($user_count > 0) {
        header('Location: anggota.php?status=error&message=Username sudah terdaftar');
        exit;
    }

    // Create user account first
    $stmt = $conn->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, "user")');
    $stmt->bind_param('ss', $username, $password);
    $stmt->execute();
    $id_user = $conn->insert_id;
    $stmt->close();

    // Validasi id_user
    if (!$id_user) {
        error_log('ID User tidak valid: ' . $conn->error);
        header('Location: anggota.php?status=error&message=Gagal membuat akun pengguna');
        exit;
    }

    // Then create anggota
    $nama = $_POST['nama'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $email = $_POST['email'] ?? '';
    $no_hp = $_POST['no_hp'] ?? '';

    // Proses upload foto
    $foto = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $foto = 'uploads/' . basename($_FILES['foto']['name']);
        move_uploaded_file($_FILES['foto']['tmp_name'], '../../' . $foto);
    }

    $stmt = $conn->prepare('INSERT INTO anggota (nama, foto, alamat, email, no_hp, id_user) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('ssssi', $nama, $foto, $alamat, $email, $no_hp, $id_user);

    if ($stmt->execute()) {
        header('Location: anggota.php?status=success&message=Anggota berhasil ditambahkan');
    } else {
        header('Location: anggota.php?status=error&message=Gagal menambahkan anggota');
    }
    $stmt->close();
}

$conn->close();
?>
