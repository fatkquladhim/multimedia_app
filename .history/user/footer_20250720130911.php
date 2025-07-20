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
