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

$conn->close();
?>
<h2>Dashboard Admin</h2>
<ul>
    <li>Jumlah User: <?php echo $user_count; ?></li>
    <li>Jumlah Anggota: <?php echo $anggota_count; ?></li>
    <li>Jumlah Tugas: <?php echo $tugas_count; ?></li>
    <li>Izin Malam: <?php echo $izin_malam_count; ?></li>
    <li>Izin Nugas: <?php echo $izin_nugas_count; ?></li>
</ul>
<ul>
    <li><a href="anggota/anggota.php">Manajemen Anggota</a></li>
    <li><a href="daftar alat/daftar-alat.php">Manajemen Alat</a></li>
    <li><a href="peminjaman/peminjaman-barang.php">Peminjaman Barang</a></li>
    <li><a href="penyewaan/penyewaan-barang.php">Penyewaan Barang</a></li>
    <li><a href="beri tugas/beri_tugas_form.php">Beri Tugas</a></li>
    <li><a href="beri tugas/tugas_user_review.php">Review Tugas User</a></li>
    <li><a href="izin_malam/izin-malam.php">Izin Malam</a></li>
    <li><a href="izin_nugas/izin-nugas.php">Izin Nugas</a></li>
    <li><a href="legalisasi laptop/legalisasi_list.php">Legalisasi Laptop</a></li>
    <li><a href="uang masuk/masuk.php">Uang Masuk</a></li>
    <li><a href="uang keluar/keluar.php">Uang Keluar</a></li>
    <li><a href="../auth/logout.php">Logout</a></li>
</ul>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pitch.io - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-yellow {
            background: linear-gradient(135deg, #FCD34D 0%, #F59E0B 100%);
        }

        .gradient-purple {
            background: linear-gradient(135deg, #8B5CF6 0%, #6D28D9 100%);
        }

        .gradient-pink {
            background: linear-gradient(135deg, #F472B6 0%, #EC4899 100%);
        }

        .gradient-gray {
            background: linear-gradient(135deg, #D1D5DB 0%, #9CA3AF 100%);
        }

        .card-shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        /* Custom styles for sidebar transition */
        .sidebar {
            transition: width 0.3s ease-in-out;
            /* Smooth transition for width */
        }

        .sidebar-text {
            transition: opacity 0.3s ease-in-out, margin-left 0.3s ease-in-out;
            /* Smooth transition for text visibility */
        }

        .sidebar-nav-item {
            justify-content: flex-start;
            /* Default alignment for expanded state */
        }

        .sidebar.collapsed .sidebar-nav-item {
            justify-content: center;
            /* Center icons when collapsed */
        }

        .sidebar.collapsed .sidebar-text {
            opacity: 0;
            width: 0;
            /* Collapse width of text container */
            overflow: hidden;
            white-space: nowrap;
            pointer-events: none;
            /* Prevent interaction with hidden text */
        }

        .sidebar.collapsed .sidebar-logo-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
            white-space: nowrap;
            pointer-events: none;
        }

        .sidebar.collapsed .sidebar-logo-icon {
            margin-right: 0 !important;
            /* Remove margin when collapsed */
        }

        .sidebar.collapsed .sidebar-create-button .sidebar-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
            white-space: nowrap;
            pointer-events: none;
        }

        .sidebar.collapsed .sidebar-create-button i {
            margin-right: 0 !important;
            /* Remove margin for icon */
        }

        .sidebar.collapsed .sidebar-upgrade-section {
            opacity: 0;
            height: 0;
            overflow: hidden;
            padding-top: 0;
            padding-bottom: 0;
            margin-top: 0;
            pointer-events: none;
        }
    </style>
</head>

<body>
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden ">
        <div class="flex h-screen">
            <!-- Sidebar -->
            <div id="sidebar" class="w-64 bg-gray-50 border-r border-gray-200 p-6 flex flex-col sidebar">
                <!-- Logo -->
                <div class="flex items-center space-x-2 mb-8">
                    <div class="w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center flex-shrink-0 sidebar-logo-icon">
                        <span class="text-white font-bold text-sm">P</span>
                    </div>
                    <span class="text-xl font-bold text-gray-800 sidebar-logo-text">Pitch.io</span>
                </div>

                <!-- Create New Pitch Button -->
                <div class="mb-8 sidebar-create-button">
                    <button>
                        <!-- <i class="fas fa-plus flex-shrink-0"></i>
                        <span class="font-medium sidebar-text">Create New Pitch</span> -->
                    </button>
                </div>

                <!-- Navigation -->
                <nav class="space-y-2">
                    <a href="./dashboard.php" class="flex items-center space-x-3 px-4 py-3 text-purple-600 bg-purple-50 rounded-lg border-l-4 border-purple-600 sidebar-nav-item">
                        <i class="fas fa-th-large flex-shrink-0"></i>
                        <span class="font-medium sidebar-text">Dashboard</span>
                    </a>

                    <a href="./portfolio/portfolio.php" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg sidebar-nav-item">
                        <i class="fas fa-edit flex-shrink-0"></i>
                        <span class="font-medium sidebar-text">portfoilo</span>
                    </a>

                    <a href="./izin malam/izin-malam.php" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg sidebar-nav-item">
                        <i class="fas fa-users flex-shrink-0"></i>
                        <span class="font-medium sidebar-text">izin malam</span>
                    </a>

                    <a href="./izin nugas/izin-nugas.php" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg sidebar-nav-item">
                        <i class="fas fa-cog flex-shrink-0"></i>
                        <span class="font-medium sidebar-text">izin nugas</span>
                    </a>

                    <a href="./tugas/riwayat_tugas.php" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg sidebar-nav-item">
                        <i class="fas fa-eye flex-shrink-0"></i>
                        <span class="font-medium sidebar-text">riwayat tugas</span>
                    </a>
                </nav>

                <!-- Upgrade Section -->
                <div class="mt-auto pt-8 sidebar-upgrade-section">
                   
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1 flex flex-col">
                <!-- Header -->
                <header  class="bg-white border-b border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <!-- Sidebar Toggle Button -->
                            <button id="sidebarToggle" class="p-2 text-gray-600 hover:text-gray-800 focus:outline-none mr-4">
                                <i class="fas fa-bars text-xl"></i>
                            </button>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
                                <p class="text-gray-600">Monday, 02 March 2020</p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <button class="p-2 text-gray-600 hover:text-gray-800">
                                <i class="fas fa-envelope text-xl"></i>
                            </button>
                            <button class="p-2 text-gray-600 hover:text-gray-800">
                                <i class="fas fa-bell text-xl"></i>
                            </button>
                            <div class="flex items-center space-x-2">
                                <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold text-sm">AJ</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <span class="font-medium text-gray-800">Alyssa Jones</span>
                                    <i class="fas fa-chevron-down text-gray-600 text-sm"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Dashboard Content -->
                <main  class="flex-1 p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Tugas Masuk</h2>
                        <?php
                        if (isset($_GET['status'])) {
                            if ($_GET['status'] == 'success') {
                                echo '<div class="message success">' . htmlspecialchars($_GET['message']) . '</div>';
                            } else {
                                echo '<div class="message error">' . htmlspecialchars($_GET['message']) . '</div>';
                            }
                        }
                        ?>
                        <button class="text-orange-500 hover:text-orange-600 font-medium"><a href="./tugas/riwayat_tugas.php"> VIEW All</a></button>
                    </div>

                    <!-- Task Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        <?php
                        if ($result->num_rows > 0):
                            while ($row = $result->fetch_assoc()):
                                // Determine card color based on task status or type (optional)
                                $card_bg_color = 'bg-purple-50';
                                $icon_bg_color = 'bg-purple-100';
                                $icon_color = 'text-purple-600';
                                $title_color = 'text-purple-700';
                                $action_color = 'text-purple-700';
                                $progress_color = 'bg-purple-600';
                                $icon_class = 'fas fa-tasks'; // Default task icon

                                // You can add logic here to change colors/icons based on $row['status'] or other criteria
                                if ($row['status'] == 'selesai' && $row['jawaban_id']) {
                                    // Task submitted, waiting for grading
                                    $card_bg_color = 'bg-orange-50';
                                    $icon_bg_color = 'bg-orange-100';
                                    $icon_color = 'text-orange-600';
                                    $title_color = 'text-orange-700';
                                    $action_color = 'text-orange-700';
                                    $progress_color = 'bg-orange-600';
                                    $icon_class = 'fas fa-hourglass-half';
                                } elseif ($row['status'] == 'diperiksa' && $row['jawaban_id']) {
                                    // Task graded
                                    $card_bg_color = 'bg-blue-50';
                                    $icon_bg_color = 'bg-blue-100';
                                    $icon_color = 'text-blue-600';
                                    $title_color = 'text-blue-700';
                                    $action_color = 'text-blue-700';
                                    $progress_color = 'bg-blue-600';
                                    $icon_class = 'fas fa-check-circle';
                                }
                        ?>
                                <div class="<?= $card_bg_color ?> rounded-2xl p-6 relative overflow-hidden">
                                    <div class="absolute top-4 right-4 w-8 h-8 <?= $icon_bg_color ?> rounded-lg flex items-center justify-center">
                                        <i class="<?= $icon_class ?> <?= $icon_color ?>"></i>
                                    </div>
                                    <div class="text-sm text-gray-600 mb-2">Deadline: <?php echo date('d/m/Y', strtotime($row['deadline'])); ?></div>
                                    <h3 class="text-xl font-bold <?= $title_color ?> mb-2"><?php echo htmlspecialchars($row['judul']); ?></h3>
                                    <p class="text-gray-700 mb-4"><?php echo nl2br(htmlspecialchars($row['deskripsi'])); ?></p>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700">Status:
                                            <?php
                                            if ($row['jawaban_id'] && $row['status'] == 'selesai') {
                                                echo 'Menunggu Penilaian'; // User submitted, admin hasn't graded
                                            } elseif ($row['jawaban_id'] && $row['status'] == 'diperiksa') {
                                                echo 'Sudah Dinilai'; // Admin has graded
                                            } else {
                                                echo htmlspecialchars($row['status']); // Pending
                                            }
                                            ?>
                                        </span>
                                        <span class="text-sm font-bold <?= $action_color ?>">
                                            <?php if (!$row['jawaban_id']): // If no answer submitted yet 
                                            ?>
                                                <a href="./tugas/tugas_kerjakan.php?id=<?php echo $row['id']; ?>">Kerjakan</a>
                                            <?php else: // If answer already submitted 
                                            ?>
                                                Sudah dikerjakan
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <!-- Removed static progress bar as it's not directly applicable to task status without a specific metric -->
                                    <!-- <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="<?= $progress_color ?> h-2 rounded-full" style="width: 20%"></div>
                        </div> -->
                                </div>
                            <?php
                            endwhile;
                        else:
                            ?>
                            <div class="col-span-full text-center text-gray-600 py-8">
                                Tidak ada tugas yang tersedia saat ini.
                            </div>
                        <?php
                        endif;
                        ?>
                    </div>

                    <!-- Bottom Section (Popular Categories and Top Mentors remain unchanged as per original context) -->
                    <div class="space-y-4">
                        <!-- Popular Categories -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h3 class="text-xl font-bold text-gray-800 mb-6">Popular Categories</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-purple-50 rounded-xl">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-palette text-purple-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-800">UI/UX Design</h4>
                                        <p class="text-sm text-gray-600">18 Course</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-orange-50 rounded-xl">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-bullhorn text-orange-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-800">Marketing</h4>
                                        <p class="text-sm text-gray-600">34 Course</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-blue-50 rounded-xl">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-code text-blue-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-800">Development</h4>
                                        <p class="text-sm text-gray-600">126 Course</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-green-50 rounded-xl">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-chart-line text-green-600"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-800">Business</h4>
                                        <p class="text-sm text-gray-600">213 Course</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Mentors -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-gray-800">Top Mentors</h3>
                            <button class="text-orange-500 hover:text-orange-600 font-medium">View All</button>
                        </div>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gray-300 rounded-full overflow-hidden">
                                        <img src="https://via.placeholder.com/40x40" alt="Shine Smith" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-800">Shine Smith</h4>
                                        <p class="text-sm text-gray-600">UI/UX Designer</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-800">18 Course</p>
                                    <p class="text-sm text-gray-600">1200 Follower</p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button class="px-4 py-2 bg-orange-500 text-white rounded-lg text-sm font-medium hover:bg-orange-600">Follow</button>
                                    <button class="p-2 text-gray-600 hover:text-gray-800">
                                        <i class="fas fa-envelope"></i>
                                    </button>
                                    <button class="p-2 text-gray-600 hover:text-gray-800">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gray-300 rounded-full overflow-hidden">
                                        <img src="https://via.placeholder.com/40x40" alt="Mikel" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-800">Mikel</h4>
                                        <p class="text-sm text-gray-600">Marketer</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-800">24 Course</p>
                                    <p class="text-sm text-gray-600">900 Follower</p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button class="px-4 py-2 bg-orange-500 text-white rounded-lg text-sm font-medium hover:bg-orange-600">Follow</button>
                                    <button class="p-2 text-gray-600 hover:text-gray-800">
                                        <i class="fas fa-envelope"></i>
                                    </button>
                                    <button class="p-2 text-gray-600 hover:text-gray-800">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gray-300 rounded-full overflow-hidden">
                                        <img src="https://via.placeholder.com/40x40" alt="Tohid golakar" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-800">Tohid golakar</h4>
                                        <p class="text-sm text-gray-600">Android Developer</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-800">64 Course</p>
                                    <p class="text-sm text-gray-600">1590 Follower</p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button class="px-4 py-2 bg-orange-500 text-white rounded-lg text-sm font-medium hover:bg-orange-600">Follow</button>
                                    <button class="p-2 text-gray-600 hover:text-gray-800">
                                        <i class="fas fa-envelope"></i>
                                    </button>
                                    <button class="p-2 text-gray-600 hover:text-gray-800">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gray-300 rounded-full overflow-hidden">
                                        <img src="https://via.placeholder.com/40x40" alt="Md Sakib" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-800">Md Sakib</h4>
                                        <p class="text-sm text-gray-600">Frontend Developer</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-800">85 Course</p>
                                    <p class="text-sm text-gray-600">3400 Follower</p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button class="px-4 py-2 bg-orange-500 text-white rounded-lg text-sm font-medium hover:bg-orange-600">Follow</button>
                                    <button class="p-2 text-gray-600 hover:text-gray-800">
                                        <i class="fas fa-envelope"></i>
                                    </button>
                                    <button class="p-2 text-gray-600 hover:text-gray-800">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                </div>
                            </div>
                        </div>


                        <!-- Digital Marketing Today -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-200 card-shadow">
                            <div class="flex items-center space-x-4">
                                <div class="w-20 h-20 bg-blue-600 rounded-xl flex items-center justify-center overflow-hidden">
                                    <i class="fas fa-chart-bar text-white text-2xl"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="text-lg font-semibold text-gray-800">Izin nugas hari ini</h4>
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm text-gray-600">Private</span>
                                            <div class="w-3 h-3 bg-purple-600 rounded-full"></div>
                                        </div>
                                    </div>
                                    <p class="text-gray-600 text-sm mb-2">nikmati kemudahan izin nugas di multimedia annur 2</p>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700"></span>
                                        <div class="flex items-center space-x-2">
                                            <button class="p-2 text-gray-600 hover:text-gray-800">
                                                <a href="./izin nugas/izin-nugas-entry.php">izin sekarang</a>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                </main>
            </div>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarTexts = document.querySelectorAll('.sidebar-text');
        const sidebarLogoText = document.querySelector('.sidebar-logo-text');
        const sidebarLogoIcon = document.querySelector('.sidebar-logo-icon');
        const sidebarNavItems = document.querySelectorAll('.sidebar-nav-item');
        const sidebarCreateButton = document.querySelector('.sidebar-create-button');
        const sidebarUpgradeSection = document.querySelector('.sidebar-upgrade-section');

        let isSidebarOpen = true; // Initial state: sidebar is open

        sidebarToggle.addEventListener('click', () => {
            if (isSidebarOpen) {
                // Collapse sidebar
                sidebar.classList.remove('w-64');
                sidebar.classList.add('w-20', 'collapsed'); // Add 'collapsed' class for specific styling

                // Hide texts
                sidebarTexts.forEach(text => {
                    text.classList.add('opacity-0', 'pointer-events-none');
                });
                sidebarLogoText.classList.add('opacity-0', 'pointer-events-none');
                sidebarUpgradeSection.classList.add('opacity-0', 'h-0', 'p-0', 'mt-0', 'pointer-events-none');

                // Adjust icon margins/alignment
                sidebarLogoIcon.classList.remove('space-x-2'); // Remove space-x-2 from logo container
                sidebarLogoIcon.classList.add('mx-auto'); // Center the icon
                sidebarNavItems.forEach(item => {
                    item.classList.remove('space-x-3', 'px-4');
                    item.classList.add('justify-center', 'px-0'); // Center icon, remove padding
                });
                sidebarCreateButton.classList.remove('space-x-2');
                sidebarCreateButton.classList.add('justify-center');
                sidebarCreateButton.querySelector('button').classList.remove('space-x-2');
                sidebarCreateButton.querySelector('button').classList.add('justify-center');

                // Change toggle icon
                sidebarToggle.querySelector('i').classList.replace('fa-bars', 'fa-arrow-right');

            } else {
                // Expand sidebar
                sidebar.classList.remove('w-20', 'collapsed');
                sidebar.classList.add('w-64');

                // Show texts
                sidebarTexts.forEach(text => {
                    text.classList.remove('opacity-0', 'pointer-events-none');
                });
                sidebarLogoText.classList.remove('opacity-0', 'pointer-events-none');
                sidebarUpgradeSection.classList.remove('opacity-0', 'h-0', 'p-0', 'mt-0', 'pointer-events-none');

                // Restore icon margins/alignment
                sidebarLogoIcon.classList.remove('mx-auto');
                sidebarLogoIcon.classList.add('space-x-2');
                sidebarNavItems.forEach(item => {
                    item.classList.remove('justify-center', 'px-0');
                    item.classList.add('space-x-3', 'px-4');
                });
                sidebarCreateButton.classList.remove('justify-center');
                sidebarCreateButton.classList.add('space-x-2');
                sidebarCreateButton.querySelector('button').classList.remove('justify-center');
                sidebarCreateButton.querySelector('button').classList.add('space-x-2');

                // Change toggle icon
                sidebarToggle.querySelector('i').classList.replace('fa-arrow-right', 'fa-bars');
            }
            isSidebarOpen = !isSidebarOpen; // Toggle the state
        });

        // Initial setup for collapsed state if desired (e.g., on mobile)
        // window.addEventListener('DOMContentLoaded', () => {
        //     if (window.innerWidth < 768) {
        //         sidebarToggle.click(); // Collapse sidebar on smaller screens by default
        //     }
        // });
    </script>
</body>

</html>
