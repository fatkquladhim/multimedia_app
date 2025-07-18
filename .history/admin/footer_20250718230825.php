   
                </main>
            </div>
        </div>
        <script>
               
    // Dark Mode Toggle
    const darkModeToggle = document.getElementById('darkModeToggle');
    const html = document.documentElement;

    // Check for saved theme preference or default to light mode
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        html.classList.add('dark');
    }

    darkModeToggle.addEventListener('click', () => {
        html.classList.toggle('dark');
        localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
    });

    // Mobile Menu Toggle
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const closeSidebar = document.getElementById('closeSidebar');

    function toggleMobileSidebar() {
        sidebar.classList.toggle('active');
        sidebarOverlay.classList.toggle('active');
    }

    mobileMenuToggle.addEventListener('click', toggleMobileSidebar);
    closeSidebar.addEventListener('click', toggleMobileSidebar);
    sidebarOverlay.addEventListener('click', toggleMobileSidebar);

    // Desktop Sidebar Toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mainContentArea = document.getElementById('mainContentArea');
    let isSidebarOpen = true;

    sidebarToggle.addEventListener('click', () => {
        if (isSidebarOpen) {
            // Close sidebar
            sidebar.classList.remove('w-64');
            sidebar.classList.add('w-20');

            // Hide text content
            sidebar.querySelectorAll('span.font-medium, h3')
                .forEach(el => el.classList.add('hidden'));

            // Show only icons
            sidebar.querySelectorAll('i')
                .forEach(el => el.classList.add('mx-auto'));

            sidebarToggle.querySelector('i').classList.replace('fa-bars', 'fa-arrow-right');
        } else {
            // Open sidebar
            sidebar.classList.remove('w-20');
            sidebar.classList.add('w-64');

            // Show text content
            sidebar.querySelectorAll('span.font-medium, h3')
                .forEach(el => el.classList.remove('hidden'));

            // Reset icon alignment
            sidebar.querySelectorAll('i')
                .forEach(el => el.classList.remove('mx-auto'));

            sidebarToggle.querySelector('i').classList.replace('fa-arrow-right', 'fa-bars');
        }
        isSidebarOpen = !isSidebarOpen;
    });

    // Responsive behavior
    function handleResize() {
        if (window.innerWidth < 768) {
            // Mobile: Hide desktop sidebar toggle, show mobile menu
            sidebarToggle.classList.add('hidden');
            mobileMenuToggle.classList.remove('hidden');

            // Reset sidebar state for mobile
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        } else {
            // Desktop: Show desktop sidebar toggle, hide mobile menu
            sidebarToggle.classList.remove('hidden');
            mobileMenuToggle.classList.add('hidden');

            // Ensure sidebar is properly positioned for desktop
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        }
    }

    // Initial check
    handleResize();

    // Listen for resize events
    window.addEventListener('resize', handleResize);

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Add loading animation to buttons
    document.querySelectorAll('button, a').forEach(element => {
        element.addEventListener('click', function() {
            if (!this.classList.contains('no-loading')) {
                this.style.opacity = '0.8';
                setTimeout(() => {
                    this.style.opacity = '1';
                }, 200);
            }
        });
    });
    
        </script>
    </body>
    </html>
    