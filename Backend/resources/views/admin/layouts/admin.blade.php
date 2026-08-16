<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') — {{ config('app.name') }}</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

    <style>
        /* ============================================
           CSS DESIGN SYSTEM — PREMIUM DARK ADMIN
           ============================================ */

        :root {
            /* Core palette */
            --bg-primary: #0a0a0f;
            --bg-secondary: #12121a;
            --bg-card: rgba(20, 20, 32, 0.7);
            --bg-card-hover: rgba(30, 30, 48, 0.8);
            --bg-glass: rgba(255, 255, 255, 0.03);
            --bg-glass-hover: rgba(255, 255, 255, 0.06);

            /* Accent colors */
            --accent-primary: #7c3aed;
            --accent-primary-light: #a78bfa;
            --accent-secondary: #06b6d4;
            --accent-success: #10b981;
            --accent-warning: #f59e0b;
            --accent-danger: #ef4444;
            --accent-info: #3b82f6;

            /* Gradients */
            --gradient-primary: linear-gradient(135deg, #7c3aed 0%, #06b6d4 100%);
            --gradient-card: linear-gradient(135deg, rgba(124, 58, 237, 0.1) 0%, rgba(6, 182, 212, 0.05) 100%);
            --gradient-success: linear-gradient(135deg, #10b981 0%, #06b6d4 100%);
            --gradient-warning: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);

            /* Text */
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --text-accent: #a78bfa;

            /* Borders */
            --border-color: rgba(255, 255, 255, 0.06);
            --border-color-hover: rgba(255, 255, 255, 0.12);

            /* Shadows */
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.3);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.4);
            --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.5);
            --shadow-glow: 0 0 40px rgba(124, 58, 237, 0.15);

            /* Spacing */
            --sidebar-width: 260px;
            --topbar-height: 64px;
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;

            /* Transitions */
            --transition-fast: 0.15s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-base: 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ============ RESET & BASE ============ */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Ambient background */
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 20% 20%, rgba(124, 58, 237, 0.08) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(6, 182, 212, 0.06) 0%, transparent 50%),
                        radial-gradient(circle at 50% 50%, rgba(16, 185, 129, 0.04) 0%, transparent 50%);
            z-index: -1;
            animation: ambientMove 20s ease-in-out infinite alternate;
        }

        @keyframes ambientMove {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(-2%, -2%) rotate(3deg); }
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: color var(--transition-fast);
        }

        /* ============ SCROLLBAR ============ */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: var(--bg-primary);
        }
        ::-webkit-scrollbar-thumb {
            background: var(--accent-primary);
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--accent-primary-light);
        }

        /* ============ LAYOUT ============ */
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* ============ SIDEBAR ============ */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--bg-secondary);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            z-index: 1000;
            transition: transform var(--transition-base);
        }

        .sidebar-brand {
            height: var(--topbar-height);
            display: flex;
            align-items: center;
            padding: 0 24px;
            border-bottom: 1px solid var(--border-color);
            gap: 12px;
        }

        .sidebar-brand .brand-icon {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-sm);
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .sidebar-brand .brand-text {
            font-size: 16px;
            font-weight: 700;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .sidebar-nav {
            flex: 1;
            padding: 16px 12px;
            overflow-y: auto;
        }

        .nav-section-title {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: var(--text-muted);
            padding: 8px 12px 8px;
            margin-top: 8px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: var(--radius-sm);
            color: var(--text-secondary);
            font-size: 14px;
            font-weight: 500;
            transition: all var(--transition-fast);
            margin-bottom: 2px;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 3px;
            height: 100%;
            background: var(--gradient-primary);
            border-radius: 0 2px 2px 0;
            transform: scaleY(0);
            transition: transform var(--transition-fast);
        }

        .nav-link:hover {
            background: var(--bg-glass-hover);
            color: var(--text-primary);
        }

        .nav-link.active {
            background: rgba(124, 58, 237, 0.12);
            color: var(--accent-primary-light);
        }

        .nav-link.active::before {
            transform: scaleY(1);
        }

        .nav-link i {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .sidebar-footer {
            padding: 16px;
            border-top: 1px solid var(--border-color);
        }

        /* ============ MAIN CONTENT ============ */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        /* ============ TOPBAR ============ */
        .topbar {
            height: var(--topbar-height);
            background: rgba(18, 18, 26, 0.8);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            position: sticky;
            top: 0;
            z-index: 900;
        }

        .topbar-title {
            font-size: 18px;
            font-weight: 600;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 12px;
            border-radius: var(--radius-sm);
            background: var(--bg-glass);
            border: 1px solid var(--border-color);
        }

        .topbar-user .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
        }

        .topbar-user .user-name {
            font-size: 13px;
            font-weight: 500;
        }

        /* ============ PAGE CONTENT ============ */
        .page-content {
            padding: 32px;
        }

        .page-header {
            margin-bottom: 32px;
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .page-header p {
            font-size: 14px;
            color: var(--text-secondary);
        }

        /* ============ CARDS ============ */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            transition: all var(--transition-base);
        }

        .card:hover {
            border-color: var(--border-color-hover);
            box-shadow: var(--shadow-md);
        }

        .card-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-header h3 {
            font-size: 16px;
            font-weight: 600;
        }

        .card-body {
            padding: 24px;
        }

        /* ============ STAT CARDS ============ */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 24px;
            position: relative;
            overflow: hidden;
            transition: all var(--transition-base);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        }

        .stat-card.purple::before { background: var(--gradient-primary); }
        .stat-card.cyan::before { background: linear-gradient(90deg, #06b6d4, #3b82f6); }
        .stat-card.green::before { background: var(--gradient-success); }
        .stat-card.amber::before { background: var(--gradient-warning); }

        .stat-card:hover {
            transform: translateY(-2px);
            border-color: var(--border-color-hover);
            box-shadow: var(--shadow-lg);
        }

        .stat-card .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }

        .stat-card.purple .stat-icon { background: rgba(124, 58, 237, 0.15); color: var(--accent-primary-light); }
        .stat-card.cyan .stat-icon { background: rgba(6, 182, 212, 0.15); color: var(--accent-secondary); }
        .stat-card.green .stat-icon { background: rgba(16, 185, 129, 0.15); color: var(--accent-success); }
        .stat-card.amber .stat-icon { background: rgba(245, 158, 11, 0.15); color: var(--accent-warning); }

        .stat-card .stat-label {
            font-size: 13px;
            color: var(--text-secondary);
            font-weight: 500;
            margin-bottom: 4px;
        }

        .stat-card .stat-value {
            font-size: 32px;
            font-weight: 800;
            line-height: 1.2;
        }

        /* ============ CHARTS GRID ============ */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }

        .charts-grid.single {
            grid-template-columns: 1fr;
        }

        .chart-container {
            position: relative;
            width: 100%;
            height: 280px;
        }

        /* ============ TABLE ============ */
        .table-wrapper {
            overflow-x: auto;
            border-radius: var(--radius-md);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead th {
            padding: 12px 16px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--text-muted);
            background: rgba(255, 255, 255, 0.02);
            border-bottom: 1px solid var(--border-color);
            white-space: nowrap;
        }

        table tbody tr {
            transition: background var(--transition-fast);
        }

        table tbody tr:hover {
            background: var(--bg-glass-hover);
        }

        table tbody td {
            padding: 12px 16px;
            font-size: 13px;
            color: var(--text-secondary);
            border-bottom: 1px solid var(--border-color);
            white-space: nowrap;
        }

        table tbody td.wrap {
            white-space: normal;
            max-width: 200px;
        }

        /* ============ BADGES ============ */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-purple { background: rgba(124, 58, 237, 0.15); color: var(--accent-primary-light); }
        .badge-cyan { background: rgba(6, 182, 212, 0.15); color: var(--accent-secondary); }
        .badge-green { background: rgba(16, 185, 129, 0.15); color: var(--accent-success); }
        .badge-amber { background: rgba(245, 158, 11, 0.15); color: var(--accent-warning); }
        .badge-blue { background: rgba(59, 130, 246, 0.15); color: var(--accent-info); }
        .badge-red { background: rgba(239, 68, 68, 0.15); color: var(--accent-danger); }

        /* ============ FORMS / FILTERS ============ */
        .filters-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-end;
            margin-bottom: 24px;
            padding: 20px 24px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .filter-group label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            padding: 8px 14px;
            background: var(--bg-glass);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-size: 13px;
            font-family: 'Inter', sans-serif;
            transition: all var(--transition-fast);
            outline: none;
            min-width: 160px;
        }

        .form-control:focus {
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.15);
        }

        .form-control::placeholder {
            color: var(--text-muted);
        }

        select.form-control {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 32px;
        }

        /* ============ BUTTONS ============ */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 8px 20px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all var(--transition-fast);
            border: none;
            outline: none;
            white-space: nowrap;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 2px 10px rgba(124, 58, 237, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(124, 58, 237, 0.4);
        }

        .btn-secondary {
            background: var(--bg-glass);
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: var(--bg-glass-hover);
            color: var(--text-primary);
            border-color: var(--border-color-hover);
        }

        .btn-danger {
            background: rgba(239, 68, 68, 0.15);
            color: var(--accent-danger);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .btn-danger:hover {
            background: rgba(239, 68, 68, 0.25);
        }

        .btn-sm {
            padding: 6px 14px;
            font-size: 12px;
        }

        /* ============ PAGINATION ============ */
        .pagination-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 24px;
            border-top: 1px solid var(--border-color);
            flex-wrap: wrap;
            gap: 12px;
        }

        .pagination-info {
            font-size: 13px;
            color: var(--text-muted);
        }

        .pagination {
            display: flex;
            gap: 4px;
            list-style: none;
        }

        .pagination li a,
        .pagination li span {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 8px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 500;
            color: var(--text-secondary);
            background: var(--bg-glass);
            border: 1px solid var(--border-color);
            transition: all var(--transition-fast);
        }

        .pagination li a:hover {
            background: var(--bg-glass-hover);
            color: var(--text-primary);
            border-color: var(--border-color-hover);
        }

        .pagination li.active span {
            background: var(--accent-primary);
            color: white;
            border-color: var(--accent-primary);
        }

        .pagination li.disabled span {
            opacity: 0.4;
            cursor: not-allowed;
        }

        /* ============ ALERTS ============ */
        .alert {
            padding: 14px 20px;
            border-radius: var(--radius-md);
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from { transform: translateY(-10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: var(--accent-success);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: var(--accent-danger);
        }

        /* ============ EMPTY STATE ============ */
        .empty-state {
            text-align: center;
            padding: 48px 24px;
        }

        .empty-state i {
            width: 48px;
            height: 48px;
            color: var(--text-muted);
            margin-bottom: 16px;
        }

        .empty-state h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .empty-state p {
            font-size: 13px;
            color: var(--text-muted);
        }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 1024px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .page-content {
                padding: 20px 16px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .filters-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-group {
                width: 100%;
            }

            .form-control {
                min-width: unset;
                width: 100%;
            }

            .topbar {
                padding: 0 16px;
            }

            .mobile-menu-btn {
                display: flex !important;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        .sidebar-toggle-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: var(--radius-sm);
            background: var(--bg-glass);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            cursor: pointer;
            transition: all var(--transition-fast);
        }

        .sidebar-toggle-btn:hover {
            background: var(--bg-glass-hover);
            border-color: var(--border-color-hover);
            color: var(--accent-primary-light);
            transform: scale(1.05);
        }

        /* Sidebar collapsed state for all screen sizes */
        .sidebar {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .main-content {
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body.sidebar-collapsed .sidebar {
            transform: translateX(-100%) !important;
        }

        body.sidebar-collapsed .main-content {
            margin-left: 0 !important;
        }

        /* Overlay for mobile */
        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 950;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        body:not(.sidebar-collapsed) .sidebar-overlay {
            opacity: 1;
            pointer-events: auto;
        }

        @media (min-width: 769px) {
            .sidebar-overlay {
                display: none !important;
            }
        }

        /* ============ FADE-IN ANIMATION ============ */
        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in-delay-1 { animation-delay: 0.1s; animation-fill-mode: both; }
        .fade-in-delay-2 { animation-delay: 0.2s; animation-fill-mode: both; }
        .fade-in-delay-3 { animation-delay: 0.3s; animation-fill-mode: both; }
        .fade-in-delay-4 { animation-delay: 0.4s; animation-fill-mode: both; }
    </style>
    @stack('styles')

    {{-- Restore sidebar state immediately to prevent flicker --}}
    <script>
        (function() {
            const saved = localStorage.getItem('admin_sidebar_collapsed');
            if (saved === 'true' || (saved === null && window.innerWidth <= 768)) {
                document.documentElement.classList.add('sidebar-collapsed-init');
            }
        })();
    </script>
</head>
<body>
    <script>
        if (document.documentElement.classList.contains('sidebar-collapsed-init')) {
            document.body.classList.add('sidebar-collapsed');
            document.documentElement.classList.remove('sidebar-collapsed-init');
        }
    </script>

    {{-- Mobile Overlay Backdrop --}}
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <div class="admin-wrapper">
        {{-- Sidebar --}}
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand" style="display:flex;align-items:center;justify-space:between;">
                <div style="display:flex;align-items:center;gap:12px;flex:1;">
                    <div class="brand-icon">
                        <i data-lucide="sparkles" style="width:20px;height:20px;color:white;"></i>
                    </div>
                    <span class="brand-text">Future Self</span>
                </div>
                <button type="button" class="sidebar-toggle-btn" onclick="toggleSidebar()" style="width:30px;height:30px;padding:0;border:none;background:transparent;" title="Close Sidebar">
                    <i data-lucide="x" style="width:18px;height:18px;color:var(--text-muted);"></i>
                </button>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section-title">Main</div>
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i data-lucide="layout-dashboard"></i>
                    <span>Dashboard</span>
                </a>

                <div class="nav-section-title">Analytics</div>
                <a href="{{ route('admin.activity-logs') }}" class="nav-link {{ request()->routeIs('admin.activity-logs') ? 'active' : '' }}">
                    <i data-lucide="activity"></i>
                    <span>Activity Logs</span>
                </a>
                <a href="{{ route('admin.daily-active-users') }}" class="nav-link {{ request()->routeIs('admin.daily-active-users') ? 'active' : '' }}">
                    <i data-lucide="users"></i>
                    <span>Daily Active Users</span>
                </a>

                <div class="nav-section-title">Engagement</div>
                <a href="{{ route('admin.feedbacks') }}" class="nav-link {{ request()->routeIs('admin.feedbacks') ? 'active' : '' }}">
                    <i data-lucide="message-square-heart"></i>
                    <span>Feedbacks</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="nav-link" style="width:100%;border:none;background:none;cursor:pointer;color:var(--text-secondary);font-family:'Inter',sans-serif;">
                        <i data-lucide="log-out"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main Content --}}
        <main class="main-content">
            {{-- Topbar --}}
            <header class="topbar">
                <div style="display:flex;align-items:center;gap:16px;">
                    <button type="button" class="sidebar-toggle-btn" id="sidebarHamburgerBtn" onclick="toggleSidebar()" title="Toggle Sidebar">
                        <i data-lucide="menu" id="sidebarHamburgerIcon" style="width:20px;height:20px;"></i>
                    </button>
                    <h2 class="topbar-title">@yield('page-title', 'Dashboard')</h2>
                </div>
                <div class="topbar-actions">
                    <div class="topbar-user">
                        <div class="user-avatar">
                            {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                        </div>
                        <span class="user-name">{{ auth()->user()->name ?? 'Admin' }}</span>
                    </div>
                </div>
            </header>

            {{-- Page Content --}}
            <div class="page-content">
                {{-- Flash Messages --}}
                @if(session('success'))
                    <div class="alert alert-success">
                        <i data-lucide="check-circle" style="width:18px;height:18px;flex-shrink:0;"></i>
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-error">
                        <i data-lucide="alert-circle" style="width:18px;height:18px;flex-shrink:0;"></i>
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    {{-- Chart.js Global Config & Sidebar Toggle JS --}}
    <script>
        // Set Chart.js defaults for dark theme
        Chart.defaults.color = '#94a3b8';
        Chart.defaults.borderColor = 'rgba(255,255,255,0.06)';
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.font.size = 12;
        Chart.defaults.plugins.legend.labels.usePointStyle = true;
        Chart.defaults.plugins.legend.labels.padding = 20;
        Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(10,10,15,0.9)';
        Chart.defaults.plugins.tooltip.borderColor = 'rgba(255,255,255,0.1)';
        Chart.defaults.plugins.tooltip.borderWidth = 1;
        Chart.defaults.plugins.tooltip.cornerRadius = 8;
        Chart.defaults.plugins.tooltip.padding = 12;
        Chart.defaults.plugins.tooltip.titleFont = { weight: '600' };

        // Sidebar Toggle Function
        function toggleSidebar() {
            document.body.classList.toggle('sidebar-collapsed');
            const isCollapsed = document.body.classList.contains('sidebar-collapsed');
            localStorage.setItem('admin_sidebar_collapsed', isCollapsed ? 'true' : 'false');
            
            // Trigger window resize event so Chart.js recalculates canvas width smoothly
            window.dispatchEvent(new Event('resize'));
        }

        // Initialize Lucide Icons
        lucide.createIcons();
    </script>

    @stack('scripts')
</body>
</html>
