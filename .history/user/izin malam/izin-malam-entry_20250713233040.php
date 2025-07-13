<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}
?>
<h2>Ajukan Izin Malam</h2>
<?php
require_once '../../includes/db_config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $_POST['tanggal'] ?? '';
    $jam_izin = $_POST['jam_izin'] ?? '';
    $jam_selesai_izin = $_POST['jam_selesai_izin'] ?? '';
    $alasan = $_POST['alasan'] ?? '';
    $status = 'Menunggu';
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $id_user = $_SESSION['user_id'];
    $result = $conn->prepare('SELECT id FROM anggota WHERE user_id = ? LIMIT 1');
    $result->bind_param('i', $id_user);
    $result->execute();
    $res = $result->get_result();
    $row = $res->fetch_assoc();
    $id_anggota = $row ? $row['id'] : null;
    $result->close();
    if ($id_anggota) {
        $stmt = $conn->prepare('INSERT INTO izin_malam (id_anggota, tanggal, jam_izin, jam_selesai_izin, alasan, status) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('isssss', $id_anggota, $tanggal, $jam_izin, $jam_selesai_izin, $alasan, $status);
        $stmt->execute();
        $stmt->close();
        echo '<p style="color:green">Pengajuan izin malam berhasil dikirim!</p>';
    } else {
        echo '<p style="color:red">Data anggota untuk user ini tidak ditemukan. Hubungi admin.</p>';
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
