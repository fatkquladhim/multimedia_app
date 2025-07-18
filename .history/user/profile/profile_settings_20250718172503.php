<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$id_user = $_SESSION['user_id'];
$nama_lengkap = '';
$email = '';

// Fetch current profile data
$stmt_profile = $conn->prepare('SELECT nama_lengkap, email FROM users WHERE id = ?');
if ($stmt_profile) {
    $stmt_profile->bind_param('i', $id_user);
    $stmt_profile->execute();
    $stmt_profile->bind_result($nama_lengkap, $email);
    $stmt_profile->fetch();
    $stmt_profile->close();
} else {
    // Handle error
    header('Location: profile_settings.php?status=error&message=Gagal mengambil data profil.');
    exit;
}

$conn->close();
include '../header_beckend.php'; 
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pengaturan Akun</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Your CSS styles here */
    </style>
</head>
<body>
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden ">
        <div class="flex h-screen">
            <?php include '../sidebar.php'; ?>

            <div class="flex-1 p-2">
                <?php include '../header_frontend.php'; ?>

                <main class="p-6">
                    <div class="bg-white p-6 rounded-lg shadow-md max-w-md mx-auto">
                        <h2 class="text-xl font-bold mb-4">Pengaturan Akun</h2>

                        <!-- Form untuk mengubah Nama Lengkap dan Email -->
                        <form method="post" action="profile_update.php">
                            <input type="hidden" name="action" value="update_profile">
                            <div class="form-group">
                                <label for="nama_lengkap">Nama Lengkap</label>
                                <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($nama_lengkap); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                            </div>
                            <div class="flex space-x-4 mt-6">
                                <button type="submit" class="btn btn-primary">Update Profil</button>
                            </div>
                        </form>

                        <hr class="my-8">

                        <!-- Form untuk mengubah Sandi -->
                        <form method="post" action="profile_update.php">
                            <input type="hidden" name="action" value="change_password">
                            <div class="form-group">
                                <label for="current_password">Sandi Saat Ini</label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>
                            <div class="form-group">
                                <label for="new_password">Sandi Baru</label>
                                <input type="password" id="new_password" name="new_password" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Konfirmasi Sandi Baru</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                            <div class="flex space-x-4 mt-6">
                                <button type="submit" class="btn btn-primary">Ubah Sandi</button>
                            </div>
                        </form>
                    </div>
                </main>
            </div>
        </div>
    </div>
    <script>
        // Your JavaScript code here
    </script>
</body>
</html>
