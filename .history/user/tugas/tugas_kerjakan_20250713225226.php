<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$id_tugas = $_GET['id'] ?? null;
if (!$id_tugas) {
    header('Location: tugas_user.php?status=error&message=ID tugas tidak valid');
    exit;
}

$stmt = $conn->prepare('SELECT judul, deskripsi, deadline FROM tugas WHERE id = ? AND id_penerima_tugas = ?');
$stmt->bind_param('ii', $id_tugas, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$tugas = $result->fetch_assoc();

if (!$tugas) {
    header('Location: tugas_user.php?status=error&message=Tugas tidak ditemukan');
    exit;
}

$stmt->close();
?>
<h2>Kerjakan Tugas</h2>
<p><strong>Judul:</strong> <?php echo htmlspecialchars($tugas['judul']); ?></p>
<p><strong>Deskripsi:</strong> <?php echo htmlspecialchars($tugas['deskripsi']); ?></p>
<p><strong>Deadline:</strong> <?php echo htmlspecialchars($tugas['deadline']); ?></p>
<form method="post" action="tugas_kirim.php" enctype="multipart/form-data">
    <input type="hidden" name="id_tugas" value="<?php echo $id_tugas; ?>">
    <textarea name="jawaban" placeholder="Jawaban Anda" required></textarea><br>
    <input type="file" name="file_jawaban" accept="application/pdf,image/jpeg,image/png"><br>
    <button type="submit">Kirim Jawaban</button>
</form>
<p><a href="tugas_user.php">Kembali ke Tugas Saya</a></p>
<?php
$conn->close();
?>
