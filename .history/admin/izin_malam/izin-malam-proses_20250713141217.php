<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $action = $_POST['action'];
    
    if ($action === 'approve' || $action === 'reject') {
        $status = ($action === 'approve') ? 'disetujui' : 'ditolak';
        
        $stmt = $conn->prepare("UPDATE izin_malam SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        
        if ($stmt->execute()) {
            header("Location: izin-malam.php?status=success&message=Status izin berhasil diubah");
        } else {
            header("Location: izin-malam.php?status=error&message=Gagal mengubah status izin");
        }
        
        $stmt->close();
    }
}

$conn->close();
?>
