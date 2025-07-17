<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo ($action === 'edit' ? 'Edit' : 'Buat'); ?> Profil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="file"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 0.375rem;
            box-sizing: border-box;
        }
        .form-group input[type="file"] { padding: 0.5rem; }
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .btn-primary { background-color: #4F46E5; color: white; border: none; }
        .btn-primary:hover { background-color: #4338CA; }
        .btn-secondary { background-color: #6B7280; color: white; border: none; }
        .btn-secondary:hover { background-color: #4B5563; }
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
                <header class="bg-white border-b border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <button id="sidebarToggle" class="p-2 text-gray-600 hover:text-gray-800 focus:outline-none mr-4">
                                <i class="fas fa-bars text-xl"></i>
                            </button>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-800">Edit Profil</h1>
                                <p class="text-gray-600"><?php echo date('l, d F Y'); ?></p>
                            </div>
                        </div>
                    </div>
                </header>

                <main class="p-6">
                    <div class="bg-white p-6 rounded-lg shadow-md max-w-md mx-auto">
                        <form method="post" action="profile_store.php" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="<?php echo $action; ?>">

                            <div class="form-group">
                                <label for="nama_lengkap">Nama Lengkap</label>
                                <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Nama Lengkap"  required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" placeholder="Email" required>
                            </div>
                            <div class="form-group">
                                <label for="alamat">Alamat</label>
                                <input type="text" id="alamat" name="alamat" placeholder="Alamat">
                            </div>
                            <div class="form-group">
                                <label for="no_hp">No HP</label>
                                <input type="text" id="no_hp" name="no_hp" placeholder="No HP" >
                            </div>
                            <div class="form-group">
                                <label for="foto">Foto Profil</label>
                                <input type="file" id="foto" name="foto" accept="image/*">
                            </div>
                            <div class="flex space-x-4 mt-6">
                                <button type="submit" class="btn btn-primary"></button>
                                <a href="profile_view.php" class="btn btn-secondary flex items-center justify-center">Batal</a>
                            </div>
                        </form>
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
