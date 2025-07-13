<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Ambil daftar izin nugas
$query = "SELECT n.id, n.id_anggota, n.tanggal, n.alasan, a.nama, a.nim 
          FROM izin_nugas n 
          LEFT JOIN anggota a ON n.id_anggota = a.id 
          ORDER BY n.tanggal DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daftar Izin Nugas</title>
</head>
<body>
    <h2>Daftar Izin Nugas</h2>
    
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
                <th>NIM</th>
                <th>Tanggal</th>
                <th>Alasan</th>
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
                <td><?php echo htmlspecialchars($row['nim']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                <td><?php echo htmlspecialchars($row['alasan']); ?></td>
                <td>
                    <a href="izin-nugas-edit.php?id=<?php echo $row['id']; ?>">Edit</a>
                    <a href="izin-nugas-hapus.php?id=<?php echo $row['id']; ?>" 
                       onclick="return confirm('Apakah Anda yakin ingin menghapus izin ini?')">Hapus</a>
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
