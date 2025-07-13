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
    
    // Get user_id first
    $stmt = $conn->prepare("SELECT id_user FROM anggota WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row && $row['id_user']) {
        // Hapus data terkait di tabel izin_malam
        $stmt = $conn->prepare("DELETE FROM izin_malam WHERE id_anggota = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        
        // Delete from anggota first (because of foreign key)
        $stmt = $conn->prepare("DELETE FROM anggota WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        
        // Then delete the user account
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param('i', $row['id_user']);
        $stmt->execute();
        
        header('Location: anggota.php?status=success&message=Anggota berhasil dihapus');
    } else {
        header('Location: anggota.php?status=error&message=Anggota tidak ditemukan');
    }
}

$conn->close();
?>
