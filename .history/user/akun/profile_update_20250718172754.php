<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$id_user = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action === 'update_profile') {
    $nama_lengkap = $_POST['nama_lengkap'] ?? '';
    $email = $_POST['email'] ?? '';

    // Start transaction for atomicity
    $conn->begin_transaction();
    $success = true;

    // Update users table
    $stmt_users = $conn->prepare('UPDATE users SET nama_lengkap = ?, email = ? WHERE id = ?');
    if ($stmt_users) {
        $stmt_users->bind_param('ssi', $nama_lengkap, $email, $id_user);
        if (!$stmt_users->execute()) {
            $success = false;
            error_log("Error updating users table: " . $stmt_users->error);
        }
        $stmt_users->close();
    } else {
        $success = false;
        error_log("Error preparing users update statement: " . $conn->error);
    }

    if ($success) {
        $conn->commit();
        header('Location: profile_settings.php?status=success&message=Profil berhasil diperbarui!');
    } else {
        $conn->rollback();
        header('Location: profile_settings.php?status=error&message=Gagal memperbarui profil. Terjadi kesalahan database.');
    }

} elseif ($action === 'change_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Fetch current hashed password from users table
    $stmt_user = $conn->prepare('SELECT password FROM users WHERE id = ?');
    $stmt_user->bind_param('i', $id_user);
    $stmt_user->execute();
    $stmt_user->bind_result($hashed_password);
    $stmt_user->fetch();
    $stmt_user->close();

    if (!password_verify($current_password, $hashed_password)) {
        header('Location: profile_settings.php?status=error&message=Sandi saat ini salah.');
        $conn->close();
        exit;
    }

    if ($new_password !== $confirm_password) {
        header('Location: profile_settings.php?status=error&message=Konfirmasi sandi baru tidak cocok.');
        $conn->close();
        exit;
    }

    // Hash the new password
    $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password in users table
    $stmt_update_password = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
    if ($stmt_update_password) {
        $stmt_update_password->bind_param('si', $new_hashed_password, $id_user);
        if ($stmt_update_password->execute()) {
            header('Location: profile_settings.php?status=success&message=Sandi berhasil diubah!');
        } else {
            error_log("Error updating password: " . $stmt_update_password->error);
            header('Location: profile_settings.php?status=error&message=Gagal mengubah sandi.');
        }
        $stmt_update_password->close();
    } else {
        error_log("Error preparing password update statement: " . $conn->error);
        header('Location: profile_settings.php?status=error&message=Gagal mengubah sandi.');
    }

} else {
    header('Location: profile_settings.php?status=error&message=Aksi tidak valid.');
}

$conn->close();
exit;
?>
