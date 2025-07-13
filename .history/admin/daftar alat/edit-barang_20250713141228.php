<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Ambil data alat yang akan diedit
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM alat WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $alat = $result->fetch_assoc();
    
    if (!$alat) {
        header("Location: daftar-alat.php?status=error&message=Alat tidak ditemukan");
        exit;
    }
    $stmt->close();
}

// Proses update data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nama_alat = $_POST['nama_alat'];
    $jumlah = $_POST['jumlah'];
    $kondisi = $_POST['kondisi'];
    
    $stmt = $conn->prepare("UPDATE alat SET nama_alat = ?, jumlah = ?, kondisi = ? WHERE id = ?");
    $stmt->bind_param("sisi", $nama_alat, $jumlah, $kondisi, $id);
    
    if ($stmt->execute()) {
        header("Location: daftar-alat.php?status=success&message=Alat berhasil diupdate");
    } else {
        header("Location: edit-barang.php?id=$id&status=error&message=Gagal mengupdate alat");
    }
    
    $stmt->close();
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Alat</title>
</head>
<body>
    <h2>Edit Alat</h2>
    
    <?php
    if (isset($_GET['status']) && isset($_GET['message'])) {
        echo "<div class='alert'>" . htmlspecialchars($_GET['message']) . "</div>";
    }
    ?>
    
    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $alat['id']; ?>">
        
        <div>
            <label>Nama Alat:</label><br>
            <input type="text" name="nama_alat" value="<?php echo htmlspecialchars($alat['nama_alat']); ?>" required>
        </div>
        
        <div>
            <label>Jumlah:</label><br>
            <input type="number" name="jumlah" value="<?php echo $alat['jumlah']; ?>" min="0" required>
        </div>
        
        <div>
            <label>Kondisi:</label><br>
            <select name="kondisi" required>
                <option value="Baik" <?php echo ($alat['kondisi'] == 'Baik') ? 'selected' : ''; ?>>Baik</option>
                <option value="Rusak Ringan" <?php echo ($alat['kondisi'] == 'Rusak Ringan') ? 'selected' : ''; ?>>Rusak Ringan</option>
                <option value="Rusak Berat" <?php echo ($alat['kondisi'] == 'Rusak Berat') ? 'selected' : ''; ?>>Rusak Berat</option>
            </select>
        </div>
        
        <br>
        <button type="submit">Update</button>
        <a href="daftar-alat.php">Kembali</a>
    </form>
</body>
</html>
