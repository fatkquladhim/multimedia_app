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
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Akun</title>
    <!-- Tailwind CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Custom styles if needed, though Tailwind should cover most */
        .form-input {
            @apply mt-1 block w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 ease-in-out;
        }
        .btn-primary {
            @apply bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition duration-200 ease-in-out;
        }
        .alert {
            @apply p-4 mb-4 rounded-lg text-sm;
        }
        .alert-success {
            @apply bg-green-100 text-green-700;
        }
        .alert-error {
            @apply bg-red-100 text-red-700;
        }
        .glass-header {
            background: linear-gradient(135deg, rgba(30, 58, 138, 0.9) 0%, rgba(59, 130, 246, 0.9) 100%);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .glass-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%) translateY(-100%) rotate(45deg);
            }

            100% {
                transform: translateX(100%) translateY(100%) rotate(45deg);
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center py-10">

    <div class="container mx-auto px-4 flex justify-center">
        <div class="max-w-xl w-full bg-white rounded-xl shadow-lg p-8 space-y-8">
            <h2 class="text-3xl font-extrabold text-center text-gray-900 mb-8">Pengaturan Akun</h2>

            <?php
            // Display status messages
            if (isset($_GET['status']) && isset($_GET['message'])) {
                $status = htmlspecialchars($_GET['status']);
                $message = htmlspecialchars($_GET['message']);
                echo '<div class="alert ' . ($status === 'success' ? 'alert-success' : 'alert-error') . '">';
                echo $message;
                echo '</div>';
            }
            ?>

            <!-- Form untuk mengubah Nama Lengkap dan Email -->
            <div class="bg-gray-50 p-6 rounded-lg shadow-inner flex-colomn justify-center align-center">
                <h3 class="text-xl font-bold text-gray-800 mb-5">Update Profil</h3>
                <form method="post" action="profile_update.php" class="space-y-5">
                    <input type="hidden" name="action" value="update_profile">
                    <div>
                        <label for="nama_lengkap" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($nama_lengkap); ?>" required class="form-input">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required class="form-input">
                    </div>
                    <div class="flex justify-center pt-4">
                        <button type="submit" class="login-btn w-full py-2 font-semibold rounded-full focus:outline-none glass-header px-3 py-2 text-white relative z-10">Update Profil</button>
                    </div>
                </form>
            </div>

            <div class="border-t border-gray-200 my-8"></div>

            <!-- Form untuk mengubah Sandi -->
            <div class="bg-gray-50 p-6 rounded-lg shadow-inner flex-colomn justify-center align-center">
                <h3 class="text-xl font-bold text-gray-800 mb-5">Ubah Sandi</h3>
                <form method="post" action="profile_update.php" class="space-y-5">
                    <input type="hidden" name="action" value="change_password">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Sandi Saat Ini</label>
                        <input type="password" id="current_password" name="current_password" required class="form-input">
                    </div>
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">Sandi Baru</label>
                        <input type="password" id="new_password" name="new_password" required class="form-input">
                    </div>
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Sandi Baru</label>
                        <input type="password" id="confirm_password" name="confirm_password" required class="form-input">
                    </div>
                    <div class="flex justify-center pt-4">
                        <button type="submit" class="login-btn w-full py-2 font-semibold rounded-full focus:outline-none glass-header px-3 py-2 text-white relative z-10">Ubah Sandi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
           <footer  class="flex justify-center">
                    <p class="text-white font-bold text-lg ">
                         Powered by <span class="text-blue-800">Media Tech Annur2Almurtadlo</span>
                    </p>
                </footer>
<?php
include '../footer.php'; // Path relatif dari 'anggota/' ke 'includes/'
$conn->close();
?>
