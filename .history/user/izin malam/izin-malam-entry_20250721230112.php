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

    // Validasi input kosong
    if (empty($tanggal) || empty($jam_izin) || empty($jam_selesai_izin) || empty($alasan)) {
        $message = 'Semua field harus diisi!';
        $message_type = 'error';
    } else {
        // Cek apakah user ada di tabel anggota
        $result_anggota = $conn->query('SELECT id FROM anggota WHERE id = ' . (int)$id_anggota);
        if ($result_anggota && $result_anggota->num_rows > 0) {
            // Cek apakah sudah pernah izin di tanggal yang sama (opsional)
            $check = $conn->prepare('SELECT id FROM izin_malam WHERE id_anggota = ? AND tanggal = ?');
            $check->bind_param('is', $id_anggota, $tanggal);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $message = 'Kamu sudah mengajukan izin malam di tanggal tersebut.';
                $message_type = 'error';
            } else {
                // Simpan data
                $stmt = $conn->prepare('INSERT INTO izin_malam (id_anggota, tanggal, jam_izin, jam_selesai_izin, alasan) VALUES (?, ?, ?, ?, ?)');
                $stmt->bind_param('issss', $id_anggota, $tanggal, $jam_izin, $jam_selesai_izin, $alasan);
                if ($stmt->execute()) {
                    header("Location: " . $_SERVER['PHP_SELF'] . "?sukses=1");
                    exit;
                } else {
                    $message = 'Gagal mengajukan izin malam. Coba lagi.';
                    $message_type = 'error';
                }
                $stmt->close();
            }
            $check->close();
        } else {
            $message = 'Akun Anda belum terdaftar sebagai anggota.';
            $message_type = 'error';
        }
        $result_anggota->close();
    }
}

if (isset($_GET['sukses'])) {
    $message = 'Izin malam berhasil dikirim!';
    $message_type = 'success';
}

// Ambil data profil (ini tidak digunakan di sini, tapi tetap ada dari kode asli)
$profile_name = "User ";
$profile_photo = "default_profile.jpg";
$id_user = $_SESSION['user_id'];

include '../header_beckend.php';
include '../header.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajukan Izin Malam</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center py-10">

    <div class="container mx-auto px-4">
        <div class="bg-white p-8 rounded-lg shadow-xl max-w-md mx-auto">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">Form Pengajuan Izin Malam</h2>

            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="post" class="space-y-5">
                <div>
                    <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                    <input type="date" id="tanggal" name="tanggal" required class="form-input">
                </div>
                <div>
                    <label for="jam_izin" class="block text-sm font-medium text-gray-700 mb-1">Jam Izin</label>
                    <input type="time" id="jam_izin" name="jam_izin" required class="form-input">
                </div>
                <div>
                    <label for="jam_selesai_izin" class="block text-sm font-medium text-gray-700 mb-1">Jam Kembali</label>
                    <input type="time" id="jam_selesai_izin" name="jam_selesai_izin" required class="form-input">
                </div>
                <div>
                    <label for="alasan" class="block text-sm font-medium text-gray-700 mb-1">Alasan</label>
                    <input type="text" id="alasan" name="alasan" placeholder="Contoh: Menjenguk keluarga sakit" required class="form-input">
                </div>
                <div class="flex justify-center pt-4">
                    <button type="submit" class="btn-primary w-full">Ajukan Izin</button>
                </div>
            </form>
        </div>
    </div>
</main>
<?php
// Sertakan footer
include '../footer.php';
$conn->close();
?>
