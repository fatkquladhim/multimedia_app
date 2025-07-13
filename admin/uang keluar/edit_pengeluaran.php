<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: keluar.php?status=error&message=ID tidak valid');
    exit;
}

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM uang_keluar WHERE id=$id");
if ($result->num_rows === 0) {
    header('Location: keluar.php?status=error&message=Data tidak ditemukan');
    exit;
}

$data = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $_POST['tanggal'];
    $jumlah = $_POST['jumlah'];
    $keterangan = $_POST['keterangan'];

    $stmt = $conn->prepare("UPDATE uang_keluar SET tanggal=?, jumlah=?, keterangan=? WHERE id=?");
    $stmt->bind_param("sisi", $tanggal, $jumlah, $keterangan, $id);
    $stmt->execute();

    header('Location: keluar.php?status=success&message=Data berhasil diupdate');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Pengeluaran</title>
    <style>
        .container { padding: 20px; }
        .form-group { margin-bottom: 10px; }
        .alert { padding: 10px; margin-bottom: 10px; border-radius: 4px; }
        .alert-success { background-color: #d4edda; color: #155724; }
        .alert-danger { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Pengeluaran</h2>
        <form method="POST">
            <div class="form-group">
                <label>Tanggal:</label>
                <input type="date" name="tanggal" value="<?php echo $data['tanggal']; ?>" required>
            </div>
            <div class="form-group">
                <label>Jumlah:</label>
                <input type="number" name="jumlah" value="<?php echo $data['jumlah']; ?>" min="1" required>
            </div>
            <div class="form-group">
                <label>Keterangan:</label>
                <input type="text" name="keterangan" value="<?php echo htmlspecialchars($data['keterangan']); ?>">
            </div>
            <button type="submit">Simpan Perubahan</button>
            <a href="keluar.php">Batal</a>
        </form>
    </div>
</body>
</html>
<?php $conn->close(); ?>
