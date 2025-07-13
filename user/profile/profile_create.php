
<?php
session_start();
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
    <title>Buat Profil</title>
    <style>
        .form-container { max-width: 400px; margin: 40px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; }
        .form-group { margin-bottom: 12px; }
        label { display: block; margin-bottom: 4px; }
        input[type="text"], input[type="email"], input[type="file"] { width: 100%; padding: 8px; }
        button { padding: 8px 16px; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Buat Profil</h2>
        <form action="profile_store.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="id_user">ID User</label>
                <input type="text" name="id_user" id="id_user" required>
            </div>
            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" id="nama_lengkap" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="alamat">Alamat</label>
                <input type="text" name="alamat" id="alamat" required>
            </div>
            <div class="form-group">
                <label for="no_hp">No. HP</label>
                <input type="text" name="no_hp" id="no_hp" required>
            </div>
            <div class="form-group">
                <label for="foto">Foto Profil</label>
                <input type="file" name="foto" id="foto" accept="image/*">
            </div>
            <button type="submit" name="submit">Simpan</button>
        </form>
    </div>
</body>
</html>
