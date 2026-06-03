<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AI Chat | Pial Mahmud</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <?php use App\Services\QuotaTracker; ?>

    <style>
        :root {
            --bg-base: #080b10;
            --bg-surface: #0d1117;
            --bg-elevated: #131920;
            --bg-overlay: #1a2230;
            --bg-input: #111820;
            --border-subtle: rgba(255, 255, 255, 0.055);
            --border-mild: rgba(255, 255, 255, 0.10);
            --text-primary: #edf2fb;
            --text-secondary: #8b98ac;
            --text-muted: #505d6e;
            --text-dim: #344050;
            --accent: #6c8fff;
            --accent-2: #38d9c0;
            --accent-3: #8f6cff;
            --accent-glow: rgba(108, 143, 255, 0.18);
            --accent-glow-sm: rgba(108, 143, 255, 0.10);
            --danger: #ff5c5c;
            --warn: #f5a623;
            --r-sm: 8px;
            --r-md: 12px;
            --r-lg: 18px;
            --r-pill: 999px;
            --font-body: 'Sora', system-ui, sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            height: 100%;
        }

        body {
            font-family: var(--font-body);
            background: var(--bg-base);
            color: var(--text-primary);
            overflow: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            background:
                radial-gradient(ellipse 80% 50% at 10% 0%, rgba(108, 143, 255, 0.09) 0%, transparent 60%),
                radial-gradient(ellipse 60% 40% at 90% 5%, rgba(56, 217, 192, 0.07) 0%, transparent 55%),
                radial-gradient(ellipse 50% 60% at 50% 100%, rgba(143, 108, 255, 0.06) 0%, transparent 60%);
        }

        body::after {
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            opacity: 0.13;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.035) 1px, transparent 1px);
            background-size: 36px 36px;
            mask-image: radial-gradient(ellipse 90% 90% at center, black 30%, transparent 100%);
        }

        .app {
            position: relative;
            z-index: 1;
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* ══ SIDEBAR ══ */
        .sidebar {
            width: 290px;
            flex-shrink: 0;
            background: rgba(13, 17, 23, 0.97);
            border-right: 1px solid var(--border-subtle);
            display: flex;
            flex-direction: column;
        }

        .sidebar-inner {
            flex: 1;
            overflow-y: auto;
            padding: 20px 16px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .sidebar-inner::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-inner::-webkit-scrollbar-thumb {
            background: var(--border-subtle);
            border-radius: var(--r-pill);
        }

        .brand-row {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 4px 4px 18px;
            border-bottom: 1px solid var(--border-subtle);
            margin-bottom: 8px;
        }

        .brand-icon {
            width: 40px;
            height: 40px;
            border-radius: var(--r-md);
            background: linear-gradient(135deg, var(--accent), var(--accent-3));
            display: grid;
            place-items: center;
            font-size: 18px;
            flex-shrink: 0;
            box-shadow: 0 0 20px rgba(108, 143, 255, 0.28);
        }

        .brand-info h1 {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .brand-info span {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 400;
        }

        .section-label {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-dim);
            padding: 14px 4px 8px;
        }

        .stack-row {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            padding: 2px 0 6px;
        }

        .stack-badge {
            font-size: 10.5px;
            font-family: var(--font-mono);
            font-weight: 500;
            padding: 3px 9px;
            border-radius: var(--r-pill);
            background: var(--bg-overlay);
            border: 1px solid var(--border-subtle);
            color: var(--text-secondary);
        }

        .stack-badge.hi {
            background: rgba(108, 143, 255, 0.1);
            border-color: rgba(108, 143, 255, 0.25);
            color: var(--accent);
        }

        .qp-list {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .quick-prompt-btn {
            display: flex;
            align-items: center;
            gap: 9px;
            width: 100%;
            background: transparent;
            border: 1px solid transparent;
            border-radius: var(--r-sm);
            padding: 8px 10px;
            color: var(--text-secondary);
            font-size: 12.5px;
            font-family: var(--font-body);
            font-weight: 400;
            cursor: pointer;
            text-align: left;
            transition: all 0.18s ease;
        }

        .quick-prompt-btn:hover {
            background: var(--accent-glow-sm);
            border-color: rgba(108, 143, 255, 0.2);
            color: var(--text-primary);
        }

        .qp-icon {
            width: 26px;
            height: 26px;
            border-radius: 7px;
            background: var(--bg-overlay);
            display: grid;
            place-items: center;
            font-size: 12px;
            color: var(--accent);
            flex-shrink: 0;
            transition: background 0.18s;
        }

        .quick-prompt-btn:hover .qp-icon {
            background: rgba(108, 143, 255, 0.18);
        }

        /* Sidebar footer */
        .sidebar-footer {
            padding: 14px 16px;
            border-top: 1px solid var(--border-subtle);
            display: flex;
            flex-direction: column;
            gap: 10px;
            background: rgba(8, 11, 16, 0.6);
        }

        .built-by {
            font-size: 11px;
            color: var(--text-dim);
            text-align: center;
        }

        .built-by a {
            color: var(--accent);
            font-weight: 600;
            text-decoration: none;
        }

        .built-by a:hover {
            text-decoration: underline;
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            background: var(--bg-elevated);
            border: 1px solid var(--border-subtle);
            border-radius: var(--r-md);
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            display: grid;
            place-items: center;
            color: white;
            font-weight: 700;
            font-size: 13px;
            flex-shrink: 0;
        }

        .user-meta h4 {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 160px;
        }

        .user-meta p {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 1px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 160px;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            width: 100%;
            background: rgba(255, 92, 92, 0.07);
            border: 1px solid rgba(255, 92, 92, 0.22);
            color: #ff8080;
            padding: 9px;
            border-radius: var(--r-md);
            font-size: 12.5px;
            font-weight: 600;
            font-family: var(--font-body);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .logout-btn:hover {
            background: rgba(255, 92, 92, 0.15);
            border-color: rgba(255, 92, 92, 0.4);
            color: #ffaaaa;
        }

        /* ══ MAIN ══ */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: var(--bg-surface);
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            height: 58px;
            border-bottom: 1px solid var(--border-subtle);
            background: rgba(13, 17, 23, 0.88);
            backdrop-filter: blur(12px);
            flex-shrink: 0;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .topbar-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--accent-2);
            box-shadow: 0 0 8px var(--accent-2);
            animation: pulse-dot 2.5s ease-in-out infinite;
        }

        @keyframes pulse-dot {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.45;
                transform: scale(0.8);
            }
        }

        .topbar-title {
            font-size: 15px;
            font-weight: 600;
            letter-spacing: -0.01em;
        }

        .topbar-sub {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 1px;
        }

        .topbar-actions {
            display: flex;
            gap: 6px;
        }

        .btn-icon {
            width: 34px;
            height: 34px;
            border: 1px solid var(--border-subtle);
            background: var(--bg-elevated);
            border-radius: var(--r-sm);
            color: var(--text-secondary);
            cursor: pointer;
            display: grid;
            place-items: center;
            font-size: 15px;
            transition: all 0.2s ease;
        }

        .btn-icon:hover {
            background: var(--bg-overlay);
            border-color: var(--border-mild);
            color: var(--text-primary);
        }

        /* Warning banner */
        .warning-banner {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 24px;
            background: rgba(245, 166, 35, 0.08);
            border-bottom: 1px solid rgba(245, 166, 35, 0.18);
            color: #f5c842;
            font-size: 12px;
            font-weight: 500;
            flex-shrink: 0;
        }

        .warning-banner.exhausted {
            background: rgba(255, 92, 92, 0.08);
            border-bottom-color: rgba(255, 92, 92, 0.18);
            color: #ff8080;
        }

        /* Quota strip */
        .quota-strip {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 7px 24px;
            border-bottom: 1px solid var(--border-subtle);
            background: rgba(13, 17, 23, 0.5);
            flex-shrink: 0;
        }

        .quota-label {
            font-size: 10.5px;
            font-family: var(--font-mono);
            color: var(--text-muted);
            white-space: nowrap;
            flex-shrink: 0;
        }

        .quota-label.low {
            color: var(--warn);
        }

        .quota-label.exhausted {
            color: var(--danger);
        }

        .quota-track {
            flex: 1;
            height: 3px;
            background: rgba(255, 255, 255, 0.07);
            border-radius: var(--r-pill);
            overflow: hidden;
        }

        .quota-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--accent-2), var(--accent));
            border-radius: var(--r-pill);
            transition: width 0.4s ease;
        }

        .quota-fill.low {
            background: linear-gradient(90deg, #f5a623, #e08000);
        }

        .quota-fill.exhausted {
            background: linear-gradient(90deg, var(--danger), #cc2222);
        }

        /* RPM pill */
        .rpm-pill {
            font-size: 10px;
            font-family: var(--font-mono);
            font-weight: 500;
            padding: 2px 8px;
            border-radius: var(--r-pill);
            background: rgba(56, 217, 192, 0.08);
            border: 1px solid rgba(56, 217, 192, 0.2);
            color: var(--accent-2);
            white-space: nowrap;
            flex-shrink: 0;
        }

        .rpm-pill.warn {
            background: rgba(245, 166, 35, 0.1);
            border-color: rgba(245, 166, 35, 0.25);
            color: var(--warn);
        }

        /* ── Chat area ── */
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .messages {
            flex: 1;
            overflow-y: auto;
            padding: 28px 28px 16px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            scroll-behavior: smooth;
        }

        .messages::-webkit-scrollbar {
            width: 5px;
        }

        .messages::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.08);
            border-radius: var(--r-pill);
        }

        /* Welcome */
        .welcome-message {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 56px 24px;
            gap: 14px;
            animation: fadeUp 0.5s ease both;
        }

        .welcome-orb {
            width: 72px;
            height: 72px;
            border-radius: 22px;
            background: linear-gradient(135deg, var(--accent), var(--accent-3));
            display: grid;
            place-items: center;
            font-size: 32px;
            box-shadow: 0 0 40px rgba(108, 143, 255, 0.28);
            margin-bottom: 4px;
        }

        .welcome-message h2 {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.03em;
        }

        .welcome-message p {
            font-size: 14px;
            color: var(--text-secondary);
            max-width: 360px;
            line-height: 1.7;
        }

        .welcome-chips {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 8px;
            margin-top: 8px;
        }

        .welcome-chip {
            font-size: 12px;
            color: var(--text-secondary);
            background: var(--bg-elevated);
            border: 1px solid var(--border-subtle);
            border-radius: var(--r-pill);
            padding: 6px 14px;
            cursor: pointer;
            transition: all 0.18s;
        }

        .welcome-chip:hover {
            background: var(--accent-glow-sm);
            border-color: rgba(108, 143, 255, 0.25);
            color: var(--text-primary);
        }

        /* Messages */
        .message {
            display: flex;
            gap: 12px;
            animation: fadeUp 0.3s ease both;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(14px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.user {
            justify-content: flex-end;
        }

        .message.assistant {
            justify-content: flex-start;
            align-items: flex-end;
        }

        .msg-avatar {
            width: 30px;
            height: 30px;
            border-radius: 9px;
            display: grid;
            place-items: center;
            font-size: 13px;
            flex-shrink: 0;
            margin-bottom: 2px;
        }

        .msg-avatar.ai {
            background: linear-gradient(135deg, var(--accent), var(--accent-3));
            color: white;
        }

        .bubble {
            max-width: 68%;
            padding: 12px 16px;
            border-radius: 16px;
            word-wrap: break-word;
            line-height: 1.65;
            font-size: 13.5px;
        }

        .message.user .bubble {
            background: linear-gradient(135deg, var(--accent), var(--accent-3));
            color: white;
            border-bottom-right-radius: 5px;
            box-shadow: 0 4px 20px rgba(108, 143, 255, 0.2);
        }

        .message.assistant .bubble {
            background: var(--bg-elevated);
            border: 1px solid var(--border-subtle);
            color: var(--text-primary);
            border-bottom-left-radius: 5px;
        }

        .bubble code {
            font-family: var(--font-mono);
            font-size: 12px;
            background: rgba(0, 0, 0, 0.25);
            padding: 2px 6px;
            border-radius: 5px;
            color: var(--accent-2);
        }

        .bubble pre {
            font-family: var(--font-mono);
            font-size: 12px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border-subtle);
            padding: 12px 14px;
            border-radius: 10px;
            overflow-x: auto;
            margin: 10px 0;
        }

        .bubble.error-bubble {
            background: rgba(255, 92, 92, 0.08);
            border: 1px solid rgba(255, 92, 92, 0.2);
            border-left: 3px solid var(--danger);
            color: #ffaaaa;
        }

        .bubble.warn-bubble {
            background: rgba(245, 166, 35, 0.08);
            border: 1px solid rgba(245, 166, 35, 0.2);
            border-left: 3px solid var(--warn);
            color: #ffd480;
        }

        /* Typing indicator */
        .typing-bubble {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 12px 16px;
            background: var(--bg-elevated);
            border: 1px solid var(--border-subtle);
            border-radius: 16px;
            border-bottom-left-radius: 5px;
        }

        .typing-bubble span {
            width: 7px;
            height: 7px;
            background: var(--text-muted);
            border-radius: 50%;
            animation: bounce 1.2s ease infinite;
        }

        .typing-bubble span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-bubble span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes bounce {

            0%,
            80%,
            100% {
                transform: translateY(0);
                opacity: 0.5;
            }

            40% {
                transform: translateY(-6px);
                opacity: 1;
            }
        }

        /* Input */
        .input-section {
            padding: 12px 24px 20px;
            background: rgba(13, 17, 23, 0.92);
            border-top: 1px solid var(--border-subtle);
            flex-shrink: 0;
        }

        .input-outer {
            display: flex;
            align-items: flex-end;
            gap: 10px;
            background: var(--bg-input);
            border: 1px solid var(--border-mild);
            border-radius: var(--r-lg);
            padding: 10px 10px 10px 16px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .input-outer:focus-within {
            border-color: rgba(108, 143, 255, 0.45);
            box-shadow: 0 0 0 3px rgba(108, 143, 255, 0.08);
        }

        #messageInput {
            flex: 1;
            background: transparent;
            border: none;
            outline: none;
            color: var(--text-primary);
            font-family: var(--font-body);
            font-size: 13.5px;
            line-height: 1.6;
            resize: none;
            max-height: 130px;
            padding: 2px 0;
        }

        #messageInput::placeholder {
            color: var(--text-muted);
        }

        .btn-send {
            width: 36px;
            height: 36px;
            border: none;
            flex-shrink: 0;
            background: linear-gradient(135deg, var(--accent), var(--accent-3));
            color: white;
            border-radius: 10px;
            cursor: pointer;
            display: grid;
            place-items: center;
            font-size: 15px;
            transition: all 0.2s ease;
        }

        .btn-send:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(108, 143, 255, 0.35);
        }

        .btn-send:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .input-hint {
            font-size: 11px;
            color: var(--text-dim);
            margin-top: 7px;
            padding-left: 2px;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .loading {
            display: inline-block;
            animation: spin 1s linear infinite;
        }

        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }

            .bubble {
                max-width: 88%;
            }

            .messages {
                padding: 20px 16px 12px;
            }

            .input-section {
                padding: 10px 16px 16px;
            }

            .topbar {
                padding: 0 16px;
            }
        }
    </style>
</head>

<body>
    <div class="app">

        {{-- ══ SIDEBAR ══ --}}
        <aside class="sidebar">
            <div class="sidebar-inner">

                <div class="brand-row">
                    <div class="brand-icon">✨</div>
                    <div class="brand-info">
                        <h1>AI Chat</h1>
                        <span>Powered by Gemini</span>
                    </div>
                </div>

                <div class="section-label">Tech Stack</div>
                <div class="stack-row">
                    <span class="stack-badge hi">Laravel</span>
                    <span class="stack-badge hi">PHP 8.3</span>
                    <span class="stack-badge">Gemini API</span>
                    <span class="stack-badge">Bootstrap 5</span>
                    <span class="stack-badge">REST API</span>
                </div>

                <div class="section-label" style="padding-top:16px;">Quick Prompts</div>
                <div class="qp-list">
                    <button class="quick-prompt-btn" data-prompt="Explain Laravel service container">
                        <span class="qp-icon"><i class="bi bi-box-seam"></i></span>Service container
                    </button>
                    <button class="quick-prompt-btn" data-prompt="How does Laravel auth work?">
                        <span class="qp-icon"><i class="bi bi-shield-check"></i></span>Authentication
                    </button>
                    <button class="quick-prompt-btn" data-prompt="Explain Eloquent relationships">
                        <span class="qp-icon"><i class="bi bi-diagram-3"></i></span>Eloquent ORM
                    </button>
                    <button class="quick-prompt-btn" data-prompt="REST API best practices in Laravel">
                        <span class="qp-icon"><i class="bi bi-arrow-left-right"></i></span>REST API design
                    </button>
                    <button class="quick-prompt-btn" data-prompt="Laravel middleware explained">
                        <span class="qp-icon"><i class="bi bi-layers"></i></span>Middleware
                    </button>
                    <button class="quick-prompt-btn" data-prompt="Optimize Laravel DB queries">
                        <span class="qp-icon"><i class="bi bi-database-gear"></i></span>Query optimization
                    </button>
                    <button class="quick-prompt-btn" data-prompt="Laravel Sanctum vs Passport">
                        <span class="qp-icon"><i class="bi bi-key"></i></span>Sanctum vs Passport
                    </button>
                    <button class="quick-prompt-btn" data-prompt="Laravel queues & jobs guide">
                        <span class="qp-icon"><i class="bi bi-cpu"></i></span>Queues &amp; Jobs
                    </button>
                    <button class="quick-prompt-btn" data-prompt="Laravel event & listener system">
                        <span class="qp-icon"><i class="bi bi-lightning-charge"></i></span>Events &amp; Listeners
                    </button>
                    <button class="quick-prompt-btn" data-prompt="Laravel service provider explained">
                        <span class="qp-icon"><i class="bi bi-plugin"></i></span>Service providers
                    </button>
                </div>

            </div>

            <div class="sidebar-footer">

                <div class="user-card">
                    <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <div class="user-meta">
                        <h4>{{ auth()->user()->name }}</h4>
                        <p>{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('auth.logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i class="bi bi-box-arrow-right"></i> Sign out
                    </button>
                </form>
                <p class="built-by">
                    Built by
                    <a href="https://portfolio-and-blog-app-fontend.vercel.app" target="_blank"
                        rel="noopener noreferrer">Pial
                        Mahmud</a>
                    &middot; Full-Stack Engineer
                </p>
            </div>
        </aside>

        {{-- ══ MAIN ══ --}}
        <div class="main-content">

            <div class="topbar">
                <div class="topbar-left">
                    <div class="topbar-dot"></div>
                    <div>
                        <div class="topbar-title">Chat with AI</div>
                        <div class="topbar-sub">gemini-2.0-flash-lite &nbsp;·&nbsp; Laravel Assistant</div>
                    </div>
                </div>
                <div class="topbar-actions">
                    <button class="btn-icon" id="clearBtn" title="Clear conversation">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            </div>

            @if ($isLowQuota)
                <div class="warning-banner {{ QuotaTracker::isExhausted() ? 'exhausted' : '' }}">
                    <i
                        class="bi {{ QuotaTracker::isExhausted() ? 'bi-exclamation-octagon' : 'bi-exclamation-triangle' }}"></i>
                    <span>{{ QuotaTracker::isExhausted() ? 'Daily quota exhausted — resets at midnight UTC.' : 'Quota running low. Consider upgrading your Gemini plan.' }}</span>
                </div>
            @endif

            <div class="quota-strip">
                <span
                    class="quota-label {{ $isLowQuota ? ($quotaFillClass === 'exhausted' ? 'exhausted' : 'low') : '' }}">
                    {!! $quotaStatus !!}
                </span>
                <div class="quota-track">
                    <div class="quota-fill {{ $quotaFillClass }}"
                        style="width: {{ QuotaTracker::getRemainingPercent() }}%;"></div>
                </div>
                <span class="rpm-pill" id="rpmPill">
                    {{ QuotaTracker::getRpmRemaining() }}/30 rpm
                </span>
            </div>

            <div class="chat-area">
                <div class="messages" id="messages">
                    @if (count($history) == 0)
                        <div class="welcome-message">
                            <div class="welcome-orb">✨</div>
                            <h2>Hello, {{ auth()->user()->name }}!</h2>
                            <p>Your Laravel AI assistant is ready. Ask anything about Laravel, PHP, APIs, or web
                                architecture.</p>
                            <div class="welcome-chips">
                                <span class="welcome-chip" data-chip="What is Laravel?">What is Laravel?</span>
                                <span class="welcome-chip" data-chip="How do I build a REST API in Laravel?">Build a REST
                                    API</span>
                                <span class="welcome-chip" data-chip="Explain Laravel Eloquent ORM">Eloquent ORM</span>
                                <span class="welcome-chip" data-chip="What are Laravel queues?">Laravel queues</span>
                            </div>
                        </div>
                    @else
                        @foreach ($history as $msg)
                            @if ($msg['role'] === 'user')
                                <div class="message user">
                                    <div class="bubble">{!! nl2br(e($msg['parts'][0]['text'])) !!}</div>
                                </div>
                            @else
                                <div class="message assistant">
                                    <div class="msg-avatar ai"><i class="bi bi-stars"></i></div>
                                    <div class="bubble">{!! nl2br(e($msg['parts'][0]['text'])) !!}</div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>

                <div class="input-section">
                    <div class="input-outer">
                        <textarea id="messageInput" placeholder="Ask anything about Laravel…" rows="1"></textarea>
                        <button class="btn-send" id="sendBtn" title="Send">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </div>
                    <p class="input-hint">Enter to send &nbsp;·&nbsp; Shift+Enter for newline &nbsp;·&nbsp; 30 req/min
                        free tier</p>
                </div>
            </div>

        </div>
    </div>

    <script>
        const messagesDiv = document.getElementById('messages');
        const messageInput = document.getElementById('messageInput');
        const sendBtn = document.getElementById('sendBtn');
        const clearBtn = document.getElementById('clearBtn');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // ── Quick prompt buttons ──
        document.querySelectorAll('.quick-prompt-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                messageInput.value = btn.dataset.prompt;
                messageInput.focus();
                messageInput.dispatchEvent(new Event('input'));
            });
        });

        // ── Welcome chips ──
        document.querySelectorAll('.welcome-chip').forEach(chip => {
            chip.addEventListener('click', () => {
                messageInput.value = chip.dataset.chip;
                messageInput.focus();
                messageInput.dispatchEvent(new Event('input'));
            });
        });

        // ── Auto-resize textarea ──
        messageInput.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 130) + 'px';
        });

        // ── Send on Enter (Shift+Enter = newline) ──
        messageInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        sendBtn.addEventListener('click', sendMessage);
        clearBtn.addEventListener('click', clearChat);

        async function sendMessage() {
            const message = messageInput.value.trim();
            if (!message || sendBtn.disabled) return;

            const welcomeMsg = messagesDiv.querySelector('.welcome-message');
            if (welcomeMsg) welcomeMsg.remove();

            // User bubble
            appendMessage('user', message);

            messageInput.value = '';
            messageInput.style.height = 'auto';
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<i class="bi bi-hourglass-split loading"></i>';
            scrollBottom();

            // Typing indicator
            const typingRow = document.createElement('div');
            typingRow.className = 'message assistant';
            typingRow.id = 'typing-indicator';
            typingRow.innerHTML = `
            <div class="msg-avatar ai"><i class="bi bi-stars"></i></div>
            <div class="typing-bubble"><span></span><span></span><span></span></div>
        `;
            messagesDiv.appendChild(typingRow);
            scrollBottom();

            try {
                const response = await fetch('{{ route("chat.send") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ message }),
                });

                const data = await response.json();
                removeTyping();
                updateQuotaDisplay(data.quota, data.quotaStatus, data.isLowQuota, data.rpmRemaining);

                // Choose bubble style
                let bubbleClass = 'bubble';
                const reply = data.reply || '';
                if (reply.includes('❌') || reply.includes('Error') || reply.includes('Internal Server')) {
                    bubbleClass = 'bubble error-bubble';
                } else if (reply.includes('⏱️') || reply.includes('Rate limit')) {
                    bubbleClass = 'bubble warn-bubble';
                }

                const row = document.createElement('div');
                row.className = 'message assistant';
                row.innerHTML = `
                <div class="msg-avatar ai"><i class="bi bi-stars"></i></div>
                <div class="${bubbleClass}">${nl2br(escapeHtml(reply))}</div>
            `;
                messagesDiv.appendChild(row);
                scrollBottom();

            } catch (error) {
                removeTyping();
                const row = document.createElement('div');
                row.className = 'message assistant';
                row.innerHTML = `
                <div class="msg-avatar ai"><i class="bi bi-stars"></i></div>
                <div class="bubble error-bubble">Connection error: ${escapeHtml(error.message)}</div>
            `;
                messagesDiv.appendChild(row);
            } finally {
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="bi bi-send-fill"></i>';
                messageInput.focus();
            }
        }

        function appendMessage(role, text) {
            const row = document.createElement('div');
            row.className = 'message ' + role;
            if (role === 'user') {
                row.innerHTML = `<div class="bubble">${nl2br(escapeHtml(text))}</div>`;
            } else {
                row.innerHTML = `
                <div class="msg-avatar ai"><i class="bi bi-stars"></i></div>
                <div class="bubble">${nl2br(escapeHtml(text))}</div>
            `;
            }
            messagesDiv.appendChild(row);
        }

        function updateQuotaDisplay(quota, quotaStatus, isLowQuota, rpmRemaining) {
            // Daily quota text
            const qLabel = document.querySelector('.quota-label');
            if (qLabel) {
                qLabel.textContent = quotaStatus;
                qLabel.className = 'quota-label' + (isLowQuota ? ' low' : '');
            }

            // Daily quota bar
            const fill = document.querySelector('.quota-fill');
            if (fill) {
                const pct = (quota / 1500) * 100;
                fill.style.width = pct + '%';
                fill.className = 'quota-fill';
                if (quota <= 100) fill.classList.add('low');
                if (quota === 0) fill.classList.add('exhausted');
            }

            // RPM pill
            const rpmPill = document.getElementById('rpmPill');
            if (rpmPill && rpmRemaining !== undefined) {
                rpmPill.textContent = rpmRemaining + '/30 rpm';
                rpmPill.className = 'rpm-pill' + (rpmRemaining <= 5 ? ' warn' : '');
            }

            // Warning banner
            if (quota === 0 && !document.querySelector('.warning-banner.exhausted')) {
                const banner = document.createElement('div');
                banner.className = 'warning-banner exhausted';
                banner.innerHTML = '<i class="bi bi-exclamation-octagon"></i><span>Daily quota exhausted — resets at midnight UTC.</span>';
                document.querySelector('.main-content').insertBefore(banner, document.querySelector('.quota-strip'));
            }
        }

        async function clearChat() {
            if (confirm('Clear all messages?')) {
                try {
                    await fetch('{{ route("chat.clear") }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken },
                    });
                    messagesDiv.innerHTML = `
                    <div class="welcome-message">
                        <div class="welcome-orb">✨</div>
                        <h2>Fresh start!</h2>
                        <p>Conversation cleared. Ask me anything about Laravel or PHP.</p>
                    </div>
                `;
                } catch (error) {
                    alert('Error clearing chat: ' + error.message);
                }
            }
        }

        function removeTyping() {
            const t = document.getElementById('typing-indicator');
            if (t) t.remove();
        }

        function scrollBottom() {
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }

        function escapeHtml(text) {
            const d = document.createElement('div');
            d.textContent = text;
            return d.innerHTML;
        }

        function nl2br(text) {
            return text.replace(/\n/g, '<br>');
        }
    </script>
</body>

</html>