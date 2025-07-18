<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Statistik
$user_count = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$anggota_count = $conn->query("SELECT COUNT(*) FROM anggota")->fetch_row()[0];
$tugas_count = $conn->query("SELECT COUNT(*) FROM tugas")->fetch_row()[0];
$izin_malam_count = $conn->query("SELECT COUNT(*) FROM izin_malam")->fetch_row()[0];
$izin_nugas_count = $conn->query("SELECT COUNT(*) FROM izin_nugas")->fetch_row()[0];

// Fetch data for "Tugas User Review"
$tugas_review_query = "SELECT t.id, t.judul AS nama_tugas, t.deskripsi, u.username 
                       FROM tugas t 
                       JOIN users u ON t.id_penerima_tugas = u.id 
                       WHERE t.status = 'pending_review' LIMIT 3";
$tugas_reviews = $conn->query($tugas_review_query);

if (!$tugas_reviews) {
    die("Database query failed: " . $conn->error);
}

// Fetch data for "Anggota yang Izin Malam"
$izin_malam_query = "SELECT a.nama, im.tanggal, im.jam_izin, im.jam_selesai_izin 
                     FROM izin_malam im 
                     JOIN anggota a ON im.id_anggota = a.id 
                     WHERE im.tanggal >= CURDATE() LIMIT 4";
$izin_malam_anggota = $conn->query($izin_malam_query);

if (!$izin_malam_anggota) {
    die("Database query failed: " . $conn->error);
}

// Fetch data for "Anggota Teratas"
$top_anggota_query = "SELECT id, nama FROM anggota ORDER BY id DESC LIMIT 4";
$top_anggota = $conn->query($top_anggota_query);

if (!$top_anggota) {
    die("Database query failed: " . $conn->error);
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eduhouse - Learning Dashboard</title>
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
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
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
    <!-- Mobile Sidebar Overlay -->
    <div id="sidebarOverlay" class="sidebar-overlay md:hidden"></div>
    
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div id="sidebar" class="w-64 bg-white dark:bg-slate-800 shadow-xl flex-shrink-0 sidebar transition-all duration-300 overflow-hidden glass-effect">
            <div class="p-4 flex items-center justify-between border-b border-light-blue-100 dark:border-slate-700">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-light-blue-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-home text-white text-xl"></i>
                    </div>
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white">Eduhouse</h1>
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
                </div>

                <div class="mt-8 mb-4">
                    <h3 class="px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Keuangan
                    </h3>
                </div>
                
                <div class="space-y-2">
                    <a href="uang masuk/masuk.php" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-light-blue-600 dark:hover:text-light-blue-400 hover:bg-light-blue-50 dark:hover:bg-slate-700 rounded-lg sidebar-nav-item hover-scale">
                        <i class="fas fa-money-bill-alt w-5 h-5 mr-3"></i>
                        <span class="font-medium">Uang Masuk</span>
                    </a>
                    
                    <a href="uang keluar/keluar.php" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-light-blue-600 dark:hover:text-light-blue-400 hover:bg-light-blue-50 dark:hover:bg-slate-700 rounded-lg sidebar-nav-item hover-scale">
                        <i class="fas fa-money-bill-wave w-5 h-5 mr-3"></i>
                        <span class="font-medium">Uang Keluar</span>
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
                        
                        <div class="hidden md:block">
                            <h1 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white">Dashboard</h1>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-2 md:space-x-4">
                        <!-- Dark Mode Toggle -->
                        <button id="darkModeToggle" class="p-2 text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white focus:outline-none">
                            <i class="fas fa-moon text-xl dark:hidden"></i>
                            <i class="fas fa-sun text-xl hidden dark:block"></i>
                        </button>
                        
                        <button class="p-2 text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white">
                            <i class="fas fa-bell text-xl"></i>
                        </button>
                        <div class="w-8 h-8 md:w-10 md:h-10 bg-light-blue-300 rounded-full overflow-hidden">
                            <img src="https://via.placeholder.com/40x40" alt="Profile" class="w-full h-full object-cover">
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
                                <p class="text-2xl font-bold text-light-blue-600 dark:text-light-blue-400"><?php echo $user_count; ?></p>
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
                                <p class="text-2xl font-bold text-green-600 dark:text-green-400"><?php echo $anggota_count; ?></p>
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
                                <p class="text-2xl font-bold text-purple-600 dark:text-purple-400"><?php echo $tugas_count; ?></p>
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
                                <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400"><?php echo $izin_malam_count; ?></p>
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
                                <p class="text-2xl font-bold text-red-600 dark:text-red-400"><?php echo $izin_nugas_count; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-lg flex items-center justify-center">
                                <i class="fas fa-book-open text-red-600 dark:text-red-400"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white">Tugas User Review</h2>
                    <a href="beri tugas/tugas_selesai_riwayat.php" class="text-light-blue-500 hover:text-light-blue-600 dark:text-light-blue-400 dark:hover:text-light-blue-300 font-medium">View All</a>
                </div>

                <!-- Tugas User Review Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-8">
                    <?php if ($tugas_reviews->num_rows > 0): ?>
                        <?php while($row = $tugas_reviews->fetch_assoc()): ?>
                            <div class="bg-white dark:bg-slate-800 rounded-xl p-4 md:p-6 shadow-sm hover-scale animate-fade-in">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">Dari: <?php echo htmlspecialchars($row['username']); ?></div>
                                        <h3 class="text-lg md:text-xl font-bold text-light-blue-700 dark:text-light-blue-300 mb-2"><?php echo htmlspecialchars($row['nama_tugas']); ?></h3>
                                        <p class="text-gray-700 dark:text-gray-300 text-sm mb-3"><?php echo htmlspecialchars($row['deskripsi']); ?></p>
                                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">Status: Menunggu Review</div>
                                    </div>
                                    <div class="w-10 h-10 bg-light-blue-100 dark:bg-light-blue-900 rounded-lg flex items-center justify-center ml-4">
                                        <i class="fas fa-tasks text-light-blue-600 dark:text-light-blue-400"></i>
                                    </div>
                                </div>
                                <button class="w-full px-4 py-2 bg-light-blue-600 hover:bg-light-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                                    <a href="beri tugas/tugas_user_review.php?id_tugas=<?php echo $row['id']; ?>" class="block">Nilai Tugas</a>
                                </button>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-span-full text-center py-8">
                            <i class="fas fa-tasks text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
                            <p class="text-gray-600 dark:text-gray-400">Tidak ada tugas yang menunggu review saat ini.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Bottom Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 md:gap-8">
                    <!-- Anggota yang Izin Malam -->
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-4 md:p-6 shadow-sm animate-fade-in">
                        <h3 class="text-lg md:text-xl font-bold text-gray-800 dark:text-white mb-6">Anggota yang Izin Malam</h3>
                        <div class="space-y-4">
                            <?php if ($izin_malam_anggota->num_rows > 0): ?>
                                <?php while($row = $izin_malam_anggota->fetch_assoc()): ?>
                                    <div class="flex items-center justify-between p-4 bg-light-blue-50 dark:bg-light-blue-900 rounded-xl hover-scale">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-light-blue-300 rounded-full overflow-hidden">
                                                <img src="https://via.placeholder.com/40x40" alt="<?php echo htmlspecialchars($row['nama']); ?>" class="w-full h-full object-cover">
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-gray-800 dark:text-white"><?php echo htmlspecialchars($row['nama']); ?></h4>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Member #<?php echo $row['id']; ?></p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <button class="px-3 py-1 md:px-4 md:py-2 bg-light-blue-500 hover:bg-light-blue-600 text-white rounded-lg text-sm font-medium transition-colors">Detail</button>
                                            <button class="p-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white">
                                                <i class="fas fa-envelope"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="text-center py-8">
                                    <i class="fas fa-users text-4xl text-gray-300 dark:text-gray-600 mb-4"></i>
                                    <p class="text-gray-600 dark:text-gray-400">Tidak ada anggota teratas yang ditemukan.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Dark Mode Toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        const html = document.documentElement;
        
        // Check for saved theme preference or default to light mode
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            html.classList.add('dark');
        }
        
        darkModeToggle.addEventListener('click', () => {
            html.classList.toggle('dark');
            localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
        });

        // Mobile Menu Toggle
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const closeSidebar = document.getElementById('closeSidebar');
        
        function toggleMobileSidebar() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        }
        
        mobileMenuToggle.addEventListener('click', toggleMobileSidebar);
        closeSidebar.addEventListener('click', toggleMobileSidebar);
        sidebarOverlay.addEventListener('click', toggleMobileSidebar);

        // Desktop Sidebar Toggle
        const sidebarToggle = document.getElementById('sidebarToggle');
        const mainContentArea = document.getElementById('mainContentArea');
        let isSidebarOpen = true;

        sidebarToggle.addEventListener('click', () => {
            if (isSidebarOpen) {
                // Close sidebar
                sidebar.classList.remove('w-64');
                sidebar.classList.add('w-20');
                
                // Hide text content
                sidebar.querySelectorAll('span.font-medium, h3')
                    .forEach(el => el.classList.add('hidden'));
                    
                // Show only icons
                sidebar.querySelectorAll('i')
                    .forEach(el => el.classList.add('mx-auto'));
                    
                sidebarToggle.querySelector('i').classList.replace('fa-bars', 'fa-arrow-right');
            } else {
                // Open sidebar
                sidebar.classList.remove('w-20');
                sidebar.classList.add('w-64');
                
                // Show text content
                sidebar.querySelectorAll('span.font-medium, h3')
                    .forEach(el => el.classList.remove('hidden'));
                    
                // Reset icon alignment
                sidebar.querySelectorAll('i')
                    .forEach(el => el.classList.remove('mx-auto'));
                    
                sidebarToggle.querySelector('i').classList.replace('fa-arrow-right', 'fa-bars');
            }
            isSidebarOpen = !isSidebarOpen;
        });

        // Responsive behavior
        function handleResize() {
            if (window.innerWidth < 768) {
                // Mobile: Hide desktop sidebar toggle, show mobile menu
                sidebarToggle.classList.add('hidden');
                mobileMenuToggle.classList.remove('hidden');
                
                // Reset sidebar state for mobile
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
            } else {
                // Desktop: Show desktop sidebar toggle, hide mobile menu
                sidebarToggle.classList.remove('hidden');
                mobileMenuToggle.classList.add('hidden');
                
                // Ensure sidebar is properly positioned for desktop
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
            }
        }

        // Initial check
        handleResize();
        
        // Listen for resize events
        window.addEventListener('resize', handleResize);
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Add loading animation to buttons
        document.querySelectorAll('button, a').forEach(element => {
            element.addEventListener('click', function() {
                if (!this.classList.contains('no-loading')) {
                    this.style.opacity = '0.8';
                    setTimeout(() => {
                        this.style.opacity = '1';
                    }, 200);
                }
            });
        });
    </script>
</body>
</html>100 dark:bg-light-blue-800 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-moon text-light-blue-600 dark:text-light-blue-400"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-gray-800 dark:text-white"><?php echo htmlspecialchars($row['nama']); ?></h4>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">Izin: <?php echo htmlspecialchars($row['tanggal']); ?></p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400"><?php echo htmlspecialchars($row['jam_izin']); ?> - <?php echo htmlspecialchars($row['jam_selesai_izin']); ?></p>
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

                    <!-- Anggota Teratas -->
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-4 md:p-6 shadow-sm animate-fade-in">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg md:text-xl font-bold text-gray-800 dark:text-white">Anggota Teratas</h3>
                            <a href="anggota/anggota.php" class="text-light-blue-500 hover:text-light-blue-600 dark:text-light-blue-400 dark:hover:text-light-blue-300 font-medium">View All</a>
                        </div>
                        <div class="space-y-4">
                            <?php if ($top_anggota->num_rows > 0): ?>
                                <?php while($row = $top_anggota->fetch_assoc()): ?>
                                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-slate-700 rounded-xl hover-scale">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-light-blue-