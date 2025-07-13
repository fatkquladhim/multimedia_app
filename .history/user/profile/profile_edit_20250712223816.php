
<?php
session_start();
require_once '../../includes/db_config.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = '$user_id' LIMIT 1";
$result = mysqli_query($conn, $query);
if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
} else {
    echo "User tidak ditemukan.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil</title>
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
        <h2>Edit Profil</h2>
        <form action="profile_store.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="id_user">ID User</label>
                <input type="text" name="id_user" id="id_user" value="<?php echo htmlspecialchars($user['id_user']); ?>" required>
            </div>
            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" id="nama_lengkap" value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="alamat">Alamat</label>
                <input type="text" name="alamat" id="alamat" value="<?php echo htmlspecialchars($user['alamat']); ?>" required>
            </div>
            <div class="form-group">
                <label for="no_hp">No. HP</label>
                <input type="text" name="no_hp" id="no_hp" value="<?php echo htmlspecialchars($user['no_hp']); ?>" required>
            </div>
            <div class="form-group">
                <label for="foto">Foto Profil</label>
                <?php if (!empty($user['foto'])): ?>
                    <img src="../../uploads/profiles/<?php echo htmlspecialchars($user['foto']); ?>" width="80" style="display:block;margin-bottom:8px;">
                <?php endif; ?>
                <input type="file" name="foto" id="foto" accept="image/*">
            </div>
            <input type="hidden" name="edit" value="1">
            <button type="submit" name="submit">Update</button>
        </form>
    </div>
</body>
</html>
