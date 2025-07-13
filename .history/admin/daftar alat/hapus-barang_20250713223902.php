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
    
    // Cek apakah alat sedang dipinjam atau disewa
    $check_query = "SELECT COUNT(*) as count FROM peminjaman_barang WHERE id_alat = ? AND status = 'dipinjam'
                   UNION ALL
                   SELECT COUNT(*) FROM penyewaan_barang WHERE id_alat = ? AND status = 'disewa'";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $id, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row1 = $result->fetch_assoc();
    $row2 = $result->fetch_assoc();
    
    if ($row1['count'] > 0 || $row2['count'] > 0) {
        header("Location: daftar-alat.php?status=error&message=Alat tidak bisa dihapus karena sedang dipinjam/disewa");
        exit;
    }
    
    // Jika aman, hapus alat
    $stmt = $conn->prepare("DELETE FROM alat WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: daftar-alat.php?status=success&message=Alat berhasil dihapus");
    } else {
        header("Location: daftar-alat.php?status=error&message=Gagal menghapus alat");
    }
    
    $stmt->close();
} else {
    header("Location: daftar-alat.php?status=error&message=ID alat tidak valid");
}

$conn->close();
?>
