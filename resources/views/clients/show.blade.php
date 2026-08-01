@extends('adminlte::page')

@section('title', $client->company_name)

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>

        <h1 class="mb-0">{{ $client->company_name }}</h1>

        <small class="text-muted">
            {{ $client->contact_name }}
        </small>

    </div>

    @if($client->status == 'Active')
        <span class="badge bg-success p-2">ACTIVE</span>
    @elseif($client->status == 'Lead')
        <span class="badge bg-warning p-2">LEAD</span>
    @else
        <span class="badge bg-danger p-2">INACTIVE</span>
    @endif

</div>

@stop

@section('content')

<div class="row">

    <div class="col-lg-3">

        <div class="small-box bg-primary">

            <div class="inner">

                <h3>0</h3>

                <p>Open Matters</p>

            </div>

        </div>

    </div>

    <div class="col-lg-3">

        <div class="small-box bg-success">

            <div class="inner">

                <h3>0</h3>

                <p>Documents</p>

            </div>

        </div>

    </div>

    <div class="col-lg-3">

        <div class="small-box bg-warning">

            <div class="inner">

                <h3>0</h3>

                <p>Appointments</p>

            </div>

        </div>

    </div>

    <div class="col-lg-3">

        <div class="small-box bg-info">

            <div class="inner">

                <h3>100%</h3>

                <p>Security Score</p>

            </div>

        </div>

    </div>

</div>

<div class="row">

    <div class="col-md-8">

        <div class="card">

            <div class="card-header">

                <h3 class="card-title">

                    Client Information

                </h3>

            </div>

            <div class="card-body">

                <table class="table table-borderless">

                    <tr>

                        <th width="180">Contact</th>

                        <td>{{ $client->contact_name }}</td>

                    </tr>

                    <tr>

                        <th>Email</th>

                        <td>{{ $client->email }}</td>

                    </tr>

                    <tr>

                        <th>Phone</th>

                        <td>{{ $client->phone }}</td>

                    </tr>

                    <tr>

                        <th>Website</th>

                        <td>{{ $client->website }}</td>

                    </tr>

                    <tr>

                        <th>Address</th>

                        <td>{{ $client->address }}</td>

                    </tr>

                    <tr>

                        <th>City</th>

                        <td>{{ $client->city }}</td>

                    </tr>

                    <tr>

                        <th>Country</th>

                        <td>{{ $client->country }}</td>

                    </tr>

                </table>

            </div>

        </div>

    </div>

    <div class="col-md-4">

        <div class="card">

            <div class="card-header">

                <h3 class="card-title">

                    Quick Actions

                </h3>

            </div>

            <div class="card-body d-grid gap-2">

                <a href="#" class="btn btn-primary btn-block">

                    ➕ New Matter

                </a>

                <a href="#" class="btn btn-success btn-block">

                    📄 Upload Document

                </a>

                <a href="#" class="btn btn-warning btn-block">

                    📅 Schedule Meeting

                </a>

                <a href="#" class="btn btn-danger btn-block">

                    🛡 Run Security Scan

                </a>

            </div>

        </div>

        <div class="card mt-3">

            <div class="card-header">

                <h3 class="card-title">

                    Recent Activity

                </h3>

            </div>

            <div class="card-body">

                <p>✅ Client Created</p>

                <p>📄 No documents uploaded</p>

                <p>📅 No appointments scheduled</p>

                <p>⚖ No matters created</p>

            </div>

        </div>

    </div>

</div>

@stop
