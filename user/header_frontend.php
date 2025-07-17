 <header class="bg-white border-b border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <!-- Sidebar Toggle Button -->
                            <button id="sidebarToggle" class="p-2 text-gray-600 hover:text-gray-800 focus:outline-none mr-4">
                                <i class="fas fa-bars text-xl"></i>
                            </button>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
                                <p class="text-gray-600"><?php echo date('l, d F Y'); ?></p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <div class="flex items-center space-x-2">
                                <div class="w-10 h-10 bg-white-600 rounded-full flex items-center justify-center overflow-hidden">
                                    <img src="../../uploads/profiles/<?php echo $profile_photo; ?>" alt="Profile Photo" class="w-full h-full object-cover rounded-full">
                                </div>
                                <div class="flex items-center space-x-1">
                                    <a href="../profile/profile_view.php">
                                        <span class="font-medium text-gray-800"><?php echo $profile_name; ?></span>
                                        <i class="fas fa-chevron-down text-gray-600 text-sm"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>