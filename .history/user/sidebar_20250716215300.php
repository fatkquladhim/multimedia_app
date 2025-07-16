
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
                </div>

                <!-- Navigation -->
                <nav class="space-y-2">
                    <a href="./dashboard.php" class="flex items-center space-x-3 px-4 py-3 text-purple-600 bg-purple-50 rounded-lg border-l-4 border-purple-600 sidebar-nav-item">
                        <i class="fas fa-th-large flex-shrink-0"></i>
                        <span class="font-medium sidebar-text">Dashboard</span>
                    </a>

                    <a href="./portfolio/portfolio.php" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg sidebar-nav-item">
                        <i class="fas fa-edit flex-shrink-0"></i>
                        <span class="font-medium sidebar-text">portfoilo</span>
                    </a>

                    <a href="./izin malam/izin-malam.php" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg sidebar-nav-item">
                        <i class="fas fa-users flex-shrink-0"></i>
                        <span class="font-medium sidebar-text">izin malam</span>
                    </a>

                    <a href="./izin nugas/izin-nugas.php" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg sidebar-nav-item">
                        <i class="fas fa-cog flex-shrink-0"></i>
                        <span class="font-medium sidebar-text">izin nugas</span>
                    </a>

                    <a href="./tugas/riwayat_tugas.php" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg sidebar-nav-item">
                        <i class="fas fa-eye flex-shrink-0"></i>
                        <span class="font-medium sidebar-text">riwayat tugas</span>
                    </a>
                </nav>
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
                                    <span class="text-white font-bold text-sm">FA</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <span class="font-medium text-gray-800">Fatkqul adhim</span>
                                    <i class="fas fa-chevron-down text-gray-600 text-sm"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>          
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

