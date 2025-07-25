<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../auth/login.php');
    exit;
}

require_once '../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$id_user = $_SESSION['user_id'];

// Ambil tugas yang diberikan ke user yang statusnya 'pending' atau 'dikirim'
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
    WHERE t.id_penerima_tugas = ? AND t.status IN ("pending", "dikirim")
    ORDER BY t.deadline ASC
');

$stmt->bind_param('ii', $id_user, $id_user);
$stmt->execute();
$result = $stmt->get_result();

// Fetch user profile for display
$profile_name = "User "; // Default
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
    <title>User Dashboard</title>
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
        /* (Your existing styles here) */
    </style>
</head>

<body class="gradient-bg dark-mode-transition">
    <div id="sidebarOverlay" class="sidebar-overlay md:hidden"></div>
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div id="sidebar" class="w-64 bg-white dark:bg-slate-800 shadow-xl flex-shrink-0 sidebar transition-all duration-300 overflow-hidden glass-effect">
            <!-- Sidebar content here -->
        </div>

        <!-- Main Content -->
        <div id="mainContentArea" class="flex-1 flex flex-col main-content-area">
            <!-- Header -->
            <header class="bg-white dark:bg-slate-800 shadow-sm p-4 md:p-6 glass-effect">
                <!-- Header content here -->
            </header>
            <main class="flex-1 p-4 md:p-6">
                <!-- Welcome Section -->
                <div class="gradient-bg2 rounded-2xl p-6 mb-8 text-white animate-fade-in">
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
                </div>

                <!-- Task Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php
                            $current_time = new DateTime();
                            $deadline_time = new DateTime($row['deadline']);
                            $is_late = $current_time > $deadline_time;

                            // Status dan tampilan kartu
                            $card_class = 'border-l-4 border-blue-500';
                            $status_text = 'Pending';
                            $action_text = 'Kerjakan';
                            $action_link = "tugas_kerjakan.php?id=" . $row['id'];
                            $show_late_warning = false;

                            // Jika status ditolak
                            if ($row['status'] == 'ditolak') {
                                $card_class = 'border-l-4 border-red-500 bg-red-50';
                                $status_text = 'Ditolak';
                                $action_text = 'Kerjakan Ulang';
                                $action_link = "tugas_kerjakan.php?id=" . $row['id'];
                            }

                            // Jika status dikirim
                            if ($row['status'] == 'dikirim') {
                                $card_class = 'border-l-4 border-yellow-500';
                                $status_text = 'Menunggu Penilaian';
                                $action_text = 'Sudah Dikerjakan';
                                $action_link = "#";
                            }

                            // Jika sudah lewat deadline
                            if ($is_late) {
                                $show_late_warning = true;
                                // Jika tugas ditolak, tetap bisa dikerjakan ulang
                                if ($row['status'] == 'ditolak') {
                                    $action_text = 'Kerjakan Ulang';
                                    $action_link = "tugas_kerjakan.php?id=" . $row['id'];
                                }
                            }
                            ?>

                            <div class="card <?= $card_class ?> shadow-md mb-4">
                                <div class="p-4">
                                    <?php if ($show_late_warning): ?>
                                        <div class="mb-3 p-2 bg-red-100 text-red-800 rounded text-sm flex items-center">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>
                                            Tugas ini sudah melewati deadline
                                        </div>
                                    <?php endif; ?>

                                    <h3 class="font-semibold text-lg"><?= htmlspecialchars($row['judul']) ?></h3>
                                    <p class="text-gray-600 mb-2"><?= htmlspecialchars($row['deskripsi']) ?></p>

                                    <div class="flex justify-between items-center mt-3">
                                        <div>
                                            <span class="text-sm <?= $is_late ? 'text-red-600' : 'text-gray-500' ?>">
                                                <i class="fas fa-calendar-day mr-1"></i>
                                                Deadline: <?= date('d M Y H:i', strtotime($row['deadline'])) ?>
                                                <?php if ($is_late): ?>
                                                    <span class="ml-2 text-red-600">(Terlambat)</span>
                                                <?php endif; ?>
                                            </span>
                                        </div>

                                        <?php if ($row['status'] == 'pending' || $row['status'] == 'ditolak'): ?>
                                            <a href="<?= $action_link ?>" 
                                               class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm">
                                                <i class="fas fa-edit mr-1"></i>
                                                <?= $action_text ?>
                                            </a>
                                        <?php elseif ($row['status'] == 'dikirim'): ?>
                                            <span class="px-3 py-1 bg-gray-200 text-gray-700 rounded text-sm">
                                                <i class="fas fa-check mr-1"></i>
                                                <?= $action_text ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
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
                                <a href="./izin malam/izin-malam-entry.php" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg glass-header">
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
                                <a href="./izin nugas/izin-nugas-entry.php" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg glass-header">
                                    <i class="fas fa-plus mr-2"></i>
                                    Ajukan Izin
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <?php
            // Sertakan footer
            include './footer.php'; // Path relatif dari 'anggota/' ke 'includes/'
            $conn->close();
            ?>
        </div>
    </div>
