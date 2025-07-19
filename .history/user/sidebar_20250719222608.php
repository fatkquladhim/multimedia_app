<div id="sidebar" class="w-64 bg-gray-50 border-r border-gray-200 p-6 flex flex-col sidebar">
    <!-- Logo -->
    <div class="flex items-center space-x-2 mb-8" style="text-align: center;">
        <img src="../../public/assets/imgs/rev-removebg-preview.png" style="max-width:50px;">
        <span class="text-xl font-bold text-gray-800 sidebar-logo-text">multimedia</span>
    </div>

    <!-- Navigation -->
    <nav class="space-y-2">
        <a href="../dashboard.php" class="flex items-center space-x-3 px-4 py-3 text-purple-600 bg-purple-50 rounded-lg border-l-4 border-purple-600 sidebar-nav-item">
            <i class="fas fa-th-large flex-shrink-0"></i>
            <span class="font-medium sidebar-text">Dashboard</span>
        </a>

        <a href="../portfolio/portfolio.php" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg sidebar-nav-item">
            <i class="fas fa-edit flex-shrink-0"></i>
            <span class="font-medium sidebar-text">Portfolio</span>
        </a>

        <a href="../izin malam/izin-malam.php" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg sidebar-nav-item">
            <i class="fas fa-moon flex-shrink-0"></i>
            <span class="font-medium sidebar-text">Izin Malam</span>
        </a>

        <a href="../izin nugas/izin-nugas.php" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg sidebar-nav-item">
            <i class="fas fa-laptop-code flex-shrink-0"></i>
            <span class="font-medium sidebar-text">Izin Nugas</span>
        </a>

        <a href="../tugas/riwayat_tugas.php" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg sidebar-nav-item">
            <i class="fas fa-history flex-shrink-0"></i>
            <span class="font-medium sidebar-text">Riwayat Tugas</span>
        </a>

        <!-- New: Link to Account Settings -->
        <a href="../akun/profile_settings.php" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg sidebar-nav-item">
            <i class="fas fa-cog flex-shrink-0"></i>
            <span class="font-medium sidebar-text">Pengaturan Akun</span>
        </a>

        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="../auth/logout.php" class="flex items-center space-x-3 px-4 py-3 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl sidebar-nav-item transition-colors">
                <i class="fas fa-sign-out-alt flex-shrink-0"></i>
                <span class="font-medium sidebar-text">Logout</span>
            </a>
        </div>
    </nav>
</div>