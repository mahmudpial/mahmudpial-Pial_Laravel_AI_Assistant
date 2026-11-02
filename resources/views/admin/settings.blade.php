@extends('admin.layout')

@section('title', 'AI & System Settings')

@section('content')
<div style="max-width: 820px; padding-bottom: 60px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <div>
            <h1 style="font-family: var(--font-heading); font-size: 1.6rem; font-weight: 700; margin-bottom: 4px;">AI Provider & System Settings</h1>
            <p style="font-size: 0.85rem; color: var(--text-muted);">Manage AI model configurations, API credentials, and developer prompts.</p>
        </div>

        <button type="button" id="test-connection-btn" class="sidebar-action-btn" style="width: auto; padding: 9px 18px; font-size: 0.85rem; background: var(--bg-surface); border: 1px solid var(--border-default); color: var(--text-primary);">
            <i data-lucide="zap" style="width: 15px; height: 15px; color: var(--accent-primary);"></i>
            <span>Test Active API</span>
        </button>
    </div>

    <!-- Test Result Notification Area -->
    <div id="test-result-box" style="display: none; padding: 14px 18px; border-radius: 12px; margin-bottom: 22px; font-size: 0.88rem;"></div>

    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf

        <!-- 1. Active Model Selection Card -->
        <div class="admin-card" style="margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
                <div style="width: 28px; height: 28px; border-radius: 6px; background: rgba(99, 102, 241, 0.15); color: var(--accent-primary); display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="cpu" style="width: 16px; height: 16px;"></i>
                </div>
                <h3 style="font-size: 1.05rem; font-weight: 600;">Active Model Selection</h3>
            </div>

            <div class="auth-form-group" style="margin-bottom: 18px;">
                <label class="auth-label" for="provider-select">Default AI Provider</label>
                <select name="ai_provider" class="auth-input" id="provider-select" style="cursor: pointer;">
                    @foreach($providers as $slug => $info)
                        <option value="{{ $slug }}" {{ $activeProvider === $slug ? 'selected' : '' }}>
                            {{ $info['name'] }}
                        </option>
                    @endforeach
                </select>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">Choose the primary LLM engine for user chat sessions.</div>
            </div>

            <div class="auth-form-group" style="margin-bottom: 8px;">
                <label class="auth-label" for="model-input">Active Model Identifier</label>
                <input type="text" id="model-input" name="ai_model" class="auth-input" value="{{ old('ai_model', $activeModel) }}" placeholder="e.g. openai/gpt-oss-120b, gpt-4o-mini, gemini-1.5-flash" required>
            </div>

            <!-- Suggested Models Badges -->
            <div style="margin-top: 10px;">
                <span style="font-size: 0.75rem; color: var(--text-muted); margin-right: 8px;">Recommended for selected provider:</span>
                <span id="model-pills" style="display: inline-flex; flex-wrap: wrap; gap: 6px; margin-top: 6px;"></span>
            </div>
        </div>

        <!-- 2. API Keys Management Card -->
        <div class="admin-card" style="margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
                <div style="width: 28px; height: 28px; border-radius: 6px; background: rgba(16, 185, 129, 0.15); color: var(--status-success); display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="key" style="width: 16px; height: 16px;"></i>
                </div>
                <h3 style="font-size: 1.05rem; font-weight: 600;">API Keys Management</h3>
            </div>
            <p style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 18px;">
                Keys entered here override your <code style="font-size: 0.8rem; background: var(--bg-canvas); padding: 2px 6px; border-radius: 4px;">.env</code> file. Leave blank to retain existing <code style="font-size: 0.8rem; background: var(--bg-canvas); padding: 2px 6px; border-radius: 4px;">.env</code> values.
            </p>

            <!-- Groq API Key -->
            <div class="auth-form-group" style="margin-bottom: 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label class="auth-label" style="margin-bottom: 0;">Groq API Key (gsk_...)</label>
                    <span style="font-size: 0.72rem; padding: 2px 8px; border-radius: 10px; {{ !empty($keys['groq']) ? 'background: rgba(16, 185, 129, 0.15); color: var(--status-success);' : 'background: rgba(245, 158, 11, 0.15); color: var(--status-warning);' }}">
                        {{ !empty($keys['groq']) ? '● Configured' : '○ Missing' }}
                    </span>
                </div>
                <div style="position: relative;">
                    <input type="password" name="groq_api_key" id="groq-key-input" class="auth-input" value="{{ old('groq_api_key', $keys['groq']) }}" placeholder="gsk_..." style="padding-right: 42px;">
                    <button type="button" onclick="toggleSecretVisibility('groq-key-input', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 4px;">
                        <i data-lucide="eye" style="width: 16px; height: 16px;"></i>
                    </button>
                </div>
            </div>

            <!-- OpenAI API Key -->
            <div class="auth-form-group" style="margin-bottom: 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label class="auth-label" style="margin-bottom: 0;">OpenAI API Key (sk-...)</label>
                    <span style="font-size: 0.72rem; padding: 2px 8px; border-radius: 10px; {{ !empty($keys['openai']) ? 'background: rgba(16, 185, 129, 0.15); color: var(--status-success);' : 'background: rgba(245, 158, 11, 0.15); color: var(--status-warning);' }}">
                        {{ !empty($keys['openai']) ? '● Configured' : '○ Not set' }}
                    </span>
                </div>
                <div style="position: relative;">
                    <input type="password" name="openai_api_key" id="openai-key-input" class="auth-input" value="{{ old('openai_api_key', $keys['openai']) }}" placeholder="sk-..." style="padding-right: 42px;">
                    <button type="button" onclick="toggleSecretVisibility('openai-key-input', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 4px;">
                        <i data-lucide="eye" style="width: 16px; height: 16px;"></i>
                    </button>
                </div>
            </div>

            <!-- DeepSeek API Key -->
            <div class="auth-form-group" style="margin-bottom: 18px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label class="auth-label" style="margin-bottom: 0;">DeepSeek API Key (sk-...)</label>
                    <span style="font-size: 0.72rem; padding: 2px 8px; border-radius: 10px; {{ !empty($keys['deepseek']) ? 'background: rgba(16, 185, 129, 0.15); color: var(--status-success);' : 'background: rgba(245, 158, 11, 0.15); color: var(--status-warning);' }}">
                        {{ !empty($keys['deepseek']) ? '● Configured' : '○ Not set' }}
                    </span>
                </div>
                <div style="position: relative;">
                    <input type="password" name="deepseek_api_key" id="deepseek-key-input" class="auth-input" value="{{ old('deepseek_api_key', $keys['deepseek']) }}" placeholder="sk-..." style="padding-right: 42px;">
                    <button type="button" onclick="toggleSecretVisibility('deepseek-key-input', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 4px;">
                        <i data-lucide="eye" style="width: 16px; height: 16px;"></i>
                    </button>
                </div>
            </div>

            <!-- Google Gemini API Key -->
            <div class="auth-form-group" style="margin-bottom: 6px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label class="auth-label" style="margin-bottom: 0;">Google Gemini API Key (AIza...)</label>
                    <span style="font-size: 0.72rem; padding: 2px 8px; border-radius: 10px; {{ !empty($keys['gemini']) ? 'background: rgba(16, 185, 129, 0.15); color: var(--status-success);' : 'background: rgba(245, 158, 11, 0.15); color: var(--status-warning);' }}">
                        {{ !empty($keys['gemini']) ? '● Configured' : '○ Not set' }}
                    </span>
                </div>
                <div style="position: relative;">
                    <input type="password" name="gemini_api_key" id="gemini-key-input" class="auth-input" value="{{ old('gemini_api_key', $keys['gemini']) }}" placeholder="AIza..." style="padding-right: 42px;">
                    <button type="button" onclick="toggleSecretVisibility('gemini-key-input', this)" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 4px;">
                        <i data-lucide="eye" style="width: 16px; height: 16px;"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- 3. System Instructions Card -->
        <div class="admin-card" style="margin-bottom: 28px;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
                <div style="width: 28px; height: 28px; border-radius: 6px; background: rgba(236, 72, 153, 0.15); color: #ec4899; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="terminal" style="width: 16px; height: 16px;"></i>
                </div>
                <h3 style="font-size: 1.05rem; font-weight: 600;">System Persona Instructions</h3>
            </div>

            <div class="auth-form-group">
                <textarea name="system_prompt" class="auth-input" rows="6" style="resize: vertical; font-family: var(--font-primary); font-size: 0.88rem; line-height: 1.5;" required>{{ old('system_prompt', $systemPrompt) }}</textarea>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 6px;">This instruction dictates the AI assistant's persona, formatting rules, and developer tone for all users.</div>
            </div>
        </div>

        <button type="submit" class="sidebar-action-btn" style="width: auto; padding: 13px 32px; font-size: 0.95rem;">
            <i data-lucide="check" style="width: 18px; height: 18px;"></i>
            <span>Save Settings</span>
        </button>
    </form>
</div>

<script>
    const providerModels = {
        'groq': ['openai/gpt-oss-120b', 'qwen/qwen3.8-27b', 'openai/gpt-oss-20b', 'llama-3.3-70b-versatile'],
        'openai': ['gpt-4o-mini', 'gpt-4o', 'gpt-3.5-turbo'],
        'deepseek': ['deepseek-chat', 'deepseek-reasoner'],
        'gemini': ['gemini-1.5-flash', 'gemini-2.0-flash']
    };

    const providerSelect = document.getElementById('provider-select');
    const modelInput = document.getElementById('model-input');
    const modelPills = document.getElementById('model-pills');
    const testBtn = document.getElementById('test-connection-btn');
    const testResultBox = document.getElementById('test-result-box');

    function updateModelPills(provider) {
        if (!modelPills) return;
        modelPills.innerHTML = '';
        const list = providerModels[provider] || [];

        list.forEach(modelName => {
            const pill = document.createElement('button');
            pill.type = 'button';
            pill.textContent = modelName;
            pill.style.cssText = 'background: var(--bg-surface); border: 1px solid var(--border-default); border-radius: 6px; padding: 3px 9px; font-size: 0.74rem; font-family: monospace; color: var(--text-secondary); cursor: pointer; transition: all 0.2s;';
            pill.onmouseenter = () => { pill.style.borderColor = 'var(--accent-primary)'; pill.style.color = 'var(--accent-primary)'; };
            pill.onmouseleave = () => { pill.style.borderColor = 'var(--border-default)'; pill.style.color = 'var(--text-secondary)'; };
            pill.onclick = () => {
                modelInput.value = modelName;
            };
            modelPills.appendChild(pill);
        });
    }

    if (providerSelect) {
        updateModelPills(providerSelect.value);

        providerSelect.addEventListener('change', function () {
            const currentProvider = this.value;
            updateModelPills(currentProvider);
            const defaults = providerModels[currentProvider];
            if (defaults && defaults.length > 0) {
                modelInput.value = defaults[0];
            }
        });
    }

    function toggleSecretVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const icon = btn.querySelector('svg');
        if (input.type === 'password') {
            input.type = 'text';
            btn.innerHTML = '<i data-lucide="eye-off" style="width: 16px; height: 16px;"></i>';
        } else {
            input.type = 'password';
            btn.innerHTML = '<i data-lucide="eye" style="width: 16px; height: 16px;"></i>';
        }
        if (window.lucide) lucide.createIcons();
    }

    if (testBtn) {
        testBtn.addEventListener('click', async () => {
            testBtn.disabled = true;
            testBtn.innerHTML = '<i data-lucide="loader-2" style="width: 15px; height: 15px; animation: spin 1s linear infinite;"></i><span>Testing...</span>';
            if (window.lucide) lucide.createIcons();

            testResultBox.style.display = 'block';
            testResultBox.style.background = 'rgba(99, 102, 241, 0.1)';
            testResultBox.style.border = '1px solid rgba(99, 102, 241, 0.2)';
            testResultBox.style.color = 'var(--text-primary)';
            testResultBox.innerHTML = 'Connecting to active AI provider in real-time...';

            try {
                const response = await fetch('{{ route("admin.settings.test") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    testResultBox.style.background = 'rgba(16, 185, 129, 0.12)';
                    testResultBox.style.border = '1px solid rgba(16, 185, 129, 0.3)';
                    testResultBox.style.color = 'var(--status-success)';
                    testResultBox.innerHTML = `<strong>✅ Success!</strong> Connected to <strong>${data.provider.toUpperCase()}</strong> (${data.model}). Response: "<em>${data.reply}</em>"`;
                } else {
                    testResultBox.style.background = 'rgba(239, 68, 68, 0.12)';
                    testResultBox.style.border = '1px solid rgba(239, 68, 68, 0.3)';
                    testResultBox.style.color = 'var(--status-danger)';
                    testResultBox.innerHTML = `<strong>❌ Connection Failed!</strong> ${data.error || 'Unknown error occurred.'}`;
                }
            } catch (err) {
                testResultBox.style.background = 'rgba(239, 68, 68, 0.12)';
                testResultBox.style.border = '1px solid rgba(239, 68, 68, 0.3)';
                testResultBox.style.color = 'var(--status-danger)';
                testResultBox.innerHTML = `<strong>❌ Request Error:</strong> ${err.message}`;
            } finally {
                testBtn.disabled = false;
                testBtn.innerHTML = '<i data-lucide="zap" style="width: 15px; height: 15px; color: var(--accent-primary);"></i><span>Test Active API</span>';
                if (window.lucide) lucide.createIcons();
            }
        });
    }
</script>
@endsection

