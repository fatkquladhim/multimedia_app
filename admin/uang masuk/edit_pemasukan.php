<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

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

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    $result = $conn->query("SELECT * FROM uang_masuk WHERE id=$id");
    $data = $result->fetch_assoc();
} else {
    header("Location: masuk.php?status=error&message=ID tidak valid");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Pemasukan</title>
    <style>
        .container { padding: 20px; }
        .form-group { margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Pemasukan</h2>
        <form method="POST">
            <input type="hidden" name="edit_id" value="<?php echo $data['id']; ?>">
            <div class="form-group">
                <label>Tanggal:</label>
                <input type="date" name="edit_tanggal" value="<?php echo $data['tanggal']; ?>" required>
            </div>
            <div class="form-group">
                <label>Jumlah:</label>
                <input type="number" name="edit_jumlah" value="<?php echo $data['jumlah']; ?>" required>
            </div>
            <div class="form-group">
                <label>Keterangan:</label>
                <input type="text" name="edit_keterangan" value="<?php echo htmlspecialchars($data['keterangan']); ?>">
            </div>
            <button type="submit">Simpan Perubahan</button>
            <a href="masuk.php">Batal</a>
        </form>
    </div>
</body>
</html>
<?php $conn->close(); ?>
