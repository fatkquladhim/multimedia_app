<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$id_user = $_SESSION['user_id'];

// Ambil riwayat tugas yang sudah dijawab oleh user
$stmt = $conn->prepare('
    SELECT 
        tj.id, 
        t.judul, 
        t.deadline,
        tj.file_jawaban, 
        tj.nilai, 
        tj.komentar,
        tj.tanggal_submit,
        t.status as tugas_status
    FROM tugas_jawaban tj 
    JOIN tugas t ON tj.id_tugas = t.id 
    WHERE tj.id_user = ?
    ORDER BY tj.tanggal_submit DESC
');
$stmt->bind_param('i', $id_user);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Riwayat Tugas</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        a { text-decoration: none; color: #007bff; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
     <div class="bg-white rounded-3xl shadow-2xl overflow-hidden ">
        <div style="display:flex; flex-direction:column;">
   <?php
include '../sidebar.php';
?>
<main class="flex-1 p-6">
                <div class="flex justify-between items-center mb-6">
    <h2>Riwayat Tugas</h2>
    
    <p><a href="tugas_user.php">Kembali ke Tugas Saya</a>

    <table>
        <thead>
            <tr>
                <th>Judul Tugas</th>
                <th>Deadline Tugas</th>
                <th>Tanggal Submit</th>
                <th>File Jawaban</th>
                <th>Status</th>
                <th>Nilai</th>
                <th>Komentar Admin</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if ($result->num_rows > 0):
                while ($row = $result->fetch_assoc()): 
            ?>
            <tr>
                <td><?php echo htmlspecialchars($row['judul']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($row['deadline'])); ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal_submit'])); ?></td>
                <td>
                    <?php if ($row['file_jawaban']) { ?>
                        <a href="../../uploads/tugas_jawaban/<?php echo htmlspecialchars($row['file_jawaban']); ?>" target="_blank">Download</a>
                    <?php } else { echo '-'; } ?>
                </td>
                <td>
                    <?php 
                    if ($row['tugas_status'] == 'diperiksa') {
                        echo 'Sudah Dinilai';
                    } elseif ($row['tugas_status'] == 'selesai') {
                        echo 'Menunggu Penilaian';
                    } else {
                        echo htmlspecialchars($row['tugas_status']); // Should ideally be 'selesai' or 'diperiksa' here
                    }
                    ?>
                </td>
                <td><?php echo $row['nilai'] !== null ? htmlspecialchars($row['nilai']) : '-'; ?></td>
                <td><?php echo htmlspecialchars($row['komentar'] ?? '-'); ?></td>
            </tr>
            <?php 
                endwhile; 
            else:
            ?>
            <tr>
                <td colspan="7">Anda belum mengirimkan jawaban untuk tugas apapun.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
            </div>
</main>
        </div>
     </div>
</body>
</html>


