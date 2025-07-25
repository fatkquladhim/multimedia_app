<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_jawaban = $_POST['id_jawaban'] ?? null; // id dari tabel tugas_jawaban
    $id_tugas = $_POST['id_tugas'] ?? null; // id dari tabel tugas
    $nilai = $_POST['nilai'] ?? null;
    $komentar = trim($_POST['komentar'] ?? '');
    $action = $_POST['action'] ?? 'nilai'; // Tambahkan ini, default 'nilai'
    
    // Validasi input
    if (!is_numeric($id_jawaban) || !is_numeric($id_tugas) || !is_numeric($nilai) || $nilai < 0 || $nilai > 100) {
        header("Location: tugas_selesai_riwayat.php?status=error&message=Data nilai tidak valid.");
        exit;
    }

    // Update nilai dan komentar di tabel tugas_jawaban
    $stmt_jawaban = $conn->prepare("UPDATE tugas_jawaban SET nilai = ?, komentar = ? WHERE id = ?");
    $stmt_jawaban->bind_param("isi", $nilai, $komentar, $id_jawaban);
    
    if ($stmt_jawaban->execute()) {
        // Setelah berhasil memberi nilai, update status tugas menjadi 'diperiksa'
        $stmt_tugas = $conn->prepare("UPDATE tugas SET status = 'selesai' WHERE id = ?");
        $stmt_tugas->bind_param("i", $id_tugas);
        $stmt_tugas->execute();
        $stmt_tugas->close();

        header("Location: tugas_selesai_riwayat.php?status=success&message=Nilai berhasil diberikan dan status tugas diperbarui.");
    } else {
        error_log("Error updating task answer: " . $stmt_jawaban->error);
        header("Location: tugas_selesai_riwayat.php?status=error&message=Gagal memberikan nilai. Silakan coba lagi.");
    }
    
    $stmt_jawaban->close();
} else {
    header("Location: tugas_selesai_riwayat.php"); // Redirect if not a POST request
}
    if ($action == 'tolak') {
        // Validasi untuk aksi tolak
        if (!is_numeric($id_tugas)) {
            header("Location: tugas_selesai_riwayat.php?status=error&message=ID tugas tidak valid untuk penolakan.");
            exit;
        }

        // Update status tugas menjadi 'pending'
        $stmt_tugas = $conn->prepare("UPDATE tugas SET status = 'pending' WHERE id = ?");
        $stmt_tugas->bind_param("i", $id_tugas);

        if ($stmt_tugas->execute()) {
            // Hapus nilai dan komentar dari tugas_jawaban (opsional, tergantung kebutuhan)
            // Jika ingin user submit ulang, nilai dan komentar sebelumnya harus direset
            $stmt_jawaban_reset = $conn->prepare("UPDATE tugas_jawaban SET nilai = NULL, komentar = NULL WHERE id_tugas = ?");
            $stmt_jawaban_reset->bind_param("i", $id_tugas);
            $stmt_jawaban_reset->execute();
            $stmt_jawaban_reset->close();

            header("Location: tugas_selesai_riwayat.php?status=success&message=Tugas berhasil ditolak. Status diubah menjadi pending.");
        } else {
            error_log("Error rejecting task: " . $stmt_tugas->error);
            header("Location: tugas_selesai_riwayat.php?status=error&message=Gagal menolak tugas. Silakan coba lagi.");
        }
        $stmt_tugas->close();

    } else { // Aksi 'nilai'
        // Validasi input untuk nilai
        if (!is_numeric($id_jawaban) || !is_numeric($id_tugas) || !is_numeric($nilai) || $nilai < 0 || $nilai > 100) {
            header("Location: tugas_selesai_riwayat.php?status=error&message=Data nilai tidak valid.");
            exit;
        }

        // Update nilai dan komentar di tabel tugas_jawaban
        $stmt_jawaban = $conn->prepare("UPDATE tugas_jawaban SET nilai = ?, komentar = ? WHERE id = ?");
        $stmt_jawaban->bind_param("isi", $nilai, $komentar, $id_jawaban);

        if ($stmt_jawaban->execute()) {
            // Setelah berhasil memberi nilai, update status tugas menjadi 'selesai'
            $stmt_tugas = $conn->prepare("UPDATE tugas SET status = 'selesai' WHERE id = ?");
            $stmt_tugas->bind_param("i", $id_tugas);
            $stmt_tugas->execute();
            $stmt_tugas->close();

            header("Location: tugas_selesai_riwayat.php?status=success&message=Nilai berhasil diberikan dan status tugas diperbarui.");
        } else {
            error_log("Error updating task answer: " . $stmt_jawaban->error);
            header("Location: tugas_selesai_riwayat.php?status=error&message=Gagal memberikan nilai. Silakan coba lagi.");
        }
        $stmt_jawaban->close();
    }
    
$conn->close();
?>
