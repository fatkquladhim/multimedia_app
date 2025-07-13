<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Ambil daftar tugas yang sudah selesai
$query = "SELECT 
    t.id,
    t.judul,
    t.deskripsi,
    t.deadline,
    t.status,
    u.username as penerima,
    tj.file_jawaban,
    tj.nilai,
    tj.komentar
FROM tugas t
LEFT JOIN users u ON t.id_penerima_tugas = u.id
LEFT JOIN tugas_jawaban tj ON t.id = tj.id_tugas
WHERE t.status = 'selesai'
ORDER BY t.deadline DESC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Riwayat Tugas Selesai</title>
</head>
<body>
    <h2>Riwayat Tugas Selesai</h2>
    
    <table border="1">
        <thead>
            <tr>
                <th>No</th>
                <th>Judul</th>
                <th>User</th>
                <th>Deadline</th>
                <th>File Jawaban</th>
                <th>Nilai</th>
                <th>Komentar</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            while($row = $result->fetch_assoc()): 
            ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo htmlspecialchars($row['judul']); ?></td>
                <td><?php echo htmlspecialchars($row['penerima']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($row['deadline'])); ?></td>
                <td>
                    <?php if($row['file_jawaban']): ?>
                        <a href="../../uploads/tugas_jawaban/<?php echo $row['file_jawaban']; ?>" target="_blank">
                            Lihat File
                        </a>
                    <?php endif; ?>
                </td>
                <td><?php echo $row['nilai'] ?? 'Belum dinilai'; ?></td>
                <td><?php echo htmlspecialchars($row['komentar'] ?? '-'); ?></td>
                <td>
                    <?php if(!$row['nilai']): ?>
                        <a href="tugas_user_review.php?id=<?php echo $row['id']; ?>">Beri Nilai</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <br>
    <a href="../dashboard.php">Kembali ke Dashboard</a>
</body>
</html>

<?php $conn->close(); ?>
