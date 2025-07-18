<?php
session_start();
require_once '../../includes/db_config.php';

// Periksa login dan role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}

// Aktifkan pelaporan error untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inisialisasi variabel
$errors = [];
$success = false;

// --- Koneksi Database ---
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

$id_user_login = $_SESSION['user_id']; // Ini adalah ID dari tabel 'users'

// --- Bagian Penting: Menghubungkan users.id dengan anggota.id melalui email ---
$id_anggota_untuk_izin = null;
$user_email = null;

// 1. Ambil email dari tabel 'users' berdasarkan id_user_login
$stmt_get_user_email = $conn->prepare('SELECT email FROM users WHERE id = ?');
if ($stmt_get_user_email) {
    $stmt_get_user_email->bind_param('i', $id_user_login);
    $stmt_get_user_email->execute();
    $stmt_get_user_email->bind_result($fetched_user_email);
    if ($stmt_get_user_email->fetch()) {
        $user_email = $fetched_user_email;
    }
    $stmt_get_user_email->close();
} else {
    error_log("Gagal menyiapkan statement untuk mengambil email user: " . $conn->error);
}

// 2. Gunakan email untuk mencari id di tabel 'anggota'
if ($user_email) {
    $stmt_get_anggota_id = $conn->prepare('SELECT id FROM anggota WHERE email = ?');
    if ($stmt_get_anggota_id) {
        $stmt_get_anggota_id->bind_param('s', $user_email);
        $stmt_get_anggota_id->execute();
        $stmt_get_anggota_id->bind_result($fetched_anggota_id);
        if ($stmt_get_anggota_id->fetch()) {
            $id_anggota_untuk_izin = $fetched_anggota_id;
        }
        $stmt_get_anggota_id->close();
    } else {
        error_log("Gagal menyiapkan statement untuk mengambil ID anggota: " . $conn->error);
    }
} else {
    error_log("Email user tidak ditemukan di tabel 'users' untuk ID: " . $id_user_login);
}
// --- AKHIR Bagian Penting ---

// --- Proses Form Pengajuan Izin ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pastikan id_anggota_untuk_izin sudah ditemukan sebelum memproses form
    if ($id_anggota_untuk_izin === null) {
        $errors[] = 'Data anggota tidak ditemukan. Tidak dapat mengajukan izin.';
    } else {
        $tanggal = $_POST['tanggal'] ?? '';
        $jam_izin = $_POST['jam_izin'] ?? '';
        $jam_selesai_izin = $_POST['jam_selesai_izin'] ?? '';
        $alasan = $_POST['alasan'] ?? '';

        // Validasi input
        if (empty($tanggal)) {
            $errors[] = 'Tanggal izin harus diisi.';
        }
        if (empty($jam_izin)) {
            $errors[] = 'Jam keluar harus diisi.';
        }
        if (empty($jam_selesai_izin)) {
            $errors[] = 'Jam kembali harus diisi.';
        }
        if (empty($alasan) || strlen($alasan) < 10) {
            $errors[] = 'Alasan harus diisi minimal 10 karakter.';
        }

        // Jika tidak ada error, simpan ke database
        if (empty($errors)) {
            $stmt_insert = $conn->prepare('INSERT INTO izin_malam
                                   (id_anggota, tanggal, jam_izin, jam_selesai_izin, alasan, status)
                                   VALUES (?, ?, ?, ?, ?, "Menunggu")');
            if ($stmt_insert) {
                $stmt_insert->bind_param('issss', $id_anggota_untuk_izin, $tanggal, $jam_izin, $jam_selesai_izin, $alasan);
                if ($stmt_insert->execute()) {
                    $success = true;
                    // Kosongkan input form setelah sukses
                    $_POST = [];
                } else {
                    $errors[] = 'Gagal menyimpan data izin: ' . $stmt_insert->error;
                }
                $stmt_insert->close();
            } else {
                $errors[] = 'Gagal menyiapkan statement INSERT: ' . $conn->error;
            }
        }
    }
}

// --- Ambil Data Profil untuk Header ---
$profile_name = "User";
$profile_photo = "default_profile.jpg";

$stmt_profile = $conn->prepare('SELECT nama_lengkap, foto FROM profile WHERE id_user = ?');
if ($stmt_profile) {
    $stmt_profile->bind_param('i', $id_user_login);
    $stmt_profile->execute();
    $stmt_profile->bind_result($fetched_name, $fetched_photo);
    if ($stmt_profile->fetch()) {
        $profile_name = htmlspecialchars($fetched_name);
        $profile_photo = htmlspecialchars($fetched_photo);
    }
    $stmt_profile->close();
} else {
    error_log("Gagal menyiapkan statement profil: " . $conn->error);
}

// Tutup koneksi database di akhir file, setelah semua data diambil
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Izin Malam</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar { transition: width 0.3s ease-in-out; }
        .sidebar-text { transition: opacity 0.3s ease-in-out, margin-left 0.3s ease-in-out; }
        .sidebar-nav-item { justify-content: flex-start; }
        .sidebar.collapsed .sidebar-nav-item { justify-content: center; }
        .sidebar.collapsed .sidebar-text { opacity: 0; width: 0; overflow: hidden; white-space: nowrap; pointer-events: none; }
        .sidebar.collapsed .sidebar-logo-text { opacity: 0; width: 0; overflow: hidden; white-space: nowrap; pointer-events: none; }
        .sidebar.collapsed .sidebar-logo-icon { margin-right: 0 !important; }
        .sidebar.collapsed .sidebar-create-button .sidebar-text { opacity: 0; width: 0; overflow: hidden; white-space: nowrap; pointer-events: none; }
        .sidebar.collapsed .sidebar-create-button i { margin-right: 0 !important; }
        .sidebar.collapsed .sidebar-upgrade-section { opacity: 0; height: 0; overflow: hidden; padding-top: 0; padding-bottom: 0; margin-top: 0; pointer-events: none; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex h-screen">
        <?php include '../sidebar.php'; ?>

        <div class="flex-1 p-4 overflow-y-auto">
            <header class="bg-white border-b border-gray-200 p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <button id="sidebarToggle" class="p-2 text-gray-600 hover:text-gray-800 focus:outline-none mr-4">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800">Pengajuan Izin Malam</h1>
                            <p class="text-gray-600"><?php echo date('l, d F Y'); ?></p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <div class="w-10 h-10 rounded-full overflow-hidden">
                            <img src="../../uploads/profiles/<?php echo $profile_photo; ?>"
                                 alt="Foto Profil"
                                 class="w-full h-full object-cover">
                        </div>
                        <div>
                            <span class="font-medium text-gray-800"><?php echo $profile_name; ?></span>
                        </div>
                    </div>
                </div>
            </header>

            <main class="p-4">
                <!-- Formulir Pengajuan -->
                <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Formulir Izin Malam</h2>

                    <?php if (!empty($errors)): ?>
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            <ul class="list-disc pl-5">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                            Pengajuan izin malam berhasil dikirim!
                        </div>
                        <a href="izin-malam.php" class="inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 mb-4">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Riwayat Izin
                        </a>
                    <?php endif; ?>

                    <?php if (!$success): // Tampilkan form hanya jika belum sukses ?>
                        <form method="POST" action="">
                            <div class="mb-4">
                                <label for="tanggal" class="block text-gray-700 mb-2">Tanggal Izin</label>
                                <input type="date" id="tanggal" name="tanggal"
                                       value="<?php echo htmlspecialchars($_POST['tanggal'] ?? date('Y-m-d')); ?>"
                                       class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="jam_izin" class="block text-gray-700 mb-2">Jam Keluar</label>
                                    <input type="time" id="jam_izin" name="jam_izin"
                                           value="<?php echo htmlspecialchars($_POST['jam_izin'] ?? ''); ?>"
                                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label for="jam_selesai_izin" class="block text-gray-700 mb-2">Jam Kembali</label>
                                    <input type="time" id="jam_selesai_izin" name="jam_selesai_izin"
                                           value="<?php echo htmlspecialchars($_POST['jam_selesai_izin'] ?? ''); ?>"
                                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="alasan" class="block text-gray-700 mb-2">Alasan Izin</label>
                                <textarea id="alasan" name="alasan" rows="4"
                                          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($_POST['alasan'] ?? ''); ?></textarea>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                                    <i class="fas fa-paper-plane mr-2"></i>Kirim Permohonan
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Sidebar toggle script (sama seperti sebelumnya)
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarTexts = document.querySelectorAll('.sidebar-text');
        const sidebarLogoText = document.querySelector('.sidebar-logo-text');
        const sidebarLogoIcon = document.querySelector('.sidebar-logo-icon');
        const sidebarNavItems = document.querySelectorAll('.sidebar-nav-item');
        const sidebarCreateButton = document.querySelector('.sidebar-create-button');
        const sidebarUpgradeSection = document.querySelector('.sidebar-upgrade-section');

        let isSidebarOpen = true;

        sidebarToggle.addEventListener('click', () => {
            if (isSidebarOpen) {
                sidebar.classList.remove('w-64');
                sidebar.classList.add('w-20', 'collapsed');

                sidebarTexts.forEach(text => { text.classList.add('opacity-0', 'pointer-events-none'); });
                if (sidebarLogoText) sidebarLogoText.classList.add('opacity-0', 'pointer-events-none');
                if (sidebarUpgradeSection) sidebarUpgradeSection.classList.add('opacity-0', 'h-0', 'p-0', 'mt-0', 'pointer-events-none');

                if (sidebarLogoIcon) {
                    sidebarLogoIcon.classList.remove('space-x-2');
                    sidebarLogoIcon.classList.add('mx-auto');
                }
                sidebarNavItems.forEach(item => {
                    item.classList.remove('space-x-3', 'px-4');
                    item.classList.add('justify-center', 'px-0');
                });
                if (sidebarCreateButton) {
                    sidebarCreateButton.classList.remove('space-x-2');
                    sidebarCreateButton.classList.add('justify-center');
                    if (sidebarCreateButton.querySelector('button')) {
                        sidebarCreateButton.querySelector('button').classList.remove('justify-center');
                        sidebarCreateButton.querySelector('button').classList.add('space-x-2');
                    }
                }

                sidebarToggle.querySelector('i').classList.replace('fa-bars', 'fa-arrow-right');

            } else {
                sidebar.classList.remove('w-20', 'collapsed');
                sidebar.classList.add('w-64');

                sidebarTexts.forEach(text => { text.classList.remove('opacity-0', 'pointer-events-none'); });
                if (sidebarLogoText) sidebarLogoText.classList.remove('opacity-0', 'pointer-events-none');
                if (sidebarUpgradeSection) sidebarUpgradeSection.classList.remove('opacity-0', 'h-0', 'p-0', 'mt-0', 'pointer-events-none');

                if (sidebarLogoIcon) {
                    sidebarLogoIcon.classList.remove('mx-auto');
                    sidebarLogoIcon.classList.add('space-x-2');
                }
                sidebarNavItems.forEach(item => {
                    item.classList.remove('justify-center', 'px-0');
                    item.classList.add('space-x-3', 'px-4');
                });
                if (sidebarCreateButton) {
                    sidebarCreateButton.classList.remove('justify-center');
                    sidebarCreateButton.classList.add('space-x-2');
                    if (sidebarCreateButton.querySelector('button')) {
                        sidebarCreateButton.querySelector('button').classList.remove('justify-center');
                        sidebarCreateButton.querySelector('button').classList.add('space-x-2');
                    }
                }

                sidebarToggle.querySelector('i').classList.replace('fa-arrow-right', 'fa-bars');
            }
            isSidebarOpen = !isSidebarOpen;
        });

        // Set default date to today
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            const tanggalInput = document.getElementById('tanggal');
            // Hanya set jika input kosong (misal setelah submit sukses)
            if (!tanggalInput.value) {
                tanggalInput.value = today;
            }
        });
    </script>
</body>
</html>
