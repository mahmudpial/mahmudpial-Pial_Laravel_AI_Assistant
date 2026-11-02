@extends('installer.layout')

@section('content')
<div style="text-align: center;">
    <div style="width: 56px; height: 56px; border-radius: 50%; background: rgba(16, 185, 129, 0.15); color: var(--status-success); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
        <i data-lucide="check-circle" style="width: 32px; height: 32px;"></i>
    </div>

    <h2 style="font-size: 1.3rem; font-weight: 700; color: var(--text-primary); margin-bottom: 8px;">Installation Complete!</h2>
    <p style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.5; margin-bottom: 24px;">
        Your Laravel AI Assistant has been successfully installed, migrated, and locked. You can now log in with your administrator account.
    </p>

    <a href="{{ route('auth.login') }}" class="auth-submit-btn" style="display: block; text-align: center; text-decoration: none;">
        <span>Go to Sign In &rarr;</span>
    </a>
</div>
@endsection
