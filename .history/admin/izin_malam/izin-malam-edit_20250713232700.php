<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Ambil data izin yang akan diedit
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT im.*, a.nama, a.nim 
                           FROM izin_malam im 
                           LEFT JOIN anggota a ON im.id_anggota = a.id 
                           WHERE im.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $izin = $result->fetch_assoc();
    
    if (!$izin) {
        header("Location: izin-malam.php?status=error&message=Izin tidak ditemukan");
        exit;
    }
    $stmt->close();
}

// Proses update data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $tanggal = $_POST['tanggal'];
    $alasan = $_POST['alasan'];
    
    $stmt = $conn->prepare("UPDATE izin_malam SET tanggal = ?, alasan = ? WHERE id = ?");
    $stmt->bind_param("ssi", $tanggal, $alasan, $id);
    
    if ($stmt->execute()) {
        header("Location: izin-malam.php?status=success&message=Izin berhasil diupdate");
    } else {
        header("Location: izin-malam-edit.php?id=$id&status=error&message=Gagal mengupdate izin");
    }
    
    $stmt->close();
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Izin Malam</title>
</head>
<body>
    <h2>Edit Izin Malam</h2>
    
    <?php
    if (isset($_GET['status']) && isset($_GET['message'])) {
        echo "<div class='alert'>" . htmlspecialchars($_GET['message']) . "</div>";
    }
    ?>
    
    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $izin['id']; ?>">
        
        <div>
            <label>Nama:</label><br>
            <input type="text" value="<?php echo htmlspecialchars($izin['nama']); ?>" disabled>
        </div>
        
        <div>
            <label>NIM:</label><br>
            <input type="text" value="<?php echo htmlspecialchars($izin['nim']); ?>" disabled>
        </div>
        
        <div>
            <label>Tanggal:</label><br>
            <input type="date" name="tanggal" value="<?php echo $izin['tanggal']; ?>" required>
        </div>
        
        <div>
            <label>Alasan:</label><br>
            <textarea name="alasan" rows="4" required><?php echo htmlspecialchars($izin['alasan']); ?></textarea>
        </div>
        
        <br>
        <button type="submit">Update</button>
        <a href="izin-malam.php">Kembali</a>
    </form>
</body>
</html>
