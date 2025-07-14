<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}
require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$id_user = $_SESSION['user_id'];
$id_tugas = $_GET['id'] ?? null;

if (!$id_tugas) {
    header('Location: tugas_user.php?status=error&message=ID tugas tidak valid.');
    exit;
}

// Ambil detail tugas dan cek apakah sudah dikerjakan
$stmt = $conn->prepare('
    SELECT 
        t.id, 
        t.judul, 
        t.deskripsi, 
        t.deadline, 
        t.status,
        tj.id as jawaban_id,
        tj.file_jawaban
    FROM tugas t 
    LEFT JOIN tugas_jawaban tj ON t.id = tj.id_tugas AND tj.id_user = ?
    WHERE t.id = ? AND t.id_penerima_tugas = ?
');
$stmt->bind_param('iii', $id_user, $id_tugas, $id_user);
$stmt->execute();
$result = $stmt->get_result();
$tugas = $result->fetch_assoc();
$stmt->close();

if (!$tugas) {
    header('Location: tugas_user.php?status=error&message=Tugas tidak ditemukan atau bukan tugas Anda.');
    exit;
}

// Jika tugas sudah dikerjakan, redirect atau tampilkan pesan
if ($tugas['jawaban_id']) {
    header('Location: tugas_user.php?status=info&message=Tugas ini sudah Anda kerjakan.');
    exit;
}

// Jika deadline sudah lewat
if (strtotime($tugas['deadline']) < strtotime(date('Y-m-d'))) {
    header('Location: tugas_user.php?status=error&message=Tugas ini sudah melewati deadline.');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kerjakan Tugas</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .task-details { background-color: #f9f9f9; border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .task-details strong { display: inline-block; width: 100px; }
        form div { margin-bottom: 10px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="file"] { margin-top: 5px; }
        button { padding: 10px 15px; background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #218838; }
        a { text-decoration: none; color: #007bff; margin-left: 10px; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h2>Kerjakan Tugas</h2>
    
    <div class="task-details">
        <strong>Judul:</strong> <?php echo htmlspecialchars($tugas['judul']); ?><br>
        <strong>Deskripsi:</strong> <?php echo nl2br(htmlspecialchars($tugas['deskripsi'])); ?><br>
        <strong>Deadline:</strong> <?php echo date('d/m/Y', strtotime($tugas['deadline'])); ?><br>
    </div>

    <form method="post" action="tugas_kirim_store.php" enctype="multipart/form-data">
        <input type="hidden" name="id_tugas" value="<?php echo $id_tugas; ?>">
        <div>
            <label for="file_jawaban">Upload File Jawaban (PDF/JPG/PNG, Max 5MB):</label>
            <input type="file" id="file_jawaban" name="file_jawaban" accept="application/pdf,image/jpeg,image/png" required>
            <small>Ukuran file maksimal 5MB.</small>
        </div>
        <br>
        <button type="submit">Kirim Jawaban</button>
        <a href="tugas_user.php">Kembali ke Tugas Saya</a>
    </form>
</body>
</html>

<?php $conn->close(); ?>
