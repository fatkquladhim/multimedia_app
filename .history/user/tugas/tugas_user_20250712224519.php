<?php
session_start();
require_once '../../includes/db_config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Query daftar tugas
$query = "SELECT * FROM tugas WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Tugas</title>
    <style>
        .task-container { max-width: 600px; margin: 40px auto; }
        .task-item { border: 1px solid #ccc; padding: 16px; margin-bottom: 8px; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="task-container">
        <h2>Daftar Tugas</h2>
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while ($task = mysqli_fetch_assoc($result)): ?>
                <div class="task-item">
                    <strong>Judul:</strong> <?php echo htmlspecialchars($task['judul']); ?><br>
                    <strong>Deskripsi:</strong> <?php echo htmlspecialchars($task['deskripsi']); ?><br>
                    <strong>Deadline:</strong> <?php echo htmlspecialchars($task['deadline']); ?><br>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Tidak ada tugas.</p>
        <?php endif; ?>
    </div>
</body>
</html>