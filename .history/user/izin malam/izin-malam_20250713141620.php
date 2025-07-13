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
// Solusi sementara: ambil id anggota pertama
$result = $conn->query('SELECT id FROM anggota LIMIT 1');
$row = $result->fetch_assoc();
$id_anggota = $row ? $row['id'] : 1;
$stmt = $conn->prepare('SELECT tanggal, alasan FROM izin_malam WHERE id_anggota = ? ORDER BY tanggal DESC');
$stmt->bind_param('i', $id_anggota);
$stmt->execute();
$izin = $stmt->get_result();
?>
<h2>Izin Malam</h2>
<a href="izin-malam-entry.php">Ajukan Izin Malam</a>
<table border="1" cellpadding="5">
    <tr>
        <th>Tanggal</th>
        <th>Alasan</th>
    </tr>
    <?php while ($row = $izin->fetch_assoc()) { ?>
    <tr>
        <td><?php echo htmlspecialchars($row['tanggal']); ?></td>
        <td><?php echo htmlspecialchars($row['alasan']); ?></td>
    </tr>
    <?php } ?>
</table>
<?php
$stmt->close();
$conn->close();
?>
