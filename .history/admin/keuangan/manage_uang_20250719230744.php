<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Proses tambah data keuangan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_data'])) {
    $tanggal = $_POST['tanggal'];
    $keterangan = $_POST['keterangan'] ?? '';
    $pemasukan = $_POST['pemasukan'] ?? 0;
    $pengeluaran = $_POST['pengeluaran'] ?? 0;

    // Hitung saldo baru
    $saldo_akhir = ($pemasukan - $pengeluaran);

    // Corrected: 5 columns, 5 placeholders, 5 variables, 5 type specifiers
    $stmt = $conn->prepare("INSERT INTO keuangan (tanggal, keterangan, pemasukan, pengeluaran, saldo)
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("siisi", $tanggal, $keterangan, $pemasukan, $pengeluaran, $saldo_akhir);
    $stmt->execute();

    header("Location: manage_uang.php?status=success&message=Data keuangan berhasil ditambahkan");
    exit;
}

// Process automatic data from rentals (assuming this logic is still desired)
$auto_pengeluaran_query = "SELECT tanggal_kembali as tanggal, biaya as pengeluaran, CONCAT('Otomatis dari penyewaan ID: ', id) as keterangan
                            FROM penyewaan_barang WHERE status='dikembalikan' AND biaya > 0";
$auto_result = $conn->query($auto_pengeluaran_query);

while ($row = $auto_result->fetch_assoc()) {
    $tanggal = $row['tanggal'];
    $pengeluaran = $row['pengeluaran'];
    $keterangan = $row['keterangan'];
    $pemasukan = 0; // For automatic pengeluaran, pemasukan is 0
    $saldo_akhir = -$pengeluaran;

    $stmt = $conn->prepare("INSERT INTO keuangan (tanggal, keterangan, pemasukan, pengeluaran, saldo)
                           VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("siisi", $tanggal, $keterangan, $pemasukan, $pengeluaran, $saldo_akhir);
    $stmt->execute();
}


// Proses hapus data
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $conn->query("DELETE FROM keuangan WHERE id=$id");
    header("Location: manage_uang.php?status=success&message=Data berhasil dihapus");
    exit;
}


// Query untuk mendapatkan data keuangan
$query = "SELECT id as nomor, tanggal,keterangan, pemasukan, pengeluaran, saldo
          FROM keuangan ORDER BY tanggal DESC";
$result = $conn->query($query);

// Hitung total pemasukan dan pengeluaran
$total_query = "SELECT SUM(pemasukan) as total_pemasukan, SUM(pengeluaran) as total_pengeluaran FROM keuangan";
$total_result = $conn->query($total_query)->fetch_assoc();
$total_pemasukan = $total_result['total_pemasukan'] ?? 0;
$total_pengeluaran = $total_result['total_pengeluaran'] ?? 0;

include '../header.php';
?>

<div class="container">
    <h2>Manajemen Keuangan</h2>

    <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
        <div class="alert alert-<?php echo $_GET['status'] === 'success' ? 'success' : 'danger'; ?>">
            <?php echo htmlspecialchars($_GET['message']); ?>
        </div>
    <?php endif; ?>

    <h3>Tambah Data Keuangan</h3>
    <form method="POST" class="mb-4">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Tanggal:</label>
                    <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>keterangan</label>
                    <input type="text" name="keterangan" class="form-control">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Pemasukan:</label>
                    <input type="number" name="pemasukan" class="form-control" min="0" value="0">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Pengeluaran:</label>
                    <input type="number" name="pengeluaran" class="form-control" min="0" value="0">
                </div>
            </div>
        </div>
        <button type="submit" name="tambah_data" class="btn btn-primary">Tambah Data</button>
    </form>

    <h3>Riwayat Keuangan</h3>
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Pemasukan</th>
                            <th>Pengeluaran</th>
                            <th>Saldo</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        while($row = $result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['tanggal']) ?></td>
                            <td><?= htmlspecialchars($row['keterangan']) ?></td>
                            <td class="text-success">Rp <?= number_format($row['pemasukan'], 0, ',', '.') ?></td>
                            <td class="text-danger">Rp <?= number_format($row['pengeluaran'], 0, ',', '.') ?></td>
                            <td class="font-weight-bold">Rp <?= number_format($row['saldo'], 0, ',', '.') ?></td>
                            <td>
                                <a href="manage_edit.php?id=<?= $row['nomor'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="manage_uang.php?hapus=<?= $row['nomor'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus data ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr class="font-weight-bold">
                            <td colspan="3" class="text-right">Total:</td>
                            <td class="text-success">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></td>
                            <td class="text-danger">Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></td>
                            <td colspan="2">Rp <?= number_format($total_pemasukan - $total_pengeluaran, 0, ',', '.') ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
include '../footer.php';
$conn->close();
?>
