<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Mulai transaction
    $conn->begin_transaction();
    
    try {
        // Ambil detail penyewaan
        $stmt = $conn->prepare("SELECT id_alat, jumlah FROM penyewaan_barang WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $penyewaan = $result->fetch_assoc();
        
        if ($penyewaan) {
            // Kembalikan stok
            $stmt = $conn->prepare("UPDATE alat SET jumlah = jumlah + ? WHERE id = ?");
            $stmt->bind_param("ii", $penyewaan['jumlah'], $penyewaan['id_alat']);
            $stmt->execute();
            
            // Hapus penyewaan
            $stmt = $conn->prepare("DELETE FROM penyewaan_barang WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            $conn->commit();
            header("Location: penyewaan-barang.php?status=success&message=Penyewaan berhasil dihapus");
        } else {
            throw new Exception("Penyewaan tidak ditemukan");
        }
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: penyewaan-barang.php?status=error&message=" . $e->getMessage());
    }
} else {
    header("Location: penyewaan-barang.php?status=error&message=ID penyewaan tidak valid");
}

$conn->close();
