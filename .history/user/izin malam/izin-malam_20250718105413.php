<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}

// Aktifkan pelaporan error untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../includes/db_config.php';

// Pastikan koneksi database berhasil
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

$id_user_login = $_SESSION['user_id']; // Ini adalah ID dari tabel 'users'

// --- Bagian Baru: Menghubungkan users.id dengan anggota.id melalui email ---
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
    echo "<!-- DEBUG: Gagal menyiapkan statement untuk mengambil email user: " . $conn->error . " -->\n";
}

// 2. Gunakan email untuk mencari id di tabel 'anggota'
if ($user_email) {
    $stmt_get_anggota_id = $conn->prepare('SELECT id FROM anggota WHERE email = ?');
    if ($stmt_get_anggota_id) {
        $stmt_get_anggota_id->bind_param('s', $user_email); // 's' karena email adalah string
        $stmt_get_anggota_id->execute();
        $stmt_get_anggota_id->bind_result($fetched_anggota_id);
        if ($stmt_get_anggota_id->fetch()) {
            $id_anggota_untuk_izin = $fetched_anggota_id;
        }
        $stmt_get_anggota_id->close();
    } else {
        echo "<!-- DEBUG: Gagal menyiapkan statement untuk mengambil ID anggota: " . $conn->error . " -->\n";
    }
} else {
    echo "<!-- DEBUG: Email user tidak ditemukan di tabel 'users' untuk ID: " . $id_user_login . " -->\n";
}

// --- AKHIR Bagian Baru ---

// --- DEBUGGING: Pastikan $id_anggota_untuk_izin terdefinisi dengan benar ---
echo "<!-- DEBUG: ID Pengguna dari Sesi (users.id): " . $id_user_login . " -->\n";
echo "<!-- DEBUG: Email User: " . ($user_email ? $user_email : "Tidak ditemukan") . " -->\n";
echo "<!-- DEBUG: ID Anggota untuk Izin Malam (anggota.id): " . ($id_anggota_untuk_izin ? $id_anggota_untuk_izin : "Tidak ditemukan") . " -->\n";
// --- AKHIR DEBUGGING ---

// Inisialisasi array untuk menyimpan data izin
$izin_data = [];

// Sekarang, gunakan $id_anggota_untuk_izin untuk query izin_malam
if ($id_anggota_untuk_izin) {
    $stmt = $conn->prepare('SELECT tanggal, jam_izin, jam_selesai_izin, alasan, status FROM izin_malam WHERE id_anggota = ? ORDER BY tanggal DESC');
    if ($stmt) { // Pastikan statement berhasil disiapkan
        $stmt->bind_param('i', $id_anggota_untuk_izin);
        $stmt->execute();
        $izin_result = $stmt->get_result(); // Ganti nama variabel agar tidak bentrok dengan $izin_data

        // Ambil semua baris ke dalam array sebelum menutup koneksi
        while ($row = $izin_result->fetch_assoc()) {
            $izin_data[] = $row;
        }
        $izin_result->free(); // Bebaskan hasil query
        $stmt->close(); // Tutup statement
    } else {
        echo "<!-- DEBUG: Gagal menyiapkan statement izin malam: " . $conn->error . " -->\n";
    }
} else {
    echo "<!-- DEBUG: Tidak dapat mengambil riwayat izin malam karena ID anggota tidak ditemukan. -->\n";
}


$profile_name = "User"; // Default
$profile_photo = "default_profile.jpg"; // Default

// Gunakan $id_user_login untuk query profil (karena profile.id_user mengacu ke users.id)
$stmt_profile = $conn->prepare('SELECT nama_lengkap, foto FROM profile WHERE id_user = ?');
if ($stmt_profile === false) {
    echo "<!-- DEBUG: Gagal menyiapkan statement profil: " . $conn->error . " -->\n";
} else {
    $stmt_profile->bind_param('i', $id_user_login); // Menggunakan ID dari tabel users
    $stmt_profile->execute();
    $stmt_profile->bind_result($fetched_name, $fetched_photo);

    // --- DEBUGGING: Periksa apakah fetch berhasil ---
    if ($stmt_profile->fetch()) {
        $profile_name = htmlspecialchars($fetched_name);
        $profile_photo = htmlspecialchars($fetched_photo);
        echo "<!-- DEBUG: Profil berhasil diambil. Nama: " . $profile_name . ", Foto: " . $profile_photo . " -->\n";
    } else {
        echo "<!-- DEBUG: Gagal mengambil profil atau tidak ada data di tabel 'profile' untuk ID: " . $id_user_login . " -->\n";
        if ($stmt_profile->error) {
            echo "<!-- DEBUG: Error statement profil: " . $stmt_profile->error . " -->\n";
        }
    }
    // --- AKHIR DEBUGGING ---
    $stmt_profile->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Izin Malam</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        a { text-decoration: none; color: #007bff; }
        a:hover { text-decoration: underline; }

        /* Gaya Sidebar (dari kode Anda sebelumnya) */
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
<body>
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden ">
        <div class="flex h-screen">
            <?php include '../sidebar.php'; ?>

            <div class="flex-1 p-2">
                 <header class="bg-white border-b border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <!-- Tombol Toggle Sidebar -->
                            <button id="sidebarToggle" class="p-2 text-gray-600 hover:text-gray-800 focus:outline-none mr-4">
                                <i class="fas fa-bars text-xl"></i>
                            </button>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
                                <p class="text-gray-600"><?php echo date('l, d F Y'); ?></p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <div class="flex items-center space-x-2">
                                <div class="w-10 h-10 bg-white-600 rounded-full flex items-center justify-center overflow-hidden">
                                    <!-- Pastikan jalur gambar ini benar -->
                                    <img src="../../uploads/profiles/<?php echo $profile_photo; ?>" alt="Profile Photo" class="w-full h-full object-cover rounded-full">
                                </div>
                                <div class="flex items-center space-x-1">
                                    <a href="../profile/profile_view.php">
                                        <span class="font-medium text-gray-800"><?php echo $profile_name; ?></span>
                                        <i class="fas fa-chevron-down text-gray-600 text-sm"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <main class="p-6">
                    <h2 class="text-xl font-bold mb-4">Riwayat Izin Malam</h2>

                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-sm">
                            <thead>
                                <tr>
                                    <th class="py-3 px-4 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tanggal</th>
                                    <th class="py-3 px-4 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Jam Izin</th>
                                    <th class="py-3 px-4 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Jam Kembali</th>
                                    <th class="py-3 px-4 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Alasan</th>
                                    <th class="py-3 px-4 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($izin_data)): // Cek apakah array $izin_data tidak kosong ?>
                                    <?php foreach ($izin_data as $row) { // Loop melalui array $izin_data ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="py-3 px-4 text-sm text-gray-700"><?php echo htmlspecialchars($row['tanggal']); ?></td>
                                        <td class="py-3 px-4 text-sm text-gray-700"><?php echo htmlspecialchars($row['jam_izin']); ?></td>
                                        <td class="py-3 px-4 text-sm text-gray-700"><?php echo htmlspecialchars($row['jam_selesai_izin']); ?></td>
                                        <td class="py-3 px-4 text-sm text-gray-700"><?php echo htmlspecialchars($row['alasan']); ?></td>
                                        <td class="py-3 px-4 text-sm text-gray-700">
                                            <?php
                                            $status_class = '';
                                            switch ($row['status']) {
                                                case 'Menunggu': $status_class = 'text-yellow-700 bg-yellow-100'; break;
                                                case 'Disetujui': $status_class = 'text-green-700 bg-green-100'; break;
                                                case 'Ditolak': $status_class = 'text-red-700 bg-red-100'; break;
                                                default: $status_class = 'text-gray-700 bg-gray-100'; break;
                                            }
                                            echo '<span class="px-2 py-1 text-xs font-semibold leading-tight ' . $status_class . ' rounded-full">' . htmlspecialchars($row['status']) . '</span>';
                                            ?>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="py-3 px-4 text-center text-sm text-gray-500">Belum ada pengajuan izin malam.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </main>
            </div>
        </div>
    </div>
    <script>
        // Logika toggle Sidebar (sama seperti dashboard.php)
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
    </script>
</body>
</html>

<?php
// Pastikan $stmt ditutup hanya jika berhasil dibuat
// $stmt_get_user_email dan $stmt_get_anggota_id sudah ditutup di blok masing-masing
// $stmt_profile sudah ditutup di bloknya
// $stmt (untuk izin malam) sudah ditutup di bloknya

// Tutup koneksi database di akhir file, setelah semua data diambil
$conn->close();
?>
