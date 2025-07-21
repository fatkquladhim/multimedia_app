<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$id_user = $_SESSION['user_id'];
$action = 'edit'; // Always 'edit' for this file

$nama_lengkap = '';
$email = '';
$alamat = '';
$no_hp = '';
$current_foto = '';

// Logic to fetch existing profile data
$stmt = $conn->prepare('SELECT nama_lengkap, email, alamat, no_hp, foto FROM profile WHERE id_user = ?');
$stmt->bind_param('i', $id_user);
$stmt->execute();
$stmt->bind_result($nama_lengkap, $email, $alamat, $no_hp, $current_foto);
$stmt->fetch();
$stmt->close();

include '../header_beckend.php';
include '../header.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .form-input {
            @apply mt-1 block w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 ease-in-out;
        }
        .btn-primary {
            @apply bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md shadow-md transition duration-200 ease-in-out;
        }
        .btn-secondary {
            @apply bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded-md shadow-md transition duration-200 ease-in-out;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center py-10">

    <div class="container mx-auto px-4">
        <div class="bg-white p-8 rounded-lg shadow-xl max-w-md mx-auto">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Edit Profil</h2>

            <form method="post" action="profile_store.php" enctype="multipart/form-data" class="space-y-5">
                <input type="hidden" name="action" value="<?php echo $action; ?>">

                <div>
                    <label for="nama_lengkap" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Nama Lengkap" value="<?php echo htmlspecialchars($nama_lengkap); ?>" required class="form-input">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required class="form-input">
                </div>
                <div>
                    <label for="alamat" class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                    <input type="text" id="alamat" name="alamat" placeholder="Alamat" value="<?php echo htmlspecialchars($alamat); ?>" class="form-input">
                </div>
                <div>
                    <label for="no_hp" class="block text-sm font-medium text-gray-700 mb-1">No HP</label>
                    <input type="text" id="no_hp" name="no_hp" placeholder="No HP" value="<?php echo htmlspecialchars($no_hp); ?>" class="form-input">
                </div>
                <div>
                    <label for="foto" class="block text-sm font-medium text-gray-700 mb-1">Foto Profil</label>
                    <input type="file" id="foto" name="foto" accept="image/*" class="form-input">
                    <?php if ($current_foto): ?>
                        <p class="text-sm text-gray-600 mt-2 flex items-center">
                            Foto saat ini:
                            <img src="../../uploads/profiles/<?php echo htmlspecialchars($current_foto); ?>" alt="Current Photo" class="w-16 h-16 object-cover rounded-full ml-3 border-2 border-gray-200">
                        </p>
                    <?php endif; ?>
                </div>
                <div class="flex space-x-4 mt-6 justify-end">
                    <button type="submit" class="btn-primary">Update Profil</button>
                    <a href="profile_view.php" class="btn-secondary flex items-center justify-center">Batal</a>
                </div>
            </form>
        </div>
    </div>
</main>
<?php
// Sertakan footer
include '../footer.php';
$conn->close();
?>
