<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Ambil data penyewaan yang akan diedit
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT pb.*, a.nama as nama_anggota, al.nama_alat as nama_alat 
                           FROM penyewaan_barang pb 
                           LEFT JOIN anggota a ON pb.id_anggota = a.id 
                           LEFT JOIN alat al ON pb.id_alat = al.id 
                           WHERE pb.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $penyewaan = $result->fetch_assoc();

    if (!$penyewaan) {
        header("Location: penyewaan-barang.php?status=error&message=Penyewaan tidak ditemukan");
        exit;
    }
}

// Ambil daftar alat untuk form edit
$query_alat = "SELECT id, nama_alat, jumlah FROM alat ORDER BY nama_alat";
$alat = $conn->query($query_alat);

// Proses form edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $id_alat_lama = $_POST['id_alat_lama'];
    $jumlah_lama = $_POST['jumlah_lama'];
    $id_alat_baru = $_POST['id_alat'];
    $jumlah_baru = $_POST['jumlah'];
    $tanggal_kembali = $_POST['tanggal_kembali'];
    
    // Mulai transaction
    $conn->begin_transaction();
    
    try {
        // Kembalikan stok lama
        $stmt = $conn->prepare("UPDATE alat SET jumlah = jumlah + ? WHERE id = ?");
        $stmt->bind_param("ii", $jumlah_lama, $id_alat_lama);
        $stmt->execute();
        
        // Validasi stok baru
        $stmt = $conn->prepare("SELECT jumlah FROM alat WHERE id = ?");
        $stmt->bind_param("i", $id_alat_baru);
        $stmt->execute();
        $result_stok = $stmt->get_result();
        $stok = $result_stok->fetch_assoc();
        
        if ($stok['jumlah'] >= $jumlah_baru) {
            // Update penyewaan
            $stmt = $conn->prepare("UPDATE penyewaan_barang SET id_alat = ?, jumlah = ?, tanggal_kembali = ? WHERE id = ?");
            $stmt->bind_param("iisi", $id_alat_baru, $jumlah_baru, $tanggal_kembali, $id);
            $stmt->execute();
            
            // Kurangi stok baru
            $stmt = $conn->prepare("UPDATE alat SET jumlah = jumlah - ? WHERE id = ?");
            $stmt->bind_param("ii", $jumlah_baru, $id_alat_baru);
            $stmt->execute();
            
            $conn->commit();
            header("Location: penyewaan-barang.php?status=success&message=Penyewaan berhasil diupdate");
            exit;
        } else {
            throw new Exception("Stok tidak mencukupi");
        }
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: edit-barang-penyewaan.php?id=$id&status=error&message=" . $e->getMessage());
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Penyewaan Barang</title>
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
        <h2>Edit Penyewaan Barang</h2>

        <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
            <div class="alert alert-<?php echo $_GET['status'] === 'success' ? 'success' : 'danger'; ?>">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $penyewaan['id']; ?>">
            <input type="hidden" name="id_alat_lama" value="<?php echo $penyewaan['id_alat']; ?>">
            <input type="hidden" name="jumlah_lama" value="<?php echo $penyewaan['jumlah']; ?>">
            
            <div class="form-group">
                <label>Penyewa:</label><br>
                <?php if ($penyewaan['tipe_penyewa'] === 'umum'): ?>
                    <input type="text" value="<?php echo htmlspecialchars($penyewaan['nama_penyewa']); ?>" disabled>
                <?php else: ?>
                    <input type="text" value="<?php echo htmlspecialchars($penyewaan['nama_anggota']); ?>" disabled>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label>Alat:</label>
                <select name="id_alat" required>
                    <?php 
                    while($row = $alat->fetch_assoc()): 
                        $selected = ($row['id'] === $penyewaan['id_alat']) ? 'selected' : '';
                    ?>
                        <option value="<?php echo $row['id']; ?>" <?php echo $selected; ?>>
                            <?php echo htmlspecialchars($row['nama_alat']); ?> 
                            (Stok: <?php echo $row['jumlah'] + ($row['id'] === $penyewaan['id_alat'] ? $penyewaan['jumlah'] : 0); ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Jumlah:</label>
                <input type="number" name="jumlah" value="<?php echo $penyewaan['jumlah']; ?>" min="1" required>
            </div>
            
            <div class="form-group">
                <label>Tanggal Kembali:</label>
                <input type="date" name="tanggal_kembali" value="<?php echo $penyewaan['tanggal_kembali']; ?>">
                <small>(Kosongkan jika belum dikembalikan)</small>
            </div>
            
            <button type="submit">Update Penyewaan</button>
            <a href="penyewaan-barang.php">Kembali</a>
        </form>
    </div>
</body>
</html>

<?php $conn->close(); ?>
