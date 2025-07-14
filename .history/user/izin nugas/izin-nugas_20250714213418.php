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
$id_anggota = $_SESSION['user_id'];
$stmt = $conn->prepare('SELECT tanggal, jam_izin, jam_selesai_izin, alasan, status FROM izin_nugas WHERE id_anggota = ? ORDER BY tanggal DESC');
$stmt->bind_param('i', $id_anggota);
$stmt->execute();
$izin = $stmt->get_result();
?>
<h2>Izin Nugas</h2>
<a href="izin-nugas-entry.php">Ajukan Izin Nugas</a>
<table border="1" cellpadding="5">
    <tr>
        <th>Tanggal</th>
        <th>Jam Izin</th>
        <th>Jam Kembali</th>
        <th>Alasan</th>
    </tr>
    <?php while ($row = $izin->fetch_assoc()) { ?>
    <tr>
        <td><?php echo htmlspecialchars($row['tanggal']); ?></td>
        <td><?php echo htmlspecialchars($row['jam_izin']); ?></td>
        <td><?php echo htmlspecialchars($row['jam_selesai_izin']); ?></td>
        <td><?php echo htmlspecialchars($row['alasan']); ?></td>
    </tr>
    <?php } ?>
</table>
<?php
$stmt->close();
$conn->close();
?>
