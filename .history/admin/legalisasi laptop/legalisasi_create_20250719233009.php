<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Ambil daftar anggota untuk dropdown
$anggota = $conn->query("SELECT id, nama FROM anggota ORDER BY nama");
include '../header_beckend.php';
include '../header.php';
$conn->close();
?>

    <h2>Tambah Legalisasi Laptop</h2>
    
    <?php
    if (isset($_GET['status']) && isset($_GET['message'])) {
        echo "<div class='alert'>" . htmlspecialchars($_GET['message']) . "</div>";
    }
    ?>
    
    <form action="legalisasi_store.php" method="POST" enctype="multipart/form-data">
        <div>
            <label>Pilih Anggota:</label><br>
            <select name="id_anggota" required>
                <option value="">Pilih Anggota</option>
                <?php while($row = $anggota->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>">
                        <?php echo htmlspecialchars($row['nama']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div>
            <label>Merk Laptop:</label><br>
            <input type="text" name="merk" required>
        </div>
        <div>
            <label>Tipe:</label><br>
            <input type="text" name="tipe" required>
        </div>
        <div>
            <label>Serial Number:</label><br>
            <input type="text" name="serial_number" required>
        </div>
        <div>
            <label>Status Laptop:</label><br>
            <select name="status" required>
                <option value="">Pilih Status</option>
                <option value="Baik">Baik</option>
                <option value="Rusak">Rusak</option>
                <option value="Perlu Perbaikan">Perlu Perbaikan</option>
            </select>
        </div>
        <div>
            <label>Upload Bukti (Foto Laptop):</label><br>
            <input type="file" name="file_bukti" accept="image/*" required>
        </div>
        <br>
        <button type="submit">Simpan</button>
        <a href="legalisasi_list.php">Kembali</a>
    </form>
<?php
// Sertakan footer
include '../footer.php'; // Path relatif dari 'anggota/' ke 'includes/'
$conn->close();
?>