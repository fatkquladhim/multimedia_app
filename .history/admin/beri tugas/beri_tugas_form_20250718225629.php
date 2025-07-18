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
include '../header.php'; // Path relatif dari 'anggota/'
?>

<!DOCTYPE html>
<html>
<head>
    <title>Beri Tugas</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form div { margin-bottom: 10px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="date"], textarea, select {
            width: 300px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;
        }
        button { padding: 10px 15px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #45a049; }
        a { text-decoration: none; color: #007bff; margin-left: 10px; }
        a:hover { text-decoration: underline; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <h2>Beri Tugas Baru</h2>

    <?php
    if (isset($_GET['status'])) {
        if ($_GET['status'] == 'success') {
            echo '<div class="message success">' . htmlspecialchars($_GET['message']) . '</div>';
        } else {
            echo '<div class="message error">' . htmlspecialchars($_GET['message']) . '</div>';
        }
    }
    ?>

    <form action="beri_tugas_store.php" method="POST">
        <div>
            <label for="judul">Judul Tugas:</label>
            <input type="text" id="judul" name="judul" required>
        </div>
        
        <div>
            <label for="deskripsi">Deskripsi Tugas:</label>
            <textarea id="deskripsi" name="deskripsi" rows="6" required></textarea>
        </div>
        
        <div>
            <label for="deadline">Deadline:</label>
            <input type="date" id="deadline" name="deadline" required>
        </div>
        
        <div>
            <label for="id_penerima_tugas">Pilih User:</label>
            <select id="id_penerima_tugas" name="id_penerima_tugas" required>
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
        <a href="../dashboard.php">Kembali ke Dashboard</a>
    </form>
</body>
</html>

<?php $conn->close(); ?>
