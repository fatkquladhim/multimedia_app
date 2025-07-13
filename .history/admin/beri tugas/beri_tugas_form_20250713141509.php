<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Ambil daftar user untuk dropdown
$users = $conn->query("SELECT id, username, nama_lengkap FROM users WHERE role = 'user'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Beri Tugas</title>
</head>
<body>
    <h2>Beri Tugas Baru</h2>
    <form action="beri_tugas_store.php" method="POST">
        <div>
            <label>Judul Tugas:</label><br>
            <input type="text" name="judul" required>
        </div>
        
        <div>
            <label>Deskripsi Tugas:</label><br>
            <textarea name="deskripsi" rows="4" required></textarea>
        </div>
        
        <div>
            <label>Deadline:</label><br>
            <input type="date" name="deadline" required>
        </div>
        
        <div>
            <label>Pilih User:</label><br>
            <select name="id_penerima_tugas" required>
                <option value="">Pilih User</option>
                <?php while($user = $users->fetch_assoc()): ?>
                    <option value="<?php echo $user['id']; ?>">
                        <?php echo htmlspecialchars($user['username'] . ' - ' . ($user['nama_lengkap'] ?? 'Nama tidak ada')); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <br>
        <button type="submit">Beri Tugas</button>
        <a href="../dashboard.php">Kembali</a>
    </form>
</body>
</html>

<?php $conn->close(); ?>
