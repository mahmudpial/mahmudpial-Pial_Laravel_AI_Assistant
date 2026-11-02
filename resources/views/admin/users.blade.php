@extends('admin.layout')

@section('title', 'User Management')

@section('content')
<div>
    <h1 style="font-family: var(--font-heading); font-size: 1.5rem; font-weight: 700; margin-bottom: 20px;">User Management</h1>

    <div class="admin-card">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
            <thead>
                <tr style="border-bottom: 1px solid var(--border-default); text-align: left; color: var(--text-muted);">
                    <th style="padding: 12px 8px;">Name</th>
                    <th style="padding: 12px 8px;">Email</th>
                    <th style="padding: 12px 8px;">Role</th>
                    <th style="padding: 12px 8px;">Total Chats</th>
                    <th style="padding: 12px 8px;">Registered</th>
                    <th style="padding: 12px 8px; text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr style="border-bottom: 1px solid var(--border-subtle);">
                        <td style="padding: 12px 8px; font-weight: 600;">{{ $user->name }}</td>
                        <td style="padding: 12px 8px; color: var(--text-secondary);">{{ $user->email }}</td>
                        <td style="padding: 12px 8px;">
                            <span style="padding: 3px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; background: {{ $user->role === 'admin' ? 'rgba(99,102,241,0.15)' : 'rgba(0,0,0,0.05)' }}; color: {{ $user->role === 'admin' ? 'var(--accent-primary)' : 'var(--text-secondary)' }};">
                                {{ strtoupper($user->role) }}
                            </span>
                        </td>
                        <td style="padding: 12px 8px;">{{ $user->conversations_count }}</td>
                        <td style="padding: 12px 8px; color: var(--text-muted);">{{ $user->created_at->format('M d, Y') }}</td>
                        <td style="padding: 12px 8px; text-align: right;">
                            @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.toggle-role', $user->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="icon-btn" style="width: auto; padding: 4px 10px; font-size: 0.75rem;">
                                        Make {{ $user->role === 'admin' ? 'User' : 'Admin' }}
                                    </button>
                                </form>
                            @else
                                <span style="font-size: 0.75rem; color: var(--text-muted);">(You)</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top: 20px;">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
