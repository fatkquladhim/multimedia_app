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
echo '<pre style="color:blue">Debug: id user dari session = ' . htmlspecialchars($_SESSION['user_id']) . "\n";
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$result = $conn->query('SELECT id FROM anggota');
echo "id anggota di database: ";
while ($row = $result->fetch_assoc()) {
    echo $row['id'] . ' ';
}
echo "</pre>";
$result->close();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $_POST['tanggal'] ?? '';
    $jam_izin = $_POST['jam_izin'] ?? '';
    $jam_selesai_izin = $_POST['jam_selesai_izin'] ?? '';
    $alasan = $_POST['alasan'] ?? '';
    $status = 'Menunggu';
    $id_anggota = $_SESSION['user_id'];
    // Validasi: cek apakah id user ada di anggota
    $result = $conn->query('SELECT id FROM anggota');
    $anggota_ids = array();
    while ($row = $result->fetch_assoc()) {
        $anggota_ids[] = $row['id'];
    }
    $result->close();
    if (in_array($id_anggota, $anggota_ids)) {
        $stmt = $conn->prepare('INSERT INTO izin_nugas (id_anggota, tanggal, jam_izin, jam_selesai_izin, alasan, status) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('isssss', $id_anggota, $tanggal, $jam_izin, $jam_selesai_izin, $alasan, $status);
        if ($stmt->execute()) {
            echo '<p style="color:green">Pengajuan izin nugas berhasil dikirim!</p>';
        } else {
            echo '<p style="color:red">Gagal mengajukan izin nugas. Pastikan akun Anda terdaftar sebagai anggota.</p>';
        }
        $stmt->close();
    } else {
        echo '<p style="color:red">Akun Anda belum terdaftar sebagai anggota. Hubungi admin untuk pendaftaran.</p>';
    }
    $conn->close();
}
?>
<form method="post">
    <input type="date" name="tanggal" required><br>
    <input type="time" name="jam_izin" required placeholder="Jam Izin"><br>
    <input type="time" name="jam_selesai_izin" required placeholder="Jam Kembali"><br>
    <input type="text" name="alasan" placeholder="Alasan" required><br>
    <button type="submit">Ajukan</button>
</form>
