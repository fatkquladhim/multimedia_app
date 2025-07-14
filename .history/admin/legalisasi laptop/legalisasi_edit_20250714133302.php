<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Ambil data legalisasi yang akan diedit
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT l.*, a.nama FROM legalisasi_laptop l LEFT JOIN anggota a ON l.id_anggota = a.id WHERE l.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $legalisasi = $result->fetch_assoc();
    $stmt->close();
    if (!$legalisasi) {
        header("Location: legalisasi_list.php?status=error&message=Data tidak ditemukan");
        exit;
    }
} else {
    header("Location: legalisasi_list.php?status=error&message=ID tidak valid");
    exit;
}

// Proses update data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $merk = $_POST['merk'];
    $tipe = $_POST['tipe'];
    $serial_number = $_POST['serial_number'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE legalisasi_laptop SET merk = ?, tipe = ?, serial_number = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $merk, $tipe, $serial_number, $status, $id);
    if ($stmt->execute()) {
        header("Location: legalisasi_list.php?status=success&message=Data berhasil diupdate");
    } else {
        header("Location: legalisasi_edit.php?id=$id&status=error&message=Gagal mengupdate data");
    }
    $stmt->close();
    $conn->close();
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Legalisasi Laptop</title>
</head>
<body>
    <h2>Edit Legalisasi Laptop</h2>
    <?php
    if (isset($_GET['status']) && isset($_GET['message'])) {
        echo "<div class='alert'>" . htmlspecialchars($_GET['message']) . "</div>";
    }
    ?>
    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $legalisasi['id']; ?>">
        <div>
            <label>Nama Anggota:</label><br>
            <input type="text" value="<?php echo htmlspecialchars($legalisasi['nama']); ?>" disabled>
        </div>
        <div>
            <label>Merk Laptop:</label><br>
            <input type="text" name="merk" value="<?php echo htmlspecialchars($legalisasi['merk']); ?>" required>
        </div>
        <div>
            <label>Tipe:</label><br>
            <input type="text" name="tipe" value="<?php echo htmlspecialchars($legalisasi['tipe']); ?>" required>
        </div>
        <div>
            <label>Serial Number:</label><br>
            <input type="text" name="serial_number" value="<?php echo htmlspecialchars($legalisasi['serial_number']); ?>" required>
        </div>
        <div>
            <label>Status Laptop:</label><br>
            <select name="status" required>
                <option value="">Pilih Status</option>
                <option value="Baik" <?php if($legalisasi['status']=='Baik') echo 'selected'; ?>>Baik</option>
                <option value="Rusak" <?php if($legalisasi['status']=='Rusak') echo 'selected'; ?>>Rusak</option>
                <option value="Perlu Perbaikan" <?php if($legalisasi['status']=='Perlu Perbaikan') echo 'selected'; ?>>Perlu Perbaikan</option>
            </select>
        </div>
        <br>
        <button type="submit">Update</button>
        <a href="legalisasi_list.php">Kembali</a>
    </form>
</body>
</html>
<?php $conn->close(); ?>
