<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Assistant | Pial Mahmud</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <i data-lucide="sparkles" style="color: var(--accent-color)"></i>
                <span>AI Assistant</span>
            </div>

            <nav class="sidebar-nav">
                <a href="#" class="nav-item active">
                    <i data-lucide="message-square"></i>
                    <span>New Chat</span>
                </a>
                <a href="#" class="nav-item">
                    <i data-lucide="book-open"></i>
                    <span>Documentation</span>
                </a>
                <a href="#" class="nav-item">
                    <i data-lucide="settings"></i>
                    <span>Settings</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="user-profile">
                    <div style="width: 32px; height: 32px; background: #ddd; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 12px;">
                        {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                    </div>
                    <div style="overflow: hidden;">
                        <div style="font-size: 0.85rem; font-weight: 600; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;">
                            {{ Auth::user()->name ?? 'Guest User' }}
                        </div>
                        <div style="font-size: 0.7rem; color: var(--text-muted); white-space: nowrap; text-overflow: ellipsis; overflow: hidden;">
                            {{ Auth::user()->email ?? 'guest@example.com' }}
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" style="margin-left: auto;">
                        @csrf
                        <button type="submit" style="background: none; border: none; cursor: pointer; color: var(--text-muted);">
                            <i data-lucide="log-out" style="width: 16px; height: 16px;"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="quota-wrapper">
                <div id="quota-bar" class="quota-bar" style="width: 0%"></div>
            </div>

            <div class="chat-window" id="chat-window">
                <div class="chat-container" id="chat-container">
                    @forelse($history as $turn)
                        <div class="message {{ $turn['role'] === 'user' ? 'user' : 'ai' }}">
                            <div class="message-content">
                                {{ $turn['parts'][0]['text'] ?? '' }}
                            </div>
                        </div>
                    @empty
                        <div style="text-align: center; margin-top: 10vh; color: var(--text-muted);">
                            <i data-lucide="bot" style="width: 48px; height: 48px; margin: 0 auto 16px; opacity: 0.5;"></i>
                            <h2 style="font-family: var(--heading-font); font-size: 1.5rem; color: var(--text-main);">How can I help you today?</h2>
                            <p style="font-size: 0.95rem; margin-top: 8px;">Ask me anything about Laravel, PHP, or Web Development.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="input-area">
                <div style="width: 100%; max-width: 768px;">
                    <div class="prompt-container">
                        <div class="prompt-chip" onclick="sendPrompt('Explain Laravel service container')">Service Container</div>
                        <div class="prompt-chip" onclick="sendPrompt('How does Auth work?')">Authentication</div>
                        <div class="prompt-chip" onclick="sendPrompt('Eloquent ORM best practices')">Eloquent ORM</div>
                        <div class="prompt-chip" onclick="sendPrompt('REST API design patterns')">REST API Design</div>
                    </div>

                    <div class="input-wrapper">
                        <input type="text" id="message-input" class="input-field" placeholder="Message AI Assistant...">
                        <button id="send-btn" class="send-btn">
                            <i data-lucide="arrow-up" style="width: 18px; height: 18px; color: white;"></i>
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        function sendPrompt(text) {
            document.getElementById('message-input').value = text;
            document.getElementById('send-btn').click();
        }
    </script>
    @vite('resources/js/app.js')
</body>
</html>
