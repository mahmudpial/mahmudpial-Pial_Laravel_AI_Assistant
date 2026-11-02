@extends('installer.layout')

@section('content')
<div>
    <h2 style="font-size: 1.1rem; font-weight: 600; color: var(--text-primary); margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
        <span style="width: 22px; height: 22px; border-radius: 50%; background: var(--accent-subtle); color: var(--accent-primary); display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">3</span>
        Database Setup & Migrations
    </h2>
    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 20px;">
        Click below to run automated schema migrations and initialize tables for users, conversations, messages, and settings.
    </p>

    <div style="background: var(--bg-elevated); border: 1px solid var(--border-default); border-radius: 12px; padding: 16px; margin-bottom: 24px;">
        <div style="font-size: 0.85rem; margin-bottom: 6px; display: flex; justify-content: space-between;">
            <span style="color: var(--text-muted);">Database Driver:</span>
            <span style="font-weight: 600; font-family: var(--font-mono);">{{ config('database.default') }}</span>
        </div>
        <div style="font-size: 0.85rem; display: flex; justify-content: space-between;">
            <span style="color: var(--text-muted);">Status:</span>
            <span style="color: var(--status-success); font-weight: 600;">Ready to migrate</span>
        </div>
    </div>

    <form action="{{ route('install.step2.process') }}" method="POST">
        @csrf
        <button type="submit" class="auth-submit-btn">
            <span>Run Database Migrations &rarr;</span>
        </button>
    </form>
</div>
@endsection
