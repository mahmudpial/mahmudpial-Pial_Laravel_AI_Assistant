<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Pial AI Assistant</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body data-theme="dark">
    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-brand-icon">
                    <i data-lucide="sparkles" style="width: 26px; height: 26px;"></i>
                </div>
                <h1 class="auth-title">Welcome Back</h1>
                <p class="auth-subtitle">Sign in to continue to Pial AI Assistant</p>
            </div>

            @if ($errors->any())
                <div class="auth-alert-danger">
                    <i data-lucide="alert-circle" style="width: 16px; height: 16px; display: inline-block; vertical-align: middle; margin-right: 4px;"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            @if (session('success'))
                <div style="padding: 10px 14px; border-radius: 10px; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: var(--status-success); font-size: 0.85rem; margin-bottom: 18px;">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('auth.login') }}" method="POST">
                @csrf

                <div class="auth-form-group">
                    <label for="email" class="auth-label">Email Address</label>
                    <div class="auth-input-wrapper">
                        <input type="email" id="email" name="email" class="auth-input" placeholder="developer@example.com" value="{{ old('email') }}" required autofocus autocomplete="email">
                    </div>
                </div>

                <div class="auth-form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label for="password" class="auth-label" style="margin-bottom: 0;">Password</label>
                    </div>
                    <div class="auth-input-wrapper">
                        <input type="password" id="password" name="password" class="auth-input" placeholder="••••••••" required autocomplete="current-password">
                        <button type="button" class="password-toggle-btn" id="toggle-password" title="Toggle password visibility">
                            <i data-lucide="eye" id="eye-icon" style="width: 17px; height: 17px;"></i>
                        </button>
                    </div>
                </div>

                <div class="auth-form-group" style="display: flex; align-items: center; justify-content: space-between; margin-top: 14px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: var(--text-secondary); cursor: pointer;">
                        <input type="checkbox" name="remember" style="width: 15px; height: 15px; accent-color: var(--accent-primary); cursor: pointer;">
                        Remember this device
                    </label>
                </div>

                <button type="submit" class="auth-submit-btn">
                    <span>Sign In</span>
                </button>
            </form>

            <div class="auth-footer">
                Don't have an account? <a href="{{ route('auth.signup') }}">Create one for free</a>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        // Password visibility toggle
        const toggleBtn = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');

        if (toggleBtn && passwordInput && eyeIcon) {
            toggleBtn.addEventListener('click', () => {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                eyeIcon.setAttribute('data-lucide', isPassword ? 'eye-off' : 'eye');
                lucide.createIcons();
            });
        }
    </script>
</body>
</html>