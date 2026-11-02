@extends('installer.layout')

@section('content')
<div style="margin-bottom: 20px;">
    <h2 style="font-size: 1.1rem; font-weight: 600; color: var(--text-primary); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
        <span style="width: 22px; height: 22px; border-radius: 50%; background: var(--accent-subtle); color: var(--accent-primary); display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">1</span>
        Server Requirements Check
    </h2>

    <div style="background: var(--bg-elevated); border: 1px solid var(--border-default); border-radius: 12px; padding: 12px 16px; margin-bottom: 20px;">
        @foreach($requirements as $name => $ok)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid var(--border-subtle); font-size: 0.85rem;">
                <span style="color: var(--text-secondary);">{{ $name }}</span>
                @if($ok)
                    <span style="color: var(--status-success); display: flex; align-items: center; gap: 4px; font-weight: 600;">
                        <i data-lucide="check" style="width: 15px; height: 15px;"></i> OK
                    </span>
                @else
                    <span style="color: var(--status-danger); display: flex; align-items: center; gap: 4px; font-weight: 600;">
                        <i data-lucide="x" style="width: 15px; height: 15px;"></i> MISSING
                    </span>
                @endif
            </div>
        @endforeach
    </div>

    <h2 style="font-size: 1.1rem; font-weight: 600; color: var(--text-primary); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
        <span style="width: 22px; height: 22px; border-radius: 50%; background: var(--accent-subtle); color: var(--accent-primary); display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem;">2</span>
        Directory Permissions
    </h2>

    <div style="background: var(--bg-elevated); border: 1px solid var(--border-default); border-radius: 12px; padding: 12px 16px; margin-bottom: 24px;">
        @foreach($permissions as $name => $ok)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid var(--border-subtle); font-size: 0.85rem;">
                <span style="color: var(--text-secondary);">{{ $name }}</span>
                @if($ok)
                    <span style="color: var(--status-success); display: flex; align-items: center; gap: 4px; font-weight: 600;">
                        <i data-lucide="check" style="width: 15px; height: 15px;"></i> OK
                    </span>
                @else
                    <span style="color: var(--status-danger); display: flex; align-items: center; gap: 4px; font-weight: 600;">
                        <i data-lucide="x" style="width: 15px; height: 15px;"></i> NOT WRITABLE
                    </span>
                @endif
            </div>
        @endforeach
    </div>

    @if($allOk)
        <a href="{{ route('install.step2') }}" class="auth-submit-btn" style="display: block; text-align: center; text-decoration: none;">
            <span>Next: Database Setup &rarr;</span>
        </a>
    @else
        <div style="text-align: center; color: var(--status-danger); font-size: 0.85rem;">
            Please fix the missing server requirements or folder permissions before continuing.
        </div>
    @endif
</div>
@endsection
