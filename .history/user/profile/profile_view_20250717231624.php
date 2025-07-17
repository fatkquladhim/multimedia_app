<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$stmt = $conn->prepare('SELECT nama_lengkap, email, alamat, no_hp, foto FROM profile WHERE id_user = ?');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($nama_lengkap, $email, $alamat, $no_hp, $foto);
$stmt->fetch();
$stmt->close();


$profile_exists = !empty($nama_lengkap); // Check if profile data exists
include '../header_beckend.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profil Saya</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .sidebar {
            transition: width 0.3s ease-in-out;
        }

        .sidebar-text {
            transition: opacity 0.3s ease-in-out, margin-left 0.3s ease-in-out;
        }

        .sidebar-nav-item {
            justify-content: flex-start;
        }

        .sidebar.collapsed .sidebar-nav-item {
            justify-content: center;
        }

        .sidebar.collapsed .sidebar-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
            white-space: nowrap;
            pointer-events: none;
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
            <?php include '../sidebar.php'; ?>

            <div class="flex-1 p-2">
               <?php include '../header_frontend.php'; ?>

                <main class="p-6">
                    <?php
                    if (isset($_GET['status'])) {
                        echo '<div class="message ' . htmlspecialchars($_GET['status']) . '">' . htmlspecialchars($_GET['message']) . '</div>';
                    }
                    ?>

                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <h2 class="text-xl font-bold mb-4">Detail Profil</h2>
                        <p class="mb-2"><strong class="text-gray-700">Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                        <p class="mb-2"><strong class="text-gray-700">Nama Lengkap:</strong> <?php echo htmlspecialchars($nama_lengkap ?? '-'); ?></p>
                        <p class="mb-2"><strong class="text-gray-700">Email:</strong> <?php echo htmlspecialchars($email ?? '-'); ?></p>
                        <p class="mb-2"><strong class="text-gray-700">Alamat:</strong> <?php echo htmlspecialchars($alamat ?? '-'); ?></p>
                        <p class="mb-2"><strong class="text-gray-700">No HP:</strong> <?php echo htmlspecialchars($no_hp ?? '-'); ?></p>
                        <?php if ($foto) { ?>
                            <p class="mb-4"><strong class="text-gray-700">Foto Profil:</strong><br><img src="../../uploads/profiles/<?php echo htmlspecialchars($foto); ?>" alt="Foto Profil" class="w-32 h-32 object-cover rounded-full mt-2"></p>
                        <?php } else { ?>
                            <p class="mb-4"><strong class="text-gray-700">Foto Profil:</strong> -</p>
                        <?php } ?>

                        <div class="mt-6">
                            <?php if ($profile_exists): ?>
                                <a href="profile_edit.php?action=edit" class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition duration-300">Edit Profil</a>
                            <?php else: ?>
                                <a href="profile_create.php?action=create" class="inline-block bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg transition duration-300">Buat Profil</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>
    <script>
        // Sidebar toggle logic (same as dashboard.php)
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
