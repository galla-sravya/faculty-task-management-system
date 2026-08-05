<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'PSG iTech Activity Monitoring System') }}</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- PSG Theme CSS -->
    <link rel="stylesheet" href="{{ asset('css/psg-theme.css') }}">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Sidebar + Top Header -->
    @include('layouts.navigation')

    <!-- Main Content Wrapper -->
    <div class="psg-content-wrapper" id="psgContentWrapper">
        <main class="px-3 px-md-4 py-4" style="max-width:1400px;">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                    <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                    <i class="bi bi-exclamation-octagon me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Sidebar Toggle Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('psgSidebar');
        const content = document.getElementById('psgContentWrapper');
        const topbar  = document.getElementById('psgTopbar');
        const toggle  = document.getElementById('sidebarToggle');
        const overlay = document.getElementById('sidebarOverlay');
        if (!sidebar || !toggle) return;

        const STORAGE_KEY = 'psg_sidebar_collapsed';
        const isMobile = () => window.innerWidth < 992;

        // Restore saved state on desktop
        if (!isMobile() && localStorage.getItem(STORAGE_KEY) === '1') {
            sidebar.classList.add('collapsed');
            content.classList.add('sidebar-collapsed');
            topbar.classList.add('sidebar-collapsed');
        }

        function toggleSidebar() {
            if (isMobile()) {
                sidebar.classList.toggle('mobile-open');
                overlay.classList.toggle('active');
                document.body.classList.toggle('sidebar-mobile-open');
            } else {
                const collapsed = sidebar.classList.toggle('collapsed');
                content.classList.toggle('sidebar-collapsed', collapsed);
                topbar.classList.toggle('sidebar-collapsed', collapsed);
                localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
            }
        }

        toggle.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('active');
            document.body.classList.remove('sidebar-mobile-open');
        });

        // Handle resize
        let prevMobile = isMobile();
        window.addEventListener('resize', function() {
            const nowMobile = isMobile();
            if (prevMobile !== nowMobile) {
                sidebar.classList.remove('mobile-open');
                overlay.classList.remove('active');
                document.body.classList.remove('sidebar-mobile-open');
                if (nowMobile) {
                    sidebar.classList.remove('collapsed');
                    content.classList.remove('sidebar-collapsed');
                    topbar.classList.remove('sidebar-collapsed');
                } else if (localStorage.getItem(STORAGE_KEY) === '1') {
                    sidebar.classList.add('collapsed');
                    content.classList.add('sidebar-collapsed');
                    topbar.classList.add('sidebar-collapsed');
                }
                prevMobile = nowMobile;
            }
        });
    });
    </script>

    @yield('scripts')
</body>
</html>
