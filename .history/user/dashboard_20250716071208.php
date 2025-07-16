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
?>
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
            transition: width 0.3s ease-in-out; /* Smooth transition for width */
        }
        .sidebar-text {
            transition: opacity 0.3s ease-in-out, margin-left 0.3s ease-in-out; /* Smooth transition for text visibility */
        }
        .sidebar-nav-item {
            justify-content: flex-start; /* Default alignment for expanded state */
        }
        .sidebar.collapsed .sidebar-nav-item {
            justify-content: center; /* Center icons when collapsed */
        }
        .sidebar.collapsed .sidebar-text {
            opacity: 0;
            width: 0; /* Collapse width of text container */
            overflow: hidden;
            white-space: nowrap;
            pointer-events: none; /* Prevent interaction with hidden text */
        }
        .sidebar.collapsed .sidebar-logo-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
            white-space: nowrap;
            pointer-events: none;
        }
        .sidebar.collapsed .sidebar-logo-icon {
            margin-right: 0 !important; /* Remove margin when collapsed */
        }
        .sidebar.collapsed .sidebar-create-button .sidebar-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
            white-space: nowrap;
            pointer-events: none;
        }
        .sidebar.collapsed .sidebar-create-button i {
            margin-right: 0 !important; /* Remove margin for icon */
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
                    <button class="w-full bg-purple-600 text-white rounded-lg p-3 flex items-center justify-center space-x-2 hover:bg-purple-700 transition-colors">
                        <i class="fas fa-plus flex-shrink-0"></i>
                        <span class="font-medium sidebar-text">Create New Pitch</span>
                    </button>
                </div>

                <!-- Navigation -->
                <nav class="space-y-2">
                    <a href="#" class="flex items-center space-x-3 px-4 py-3 text-purple-600 bg-purple-50 rounded-lg border-l-4 border-purple-600 sidebar-nav-item">
                        <i class="fas fa-th-large flex-shrink-0"></i>
                        <span class="font-medium sidebar-text">Dashboard</span>
                    </a>

                    <a href="#" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg sidebar-nav-item">
                        <i class="fas fa-edit flex-shrink-0"></i>
                        <span class="font-medium sidebar-text">Editor</span>
                    </a>

                    <a href="#" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg sidebar-nav-item">
                        <i class="fas fa-users flex-shrink-0"></i>
                        <span class="font-medium sidebar-text">Leads</span>
                    </a>

                    <a href="#" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg sidebar-nav-item">
                        <i class="fas fa-cog flex-shrink-0"></i>
                        <span class="font-medium sidebar-text">Settings</span>
                    </a>

                    <a href="#" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg sidebar-nav-item">
                        <i class="fas fa-eye flex-shrink-0"></i>
                        <span class="font-medium sidebar-text">Preview</span>
                    </a>
                </nav>

                <!-- Upgrade Section -->
                <div class="mt-auto pt-8 sidebar-upgrade-section">
                    <div class="bg-white rounded-lg p-4 border border-gray-200 text-center">
                        <div class="w-16 h-16 bg-purple-100 rounded-lg mx-auto mb-3 flex items-center justify-center">
                            <i class="fas fa-laptop text-purple-600 text-xl"></i>
                        </div>
                        <p class="text-sm text-gray-600 mb-3 sidebar-text">Upgrade to PRO for unlimited access</p>
                        <button class="w-full bg-purple-600 text-white rounded-lg py-2 text-sm font-medium hover:bg-purple-700 transition-colors sidebar-text">
                            Upgrade
                        </button>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1 flex flex-col">
                <!-- Header -->
                <header class="bg-white border-b border-gray-200 p-6">
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
                <main class="flex-1 p-6">
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
                    <button class="text-orange-500 hover:text-orange-600 font-medium">View All</button>
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
                                <?php if (!$row['jawaban_id']): // If no answer submitted yet ?>
                                    <a href="tugas_kerjakan.php?id=<?php echo $row['id']; ?>">Kerjakan</a>
                                <?php else: // If answer already submitted ?>
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
                        <!-- Next in Fashion -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-200 card-shadow">
                            <div class="flex items-center space-x-4">
                                <div class="w-20 h-20 bg-yellow-400 rounded-xl flex items-center justify-center overflow-hidden">
                                    <i class="fas fa-tshirt text-white text-2xl"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="text-lg font-semibold text-gray-800">Izin malam hari ini</h4>
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm text-gray-600">Private</span>
                                            <div class="w-3 h-3 bg-purple-600 rounded-full"></div>
                                        </div>
                                    </div>
                                    <p class="text-gray-600 text-sm mb-2">nikmati kemudahan izin malam di multimedia annur 2</p>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <button class="p-2 text-gray-600 hover:text-gray-800">
                                                <a href="./izin malam/izin-malam-entry.php">izin sekarang</a>
                                            </button>
                                        </div>
                                    </div>
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
                                        <h4 class="text-lg font-semibold text-gray-800">Izin malam hari ini</h4>
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm text-gray-600">Private</span>
                                            <div class="w-3 h-3 bg-gray-400 rounded-full"></div>
                                        </div>
                                    </div>
                                    <p class="text-gray-600 text-sm mb-2">nikmati kemudahan izin malam di multimedia annur 2</p>
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <button class="p-2 text-gray-600 hover:text-gray-800">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="p-2 text-gray-600 hover:text-gray-800">
                                                <i class="fas fa-trash"></i>
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
<?php
$stmt->close();
$conn->close();
?>
