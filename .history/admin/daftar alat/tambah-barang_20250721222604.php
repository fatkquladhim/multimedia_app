<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari formulir
    $nama_alat = $_POST['nama_alat'];
    $jumlah = $_POST['jumlah'];
    $kondisi = $_POST['kondisi'];
    $kelompok = $_POST['kelompok']; // Tambahkan ini
    $milik = $_POST['milik'];     // Tambahkan ini

    // Perbarui query INSERT
    $stmt = $conn->prepare("INSERT INTO alat (nama_alat, jumlah, kondisi, kelompok, milik) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sisss", $nama_alat, $jumlah, $kondisi, $kelompok, $milik); // Perbarui tipe parameter


    if ($stmt->execute()) {
        header("Location: daftar-alat.php?status=success&message=Alat berhasil ditambahkan");
    } else {
        header("Location: tambah-barang.php?status=error&message=Gagal menambahkan alat");
    }

    $stmt->close();
    exit;
}
include '../header_beckend.php';
include '../header.php';
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
    <div>
        <label>Kelompok:</label><br>
        <input type="text" name="kelompok">
    </div>

    <div>
        <label>Milik:</label><br>
        <input type="text" name="milik">
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