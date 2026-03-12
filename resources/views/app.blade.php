<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracker Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #64748b;
            --accent: #f43f5e;
            --bg-body: #f8fafc;
            --sidebar-bg: #1e1b4b;
            --card-glass: rgba(255, 255, 255, 0.7);
            --card-border: rgba(226, 232, 240, 0.8);
        }

        body {
            background-color: var(--bg-body);
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1e293b;
            overflow-x: hidden;
        }

        .tracker-main-content .sidebar {
            min-height: 100vh;
            background: var(--sidebar-bg);
            color: white;
            position: fixed;
            width: 260px;
            top: 0;
            left: 0;
            padding: 2rem 1.5rem;
            z-index: 1000;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .tracker-main-content .sidebar .nav-link {
            color: #94a3b8;
            padding: 0.875rem 1.25rem;
            border-radius: 12px;
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s ease;
        }

        .tracker-main-content .sidebar .nav-link i {
            font-size: 1.25rem;
        }

        .tracker-main-content .sidebar .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.05);
        }

        .tracker-main-content .sidebar .nav-link.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 8px 16px -4px rgba(99, 102, 241, 0.4);
        }

        .tracker-main-content .main-content {
            margin-left: 260px;
            padding: 2.5rem;
            transition: margin-left 0.3s ease;
        }

        .tracker-main-content .card {
            background: var(--card-glass);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            box-shadow: 0 4px 20px -1px rgba(0, 0, 0, 0.03);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .tracker-main-content .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 30px -4px rgba(0, 0, 0, 0.08);
        }

        .tracker-main-content .counter-box {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            border: 1px solid var(--card-border);
            position: relative;
            overflow: hidden;
        }

        .tracker-main-content .counter-box h3 {
            font-size: 2.5rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 0.25rem;
            letter-spacing: -1px;
        }

        .tracker-main-content .counter-box p {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--secondary);
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .tracker-main-content .chart-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1.5rem;
        }

        .tracker-main-content .btn-primary {
            background: var(--primary);
            border: none;
            padding: 0.625rem 1.25rem;
            border-radius: 12px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
        }

        .tracker-main-content .table thead th {
            background: #f1f5f9;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            padding: 1.25rem 1.5rem;
            border: none;
        }

        .tracker-main-content .table tbody td {
            padding: 1.25rem 1.5rem;
            vertical-align: middle;
            color: #334155;
            border-bottom: 1px solid #f1f5f9;
        }

        @media (max-width: 992px) {
            .tracker-main-content .sidebar {
                transform: translateX(-100%);
            }
            .tracker-main-content .main-content {
                margin-left: 0;
                padding: 1.5rem;
            }
            .tracker-main-content .sidebar.show {
                transform: translateX(0);
            }
        }

        /* Mobile Header Styles */
        .mobile-header {
            display: none;
            background: var(--sidebar-bg);
            color: white;
            padding: 1rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(4px);
            z-index: 999;
        }

        .sidebar-overlay.active {
            display: block;
        }
    </style>
    
    @stack('tracker-styles')
</head>
<body>
    <div class="tracker-main-content">
        <!-- Mobile Header -->
        <div class="mobile-header">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-graph-up-arrow text-primary fs-4"></i>
                <span class="fw-700 h5 mb-0">Tracker</span>
            </div>
            <button class="btn btn-outline-light border-0 px-2" id="sidebarToggle">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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