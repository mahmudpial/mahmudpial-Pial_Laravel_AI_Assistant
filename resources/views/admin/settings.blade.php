@extends('admin.layout')

@section('title', 'AI & System Settings')

@section('content')
<div style="max-width: 780px;">
    <h1 style="font-family: var(--font-heading); font-size: 1.5rem; font-weight: 700; margin-bottom: 20px;">AI Provider & System Settings</h1>

    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf

        <div class="admin-card" style="margin-bottom: 20px;">
            <h3 style="font-size: 1.05rem; font-weight: 600; margin-bottom: 14px;">Active Model Selection</h3>

            <div class="auth-form-group">
                <label class="auth-label">Default AI Provider</label>
                <select name="ai_provider" class="auth-input" id="provider-select" style="cursor: pointer;">
                    @foreach($providers as $slug => $info)
                        <option value="{{ $slug }}" {{ $activeProvider === $slug ? 'selected' : '' }}>
                            {{ $info['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="auth-form-group">
                <label class="auth-label">Active Model Identifier</label>
                <input type="text" name="ai_model" class="auth-input" value="{{ old('ai_model', $activeModel) }}" placeholder="e.g. llama-3.3-70b-versatile, gpt-4o-mini, deepseek-chat" required>
            </div>
        </div>

        <div class="admin-card" style="margin-bottom: 20px;">
            <h3 style="font-size: 1.05rem; font-weight: 600; margin-bottom: 14px;">API Keys Management</h3>

            <div class="auth-form-group">
                <label class="auth-label">Groq API Key (gsk_...)</label>
                <input type="password" name="groq_api_key" class="auth-input" value="{{ old('groq_api_key', $keys['groq']) }}" placeholder="Leave blank to use .env GROQ_API_KEY">
            </div>

            <div class="auth-form-group">
                <label class="auth-label">OpenAI API Key (sk-...)</label>
                <input type="password" name="openai_api_key" class="auth-input" value="{{ old('openai_api_key', $keys['openai']) }}" placeholder="sk-...">
            </div>

            <div class="auth-form-group">
                <label class="auth-label">DeepSeek API Key (sk-...)</label>
                <input type="password" name="deepseek_api_key" class="auth-input" value="{{ old('deepseek_api_key', $keys['deepseek']) }}" placeholder="sk-...">
            </div>

            <div class="auth-form-group">
                <label class="auth-label">Google Gemini API Key (AIza...)</label>
                <input type="password" name="gemini_api_key" class="auth-input" value="{{ old('gemini_api_key', $keys['gemini']) }}" placeholder="AIza...">
            </div>
        </div>

        <div class="admin-card" style="margin-bottom: 24px;">
            <h3 style="font-size: 1.05rem; font-weight: 600; margin-bottom: 14px;">Default System Instructions</h3>

            <div class="auth-form-group">
                <label class="auth-label">System Persona Prompt</label>
                <textarea name="system_prompt" class="auth-input" rows="5" style="resize: vertical; font-family: var(--font-primary); font-size: 0.88rem;" required>{{ old('system_prompt', $systemPrompt) }}</textarea>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">This instruction dictates the AI assistant's persona, formatting rules, and developer tone.</div>
            </div>
        </div>

        <button type="submit" class="sidebar-action-btn" style="width: auto; padding: 12px 28px; font-size: 0.95rem;">
            <span>Save Settings</span>
        </button>
    </form>
</div>
@endsection
