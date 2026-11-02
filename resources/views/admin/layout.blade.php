<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') | Pial AI Assistant</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .admin-nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 8px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        .admin-nav-item:hover, .admin-nav-item.active {
            background: var(--bg-elevated);
            color: var(--accent-primary);
        }
        .admin-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-default);
            border-radius: 16px;
            padding: 20px;
            box-shadow: var(--shadow-sm);
        }
        .admin-stat-num {
            font-family: var(--font-heading);
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-top: 6px;
        }
    </style>
</head>
<body data-theme="dark" style="min-height: 100vh; display: flex; flex-direction: column; background: var(--bg-canvas);">
    <!-- Top Admin Bar -->
    <header style="height: 64px; border-bottom: 1px solid var(--border-default); background: var(--bg-surface); display: flex; align-items: center; justify-content: space-between; padding: 0 24px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 34px; height: 34px; border-radius: 8px; background: var(--accent-gradient); color: white; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="shield" style="width: 18px; height: 18px;"></i>
            </div>
            <span style="font-family: var(--font-heading); font-weight: 700; font-size: 1.05rem;">Admin Control Panel</span>
        </div>

        <div style="display: flex; align-items: center; gap: 14px;">
            <a href="{{ route('chat.index') }}" class="sidebar-action-btn" style="width: auto; padding: 6px 14px; font-size: 0.82rem; text-decoration: none;">
                <i data-lucide="message-square" style="width: 14px; height: 14px;"></i>
                <span>Back to Chat</span>
            </a>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-btn" title="Sign Out">
                    <i data-lucide="log-out" style="width: 18px; height: 18px;"></i>
                </button>
            </form>
        </div>
    </header>

    <div style="display: flex; flex: 1;">
        <!-- Admin Sidebar Nav -->
        <aside style="width: 240px; border-right: 1px solid var(--border-default); background: var(--bg-surface); padding: 20px 12px; display: flex; flex-direction: column; gap: 6px;">
            <a href="{{ route('admin.dashboard') }}" class="admin-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i data-lucide="layout-dashboard" style="width: 18px; height: 18px;"></i>
                <span>Overview</span>
            </a>
            <a href="{{ route('admin.users') }}" class="admin-nav-item {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                <i data-lucide="users" style="width: 18px; height: 18px;"></i>
                <span>Users</span>
            </a>
            <a href="{{ route('admin.settings') }}" class="admin-nav-item {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                <i data-lucide="sliders" style="width: 18px; height: 18px;"></i>
                <span>AI & Settings</span>
            </a>
        </aside>

        <!-- Main Admin Content -->
        <main style="flex: 1; padding: 28px; overflow-y: auto;">
            @if(session('success'))
                <div style="padding: 12px 16px; border-radius: 10px; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: var(--status-success); font-size: 0.88rem; margin-bottom: 20px;">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div style="padding: 12px 16px; border-radius: 10px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: var(--status-danger); font-size: 0.88rem; margin-bottom: 20px;">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
