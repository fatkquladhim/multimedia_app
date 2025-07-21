<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Ambil data berdasarkan ID
$id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM keuangan WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    header("Location: manage_uang.php?status=error&message=Data tidak ditemukan");
    exit;
}

// Proses update data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_data'])) {
    $tanggal = $_POST['tanggal'];
    $keterangan = $_POST['keterangan'] ?? '';
    $pemasukan = $_POST['pemasukan'] ?? 0;
    $pengeluaran = $_POST['pengeluaran'] ?? 0;
    $saldo_akhir = ($pemasukan - $pengeluaran);

    $stmt = $conn->prepare("UPDATE keuangan SET 
                          tanggal = ?,
                          keterangan = ?,
                          pemasukan = ?,
                          pengeluaran = ?,
                          saldo = ?
                          WHERE id = ?");
    $stmt->bind_param("siissi", $tanggal, $pemasukan, $pengeluaran, $keterangan, $saldo_akhir, $id);
    $stmt->execute();

    header("Location: manage_uang.php?status=success&message=Data keuangan berhasil diperbarui");
    exit;
}

include '../header_beckend.php';
include '../header.php';
?>

<div class="container">
    <h2>Edit Data Keuangan</h2>

    <form method="POST">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Tanggal:</label>
                    <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($data['tanggal']) ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Keterangan </label>
                    <input type="text" name="keterangan_pemasukan" class="form-control" value="<?= htmlspecialchars($data['keterangan']) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Pemasukan:</label>
                    <input type="number" name="pemasukan" class="form-control" min="0" value="<?= htmlspecialchars($data['pemasukan']) ?>">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Pengeluaran:</label>
                    <input type="number" name="pengeluaran" class="form-control" min="0" value="<?= htmlspecialchars($data['pengeluaran']) ?>">
                </div>
            </div>
        </div>

        <div class="form-group">
            <button type="submit" name="update_data" class="btn btn-primary">Update Data</button>
            <a href="manage_uang.php" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>

<?php
include '../footer.php';
$conn->close();
?>

