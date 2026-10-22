@extends('layouts.aceguard')

@section('title', 'Workspace Documents')
@section('page-title', 'Workspace Documents')
@section('page-subtitle', 'Private documents for ' . $client->company_name . '.')

@push('styles')
<style>
.ag-documents-card {
    background: white; padding: 24px; border-radius: 20px;
    border: 1px solid #e2e8f0; margin-bottom: 24px;
}
.ag-documents-card label {
    display: block; font-weight: 600; margin: 18px 0 8px;
}
.ag-documents-card input[type="text"] {
    width: 100%; max-width: 640px; padding: 12px;
    border: 1px solid #cbd5e1; border-radius: 8px;
    box-sizing: border-box;
}
.ag-documents-card button {
    display: block; margin-top: 20px; padding: 12px 20px;
    background: #2563eb; color: white; border: 0;
    border-radius: 8px; cursor: pointer;
}
.ag-documents-scroll { overflow-x: auto; }
.ag-documents-card table { width: 100%; border-collapse: collapse; }
.ag-documents-card th, .ag-documents-card td {
    padding: 14px; text-align: left; border-bottom: 1px solid #e2e8f0;
}
.ag-documents-card small { display: block; color: #64748b; }
.ag-documents-error { color: #b91c1c; }
.ag-documents-success { color: #047857; }
.ag-documents-pages {
    display: flex; justify-content: space-between; margin-top: 24px;
}
</style>
@endpush

@section('content')
<div class="ag-documents-card">
    <a href="{{ route('clients.show', $client) }}">Back to workspace</a>

    @if (session('success'))
        <p class="ag-documents-success" role="status">{{ session('success') }}</p>
    @endif

    @if ($errors->any())
        <ul class="ag-documents-error" role="alert">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <h2>Private workspace documents</h2>
    <p>Only people with access to this workspace can list and download these files.</p>

    @if (auth()->user()->canAccessWorkspace($client, 'edit'))
        <form method="POST"
              action="{{ route('workspace-documents.store', $client) }}"
              enctype="multipart/form-data">
            @csrf
            <label for="document-title">Document title</label>
            <input id="document-title" type="text" name="title"
                   value="{{ old('title') }}" maxlength="255" required>

            <label for="document-file">File</label>
            <input id="document-file" type="file" name="document"
                   accept=".pdf,.txt" required>
            <p>PDF or plain text, maximum 10 MB. Files are not scanned for malware.</p>

            <button type="submit">Upload document</button>
        </form>
    @endif
</div>

<div class="ag-documents-card">
    <h2>Documents</h2>
    <div class="ag-documents-scroll">
        <table>
            <thead>
                <tr>
                    <th scope="col">Document</th>
                    <th scope="col">Uploaded by</th>
                    <th scope="col">Size</th>
                    <th scope="col">Date UTC</th>
                    <th scope="col">Download</th>
                    @if (auth()->user()->canAccessWorkspace($client, 'edit'))
                        <th scope="col">Delete</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($documents as $document)
                    <tr>
                        <td>
                            {{ $document->title }}
                            <small>{{ $document->original_name }}</small>
                        </td>
                        <td>{{ $document->uploader?->name ?? 'Deleted account' }}</td>
                        <td>
                            @if ($document->size < 1024)
                                {{ $document->size }} B
                            @else
                                {{ number_format($document->size / 1024, 1) }} KB
                            @endif
                        </td>
                        <td>{{ $document->created_at->copy()->utc()->format('d M Y H:i') }}</td>
                        <td>
                            <a href="{{ route('workspace-documents.download', [$client, $document]) }}">
                                Download
                            </a>
                        </td>
                        @if (auth()->user()->canAccessWorkspace($client, 'edit'))
                            <td>
                                <form method="POST"
                                      action="{{ route('workspace-documents.destroy', [$client, $document]) }}"
                                      onsubmit="return confirm('Permanently delete this document?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background:#b91c1c;margin-top:0">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->canAccessWorkspace($client, 'edit') ? 6 : 5 }}">
                            No documents uploaded yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <nav class="ag-documents-pages" aria-label="Document pages">
        <span>
            @if ($documents->previousPageUrl())
                <a href="{{ $documents->previousPageUrl() }}">Previous</a>
            @endif
        </span>
        <span>Page {{ $documents->currentPage() }} of {{ $documents->lastPage() }}</span>
        <span>
            @if ($documents->nextPageUrl())
                <a href="{{ $documents->nextPageUrl() }}">Next</a>
            @endif
        </span>
    </nav>
</div>
@endsection
