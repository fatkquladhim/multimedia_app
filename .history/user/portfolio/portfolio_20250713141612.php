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
// Cek apakah tabel portfolio sudah ada, jika belum tampilkan pesan
$result = $conn->query("SHOW TABLES LIKE 'portfolio'");
if ($result->num_rows == 0) {
    echo '<h2>Portfolio Saya</h2><p>Tabel portfolio belum tersedia di database.</p>';
    $conn->close();
    exit;
}
$stmt = $conn->prepare('SELECT id, judul, deskripsi, file_karya FROM portfolio WHERE id_user = ?');
$stmt->bind_param('i', $id_user);
$stmt->execute();
$result = $stmt->get_result();
?>
<h2>Portfolio Saya</h2>
<table border="1" cellpadding="5">
    <tr>
        <th>Judul</th>
        <th>Deskripsi</th>
        <th>File Karya</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()) { ?>
    <tr>
        <td><?php echo htmlspecialchars($row['judul']); ?></td>
        <td><?php echo htmlspecialchars($row['deskripsi']); ?></td>
        <td>
            <?php if ($row['file_karya']) { ?>
                <a href="../../uploads/portfolio/<?php echo htmlspecialchars($row['file_karya']); ?>" target="_blank">Download</a>
            <?php } else { echo '-'; } ?>
        </td>
    </tr>
    <?php } ?>
</table>
<?php
$stmt->close();
$conn->close();
?>
