
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multimedia Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
    <style>
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

<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
    <div class="min-h-screen">
        <div class="flex">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>
            <!-- Main Content -->
            <div class="flex-1 flex flex-col">
                <!-- Header -->
                <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <!-- Sidebar Toggle Button -->
                            <button id="sidebarToggle" class="p-2 text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 focus:outline-none transition-colors">
                                <i class="fas fa-bars text-xl"></i>
                            </button>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Dashboard</h1>
                                <p class="text-gray-600 dark:text-gray-400"><?php echo date('l, d F Y'); ?></p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <!-- Dark Mode Toggle -->
                            <button id="darkModeToggle" class="p-2 text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 focus:outline-none transition-colors">
                                <i class="fas fa-sun dark:hidden text-xl"></i>
                                <i class="fas fa-moon hidden dark:block text-xl"></i>
                            </button>

                            <!-- Profile -->
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900 rounded-full flex items-center justify-center overflow-hidden">
                                    <img src="../../uploads/profiles/<?php echo $profile_photo; ?>" alt="Profile Photo" class="w-full h-full object-cover rounded-full">
                                </div>
                                <div class="flex items-center space-x-2">
                                    <a href="../../profile/profile_view.php" class="flex items-center space-x-1 hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                        <span class="font-medium text-gray-800 dark:text-white"><?php echo $profile_name; ?></span>
                                        <i class="fas fa-chevron-down text-gray-600 dark:text-gray-400 text-sm"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>