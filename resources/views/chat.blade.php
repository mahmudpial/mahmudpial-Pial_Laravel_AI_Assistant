<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($activeConversation) ? $activeConversation->title . ' | ' : '' }}Pial AI Assistant</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
</head>
<body data-theme="dark">
    <!-- Active Conversation Tracker -->
    <input type="hidden" id="active-conversation-id" value="{{ $activeConversation->id ?? '' }}">

    <!-- Backdrop for mobile drawer -->
    <div class="drawer-backdrop" id="drawer-backdrop"></div>

    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="brand-icon">
                    <i data-lucide="sparkles" style="width: 20px; height: 20px;"></i>
                </div>
                <div class="brand-text">
                    <span>Pial AI</span>
                    <span class="brand-badge">Assistant</span>
                </div>
            </div>

            <div class="sidebar-content">
                <!-- Top Primary Action -->
                <button type="button" class="sidebar-action-btn" id="new-chat-btn" title="Start a fresh chat">
                    <i data-lucide="plus" style="width: 18px; height: 18px;"></i>
                    <span>New Chat</span>
                </button>

                <!-- Search Input for Chats -->
                <div class="sidebar-search-box">
                    <i data-lucide="search" class="sidebar-search-icon" style="width: 14px; height: 14px;"></i>
                    <input type="text" id="conversation-search" class="sidebar-search-input" placeholder="Search chats...">
                </div>

                <!-- Chat History Section -->
                <div id="history-container">
                    @if(!empty($groupedConversations['today']))
                        <div class="history-group">
                            <div class="history-group-title">Today</div>
                            @foreach($groupedConversations['today'] as $conv)
                                <a href="{{ route('chat.show', $conv['id']) }}" class="conversation-item {{ (isset($activeConversation) && $activeConversation->id === $conv['id']) ? 'active' : '' }}" data-id="{{ $conv['id'] }}" data-title="{{ strtolower($conv['title']) }}">
                                    <i data-lucide="message-square" style="width: 15px; height: 15px; flex-shrink: 0;"></i>
                                    <span class="conversation-title-text">{{ $conv['title'] }}</span>
                                    <div class="conversation-item-actions">
                                        <button type="button" class="item-action-btn rename-btn" title="Rename" onclick="event.preventDefault(); window.openRenameModal({{ $conv['id'] }}, '{{ addslashes($conv['title']) }}')">
                                            <i data-lucide="pencil" style="width: 12px; height: 12px;"></i>
                                        </button>
                                        <button type="button" class="item-action-btn delete delete-btn" title="Delete" onclick="event.preventDefault(); window.deleteConversation({{ $conv['id'] }})">
                                            <i data-lucide="trash-2" style="width: 12px; height: 12px;"></i>
                                        </button>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @if(!empty($groupedConversations['previous7Days']))
                        <div class="history-group">
                            <div class="history-group-title">Previous 7 Days</div>
                            @foreach($groupedConversations['previous7Days'] as $conv)
                                <a href="{{ route('chat.show', $conv['id']) }}" class="conversation-item {{ (isset($activeConversation) && $activeConversation->id === $conv['id']) ? 'active' : '' }}" data-id="{{ $conv['id'] }}" data-title="{{ strtolower($conv['title']) }}">
                                    <i data-lucide="message-square" style="width: 15px; height: 15px; flex-shrink: 0;"></i>
                                    <span class="conversation-title-text">{{ $conv['title'] }}</span>
                                    <div class="conversation-item-actions">
                                        <button type="button" class="item-action-btn rename-btn" title="Rename" onclick="event.preventDefault(); window.openRenameModal({{ $conv['id'] }}, '{{ addslashes($conv['title']) }}')">
                                            <i data-lucide="pencil" style="width: 12px; height: 12px;"></i>
                                        </button>
                                        <button type="button" class="item-action-btn delete delete-btn" title="Delete" onclick="event.preventDefault(); window.deleteConversation({{ $conv['id'] }})">
                                            <i data-lucide="trash-2" style="width: 12px; height: 12px;"></i>
                                        </button>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @if(!empty($groupedConversations['older']))
                        <div class="history-group">
                            <div class="history-group-title">Older</div>
                            @foreach($groupedConversations['older'] as $conv)
                                <a href="{{ route('chat.show', $conv['id']) }}" class="conversation-item {{ (isset($activeConversation) && $activeConversation->id === $conv['id']) ? 'active' : '' }}" data-id="{{ $conv['id'] }}" data-title="{{ strtolower($conv['title']) }}">
                                    <i data-lucide="message-square" style="width: 15px; height: 15px; flex-shrink: 0;"></i>
                                    <span class="conversation-title-text">{{ $conv['title'] }}</span>
                                    <div class="conversation-item-actions">
                                        <button type="button" class="item-action-btn rename-btn" title="Rename" onclick="event.preventDefault(); window.openRenameModal({{ $conv['id'] }}, '{{ addslashes($conv['title']) }}')">
                                            <i data-lucide="pencil" style="width: 12px; height: 12px;"></i>
                                        </button>
                                        <button type="button" class="item-action-btn delete delete-btn" title="Delete" onclick="event.preventDefault(); window.deleteConversation({{ $conv['id'] }})">
                                            <i data-lucide="trash-2" style="width: 12px; height: 12px;"></i>
                                        </button>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- AI Tools Section -->
                <div>
                    <div class="nav-section-title">Developer AI Tools</div>
                    <nav class="sidebar-nav">
                        <button type="button" class="nav-item" id="open-prompt-library-btn" style="width: 100%; border: none; background: transparent; text-align: left;">
                            <i data-lucide="sparkles" style="width: 17px; height: 17px; color: var(--accent-primary);"></i>
                            <span>Prompt Library</span>
                        </button>
                        <button type="button" class="nav-item" id="open-cheatsheet-btn" style="width: 100%; border: none; background: transparent; text-align: left;">
                            <i data-lucide="terminal" style="width: 17px; height: 17px; color: var(--status-warning);"></i>
                            <span>Artisan Cheatsheet</span>
                        </button>
                        <button type="button" class="nav-item" id="open-code-reviewer-btn" style="width: 100%; border: none; background: transparent; text-align: left;">
                            <i data-lucide="shield-alert" style="width: 17px; height: 17px; color: var(--status-success);"></i>
                            <span>Code Review & Audit</span>
                        </button>
                    </nav>
                </div>

                <!-- Resources Section -->
                <div>
                    <div class="nav-section-title">Resources</div>
                    <nav class="sidebar-nav">
                        @if(optional(Auth::user())->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="nav-item" style="color: var(--accent-primary); font-weight: 600;">
                                <i data-lucide="shield" style="width: 17px; height: 17px;"></i>
                                <span>Admin Panel</span>
                            </a>
                        @endif
                        <a href="https://laravel.com/docs" target="_blank" rel="noopener noreferrer" class="nav-item">
                            <i data-lucide="book-open" style="width: 17px; height: 17px;"></i>
                            <span>Laravel 11 Docs</span>
                        </a>
                        <a href="https://github.com/mahmudpial" target="_blank" rel="noopener noreferrer" class="nav-item">
                            <i data-lucide="github" style="width: 17px; height: 17px;"></i>
                            <span>Developer Profile</span>
                        </a>
                    </nav>
                </div>
            </div>

            <div class="sidebar-footer">
                <!-- Sidebar Quota Meter -->
                <div class="sidebar-quota-box" title="API usage meter">
                    <div class="sidebar-quota-header">
                        <span>Daily Free Tier Quota</span>
                        <span id="sidebar-quota-num">1,500</span>
                    </div>
                    <div class="sidebar-quota-progress">
                        <div id="sidebar-quota-fill" class="sidebar-quota-fill" style="width: 100%;"></div>
                    </div>
                </div>

                <div class="user-card">
                    <div class="user-avatar">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="user-meta">
                        <div class="user-name">{{ Auth::user()->name ?? 'Guest User' }}</div>
                        <div class="user-email">{{ Auth::user()->email ?? 'guest@example.com' }}</div>
                    </div>
                    <button type="button" class="item-action-btn" id="open-settings-btn" title="Settings" style="margin-right: 4px;">
                        <i data-lucide="settings" style="width: 16px; height: 16px;"></i>
                    </button>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="logout-btn" title="Sign Out">
                            <i data-lucide="log-out" style="width: 16px; height: 16px;"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Quota Bar Indicator at top -->
            <div class="quota-bar-wrapper">
                <div id="quota-bar-fill" class="quota-bar-fill" style="width: 100%"></div>
            </div>

            <!-- Top Header -->
            <header class="top-header">
                <div class="header-left">
                    <button class="icon-btn" id="collapse-sidebar" title="Toggle Sidebar">
                        <i data-lucide="panel-left" style="width: 18px; height: 18px;"></i>
                    </button>
                    <div class="model-pill">
                        <span class="latency-dot"></span>
                        <span style="text-transform: capitalize;">{{ App\Services\AiService::getActiveProvider() }}: {{ App\Services\AiService::getActiveModel() }}</span>
                    </div>

                    <!-- AI Persona Selector -->
                    <div class="persona-dropdown-container" id="persona-dropdown-container">
                        <button type="button" class="persona-btn" id="persona-btn" title="Choose AI Expert Persona">
                            <i data-lucide="layers" id="persona-btn-icon" style="width: 14px; height: 14px; color: var(--accent-primary);"></i>
                            <span id="persona-btn-text">Laravel Architect</span>
                            <span class="persona-active-badge" id="persona-btn-badge">Architect</span>
                            <i data-lucide="chevron-down" style="width: 12px; height: 12px; opacity: 0.6;"></i>
                        </button>
                        <div class="persona-dropdown" id="persona-dropdown">
                            <div class="persona-dropdown-header">
                                <span>AI Expert Personas</span>
                                <span class="persona-dropdown-sub">Custom Specialized Prompts</span>
                            </div>
                            <div class="persona-list">
                                @foreach($personas as $key => $p)
                                    <button type="button" class="persona-item" data-key="{{ $key }}" data-name="{{ $p['name'] }}" data-badge="{{ $p['badge'] }}" data-icon="{{ $p['icon'] }}">
                                        <div class="persona-item-icon">
                                            <i data-lucide="{{ $p['icon'] }}" style="width: 16px; height: 16px;"></i>
                                        </div>
                                        <div class="persona-item-info">
                                            <div class="persona-item-title-row">
                                                <span class="persona-item-name">{{ $p['name'] }}</span>
                                                <span class="persona-badge-tag">{{ $p['badge'] }}</span>
                                            </div>
                                            <div class="persona-item-desc">{{ $p['description'] }}</div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="header-right">
                    <div class="quota-telemetry-pill" title="Daily API Quota Remaining">
                        <i data-lucide="zap" style="width: 14px; height: 14px;"></i>
                        <span id="quota-text">{{ $quotaStatus ?? '1,500 / 1,500' }}</span>
                        <div class="quota-mini-progress">
                            <div id="quota-mini-fill" class="quota-mini-fill"></div>
                        </div>
                    </div>

                    <!-- Export Chat Dropdown -->
                    <div class="export-dropdown-container" id="export-dropdown-container">
                        <button class="icon-btn" id="export-chat-btn" title="Export Conversation">
                            <i data-lucide="download" style="width: 17px; height: 17px;"></i>
                        </button>
                        <div class="export-dropdown" id="export-dropdown">
                            <button type="button" class="export-menu-item" id="export-markdown-btn">
                                <i data-lucide="file-text" style="width: 15px; height: 15px; color: var(--accent-primary);"></i>
                                <div class="export-item-text">
                                    <span class="export-title">Export Markdown (.md)</span>
                                    <span class="export-subtitle">Download clean documentation file</span>
                                </div>
                            </button>
                            <button type="button" class="export-menu-item" id="export-print-btn">
                                <i data-lucide="printer" style="width: 15px; height: 15px; color: var(--status-success);"></i>
                                <div class="export-item-text">
                                    <span class="export-title">Print / Save as PDF</span>
                                    <span class="export-subtitle">Clean formatted printable view</span>
                                </div>
                            </button>
                            <button type="button" class="export-menu-item" id="export-copy-btn">
                                <i data-lucide="copy" style="width: 15px; height: 15px; color: var(--status-warning);"></i>
                                <div class="export-item-text">
                                    <span class="export-title">Copy Chat Text</span>
                                    <span class="export-subtitle">Copy conversation to clipboard</span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <button class="icon-btn" id="clear-chat-header-btn" title="Clear Conversation">
                        <i data-lucide="trash-2" style="width: 17px; height: 17px;"></i>
                    </button>

                    <button class="icon-btn" id="theme-toggle" title="Toggle Theme">
                        <i data-lucide="sun" id="theme-icon" style="width: 17px; height: 17px;"></i>
                    </button>
                </div>
            </header>

            <!-- Chat Window -->
            <div class="chat-window" id="chat-window">
                <div class="chat-container" id="chat-container">
                    @forelse($history as $turn)
                        <div class="message {{ $turn['role'] === 'user' ? 'user' : 'ai' }}">
                            @if($turn['role'] === 'user')
                                <div class="message-content-wrapper">
                                    <div class="message-body">{{ $turn['parts'][0]['text'] ?? '' }}</div>
                                </div>
                            @else
                                <div class="ai-avatar">
                                    <i data-lucide="sparkles" style="width: 18px; height: 18px;"></i>
                                </div>
                                <div class="message-content-wrapper">
                                    <div class="message-body raw-markdown" data-content="{{ base64_encode($turn['parts'][0]['text'] ?? '') }}"></div>
                                    <div class="message-actions">
                                        <button class="action-btn copy-msg-btn">
                                            <i data-lucide="copy" style="width: 13px; height: 13px;"></i>
                                            <span>Copy</span>
                                        </button>
                                        <button class="action-btn regenerate-msg-btn" title="Regenerate this response">
                                            <i data-lucide="refresh-cw" style="width: 13px; height: 13px;"></i>
                                            <span>Regenerate</span>
                                        </button>
                                        @if(isset($turn['created_at']))
                                            <span style="font-size: 0.72rem; color: var(--text-muted); margin-left: 6px;">{{ $turn['created_at'] }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="empty-state" id="empty-state">
                            <div class="hero-badge">
                                <i data-lucide="sparkles" style="width: 14px; height: 14px;"></i>
                                <span>Ultra-Fast LPU Engine • Laravel & Web Architect</span>
                            </div>
                            <h1 class="hero-title">How can I help you build today?</h1>
                            <p class="hero-subtitle">Ask anything about Laravel design patterns, Eloquent optimization, APIs, or modern web engineering.</p>

                            <div class="prompt-grid">
                                <div class="prompt-card" onclick="window.sendPrompt('Explain the Laravel Service Container and show a clean interface-binding example with Dependency Injection.')">
                                    <div class="prompt-card-header">
                                        <div class="prompt-card-icon"><i data-lucide="box" style="width: 15px; height: 15px;"></i></div>
                                        <span>Service Container</span>
                                    </div>
                                    <div class="prompt-card-body">Explore IoC bindings, singletons, and constructor injection patterns.</div>
                                </div>

                                <div class="prompt-card" onclick="window.sendPrompt('How do I optimize slow Eloquent queries and eliminate N+1 query problems with eager loading?')">
                                    <div class="prompt-card-header">
                                        <div class="prompt-card-icon"><i data-lucide="database" style="width: 15px; height: 15px;"></i></div>
                                        <span>Eloquent Performance</span>
                                    </div>
                                    <div class="prompt-card-body">Eager loading, composite index strategies, and query chunking.</div>
                                </div>

                                <div class="prompt-card" onclick="window.sendPrompt('Show how to implement secure REST API token authentication in Laravel with Sanctum and rate limiting.')">
                                    <div class="prompt-card-header">
                                        <div class="prompt-card-icon"><i data-lucide="shield-check" style="width: 15px; height: 15px;"></i></div>
                                        <span>API Security</span>
                                    </div>
                                    <div class="prompt-card-body">Token abilities, authentication middleware, and rate-limiting rules.</div>
                                </div>

                                <div class="prompt-card" onclick="window.sendPrompt('Write clean Pest unit and feature tests for an API controller with mocked services and database transactions.')">
                                    <div class="prompt-card-header">
                                        <div class="prompt-card-icon"><i data-lucide="flask-conical" style="width: 15px; height: 15px;"></i></div>
                                        <span>Pest Testing</span>
                                    </div>
                                    <div class="prompt-card-body">Write elegant test suites, mock dependencies, and test response payloads.</div>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Input Bar -->
            <div class="input-area">
                <!-- Floating Stop Generating Button (active during SSE stream) -->
                <div class="stop-generation-container" id="stop-generation-container" style="display: none;">
                    <button type="button" class="stop-generation-btn" id="stop-generation-btn">
                        <i data-lucide="square" style="width: 13px; height: 13px; fill: currentColor;"></i>
                        <span>Stop Generating</span>
                    </button>
                </div>

                <div class="input-container">
                    <textarea id="message-input" class="chat-textarea" placeholder="Message AI Assistant (Shift+Enter for new line)..." rows="1"></textarea>
                    <div class="input-footer">
                        <div class="input-hint">
                            <span class="active-persona-indicator" id="active-persona-indicator" title="Current Active Persona">
                                <i data-lucide="layers" id="input-persona-icon" style="width: 12px; height: 12px;"></i>
                                <span id="input-persona-label">Architect</span>
                            </span>
                            <span class="hint-divider">•</span>
                            <i data-lucide="corner-down-left" style="width: 13px; height: 13px;"></i>
                            <span>Return to send</span>
                        </div>
                        <button id="send-btn" class="send-action-btn" title="Send message">
                            <i data-lucide="arrow-up" style="width: 18px; height: 18px;"></i>
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal: Prompt Library -->
    <div class="modal-overlay" id="modal-prompt-library">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title">
                    <i data-lucide="sparkles" style="width: 20px; height: 20px; color: var(--accent-primary);"></i>
                    <span>Developer Prompt Library</span>
                </div>
                <button type="button" class="modal-close-btn" onclick="window.closeModal('modal-prompt-library')">
                    <i data-lucide="x" style="width: 18px; height: 18px;"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="modal-prompt-list">
                    <div class="modal-prompt-item" onclick="window.usePromptTemplate('Generate a complete Laravel 11 CRUD architecture including Model with fillable & casts, Migration with foreign keys and indexes, Form Request with custom validation rules, and Resource Controller.')">
                        <div>
                            <div class="modal-prompt-title">⚡ Complete CRUD Architecture Scaffolder</div>
                            <div class="modal-prompt-desc">Generate model, migration, form request, policy, and API controller with clean separation.</div>
                        </div>
                        <i data-lucide="arrow-right" style="width: 16px; height: 16px; color: var(--accent-primary); flex-shrink: 0; margin-top: 4px;"></i>
                    </div>

                    <div class="modal-prompt-item" onclick="window.usePromptTemplate('Analyze this database schema and write optimized Eloquent queries, recommend composite indexes, and prevent N+1 lazy loading issues: ')">
                        <div>
                            <div class="modal-prompt-title">📊 Database & Eloquent Query Optimizer</div>
                            <div class="modal-prompt-desc">Paste your SQL schema or query to get indexed eager loading and query optimizations.</div>
                        </div>
                        <i data-lucide="arrow-right" style="width: 16px; height: 16px; color: var(--accent-primary); flex-shrink: 0; margin-top: 4px;"></i>
                    </div>

                    <div class="modal-prompt-item" onclick="window.usePromptTemplate('Review this PHP code for OWASP security vulnerabilities (SQL Injection, XSS, CSRF, Mass Assignment, Sensitive Data Exposure) and suggest production-grade hardening: ')">
                        <div>
                            <div class="modal-prompt-title">🔒 OWASP Code Security Audit</div>
                            <div class="modal-prompt-desc">Inspect code snippets for injection vulnerabilities, unauthorized access, and mass assignment leaks.</div>
                        </div>
                        <i data-lucide="arrow-right" style="width: 16px; height: 16px; color: var(--accent-primary); flex-shrink: 0; margin-top: 4px;"></i>
                    </div>

                    <div class="modal-prompt-item" onclick="window.usePromptTemplate('Write comprehensive Pest PHP tests (Unit and Feature) covering successful scenarios, validation errors, and unauthorized access: ')">
                        <div>
                            <div class="modal-prompt-title">🧪 Pest PHP Test Suite Generator</div>
                            <div class="modal-prompt-desc">Generate modern, expressive Pest tests with mock assertions and dataset providers.</div>
                        </div>
                        <i data-lucide="arrow-right" style="width: 16px; height: 16px; color: var(--accent-primary); flex-shrink: 0; margin-top: 4px;"></i>
                    </div>

                    <div class="modal-prompt-item" onclick="window.usePromptTemplate('Explain how to implement background Jobs, Queues with Redis, and retry backoff strategies in Laravel.')">
                        <div>
                            <div class="modal-prompt-title">⏱️ Redis Queues & Horizon Job Architecture</div>
                            <div class="modal-prompt-desc">Best practices for dispatching queued notifications, exponential backoffs, and failed job handling.</div>
                        </div>
                        <i data-lucide="arrow-right" style="width: 16px; height: 16px; color: var(--accent-primary); flex-shrink: 0; margin-top: 4px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Artisan Cheatsheet -->
    <div class="modal-overlay" id="modal-cheatsheet">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title">
                    <i data-lucide="terminal" style="width: 20px; height: 20px; color: var(--status-warning);"></i>
                    <span>Laravel Artisan Cheatsheet</span>
                </div>
                <button type="button" class="modal-close-btn" onclick="window.closeModal('modal-cheatsheet')">
                    <i data-lucide="x" style="width: 18px; height: 18px;"></i>
                </button>
            </div>
            <div class="modal-body">
                <input type="text" id="cheatsheet-search-input" class="cheatsheet-search" placeholder="Search commands (e.g. make:model, migrate, queue)...">

                <div id="cheatsheet-list">
                    <div class="cheatsheet-item">
                        <div>
                            <div class="cheatsheet-cmd">php artisan make:model Post -a</div>
                            <div class="cheatsheet-desc">Generate model, migration, factory, seeder, controller, request, and policy all-in-one.</div>
                        </div>
                        <button type="button" class="cheatsheet-use-btn" onclick="window.askCheatsheet('php artisan make:model Post -a')">Ask AI</button>
                    </div>

                    <div class="cheatsheet-item">
                        <div>
                            <div class="cheatsheet-cmd">php artisan optimize:clear</div>
                            <div class="cheatsheet-desc">Clear config, route, view, and compiled class caches.</div>
                        </div>
                        <button type="button" class="cheatsheet-use-btn" onclick="window.askCheatsheet('php artisan optimize:clear')">Ask AI</button>
                    </div>

                    <div class="cheatsheet-item">
                        <div>
                            <div class="cheatsheet-cmd">php artisan migrate:fresh --seed</div>
                            <div class="cheatsheet-desc">Drop all database tables, re-run all migrations, and seed sample data.</div>
                        </div>
                        <button type="button" class="cheatsheet-use-btn" onclick="window.askCheatsheet('php artisan migrate:fresh --seed')">Ask AI</button>
                    </div>

                    <div class="cheatsheet-item">
                        <div>
                            <div class="cheatsheet-cmd">php artisan route:list --except-vendor</div>
                            <div class="cheatsheet-desc">Display all registered application routes cleanly excluding package vendor routes.</div>
                        </div>
                        <button type="button" class="cheatsheet-use-btn" onclick="window.askCheatsheet('php artisan route:list --except-vendor')">Ask AI</button>
                    </div>

                    <div class="cheatsheet-item">
                        <div>
                            <div class="cheatsheet-cmd">php artisan queue:work --tries=3 --timeout=90</div>
                            <div class="cheatsheet-desc">Process queued jobs with maximum 3 retry attempts and 90s timeout.</div>
                        </div>
                        <button type="button" class="cheatsheet-use-btn" onclick="window.askCheatsheet('php artisan queue:work')">Ask AI</button>
                    </div>

                    <div class="cheatsheet-item">
                        <div>
                            <div class="cheatsheet-cmd">php artisan make:request StorePostRequest</div>
                            <div class="cheatsheet-desc">Create a Form Request class with authorize() and rules() methods.</div>
                        </div>
                        <button type="button" class="cheatsheet-use-btn" onclick="window.askCheatsheet('php artisan make:request StorePostRequest')">Ask AI</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Settings -->
    <div class="modal-overlay" id="modal-settings">
        <div class="modal-card" style="max-width: 480px;">
            <div class="modal-header">
                <div class="modal-title">
                    <i data-lucide="settings" style="width: 20px; height: 20px; color: var(--text-primary);"></i>
                    <span>Assistant Settings</span>
                </div>
                <button type="button" class="modal-close-btn" onclick="window.closeModal('modal-settings')">
                    <i data-lucide="x" style="width: 18px; height: 18px;"></i>
                </button>
            </div>
            <div class="modal-body">
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 0.82rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px;">Active AI Model</label>
                    <div style="padding: 12px 14px; border-radius: 10px; background: var(--bg-elevated); border: 1px solid var(--border-default); display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <div style="font-weight: 600; font-size: 0.92rem; color: var(--text-primary);">Groq Llama 3.3 70B</div>
                            <div style="font-size: 0.78rem; color: var(--text-muted);">Ultra-low latency LPU Inference</div>
                        </div>
                        <span style="font-size: 0.72rem; padding: 3px 8px; border-radius: 12px; background: rgba(16, 185, 129, 0.15); color: var(--status-success); font-weight: 600;">ACTIVE</span>
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 0.82rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px;">Quota Telemetry</label>
                    <div style="padding: 12px 14px; border-radius: 10px; background: var(--bg-elevated); border: 1px solid var(--border-default);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 0.85rem;">
                            <span style="color: var(--text-muted);">Daily Limit:</span>
                            <span style="font-weight: 600;">1,500 requests</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem;">
                            <span style="color: var(--text-muted);">Rate Limit (RPM):</span>
                            <span style="font-weight: 600;">30 requests / min</span>
                        </div>
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 0.82rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px;">Developer Info</label>
                    <div style="padding: 12px 14px; border-radius: 10px; background: var(--bg-elevated); border: 1px solid var(--border-default); font-size: 0.82rem; color: var(--text-muted); line-height: 1.5;">
                        Developed by <strong style="color: var(--text-primary);">Pial Mahmud</strong> • Full-Stack Web Engineer. Powered by Laravel 11, Groq, and Tailwind CSS.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Rename Conversation -->
    <div class="modal-overlay" id="modal-rename">
        <div class="modal-card" style="max-width: 420px;">
            <div class="modal-header">
                <div class="modal-title">
                    <i data-lucide="pencil" style="width: 18px; height: 18px;"></i>
                    <span>Rename Chat</span>
                </div>
                <button type="button" class="modal-close-btn" onclick="window.closeModal('modal-rename')">
                    <i data-lucide="x" style="width: 18px; height: 18px;"></i>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="rename-conversation-id">
                <input type="text" id="rename-title-input" class="auth-input" placeholder="Enter new chat title..." maxlength="60" style="margin-bottom: 14px;">
                <div style="display: flex; justify-content: flex-end; gap: 8px;">
                    <button type="button" class="icon-btn" style="width: auto; padding: 8px 14px;" onclick="window.closeModal('modal-rename')">Cancel</button>
                    <button type="button" class="sidebar-action-btn" style="width: auto; padding: 8px 18px;" id="save-rename-btn">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast-box">
        <i data-lucide="check-circle" style="width: 16px; height: 16px; color: var(--status-success);"></i>
        <span id="toast-msg">Action completed</span>
    </div>
</body>
</html>
