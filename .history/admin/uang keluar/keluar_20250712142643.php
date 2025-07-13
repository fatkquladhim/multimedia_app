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

    // Kurangi saldo utama
    $saldo_utama -= $jumlah;

    // Tambahkan pengeluaran ke tabel uang_keluar
    $stmt = $conn->prepare("INSERT INTO uang_keluar (tanggal, jumlah, keterangan) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $tanggal, $jumlah, $keterangan);
    $stmt->execute();

    // Update saldo utama di tabel uang_masuk
    $stmt_update = $conn->prepare("UPDATE uang_masuk SET jumlah=(SELECT SUM(jumlah) FROM uang_masuk) - ? WHERE id=(SELECT MAX(id) FROM uang_masuk)");
    $stmt_update->bind_param("i", $jumlah);
    $stmt_update->execute();

    header("Location: keluar.php?status=success&message=Pengeluaran berhasil ditambahkan");
    exit;
}

// Proses hapus pengeluaran
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $result = $conn->query("SELECT id FROM uang_keluar WHERE id=$id");
    if ($result->num_rows > 0) {
        $conn->query("DELETE FROM uang_keluar WHERE id=$id");
        header("Location: keluar.php?status=success&message=Data dihapus");
    } else {
        header("Location: keluar.php?status=error&message=ID tidak valid");
    }
    exit;
}

// Proses edit pengeluaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id = $_POST['edit_id'];
    $result = $conn->query("SELECT id FROM uang_keluar WHERE id=$id");
    if ($result->num_rows > 0) {
        $tanggal = $_POST['edit_tanggal'];
        $jumlah = $_POST['edit_jumlah'];
        $keterangan = $_POST['edit_keterangan'];
        $stmt = $conn->prepare("UPDATE uang_keluar SET tanggal=?, jumlah=?, keterangan=? WHERE id=?");
        $stmt->bind_param("sisi", $tanggal, $jumlah, $keterangan, $id);
        $stmt->execute();
        header("Location: keluar.php?status=success&message=Data diupdate");
    } else {
        header("Location: keluar.php?status=error&message=ID tidak valid");
    }
    exit;
}

// Query data pengeluaran
$q_pengeluaran = "SELECT id, tanggal, jumlah, keterangan FROM uang_keluar ORDER BY tanggal ASC";
$r_pengeluaran = $conn->query($q_pengeluaran);

// Total pengeluaran
$q_total = "SELECT SUM(jumlah) as total FROM uang_keluar";
$total_pengeluaran = $conn->query($q_total)->fetch_assoc()['total'] ?? 0;

// Total pemasukan manual
$q_total_manual = "SELECT SUM(jumlah) as total FROM uang_masuk";
$total_manual = $conn->query($q_total_manual)->fetch_assoc()['total'] ?? 0;

// Total pemasukan otomatis dari penyewaan
$q_total_auto = "SELECT SUM(biaya) as total FROM penyewaan_barang WHERE status='dikembalikan' AND biaya > 0";
$total_auto = $conn->query($q_total_auto)->fetch_assoc()['total'] ?? 0;

// Hitung total pemasukan
$total_masuk = $total_manual + $total_auto;

// Hitung saldo utama
$saldo_utama = $total_masuk; // Saldo utama diambil dari total pemasukan

// Inisialisasi saldo untuk pengeluaran
$saldo = $saldo_utama;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Uang Keluar</title>
    <style>
        .container { padding: 20px; }
        .form-group { margin-bottom: 10px; }
        .alert { padding: 10px; margin-bottom: 10px; border-radius: 4px; }
        .alert-success { background-color: #d4edda; color: #155724; }
        .alert-danger { background-color: #f8d7da; color: #721c24; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 8px; border: 1px solid #ddd; }
        th { background-color: #f5f5f5; }
    </style>
</head>
<body>
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
        <h2>Saldo utama: Rp <?php echo number_format($saldo_utama, 0, ',', '.'); ?></h2>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Deskripsi</th>
                    <th>Pengeluaran</th>
                    <th>Saldo</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; ?>
                <?php while($row = $r_pengeluaran->fetch_assoc()): ?>
                <?php $saldo -= $row['jumlah']; ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($row['tanggal']); ?></td>
                    <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
                    <td>Rp <?php echo number_format($row['jumlah'], 0, ',', '.'); ?></td>
                    <td>Rp <?php echo number_format($saldo, 0, ',', '.'); ?></td>
                    <td>
                        <a href="edit_pengeluaran.php?id=<?php echo $row['id']; ?>">Edit</a> |
                        <a href="?hapus=<?php echo $row['id']; ?>" onclick="return confirm('Hapus data?')">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <p><strong>Total pengeluaran: Rp <?php echo number_format($total_pengeluaran, 0, ',', '.'); ?></strong></p>
        <form method="POST" action="export_excel_keluar.php">
            <button type="submit">Export Excel</button>
        </form>
        <br>
        <a href="../dashboard.php">Kembali ke Dashboard</a>
    </div>
</body>
</html>
<?php $conn->close(); ?>