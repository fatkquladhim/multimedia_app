<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $_POST['tanggal'] ?? '';
    $jam_izin = $_POST['jam_izin'] ?? '';
    $jam_selesai_izin = $_POST['jam_selesai_izin'] ?? '';
    $alasan = $_POST['alasan'] ?? '';
    $id_anggota = $_SESSION['user_id'];

    // Validasi: cek apakah id user ada di anggota
    $result_anggota = $conn->query('SELECT id FROM anggota WHERE id = ' . $id_anggota); // Direct query for check
    if ($result_anggota->num_rows > 0) {
        $stmt = $conn->prepare('INSERT INTO izin_malam (id_anggota, tanggal, jam_izin, jam_selesai_izin, alasan) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('issss', $id_anggota, $tanggal, $jam_izin, $jam_selesai_izin, $alasan);
        if ($stmt->execute()) {
            $message = 'izin malam berhasil dikirim!';
            $message_type = 'success';
        } else {
            $message = 'Gagal mengajukan izin malam. Pastikan akun Anda terdaftar sebagai anggota.';
            $message_type = 'error';
        }
        $stmt->close();
    } else {
        $message = 'Akun Anda belum terdaftar sebagai anggota. Hubungi admin untuk pendaftaran.';
        $message_type = 'error';
    }
    $result_anggota->close();
}

// Fetch profile information before closing the connection
$profile_name = "User "; // Default
$profile_photo = "default_profile.jpg"; // Default
$id_user = $_SESSION['user_id']; // Make sure to get the user ID from the session

include '../header_beckend.php';
include '../header.php';
?>

<main class="p-6">
    <?php if ($message): ?>
        <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="bg-white p-6 rounded-lg shadow-md max-w-md mx-auto">
        <form method="post">
            <div class="form-group">
                <label for="tanggal">Tanggal</label>
                <input type="date" id="tanggal" name="tanggal" required>
            </div>
            <div class="form-group">
                <label for="jam_izin">Jam Izin</label>
                <input type="time" id="jam_izin" name="jam_izin" required>
            </div>
            <div class="form-group">
                <label for="jam_selesai_izin">Jam Kembali</label>
                <input type="time" id="jam_selesai_izin" name="jam_selesai_izin" required>
            </div>
            <div class="form-group">
                <label for="alasan">Alasan</label>
                <input type="text" id="alasan" name="alasan" placeholder="Alasan" required>
            </div>
            <div class="flex space-x-4 mt-6">
                <button type="submit" class="btn btn-primary">Ajukan</button>
            </div>
        </form>
    </div>
    <?php
    // Sertakan footer
    include '../footer.php'; // Path relatif dari 'anggota/' ke 'includes/'
    $conn->close();
    ?>