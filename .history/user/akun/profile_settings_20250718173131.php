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

$conn->close();

// Define profile_name for header
$profile_name = $nama_lengkap; // Use the fetched name for the header
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pengaturan Akun</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
