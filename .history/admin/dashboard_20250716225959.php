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
                       WHERE t.status = 'pending_review' LIMIT 3"; // Fetch up to 3 tasks for review
$tugas_reviews = $conn->query($tugas_review_query);

// Check for errors
if (!$tugas_reviews) {
    die("Database query failed: " . $conn->error);
}

// Fetch data for "Anggota yang Izin Malam"
$izin_malam_query = "SELECT a.nama, im.tanggal, im.jam_izin, im.jam_selesai_izin 
                     FROM izin_malam im 
                     JOIN anggota a ON im.id_anggota = a.id 
                     WHERE im.status = 'approved' AND im.tanggal >= CURDATE() LIMIT 4"; // Fetch up to 4 active night permits
$izin_malam_anggota = $conn->query($izin_malam_query);

// Check for errors
if (!$izin_malam_anggota) {
    die("Database query failed: " . $conn->error);
}

// Fetch data for "Anggota Teratas"
$top_anggota_query = "SELECT id, nama FROM anggota ORDER BY id DESC LIMIT 4"; // Example: just fetch latest 4 members
$top_anggota = $conn->query($top_anggota_query);

// Check for errors
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
                    <h2 class="text-2xl font-bold text-gray-800">Tugas User Review</h2>
                    <a href="beri tugas/tugas_user_review.php" class="text-orange-500 hover:text-orange-600 font-medium">View All</a>
                </div>

                <!-- Tugas User Review Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <?php if ($tugas_reviews->num_rows > 0): ?>
                        <?php while($row = $tugas_reviews->fetch_assoc()): ?>
                            <div class="bg-purple-50 rounded-2xl p-6 relative overflow-hidden">
                                <div class="absolute top-4 right-4 w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-tasks text-purple-600"></i>
                                </div>
                                <div class="text-sm text-gray-600 mb-2">Dari: <?php echo htmlspecialchars($row['username']); ?></div>
                                <h3 class="text-xl font-bold text-purple-700 mb-2"><?php echo htmlspecialchars($row['nama_tugas']); ?></h3>
                                <p class="text-gray-700 mb-4"><?php echo htmlspecialchars($row['deskripsi']); ?></p>
                                <div class="text-sm text-gray-600 mb-2">Status: Menunggu Review</div>
                                <div class="flex items-center justify-between">
                                   <button class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700">
                                       <a href="beri tugas/tugas_user_review.php?task_id=<?php echo $row['id']; ?>">Nilai Tugas</a>
                                   </button> 
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="col-span-full text-gray-600">Tidak ada tugas yang menunggu review saat ini.</p>
                    <?php endif; ?>
                </div>

                <!-- Bottom Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Anggota yang Izin Malam -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h3 class="text-xl font-bold text-gray-800 mb-6">Anggota yang Izin Malam</h3>
                        <div class="space-y-4">
                            <?php if ($izin_malam_anggota->num_rows > 0): ?>
                                <?php while($row = $izin_malam_anggota->fetch_assoc()): ?>
                                    <div class="flex items-center justify-between p-4 bg-purple-50 rounded-xl">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-moon text-purple-600"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($row['nama']); ?></h4>
                                                <p class="text-sm text-gray-600">Izin: <?php echo htmlspecialchars($row['tanggal']); ?>, <?php echo htmlspecialchars($row['jam_izin']); ?> s/d <?php echo htmlspecialchars($row['jam_selesai_izin']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-gray-600">Tidak ada anggota yang sedang izin malam.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Anggota Teratas -->
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-gray-800">Anggota Teratas</h3>
                            <a href="anggota/anggota.php" class="text-orange-500 hover:text-orange-600 font-medium">View All</a>
                        </div>
                        <div class="space-y-4">
                            <?php if ($top_anggota->num_rows > 0): ?>
                                <?php while($row = $top_anggota->fetch_assoc()): ?>
                                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-gray-300 rounded-full overflow-hidden">
                                                <img src="https://via.placeholder.com/40x40" alt="<?php echo htmlspecialchars($row['nama']); ?>" class="w-full h-full object-cover">
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($row['nama']); ?></h4>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <button class="px-4 py-2 bg-orange-500 text-white rounded-lg text-sm font-medium hover:bg-orange-600">Detail</button>
                                            <button class="p-2 text-gray-600 hover:text-gray-800">
                                                <i class="fas fa-envelope"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-gray-600">Tidak ada anggota teratas yang ditemukan.</p>
                            <?php endif; ?>
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
                // Close sidebar smoothly with nested transitions
                sidebar.classList.add('transition-all');
                setTimeout(() => {
                    sidebar.classList.remove('w-64');
                    sidebar.classList.add('w-20');
                    
                    // Hide text content with delay
                    setTimeout(() => {
                        sidebar.querySelectorAll('span.font-medium, div.px-6.text-xs')
                            .forEach(el => el.classList.add('opacity-0', 'absolute'));
                    }, 150);
                    
                    sidebarToggle.querySelector('i').classList.replace('fa-bars', 'fa-arrow-right');
                }, 10);
            } else {
                // Show text first with opacity transition
                sidebar.querySelectorAll('span.font-medium, div.px-6.text-xs')
                    .forEach(el => {
                        el.classList.remove('opacity-0', 'absolute');
                        el.classList.add('opacity-100');
                    });
                
                // Then expand sidebar
                setTimeout(() => {
                    sidebar.classList.remove('w-20');
                    sidebar.classList.add('w-64');
                    
                    sidebarToggle.querySelector('i').classList.replace('fa-arrow-right', 'fa-bars');
                }, 50);
            }
            isSidebarOpen = !isSidebarOpen; // Toggle the state
        });
    </script>
</body>
</html>
