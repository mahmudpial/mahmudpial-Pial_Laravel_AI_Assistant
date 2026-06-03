<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Pial Mahmud AI Assistant</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        :root {
            --bg: #0b0f14;
            --panel: #11161d;
            --border: rgba(255, 255, 255, 0.08);
            --text: #f3f6fb;
            --muted: #9aa7b7;
            --brand: #7c9cff;
            --brand-2: #4ed1b2;
            --brand-3: #9a7cff;
            --shadow: 0 24px 80px rgba(0, 0, 0, 0.34);
            --radius: 22px;
        }

        * { box-sizing: border-box; }
        html, body { min-height: 100vh; }

        body {
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(124, 156, 255, 0.16), transparent 25%),
                radial-gradient(circle at top right, rgba(78, 209, 178, 0.12), transparent 22%),
                linear-gradient(180deg, #0a0d12 0%, #0d1117 45%, #0b0f14 100%);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
            opacity: .22;
            background-image:
                linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px);
            background-size: 42px 42px;
            mask-image: radial-gradient(circle at center, black 48%, transparent 100%);
            z-index: -1;
        }

        .auth-container {
            width: 100%;
            max-width: 420px;
        }

        .auth-card {
            background: rgba(21, 27, 35, 0.82);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 40px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(18px);
        }

        .brand-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .brand-mark {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, var(--brand), var(--brand-3));
            color: #fff;
            box-shadow: 0 16px 34px rgba(124, 156, 255, 0.24);
            margin: 0 auto 16px;
            font-size: 28px;
            font-weight: 700;
        }

        .brand-title {
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 8px;
            letter-spacing: -0.02em;
        }

        .brand-subtitle {
            color: var(--muted);
            font-size: 14px;
            margin: 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
            color: var(--text);
        }

        .form-control {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: rgba(17, 22, 29, 0.5);
            color: var(--text);
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--brand);
            background: rgba(17, 22, 29, 0.8);
            box-shadow: 0 0 0 3px rgba(124, 156, 255, 0.1);
        }

        .form-control::placeholder {
            color: var(--muted);
        }

        .btn-signup {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--brand), var(--brand-3));
            color: white;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-signup:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(124, 156, 255, 0.28);
        }

        .btn-signup:active {
            transform: translateY(0);
        }

        .form-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: var(--muted);
        }

        .form-footer a {
            color: var(--brand);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .form-footer a:hover {
            color: var(--brand-2);
        }

        .error-message {
            color: #ff6b6b;
            font-size: 13px;
            margin-top: 6px;
        }

        .alert {
            padding: 12px 14px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid;
        }

        .alert-danger {
            background: rgba(255, 107, 107, 0.1);
            border-color: rgba(255, 107, 107, 0.3);
            color: #ff8787;
        }

        .help-text {
            font-size: 12px;
            color: var(--muted);
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="brand-section">
                <div class="brand-mark">✨</div>
                <h1 class="brand-title">Create Account</h1>
                <p class="brand-subtitle">Join our AI community</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('auth.signup') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        class="form-control" 
                        placeholder="John Doe"
                        value="{{ old('name') }}"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control" 
                        placeholder="your@email.com"
                        value="{{ old('email') }}"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="••••••••"
                        required
                    >
                    <div class="help-text">Minimum 8 characters</div>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input 
                        type="password" 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        class="form-control" 
                        placeholder="••••••••"
                        required
                    >
                </div>

                <button type="submit" class="btn-signup">Create Account</button>
            </form>

            <div class="form-footer">
                Already have an account? <a href="{{ route('auth.login') }}">Sign in</a>
            </div>
        </div>
    </div>
</body>
</html>
