<?php

namespace App\Http\Controllers;

use App\Models\AccessAuditEvent;
use App\Models\Client;
use Illuminate\Http\Request;

class AccessAuditController extends Controller
{
    public function index(Request $request, Client $client)
    {
        abort_unless($request->user()->canAccessWorkspace($client), 404);
        abort_unless($request->user()->canAccessWorkspace($client, 'admin'), 403);

        $events = AccessAuditEvent::query()
            ->where('client_id', $client->id)
            ->with(['actor', 'subject'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(25);

        return response()->view('clients.access-history', [
            'client' => $client,
            'events' => $events,
        ])->header('Cache-Control', 'private, no-store');
    }
}
