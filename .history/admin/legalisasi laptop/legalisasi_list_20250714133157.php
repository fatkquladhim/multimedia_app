<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Ambil daftar legalisasi
$query = "SELECT l.*, a.nama 
          FROM legalisasi_laptop l 
          LEFT JOIN anggota a ON l.id_anggota = a.id 
          ORDER BY l.id DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daftar Legalisasi Laptop</title>
</head>
<body>
    <h2>Daftar Legalisasi Laptop</h2>
    
    <a href="legalisasi_create.php" class="btn-tambah">Tambah Legalisasi Baru</a>
    
    <?php
    if (isset($_GET['status']) && isset($_GET['message'])) {
        echo "<div class='alert'>" . htmlspecialchars($_GET['message']) . "</div>";
    }
    ?>
    
    <table border="1">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Merk Laptop</th>
                <th>Tipe</th>
                <th>Serial Number</th>
                <th>Bukti</th>
                <th>Status</th>
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
                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                <td><?php echo htmlspecialchars($row['merk']); ?></td>
                <td><?php echo htmlspecialchars($row['tipe']); ?></td>
                <td><?php echo htmlspecialchars($row['serial_number']); ?></td>
                <td>
                    <?php if($row['file_bukti']): ?>
                        <a href="../../uploads/legalisasi/<?php echo $row['file_bukti']; ?>" target="_blank">
                            Lihat Bukti
                        </a>
                    <?php else: ?>
                        Tidak ada bukti
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td>
                    <a href="legalisasi_edit.php?id=<?php echo $row['id']; ?>">Edit</a> |
                    <a href="legalisasi_hapus.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus legalisasi ini?')">Hapus</a>
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
