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
    // Ambil nama file bukti
    $stmt = $conn->prepare("SELECT file_bukti FROM legalisasi_laptop WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();

    // Hapus data legalisasi
    $stmt = $conn->prepare("DELETE FROM legalisasi_laptop WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        // Hapus file bukti jika ada
        if ($data && !empty($data['file_bukti'])) {
            $file_path = '../../uploads/legalisasi/' . $data['file_bukti'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        header("Location: legalisasi_list.php?status=success&message=Data legalisasi berhasil dihapus");
    } else {
        header("Location: legalisasi_list.php?status=error&message=Gagal menghapus data legalisasi");
    }
    $stmt->close();
} else {
    header("Location: legalisasi_list.php?status=error&message=ID tidak valid");
}
$conn->close();
?>
