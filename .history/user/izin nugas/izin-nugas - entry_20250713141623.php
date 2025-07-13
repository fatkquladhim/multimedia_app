<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}
?>
<h2>Ajukan Izin Nugas</h2>
<?php
require_once '../../includes/db_config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $_POST['tanggal'] ?? '';
    $alasan = $_POST['alasan'] ?? '';
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    // Solusi sementara: ambil id anggota pertama
    $result = $conn->query('SELECT id FROM anggota LIMIT 1');
    $row = $result->fetch_assoc();
    $id_anggota = $row ? $row['id'] : 1;
    $stmt = $conn->prepare('INSERT INTO izin_nugas (id_anggota, tanggal, alasan) VALUES (?, ?, ?)');
    $stmt->bind_param('iss', $id_anggota, $tanggal, $alasan);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    echo '<p style="color:green">Pengajuan izin nugas berhasil dikirim!</p>';
}
?>
<form method="post">
    <input type="date" name="tanggal" required><br>
    <input type="text" name="alasan" placeholder="Alasan" required><br>
    <button type="submit">Ajukan</button>
</form>
