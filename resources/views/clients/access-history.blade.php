@extends('layouts.aceguard')

@section('title', 'Access History')
@section('page-title', 'Access History')
@section('page-subtitle', 'Access changes for ' . $client->company_name . '.')

@push('styles')
<style>
.ag-audit {
    background: white; padding: 24px; border-radius: 20px;
    border: 1px solid #e2e8f0;
}
.ag-audit-scroll { overflow-x: auto; }
.ag-audit table { width: 100%; border-collapse: collapse; }
.ag-audit th, .ag-audit td {
    padding: 14px; text-align: left;
    border-bottom: 1px solid #e2e8f0; vertical-align: top;
}
.ag-audit th { background: #f8fafc; }
.ag-audit small { display: block; color: #64748b; margin-top: 5px; }
.ag-audit-links {
    display: flex; flex-wrap: wrap; gap: 18px;
    margin-bottom: 24px;
}
.ag-audit-pagination {
    display: flex; justify-content: space-between;
    gap: 16px; margin-top: 24px;
}
.ag-audit a { color: #1d4ed8; }
</style>
@endpush

@section('content')
@php
$labels = [
    'workspace.member_added' => 'Member added',
    'workspace.member_removed' => 'Member removed',
    'workspace.role_changed' => 'Member role changed',
    'invitation.created' => 'Invitation created',
    'invitation.replaced' => 'Invitation replaced',
    'invitation.cancelled' => 'Invitation cancelled',
    'invitation.accepted' => 'Invitation accepted',
    'invitation.delivery_failed' => 'Invitation delivery failed',
];
@endphp

<div class="ag-audit">
    <div class="ag-audit-links">
        <a href="{{ route('clients.show', $client) }}">Back to workspace</a>
        <a href="{{ route('clients.members.index', $client) }}">Manage members</a>
    </div>

    <h2>Workspace access history</h2>
    <p>Records begin when audit recording was introduced. Earlier changes are not included.</p>

    <div class="ag-audit-scroll">
        <table>
            <thead>
                <tr>
                    <th scope="col">Date (UTC)</th>
                    <th scope="col">Event</th>
                    <th scope="col">Performed by</th>
                    <th scope="col">Affected account / invitation</th>
                    <th scope="col">Role change</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($events as $event)
                <tr>
                    <td>{{ $event->created_at->utc()->format('d M Y H:i:s') }}</td>
                    <td>
                        {{ $labels[$event->action] ?? $event->action }}
                        <small>Event #{{ $event->id }} · {{ strtoupper($event->source) }}</small>
                    </td>
                    <td>
                        @if ($event->actor_id !== null)
                            {{ $event->actor?->name ?? 'Deleted account' }}
                            <small>User #{{ $event->actor_id }}</small>
                        @elseif ($event->source === 'cli')
                            Server command
                            <small>No signed-in BOS user recorded</small>
                        @else
                            Invitation acceptance
                            <small>No signed-in BOS user recorded</small>
                        @endif
                    </td>
                    <td>
                        @if ($event->subject_user_id !== null)
                            {{ $event->subject?->name ?? 'Deleted account' }}
                            <small>User #{{ $event->subject_user_id }}</small>
                        @endif

                        @if ($event->invitation_id !== null)
                            <small>Invitation #{{ $event->invitation_id }}</small>
                        @endif

                        @if ($event->subject_user_id === null && $event->invitation_id === null)
                            —
                        @endif
                    </td>
                    <td>
                        {{ $event->previous_role ? ucfirst($event->previous_role) : 'None' }}
                        →
                        {{ $event->new_role ? ucfirst($event->new_role) : 'None' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5">No access events recorded for this workspace yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <nav class="ag-audit-pagination" aria-label="Access history pages">
        <span>
            @if ($events->previousPageUrl())
                <a href="{{ $events->previousPageUrl() }}">Previous</a>
            @endif
        </span>
        <span>Page {{ $events->currentPage() }} of {{ $events->lastPage() }}</span>
        <span>
            @if ($events->nextPageUrl())
                <a href="{{ $events->nextPageUrl() }}">Next</a>
            @endif
        </span>
    </nav>
</div>
@endsection
