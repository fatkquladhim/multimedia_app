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
$conn->close();
?>
                <main class="p-6">
                    <?php
                    if (isset($_GET['status'])) {
                        echo '<div class="message ' . htmlspecialchars($_GET['status']) . '">' . htmlspecialchars($_GET['message']) . '</div>';
                    }
                    ?>

                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h2 class="text-xl font-bold mb-4">Detail Profil</h2>
                        <p class="mb-2"><strong class="text-gray-700">Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                        <p class="mb-2"><strong class="text-gray-700">Nama Lengkap:</strong> <?php echo htmlspecialchars($nama_lengkap ?? '-'); ?></p>
                        <p class="mb-2"><strong class="text-gray-700">Email:</strong> <?php echo htmlspecialchars($email ?? '-'); ?></p>
                        <p class="mb-2"><strong class="text-gray-700">Alamat:</strong> <?php echo htmlspecialchars($alamat ?? '-'); ?></p>
                        <p class="mb-2"><strong class="text-gray-700">No HP:</strong> <?php echo htmlspecialchars($no_hp ?? '-'); ?></p>
                        <?php if ($foto) { ?>
                            <p class="mb-4"><strong class="text-gray-700">Foto Profil:</strong><br><img src="../../uploads/profiles/<?php echo htmlspecialchars($foto); ?>" alt="Foto Profil" class="w-32 h-32 object-cover rounded-full mt-2"></p>
                        <?php } else { ?>
                            <p class="mb-4"><strong class="text-gray-700">Foto Profil:</strong> -</p>
                        <?php } ?>

                        <div class="mt-6">
                            <?php if ($profile_exists): ?>
                                <a href="profile_edit.php?action=edit" class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition duration-300">Edit Profil</a>
                            <?php else: ?>
                                <a href="profile_create.php?action=create" class="inline-block bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg transition duration-300">Buat Profil</a>
                            <?php endif; ?>
                        </div>
                    </div>
                 <?php
    // Sertakan footer
    include '../footer.php'; // Path relatif dari 'anggota/' ke 'includes/'
    $conn->close();
    ?>