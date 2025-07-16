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
    <title>Pitch.io - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-yellow { background: linear-gradient(135deg, #FCD34D 0%, #F59E0B 100%); }
        .gradient-purple { background: linear-gradient(135deg, #8B5CF6 0%, #6D28D9 100%); }
        .gradient-pink { background: linear-gradient(135deg, #F472B6 0%, #EC4899 100%); }
        .gradient-gray { background: linear-gradient(135deg, #D1D5DB 0%, #9CA3AF 100%); }
        .card-shadow { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }

        /* Custom styles for sidebar transition */
        .sidebar { transition: width 0.3s ease-in-out; }
        .sidebar-text { transition: opacity 0.3s ease-in-out, margin-left 0.3s ease-in-out; }
        .sidebar-nav-item { justify-content: flex-start; }
        .sidebar.collapsed .sidebar-nav-item { justify-content: center; }
        .sidebar.collapsed .sidebar-text { opacity: 0; width: 0; overflow: hidden; white-space: nowrap; pointer-events: none; }
        .sidebar.collapsed .sidebar-logo-text { opacity: 0; width: 0; overflow: hidden; white-space: nowrap; pointer-events: none; }
        .sidebar.collapsed .sidebar-logo-icon { margin-right: 0 !important; }
        .sidebar.collapsed .sidebar-create-button .sidebar-text { opacity: 0; width: 0; overflow: hidden; white-space: nowrap; pointer-events: none; }
        .sidebar.collapsed .sidebar-create-button i { margin-right: 0 !important; }
        .sidebar.collapsed .sidebar-upgrade-section { opacity: 0; height: 0; overflow: hidden; padding-top: 0; padding-bottom: 0; margin-top: 0; pointer-events: none; }

        .message { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
    </style>
</head>

<body>
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden ">
        <div class="flex h-screen">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>

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
                                <p class="text-gray-600"><?php echo date('l, d F Y'); ?></p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <div class="flex items-center space-x-2">
                                <div class="w-10 h-10 bg-white-600 rounded-full flex items-center justify-center overflow-hidden">
                                    <img src="../uploads/profiles/<?php echo $profile_photo; ?>" alt="Profile Photo" class="w-full h-full object-cover rounded-full">
                                </div>
                                <div class="flex items-center space-x-1">
                                    <a href="./profile/profile_view.php">
                                        <span class="font-medium text-gray-800"><?php echo $profile_name; ?></span>
                                        <i class="fas fa-chevron-down text-gray-600 text-sm"></i>
                                    </a>
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
                            echo '<div class="message ' . htmlspecialchars($_GET['status']) . '">' . htmlspecialchars($_GET['message']) . '</div>';
                        }
                        ?>
                        <button class="text-orange-500 hover:text-orange-600 font-medium"><a href="./tugas/riwayat_tugas.php"> VIEW All</a></button>
                    </div>

                    <!-- Task Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        <?php
                        if ($result->num_rows > 0):
                            while ($row = $result->fetch_assoc()):
                                $card_bg_color = 'bg-purple-50';
                                $icon_bg_color = 'bg-purple-100';
                                $icon_color = 'text-purple-600';
                                $title_color = 'text-purple-700';
                                $action_color = 'text-purple-700';
                                $icon_class = 'fas fa-tasks';

                                if ($row['jawaban_id'] && $row['status'] == 'selesai') {
                                    $card_bg_color = 'bg-orange-50';
                                    $icon_bg_color = 'bg-orange-100';
                                    $icon_color = 'text-orange-600';
                                    $title_color = 'text-orange-700';
                                    $action_color = 'text-orange-700';
                                    $icon_class = 'fas fa-hourglass-half';
                                } elseif ($row['jawaban_id'] && $row['status'] == 'diperiksa') {
                                    $card_bg_color = 'bg-blue-50';
                                    $icon_bg_color = 'bg-blue-100';
                                    $icon_color = 'text-blue-600';
                                    $title_color = 'text-blue-700';
                                    $action_color = 'text-blue-700';
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
                                                echo 'Menunggu Penilaian';
                                            } elseif ($row['jawaban_id'] && $row['status'] == 'diperiksa') {
                                                echo 'Sudah Dinilai';
                                            } else {
                                                echo htmlspecialchars($row['status']);
                                            }
                                            ?>
                                        </span>
                                        <span class="text-sm font-bold <?= $action_color ?>">
                                            <?php if (!$row['jawaban_id']): ?>
                                                <a href="./tugas/tugas_kerjakan.php?id=<?php echo $row['id']; ?>">Kerjakan</a>
                                            <?php else: ?>
                                                Sudah dikerjakan
                                            <?php endif; ?>
                                        </span>
                                    </div>
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

                    <div class="space-y-4">
                        <div class="bg-white rounded-2xl p-6 border border-gray-200 card-shadow">
                            <div class="flex items-center space-x-4">
                                <div class="w-20 h-20 bg-yellow-400 rounded-xl flex items-center justify-center overflow-hidden">
                                    <i class="fas fa-moon text-white text-2xl"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="text-lg font-semibold text-gray-800">Izin Malam Hari Ini</h4>
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm text-gray-600">Private</span>
                                            <div class="w-3 h-3 bg-purple-600 rounded-full"></div>
                                        </div>
                                    </div>
                                    <p class="text-gray-600 text-sm mb-2">Nikmati kemudahan izin malam di multimedia annur 2</p>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700"></span>
                                        <div class="flex items-center space-x-2">
                                            <button class="p-2 text-gray-600 hover:text-gray-800">
                                                <a href="./izin malam/izin-malam-entry.php">Izin Sekarang</a>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl p-6 border border-gray-200 card-shadow">
                            <div class="flex items-center space-x-4">
                                <div class="w-20 h-20 bg-blue-600 rounded-xl flex items-center justify-center overflow-hidden">
                                    <i class="fas fa-laptop-code text-white text-2xl"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="text-lg font-semibold text-gray-800">Izin Nugas Hari Ini</h4>
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm text-gray-600">Private</span>
                                            <div class="w-3 h-3 bg-purple-600 rounded-full"></div>
                                        </div>
                                    </div>
                                    <p class="text-gray-600 text-sm mb-2">Nikmati kemudahan izin nugas di multimedia annur 2</p>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700"></span>
                                        <div class="flex items-center space-x-2">
                                            <button class="p-2 text-gray-600 hover:text-gray-800">
                                                <a href="./izin nugas/izin-nugas-entry.php">Izin Sekarang</a>
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

        let isSidebarOpen = true;

        sidebarToggle.addEventListener('click', () => {
            if (isSidebarOpen) {
                sidebar.classList.remove('w-64');
                sidebar.classList.add('w-20', 'collapsed');

                sidebarTexts.forEach(text => { text.classList.add('opacity-0', 'pointer-events-none'); });
                if (sidebarLogoText) sidebarLogoText.classList.add('opacity-0', 'pointer-events-none');
                if (sidebarUpgradeSection) sidebarUpgradeSection.classList.add('opacity-0', 'h-0', 'p-0', 'mt-0', 'pointer-events-none');

                if (sidebarLogoIcon) {
                    sidebarLogoIcon.classList.remove('space-x-2');
                    sidebarLogoIcon.classList.add('mx-auto');
                }
                sidebarNavItems.forEach(item => {
                    item.classList.remove('space-x-3', 'px-4');
                    item.classList.add('justify-center', 'px-0');
                });
                if (sidebarCreateButton) {
                    sidebarCreateButton.classList.remove('space-x-2');
                    sidebarCreateButton.classList.add('justify-center');
                    if (sidebarCreateButton.querySelector('button')) {
                        sidebarCreateButton.querySelector('button').classList.remove('space-x-2');
                        sidebarCreateButton.querySelector('button').classList.add('justify-center');
                    }
                }

                sidebarToggle.querySelector('i').classList.replace('fa-bars', 'fa-arrow-right');

            } else {
                sidebar.classList.remove('w-20', 'collapsed');
                sidebar.classList.add('w-64');

                sidebarTexts.forEach(text => { text.classList.remove('opacity-0', 'pointer-events-none'); });
                if (sidebarLogoText) sidebarLogoText.classList.remove('opacity-0', 'pointer-events-none');
                if (sidebarUpgradeSection) sidebarUpgradeSection.classList.remove('opacity-0', 'h-0', 'p-0', 'mt-0', 'pointer-events-none');

                if (sidebarLogoIcon) {
                    sidebarLogoIcon.classList.remove('mx-auto');
                    sidebarLogoIcon.classList.add('space-x-2');
                }
                sidebarNavItems.forEach(item => {
                    item.classList.remove('justify-center', 'px-0');
                    item.classList.add('space-x-3', 'px-4');
                });
                if (sidebarCreateButton) {
                    sidebarCreateButton.classList.remove('justify-center');
                    sidebarCreateButton.classList.add('space-x-2');
                    if (sidebarCreateButton.querySelector('button')) {
                        sidebarCreateButton.querySelector('button').classList.remove('justify-center');
                        sidebarCreateButton.querySelector('button').classList.add('space-x-2');
                    }
                }

                sidebarToggle.querySelector('i').classList.replace('fa-arrow-right', 'fa-bars');
            }
            isSidebarOpen = !isSidebarOpen;
        });
    </script>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>
