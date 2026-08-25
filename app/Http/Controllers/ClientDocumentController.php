<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\ClientDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ClientDocumentController extends Controller
{
    public function store(Request $request, Client $client)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'document' => 'required|file|max:10240', // 10MB max
        ]);

        $file = $request->file('document');
        $path = $file->store('client-documents/' . $client->id, 'public');

        $client->documents()->create([
            'title' => $request->title,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => Auth::id(),
        ]);

        return redirect()->route('clients.show', $client)->with('success', 'Document uploaded successfully!');
    }

    public function download(ClientDocument $document)
    {
        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    public function destroy(ClientDocument $document)
    {
        Storage::disk('public')->delete($document->file_path);
        $clientId = $document->client_id;
        $document->delete();

        return redirect()->route('clients.show', $clientId)->with('success', 'Document deleted successfully!');
    }
}
