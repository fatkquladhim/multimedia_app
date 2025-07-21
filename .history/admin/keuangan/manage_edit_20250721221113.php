<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Pastikan ID data keuangan diberikan
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: manage_uang.php?status=danger&message=ID data keuangan tidak valid.');
    exit;
}

$id_keuangan = $_GET['id'];

// Proses update data keuangan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_data'])) {
    $tanggal = $_POST['tanggal'];
    $keterangan = $_POST['keterangan'] ?? '';
    $pemasukan = $_POST['pemasukan'] ?? 0;
    $pengeluaran = $_POST['pengeluaran'] ?? 0;

    // Hitung saldo baru
    $saldo_akhir = ($pemasukan - $pengeluaran);

    $stmt = $conn->prepare("UPDATE keuangan SET tanggal = ?, keterangan = ?, pemasukan = ?, pengeluaran = ?, saldo = ? WHERE id = ?");
    $stmt->bind_param("ssiiii", $tanggal, $keterangan, $pemasukan, $pengeluaran, $saldo_akhir, $id_keuangan);

    if ($stmt->execute()) {
        header("Location: manage_uang.php?status=success&message=Data keuangan berhasil diperbarui.");
        exit;
    } else {
        header("Location: manage_uang.php?status=danger&message=Gagal memperbarui data keuangan.");
        exit;
    }
}

// Ambil data keuangan yang akan diedit
$query = "SELECT id, tanggal, keterangan, pemasukan, pengeluaran, saldo FROM keuangan WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_keuangan);
$stmt->execute();
$result = $stmt->get_result();
$data_keuangan = $result->fetch_assoc();

// Jika data tidak ditemukan, redirect kembali
if (!$data_keuangan) {
    header('Location: manage_uang.php?status=danger&message=Data keuangan tidak ditemukan.');
    exit;
}

include '../header_beckend.php';
include '../header.php';
?>

<div class="container">
    <h2>Edit Data Keuangan</h2>

    <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
        <div class="alert alert-<?php echo $_GET['status'] === 'success' ? 'success' : 'danger'; ?>">
            <?php echo htmlspecialchars($_GET['message']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="mb-4">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Tanggal:</label>
                    <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($data_keuangan['tanggal']) ?>" required>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Keterangan:</label>
                    <input type="text" name="keterangan" class="form-control" value="<?= htmlspecialchars($data_keuangan['keterangan']) ?>">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Pemasukan:</label>
                    <input type="number" name="pemasukan" class="form-control" min="0" value="<?= htmlspecialchars($data_keuangan['pemasukan']) ?>">
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label>Pengeluaran:</label>
                    <input type="number" name="pengeluaran" class="form-control" min="0" value="<?= htmlspecialchars($data_keuangan['pengeluaran']) ?>">
                </div>
            </div>
        </div>
        <button type="submit" name="update_data" class="btn btn-primary">Update Data</button>
        <a href="manage_uang.php" class="btn btn-secondary">Batal</a>
    </form>
</div>

<?php
include '../footer.php';
$conn->close();
?>
