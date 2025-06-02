<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracker Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f5f6fa;
            font-family: 'Inter', sans-serif;
        }
        .tracker-main-content .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1e3a8a, #3b82f6);
            color: white;
            position: fixed;
            width: 250px;
            top: 0;
            left: 0;
            padding: 1.5rem;
            transition: transform 0.3s ease;
        }
        .tracker-main-content .sidebar .nav-link {
            color: #d1d5db;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .tracker-main-content .sidebar .nav-link.active {
            background-color: #0d6efd;
            color: white;
        }
        .tracker-main-content .main-content {
            margin-left: 250px;
            padding: 2rem;
        }
        .tracker-main-content .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .tracker-main-content .card:hover {
            transform: translateY(-4px);
        }
        .tracker-main-content .counter-box {
            background: linear-gradient(135deg, #ffffff, #f8fafc);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.2s;
        }
        .tracker-main-content .counter-box h3 {
            font-size: 2.25rem;
            font-weight: 700;
            color: #1e3a8a;
            margin-bottom: 0.5rem;
        }
        .tracker-main-content .counter-box p {
            font-size: 0.9rem;
            color: #6b7280;
            margin: 0;
        }
        .tracker-main-content .chart-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e3a8a;
            margin-bottom: 1rem;
        }
        .tracker-main-content .offcanvas-header {
            background: #1e3a8a;
            color: white;
        }
        
        @media (max-width: 768px) {
            .tracker-main-content .chart-container {
                height: 250px
            }
        }
        .tracker-main-content .card-body {
            padding: 1.5rem;
        }
        @media (max-width: 992px) {
            .tracker-main-content .sidebar {
                transform: translateX(-100%);
            }
            .tracker-main-content .tracker-main-content {
                margin-left: 0;
            }
            .tracker-main-content .sidebar.show {
                transform: translateX(0);
            }
        }
    </style>
    
    @stack('tracker-styles')
</head>
<body>
    <div class="tracker-main-content">
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
    @stack('tracker-scripts')
</body>
</html>