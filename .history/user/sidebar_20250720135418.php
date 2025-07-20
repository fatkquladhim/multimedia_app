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

     <!-- Navigation -->
     <nav class="space-y-2 flex-1">
         <a href="../dashboard.php" class="flex items-center px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-light-blue-600 dark:hover:text-light-blue-400 hover:bg-light-blue-50 dark:hover:bg-slate-700 rounded-lg sidebar-nav-item hover-scale">
             <i class="fas fa-th-large flex-shrink-0"></i>
             <span class="font-medium sidebar-text">Dashboard</span>
         </a>

         <a href="../portfolio/portfolio.php" class="flex items-center space-x-3 px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-xl sidebar-nav-item transition-colors">
             <i class="fas fa-briefcase flex-shrink-0"></i>
             <span class="font-medium sidebar-text">Portfolio</span>
         </a>

         <a href="../izin malam/izin-malam.php" class="flex items-center space-x-3 px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-xl sidebar-nav-item transition-colors">
             <i class="fas fa-moon flex-shrink-0"></i>
             <span class="font-medium sidebar-text">Izin Malam</span>
         </a>

         <a href="../izin nugas/izin-nugas.php" class="flex items-center space-x-3 px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-xl sidebar-nav-item transition-colors">
             <i class="fas fa-laptop-code flex-shrink-0"></i>
             <span class="font-medium sidebar-text">Izin Nugas</span>
         </a>

         <a href="../tugas/riwayat_tugas.php" class="flex items-center space-x-3 px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-xl sidebar-nav-item transition-colors">
             <i class="fas fa-history flex-shrink-0"></i>
             <span class="font-medium sidebar-text">Riwayat Tugas</span>
         </a>
         <a href="../akun/profile_settings.php" class="flex items-center space-x-3 px-4 py-3 text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-600 hover:bg-gray-100 rounded-lg sidebar-nav-item">
             <i class="fas fa-cog flex-shrink-0"></i>
             <span class="font-medium sidebar-text">Pengaturan Akun</span>
         </a>

         <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
             <a href="../../auth/logout.php" class="flex items-center space-x-3 px-4 py-3 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl sidebar-nav-item transition-colors">
                 <i class="fas fa-sign-out-alt flex-shrink-0"></i>
                 <span class="font-medium sidebar-text">Logout</span>
             </a>
         </div>
     </nav>
 </div>