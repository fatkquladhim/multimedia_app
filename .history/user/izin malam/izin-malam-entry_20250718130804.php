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
            $message = 'Pengajuan izin malam berhasil dikirim!';
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

$stmt_profile = $conn->prepare('SELECT nama_lengkap, foto FROM profile WHERE id_user = ?');
if ($stmt_profile) {
    $stmt_profile->bind_param('i', $id_user);
    $stmt_profile->execute();
    $stmt_profile->bind_result($fetched_name, $fetched_photo);
    if ($stmt_profile->fetch()) {
        $profile_name = htmlspecialchars($fetched_name);
        $profile_photo = htmlspecialchars($fetched_photo);
    }
    $stmt_profile->close();
} else {
    // Handle error if the statement could not be prepared
    $message = 'Error preparing statement for profile fetch.';
    $message_type = 'error';
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajukan Izin Malam</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {"50":"#eff6ff","100":"#dbeafe","200":"#bfdbfe","300":"#93c5fd","400":"#60a5fa","500":"#3b82f6","600":"#2563eb","700":"#1d4ed8","800":"#1e40af","900":"#1e3a8a","950":"#172554"}
                    }
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style type="text/tailwindcss">
        @layer components {
            .form-input { 
                @apply w-full p-3 border border-gray-300 rounded-lg dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500;
            }
            .btn-primary {
                @apply bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200;
            }
            .btn-secondary {
                @apply bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition duration-200;
            }
            .message {
                @apply p-4 mb-4 rounded-lg;
            }
            .success {
                @apply bg-green-100 border border-green-400 text-green-700 dark:bg-green-800 dark:text-green-100;
            }
            .error {
                @apply bg-red-100 border border-red-400 text-red-700 dark:bg-red-800 dark:text-red-100;
            }
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900">
    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl overflow-hidden min-h-screen">
        <div class="flex">
            <?php include '../sidebar.php'; ?>

            <div class="flex-1 p-2">
                <?php include '../header_frontend.php'; ?>

                <main class="p-6">
                    <?php if ($message): ?>
                        <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
                    <?php endif; ?>

                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md max-w-md mx-auto border border-gray-200 dark:border-gray-700">
                        <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Ajukan Izin Malam</h2>
                        <form method="post" class="space-y-4">
                            <div>
                                <label for="tanggal" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tanggal</label>
                                <input type="date" id="tanggal" name="tanggal" class="form-input" required>
                            </div>
                            <div>
                                <label for="jam_izin" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jam Izin</label>
                                <input type="time" id="jam_izin" name="jam_izin" class="form-input" required>
                            </div>
                            <div>
                                <label for="jam_selesai_izin" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jam Kembali</label>
                                <input type="time" id="jam_selesai_izin" name="jam_selesai_izin" class="form-input" required>
                            </div>
                            <div>
                                <label for="alasan" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Alasan</label>
                                <input type="text" id="alasan" name="alasan" placeholder="Alasan" class="form-input" required>
                            </div>
                            <div class="flex space-x-4">
                                <button type="submit" class="btn-primary">Ajukan</button>
                            </div>
                        </form>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <script>
        // Dark mode toggle handler
        const themeToggle = document.getElementById('theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', function() {
                document.documentElement.classList.toggle('dark');
                localStorage.setItem('color-theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
            });
        }

        // Sidebar toggle logic
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');

        let isSidebarOpen = localStorage.getItem('sidebarState') === 'open' || localStorage.getItem('sidebarState') === null;

        function toggleSidebar() {
            isSidebarOpen = !isSidebarOpen;
            localStorage.setItem('sidebarState', isSidebarOpen ? 'open' : 'closed');
            
            if (isSidebarOpen) {
                sidebar.classList.remove('w-20', 'collapsed');
                sidebar.classList.add('w-64');
                document.querySelectorAll('.sidebar-text').forEach(text => text.classList.remove('opacity-0', 'pointer-events-none'));
                if (sidebarLogoText) sidebarLogoText.classList.remove('opacity-0', 'pointer-events-none');
                
                document.querySelector('.sidebar-toggle-icon').classList.replace('fa-arrow-right', 'fa-bars');
            } else {
                sidebar.classList.remove('w-64');
                sidebar.classList.add('w-20', 'collapsed');
                document.querySelectorAll('.sidebar-text').forEach(text => text.classList.add('opacity-0', 'pointer-events-none'));
                if (sidebarLogoText) sidebarLogoText.classList.add('opacity-0', 'pointer-events-none');
                
                document.querySelector('.sidebar-toggle-icon').classList.replace('fa-bars', 'fa-arrow-right');
            }
        }

        // Initialize sidebar state
        if (sidebarToggle) {
            toggleSidebar(); // Set initial state
            sidebarToggle.addEventListener('click', toggleSidebar);
        }

        // Check for saved theme preference
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
</body>
</html>

