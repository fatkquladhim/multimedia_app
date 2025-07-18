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
// Query otomatis dari penyewaan
// Ambil nama penyewa dari tabel anggota
$q_auto = "SELECT NULL as id, p.tanggal_kembali as tanggal, p.biaya as jumlah, CONCAT('Otomatis dari penyewaan ID: ', p.id, ' (', IFNULL(a.nama, '-'), ')') as keterangan, p.status FROM penyewaan_barang p LEFT JOIN anggota a ON p.user_id = a.id WHERE p.status='dikembalikan' AND p.biaya > 0";
$r_auto = $conn->query($q_auto);
// Total pemasukan
$q_total = "SELECT SUM(jumlah) as total FROM uang_masuk $where_sql";
$total_manual = $conn->query($q_total)->fetch_assoc()['total'] ?? 0;
$q_total_auto = "SELECT SUM(biaya) as total FROM penyewaan_barang WHERE status='dikembalikan' AND biaya > 0";
$total_auto = $conn->query($q_total_auto)->fetch_assoc()['total'] ?? 0;
$total_semua = $total_manual + $total_auto;
include '../header.php'; // Path relatif dari 'anggota/'
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
        <h3>Filter & Pencarian</h3>
        <form method="GET" style="margin-bottom:10px;">
            <label>Periode:</label>
            <input type="date" name="tgl1" value="<?php echo isset($_GET['tgl1']) ? $_GET['tgl1'] : ''; ?>">
            <input type="date" name="tgl2" value="<?php echo isset($_GET['tgl2']) ? $_GET['tgl2'] : ''; ?>">
            <label>Cari:</label>
            <input type="text" name="cari" value="<?php echo isset($_GET['cari']) ? htmlspecialchars($_GET['cari']) : ''; ?>">
            <button type="submit">Terapkan</button>
            <a href="masuk.php">Reset</a>
        </form>
        <h3>Riwayat Pemasukan</h3>
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Jumlah</th>
                    <th>Keterangan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $r_manual->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['tanggal']); ?></td>
                    <td>Rp <?php echo number_format($row['jumlah'], 0, ',', '.'); ?></td>
                    <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
                    <td>Manual</td>
                    <td>
                        <a href="?edit=<?php echo $row['id']; ?>">Edit</a> |
                        <a href="?hapus=<?php echo $row['id']; ?>" onclick="return confirm('Hapus data?')">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php while($row = $r_auto->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['tanggal']); ?></td>
                    <td>Rp <?php echo number_format($row['jumlah'], 0, ',', '.'); ?></td>
                    <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                    <td>-</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <p><strong>Total pemasukan: Rp <?php echo number_format($total_semua, 0, ',', '.'); ?></strong></p>
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
        <form method="POST" action="export_excel.php">
            <button type="submit">Export Excel</button>
        </form>
        <br>
        <a href="../dashboard.php">Kembali ke Dashboard</a>
    </div>
</body>
</html>
<?php $conn->close(); ?>
