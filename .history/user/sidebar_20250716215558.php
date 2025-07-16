<div id="sidebar" class="w-64 bg-gray-50 border-r border-gray-200 p-6 flex flex-col sidebar">
    <!-- Logo -->
    <div class="flex items-center space-x-2 mb-8" style="text-align: center;">
           <img src="../../public/assets/imgs/rev-removebg-preview.png" style="max-width:50px;">
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
            <i class="fas fa-users flex-shrink-0"></i>
            <span class="font-medium sidebar-text">Izin Malam</span>
        </a>

        <a href="../izin nugas/izin-nugas.php" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg sidebar-nav-item">
            <i class="fas fa-cog flex-shrink-0"></i>
            <span class="font-medium sidebar-text">Izin Nugas</span>
        </a>

        <a href="../tugas/riwayat_tugas.php" class="flex items-center space-x-3 px-4 py-3 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg sidebar-nav-item">
            <i class="fas fa-eye flex-shrink-0"></i>
            <span class="font-medium sidebar-text">Riwayat Tugas</span>
        </a>
    </nav>
</div>
<body>
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
    </body>