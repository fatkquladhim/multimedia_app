<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM izin_nugas WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: izin-nugas.php?status=success&message=Izin berhasil dihapus");
    } else {
        header("Location: izin-nugas.php?status=error&message=Gagal menghapus izin");
    }
    
    $stmt->close();
} else {
    header("Location: izin-nugas.php?status=error&message=ID izin tidak valid");
}

$conn->close();
?>
