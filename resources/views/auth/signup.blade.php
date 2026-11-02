<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Pial AI Assistant</title>

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
                <h1 class="auth-title">Create Account</h1>
                <p class="auth-subtitle">Join Pial AI Assistant for Laravel & Web Engineering</p>
            </div>

            @if ($errors->any())
                <div class="auth-alert-danger">
                    <i data-lucide="alert-circle" style="width: 16px; height: 16px; display: inline-block; vertical-align: middle; margin-right: 4px;"></i>
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('auth.signup') }}" method="POST">
                @csrf

                <div class="auth-form-group">
                    <label for="name" class="auth-label">Full Name</label>
                    <div class="auth-input-wrapper">
                        <input type="text" id="name" name="name" class="auth-input" placeholder="Pial Mahmud" value="{{ old('name') }}" required autofocus autocomplete="name">
                    </div>
                </div>

                <div class="auth-form-group">
                    <label for="email" class="auth-label">Email Address</label>
                    <div class="auth-input-wrapper">
                        <input type="email" id="email" name="email" class="auth-input" placeholder="developer@example.com" value="{{ old('email') }}" required autocomplete="email">
                    </div>
                </div>

                <div class="auth-form-group">
                    <label for="password" class="auth-label">Password</label>
                    <div class="auth-input-wrapper">
                        <input type="password" id="password" name="password" class="auth-input" placeholder="Minimum 8 characters" required autocomplete="new-password">
                        <button type="button" class="password-toggle-btn" id="toggle-password" title="Toggle password visibility">
                            <i data-lucide="eye" id="eye-icon" style="width: 17px; height: 17px;"></i>
                        </button>
                    </div>
                </div>

                <div class="auth-form-group">
                    <label for="password_confirmation" class="auth-label">Confirm Password</label>
                    <div class="auth-input-wrapper">
                        <input type="password" id="password_confirmation" name="password_confirmation" class="auth-input" placeholder="Repeat your password" required autocomplete="new-password">
                    </div>
                </div>

                <button type="submit" class="auth-submit-btn">
                    <span>Create Account</span>
                </button>
            </form>

            <div class="auth-footer">
                Already have an account? <a href="{{ route('auth.login') }}">Sign in instead</a>
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