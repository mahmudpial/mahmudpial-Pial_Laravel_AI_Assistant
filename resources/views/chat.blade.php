<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Pial Mahmud's Laravel AI Assistant powered by Gemini API.">
    <title>Pial Mahmud | Laravel AI Assistant</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --bg: #0b0f14;
            --panel: #11161d;
            --panel-2: #151b23;
            --panel-3: #1a2130;
            --border: rgba(255, 255, 255, 0.08);
            --text: #f3f6fb;
            --muted: #9aa7b7;
            --brand: #7c9cff;
            --brand-2: #4ed1b2;
            --brand-3: #9a7cff;
            --user: linear-gradient(135deg, rgba(124, 156, 255, 0.95), rgba(154, 124, 255, 0.95));
            --assistant: #ffffff;
            --shadow: 0 24px 80px rgba(0, 0, 0, 0.34);
            --radius: 22px;
        }

        * { box-sizing: border-box; }

        html, body { min-height: 100%; }

        body {
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background:
                radial-gradient(circle at top left, rgba(124, 156, 255, 0.16), transparent 25%),
                radial-gradient(circle at top right, rgba(78, 209, 178, 0.12), transparent 22%),
                linear-gradient(180deg, #0a0d12 0%, #0d1117 45%, #0b0f14 100%);
            color: var(--text);
            overflow: hidden;
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
        }

        .app {
            height: 100vh;
            display: grid;
            grid-template-columns: 320px minmax(0, 1fr);
            overflow: hidden;
        }

        .sidebar {
            background: rgba(17, 22, 29, 0.92);
            border-right: 1px solid var(--border);
            padding: 18px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            backdrop-filter: blur(18px);
            height: 100vh;
            overflow-y: auto;
            position: sticky;
            top: 0;
        }

        .brand-card,
        .side-card,
        .chat-shell {
            background: rgba(21, 27, 35, 0.82);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .brand-card {
            padding: 18px;
            background:
                linear-gradient(145deg, rgba(124, 156, 255, 0.18), rgba(154, 124, 255, 0.10)),
                rgba(21, 27, 35, 0.82);
        }

        .brand-top {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-mark {
            width: 50px;
            height: 50px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, var(--brand), var(--brand-3));
            color: #fff;
            box-shadow: 0 16px 34px rgba(124, 156, 255, 0.24);
            flex: 0 0 auto;
        }

        .brand-name {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .brand-note {
            margin: 3px 0 0;
            color: var(--muted);
            font-size: .88rem;
        }

        .status {
            margin-top: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(78, 209, 178, 0.08);
            border: 1px solid rgba(78, 209, 178, 0.18);
            color: #d6fff3;
            font-size: .82rem;
        }

        .status .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--brand-2);
            box-shadow: 0 0 0 5px rgba(78, 209, 178, 0.12);
        }

        .side-card {
            padding: 16px;
        }

        .side-card h3 {
            margin: 0 0 12px;
            font-size: .92rem;
            color: #eef2fa;
            letter-spacing: -.01em;
        }

        .profile {
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .avatar {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            font-weight: 700;
            flex: 0 0 auto;
        }

        .avatar.brand {
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            color: white;
        }

        .profile h4 {
            margin: 0;
            font-size: .98rem;
            font-weight: 700;
        }

        .profile p {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: .88rem;
            line-height: 1.55;
        }

        .stack-list {
            display: grid;
            gap: 10px;
        }

        .stack-item {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            padding: 12px;
            border-radius: 16px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.06);
        }

        .stack-icon {
            width: 34px;
            height: 34px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: rgba(255,255,255,0.06);
            color: #fff;
            flex: 0 0 auto;
        }

        .stack-item strong {
            display: block;
            font-size: .9rem;
            margin-bottom: 2px;
        }

        .stack-item span {
            display: block;
            color: var(--muted);
            font-size: .82rem;
            line-height: 1.5;
        }

        .prompt-list {
            display: grid;
            gap: 10px;
        }

        .prompt-btn {
            width: 100%;
            text-align: left;
            border: 1px solid rgba(255,255,255,.06);
            background: rgba(255,255,255,.03);
            color: var(--text);
            border-radius: 16px;
            padding: 12px 14px;
            transition: transform .16s ease, background .16s ease, border-color .16s ease;
        }

        .prompt-btn:hover {
            transform: translateY(-1px);
            background: rgba(124, 156, 255, 0.10);
            border-color: rgba(124, 156, 255, 0.34);
        }

        .prompt-btn small {
            display: block;
            margin-top: 3px;
            color: var(--muted);
            font-size: .8rem;
        }

        .side-footer {
            margin-top: auto;
            padding-top: 4px;
            color: var(--muted);
            font-size: .82rem;
            line-height: 1.55;
        }

        .content {
            min-width: 0;
            padding: 18px;
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .chat-shell {
            min-height: 0;
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background: rgba(17, 22, 29, 0.78);
        }

        .topbar {
            padding: 16px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 1px solid var(--border);
            background: rgba(255,255,255,0.02);
        }

        .topbar-left {
            min-width: 0;
        }

        .topbar-title {
            margin: 0;
            font-size: 1.02rem;
            font-weight: 800;
            letter-spacing: -.03em;
        }

        .topbar-subtitle {
            margin: 3px 0 0;
            color: var(--muted);
            font-size: .88rem;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            border-radius: 999px;
            background: rgba(124, 156, 255, 0.08);
            border: 1px solid rgba(124, 156, 255, 0.14);
            color: #e5ecff;
            font-size: .82rem;
        }

        .pill .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--brand);
        }

        .btn-clean {
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,.08);
            background: rgba(255,255,255,.04);
            color: var(--text);
            padding: .68rem .95rem;
        }

        .btn-clean:hover {
            background: rgba(255,255,255,.08);
            border-color: rgba(255,255,255,.12);
            color: #fff;
        }

        .chat-window {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            padding: 22px 22px 14px;
            background:
                radial-gradient(circle at top, rgba(124, 156, 255, 0.05), transparent 28%),
                rgba(10, 14, 20, 0.26);
        }

        .chat-window::-webkit-scrollbar { width: 10px; }
        .chat-window::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,.14);
            border-radius: 999px;
            border: 2px solid rgba(10, 14, 20, 0.3);
        }
        .chat-window::-webkit-scrollbar-track { background: transparent; }

        .welcome {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 16px;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(124, 156, 255, 0.08), rgba(78, 209, 178, 0.06));
            border: 1px solid rgba(255,255,255,.06);
            margin-bottom: 18px;
        }

        .welcome .icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: rgba(255,255,255,.08);
            color: #fff;
            flex: 0 0 auto;
        }

        .welcome h2 {
            margin: 0 0 4px;
            font-size: .98rem;
            font-weight: 700;
        }

        .welcome p {
            margin: 0;
            color: #d7dfeb;
            line-height: 1.65;
            font-size: .92rem;
        }

        .msg-row {
            display: flex;
            align-items: flex-end;
            gap: 10px;
            margin-bottom: 14px;
            animation: rise .22s ease-out both;
        }

        .msg-row.user { justify-content: flex-end; }
        .msg-row.model { justify-content: flex-start; }

        .msg-row.user .meta {
            order: -1;
            text-align: right;
        }

        .bubble {
            max-width: min(80%, 760px);
            padding: 14px 16px;
            border-radius: 18px;
            line-height: 1.7;
            font-size: .96rem;
            word-break: break-word;
            box-shadow: 0 12px 26px rgba(0,0,0,.16);
        }

        .msg-row.user .bubble {
            background: var(--user);
            color: #fff;
            border-bottom-right-radius: 8px;
        }

        .msg-row.model .bubble {
            background: var(--assistant);
            color: #121a26;
            border: 1px solid rgba(0,0,0,.06);
            border-bottom-left-radius: 8px;
        }

        .meta {
            min-width: 50px;
            color: rgba(230,236,245,.58);
            font-size: .72rem;
            padding-bottom: 4px;
            line-height: 1;
        }

        .bubble code {
            background: rgba(124, 156, 255, 0.12);
            color: inherit;
            border-radius: 8px;
            padding: 2px 6px;
            font-size: .88em;
        }

        .reply-section {
            display: block;
            margin-bottom: 14px;
            padding: 14px 14px 12px;
            border-radius: 18px;
        }

        .reply-section + .reply-section {
            margin-top: 10px;
        }

        .reply-section:last-child {
            margin-bottom: 0;
        }

        .reply-heading {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 11px;
            margin-bottom: 8px;
            border-radius: 12px;
            background: rgba(124, 156, 255, 0.10);
            color: #132033;
            font-size: .82rem;
            font-weight: 800;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .reply-heading-icon {
            display: inline-grid;
            place-items: center;
            width: 20px;
            height: 20px;
            border-radius: 999px;
            background: rgba(255,255,255,.22);
            color: currentColor;
            font-size: .72rem;
            line-height: 1;
        }

        .reply-heading--code {
            background: rgba(78, 209, 178, 0.10);
            color: #dffcf4;
        }

        .reply-heading--output {
            background: rgba(255, 196, 87, 0.16);
            color: #4e3413;
        }

        .reply-heading--notes {
            background: rgba(124, 156, 255, 0.12);
            color: #132033;
        }

        .reply-section--explanation {
            background: rgba(124, 156, 255, 0.06);
        }

        .reply-section--code {
            background: rgba(78, 209, 178, 0.06);
        }

        .reply-section--output {
            background: rgba(255, 196, 87, 0.10);
        }

        .reply-section--notes {
            background: rgba(124, 156, 255, 0.08);
        }

        .reply-section--notes .reply-text {
            color: rgba(18, 26, 38, 0.82);
        }

        .reply-section--output .reply-text {
            color: #41310e;
        }

        .reply-heading::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
        }

        .reply-text {
            color: inherit;
            line-height: 1.7;
        }

        .reply-list {
            padding-left: 18px;
            margin: 0;
        }

        .reply-list li {
            margin-bottom: 6px;
        }

        .reply-section--notes .reply-list li,
        .reply-section--output .reply-list li {
            color: inherit;
        }

        .bubble pre {
            background: #0f1722;
            color: #dde7f7;
            border-radius: 16px;
            padding: 16px;
            overflow-x: auto;
            margin: 12px 0 0;
            border: 1px solid rgba(255,255,255,.08);
        }

        .code-shell {
            margin-top: 10px;
            padding: 14px;
            border-radius: 16px;
            background: rgba(9, 17, 27, 0.92);
            border: 1px solid rgba(255,255,255,.08);
        }

        .code-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .code-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 11px;
            border-radius: 12px;
            background: rgba(78, 209, 178, 0.10);
            color: #dffcf4;
            font-size: .82rem;
            font-weight: 700;
        }

        .code-label::before {
            content: '</>';
            font-size: .74rem;
            opacity: .9;
        }

        .copy-code-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid rgba(255,255,255,.10);
            background: rgba(255,255,255,.04);
            color: #edf5ff;
            border-radius: 12px;
            padding: 7px 10px;
            font-size: .8rem;
            font-weight: 700;
            transition: background .16s ease, transform .16s ease, border-color .16s ease;
        }

        .copy-code-btn:hover {
            background: rgba(255,255,255,.08);
            border-color: rgba(255,255,255,.16);
            transform: translateY(-1px);
        }

        .copy-code-btn.copied {
            background: rgba(78, 209, 178, 0.16);
            border-color: rgba(78, 209, 178, 0.28);
            color: #dcfff5;
        }

        .reply-notes-toggle {
            margin-top: 8px;
        }

        .reply-notes-toggle summary {
            list-style: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #132033;
            font-size: .82rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .reply-notes-toggle summary::-webkit-details-marker {
            display: none;
        }

        .reply-notes-toggle summary::before {
            content: '▾';
            display: inline-grid;
            place-items: center;
            width: 18px;
            height: 18px;
            border-radius: 999px;
            background: rgba(255,255,255,.32);
            color: inherit;
            font-size: .7rem;
            transition: transform .18s ease;
        }

        .reply-notes-toggle[open] summary::before {
            transform: rotate(180deg);
        }

        .reply-notes-body {
            padding-left: 2px;
        }

        .bubble pre code {
            background: none;
            padding: 0;
            color: inherit;
        }

        #typing-indicator { display: none; }

        .typing {
            display: inline-flex;
            gap: 6px;
            align-items: center;
            min-height: 22px;
        }

        .typing span {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: rgba(18,26,38,.45);
            animation: pulse 1.05s infinite ease-in-out;
        }

        .typing span:nth-child(2) { animation-delay: .15s; }
        .typing span:nth-child(3) { animation-delay: .3s; }

        .composer {
            padding: 16px 18px 18px;
            border-top: 1px solid var(--border);
            background: linear-gradient(180deg, rgba(255,255,255,.02), rgba(255,255,255,.01));
        }

        .composer-bar {
            display: flex;
            align-items: flex-end;
            gap: 12px;
            padding: 14px;
            border-radius: 22px;
            background: rgba(255,255,255,.03);
            border: 1px solid rgba(255,255,255,.06);
        }

        .input-wrap { flex: 1 1 auto; min-width: 0; }

        .composer-label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #dce4f1;
            font-size: .85rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        #message-input {
            resize: none;
            min-height: 56px;
            max-height: 150px;
            border-radius: 16px;
            background: rgba(255,255,255,.95);
            color: #111827;
            border: 1px solid transparent;
            padding: 14px 16px;
            font-size: .98rem;
            line-height: 1.5;
        }

        #message-input::placeholder { color: #7b8796; }
        #message-input:focus {
            border-color: rgba(124, 156, 255, 0.5);
            box-shadow: 0 0 0 5px rgba(124, 156, 255, 0.14);
        }

        .send-btn {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            border: 0;
            color: white;
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            display: grid;
            place-items: center;
            box-shadow: 0 18px 28px rgba(124, 156, 255, 0.24);
            transition: transform .16s ease, opacity .16s ease;
            flex: 0 0 auto;
        }

        .send-btn:hover {
            transform: translateY(-1px);
        }

        .send-btn:disabled,
        .btn-clean:disabled,
        .prompt-btn:disabled {
            opacity: .55;
            cursor: not-allowed;
        }

        .composer-footer {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-top: 10px;
            flex-wrap: wrap;
            color: var(--muted);
            font-size: .82rem;
        }

        .composer-footer strong { color: #eef3ff; }

        .footer-note {
            max-width: 100%;
            text-align: center;
            padding: 0 18px;
            color: rgba(235, 241, 255, 0.72);
            font-size: .84rem;
            flex: 0 0 auto;
        }

        .footer-note span {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.06);
        }

        .reveal { animation: rise .25s ease-out both; }

        @keyframes rise {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulse {
            0%, 80%, 100% { opacity: .42; transform: translateY(0); }
            40% { opacity: 1; transform: translateY(-4px); }
        }

        @media (max-width: 1100px) {
            .app {
                grid-template-columns: 1fr;
                height: 100vh;
            }

            .sidebar {
                display: none;
            }

        }

        @media (max-width: 768px) {
            .content {
                padding: 10px;
            }

            .topbar,
            .chat-window,
            .composer {
                padding-left: 14px;
                padding-right: 14px;
            }

            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .topbar-actions {
                width: 100%;
                justify-content: space-between;
            }

            .pill {
                width: 100%;
                justify-content: center;
            }

            .chat-window {
                padding-top: 18px;
                padding-bottom: 12px;
            }

            .bubble {
                max-width: 90%;
                font-size: .94rem;
            }

            .composer-bar {
                padding: 12px;
            }

            .send-btn {
                width: 52px;
                height: 52px;
                border-radius: 15px;
            }
        }
    </style>
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="brand-card reveal">
            <div class="brand-top">
                <div class="brand-mark">
                    <i class="bi bi-robot fs-4"></i>
                </div>
                <div>
                    <h1 class="brand-name">Pial Mahmud AI Assistant</h1>
                    <p class="brand-note">Gemini-powered Laravel chat experience</p>
                </div>
            </div>
            <div class="status"><span class="dot"></span> Ready to chat</div>
        </div>

        <div class="side-card reveal">
            <h3>Quick prompts</h3>
            <div class="prompt-list">
                <button class="prompt-btn" type="button" data-prompt="Explain Laravel middleware in simple words with a tiny example.">
                    Laravel middleware
                    <small>Good for quick framework explanations.</small>
                </button>
                <button class="prompt-btn" type="button" data-prompt="How should I structure a Laravel controller and service for a chat app?">
                    Controller + service
                    <small>Ask for architecture guidance.</small>
                </button>
                <button class="prompt-btn" type="button" data-prompt="Give me a clean way to validate chat input and send it with AJAX in Laravel.">
                    Validation flow
                    <small>Useful for request handling patterns.</small>
                </button>
            </div>
        </div>

        <div class="side-card reveal">
            <h3>Stack</h3>
            <div class="stack-list">
                <div class="stack-item">
                    <div class="stack-icon"><i class="bi bi-layers"></i></div>
                    <div><strong>Laravel</strong><span>Backend framework and app structure.</span></div>
                </div>
                <div class="stack-item">
                    <div class="stack-icon"><i class="bi bi-cpu"></i></div>
                    <div><strong>Gemini API</strong><span>AI responses and chat generation.</span></div>
                </div>
                <div class="stack-item">
                    <div class="stack-icon"><i class="bi bi-bootstrap"></i></div>
                    <div><strong>Bootstrap 5</strong><span>Responsive layout and components.</span></div>
                </div>
            </div>
        </div>

        <div class="side-card reveal">
            <h3>About this app</h3>
            <div class="profile">
                <div class="avatar brand">PM</div>
                <div>
                    <h4>Pial Mahmud</h4>
                    <p>Creator, owner, and maintainer of this Laravel AI assistant project.</p>
                </div>
            </div>
            <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,.06); color: var(--muted); font-size: .82rem; line-height: 1.55;">
                Owned and maintained by Pial Mahmud.
            </div>
        </div>
    </aside>

    <main class="content">
        <section class="chat-shell reveal">
            <div class="topbar">
                <div class="topbar-left">
                    <h2 class="topbar-title">Chat</h2>
                    <p class="topbar-subtitle">Ask questions, refine ideas, and get practical answers.</p>
                </div>
                <div class="topbar-actions">
                    <div class="pill">
                        <span class="dot"></span>
                        Gemini connected
                    </div>
                    <button id="clear-btn" class="btn btn-clean" type="button" title="Clear chat">
                        <i class="bi bi-trash3 me-1"></i>
                        Clear
                    </button>
                </div>
            </div>

            <div id="chat-window" class="chat-window">
                <div class="welcome">
                    <div class="icon"><i class="bi bi-chat-dots fs-4"></i></div>
                    <div>
                        <h2>Welcome back</h2>
                        <p>
                            Ask anything about Laravel, PHP, or software development. Use the quick prompts on the left if you want a faster start.
                        </p>
                    </div>
                </div>

                @foreach ($history as $turn)
                    @if ($turn['role'] === 'user')
                        <div class="msg-row user">
                            <div class="meta">{{ now()->format('H:i') }}</div>
                            <div class="bubble">{{ $turn['parts'][0]['text'] }}</div>
                            <div class="avatar brand">You</div>
                        </div>
                    @else
                        <div class="msg-row model">
                            <div class="avatar" style="background: rgba(255,255,255,.08); color: #fff; border: 1px solid rgba(255,255,255,.08);">
                                <i class="bi bi-robot"></i>
                            </div>
                            <div class="bubble">{!! nl2br(e($turn['parts'][0]['text'])) !!}</div>
                            <div class="meta">{{ now()->format('H:i') }}</div>
                        </div>
                    @endif
                @endforeach

                <div class="msg-row model" id="typing-indicator">
                    <div class="avatar" style="background: rgba(255,255,255,.08); color: #fff; border: 1px solid rgba(255,255,255,.08);">
                        <i class="bi bi-robot"></i>
                    </div>
                    <div class="bubble">
                        <div class="typing" aria-label="Assistant is typing">
                            <span></span><span></span><span></span>
                        </div>
                    </div>
                    <div class="meta"></div>
                </div>
            </div>

            <div class="composer">
                <div class="composer-bar">
                    <div class="input-wrap">
                        <div class="composer-label">
                            <i class="bi bi-pencil-square"></i>
                            Message
                        </div>
                        <textarea
                            id="message-input"
                            class="form-control"
                            rows="1"
                            placeholder="Message Pial's assistant..."
                            maxlength="2000"
                        ></textarea>
                    </div>
                    <button id="send-btn" class="send-btn" type="button" title="Send message">
                        <i class="bi bi-send-fill fs-5"></i>
                    </button>
                </div>

                <div class="composer-footer">
                    <div>
                        <i class="bi bi-info-circle me-1"></i>
                        Session keeps the latest <strong>20 turns</strong>.
                    </div>
                    <div id="char-count">0 / 2000</div>
                </div>
            </div>
        </section>

        <div class="footer-note">
            <span>
                <i class="bi bi-shield-check"></i>
                Owned and maintained by Pial Mahmud
            </span>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const chatWindow = document.getElementById('chat-window');
    const messageInput = document.getElementById('message-input');
    const sendBtn = document.getElementById('send-btn');
    const clearBtn = document.getElementById('clear-btn');
    const typingIndicator = document.getElementById('typing-indicator');
    const charCount = document.getElementById('char-count');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const promptButtons = document.querySelectorAll('.prompt-btn');

    function scrollBottom() {
        chatWindow.scrollTop = chatWindow.scrollHeight;
    }

    function now() {
        return new Date().toLocaleTimeString('en-GB', {
            hour: '2-digit',
            minute: '2-digit',
        });
    }

    function escapeHtml(str) {
        return String(str)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function formatInline(text) {
        return escapeHtml(text)
            .replace(/`([^`]+)`/g, '<code>$1</code>');
    }

    function isHeading(text) {
        return /^(?:\*\*|#{1,3}\s*)?(Explanation|Code|Output|Notes)(?:\*\*|:)?$/i.test(text.trim());
    }

    function normalizeHeading(text) {
        return text
            .trim()
            .replace(/^(?:\*\*|#{1,3}\s*)/i, '')
            .replace(/(?:\*\*|:)?$/i, '')
            .trim();
    }

    function parseSectionType(title) {
        if (/^code$/i.test(title)) return 'code';
        if (/^output$/i.test(title)) return 'output';
        if (/^notes$/i.test(title)) return 'notes';
        return 'explanation';
    }

    function renderSection(title, body) {
        const sectionType = parseSectionType(title);
        const sectionClass = `reply-section reply-section--${sectionType}`;
        const headingClass = sectionType === 'code'
            ? ' reply-heading--code'
            : sectionType === 'output'
                ? ' reply-heading--output'
                : sectionType === 'notes'
                    ? ' reply-heading--notes'
                    : '';
        const icon = sectionType === 'code'
            ? 'bi-code-slash'
            : sectionType === 'output'
                ? 'bi-clipboard-data'
                : sectionType === 'notes'
                    ? 'bi-sticky'
                    : 'bi-lightbulb';

        const bodyHtml = body.length
            ? body.map((block) => renderParagraphBlock(block, sectionType)).join('')
            : '';

        if (sectionType === 'notes') {
            const expanded = body.join(' ').length < 220;
            return `
                <div class="${sectionClass}">
                    <div class="reply-heading${headingClass}">
                        <span class="reply-heading-icon"><i class="bi ${icon}"></i></span>
                        <span>${escapeHtml(title)}</span>
                    </div>
                    <details class="reply-notes-toggle" ${expanded ? 'open' : ''}>
                        <summary>${expanded ? 'Hide notes' : 'Show notes'}</summary>
                        <div class="reply-notes-body">${bodyHtml}</div>
                    </details>
                </div>
            `;
        }

        return `
            <div class="${sectionClass}">
                <div class="reply-heading${headingClass}">
                    <span class="reply-heading-icon"><i class="bi ${icon}"></i></span>
                    <span>${escapeHtml(title)}</span>
                </div>
                ${bodyHtml}
            </div>
        `;
    }

    function renderParagraphBlock(block, sectionType = 'explanation') {
        const trimmed = block.trim();
        if (!trimmed) {
            return '';
        }

        const lines = trimmed.split('\n').map((line) => line.trim());
        const isList = lines.every((line) => /^(-|\*)\s+/.test(line) || /^\d+\.\s+/.test(line));

        if (isList) {
            const items = lines.map((line) => `<li>${formatInline(line.replace(/^(-|\*)\s+|^\d+\.\s+/, ''))}</li>`).join('');
            return `<ul class="reply-list">${items}</ul>`;
        }

        const colorClass = sectionType === 'notes'
            ? 'reply-text reply-text--notes'
            : sectionType === 'output'
                ? 'reply-text reply-text--output'
                : 'reply-text';

        return `<p class="${colorClass}">${lines.map((line) => formatInline(line)).join('<br>')}</p>`;
    }

    function renderTextPart(text) {
        const lines = String(text)
            .replace(/\r/g, '')
            .split('\n');

        const sections = [];
        let currentTitle = 'Explanation';
        let currentBody = [];
        let buffer = [];
        let sawExplicitHeading = false;

        const pushBuffer = () => {
            const content = buffer.join('\n').trim();
            if (!content) {
                buffer = [];
                return;
            }

            currentBody.push(content);

            buffer = [];
        };

        const pushSection = () => {
            if (!currentBody.length) {
                return;
            }

            sections.push({
                title: currentTitle,
                body: currentBody.slice(),
            });
            currentTitle = sawExplicitHeading ? currentTitle : 'Explanation';
            currentBody = [];
        };

        for (const line of lines) {
            const trimmedLine = line.trim();

            if (!trimmedLine) {
                pushBuffer();
                continue;
            }

            if (isHeading(trimmedLine)) {
                pushBuffer();
                pushSection();
                currentTitle = normalizeHeading(trimmedLine);
                sawExplicitHeading = true;
                continue;
            }

            buffer.push(line);
        }

        pushBuffer();
        pushSection();

        if (!sections.length) {
            return '';
        }

        return sections.map(({ title, body }) => renderSection(title, body)).join('');
    }

    function renderCodeBlock(block) {
        const raw = block.trim();
        if (!raw) {
            return '';
        }

        const lines = raw.split('\n');
        let language = '';
        let codeLines = lines;

        if (lines.length > 1 && /^[a-zA-Z0-9_#+-]+$/.test(lines[0].trim())) {
            language = lines[0].trim().toLowerCase();
            codeLines = lines.slice(1);
        }

        const code = codeLines.join('\n').trimEnd();
        const label = language ? `Code · ${language}` : 'Code';
        const copyId = `code-${Math.random().toString(36).slice(2, 10)}`;

        return `
            <div class="reply-section code-shell" data-code-id="${copyId}">
                <div class="code-toolbar">
                    <div class="code-label">${escapeHtml(label)}</div>
                    <button type="button" class="copy-code-btn" data-copy-code="${escapeHtml(code)}" aria-label="Copy code">
                        <i class="bi bi-clipboard"></i>
                        Copy
                    </button>
                </div>
                <pre><code>${escapeHtml(code)}</code></pre>
            </div>
        `;
    }

    function formatReply(text) {
        const parts = String(text).split(/```([\s\S]*?)```/g);

        return parts.map((part, index) => {
            if (index % 2 === 1) {
                return renderCodeBlock(part);
            }

            return renderTextPart(part);
        }).join('');
    }

    async function copyCodeButton(button) {
        const code = button.getAttribute('data-copy-code') || '';
        if (!code) return;

        try {
            await navigator.clipboard.writeText(code);
            const original = button.innerHTML;
            button.classList.add('copied');
            button.innerHTML = '<i class="bi bi-check2"></i> Copied';

            setTimeout(() => {
                button.classList.remove('copied');
                button.innerHTML = original;
            }, 1400);
        } catch (error) {
            console.error('Copy failed:', error);
        }
    }

    chatWindow.addEventListener('click', (event) => {
        const button = event.target.closest('.copy-code-btn');
        if (!button) return;
        copyCodeButton(button);
    });

    function appendMessage(role, text) {
        const row = document.createElement('div');
        row.className = `msg-row ${role}`;

        if (role === 'user') {
            row.innerHTML = `
                <div class="meta">${now()}</div>
                <div class="bubble">${escapeHtml(text)}</div>
                <div class="avatar brand">You</div>
            `;
        } else {
            row.innerHTML = `
                <div class="avatar" style="background: rgba(255,255,255,.08); color: #fff; border: 1px solid rgba(255,255,255,.08);">
                    <i class="bi bi-robot"></i>
                </div>
                <div class="bubble">${formatReply(text)}</div>
                <div class="meta">${now()}</div>
            `;
        }

        chatWindow.insertBefore(row, typingIndicator);
        scrollBottom();
    }

    function setLoading(isLoading) {
        typingIndicator.style.display = isLoading ? 'flex' : 'none';
        sendBtn.disabled = isLoading;
        clearBtn.disabled = isLoading;
        messageInput.disabled = isLoading;
        promptButtons.forEach((btn) => btn.disabled = isLoading);

        if (isLoading) {
            scrollBottom();
        }
    }

    function syncTextareaHeight() {
        messageInput.style.height = 'auto';
        messageInput.style.height = Math.min(messageInput.scrollHeight, 150) + 'px';
    }

    function setPrompt(text) {
        messageInput.value = text;
        syncTextareaHeight();
        charCount.textContent = `${messageInput.value.length} / 2000`;
        messageInput.focus();
    }

    promptButtons.forEach((button) => {
        button.addEventListener('click', () => setPrompt(button.dataset.prompt || ''));
    });

    messageInput.addEventListener('input', () => {
        syncTextareaHeight();
        charCount.textContent = `${messageInput.value.length} / 2000`;
    });

    messageInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            sendMessage();
        }
    });

    sendBtn.addEventListener('click', sendMessage);

    async function sendMessage() {
        const text = messageInput.value.trim();
        if (!text || sendBtn.disabled) return;

        appendMessage('user', text);
        messageInput.value = '';
        charCount.textContent = '0 / 2000';
        syncTextareaHeight();

        setLoading(true);

        try {
            const response = await fetch('{{ route("chat.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ message: text }),
            });

            const data = await response.json();

            if (data.success) {
                appendMessage('model', data.reply);
            } else {
                appendMessage('model', 'Something went wrong. Please try again.');
            }
        } catch (error) {
            console.error(error);
            appendMessage('model', 'Network error. Please check your connection and try again.');
        } finally {
            setLoading(false);
            messageInput.focus();
        }
    }

    clearBtn.addEventListener('click', async () => {
        if (!confirm('Clear the current conversation?')) return;

        try {
            setLoading(true);

            await fetch('{{ route("chat.clear") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });

            document.querySelectorAll('.msg-row:not(#typing-indicator)').forEach((row, index) => {
                if (index > 0) row.remove();
            });
        } finally {
            setLoading(false);
            messageInput.focus();
        }
    });

    scrollBottom();
    syncTextareaHeight();
    messageInput.focus();
</script>
</body>
</html>
