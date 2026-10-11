@extends('layouts.aceguard')

@section('title', 'Edit Workspace')

@section('page-title', 'Edit Workspace')
@section('page-subtitle', 'Update organisation details for ' . $client->company_name . '.')

@section('content')
<style>
.ag-workspace-edit {
    background: white; padding: 28px; border-radius: 20px;
    border: 1px solid #e2e8f0; max-width: 960px;
}
.ag-workspace-edit .form-group { margin-bottom: 20px; }
.ag-workspace-edit label {
    display: block; margin-bottom: 8px; font-weight: 600;
}
.ag-workspace-edit .form-control {
    width: 100%; padding: 12px; border: 1px solid #cbd5e1;
    border-radius: 8px; background: white; color: #0f172a;
}
.ag-workspace-edit textarea { min-height: 120px; }
.ag-workspace-edit .btn {
    display: inline-block; padding: 12px 20px;
    border-radius: 8px; text-decoration: none; margin-right: 8px;
}
.ag-workspace-edit .btn-success {
    background: #2563eb; color: white; border: 0; cursor: pointer;
}
.ag-workspace-edit .btn-secondary { background: #e2e8f0; color: #0f172a; }
.ag-workspace-edit .alert-danger { color: #b91c1c; margin-bottom: 20px; }
</style>
<div class="ag-workspace-edit">
<div class="card-body">

@if ($errors->any())
<div class="alert alert-danger">
<ul class="mb-0">
@foreach ($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
</div>
@endif

<form method="POST" action="{{ route('clients.update', $client) }}">
@csrf
@method('PUT')

@foreach ([
    'company_name' => 'Company Name',
    'contact_name' => 'Contact Name',
    'email' => 'Email',
    'phone' => 'Phone',
    'website' => 'Website',
    'address' => 'Address',
    'city' => 'City',
    'postcode' => 'Postcode',
    'country' => 'Country',
] as $field => $label)
<div class="form-group mb-3">
<label for="{{ $field }}">{{ $label }}</label>
<input
    id="{{ $field }}"
    type="{{ $field === 'email' ? 'email' : 'text' }}"
    name="{{ $field }}"
    value="{{ old($field, $client->{$field}) }}"
    class="form-control"
    @if (in_array($field, ['company_name', 'contact_name', 'email'])) required @endif
>
</div>
@endforeach

<div class="form-group mb-3">
<label for="status">Status</label>
<select id="status" name="status" class="form-control" required>
@foreach (['Lead', 'Active', 'Inactive'] as $status)
<option value="{{ $status }}" @selected(old('status', $client->status) === $status)>
{{ $status }}
</option>
@endforeach
</select>
</div>

<div class="form-group mb-3">
<label for="notes">Notes</label>
<textarea id="notes" name="notes" class="form-control">{{ old('notes', $client->notes) }}</textarea>
</div>

<button type="submit" class="btn btn-success">Save Workspace</button>
<a href="{{ route('clients.show', $client) }}" class="btn btn-secondary">Cancel</a>
</form>

</div>
</div>
@stop
