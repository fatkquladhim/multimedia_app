<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../../auth/login.php');
    exit;
}

require_once '../../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$id_user = $_SESSION['user_id'];
$nama_lengkap = '';
$email = '';

// Fetch current profile data
$stmt_profile = $conn->prepare('SELECT nama_lengkap, email FROM users WHERE id = ?');
if ($stmt_profile) {
    $stmt_profile->bind_param('i', $id_user);
    $stmt_profile->execute();
    $stmt_profile->bind_result($nama_lengkap, $email);
    $stmt_profile->fetch();
    $stmt_profile->close();
} else {
    // Handle error
    header('Location: profile_settings.php?status=error&message=Gagal mengambil data profil.');
    exit;
}



// Define profile_name for header
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
<html>
<head>
    <title>Pengaturan Akun</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: bold; }
        .form-group input[type="date"],
        .form-group input[type="time"],
        .form-group input[type="text"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 0.375rem;
            box-sizing: border-box;
        }
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
                    <div class="bg-white p-6 rounded-lg shadow-md max-w-md mx-auto">
                        <h2 class="text-xl font-bold mb-4">Pengaturan Akun</h2>

                        <!-- Form untuk mengubah Nama Lengkap dan Email -->
                        <form method="post" action="profile_update.php">
                            <input type="hidden" name="action" value="update_profile">
                            <div class="form-group">
                                <label for="nama_lengkap">Nama Lengkap</label>
                                <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($nama_lengkap); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                            </div>
                            <div class="flex space-x-4 mt-6">
                                <button type="submit" class="btn btn-primary">Update Profil</button>
                            </div>
                        </form>

                        <hr class="my-8">

                        <!-- Form untuk mengubah Sandi -->
                        <form method="post" action="profile_update.php">
                            <input type="hidden" name="action" value="change_password">
                            <div class="form-group">
                                <label for="current_password">Sandi Saat Ini</label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>
                            <div class="form-group">
                                <label for="new_password">Sandi Baru</label>
                                <input type="password" id="new_password" name="new_password" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Konfirmasi Sandi Baru</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                            <div class="flex space-x-4 mt-6">
                                <button type="submit" class="btn btn-primary">Ubah Sandi</button>
                            </div>
                        </form>
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
