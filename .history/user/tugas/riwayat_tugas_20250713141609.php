<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}
?>
<?php
require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$id_user = $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT tj.id, t.judul, tj.file_jawaban, tj.nilai, tj.komentar FROM tugas_jawaban tj JOIN tugas t ON tj.id_tugas = t.id WHERE tj.id_user = ?');
$stmt->bind_param('i', $id_user);
$stmt->execute();
$result = $stmt->get_result();
?>
<h2>Riwayat Tugas</h2>
<table border="1" cellpadding="5">
    <tr>
        <th>Judul Tugas</th>
        <th>File Jawaban</th>
        <th>Nilai</th>
        <th>Komentar</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
    <tr>
        <td><?php echo htmlspecialchars($row['judul']); ?></td>
        <td>
            <?php if ($row['file_jawaban']) { ?>
                <a href="../../uploads/tugas_jawaban/<?php echo htmlspecialchars($row['file_jawaban']); ?>" target="_blank">Download</a>
            <?php } else { echo '-'; } ?>
        </td>
        <td><?php echo $row['nilai'] !== null ? htmlspecialchars($row['nilai']) : '-'; ?></td>
        <td><?php echo htmlspecialchars($row['komentar']); ?></td>
    </tr>
    <?php } ?>
</table>
<?php
$stmt->close();
$conn->close();
?>
