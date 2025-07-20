<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Ambil daftar alat
$query = "SELECT * FROM alat ORDER BY nama_alat";
$result = $conn->query($query);
include '../header_beckend.php';
include '../header.php';
?>

    <h2>Daftar Alat</h2>
    
    <a href="tambah-barang.php" class="btn-tambah">Tambah Alat Baru</a>
    
    <?php
    // Tampilkan pesan status jika ada
    if (isset($_GET['status']) && isset($_GET['message'])) {
        echo "<div class='alert'>" . htmlspecialchars($_GET['message']) . "</div>";
    }
    ?>
    
    <table border="1">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Alat</th>
                <th>Jumlah</th>
                <th>Kondisi</th>
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
                <td><?php echo htmlspecialchars($row['nama_alat']); ?></td>
                <td><?php echo $row['jumlah']; ?></td>
                <td><?php echo htmlspecialchars($row['kondisi']); ?></td>
                <td>
                    <a href="edit-barang.php?id=<?php echo $row['id']; ?>">Edit</a>
                    <a href="hapus-barang.php?id=<?php echo $row['id']; ?>" 
                       onclick="return confirm('Apakah Anda yakin ingin menghapus alat ini?')">Hapus</a>
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
