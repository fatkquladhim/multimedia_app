<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Ambil daftar tugas yang sudah selesai atau diperiksa
// 'diperiksa' status can be added if you want to show tasks that have been graded
$query = "SELECT 
    t.id,
    t.judul,
    t.deskripsi,
    t.deadline,
    t.status,
    u.username as penerima_username,
    u.nama_lengkap as penerima_nama_lengkap,
    tj.file_jawaban,
    tj.nilai,
    tj.komentar,
    tj.id as id_jawaban -- Get the ID of the answer for review
FROM tugas t
LEFT JOIN users u ON t.id_penerima_tugas = u.id
LEFT JOIN tugas_jawaban tj ON t.id = tj.id_tugas
WHERE t.status IN ('selesai') -- Include 'diperiksa' if you use it
ORDER BY t.deadline DESC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Riwayat Tugas Selesai</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        a { text-decoration: none; color: #007bff; }
        a:hover { text-decoration: underline; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <h2>Riwayat Tugas Selesai</h2>

    <?php
    if (isset($_GET['status'])) {
        if ($_GET['status'] == 'success') {
            echo '<div class="message success">' . htmlspecialchars($_GET['message']) . '</div>';
        } else {
            echo '<div class="message error">' . htmlspecialchars($_GET['message']) . '</div>';
        }
    }
    ?>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Judul</th>
                <th>User Penerima</th>
                <th>Deadline</th>
                <th>Status</th>
                <th>File Jawaban</th>
                <th>Nilai</th>
                <th>Komentar</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            if ($result->num_rows > 0):
                while($row = $result->fetch_assoc()): 
            ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo htmlspecialchars($row['judul']); ?></td>
                <td><?php echo htmlspecialchars($row['penerima_username'] ); ?></td>
                <td><?php echo date('d/m/Y', strtotime($row['deadline'])); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td>
                    <?php if($row['file_jawaban']): ?>
                        <a href="../../uploads/tugas_jawaban/<?php echo htmlspecialchars($row['file_jawaban']); ?>" target="_blank">
                            Lihat File
                        </a>
                    <?php else: ?>
                        Tidak ada file
                    <?php endif; ?>
                </td>
                <td><?php echo $row['nilai'] !== null ? htmlspecialchars($row['nilai']) : 'Belum dinilai'; ?></td>
                <td><?php echo htmlspecialchars($row['komentar'] ?? '-'); ?></td>
                <td>
                    <?php 
                    // Only allow grading if there's a submission and it hasn't been graded yet
                    if($row['id_jawaban'] && $row['nilai'] === null): 
                    ?>
                        <a href="tugas_user_review.php?id_tugas=<?php echo $row['id']; ?>">Beri Nilai</a>
                    <?php elseif ($row['id_jawaban'] && $row['nilai'] !== null): ?>
                        <a href="tugas_user_review.php?id_tugas=<?php echo $row['id']; ?>">Lihat/Edit Nilai</a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
            <?php 
                endwhile; 
            else:
            ?>
            <tr>
                <td colspan="9">Tidak ada tugas yang sudah selesai atau diperiksa.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <br>
    <a href="../dashboard.php">Kembali ke Dashboard</a>
</body>
</html>

<?php $conn->close(); ?>
