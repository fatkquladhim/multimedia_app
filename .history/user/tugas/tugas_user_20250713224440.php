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
// Ambil tugas yang diberikan ke user
$stmt = $conn->prepare('SELECT t.id, t.judul, t.deskripsi, t.deadline, t.status FROM tugas t WHERE t.id_penerima_tugas = ?');
$stmt->bind_param('i', $id_user);
$stmt->execute();
$result = $stmt->get_result();
?>
<h2>Tugas Saya</h2>
<a href="tugas_kirim.php">Kirim Jawaban Tugas</a>
<a href="riwayat_tugas.php">Riwayat Tugas</a>
<table border="1" cellpadding="5">
    <tr>
        <th>Judul</th>
        <th>Deskripsi</th>
        <th>Deadline</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
    <tr>
        <td><?php echo htmlspecialchars($row['judul']); ?></td>
        <td><?php echo htmlspecialchars($row['deskripsi']); ?></td>
        <td><?php echo htmlspecialchars($row['deadline']); ?></td>
        <td><?php echo htmlspecialchars($row['status']); ?></td>
        <td>
            <a href="tugas_edit.php?id=<?php echo $row['id']; ?>">Edit</a> |
            <a href="tugas_hapus.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Yakin hapus tugas?')">Hapus</a>
        </td>
    </tr>
    <?php } ?>
</table>
<?php
$stmt->close();
$conn->close();
?>
