@extends('adminlte::page')

@section('title', 'Clients')

@section('content_header')
    <h1>Clients</h1>
@stop

@section('content')

<div class="card">

    <div class="card-header">

        <a href="{{ route('clients.create') }}" class="btn btn-primary">
            New Client
        </a>

    </div>

    <div class="card-body">

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <table class="table table-bordered table-hover">

            <thead>

                <tr>

                    <th>Company</th>
                    <th>Contact</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th width="220">Actions</th>

                </tr>

            </thead>

            <tbody>

            @forelse($clients as $client)

                <tr>

                    <td>{{ $client->company_name }}</td>

                    <td>{{ $client->contact_name }}</td>

                    <td>{{ $client->email }}</td>

                    <td>{{ $client->phone }}</td>

                    <td>

                        @if($client->status == 'Active')

                            <span class="badge bg-success">
                                Active
                            </span>

                        @elseif($client->status == 'Lead')

                            <span class="badge bg-warning">
                                Lead
                            </span>

                        @else

                            <span class="badge bg-danger">
                                Inactive
                            </span>

                        @endif

                    </td>

                    <td>

                        <a href="{{ route('clients.show', $client) }}"
                           class="btn btn-info btn-sm">

                            View

                        </a>

                        <a href="{{ route('clients.edit', $client) }}"
                           class="btn btn-warning btn-sm">

                            Edit

                        </a>

                        <form action="{{ route('clients.destroy', $client) }}"
                              method="POST"
                              style="display:inline;">

                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Are you sure you want to delete this client?')">

                                Delete

                            </button>

                        </form>

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="6" class="text-center">

                        No clients found.

                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

</div>

@stop
