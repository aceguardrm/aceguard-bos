@extends('adminlte::page')

@section('title', 'AceGuard Secure CRM')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
<div class="col-lg-4">

    @if($health)

        @include('components.dashboard.business-health', [
            'health' => $health
        ])

    @endif

</div>
    <div>
        <h1 class="mb-0">Dashboard</h1>
        <small class="text-muted">
            Welcome back, {{ Auth::user()->name }}
        </small>
    </div>

    <div>
        <a href="{{ route('clients.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Client
        </a>
    </div>
</div>
@stop

@section('content')

<div class="row">

    <div class="col-lg-3 col-md-6">

        <div class="small-box bg-gradient-primary">

            <div class="inner">

                <h3>{{ \App\Models\Client::count() }}</h3>

                <p>Total Clients</p>

            </div>

            <div class="icon">
                <i class="fas fa-users"></i>
            </div>

            <a href="{{ route('clients.index') }}" class="small-box-footer">

                View Clients

                <i class="fas fa-arrow-circle-right"></i>

            </a>

        </div>

    </div>



    <div class="col-lg-3 col-md-6">

        <div class="small-box bg-gradient-success">

            <div class="inner">

                <h3>0</h3>

                <p>Open Matters</p>

            </div>

            <div class="icon">
                <i class="fas fa-folder-open"></i>
            </div>

        </div>

    </div>



    <div class="col-lg-3 col-md-6">

        <div class="small-box bg-gradient-warning">

            <div class="inner">

                <h3>0</h3>

                <p>Documents</p>

            </div>

            <div class="icon">
                <i class="fas fa-file-alt"></i>
            </div>

        </div>

    </div>



    <div class="col-lg-3 col-md-6">

        <div class="small-box bg-gradient-danger">

            <div class="inner">

                <h3>0</h3>

                <p>Security Alerts</p>

            </div>

            <div class="icon">
                <i class="fas fa-shield-alt"></i>
            </div>

        </div>

    </div>

</div>



<div class="row">

    <div class="col-lg-8">

        <div class="card card-primary card-outline">

            <div class="card-header">

                <h3 class="card-title">

                    Recent Clients

                </h3>

            </div>

            <div class="card-body p-0">

                <table class="table table-hover">

                    <thead>

                    <tr>

                        <th>Company</th>

                        <th>Contact</th>

                        <th>Status</th>

                    </tr>

                    </thead>

                    <tbody>

                    @forelse(\App\Models\Client::latest()->take(5)->get() as $client)

                        <tr>

                            <td>

                                <a href="{{ route('clients.show',$client) }}">

                                    {{ $client->company_name }}

                                </a>

                            </td>

                            <td>{{ $client->contact_name }}</td>

                            <td>

                                <span class="badge badge-success">

                                    {{ $client->status }}

                                </span>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="3" class="text-center">

                                No clients found

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>



    <div class="col-lg-4">

        <div class="card">

            <div class="card-header">

                <h3 class="card-title">

                    Quick Actions

                </h3>

            </div>

            <div class="card-body">

                <a href="{{ route('clients.create') }}" class="btn btn-primary btn-block mb-2">

                    <i class="fas fa-user-plus"></i>

                    Add Client

                </a>

                <button class="btn btn-success btn-block mb-2">

                    <i class="fas fa-folder-plus"></i>

                    New Matter

                </button>

                <button class="btn btn-warning btn-block mb-2">

                    <i class="fas fa-calendar-alt"></i>

                    Schedule Meeting

                </button>

                <button class="btn btn-danger btn-block">

                    <i class="fas fa-shield-alt"></i>

                    Run Security Scan

                </button>

            </div>

        </div>

    </div>

</div>

@stop
