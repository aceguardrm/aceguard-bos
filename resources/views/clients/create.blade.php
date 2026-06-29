@extends('adminlte::page')

@section('title', 'New Client')

@section('content_header')
<h1>New Client</h1>
@stop

@section('content')

<div class="card">

<div class="card-body">

<form method="POST" action="{{ route('clients.store') }}">

@csrf

<div class="form-group mb-3">
<label>Company Name</label>
<input type="text" name="company_name" class="form-control" required>
</div>

<div class="form-group mb-3">
<label>Contact Name</label>
<input type="text" name="contact_name" class="form-control" required>
</div>

<div class="form-group mb-3">
<label>Email</label>
<input type="email" name="email" class="form-control" required>
</div>

<div class="form-group mb-3">
<label>Phone</label>
<input type="text" name="phone" class="form-control">
</div>

<div class="form-group mb-3">
<label>Website</label>
<input type="text" name="website" class="form-control">
</div>

<div class="form-group mb-3">
<label>Address</label>
<input type="text" name="address" class="form-control">
</div>

<div class="form-group mb-3">
<label>City</label>
<input type="text" name="city" class="form-control">
</div>

<div class="form-group mb-3">
<label>Postcode</label>
<input type="text" name="postcode" class="form-control">
</div>

<div class="form-group mb-3">
<label>Status</label>

<select name="status" class="form-control">

<option value="Lead">Lead</option>

<option value="Active">Active</option>

<option value="Inactive">Inactive</option>

</select>

</div>

<div class="form-group mb-3">
<label>Notes</label>
<textarea name="notes" class="form-control"></textarea>
</div>

<button class="btn btn-success">
Save Client
</button>

<a href="{{ route('clients.index') }}" class="btn btn-secondary">
Cancel
</a>

</form>

</div>
</div>

@stop
