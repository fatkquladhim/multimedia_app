<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}
require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Sertakan header
include '../header_beckend.php';
include '../header.php';
?>
<div class="container">
    <h2>Daftar Anggota</h2>
    <a href="anggota-entry.php" class="btn-tambah">Tambah Anggota</a>
    <?php
    if (isset($_GET['status']) && isset($_GET['message'])) {
        echo "<div class='alert alert-" . ($_GET['status'] === 'success' ? 'success' : 'danger') . "'>" . htmlspecialchars($_GET['message']) . "</div>";
    }
    ?>
    <table border="1" cellpadding="5">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Alamat</th>
                <th>Email</th>
                <th>No HP</th>
                <th>Foto</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $conn->query('SELECT * FROM anggota ORDER BY id DESC');
            while ($row = $result->fetch_assoc()) {
            ?>
            <tr>
                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                <td><?php echo htmlspecialchars($row['alamat']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['no_hp']); ?></td>
                <td>
                    <?php if ($row['foto']) { ?>
                        <img src="../../uploads/<?php echo htmlspecialchars($row['foto']); ?>" alt="Foto Anggota" width="50">
                    <?php } ?>
                </td>
                <td>
                    <a href="anggota-edit.php?edit=<?php echo $row['id']; ?>">Edit</a> |
                    <a href="anggota-hapus.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Yakin hapus anggota?')">Hapus</a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
<?php
// Sertakan footer
include '../footer.php'; // Path relatif dari 'anggota/' ke 'includes/'
$conn->close();
?>
