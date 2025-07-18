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
    WHERE t.id_penerima_tugas = ? AND t.status IN ("pending", "selesai")
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
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .sidebar {
            transition: width 0.3s ease-in-out;
        }

        .sidebar-text {
            transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
        }

        .sidebar-logo-text {
            transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
        }

        .sidebar.collapsed {
            width: 4rem !important;
        }

        .sidebar.collapsed .sidebar-text {
            opacity: 0;
            transform: translateX(-20px);
            width: 0;
            overflow: hidden;
            white-space: nowrap;
        }

        .sidebar.collapsed .sidebar-logo-text {
            opacity: 0;
            transform: translateX(-20px);
            width: 0;
            overflow: hidden;
            white-space: nowrap;
        }

        .sidebar.collapsed .sidebar-nav-item {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }

        .sidebar.collapsed .sidebar-nav-item span {
            display: none;
        }

        .sidebar.collapsed .sidebar-nav-item i {
            margin-right: 0;
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

<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <div class="min-h-screen">
        <div class="flex">
            <!-- Sidebar -->
            <div id="sidebar" class="sidebar w-64 bg-white dark:bg-gray-800 shadow-xl border-r border-gray-200 dark:border-gray-700 p-6 flex flex-col min-h-screen">
                <!-- Logo -->
                <div class="flex items-center space-x-3 mb-8">
                    <div class="w-10 h-10 bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-graduation-cap text-white text-lg"></i>
                    </div>
                    <span class="text-xl font-bold text-gray-800 dark:text-white sidebar-logo-text">Multimedia</span>
                </div>

                <!-- Navigation -->
                <nav class="space-y-2 flex-1">
                    <a href="dashboard.php" class="flex items-center space-x-3 px-4 py-3 text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 rounded-xl border-l-4 border-primary-600 dark:border-primary-400 sidebar-nav-item">
                        <i class="fas fa-th-large flex-shrink-0"></i>
                        <span class="font-medium sidebar-text">Dashboard</span>
                    </a>

                    <a href="portfolio/portfolio.php" class="flex items-center space-x-3 px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-xl sidebar-nav-item transition-colors">
                        <i class="fas fa-briefcase flex-shrink-0"></i>
                        <span class="font-medium sidebar-text">Portfolio</span>
                    </a>

                    <a href="izin malam/izin-malam.php" class="flex items-center space-x-3 px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-xl sidebar-nav-item transition-colors">
                        <i class="fas fa-moon flex-shrink-0"></i>
                        <span class="font-medium sidebar-text">Izin Malam</span>
                    </a>

                    <a href="izin nugas/izin-nugas.php" class="flex items-center space-x-3 px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-xl sidebar-nav-item transition-colors">
                        <i class="fas fa-laptop-code flex-shrink-0"></i>
                        <span class="font-medium sidebar-text">Izin Nugas</span>
                    </a>

                    <a href="tugas/riwayat_tugas.php" class="flex items-center space-x-3 px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-xl sidebar-nav-item transition-colors">
                        <i class="fas fa-history flex-shrink-0"></i>
                        <span class="font-medium sidebar-text">Riwayat Tugas</span>
                    </a>
                    <a href="akun/profile_settings.php" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg sidebar-nav-item">
                        <i class="fas fa-cog flex-shrink-0"></i>
                        <span class="font-medium sidebar-text">Pengaturan Akun</span>
                    </a>

                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                        <a href="../auth/logout.php" class="flex items-center space-x-3 px-4 py-3 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl sidebar-nav-item transition-colors">
                            <i class="fas fa-sign-out-alt flex-shrink-0"></i>
                            <span class="font-medium sidebar-text">Logout</span>
                        </a>
                    </div>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="flex-1 flex flex-col">
                <!-- Header -->
                <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <!-- Sidebar Toggle Button -->
                            <button id="sidebarToggle" class="p-2 text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 focus:outline-none transition-colors">
                                <i class="fas fa-bars text-xl"></i>
                            </button>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Dashboard</h1>
                                <p class="text-gray-600 dark:text-gray-400"><?php echo date('l, d F Y'); ?></p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <!-- Dark Mode Toggle -->
                            <button id="darkModeToggle" class="p-2 text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 focus:outline-none transition-colors">
                                <i class="fas fa-sun dark:hidden text-xl"></i>
                                <i class="fas fa-moon hidden dark:block text-xl"></i>
                            </button>

                            <!-- Profile -->
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
                <main class="flex-1 p-6">
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

                                if ($row['jawaban_id'] && $row['status'] == 'selesai') {
                                    $card_class = 'status-waiting';
                                    $icon_class = 'fas fa-hourglass-half';
                                    $status_text = 'Menunggu Penilaian';
                                    $action_text = 'Sudah Dikerjakan';
                                    $action_link = '#';
                                } elseif ($row['jawaban_id'] && $row['nilai']) {
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
        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        let isSidebarOpen = true;

        sidebarToggle.addEventListener('click', () => {
            if (isSidebarOpen) {
                // Collapse sidebar
                sidebar.classList.add('collapsed');
                sidebar.classList.remove('w-64');
                sidebar.classList.add('w-16');
                sidebarToggle.querySelector('i').classList.replace('fa-bars', 'fa-arrow-right');
            } else {
                // Expand sidebar
                sidebar.classList.remove('collapsed');
                sidebar.classList.remove('w-16');
                sidebar.classList.add('w-64');
                sidebarToggle.querySelector('i').classList.replace('fa-arrow-right', 'fa-bars');
            }
            isSidebarOpen = !isSidebarOpen;
        });

        // Dark Mode Toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        const html = document.documentElement;

        // Check for saved theme preference or default to light mode
        const currentTheme = localStorage.getItem('theme') || 'light';
        if (currentTheme === 'dark') {
            html.classList.add('dark');
        }

        darkModeToggle.addEventListener('click', () => {
            html.classList.toggle('dark');
            const isDark = html.classList.contains('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });

        // Animate cards on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.card-hover').forEach(card => {
            observer.observe(card);
        });

        // Add bounce animation to task cards
        document.querySelectorAll('.card-hover').forEach((card, index) => {
            setTimeout(() => {
                card.classList.add('animate-bounce-in');
            }, index * 100);
        });
    </script>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>