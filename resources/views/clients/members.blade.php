@extends('layouts.aceguard')

@section('title', 'Workspace Members')
@section('page-title', 'Workspace Members')
@section('page-subtitle', 'Manage access to ' . $client->company_name . '.')

@push('styles')
<style>
.ag-members-card {
    background: white; padding: 24px; border-radius: 20px;
    border: 1px solid #e2e8f0; margin-bottom: 24px;
}
.ag-members-table { width: 100%; border-collapse: collapse; }
.ag-members-table th, .ag-members-table td {
    padding: 14px; text-align: left; border-bottom: 1px solid #e2e8f0;
}
.ag-members-table select {
    padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px;
}
.ag-members-button {
    padding: 10px 16px; border: 0; border-radius: 8px;
    background: #2563eb; color: white; cursor: pointer;
}
.ag-members-danger { background: #b91c1c; }
.ag-members-success { color: #047857; }
.ag-members-error { color: #b91c1c; }
.ag-members-scroll { overflow-x: auto; }
</style>
@endpush

@section('content')
<div class="ag-members-card">
<a href="{{ route('clients.show', $client) }}">Back to workspace</a>
<p>
<a href="{{ route('clients.access-history.index', $client) }}">Access history</a>
</p>


@if (session('success'))
<p class="ag-members-success" role="status">{{ session('success') }}</p>
@endif

@if ($errors->any())
<div class="ag-members-error" role="alert">
<ul>
@foreach ($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
</div>
@endif

<h2>Members</h2>
<p>
Viewer: read-only access.
Editor: manages projects, tasks and assessments.
Administrator: also manages workspace details and members.
</p>

<div class="ag-members-scroll">
<table class="ag-members-table">
<thead>
<tr><th>Name</th><th>Email</th><th>Workspace role</th><th>Remove access</th></tr>
</thead>
<tbody>
@forelse ($members as $member)
<tr>
<td>{{ $member->name }}</td>
<td>{{ $member->email }}</td>
<td>
<form method="POST" action="{{ route('clients.members.update', [$client, $member]) }}">
@csrf
@method('PATCH')
<label for="role-{{ $member->id }}">Role for {{ $member->name }}</label>
<select id="role-{{ $member->id }}" name="role" required>
@foreach (['viewer', 'editor', 'administrator'] as $role)
<option value="{{ $role }}" @selected($member->pivot->role === $role)>
{{ ucfirst($role) }}
</option>
@endforeach
</select>
<button type="submit" class="ag-members-button">Save role</button>
</form>
</td>
<td>
<form method="POST" action="{{ route('clients.members.destroy', [$client, $member]) }}">
@csrf
@method('DELETE')
<button type="submit" class="ag-members-button ag-members-danger">
Remove {{ $member->name }}
</button>
</form>
</td>
</tr>
@empty
<tr><td colspan="4">No workspace members assigned yet.</td></tr>
@endforelse
</tbody>
</table>
</div>

<p>Removing workspace membership does not delete the user's account.
Platform administrators retain their platform access.</p>
</div>

<div class="ag-members-card">

<h2>Invite a member</h2>
<form method="POST" action="{{ route('clients.invitations.store', $client) }}">
@csrf
<label for="invite-email">Email address</label>
<input id="invite-email" type="email" name="email"
    value="{{ old('email') }}" maxlength="255" required>
<label for="invite-role">Workspace role</label>
<select id="invite-role" name="role" required>
@foreach (['viewer', 'editor', 'administrator'] as $role)
<option value="{{ $role }}" @selected(old('role', 'viewer') === $role)>
{{ ucfirst($role) }}
</option>
@endforeach
</select>
<button type="submit" class="ag-members-button">Send invitation</button>
</form>
<p>Invitations expire after seven days. Sending a replacement invalidates the previous link.</p>

<h2>Pending invitations</h2>
@forelse ($invitations as $invitation)
<p>
{{ $invitation->email }} — {{ ucfirst($invitation->role) }}
— expires {{ $invitation->expires_at->format('d M Y H:i T') }}
</p>
<form method="POST" action="{{ route('clients.invitations.destroy', [$client, $invitation]) }}">
@csrf
@method('DELETE')
<button type="submit" class="ag-members-button ag-members-danger">Cancel invitation</button>
</form>
@empty
<p>No pending invitations.</p>
@endforelse
</div>
@endsection
