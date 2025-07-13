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
        // Ambil detail peminjaman
        $stmt = $conn->prepare("SELECT id_alat, jumlah FROM peminjaman_barang WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $peminjaman = $result->fetch_assoc();
        
        if ($peminjaman) {
            // Kembalikan stok
            $stmt = $conn->prepare("UPDATE alat SET jumlah = jumlah + ? WHERE id = ?");
            $stmt->bind_param("ii", $peminjaman['jumlah'], $peminjaman['id_alat']);
            $stmt->execute();
            
            // Hapus peminjaman
            $stmt = $conn->prepare("DELETE FROM peminjaman_barang WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            $conn->commit();
            header("Location: peminjaman-barang.php?status=success&message=Peminjaman berhasil dihapus");
        } else {
            throw new Exception("Peminjaman tidak ditemukan");
        }
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: peminjaman-barang.php?status=error&message=" . $e->getMessage());
    }
} else {
    header("Location: peminjaman-barang.php?status=error&message=ID peminjaman tidak valid");
}

$conn->close();
