@extends('adminlte::page')

@section('title', 'AceGuard Secure CRM')

@section('content_header')
    <h1>🛡️ AceGuard Secure CRM</h1>
@stop

@section('content')

<div class="row">

    <div class="col-md-3">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>0</h3>
                <p>Clients</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>0</h3>
                <p>Matters</p>
            </div>
            <div class="icon">
                <i class="fas fa-folder-open"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>0</h3>
                <p>Security Reports</p>
            </div>
            <div class="icon">
                <i class="fas fa-shield-alt"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>0</h3>
                <p>Users</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-lock"></i>
            </div>
        </div>
    </div>

</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Welcome to AceGuard Secure CRM</h3>
    </div>

    <div class="card-body">
        <h5>Quick Actions</h5>

        <a href="#" class="btn btn-primary">New Client</a>
        <a href="#" class="btn btn-success">New Matter</a>
        <a href="#" class="btn btn-warning">New Scan</a>
        <a href="#" class="btn btn-danger">Upload Report</a>
    </div>
</div>

@stop
