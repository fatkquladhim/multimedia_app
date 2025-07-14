<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$id_user = $_SESSION['user_id'];

// Ambil tugas yang diberikan ke user yang statusnya 'pending' atau 'selesai' (jika belum dinilai)
// Join dengan tugas_jawaban untuk mengecek apakah sudah ada jawaban
$stmt = $conn->prepare('
    SELECT 
        t.id, 
        t.judul, 
        t.deskripsi, 
        t.deadline, 
        t.status,
        tj.id as jawaban_id,
        tj.file_jawaban,
        tj.nilai,
        tj.komentar
    FROM tugas t 
    LEFT JOIN tugas_jawaban tj ON t.id = tj.id_tugas AND tj.id_user = ?
    WHERE t.id_penerima_tugas = ? AND t.status IN ("pending", "selesai")
    ORDER BY t.deadline ASC
');
$stmt->bind_param('ii', $id_user, $id_user);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tugas Saya</title>
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
        .info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
    </style>
</head>
<body>
    <h2>Tugas Saya</h2>
    
    <?php
    if (isset($_GET['status'])) {
        if ($_GET['status'] == 'success') {
            echo '<div class="message success">' . htmlspecialchars($_GET['message']) . '</div>';
        } else {
            echo '<div class="message error">' . htmlspecialchars($_GET['message']) . '</div>';
        }
    }
    ?>

    <p class="info">
        <strong>Alur Tugas:</strong><br>
        1. Tugas yang diberikan akan muncul di tabel di bawah.<br>
        2. Klik tombol <b>Kerjakan</b> untuk melihat detail tugas dan mengunggah file jawaban.<br>
        3. Setelah file diunggah, status tugas akan berubah menjadi 'selesai'.<br>
        4. Anda dapat melihat riwayat tugas yang sudah dikerjakan dan nilainya di halaman "Riwayat Tugas".
    </p>

    <p><a href="riwayat_tugas.php">Riwayat Tugas</a> | <a href="../dashboard.php">Kembali ke Dashboard</a></p>

    <table>
        <thead>
            <tr>
                <th>Judul</th>
                <th>Deskripsi</th>
                <th>Deadline</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if ($result->num_rows > 0):
                while ($row = $result->fetch_assoc()): 
            ?>
            <tr>
                <td><?php echo htmlspecialchars($row['judul']); ?></td>
                <td><?php echo nl2br(htmlspecialchars($row['deskripsi'])); ?></td>
                <td><?php echo date('d/m/Y', strtotime($row['deadline'])); ?></td>
                <td>
                    <?php 
                    if ($row['jawaban_id'] && $row['status'] == 'selesai') {
                        echo 'Menunggu Penilaian'; // User submitted, admin hasn't graded
                    } elseif ($row['jawaban_id'] && $row['status'] == 'diperiksa') {
                        echo 'Sudah Dinilai'; // Admin has graded
                    } else {
                        echo htmlspecialchars($row['status']); // Pending
                    }
                    ?>
                </td>
                <td>
                    <?php if (!$row['jawaban_id']): // If no answer submitted yet ?>
                        <a href="tugas_kerjakan.php?id=<?php echo $row['id']; ?>">Kerjakan</a>
                    <?php else: // If answer already submitted ?>
                        Sudah dikerjakan
                    <?php endif; ?>
                </td>
            </tr>
            <?php 
                endwhile; 
            else:
            ?>
            <tr>
                <td colspan="5">Tidak ada tugas yang diberikan kepada Anda saat ini.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
