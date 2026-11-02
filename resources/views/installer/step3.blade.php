@extends('installer.layout')

@section('content')
<div>
    <h2 style="font-size: 1.1rem; font-weight: 600; color: var(--text-primary); margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
        <span style="width: 22px; height: 22px; border-radius: 50%; background: var(--accent-subtle); color: var(--accent-primary); display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">4</span>
        Create Administrator & AI Provider
    </h2>
    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 20px;">
        Set up your primary administrator account and configure your initial AI API key.
    </p>

    <form action="{{ route('install.step3.process') }}" method="POST">
        @csrf

        <div class="auth-form-group">
            <label class="auth-label">Admin Full Name</label>
            <input type="text" name="name" class="auth-input" placeholder="Admin User" value="{{ old('name', 'Admin User') }}" required>
        </div>

        <div class="auth-form-group">
            <label class="auth-label">Admin Email Address</label>
            <input type="email" name="email" class="auth-input" placeholder="admin@example.com" value="{{ old('email') }}" required>
        </div>

        <div class="auth-form-group">
            <label class="auth-label">Admin Password</label>
            <input type="password" name="password" class="auth-input" placeholder="Minimum 8 characters" required>
        </div>

        <div class="auth-form-group">
            <label class="auth-label">Confirm Password</label>
            <input type="password" name="password_confirmation" class="auth-input" placeholder="Repeat password" required>
        </div>

        <div class="auth-form-group" style="margin-top: 16px;">
            <label class="auth-label">Default AI Provider</label>
            <select name="ai_provider" class="auth-input" style="cursor: pointer;">
                <option value="groq" selected>Groq (Ultra-Fast LPU - Recommended)</option>
                <option value="openai">OpenAI (GPT-4o / GPT-4o-mini)</option>
                <option value="deepseek">DeepSeek (V3 / R1 Reasoning)</option>
                <option value="gemini">Google Gemini (Flash)</option>
            </select>
        </div>

        <div class="auth-form-group">
            <label class="auth-label">Provider API Key (Optional now, can add later in Admin)</label>
            <input type="password" name="api_key" class="auth-input" placeholder="gsk_... or sk-...">
        </div>

        <button type="submit" class="auth-submit-btn" style="margin-top: 16px;">
            <span>Complete Installation & Lock &rarr;</span>
        </button>
    </form>
</div>
@endsection
