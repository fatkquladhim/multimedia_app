<?php
session_start();
require_once '../../includes/db_config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirim Tugas</title>
    <style>
        .form-container { max-width: 400px; margin: 40px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; }
        .form-group { margin-bottom: 12px; }
        label { display: block; margin-bottom: 4px; }
        input[type="text"], textarea, input[type="file"] { width: 100%; padding: 8px; }
        button { padding: 8px 16px; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Kirim Tugas</h2>
        <form action="tugas_kirim_store.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="judul">Judul Tugas</label>
                <input type="text" name="judul" id="judul" required>
            </div>
            <div class="form-group">
                <label for="deskripsi">Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi" required></textarea>
            </div>
            <div class="form-group">
                <label for="file">File Tugas</label>
                <input type="file" name="file" id="file" accept="application/pdf,application/msword,image/*">
            </div>
            <button type="submit" name="submit">Kirim</button>
        </form>
    </div>
</body>
</html>