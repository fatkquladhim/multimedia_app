<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Proses tambah pengeluaran manual
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_pengeluaran'])) {
    $tanggal = $_POST['tanggal'];
    $jumlah = $_POST['jumlah'];
    $keterangan = $_POST['keterangan'];
    $petugas = $_SESSION['user_id']; // Mengambil ID petugas dari session
    $stmt = $conn->prepare("INSERT INTO uang_keluar (tanggal, jumlah, keterangan, petugas) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sisi", $tanggal, $jumlah, $keterangan, $petugas);
    $stmt->execute();
    header("Location: keluar.php?status=success&message=Pengeluaran berhasil ditambahkan");
    exit;
}

include '../header.php'; // Sertakan header
?>

<div class="container">
    <h2>Uang Keluar</h2>
    <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
        <div class="alert alert-<?php echo $_GET['status'] === 'success' ? 'success' : 'danger'; ?>">
            <?php echo htmlspecialchars($_GET['message']); ?>
        </div>
    <?php endif; ?>
    
    <h3>Tambah Pengeluaran</h3>
    <form method="POST">
        <input type="hidden" name="tambah_pengeluaran" value="1">
        <div class="form-group">
            <label>Tanggal:</label>
            <input type="date" name="tanggal" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        <div class="form-group">
            <label>Jumlah:</label>
            <input type="number" name="jumlah" min="1" required>
        </div>
        <div class="form-group">
            <label>Keterangan:</label>
            <input type="text" name="keterangan">
        </div>
        <button type="submit">Tambah Pengeluaran</button>
    </form>

    <h3>Riwayat Pengeluaran</h3>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jumlah</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
           
            <tr>
                <td><?php echo htmlspecialchars($row['tanggal']); ?></td>
                <td>Rp <?php echo number_format($row['jumlah'], 0, ',', '.'); ?></td>
                <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
            </tr>
           
        </tbody>
    </table>
    <br>
    <a href="../dashboard.php">Kembali ke Dashboard</a>
</div>

<?php
// Sertakan footer
include '../footer.php'; // Path relatif dari 'anggota/' ke 'includes/'
$conn->close();
?>
