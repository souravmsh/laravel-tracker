<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('tracker.title', 'TRACKER // ANALYTICS') }}</title>

    <!-- Local Assets -->
    <link rel="stylesheet" href="{{ asset('vendor/tracker/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/tracker/css/bootstrap-icons.min.css') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-master: #0b0e14;
            --bg-panel: #151921;
            --bg-card: #1c2128;
            --border-primary: #30363d;
            --accent-cyan: #00f2ff;
            --accent-orange: #f0883e;
            --text-main: #c9d1d9;
            --text-muted: #8b949e;
            --text-placeholder: rgba(139, 148, 158, 0.45);
            --sidebar-width: 200px;
            --header-height: 48px;
        }

        .text-main { color: var(--text-main) !important; }
        .text-muted { color: var(--text-muted) !important; }
        .text-info { color: var(--accent-cyan) !important; }
        .text-accent-cyan { color: var(--accent-cyan) !important; }
        .bg-master { background-color: var(--bg-master) !important; }
        .bg-panel { background-color: var(--bg-panel) !important; }
        .bg-card { background-color: var(--bg-card) !important; }

        body {
            background-color: var(--bg-master);
            font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
            color: var(--text-main);
            overflow-x: hidden;
            font-size: 0.85rem;
            letter-spacing: -0.01em;
        }

        code,
        .mono {
            font-family: 'JetBrains Mono', monospace;
        }

        .tracker-main-content .sidebar {
            min-height: 100vh;
            background: var(--bg-panel);
            border-right: 1px solid var(--border-primary);
            color: var(--text-main);
            position: fixed;
            width: var(--sidebar-width);
            top: 0;
            left: 0;
            padding: 1rem 0.75rem;
            z-index: 1000;
            transition: transform 0.2s ease;
        }

        .tracker-main-content .sidebar-header {
            padding: 0 0.5rem 1.5rem;
            border-bottom: 1px solid var(--border-primary);
            margin-bottom: 1.5rem;
        }

        .tracker-main-content .sidebar-header h2 {
            font-size: 0.9rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1rem;
            color: var(--accent-cyan);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tracker-main-content .sidebar .nav-link {
            color: var(--text-muted);
            padding: 0.5rem 0.75rem;
            border-radius: 4px;
            font-weight: 600;
            margin-bottom: 0.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05rem;
            transition: all 0.15s ease;
            position: relative;
        }

        .tracker-main-content .sidebar .nav-link:hover {
            color: var(--text-main);
            background: rgba(255, 255, 255, 0.03);
        }

        .tracker-main-content .sidebar .nav-link.active {
            background: rgba(0, 242, 255, 0.08);
            color: var(--accent-cyan);
            border-left: 2px solid var(--accent-cyan);
        }

        .tracker-main-content .main-content {
            margin-left: var(--sidebar-width);
            padding: 1.25rem;
            transition: margin-left 0.2s ease;
        }

        .tracker-main-content .card {
            background: var(--bg-card);
            border: 1px solid var(--border-primary);
            border-radius: 4px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            margin-bottom: 1.25rem;
        }

        .tracker-main-content .card-header {
            background: rgba(255, 255, 255, 0.02);
            border-bottom: 1px solid var(--border-primary);
            padding: 0.75rem 1rem;
        }

        .tracker-main-content .counter-box {
            background: var(--bg-card);
            border: 1px solid var(--border-primary);
            border-radius: 4px;
            padding: 1rem;
            position: relative;
            overflow: hidden;
        }

        .tracker-main-content .counter-box h3 {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent-cyan);
            margin-bottom: 0.25rem;
        }

        .tracker-main-content .counter-box p {
            font-size: 0.65rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05rem;
            margin: 0;
        }

        .tracker-main-content .chart-title {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-main);
            letter-spacing: 0.05rem;
        }

        .tracker-main-content .btn-primary {
            background: var(--accent-cyan);
            border: none;
            color: #000;
            padding: 0.4rem 0.8rem;
            border-radius: 2px;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05rem;
        }

        .tracker-main-content .btn-primary:hover {
            background: #00d4df;
            color: #000;
        }

        .tracker-main-content .table thead th {
            background: rgba(255, 255, 255, 0.03);
            color: var(--text-muted);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.65rem;
            letter-spacing: 0.05rem;
            padding: 0.6rem 0.75rem;
            border-bottom: 1px solid var(--border-primary);
        }

        .tracker-main-content .table tbody td {
            padding: 0.6rem 0.75rem;
            font-size: 0.75rem;
            color: var(--text-main);
            border-bottom: 1px solid var(--border-primary);
            background: transparent;
        }

        .tracker-main-content .table-hover tbody tr:hover {
            background: rgba(255, 255, 255, 0.02);
            color: var(--text-main);
        }

        @media (max-width: 992px) {
            .tracker-main-content .sidebar {
                transform: translateX(-100%);
            }

            .tracker-main-content .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .tracker-main-content .sidebar.show {
                transform: translateX(0);
            }
        }

        .mobile-header {
            display: none;
            background: var(--bg-panel);
            border-bottom: 1px solid var(--border-primary);
            padding: 0.5rem 1rem;
            position: sticky;
            top: 0;
            z-index: 1100;
        }

        @media (max-width: 992px) {
            .mobile-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
            z-index: 999;
        }

        .sidebar-overlay.active {
            display: block;
        }

        /* GLOBAL PLACEHOLDER STYLE */
        .tracker-main-content ::placeholder {
            color: var(--text-placeholder) !important;
            opacity: 1;
        }
        
        .tracker-main-content :-ms-input-placeholder {
            color: var(--text-placeholder) !important;
        }
        
        .tracker-main-content ::-ms-input-placeholder {
            color: var(--text-placeholder) !important;
        }

        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-master);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--border-primary);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--text-muted);
        }
        /* PAGINATION REDESIGN */
        .tracker-main-content .pagination {
            gap: 6px;
        }

        .tracker-main-content .page-item:first-child .page-link,
        .tracker-main-content .page-item:last-child .page-link {
            border-radius: 4px;
        }

        .tracker-main-content .page-link {
            background-color: var(--bg-panel);
            border: 1px solid var(--border-primary);
            color: var(--text-muted);
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 6px 12px;
            transition: all 0.2s ease;
            border-radius: 4px;
        }

        .tracker-main-content .page-link:hover {
            background-color: rgba(0, 242, 255, 0.05);
            border-color: var(--accent-cyan);
            color: var(--accent-cyan);
        }

        .tracker-main-content .page-item.active .page-link {
            background-color: var(--accent-cyan);
            border-color: var(--accent-cyan);
            color: #000;
        }

        .tracker-main-content .page-item.disabled .page-link {
            background-color: var(--bg-master);
            border-color: var(--border-primary);
            color: #444;
            opacity: 0.5;
        }
    </style>

    @stack('tracker-styles')
</head>

<body>
    <div class="tracker-main-content">
        <!-- Mobile Header -->
        <div class="mobile-header">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-robot text-accent-cyan fs-4"></i>
                <span class="fw-700 h6 mb-0 mono" style="color: var(--accent-cyan)">TRACKER_OS</span>
            </div>
            <button class="btn border-0 text-main" id="sidebarToggle">
                <i class="bi bi-list fs-3"></i>
            </button>
        </div>

        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Sidebar -->
        @include('tracker::common.sidebar')

        <!-- Offcanvas Filter -->
        @include('tracker::common.filter')

        <!-- Main Content -->
        <div class="main-content">
            @yield('tracker-content')
        </div>

    </div>

    <!-- Local Scripts -->
    <script src="{{ asset('vendor/tracker/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const toggle = document.getElementById('sidebarToggle');
            const overlay = document.getElementById('sidebarOverlay');

            if (toggle && sidebar && overlay) {
                toggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    overlay.classList.toggle('active');
                });

                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('active');
                });
            }
        });
    </script>
    @stack('tracker-scripts')
</body>

</html>