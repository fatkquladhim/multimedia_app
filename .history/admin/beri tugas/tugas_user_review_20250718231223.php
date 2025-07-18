<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!isset($_GET['id_tugas']) || !is_numeric($_GET['id_tugas'])) {
    header('Location: tugas_selesai_riwayat.php?status=error&message=ID tugas tidak valid.');
    exit;
}

$id_tugas = $_GET['id_tugas'];

// Ambil detail tugas dan jawaban
$query = "SELECT 
    t.id as tugas_id,
    t.judul,
    t.deskripsi,
    t.deadline,
    t.status as tugas_status,
    u.username as penerima_username,
    u.nama_lengkap as penerima_nama_lengkap,
    tj.id as id_jawaban,
    tj.file_jawaban,
    tj.nilai,
    tj.komentar,
    tj.tanggal_submit
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
    echo '<p><a href="tugas_selesai_riwayat.php">Kembali</a></p>';
    $stmt->close();
    $conn->close();
    exit;
}
include '../header.php'; // Path relatif dari 'anggota/'
?>

    <h2>Review Tugas</h2>
    
    <div class="task-details">
        <strong>Judul:</strong> <?php echo htmlspecialchars($tugas['judul']); ?><br>
        <strong>Deskripsi:</strong> <?php echo nl2br(htmlspecialchars($tugas['deskripsi'])); ?><br>
        <strong>Dikerjakan oleh:</strong> <?php echo htmlspecialchars($tugas['penerima_username'] . ' (' . ($tugas['penerima_nama_lengkap'] ?? 'Nama tidak ada') . ')'); ?><br>
        <strong>Deadline:</strong> <?php echo date('d/m/Y', strtotime($tugas['deadline'])); ?><br>
        <strong>Status Tugas:</strong> <?php echo htmlspecialchars($tugas['tugas_status']); ?><br>
        <strong>Tanggal Submit Jawaban:</strong> <?php echo $tugas['tanggal_submit'] ? date('d/m/Y H:i', strtotime($tugas['tanggal_submit'])) : '-'; ?><br>
        <strong>File Jawaban:</strong> 
        <?php if($tugas['file_jawaban']): ?>
            <a href="../../uploads/tugas_jawaban/<?php echo htmlspecialchars($tugas['file_jawaban']); ?>" target="_blank">
                Lihat File
            </a>
        <?php else: ?>
            <span style="color:red">Belum ada file jawaban.</span>
        <?php endif; ?>
    </div>
    
    <?php if (empty($tugas['id_jawaban'])): ?>
        <div class="message warning">Tugas ini belum memiliki jawaban dari user. Tidak dapat memberikan nilai.</div>
        <p><a href="tugas_selesai_riwayat.php">Kembali ke Riwayat Tugas</a></p>
    <?php else: ?>
        <form action="tugas_user_review_store.php" method="POST">
            <input type="hidden" name="id_jawaban" value="<?php echo $tugas['id_jawaban']; ?>">
            <input type="hidden" name="id_tugas" value="<?php echo $tugas['tugas_id']; ?>">
            
            <div>
                <label for="nilai">Nilai (0-100):</label>
                <input type="number" id="nilai" name="nilai" min="0" max="100" value="<?php echo htmlspecialchars($tugas['nilai'] ?? ''); ?>" required>
            </div>
            
            <div>
                <label for="komentar">Komentar:</label>
                <textarea id="komentar" name="komentar" rows="6"><?php echo htmlspecialchars($tugas['komentar'] ?? ''); ?></textarea>
            </div>
            
            <br>
            <button type="submit">Simpan Nilai</button>
            <a href="tugas_selesai_riwayat.php">Kembali ke Riwayat Tugas</a>
        </form>
    <?php endif; ?>
<?php
// Sertakan footer
include '../footer.php'; // Path relatif dari 'anggota/' ke 'includes/'
$conn->close();
$stmt->close();
?>
