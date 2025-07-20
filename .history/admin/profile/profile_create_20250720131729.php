<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$id_user = $_SESSION['user_id'];
$action = $_GET['action'] ?? 'create'; // 'create' or 'edit'

$nama_lengkap = '';
$email = '';
$alamat = '';
$no_hp = '';
$current_foto = '';

if ($action === 'edit') {
    $stmt = $conn->prepare('SELECT nama_lengkap, email, alamat, no_hp, foto FROM profile WHERE id_user = ?');
    $stmt->bind_param('i', $id_user);
    $stmt->execute();
    $stmt->bind_result($nama_lengkap, $email, $alamat, $no_hp, $current_foto);
    $stmt->fetch();
    $stmt->close();
}
include '../header_beckend.php';
include '../header.php';
?>
<main class="p-6">
    <div class="bg-white p-6 rounded-lg shadow-md max-w-md mx-auto">
        <form method="post" action="profile_store.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?php echo $action; ?>">

            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap</label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Nama Lengkap" value="<?php echo htmlspecialchars($nama_lengkap); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <label for="alamat">Alamat</label>
                <input type="text" id="alamat" name="alamat" placeholder="Alamat" value="<?php echo htmlspecialchars($alamat); ?>">
            </div>
            <div class="form-group">
                <label for="no_hp">No HP</label>
                <input type="text" id="no_hp" name="no_hp" placeholder="No HP" value="<?php echo htmlspecialchars($no_hp); ?>">
            </div>
            <div class="form-group">
                <label for="foto">Foto Profil</label>
                <input type="file" id="foto" name="foto" accept="image/*">
                <?php if ($current_foto): ?>
                    <p class="text-sm text-gray-600 mt-2">Foto saat ini: <img src="../../uploads/profiles/<?php echo htmlspecialchars($current_foto); ?>" alt="Current Photo" class="w-20 h-20 object-cover rounded-full inline-block ml-2"></p>
                <?php endif; ?>
            </div>
            <div class="flex space-x-4 mt-6">
                <button type="submit" class="btn btn-primary"><?php echo ($action === 'edit' ? 'Update' : 'Simpan'); ?></button>
                <a href="profile_view.php" class="btn btn-secondary flex items-center justify-center">Batal</a>
            </div>
        </form>
    </div>
    <?php
    // Sertakan footer
    include '../footer.php'; // Path relatif dari 'anggota/' ke 'includes/'
    $conn->close();
    ?>