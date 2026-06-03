<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AI Chat | Pial Mahmud</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <?php 
    use App\Services\QuotaTracker;
    ?>

    <style>
        :root {
            --bg: #0a0d12;
            --panel: #11161d;
            --border: rgba(255, 255, 255, 0.08);
            --text: #f3f6fb;
            --muted: #9aa7b7;
            --brand: #7c9cff;
            --brand-2: #4ed1b2;
            --brand-3: #9a7cff;
            --user-bg: linear-gradient(135deg, #7c9cff 0%, #9a7cff 100%);
            --shadow: 0 24px 80px rgba(0, 0, 0, 0.34);
            --radius: 22px;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
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
                linear-gradient(rgba(255, 255, 255, .04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, .04) 1px, transparent 1px);
            background-size: 42px 42px;
            mask-image: radial-gradient(circle at center, black 48%, transparent 100%);
            z-index: -1;
        }

        .app {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .sidebar {
            width: 320px;
            background: rgba(17, 22, 29, 0.92);
            border-right: 1px solid var(--border);
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            backdrop-filter: blur(18px);
            overflow-y: auto;
        }

        .brand-section {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .brand-mark {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--brand), var(--brand-3));
            display: grid;
            place-items: center;
            color: white;
            font-size: 24px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .brand-text h1 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
        }

        .brand-text p {
            margin: 2px 0 0;
            font-size: 12px;
            color: var(--muted);
        }

        .sidebar-card {
            background: rgba(21, 27, 35, 0.82);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 14px;
            font-size: 14px;
        }

        .sidebar-card h3 {
            margin: 0 0 10px;
            font-size: 12px;
            font-weight: 600;
            color: #eef2fa;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 12px;
            margin-bottom: 12px;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            display: grid;
            place-items: center;
            color: white;
            font-weight: 700;
            flex-shrink: 0;
        }

        .user-details h4 {
            margin: 0;
            font-size: 13px;
            font-weight: 600;
        }

        .user-details p {
            margin: 2px 0 0;
            font-size: 11px;
            color: var(--muted);
        }

        .logout-btn {
            width: 100%;
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid rgba(255, 107, 107, 0.3);
            color: #ff8787;
            padding: 8px 12px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: auto;
        }

        .logout-btn:hover {
            background: rgba(255, 107, 107, 0.2);
            border-color: rgba(255, 107, 107, 0.5);
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .topbar {
            padding: 16px 24px;
            border-bottom: 1px solid var(--border);
            background: rgba(255, 255, 255, 0.02);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .topbar-title {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .topbar-actions {
            display: flex;
            gap: 8px;
        }

        .btn-icon {
            width: 36px;
            height: 36px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.04);
            border-radius: 10px;
            color: var(--text);
            cursor: pointer;
            display: grid;
            place-items: center;
            transition: all 0.3s ease;
            font-size: 16px;
        }

        .btn-icon:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.15);
        }

        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .messages::-webkit-scrollbar {
            width: 8px;
        }

        .messages::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 999px;
        }

        .messages::-webkit-scrollbar-track {
            background: transparent;
        }

        .message {
            display: flex;
            gap: 10px;
            animation: slideIn 0.3s ease;
        }

        .message.user {
            justify-content: flex-end;
        }

        .message.user .bubble {
            background: var(--user-bg);
            color: white;
            border-bottom-right-radius: 6px;
        }

        .message.assistant {
            justify-content: flex-start;
        }

        .message.assistant .bubble {
            background: rgba(21, 27, 35, 0.82);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: var(--text);
            border-bottom-left-radius: 6px;
        }

        .bubble {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 16px;
            word-wrap: break-word;
            line-height: 1.6;
            font-size: 14px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .bubble code {
            background: rgba(124, 156, 255, 0.1);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.9em;
        }

        .bubble pre {
            background: rgba(0, 0, 0, 0.2);
            padding: 12px;
            border-radius: 8px;
            overflow-x: auto;
            margin: 8px 0;
        }

        .input-section {
            padding: 16px 24px 24px;
            border-top: 1px solid var(--border);
            background: rgba(255, 255, 255, 0.01);
        }

        .input-wrapper {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }

        #messageInput {
            flex: 1;
            padding: 12px 14px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(21, 27, 35, 0.82);
            border-radius: 12px;
            color: var(--text);
            font-family: inherit;
            font-size: 14px;
            resize: none;
            max-height: 120px;
            transition: all 0.3s ease;
        }

        #messageInput:focus {
            outline: none;
            border-color: var(--brand);
            background: rgba(21, 27, 35, 0.95);
            box-shadow: 0 0 0 3px rgba(124, 156, 255, 0.1);
        }

        #messageInput::placeholder {
            color: var(--muted);
        }

        .btn-send {
            width: 36px;
            height: 36px;
            border: none;
            background: linear-gradient(135deg, var(--brand), var(--brand-3));
            color: white;
            border-radius: 10px;
            cursor: pointer;
            display: grid;
            place-items: center;
            font-size: 16px;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        .btn-send:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(124, 156, 255, 0.28);
        }

        .btn-send:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .welcome-message {
            text-align: center;
            padding: 40px 20px;
            color: var(--muted);
        }

        .welcome-message h2 {
            margin: 0 0 8px;
            font-size: 24px;
            font-weight: 700;
            color: var(--text);
        }

        .error {
            color: #ff6b6b;
            background: rgba(255, 107, 107, 0.1);
            padding: 12px;
            border-radius: 10px;
            border-left: 3px solid #ff6b6b;
        }

        .quota-banner {
            padding: 12px 24px;
            border-bottom: 1px solid var(--border);
            background: rgba(255, 255, 255, 0.02);
            display: flex;
            flex-direction: column;
            gap: 8px;
            font-size: 13px;
        }

        .quota-text {
            color: var(--muted);
            font-weight: 500;
            font-size: 12px;
        }

        .quota-text.low {
            color: #ffa500;
        }

        .quota-text.exhausted {
            color: #ff6b6b;
        }

        .quota-bar {
            width: 100%;
            height: 6px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 999px;
            overflow: hidden;
        }

        .quota-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--brand-2), var(--brand));
            border-radius: 999px;
            transition: width 0.3s ease;
        }

        .quota-fill.low {
            background: linear-gradient(90deg, #ffa500, #ff8c00);
        }

        .quota-fill.exhausted {
            background: linear-gradient(90deg, #ff6b6b, #ff4444);
        }

        .warning-banner {
            padding: 12px 24px;
            background: rgba(255, 165, 0, 0.1);
            border-bottom: 1px solid rgba(255, 165, 0, 0.3);
            color: #ffd580;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .warning-banner.exhausted {
            background: rgba(255, 107, 107, 0.1);
            border-bottom-color: rgba(255, 107, 107, 0.3);
            color: #ff8787;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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
                max-width: 85%;
            }
        }
    </style>
</head>

<body>
    <div class="app">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="brand-section">
                <div class="brand-mark">✨</div>
                <div class="brand-text">
                    <h1>AI Chat</h1>
                    <p>Powered by Gemini</p>
                </div>
            </div>

            <div class="sidebar-card">
                <div class="user-info">
                    <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <div class="user-details">
                        <h4>{{ auth()->user()->name }}</h4>
                        <p>{{ auth()->user()->email }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('auth.logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>

            <div class="sidebar-card" style="flex: 1; overflow-y: auto;">
                <h3>Tips</h3>
                <p style="margin: 0; font-size: 13px; color: var(--muted); line-height: 1.6;">
                    ✓ Ask any Laravel questions<br>
                    ✓ Get code examples<br>
                    ✓ Get explanations<br>
                    ✓ Rate limit: 10/min
                </p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Bar -->
            <div class="topbar">
                <h1 class="topbar-title">Chat with AI</h1>
                <div class="topbar-actions">
                    <button class="btn-icon" id="clearBtn" title="Clear chat">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            </div>

            <!-- Quota Banner -->
            @if ($isLowQuota)
                <div class="warning-banner {{ QuotaTracker::isExhausted() ? 'exhausted' : '' }}">
                    <i
                        class="bi {{ QuotaTracker::isExhausted() ? 'bi-exclamation-circle' : 'bi-exclamation-triangle' }}"></i>
                    <span>{{ QuotaTracker::isExhausted() ? '⛔ Daily quota exhausted. Reset at midnight UTC.' : '⚠️ Low quota remaining. Consider upgrading.' }}</span>
                </div>
            @endif

            <div class="quota-banner">
                <span class="quota-text {{ $isLowQuota ? 'low' : '' }}">
                    {!! $quotaStatus !!}
                </span>
                <div class="quota-bar">
                    <div class="quota-fill {{ $quotaFillClass }}"
                        style="width: {{ QuotaTracker::getRemainingPercent() }}%;"></div>
                </div>
            </div>

            <!-- Chat Area -->
            <div class="chat-area">
                <div class="messages" id="messages">
                    @if (count($history) == 0)
                        <div class="welcome-message">
                            <h2>👋 Welcome, {{ auth()->user()->name }}!</h2>
                            <p>Start a conversation with the AI. Ask anything about Laravel or web development.</p>
                        </div>
                    @else
                        @foreach ($history as $msg)
                            <div class="message {{ $msg['role'] === 'user' ? 'user' : 'assistant' }}">
                                <div class="bubble">{!! nl2br(e($msg['parts'][0]['text'])) !!}</div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <!-- Input Section -->
                <div class="input-section">
                    <div class="input-wrapper">
                        <textarea id="messageInput" placeholder="Ask me anything..." rows="1"></textarea>
                        <button class="btn-send" id="sendBtn" title="Send message">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </div>
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

        // Auto-resize textarea
        messageInput.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });

        // Send message on Enter (Ctrl+Enter on mobile)
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

            // Check if welcome message exists and remove it
            const welcomeMsg = messagesDiv.querySelector('.welcome-message');
            if (welcomeMsg) welcomeMsg.remove();

            // Add user message to UI
            const userBubble = document.createElement('div');
            userBubble.className = 'message user';
            userBubble.innerHTML = `<div class="bubble">${escapeHtml(message)}</div>`;
            messagesDiv.appendChild(userBubble);

            // Clear input
            messageInput.value = '';
            messageInput.style.height = 'auto';
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<i class="bi bi-hourglass-split loading"></i>';

            // Scroll to bottom
            messagesDiv.scrollTop = messagesDiv.scrollHeight;

            try {
                const response = await fetch('{{ route("chat.send") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ message }),
                });

                const data = await response.json();

                // Update quota display
                updateQuotaDisplay(data.quota, data.quotaStatus, data.isLowQuota);

                // Add assistant message
                const assistantBubble = document.createElement('div');
                assistantBubble.className = 'message assistant';

                let content = data.reply;
                if (data.reply.includes('Error') || data.reply.includes('Quota')) {
                    assistantBubble.classList.add('error');
                }

                assistantBubble.innerHTML = `<div class="bubble">${escapeHtml(content)}</div>`;
                messagesDiv.appendChild(assistantBubble);

                // Scroll to bottom
                messagesDiv.scrollTop = messagesDiv.scrollHeight;

            } catch (error) {
                const errorBubble = document.createElement('div');
                errorBubble.className = 'message assistant error';
                errorBubble.innerHTML = `<div class="bubble">❌ Error: ${escapeHtml(error.message)}</div>`;
                messagesDiv.appendChild(errorBubble);
            } finally {
                sendBtn.disabled = false;
                sendBtn.innerHTML = '<i class="bi bi-send-fill"></i>';
                messageInput.focus();
            }
        }

        function updateQuotaDisplay(quota, quotaStatus, isLowQuota) {
            const percent = (quota / 250) * 100;

            // Update quota text
            const quotaText = document.querySelector('.quota-text');
            if (quotaText) {
                quotaText.textContent = quotaStatus;
                quotaText.className = 'quota-text';
                if (isLowQuota) quotaText.classList.add('low');
            }

            // Update quota bar
            const quotaFill = document.querySelector('.quota-fill');
            if (quotaFill) {
                quotaFill.style.width = percent + '%';
                quotaFill.className = 'quota-fill';
                if (quota <= 20) quotaFill.classList.add('low');
                if (quota === 0) quotaFill.classList.add('exhausted');
            }

            // Update warning banner
            const warningBanner = document.querySelector('.warning-banner');
            if (quota === 0) {
                if (!warningBanner) {
                    const banner = document.createElement('div');
                    banner.className = 'warning-banner exhausted';
                    banner.innerHTML = '<i class="bi bi-exclamation-circle"></i><span>⛔ Daily quota exhausted. Reset at midnight UTC.</span>';
                    document.querySelector('.main-content').insertBefore(banner, document.querySelector('.quota-banner'));
                }
            }
        }

        async function clearChat() {
            if (confirm('Clear all messages?')) {
                try {
                    await fetch('{{ route("chat.clear") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    });
                    messagesDiv.innerHTML = `
                        <div class="welcome-message">
                            <h2>👋 Welcome back!</h2>
                            <p>Start a new conversation with the AI.</p>
                        </div>
                    `;
                } catch (error) {
                    alert('Error clearing chat: ' + error.message);
                }
            }
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>

</html>