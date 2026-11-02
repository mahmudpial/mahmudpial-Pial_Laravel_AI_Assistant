@extends('admin.layout')

@section('title', 'Overview')

@section('content')
<div>
    <h1 style="font-family: var(--font-heading); font-size: 1.5rem; font-weight: 700; margin-bottom: 20px;">System Overview</h1>

    <!-- Stats Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 28px;">
        <div class="admin-card">
            <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Total Users</div>
            <div class="admin-stat-num">{{ number_format($stats['total_users']) }}</div>
        </div>
        <div class="admin-card">
            <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Total Conversations</div>
            <div class="admin-stat-num">{{ number_format($stats['total_conversations']) }}</div>
        </div>
        <div class="admin-card">
            <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Total Messages</div>
            <div class="admin-stat-num">{{ number_format($stats['total_messages']) }}</div>
        </div>
        <div class="admin-card">
            <div style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Active AI Provider</div>
            <div style="font-size: 1.15rem; font-weight: 700; color: var(--accent-primary); margin-top: 10px; text-transform: capitalize;">
                {{ $stats['active_provider'] }}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted);">Model: {{ $stats['active_model'] }}</div>
        </div>
    </div>

    <!-- Tables Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 20px;">
        <div class="admin-card">
            <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 14px;">Recent Registered Users</h3>
            <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-default); text-align: left; color: var(--text-muted);">
                        <th style="padding: 8px 4px;">Name</th>
                        <th style="padding: 8px 4px;">Role</th>
                        <th style="padding: 8px 4px;">Joined</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentUsers as $u)
                        <tr style="border-bottom: 1px solid var(--border-subtle);">
                            <td style="padding: 10px 4px; font-weight: 600;">{{ $u->name }}</td>
                            <td style="padding: 10px 4px;">
                                <span style="padding: 2px 8px; border-radius: 6px; font-size: 0.72rem; font-weight: 600; background: {{ $u->role === 'admin' ? 'rgba(99,102,241,0.15)' : 'rgba(0,0,0,0.05)' }}; color: {{ $u->role === 'admin' ? 'var(--accent-primary)' : 'var(--text-secondary)' }};">
                                    {{ strtoupper($u->role) }}
                                </span>
                            </td>
                            <td style="padding: 10px 4px; color: var(--text-muted);">{{ $u->created_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="admin-card">
            <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 14px;">Recent Active Chats</h3>
            <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-default); text-align: left; color: var(--text-muted);">
                        <th style="padding: 8px 4px;">Chat Title</th>
                        <th style="padding: 8px 4px;">User</th>
                        <th style="padding: 8px 4px;">Updated</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentConversations as $c)
                        <tr style="border-bottom: 1px solid var(--border-subtle);">
                            <td style="padding: 10px 4px; font-weight: 600;">{{ Str::limit($c->title, 24) }}</td>
                            <td style="padding: 10px 4px; color: var(--text-secondary);">{{ $c->user->name ?? 'User' }}</td>
                            <td style="padding: 10px 4px; color: var(--text-muted);">{{ $c->updated_at->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
