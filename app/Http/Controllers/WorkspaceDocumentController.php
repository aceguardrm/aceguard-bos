<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\WorkspaceDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WorkspaceDocumentController extends Controller
{
    private function authorizeWorkspace(
        Request $request,
        Client $client,
        string $level = 'view'
    ): void {
        abort_unless($request->user()->canAccessWorkspace($client), 404);
        abort_unless($request->user()->canAccessWorkspace($client, $level), 403);
    }

    public function index(Request $request, Client $client)
    {
        $this->authorizeWorkspace($request, $client);

        $documents = WorkspaceDocument::where('client_id', $client->id)
            ->with('uploader')
            ->latest('id')
            ->paginate(20);

        return response()->view('clients.documents', [
            'client' => $client,
            'documents' => $documents,
        ])->header('Cache-Control', 'private, no-store');
    }

    public function store(Request $request, Client $client)
    {
        $this->authorizeWorkspace($request, $client, 'edit');

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'document' => [
                'required',
                'file',
                'max:10240',
                'mimes:pdf,txt',
                'extensions:pdf,txt',
            ],
        ]);

        $file = $request->file('document');
        $extension = strtolower($file->getClientOriginalExtension());
        $name = basename(str_replace('\\', '/', $file->getClientOriginalName()));
        $name = preg_replace('/[\x00-\x1F\x7F]/', '', $name);
        $name = Str::limit($name, 240, '');
        $path = null;
        $disk = Storage::disk('workspace_documents');

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use (
                $request, $client, $validated, $file, $extension, $name, $disk, &$path
            ) {
                $lockedClient = Client::whereKey($client->id)
                    ->lockForUpdate()->firstOrFail();
                $this->authorizeWorkspace($request, $lockedClient, 'edit');

            $path = $disk->putFileAs(
                (string) $client->id,
                $file,
                Str::uuid().'.'.$extension
            );

            if (!is_string($path) || $path === '') {
                throw new \RuntimeException('Document storage failed.');
            }

            $document = new WorkspaceDocument([
                'title' => $validated['title'],
                'original_name' => $name ?: 'document.'.$extension,
                'path' => $path,
                'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                'size' => $file->getSize(),
            ]);

            // Workspace and uploader IDs come from trusted server context.
            $document->client()->associate($client);
            $document->uploader()->associate($request->user());
            $document->save();
            });
        } catch (\Throwable $error) {
            if (is_string($path) && $path !== '') {
                try {
                    $disk->delete($path);
                } catch (\Throwable $cleanupError) {
                    report($cleanupError);
                }
            }

            throw $error;
        }

        return redirect()->route('workspace-documents.index', $client)
            ->with('success', 'Document uploaded.');
    }


    public function destroy(
        Request $request,
        Client $client,
        WorkspaceDocument $document
    ) {
        $this->authorizeWorkspace($request, $client, 'edit');
        abort_unless((int) $document->client_id === (int) $client->id, 404);

        \Illuminate\Support\Facades\DB::transaction(function () use (
            $request, $client, $document
        ) {
            $lockedClient = Client::whereKey($client->id)
                ->lockForUpdate()->firstOrFail();
            $this->authorizeWorkspace($request, $lockedClient, 'edit');

            $locked = WorkspaceDocument::whereKey($document->id)
                ->where('client_id', $lockedClient->id)
                ->lockForUpdate()->firstOrFail();

            $disk = Storage::disk('workspace_documents');

            $locked->delete();

            if ($disk->exists($locked->path) && !$disk->delete($locked->path)) {
                throw new \RuntimeException('Document file could not be deleted.');
            }
        });

        return redirect()->route('workspace-documents.index', $client)
            ->with('success', 'Document deleted.');
    }

    public function download(
        Request $request,
        Client $client,
        WorkspaceDocument $document
    ) {
        $this->authorizeWorkspace($request, $client);
        abort_unless((int) $document->client_id === (int) $client->id, 404);

        $disk = Storage::disk('workspace_documents');
        abort_unless($disk->exists($document->path), 404);

        return $disk->download(
            $document->path,
            $document->original_name,
            [
                'Content-Type' => 'application/octet-stream',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ]
        );
    }
}
