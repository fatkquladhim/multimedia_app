<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$stmt = $conn->prepare('SELECT nama_lengkap, email, alamat, no_hp, foto FROM profile WHERE id_user = ?');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($nama_lengkap, $email, $alamat, $no_hp, $foto);
$stmt->fetch();
$stmt->close();
$profile_exists = !empty($nama_lengkap); // Check if profile data exists
include '../header_beckend.php';
include '../header.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Profil</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .message {
            @apply p-3 mb-4 rounded-md text-sm;
        }
        .message.success {
            @apply bg-green-100 text-green-700;
        }
        .message.error {
            @apply bg-red-100 text-red-700;
        }
        .btn-action {
            @apply inline-block py-2 px-4 rounded-lg font-bold transition duration-300;
        }
        .btn-edit {
            @apply bg-blue-500 hover:bg-blue-600 text-white;
        }
        .btn-create {
            @apply bg-green-500 hover:bg-green-600 text-white;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center py-10">

    <div class="container mx-auto px-4">
        <?php
        if (isset($_GET['status'])) {
            echo '<div class="message ' . htmlspecialchars($_GET['status']) . '">' . htmlspecialchars($_GET['message']) . '</div>';
        }
        ?>

        <div class="bg-white p-8 rounded-lg shadow-xl max-w-md mx-auto text-center">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Detail Profil</h2>

            <?php if ($foto): ?>
                <div class="mb-6">
                    <img src="../../uploads/profiles/<?php echo htmlspecialchars($foto); ?>" alt="Foto Profil" class="w-32 h-32 object-cover rounded-full mx-auto border-4 border-blue-200 shadow-md">
                </div>
            <?php else: ?>
                <div class="mb-6">
                    <div class="w-32 h-32 bg-gray-200 rounded-full mx-auto flex items-center justify-center text-gray-500 text-xs border-4 border-gray-300">
                        Tidak Ada Foto
                    </div>
                </div>
            <?php endif; ?>

            <div class="text-left space-y-3 mb-6">
                <p><strong class="text-gray-700">Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                <p><strong class="text-gray-700">Nama Lengkap:</strong> <?php echo htmlspecialchars($nama_lengkap ?? '-'); ?></p>
                <p><strong class="text-gray-700">Email:</strong> <?php echo htmlspecialchars($email ?? '-'); ?></p>
                <p><strong class="text-gray-700">Alamat:</strong> <?php echo htmlspecialchars($alamat ?? '-'); ?></p>
                <p><strong class="text-gray-700">No HP:</strong> <?php echo htmlspecialchars($no_hp ?? '-'); ?></p>
            </div>

            <div class="mt-6">
                <?php if ($profile_exists): ?>
                    <a href="profile_edit.php" class="btn-action btn-edit">Edit Profil</a>
                <?php else: ?>
                    <a href="profile_create.php?action=create" class="btn-action btn-create">Buat Profil</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<?php
// Sertakan footer
include '../footer.php';
$conn->close();
?>
