    <?php
    // includes/header.php
    // Pastikan session sudah dimulai di file yang meng-include ini
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Dashboard</title> <!-- Sesuaikan judul per halaman -->
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
            .sidebar {
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .main-content-area {
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .sidebar-nav-item {
                transition: all 0.3s ease-in-out;
            }
            .dark-mode-transition {
                transition: background-color 0.3s ease, color 0.3s ease;
            }
            .gradient-bg {
                background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
            }
            .dark .gradient-bg {
                background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            }
            .glass-effect {
                backdrop-filter: blur(10px);
                background: rgba(255, 255, 255, 0.9);
            }
            .dark .glass-effect {
                background: rgba(30, 41, 59, 0.9);
            }
            .hover-scale {
                transition: transform 0.2s ease;
            }
            .hover-scale:hover {
                transform: scale(1.02);
            }
            .animate-fade-in {
                animation: fadeIn 0.5s ease-in-out;
            }
            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            .mobile-menu {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            .mobile-menu.active {
                transform: translateX(0);
            }
            @media (max-width: 768px) {
                .sidebar {
                    position: fixed;
                    top: 0;
                    left: 0;
                    height: 100vh;
                    z-index: 50;
                    transform: translateX(-100%);
                }
                .sidebar.active {
                    transform: translateX(0);
                }
                .sidebar-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.5);
                    z-index: 40;
                    opacity: 0;
                    visibility: hidden;
                    transition: all 0.3s ease;
                }
                .sidebar-overlay.active {
                    opacity: 1;
                    visibility: visible;
                }
            }
            /* Specific styles for forms/tables if needed */
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
            th { background-color: #f2f2f2; }
            .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
            .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
            .alert-error, .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
            .form-group { margin-bottom: 10px; }
            input[type="text"], input[type="email"], input[type="password"], input[type="number"], input[type="date"], input[type="time"], select, textarea {
                width: 100%; max-width: 300px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;
            }
            button { padding: 10px 15px; background-color: #0ea5e9; color: white; border: none; border-radius: 4px; cursor: pointer; }
            button:hover { background-color: #0284c7; }
            a { text-decoration: none; color: #007bff; }
            a:hover { text-decoration: underline; }
        </style>
    </head>
    <body class="gradient-bg dark-mode-transition">
        <div id="sidebarOverlay" class="sidebar-overlay md:hidden"></div>
        <div class="min-h-screen flex">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>
            <!-- Main Content Area -->
            <div id="mainContentArea" class="flex-1 flex flex-col main-content-area">
                <!-- Header -->
                <header class="bg-white dark:bg-slate-800 shadow-sm p-4 md:p-6 glass-effect">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <button id="mobileMenuToggle" class="md:hidden p-2 text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white focus:outline-none">
                                <i class="fas fa-bars text-xl"></i>
                            </button>
                            <button id="sidebarToggle" class="hidden md:block p-2 text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white focus:outline-none">
                                <i class="fas fa-bars text-xl"></i>
                            </button>
                            <div class="hidden md:block">
                                <h1 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white">
                                    <?php echo basename($_SERVER['PHP_SELF'], '.php'); ?> <!-- Dynamic title -->
                                </h1>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2 md:space-x-4">
                            <button id="darkModeToggle" class="p-2 text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white focus:outline-none">
                                <i class="fas fa-moon text-xl dark:hidden"></i>
                                <i class="fas fa-sun text-xl hidden dark:block"></i>
                            </button>
                            <!-- <button class="p-2 text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white">
                                <i class="fas fa-bell text-xl"></i>
                            </button> -->
                            <div class="w-8 h-8 md:w-10 md:h-10 bg-light-blue-300 rounded-full overflow-hidden">
                                <img src="https://via.placeholder.com/40x40" alt="Profile" class="w-full h-full object-cover">
                            </div>
                        </div>
                    </div>
                </header>
                <main class="flex-1 p-4 md:p-6">
    