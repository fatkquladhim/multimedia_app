<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../includes/db_config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset("utf8mb4");

    // Statistik
    $user_count = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
    $anggota_count = $conn->query("SELECT COUNT(*) FROM anggota")->fetch_row()[0];
    $tugas_count = $conn->query("SELECT COUNT(*) FROM tugas")->fetch_row()[0];
    $izin_malam_count = $conn->query("SELECT COUNT(*) FROM izin_malam")->fetch_row()[0];
    $izin_nugas_count = $conn->query("SELECT COUNT(*) FROM izin_nugas")->fetch_row()[0];

    // Query tugas review
    $stmt_tugas_review = $conn->prepare("SELECT t.id, t.judul AS nama_tugas, t.deskripsi, u.username, 
                                        tj.file_jawaban, tj.tanggal_submit, tj.id_user
                                        FROM tugas t 
                                        JOIN tugas_jawaban tj ON t.id = tj.id_tugas
                                        JOIN users u ON tj.id_user = u.id 
                                        WHERE t.status = 'dikirim' LIMIT 3");
    $stmt_tugas_review->execute();
    $tugas_reviews = $stmt_tugas_review->get_result();

    // Query izin malam
    $stmt_izin_malam = $conn->prepare("SELECT a.id, a.nama, im.tanggal, 
                                      DATE_FORMAT(im.jam_izin, '%H:%i') as jam_izin, 
                                      DATE_FORMAT(im.jam_selesai_izin, '%H:%i') as jam_selesai_izin,
                                      im.alasan
                                      FROM izin_malam im 
                                      JOIN anggota a ON im.id_anggota = a.id 
                                      WHERE im.tanggal >= CURDATE() 
                                      ORDER BY im.tanggal, im.jam_izin LIMIT 4");
    $stmt_izin_malam->execute();
    $izin_malam_anggota = $stmt_izin_malam->get_result();

    // Pagination anggota
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = 10;
    $offset = ($page - 1) * $per_page;

    $total_anggota = $conn->query("SELECT COUNT(*) FROM anggota")->fetch_row()[0];
    $total_pages = ceil($total_anggota / $per_page);

    $stmt_all_anggota = $conn->prepare("SELECT id, nama, email, no_hp FROM anggota 
                                       ORDER BY nama LIMIT ?, ?");
    $stmt_all_anggota->bind_param("ii", $offset, $per_page);
    $stmt_all_anggota->execute();
    $all_anggota = $stmt_all_anggota->get_result();

    // ✅ Ambil profil user admin
    $profile_name = "Admin"; // Default
    $profile_photo = "default_profile.jpg"; // Default
    $id_user = $_SESSION['user_id'] ?? 0;

    $stmt_profile = $conn->prepare('SELECT nama_lengkap, foto FROM profile WHERE id_user = ?');
    $stmt_profile->bind_param('i', $id_user);
    $stmt_profile->execute();
    $stmt_profile->bind_result($fetched_name, $fetched_photo);
    if ($stmt_profile->fetch()) {
        $profile_name = htmlspecialchars($fetched_name);
        $profile_photo = htmlspecialchars($fetched_photo);
    }
    $stmt_profile->close();
} catch (mysqli_sql_exception $e) {
    error_log("Database error in dashboard.php: " . $e->getMessage());
    die("Terjadi kesalahan pada database. Mohon coba lagi nanti.");
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'light-blue': {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .sidebar {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .main-content-area {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-nav-item {
            transition: all 0.3s ease-in-out;
        }

        .dark-mode-transition {
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
             background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
        }

        .dark .gradient-bg {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        }

        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.9);
        }

        .dark .glass-effect {
            background: rgba(30, 41, 59, 0.9);
        }

        .hover-scale {
            transition: transform 0.2s ease;
        }

        .hover-scale:hover {
            transform: scale(1.02);
        }

        .animate-fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .mobile-menu {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .mobile-menu.active {
            transform: translateX(0);
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                z-index: 50;
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 40;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }

            .sidebar-overlay.active {
                opacity: 1;
                visibility: visible;
            }
        }
    </style>
</head>

<body class="gradient-bg dark-mode-transition">

    <div id="sidebarOverlay" class="sidebar-overlay md:hidden"></div>

    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div id="sidebar" class="w-64 bg-white dark:bg-slate-800 shadow-xl flex-shrink-0 sidebar transition-all duration-300 overflow-hidden glass-effect">
            <div class="p-4 flex items-center justify-between border-b border-light-blue-100 dark:border-slate-700">
                <div class="flex items-center space-x-6">
                    <div class="w-12 h-12 bg-light-blue-100 rounded-full flex items-center justify-center">
                        <img src="../public/assets/imgs/rev-removebg-preview.png">
                    </div>
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white">Multimedia</h1>
                </div>
                <button id="closeSidebar" class="md:hidden p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <nav class="mt-6 px-4">
                <div class="space-y-2">
                    <a href="dashboard.php" class="flex items-center px-4 py-3 text-light-blue-600 bg-light-blue-50 dark:bg-light-blue-900 dark:text-light-blue-300 rounded-lg sidebar-nav-item hover-scale">
                        <i class="fas fa-tachometer-alt w-5 h-5 mr-3"></i>
                        <span class="font-medium">Dashboard</span>
                    </a>

                    <a href="anggota/anggota.php" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-light-blue-600 dark:hover:text-light-blue-400 hover:bg-light-blue-50 dark:hover:bg-slate-700 rounded-lg sidebar-nav-item hover-scale">
                        <i class="fas fa-users w-5 h-5 mr-3"></i>
                        <span class="font-medium">Manajemen Anggota</span>
                    </a>

                    <a href="daftar alat/daftar-alat.php" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-light-blue-600 dark:hover:text-light-blue-400 hover:bg-light-blue-50 dark:hover:bg-slate-700 rounded-lg sidebar-nav-item hover-scale">
                        <i class="fas fa-tools w-5 h-5 mr-3"></i>
                        <span class="font-medium">Manajemen Alat</span>
                    </a>

                    <a href="peminjaman/peminjaman-barang.php" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-light-blue-600 dark:hover:text-light-blue-400 hover:bg-light-blue-50 dark:hover:bg-slate-700 rounded-lg sidebar-nav-item hover-scale">
                        <i class="fas fa-handshake w-5 h-5 mr-3"></i>
                        <span class="font-medium">Peminjaman Barang</span>
                    </a>

                    <a href="penyewaan/penyewaan-barang.php" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-light-blue-600 dark:hover:text-light-blue-400 hover:bg-light-blue-50 dark:hover:bg-slate-700 rounded-lg sidebar-nav-item hover-scale">
                        <i class="fas fa-cash-register w-5 h-5 mr-3"></i>
                        <span class="font-medium">Penyewaan Barang</span>
                    </a>

                    <a href="legalisasi laptop/legalisasi_list.php" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-light-blue-600 dark:hover:text-light-blue-400 hover:bg-light-blue-50 dark:hover:bg-slate-700 rounded-lg sidebar-nav-item hover-scale">
                        <i class="fas fa-laptop w-5 h-5 mr-3"></i>
                        <span class="font-medium">Legalisasi Laptop</span>
                    </a>
                </div>

                <div class="mt-8 mb-4">
                    <h3 class="px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Tugas & Izin
                    </h3>
                </div>

                <div class="space-y-2">
                    <a href="beri tugas/beri_tugas_form.php" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-light-blue-600 dark:hover:text-light-blue-400 hover:bg-light-blue-50 dark:hover:bg-slate-700 rounded-lg sidebar-nav-item hover-scale">
                        <i class="fas fa-clipboard-list w-5 h-5 mr-3"></i>
                        <span class="font-medium">Beri Tugas</span>
                    </a>

                    <a href="beri tugas/tugas_selesai_riwayat.php" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-light-blue-600 dark:hover:text-light-blue-400 hover:bg-light-blue-50 dark:hover:bg-slate-700 rounded-lg sidebar-nav-item hover-scale">
                        <i class="fas fa-check-double w-5 h-5 mr-3"></i>
                        <span class="font-medium">Riwayat Tugas</span>
                    </a>

                    <a href="izin_malam/izin-malam.php" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-light-blue-600 dark:hover:text-light-blue-400 hover:bg-light-blue-50 dark:hover:bg-slate-700 rounded-lg sidebar-nav-item hover-scale">
                        <i class="fas fa-moon w-5 h-5 mr-3"></i>
                        <span class="font-medium">Izin Malam</span>
                    </a>

                    <a href="izin_nugas/izin-nugas.php" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-light-blue-600 dark:hover:text-light-blue-400 hover:bg-light-blue-50 dark:hover:bg-slate-700 rounded-lg sidebar-nav-item hover-scale">
                        <i class="fas fa-book-open w-5 h-5 mr-3"></i>
                        <span class="font-medium">Izin Nugas</span>
                    </a>

                    <a href="./keuangan/manage_uang.php" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-light-blue-600 dark:hover:text-light-blue-400 hover:bg-light-blue-50 dark:hover:bg-slate-700 rounded-lg sidebar-nav-item hover-scale">
                        <i class="fas fa-money-bill-alt w-5 h-5 mr-3"></i>
                        <span class="font-medium">Keuangan</span>
                    </a>
                </div>

                <div class="mt-8 mb-4">
                    <h3 class="px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Akun
                    </h3>
                </div>

                <div class="space-y-2">
                    <a href="../auth/logout.php" class="flex items-center px-4 py-3 text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900 rounded-lg sidebar-nav-item hover-scale">
                        <i class="fas fa-sign-out-alt w-5 h-5 mr-3"></i>
                        <span class="font-medium">Logout</span>
                    </a>
                </div>
            </nav>
        </div>

        <!-- Main Content Area -->
        <div id="mainContentArea" class="flex-1 flex flex-col main-content-area">
            <!-- Header -->
            <header class="bg-white dark:bg-slate-800 shadow-sm p-4 md:p-6 glass-effect">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <!-- Mobile Menu Button -->
                        <button id="mobileMenuToggle" class="md:hidden p-2 text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white focus:outline-none">
                            <i class="fas fa-bars text-xl"></i>
                        </button>

                        <!-- Desktop Sidebar Toggle -->
                        <button id="sidebarToggle" class="hidden md:block p-2 text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white focus:outline-none">
                            <i class="fas fa-bars text-xl"></i>
                        </button>

                        <div>
                            <p class="text-gray-600 dark:text-gray-400"><?php echo date('l, d F Y'); ?></p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2 md:space-x-4">
                        <!-- Dark Mode Toggle -->
                        <button id="darkModeToggle" class="p-2 text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white focus:outline-none">
                            <i class="fas fa-moon text-xl dark:hidden"></i>
                            <i class="fas fa-sun text-xl hidden dark:block"></i>
                        </button>

                        <!-- <button class="p-2 text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white">
                            <i class="fas fa-bell text-xl"></i>
                        </button> -->
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center overflow-hidden">
                                <img src="../uploads/profiles/<?php echo $profile_photo; ?>" alt="Profile Photo" class="w-full h-full object-cover rounded-full">
                            </div>
                            <div class="flex items-center space-x-2">
                                <a href="./profile/profile_view.php" class="flex items-center space-x-1 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                    <span class="font-medium text-gray-800 dark:text-white"><?php echo $profile_name; ?></span>
                                    <i class="fas fa-chevron-down text-gray-600 dark:text-gray-400 text-sm"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <main class="flex-1 p-4 md:p-6">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm hover-scale animate-fade-in">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total Users</p>
                                <p class="text-2xl font-bold text-light-blue-600 dark:text-light-blue-400"><?php echo htmlspecialchars($user_count); ?></p>
                            </div>
                            <div class="w-12 h-12 bg-light-blue-100 dark:bg-light-blue-900 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user text-light-blue-600 dark:text-light-blue-400"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm hover-scale animate-fade-in">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Anggota</p>
                                <p class="text-2xl font-bold text-green-600 dark:text-green-400"><?php echo htmlspecialchars($anggota_count); ?></p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                <i class="fas fa-users text-green-600 dark:text-green-400"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm hover-scale animate-fade-in">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Tugas</p>
                                <p class="text-2xl font-bold text-purple-600 dark:text-purple-400"><?php echo htmlspecialchars($tugas_count); ?></p>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                                <i class="fas fa-tasks text-purple-600 dark:text-purple-400"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm hover-scale animate-fade-in">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Izin Malam</p>
                                <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400"><?php echo htmlspecialchars($izin_malam_count); ?></p>
                            </div>
                            <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                                <i class="fas fa-moon text-yellow-600 dark:text-yellow-400"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm hover-scale animate-fade-in">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Izin Nugas</p>
                                <p class="text-2xl font-bold text-red-600 dark:text-red-400"><?php echo htmlspecialchars($izin_nugas_count); ?></p>
                            </div>
                            <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                                <i class="fas fa-book-open text-red-600 dark:text-red-400"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4 animate-fade-in">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white">Tugas User Review</h2>
                    <a href="beri tugas/tugas_selesai_riwayat.php" class="text-light-blue-500 hover:text-light-blue-600 dark:text-light-blue-400 dark:hover:text-light-blue-300 font-medium">View All</a>
                </div>


                <!-- Tugas User Review Cards (Diperbarui) -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-8">
                    <?php if ($tugas_reviews->num_rows > 0): ?>
                        <?php while ($row = $tugas_reviews->fetch_assoc()): ?>
                            <div class="bg-white dark:bg-slate-800 rounded-xl p-4 md:p-6 shadow-sm hover-scale animate-fade-in">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                            Dari: <?php echo htmlspecialchars($row['username']); ?>
                                        </div>
                                        <h3 class="text-lg md:text-xl font-bold text-light-blue-700 dark:text-light-blue-300 mb-2">
                                            <?php echo htmlspecialchars($row['nama_tugas']); ?>
                                        </h3>
                                        <p class="text-gray-700 dark:text-gray-300 text-sm mb-3">
                                            <?php echo htmlspecialchars($row['deskripsi']); ?>
                                        </p>
                                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                            File Jawaban:
                                            <a href="<?php echo htmlspecialchars($row['file_jawaban']); ?>"
                                                class="text-light-blue-500 hover:underline"
                                                target="_blank">
                                                Lihat Jawaban
                                            </a>
                                        </div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">
                                            Dikirim: <?php echo date('d M Y H:i', strtotime($row['tanggal_submit'])); ?>
                                        </div>
                                    </div>
                                </div>
                                <button class="w-full px-4 py-2 bg-light-blue-600 hover:bg-light-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                                    <a href="beri tugas/tugas_user_review.php?id_tugas=<?php echo htmlspecialchars($row['id']); ?>&id_user=<?php echo htmlspecialchars($row['id_user']); ?>" class="block">
                                        Nilai Tugas
                                    </a>
                                </button>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-span-full text-center py-8 hover-scale animate-fade-in">
                            <i class="fas fa-tasks text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
                            <p class="text-gray-600 dark:text-gray-400">Tidak ada tugas yang menunggu review saat ini.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Bagian Anggota yang Izin Malam dengan Detail Waktu -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 md:gap-8">
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-4 md:p-6 shadow-sm animate-fade-in">
                        <h3 class="text-lg md:text-xl font-bold text-gray-800 dark:text-white mb-6">Anggota yang Izin Malam</h3>
                        <div class="space-y-4">
                            <?php if ($izin_malam_anggota->num_rows > 0): ?>
                                <?php while ($row = $izin_malam_anggota->fetch_assoc()): ?>
                                    <div class="flex items-start justify-between p-4 bg-light-blue-50 dark:bg-light-blue-900 rounded-xl hover-scale">
                                        <div class="flex items-start space-x-3">
                                            <div class="flex gap-10">
                                                <h4 class="font-semibold text-gray-800 dark:text-white"> Nama:<br><?php echo htmlspecialchars($row['nama']); ?></h4>
                                                <div class="flex gap-10">
                                                    <h4 class="font-semibold text-gray-800 dark:text-white">
                                                        Tanggal: <br><?php echo date('d M Y', strtotime($row['tanggal'])); ?>
                                                    </h4>
                                                    <h4 class="font-semibold text-gray-800 dark:text-white">
                                                        Jam: <br><?php echo htmlspecialchars($row['jam_izin']); ?> - <?php echo htmlspecialchars($row['jam_selesai_izin']); ?>
                                                    </h4>
                                                    <h4 class="font-semibold text-gray-800 dark:text-white">
                                                        Alasan: <br><?php echo htmlspecialchars($row['alasan']); ?>
                                                    </h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="text-center py-8">
                                    <i class="fas fa-moon text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
                                    <p class="text-gray-600 dark:text-gray-400">Tidak ada anggota yang sedang izin malam.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Daftar Semua Anggota -->
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-4 md:p-6 shadow-sm animate-fade-in">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg md:text-xl font-bold text-gray-800 dark:text-white">Daftar Semua Anggota</h3>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white dark:bg-slate-800">
                                <thead>
                                    <tr class=" dark:bg-light-blue-900">
                                        <th class="py-2 px-4 border-b border-gray-200 dark:border-gray-700 text-left text-gray-600 dark:text-gray-300">Nama</th>
                                        <th class="py-2 px-4 border-b border-gray-200 dark:border-gray-700 text-left text-gray-600 dark:text-gray-300">Email</th>
                                        <th class="py-2 px-4 border-b border-gray-200 dark:border-gray-700 text-left text-gray-600 dark:text-gray-300">No HP</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($all_anggota->num_rows > 0): ?>
                                        <?php while ($row = $all_anggota->fetch_assoc()): ?>
                                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700">
                                                <td class="py-2 px-4 border-b border-gray-200 dark:border-gray-700 dark:text-white"><?php echo htmlspecialchars($row['nama']); ?></td>
                                                <td class="py-2 px-4 border-b border-gray-200 dark:border-gray-700 dark:text-white"><?php echo htmlspecialchars($row['email']); ?></td>
                                                <td class="py-2 px-4 border-b border-gray-200 dark:border-gray-700 dark:text-white"><?php echo htmlspecialchars($row['no_hp']); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="py-4 text-center text-gray-600 dark:text-gray-400">
                                                Tidak ada data anggota
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>

                            <!-- Pagination -->
                            <div class="flex justify-between items-center mt-4">
                                <div>
                                    <?php if ($page > 1): ?>
                                        <a href="?page=<?php echo $page - 1; ?>" class="px-4 py-2 bg-light-blue-600 text-white rounded hover:bg-light-blue-700">
                                            Sebelumnya
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?>
                                </div>
                                <div>
                                    <?php if ($page < $total_pages): ?>
                                        <a href="?page=<?php echo $page + 1; ?>" class="px-4 py-2 bg-light-blue-600 text-white rounded hover:bg-light-blue-700">
                                            Selanjutnya
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                // Sertakan footer
                include './footer.php'; // Path relatif dari 'anggota/' ke 'includes/'
                ?>