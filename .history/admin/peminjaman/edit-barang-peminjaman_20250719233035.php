<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Ambil data peminjaman yang akan diedit
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT pb.*, a.nama as nama_anggota, al.nama_alat as nama_alat 
                           FROM peminjaman_barang pb 
                           LEFT JOIN anggota a ON pb.id_anggota = a.id 
                           LEFT JOIN alat al ON pb.id_alat = al.id 
                           WHERE pb.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $peminjaman = $result->fetch_assoc();

    if (!$peminjaman) {
        header("Location: peminjaman-barang.php?status=error&message=Peminjaman tidak ditemukan");
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
            // Update peminjaman
            $stmt = $conn->prepare("UPDATE peminjaman_barang SET id_alat = ?, jumlah = ?, tanggal_kembali = ? WHERE id = ?");
            $stmt->bind_param("iisi", $id_alat_baru, $jumlah_baru, $tanggal_kembali, $id);
            $stmt->execute();
            
            // Kurangi stok baru
            $stmt = $conn->prepare("UPDATE alat SET jumlah = jumlah - ? WHERE id = ?");
            $stmt->bind_param("ii", $jumlah_baru, $id_alat_baru);
            $stmt->execute();
            
            $conn->commit();
            header("Location: peminjaman-barang.php?status=success&message=Peminjaman berhasil diupdate");
            exit;
        } else {
            throw new Exception("Stok tidak mencukupi");
        }
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: edit-barang-peminjaman.php?id=$id&status=error&message=" . $e->getMessage());
        exit;
    }
}
include '../header_beckend.php';
include '../header.php';
$conn->close();
?>
    <div class="container">
        <h2>Edit Peminjaman Barang</h2>
        
        <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
            <div class="alert alert-<?php echo $_GET['status'] === 'success' ? 'success' : 'danger'; ?>">
                <?php echo htmlspecialchars($_GET['message']); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $peminjaman['id']; ?>">
            <input type="hidden" name="id_alat_lama" value="<?php echo $peminjaman['id_alat']; ?>">
            <input type="hidden" name="jumlah_lama" value="<?php echo $peminjaman['jumlah']; ?>">
            
            <div class="form-group">
                <label>Peminjam:</label><br>
                <?php if ($peminjaman['tipe_peminjam'] === 'umum'): ?>
                    <input type="text" value="<?php echo htmlspecialchars($peminjaman['nama_peminjam']); ?>" disabled>
                <?php else: ?>
                    <input type="text" value="<?php echo htmlspecialchars($peminjaman['nama_anggota']); ?>" disabled>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label>Alat:</label>
                <select name="id_alat" required>
                    <?php 
                    while($row = $alat->fetch_assoc()): 
                        $selected = ($row['id'] === $peminjaman['id_alat']) ? 'selected' : '';
                    ?>
                        <option value="<?php echo $row['id']; ?>" <?php echo $selected; ?>>
                            <?php echo htmlspecialchars($row['nama_alat']); ?> 
                            (Stok: <?php echo $row['jumlah'] + ($row['id'] === $peminjaman['id_alat'] ? $peminjaman['jumlah'] : 0); ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Jumlah:</label>
                <input type="number" name="jumlah" value="<?php echo $peminjaman['jumlah']; ?>" min="1" required>
            </div>
            
            <div class="form-group">
                <label>Tanggal Kembali:</label>
                <input type="date" name="tanggal_kembali" value="<?php echo $peminjaman['tanggal_kembali']; ?>">
                <small>(Kosongkan jika belum dikembalikan)</small>
            </div>
            
            <button type="submit">Update Peminjaman</button>
            <a href="peminjaman-barang.php">Kembali</a>
        </form>
    </div>
<?php
// Sertakan footer
include '../footer.php'; // Path relatif dari 'anggota/' ke 'includes/'
$conn->close();
?>
