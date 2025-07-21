<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$id_user = $_SESSION['user_id'];

// Ambil tugas yang diberikan ke user yang statusnya 'pending' atau 'selesai' (jika belum dinilai)
// Join dengan tugas_jawaban untuk mengecek apakah sudah ada jawaban
$stmt = $conn->prepare('
    SELECT
        t.id,
        t.judul,
        t.deskripsi,
        t.deadline,
        t.status,
        tj.id as jawaban_id,
        tj.file_jawaban,
        tj.nilai,
        tj.komentar
    FROM tugas t
    LEFT JOIN tugas_jawaban tj ON t.id = tj.id_tugas AND tj.id_user = ?
    WHERE t.id_penerima_tugas = ? AND t.status IN ("pending")
    ORDER BY t.deadline ASC
');
$stmt->bind_param('ii', $id_user, $id_user);
$stmt->execute();
$result = $stmt->get_result();

// Fetch user profile for display
$profile_name = "User"; // Default
$profile_photo = "default_profile.jpg"; // Default
$stmt_profile = $conn->prepare('SELECT nama_lengkap, foto FROM profile WHERE id_user = ?');
$stmt_profile->bind_param('i', $id_user);
$stmt_profile->execute();
$stmt_profile->bind_result($fetched_name, $fetched_photo);
if ($stmt_profile->fetch()) {
    $profile_name = htmlspecialchars($fetched_name);
    $profile_photo = htmlspecialchars($fetched_photo);
}
$stmt_profile->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multimedia Dashboard</title>
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
        /* Styles from dashboard.php */
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

        /* Specific styles for forms/tables if needed */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error,
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-group {
            margin-bottom: 10px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        input[type="date"],
        input[type="time"],
        select,
        textarea {
            width: 100%;
            max-width: 300px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            padding: 10px 15px;
            background-color: #0ea5e9;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0284c7;
        }

        a {
            text-decoration: none;
            color: #007bff;
        }

        a:hover {
            text-decoration: underline;
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .dark .glass-effect {
            background: rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .gradient-bg {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        }

        .gradient-card {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        }

        .dark .gradient-card {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        }



        .status-pending {
            background: linear-gradient(146deg, #058cd0 0%, #e9f0ff00 100%);
        }

        .status-waiting {
            background: linear-gradient(135deg, #f2db0f9c 0%, #fcf8f3 100%);
        }

        .status-completed {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        }

        .dark .status-pending {
            background: linear-gradient(135deg, #0c1d77 0%, #1f293700 100%);
        }

        .dark .status-waiting {
            background: linear-gradient(135deg, #431407 0%, #9a3412 100%);
        }

        .dark .status-completed {
            background: linear-gradient(135deg, #064e3b 0%, #047857 100%);
        }
    </style>
</head>

<body class="dark-mode-transition">
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
            <!-- Navigation -->
            <nav class="space-y-2 flex-1">
                <a href="dashboard.php" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-light-blue-600 dark:hover:text-light-blue-400 hover:bg-light-blue-50 dark:hover:bg-slate-700 rounded-lg sidebar-nav-item hover-scale">
                    <i class="fas fa-th-large  w-5 h-5 mr-2"></i>
                    <span class="font-medium sidebar-text">Dashboard</span>
                </a>

                <a href="portfolio/portfolio.php" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-light-blue-600 dark:hover:text-light-blue-400 hover:bg-light-blue-50 dark:hover:bg-slate-700 rounded-lg sidebar-nav-item hover-scale">
                    <i class="fas fa-briefcase  w-5 h-5 mr-4"></i>
                    <span class="font-medium sidebar-text">Portfolio</span>
                </a>

                <a href="izin malam/izin-malam.php" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-light-blue-600 dark:hover:text-light-blue-400 hover:bg-light-blue-50 dark:hover:bg-slate-700 rounded-lg sidebar-nav-item hover-scale">
                    <i class="fas fa-moon  w-5 h-5 mr-4"></i>
                    <span class="font-medium sidebar-text">Izin Malam</span>
                </a>

                <a href="izin nugas/izin-nugas.php" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-light-blue-600 dark:hover:text-light-blue-400 hover:bg-light-blue-50 dark:hover:bg-slate-700 rounded-lg sidebar-nav-item hover-scale">
                    <i class="fas fa-laptop-code  w-5 h-5 mr-4"></i>
                    <span class="font-medium sidebar-text">Izin Nugas</span>
                </a>

                <a href="tugas/riwayat_tugas.php" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-light-blue-600 dark:hover:text-light-blue-400 hover:bg-light-blue-50 dark:hover:bg-slate-700 rounded-lg sidebar-nav-item hover-scale">
                    <i class="fas fa-history  w-5 h-5 mr-4"></i>
                    <span class="font-medium sidebar-text">Riwayat Tugas</span>
                </a>
                <a href="akun/profile_settings.php" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-light-blue-600 dark:hover:text-light-blue-400 hover:bg-light-blue-50 dark:hover:bg-slate-700 rounded-lg sidebar-nav-item hover-scale">
                    <i class="fas fa-cog  w-5 h-5 mr-4"></i>
                    <span class="font-medium sidebar-text">Pengaturan Akun</span>
                </a>

                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="../auth/logout.php" class="flex items-center space-x-3 px-4 py-3 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl sidebar-nav-item transition-colors">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="font-medium sidebar-text">Logout</span>
                    </a>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div id="mainContentArea" class="flex-1 flex flex-col main-content-area">
            <!-- Header -->
            <header class="bg-white dark:bg-slate-800 shadow-sm p-4 md:p-6 glass-effect">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <button id="mobileMenuToggle" class="md:hidden p-2 text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white focus:outline-none">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <button id="sidebarToggle" class="hidden md:block p-2 text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white focus:outline-none">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <div>
                            <p class="text-gray-600 dark:text-gray-400"><?php echo date('l, d F Y'); ?></p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 md:space-x-4">
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
                                <a href="profile/profile_view.php" class="flex items-center space-x-1 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                    <span class="font-medium text-gray-800 dark:text-white"><?php echo $profile_name; ?></span>
                                    <i class="fas fa-chevron-down text-gray-600 dark:text-gray-400 text-sm"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <main class="flex-1 p-4 md:p-6">
                <!-- Welcome Section -->
                <div class="gradient-bg rounded-2xl p-6 mb-8 text-white animate-fade-in">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold mb-2">Selamat Datang, <?php echo $profile_name; ?>!</h2>
                            <p class="text-blue-100">Kelola tugas dan aktivitas multimedia Anda dengan mudah</p>
                        </div>
                        <div class="hidden md:block">
                            <i class="fas fa-graduation-cap text-6xl text-blue-200"></i>
                        </div>
                    </div>
                </div>

                <!-- Status Messages -->
                <?php if (isset($_GET['status'])): ?>
                    <div class="mb-6 p-4 rounded-xl <?php echo $_GET['status'] == 'success' ? 'bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-400 border border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-400 border border-red-200 dark:border-red-800'; ?>">
                        <i class="fas <?php echo $_GET['status'] == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> mr-2"></i>
                        <?php echo htmlspecialchars($_GET['message']); ?>
                    </div>
                <?php endif; ?>

                <!-- Task Section -->
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Tugas Masuk</h2>
                    <!-- <a href="./tugas/riwayat_tugas.php" class="text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium transition-colors">
                            <i class="fas fa-arrow-right mr-2"></i>Lihat Semua
                        </a> -->
                </div>

                <!-- Task Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php
                            $card_class = 'status-pending';
                            $icon_class = 'fas fa-clock';
                            $status_text = 'Pending';
                            $action_text = 'Kerjakan';
                            $action_link = "./tugas/tugas_kerjakan.php?id=" . $row['id'];

                            if ($row['jawaban_id'] && $row['status'] == 'dikirim') {
                                $card_class = 'status-waiting';
                                $icon_class = 'fas fa-hourglass-half';
                                $status_text = 'Menunggu Penilaian';
                                $action_text = 'Sudah Dikerjakan';
                                $action_link = '#';
                            } elseif ($row['jawaban_id'] && $row['selesai']) {
                                $card_class = 'status-completed';
                                $icon_class = 'fas fa-check-circle';
                                $status_text = 'Selesai';
                                $action_text = 'Lihat Nilai';
                                $action_link = './tugas/tugas_detail.php?id=' . $row['id'];
                            }
                            ?>

                            <div class="<?= $card_class ?> rounded-2xl p-6 card-hover animate-fade-in transform transition-all duration-300">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-12 h-12 bg-white dark:bg-gray-800 rounded-xl flex items-center justify-center shadow-sm">
                                            <i class="<?= $icon_class ?> text-primary-600 dark:text-primary-400 text-lg"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-800 dark:text-white"><?php echo htmlspecialchars($row['judul']); ?></h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                <i class="fas fa-calendar-alt mr-1"></i>
                                                <?php echo date('d M Y', strtotime($row['deadline'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 bg-white dark:bg-gray-800 text-xs font-medium rounded-full text-gray-700 dark:text-gray-300">
                                        <?= $status_text ?>
                                    </span>
                                </div>

                                <p class="text-gray-700 dark:text-gray-300 mb-4 line-clamp-3">
                                    <?php echo nl2br(htmlspecialchars(substr($row['deskripsi'], 0, 100))); ?>
                                    <?php if (strlen($row['deskripsi']) > 100) echo '...'; ?>
                                </p>

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <?php if ($row['nilai']): ?>
                                            <div class="flex items-center space-x-1">
                                                <i class="fas fa-star text-yellow-500"></i>
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    <?php echo $row['nilai']; ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if (!$row['jawaban_id']): ?>
                                        <a href="<?= $action_link ?>" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors text-sm font-medium">
                                            <i class="fas fa-play mr-2"></i>
                                            <?= $action_text ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-sm text-gray-600 dark:text-gray-400 font-medium">
                                            <i class="fas fa-check mr-1"></i>
                                            <?= $action_text ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-span-full text-center py-12">
                            <div class="w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-tasks text-gray-400 dark:text-gray-600 text-3xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-800 dark:text-white mb-2">Tidak ada tugas</h3>
                            <p class="text-gray-600 dark:text-gray-400">Belum ada tugas yang tersedia saat ini.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Izin Malam Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 card-hover animate-fade-in">
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 bg-gradient-to-r from-blue-900 to-blue-400 rounded-2xl flex items-center justify-center">
                                <i class="fas fa-moon text-white text-2xl"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-xl font-bold text-gray-800 dark:text-white mb-2">Izin Malam</h4>
                                <p class="text-gray-600 dark:text-gray-400 mb-4">Ajukan izin malam dengan mudah dan cepat</p>
                                <a href="./izin malam/izin-malam-entry.php" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-300 hover:text-black transition-colors text-sm font-medium">
                                    <i class="fas fa-plus mr-2"></i>
                                    Ajukan Izin
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Izin Nugas Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700 card-hover animate-fade-in">
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 bg-gradient-to-r from-blue-900 to-blue-400 rounded-2xl flex items-center justify-center">
                                <i class="fas fa-laptop-code text-white text-2xl"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-xl font-bold text-gray-800 dark:text-white mb-2">Izin Nugas</h4>
                                <p class="text-gray-600 dark:text-gray-400 mb-4">Kelola izin mengerjakan tugas dengan efisien</p>
                                <a href="./izin nugas/izin-nugas-entry.php" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-300 hover:text-black transition-colors text-sm font-medium">
                                    <i class="fas fa-plus mr-2"></i>
                                    Ajukan Izin
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
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
            anchor.addEventListener('click', function(e) {
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

        // penambahan untuk peminjaman dan penyewaan
        function togglePeminjam() {
            var tipe = document.getElementById('tipe_peminjam').value;
            if (tipe === 'umum') {
                document.getElementById('form_umum').style.display = 'block';
                document.getElementById('form_anggota').style.display = 'none';
                document.getElementById('nama_peminjam').required = true;
                document.getElementById('kontak_peminjam').required = true;
                document.getElementById('id_anggota').required = false;
            } else {
                document.getElementById('form_umum').style.display = 'none';
                document.getElementById('form_anggota').style.display = 'block';
                document.getElementById('nama_peminjam').required = false;
                document.getElementById('kontak_peminjam').required = false;
                document.getElementById('id_anggota').required = true;
            }
        }

        function togglePenyewa() {
            var tipe = document.getElementById('tipe_penyewa').value;
            if (tipe === 'umum') {
                document.getElementById('form_umum').style.display = 'block';
                document.getElementById('form_anggota').style.display = 'none';
                document.getElementById('nama_penyewa').required = true;
                document.getElementById('kontak_penyewa').required = true;
                document.getElementById('id_anggota').required = false;
            } else {
                document.getElementById('form_umum').style.display = 'none';
                document.getElementById('form_anggota').style.display = 'block';
                document.getElementById('nama_penyewa').required = false;
                document.getElementById('kontak_penyewa').required = false;
                document.getElementById('id_anggota').required = true;
            }
        }
        // Panggil saat halaman dimuat
        window.onload = togglePeminjam;
    </script>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>