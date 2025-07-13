<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
//     header('Location: tugas_selesai_riwayat.php');
//     exit;
// }

$id_tugas = $_GET['id'];

// Ambil detail tugas dan jawaban
$query = "SELECT 
    t.judul,
    t.deskripsi,
    t.deadline,
    u.username as penerima,
    tj.id as id_jawaban,
    tj.file_jawaban
FROM tugas t
LEFT JOIN users u ON t.id_penerima_tugas = u.id
LEFT JOIN tugas_jawaban tj ON t.id = tj.id_tugas
WHERE t.id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_tugas);
$stmt->execute();
$result = $stmt->get_result();
$tugas = $result->fetch_assoc();

if (!$tugas) {
    echo '<p style="color:red">Tugas tidak ditemukan.</p>';
    exit;
}

// Jika tidak ada jawaban user
if (empty($tugas['id_jawaban'])) {
    echo '<p style="color:orange">Tugas ditemukan, tapi belum ada jawaban dari user.</p>';
    // Tetap tampilkan detail tugas tanpa form penilaian
    echo '<div>';
    echo '<strong>Judul:</strong> ' . htmlspecialchars($tugas['judul']) . '<br>';
    echo '<strong>Dikerjakan oleh:</strong> ' . htmlspecialchars($tugas['penerima']) . '<br>';
    echo '<strong>Deadline:</strong> ' . date('d/m/Y', strtotime($tugas['deadline'])) . '<br>';
    echo '<strong>File Jawaban:</strong> <span style="color:red">Belum ada file jawaban.</span>';
    echo '</div>';
    echo '<p><a href="tugas_selesai_riwayat.php">Kembali</a></p>';
    $stmt->close();
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Review Tugas</title>
</head>
<body>
    <h2>Review Tugas</h2>
    
    <div>
        <strong>Judul:</strong> <?php echo htmlspecialchars($tugas['judul']); ?><br>
        <strong>Dikerjakan oleh:</strong> <?php echo htmlspecialchars($tugas['penerima']); ?><br>
        <strong>Deadline:</strong> <?php echo date('d/m/Y', strtotime($tugas['deadline'])); ?><br>
        <strong>File Jawaban:</strong> 
        <?php if($tugas['file_jawaban']): ?>
            <a href="../../uploads/tugas_jawaban/<?php echo $tugas['file_jawaban']; ?>" target="_blank">
                Lihat File
            </a>
        <?php else: ?>
            Tidak ada file
        <?php endif; ?>
    </div>
    
    <form action="tugas_user_review_store.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $tugas['id_jawaban']; ?>">
        
        <div>
            <label>Nilai (0-100):</label><br>
            <input type="number" name="nilai" min="0" max="100" required>
        </div>
        
        <div>
            <label>Komentar:</label><br>
            <textarea name="komentar" rows="4"></textarea>
        </div>
        
        <br>
        <button type="submit">Simpan Nilai</button>
        <a href="tugas_selesai_riwayat.php">Kembali</a>
    </form>
</body>
</html>

<?php 
$stmt->close();
$conn->close(); 
?>
