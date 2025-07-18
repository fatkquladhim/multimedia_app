<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Proses tambah pemasukan manual
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_pemasukan'])) {
    $tanggal = $_POST['tanggal'];
    $jumlah = $_POST['jumlah'];
    $keterangan = $_POST['keterangan'];
    $stmt = $conn->prepare("INSERT INTO uang_masuk (tanggal, jumlah, keterangan) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $tanggal, $jumlah, $keterangan);
    $stmt->execute();
    header("Location: masuk.php?status=success&message=Pemasukan berhasil ditambahkan");
    exit;
}
// Ambil riwayat pengeluaran (manual + dari penyewaan)
$query = "SELECT tanggal, jumlah, keterangan FROM uang_keluar
          UNION ALL
          SELECT tanggal_kembali as tanggal, biaya as jumlah, CONCAT('Otomatis dari penyewaan ID: ', id) as keterangan FROM penyewaan_barang WHERE status='dikembalikan' AND biaya > 0";
$result = $conn->query($query);

// Proses hapus pemasukan
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $conn->query("DELETE FROM uang_masuk WHERE id=$id");
    header("Location: masuk.php?status=success&message=Data dihapus");
    exit;
}

// Proses edit pemasukan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id = $_POST['edit_id'];
    $tanggal = $_POST['edit_tanggal'];
    $jumlah = $_POST['edit_jumlah'];
    $keterangan = $_POST['edit_keterangan'];
    $stmt = $conn->prepare("UPDATE uang_masuk SET tanggal=?, jumlah=?, keterangan=? WHERE id=?");
    $stmt->bind_param("sisi", $tanggal, $jumlah, $keterangan, $id);
    $stmt->execute();
    header("Location: masuk.php?status=success&message=Data diupdate");
    exit;
}

// Filter dan pencarian
$where = [];
if (!empty($_GET['tgl1']) && !empty($_GET['tgl2'])) {
    $where[] = "tanggal BETWEEN '".$conn->real_escape_string($_GET['tgl1'])."' AND '".$conn->real_escape_string($_GET['tgl2'])."'";
}
if (!empty($_GET['cari'])) {
    $cari = $conn->real_escape_string($_GET['cari']);
    $where[] = "(keterangan LIKE '%$cari%' OR jumlah LIKE '%$cari%')";
}
$where_sql = $where ? 'WHERE '.implode(' AND ', $where) : '';

// Query manual
$q_manual = "SELECT id, tanggal, jumlah, keterangan FROM uang_masuk $where_sql";
$r_manual = $conn->query($q_manual);

// Total pemasukan
$q_total = "SELECT SUM(jumlah) as total FROM uang_masuk $where_sql";
$total_manual = $conn->query($q_total)->fetch_assoc()['total'] ?? 0;

include '../header.php'; // Sertakan header
?>

<div class="container">
    <h2>Uang Masuk</h2>
    <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
        <div class="alert alert-<?php echo $_GET['status'] === 'success' ? 'success' : 'danger'; ?>">
            <?php echo htmlspecialchars($_GET['message']); ?>
        </div>
    <?php endif; ?>
    
    <h3>Tambah Pemasukan</h3>
    <form method="POST">
        <input type="hidden" name="tambah_pemasukan" value="1">
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
        <button type="submit">Tambah Pemasukan</button>
    </form>

    <h3>Riwayat Pemasukan</h3>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jumlah</th>
                <th>Keterangan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $r_manual->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['tanggal']); ?></td>
                <td>Rp <?php echo number_format($row['jumlah'], 0, ',', '.'); ?></td>
                <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
                <td>
                    <a href="?edit=<?php echo $row['id']; ?>">Edit</a> |
                    <a href="?hapus=<?php echo $row['id']; ?>" onclick="return confirm('Hapus data?')">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <p><strong>Total pemasukan: Rp <?php echo number_format($total_manual, 0, ',', '.'); ?></strong></p>

    <?php if (isset($_GET['edit']) && is_numeric($_GET['edit'])):
        $idedit = $_GET['edit'];
        $dedit = $conn->query("SELECT * FROM uang_masuk WHERE id=$idedit")->fetch_assoc();
    ?>
    <h3>Edit Pemasukan</h3>
    <form method="POST">
        <input type="hidden" name="edit_id" value="<?php echo $dedit['id']; ?>">
        <div class="form-group">
            <label>Tanggal:</label>
            <input type="date" name="edit_tanggal" value="<?php echo $dedit['tanggal']; ?>" required>
        </div>
        <div class="form-group">
            <label>Jumlah:</label>
            <input type="number" name="edit_jumlah" value="<?php echo $dedit['jumlah']; ?>" required>
        </div>
        <div class="form-group">
            <label>Keterangan:</label>
            <input type="text" name="edit_keterangan" value="<?php echo htmlspecialchars($dedit['keterangan']); ?>">
        </div>
        <button type="submit">Simpan Perubahan</button>
        <a href="masuk.php">Batal</a>
    </form>
    <?php endif; ?>
    
    <br>
    <a href="../dashboard.php">Kembali ke Dashboard</a>
</div>

<?php
// Sertakan footer
include '../footer.php'; // Path relatif dari 'anggota/'
$conn->close();
?>
