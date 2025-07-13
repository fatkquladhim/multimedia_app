<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Ambil daftar izin nugas untuk user ini
$query = "SELECT n.*, a.nama, a.nim 
          FROM izin_nugas n 
          LEFT JOIN anggota a ON n.id_anggota = a.id 
          ORDER BY n.tanggal DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Izin Nugas</title>
</head>
<body>
    <h2>Izin Nugas</h2>
    
    <a href="izin-nugas - entry.php" class="btn-tambah">Ajukan Izin Nugas</a>
    
    <table border="1">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Alasan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            while($row = $result->fetch_assoc()): 
            ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                <td><?php echo htmlspecialchars($row['alasan']); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <br>
    <a href="../dashboard.php">Kembali ke Dashboard</a>
</body>
</html>

<?php $conn->close(); ?>
