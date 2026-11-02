<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installer | Pial AI Assistant</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body data-theme="dark" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; background: var(--bg-canvas);">
    <div style="width: 100%; max-width: 580px; background: var(--bg-surface); border: 1px solid var(--border-default); border-radius: 24px; padding: 36px; box-shadow: var(--shadow-floating);">
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="width: 52px; height: 52px; border-radius: 14px; background: var(--accent-gradient); color: white; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; box-shadow: 0 8px 24px rgba(99, 102, 241, 0.3);">
                <i data-lucide="sparkles" style="width: 24px; height: 24px;"></i>
            </div>
            <h1 style="font-family: var(--font-heading); font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin-bottom: 4px;">Pial AI Assistant Installer</h1>
            <p style="font-size: 0.85rem; color: var(--text-muted);">Turnkey Software Setup Wizard</p>
        </div>

        @if(session('error'))
            <div style="padding: 12px 14px; border-radius: 10px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: var(--status-danger); font-size: 0.85rem; margin-bottom: 20px;">
                {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div style="padding: 12px 14px; border-radius: 10px; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: var(--status-success); font-size: 0.85rem; margin-bottom: 20px;">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
