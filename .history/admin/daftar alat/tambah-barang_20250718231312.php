<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_alat = $_POST['nama_alat'];
    $jumlah = $_POST['jumlah'];
    $kondisi = $_POST['kondisi'];
    
    $stmt = $conn->prepare("INSERT INTO alat (nama_alat, jumlah, kondisi) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $nama_alat, $jumlah, $kondisi);
    
    if ($stmt->execute()) {
        header("Location: daftar-alat.php?status=success&message=Alat berhasil ditambahkan");
    } else {
        header("Location: tambah-barang.php?status=error&message=Gagal menambahkan alat");
    }
    
    $stmt->close();
    $conn->close();
    exit;
}
include '../header.php'; // Path relatif dari 'anggota/'
?>

    <h2>Tambah Alat Baru</h2>
    
    <?php
    if (isset($_GET['status']) && isset($_GET['message'])) {
        echo "<div class='alert'>" . htmlspecialchars($_GET['message']) . "</div>";
    }
    ?>
    
    <form method="POST">
        <div>
            <label>Nama Alat:</label><br>
            <input type="text" name="nama_alat" required>
        </div>
        
        <div>
            <label>Jumlah:</label><br>
            <input type="number" name="jumlah" min="0" required>
        </div>
        
        <div>
            <label>Kondisi:</label><br>
            <select name="kondisi" required>
                <option value="Baik">Baik</option>
                <option value="Rusak Ringan">Rusak Ringan</option>
                <option value="Rusak Berat">Rusak Berat</option>
            </select>
        </div>
        
        <br>
        <button type="submit">Simpan</button>
        <a href="daftar-alat.php">Kembali</a>
    </form>
<?php
// Sertakan footer
include '../footer.php'; // Path relatif dari 'anggota/' ke 'includes/'
$conn->close();
?>
