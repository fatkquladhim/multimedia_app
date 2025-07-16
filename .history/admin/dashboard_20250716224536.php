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
    <title>Eduhouse - Learning Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Custom styles if needed, though Tailwind handles most */
        .sidebar {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .main-content-area {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar-nav-item {
            transition: opacity 0.3s ease-in-out;
        }
    </style>
</head>
<body class="bg-amber-50">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div id="sidebar" class="w-20 bg-white shadow-lg flex-shrink-0 sidebar transition-all duration-300 overflow-hidden">
            <div class="p-4 flex justify-center">
                <i class="fas fa-home text-2xl text-gray-800"></i>
            </div>
            
            <nav class="mt-4">
                <div class="px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 hidden">
                    Main Menu
                </div>
                
                <div class="space-y-2">
                    <a href="#" class="flex items-center justify-center py-3 text-orange-500 bg-orange-50 border-r-3 border-orange-500 sidebar-nav-item">
                        <i class="fas fa-home w-5 h-5 mr-3 transition-all duration-300"></i>
                        <span class="font-medium opacity-100 transition-opacity duration-300">Overview</span>
                    </a>
                    
                    <a href="#" class="flex items-center px-6 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-50">
                        <i class="fas fa-book w-5 h-5 mr-3"></i>
                        <span class="font-medium">E-Book</span>
                    </a>
                    
                    <a href="#" class="flex items-center px-6 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-50">
                        <i class="fas fa-heart w-5 h-5 mr-3"></i>
                        <span class="font-medium">My Courses</span>
                    </a>
                    
                    <a href="#" class="flex items-center px-6 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-50">
                        <i class="fas fa-shopping-cart w-5 h-5 mr-3"></i>
                        <span class="font-medium">Purchase Course</span>
                    </a>
                    
                    <a href="#" class="flex items-center px-6 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-50">
                        <i class="fas fa-check-circle w-5 h-5 mr-3"></i>
                        <span class="font-medium">Completed Courses</span>
                    </a>
                    
                    <a href="#" class="flex items-center px-6 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-50">
                        <i class="fas fa-users w-5 h-5 mr-3"></i>
                        <span class="font-medium">Community</span>
                    </a>
                </div>
                
                <div class="px-6 text-xs font-semibold text-gray-500 uppercase tracking-wider mt-8 mb-4">
                    Setting
                </div>
                
                <div class="space-y-2">
                    <a href="#" class="flex items-center px-6 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-50">
                        <i class="fas fa-user w-5 h-5 mr-3"></i>
                        <span class="font-medium">Profile</span>
                    </a>
                    
                    <a href="#" class="flex items-center px-6 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-50">
                        <i class="fas fa-cog w-5 h-5 mr-3"></i>
                        <span class="font-medium">Setting</span>
                    </a>
                    
                    <a href="#" class="flex items-center px-6 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-50">
                        <i class="fas fa-sign-out-alt w-5 h-5 mr-3"></i>
                        <span class="font-medium">Logout</span>
                    </a>
                </div>
            </nav>
        </div>

        <!-- Main Content Area -->
        <div id="mainContentArea" class="flex-1 flex flex-col main-content-area">
            <!-- Header -->
            <header class="bg-white shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <!-- Sidebar Toggle Button -->
                        <button id="sidebarToggle" class="p-2 text-gray-600 hover:text-gray-800 focus:outline-none">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <div class="relative">
                            <input type="text" placeholder="Search here" class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>
                        <div class="w-16 h-16 bg-orange-400 rounded-2xl flex items-center justify-center shadow-lg transform rotate-12">
                            <i class="fas fa-graduation-cap text-white text-2xl"></i>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <button class="p-2 text-gray-600 hover:text-gray-800">
                            <i class="fas fa-bell text-xl"></i>
                        </button>
                        <div class="w-10 h-10 bg-gray-300 rounded-full overflow-hidden">
                            <img src="https://via.placeholder.com/40x40" alt="Profile" class="w-full h-full object-cover">
                        </div>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <main class="flex-1 p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Course in Progress</h2>
                    <button class="text-orange-500 hover:text-orange-600 font-medium">View All</button>
                </div>

                <!-- Course Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <!-- App Design Course -->
                    <div class="bg-purple-50 rounded-2xl p-6 relative overflow-hidden">
                        <div class="absolute top-4 right-4 w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-mobile-alt text-purple-600"></i>
                        </div>
                        <div class="text-sm text-gray-600 mb-2">Dec 15, 2020</div>
                        <h3 class="text-xl font-bold text-purple-700 mb-2">App Design</h3>
                        <p class="text-gray-700 mb-4">Learn App design from our expert trainer</p>
                        <div class="text-sm text-gray-600 mb-2">Finally a comprehensive guide to using sketch for designing...</div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Progress</span>
                            <span class="text-sm font-bold text-purple-700">20%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="bg-purple-600 h-2 rounded-full" style="width: 20%"></div>
                        </div>
                    </div>

                    <!-- Web Design Course -->
                    <div class="bg-orange-50 rounded-2xl p-6 relative overflow-hidden">
                        <div class="absolute top-4 right-4 w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-code text-orange-600"></i>
                        </div>
                        <div class="text-sm text-gray-600 mb-2">Dec 15, 2020</div>
                        <h3 class="text-xl font-bold text-orange-700 mb-2">Web Design</h3>
                        <p class="text-gray-700 mb-4">Learn Web design from our expert trainer</p>
                        <div class="text-sm text-gray-600 mb-2">Finally a comprehensive guide to using sketch for designing...</div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Progress</span>
                            <span class="text-sm font-bold text-orange-700">80%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="bg-orange-600 h-2 rounded-full" style="width: 80%"></div>
                        </div>
                    </div>

                    <!-- Dashboard Course -->
                    <div class="bg-blue-50 rounded-2xl p-6 relative overflow-hidden">
                        <div class="absolute top-4 right-4 w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-bar text-blue-600"></i>
                        </div>
                        <div class="text-sm text-gray-600 mb-2">Dec 15, 2020</div>
                        <h3 class="text-xl font-bold text-blue-700 mb-2">Dashboard</h3>
                        <p class="text-gray-700 mb-4">Learn Typography from our expert trainer</p>
                        <div class="text-sm text-gray-600 mb-2">Finally a comprehensive guide to using sketch for designing...</div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Progress</span>
                            <span class="text-sm font-bold text-blue-700">50%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: 50%"></div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
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
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
       const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');
const mainContentArea = document.getElementById('mainContentArea');

// Initial state: sidebar is open
let isSidebarOpen = true; 

sidebarToggle.addEventListener('click', () => {
    if (isSidebarOpen) {
        // Close sidebar smoothly
        sidebar.classList.remove('w-64');
        sidebar.classList.add('w-20');
        sidebar.style.opacity = '0'; // Fade out

        setTimeout(() => {
            sidebar.style.opacity = '1'; // Reset opacity for next open
            sidebar.querySelectorAll('span.font-medium, div.px-6.text-xs')
                .forEach(el => el.classList.add('opacity-0', 'absolute'));
        }, 400); // Match this timeout with the transition duration
        sidebarToggle.querySelector('i').classList.replace('fa-bars', 'fa-arrow-right');
    } else {
        // Show sidebar smoothly
        sidebar.querySelectorAll('span.font-medium, div.px-6.text-xs')
            .forEach(el => {
                el.classList.remove('opacity-0', 'absolute');
                el.classList.add('opacity-100');
            });

        setTimeout(() => {
            sidebar.classList.remove('w-20');
            sidebar.classList.add('w-64');
        }, 50); // Slight delay for the opacity transition
        sidebarToggle.querySelector('i').classList.replace('fa-arrow-right', 'fa-bars');
    }
    isSidebarOpen = !isSidebarOpen; // Toggle the state
});

    </script>
</body>
</html>