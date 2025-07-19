<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$conn->close();
include '../header.php';
?>
                <main class="p-6">
                    <div class="bg-white p-6 rounded-lg shadow-md max-w-md mx-auto">
                        <form method="post" action="profile_store.php" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="<?php echo $action; ?>">

                            <div class="form-group">
                                <label for="nama_lengkap">Nama Lengkap</label>
                                <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Nama Lengkap"  required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" placeholder="Email" required>
                            </div>
                            <div class="form-group">
                                <label for="alamat">Alamat</label>
                                <input type="text" id="alamat" name="alamat" placeholder="Alamat">
                            </div>
                            <div class="form-group">
                                <label for="no_hp">No HP</label>
                                <input type="text" id="no_hp" name="no_hp" placeholder="No HP" >
                            </div>
                            <div class="form-group">
                                <label for="foto">Foto Profil</label>
                                <input type="file" id="foto" name="foto" accept="image/*">
                            </div>
                            <div class="flex space-x-4 mt-6">
                                <button type="submit" class="btn btn-primary">simpan</button>
                                <a href="profile_view.php" class="btn btn-secondary flex items-center justify-center">Batal</a>
                            </div>
                        </form>
                    </div>
                <?php
    // Sertakan footer
    include '../footer.php'; // Path relatif dari 'anggota/' ke 'includes/'
    $conn->close();
    ?>