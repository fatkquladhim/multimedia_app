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

    $nama = $_POST['nama'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $email = $_POST['email'] ?? '';
    $no_hp = $_POST['no_hp'] ?? '';

    // Proses upload foto
    $foto = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB

        if (!in_array($_FILES['foto']['type'], $allowed_types)) {
            error_log('File foto tidak valid: Tipe file tidak diizinkan (' . $_FILES['foto']['type'] . ')');
            header('Location: anggota.php?status=error&message=Tipe file tidak diizinkan');
            exit;
        }

        if ($_FILES['foto']['size'] > $max_size) {
            error_log('File foto tidak valid: Ukuran file terlalu besar (' . $_FILES['foto']['size'] . ' bytes)');
            header('Location: anggota.php?status=error&message=Ukuran file terlalu besar');
            exit;
        }

        $foto = basename($_FILES['foto']['name']);
        if (!move_uploaded_file($_FILES['foto']['tmp_name'], '../../uploads/' . $foto)) {
            error_log('Gagal memindahkan file foto: ' . $_FILES['foto']['name']);
            header('Location: anggota.php?status=error&message=Gagal mengunggah foto');
            exit;
        }
    }

    // Validate anggota data
    if (empty($nama) || empty($alamat) || empty($email) || empty($no_hp) || empty($foto)) {
        error_log('Data anggota tidak valid: Nama, alamat, email, no_hp, atau foto kosong');
        header('Location: anggota-entry.php?status=error&message=Data anggota tidak valid');
        exit;
    }

    // Create user account only if anggota data is valid
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

    $stmt = $conn->prepare('INSERT INTO anggota (nama, foto, alamat, email, no_hp, id_user) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('sssssi', $nama, $foto, $alamat, $email, $no_hp, $id_user);

    // Debugging logs for file upload
    if (isset($_FILES['foto'])) {
        error_log('File upload status: ' . $_FILES['foto']['error']);
        error_log('File type: ' . $_FILES['foto']['type']);
        error_log('File size: ' . $_FILES['foto']['size']);
    }

    // Debugging logs for database insertion
    error_log('Nama: ' . $nama);
    error_log('Foto: ' . $foto);
    error_log('Alamat: ' . $alamat);
    error_log('Email: ' . $email);
    error_log('No HP: ' . $no_hp);
    error_log('ID User: ' . $id_user);

    if ($stmt->execute()) {
        error_log('Database insertion successful');
        header('Location: anggota.php?status=success&message=Anggota berhasil ditambahkan');
    } else {
        error_log('Database insertion failed: ' . $stmt->error);
        header('Location: anggota.php?status=error&message=Gagal menambahkan anggota');
    }
    $stmt->close();
}

$conn->close();
?>
