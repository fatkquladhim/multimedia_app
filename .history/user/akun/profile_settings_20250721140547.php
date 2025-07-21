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
include '../header_beckend.php';
include '../header.php';
?>
<div class="container mx-auto mt-10 p-6 bg-white rounded-lg shadow-md max-w-md">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Pengaturan Akun</h2>

        <!-- Form untuk mengubah Nama Lengkap dan Email -->
        <form method="post" action="profile_update.php" class="mb-6">
            <input type="hidden" name="action" value="update_profile">
            <div class="form-group mb-4">
                <label for="nama_lengkap" class="block text-gray-700">Nama Lengkap</label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($nama_lengkap); ?>" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-blue-300">
            </div>
            <div class="form-group mb-4">
                <label for="email" class="block text-gray-700">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-blue-300">
            </div>
            <div class="flex justify-center">
                <button type="submit" class="btn btn-primary bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Update Profil</button>
            </div>
        </form>

        <hr class="my-6">

        <!-- Form untuk mengubah Sandi -->
        <form method="post" action="profile_update.php">
            <input type="hidden" name="action" value="change_password">
            <div class="form-group mb-4">
                <label for="current_password" class="block text-gray-700">Sandi Saat Ini</label>
                <input type="password" id="current_password" name="current_password" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-blue-300">
            </div>
            <div class="form-group mb-4">
                <label for="new_password" class="block text-gray-700">Sandi Baru</label>
                <input type="password" id="new_password" name="new_password" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-blue-300">
            </div>
            <div class="form-group mb-4">
                <label for="confirm_password" class="block text-gray-700">Konfirmasi Sandi Baru</label>
                <input type="password" id="confirm_password" name="confirm_password" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-blue-300">
            </div>
            <div class="flex justify-center">
                <button type="submit" class="btn btn-primary bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Ubah Sandi</button>
            </div>
        </form>
    </div>
    <?php
    // Sertakan footer
    include '../footer.php'; // Path relatif dari 'anggota/' ke 'includes/'
    $conn->close();
    ?>
<!-- 
    <!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Akun</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    
</body>
</html> -->
