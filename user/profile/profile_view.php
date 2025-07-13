
<?php
session_start();
require_once '../../includes/db_config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Query data user
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
    <title>Profil Saya</title>
    <style>
        .profile-container { max-width: 400px; margin: 40px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; }
        .profile-img { width: 120px; height: 120px; object-fit: cover; border-radius: 50%; margin-bottom: 16px; }
        .profile-info { margin-bottom: 8px; }
    </style>
</head>
<body>
    <div class="profile-container">
        <h2>Profil Saya</h2>
        <?php if (!empty($user['foto'])): ?>
            <img src="../../uploads/profiles/<?php echo htmlspecialchars($user['foto']); ?>" class="profile-img" alt="Foto Profil">
        <?php else: ?>
            <img src="../../public/assets/imgs/profile-default.png" class="profile-img" alt="Foto Profil">
        <?php endif; ?>
        <div class="profile-info"><strong>ID User:</strong> <?php echo htmlspecialchars($user['id_user']); ?></div>
        <div class="profile-info"><strong>Nama Lengkap:</strong> <?php echo htmlspecialchars($user['nama_lengkap']); ?></div>
        <div class="profile-info"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></div>
        <div class="profile-info"><strong>Alamat:</strong> <?php echo htmlspecialchars($user['alamat']); ?></div>
        <div class="profile-info"><strong>No. HP:</strong> <?php echo htmlspecialchars($user['no_hp']); ?></div>
        <a href="profile_edit.php">Edit Profil</a>
    </div>
</body>
</html>
