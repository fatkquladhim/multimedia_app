<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Ambil daftar izin nugas
$query = "SELECT im.*, a.nama 
          FROM izin_nugas im 
          LEFT JOIN anggota a ON im.id_anggota = a.id 
          ORDER BY im.tanggal DESC";
$result = $conn->query($query);
include '../header_beckend.php';
include '../header.php';
?>

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
                <!-- NIM column removed -->
                <th>Tanggal</th>
                <th>Jam Izin</th>
                <th>Jam Kembali</th>
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
                <!-- NIM value removed -->
                <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                <td><?php echo htmlspecialchars($row['jam_izin']); ?></td>
                <td><?php echo htmlspecialchars($row['jam_selesai_izin']); ?></td>
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
<?php
// Sertakan footer
include '../footer.php'; // Path relatif dari 'anggota/' ke 'includes/'
$conn->close();
?>
