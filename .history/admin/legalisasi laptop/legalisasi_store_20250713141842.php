<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Proses persetujuan/penolakan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = $_POST['id'];
    $action = $_POST['action'];
    
    if ($action === 'approve' || $action === 'reject') {
        $status = ($action === 'approve') ? 'disetujui' : 'ditolak';
        
        $stmt = $conn->prepare("UPDATE legalisasi_laptop SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        
        if ($stmt->execute()) {
            header("Location: legalisasi_list.php?status=success&message=Status legalisasi berhasil diubah");
        } else {
            header("Location: legalisasi_list.php?status=error&message=Gagal mengubah status legalisasi");
        }
        
        $stmt->close();
        $conn->close();
        exit;
    }
}

// Proses penyimpanan legalisasi baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    $id_anggota = $_POST['id_anggota'];
    $merk = $_POST['merk'];
    $tipe = $_POST['tipe'];
    $serial_number = $_POST['serial_number'];
    $status = 'pending';
    
    // Upload file
    $file_bukti = '';
    if (isset($_FILES['file_bukti']) && $_FILES['file_bukti']['error'] == 0) {
        $upload_dir = '../../uploads/legalisasi/';
        
        // Buat direktori jika belum ada
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate nama file unik
        $file_extension = pathinfo($_FILES['file_bukti']['name'], PATHINFO_EXTENSION);
        $file_bukti = uniqid() . '.' . $file_extension;
        $target_path = $upload_dir . $file_bukti;
        
        // Pindahkan file
        if (move_uploaded_file($_FILES['file_bukti']['tmp_name'], $target_path)) {
            // File berhasil diupload
            $stmt = $conn->prepare("INSERT INTO legalisasi_laptop (id_anggota, merk, tipe, serial_number, status, file_bukti) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssss", $id_anggota, $merk, $tipe, $serial_number, $status, $file_bukti);
            
            if ($stmt->execute()) {
                header("Location: legalisasi_list.php?status=success&message=Legalisasi berhasil ditambahkan");
            } else {
                unlink($target_path); // Hapus file jika gagal insert ke database
                header("Location: legalisasi_create.php?status=error&message=Gagal menyimpan data legalisasi");
            }
            
            $stmt->close();
        } else {
            header("Location: legalisasi_create.php?status=error&message=Gagal mengupload file");
        }
    } else {
        header("Location: legalisasi_create.php?status=error&message=File bukti harus diupload");
    }
}

$conn->close();
?>
