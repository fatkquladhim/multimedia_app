<?php
session_start();
require_once '../../includes/db_config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Query riwayat tugas
$query = "SELECT * FROM tugas_kirim WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Tugas</title>
    <style>
        .history-container { max-width: 600px; margin: 40px auto; }
        .history-item { border: 1px solid #ccc; padding: 16px; margin-bottom: 8px; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="history-container">
        <h2>Riwayat Tugas</h2>
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while ($task = mysqli_fetch_assoc($result)): ?>
                <div class="history-item">
                    <strong>Judul:</strong> <?php echo htmlspecialchars($task['judul']); ?><br>
                    <strong>Deskripsi:</strong> <?php echo htmlspecialchars($task['deskripsi']); ?><br>
                    <strong>File:</strong> <a href="../../uploads/tugas_jawaban/<?php echo htmlspecialchars($task['file']); ?>" target="_blank">Download</a><br>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Belum ada tugas yang dikirim.</p>
        <?php endif; ?>
    </div>
</body>
</html>