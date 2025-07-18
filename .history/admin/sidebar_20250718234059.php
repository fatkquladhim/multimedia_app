    <?php
    // includes/sidebar.php
    // Pastikan session sudah dimulai di file yang meng-include ini
    // if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    //     header('Location: ../auth/login.php');
    //     exit;
    // }
    ?>
    <div id="sidebar" class="w-64 bg-white dark:bg-slate-800 shadow-xl flex-shrink-0 sidebar transition-all duration-300 overflow-hidden glass-effect">
        <div class="p-4 flex items-center justify-between border-b border-light-blue-100 dark:border-slate-700">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-light-blue-500 rounded-lg flex items-center justify-center">
                    <i class="fas fa-home text-white text-xl"></i>
                </div>
                <h1 class="text-xl font-bold text-gray-800 dark:text-white">Multimedia</h1>
            </div>
            <button id="closeSidebar" class="md:hidden p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <nav class="mt-6 px-4">
            <div class="space-y-2">
                <a href="../dashboard.php" class="flex items-center px-4 py-3 text-light-blue-600 bg-light-blue-50 dark:bg-light-blue-900 dark:text-light-blue-300 rounded-lg sidebar-nav-item hover-scale">
                    <i class="fas fa-tachometer-alt w-5 h-5 mr-3"></i>
                    <span class="font-medium">Dashboard</span>
                </a>

                <a href="../anggota/anggota.php" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-light-blue-600 dark:hover:text-light-blue-400 hover:bg-light-blue-50 dark:hover:bg-slate-700 rounded-lg sidebar-nav-item hover-scale">
                    <i class="fas fa-users w-5 h-5 mr-3"></i>
                    <span class="font-medium">Manajemen Anggota</span>
                </a>

                <a href="../daftar alat/daftar-alat.php" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-light-blue-600 dark:hover:text-light-blue-400 hover:bg-light-blue-50 dark:hover:bg-slate-700 rounded-lg sidebar-nav-item hover-scale">
                    <i class="fas fa-tools w-5 h-5 mr-3"></i>
                    <span class="font-medium">Manajemen Alat</span>
                </a>

                <a href="../peminjaman/peminjaman-barang.php" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-light-blue-600 dark:hover:text-light-blue-400 hover:bg-light-blue-50 dark:hover:bg-slate-700 rounded-lg sidebar-nav-item hover-scale">
                    <i class="fas fa-handshake w-5 h-5 mr-3"></i>
                    <span class="font-medium">Peminjaman Barang</span>
                </a>

                <a href="../penyewaan/penyewaan-barang.php" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-light-blue-600 dark:hover:text-light-blue-400 hover:bg-light-blue-50 dark:hover:bg-slate-700 rounded-lg sidebar-nav-item hover-scale">
                    <i class="fas fa-cash-register w-5 h-5 mr-3"></i>
                    <span class="font-medium">Penyewaan Barang</span>
                </a>

                <a href="../legalisasi laptop/legalisasi_list.php" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-light-blue-600 dark:hover:text-light-blue-400 hover:bg-light-blue-50 dark:hover:bg-slate-700 rounded-lg sidebar-nav-item hover-scale">
                    <i class="fas fa-laptop w-5 h-5 mr-3"></i>
                    <span class="font-medium">Legalisasi Laptop</span>
                </a>
            </div>

            <div class="mt-8 mb-4">
                <h3 class="px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Tugas & Izin
                </h3>
            </div>

            <div class="space-y-2">
                <a href="../beri tugas/beri_tugas_form.php" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-light-blue-600 dark:hover:text-light-blue-400 hover:bg-light-blue-50 dark:hover:bg-slate-700 rounded-lg sidebar-nav-item hover-scale">
                    <i class="fas fa-clipboard-list w-5 h-5 mr-3"></i>
                    <span class="font-medium">Beri Tugas</span>
                </a>

                <a href="../beri tugas/tugas_selesai_riwayat.php" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-light-blue-600 dark:hover:text-light-blue-400 hover:bg-light-blue-50 dark:hover:bg-slate-700 rounded-lg sidebar-nav-item hover-scale">
                    <i class="fas fa-check-double w-5 h-5 mr-3"></i>
                    <span class="font-medium">Riwayat Tugas</span>
                </a>

                <a href="../izin_malam/izin-malam.php" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-light-blue-600 dark:hover:text-light-blue-400 hover:bg-light-blue-50 dark:hover:bg-slate-700 rounded-lg sidebar-nav-item hover-scale">
                    <i class="fas fa-moon w-5 h-5 mr-3"></i>
                    <span class="font-medium">Izin Malam</span>
                </a>

                <a href="../izin_nugas/izin-nugas.php" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-light-blue-600 dark:hover:text-light-blue-400 hover:bg-light-blue-50 dark:hover:bg-slate-700 rounded-lg sidebar-nav-item hover-scale">
                    <i class="fas fa-book-open w-5 h-5 mr-3"></i>
                    <span class="font-medium">Izin Nugas</span>
                </a>
            </div>

            <div class="space-y-2">
                <a href="../keuangan/manage_uang.php" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-light-blue-600 dark:hover:text-light-blue-400 hover:bg-light-blue-50 dark:hover:bg-slate-700 rounded-lg sidebar-nav-item hover-scale">
                    <i class="fas fa-money-bill-alt w-5 h-5 mr-3"></i>
                    <span class="font-medium">Keuangan</span>
                </a>
            </div>

            <div class="mt-8 mb-4">
                <h3 class="px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Akun
                </h3>
            </div>

            <div class="space-y-2">
                <a href="../auth/logout.php" class="flex items-center px-4 py-3 text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900 rounded-lg sidebar-nav-item hover-scale">
                    <i class="fas fa-sign-out-alt w-5 h-5 mr-3"></i>
                    <span class="font-medium">Logout</span>
                </a>
            </div>
        </nav>
    </div>
    