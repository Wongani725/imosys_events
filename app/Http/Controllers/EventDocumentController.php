<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventDocument;
use Illuminate\Http\Request;

class EventDocumentController extends Controller
{
    public function index($event_id)
    {
        $event = Event::where('event_id', $event_id)->firstOrFail();
        $documents = EventDocument::where('event_id', $event_id)->latest()->get();
        return view('admin.documents.index', compact('event', 'documents'));
    }

    public function store(Request $request, $event_id)
    {
        $event = Event::where('event_id', $event_id)->firstOrFail();

        $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:20480',
            'type' => 'nullable|string|max:100',
            'is_public' => 'nullable|boolean',
        ]);

        $path = $request->file('file')->store('event_documents', 'public');

        EventDocument::create([
            'event_id' => $event_id,
            'title' => $request->title,
            'file_path' => $path,
            'type' => $request->type ?? 'document',
            'is_public' => $request->boolean('is_public'),
        ]);

        return redirect()->route('admin.documents.index', $event_id)
            ->with('success', "Document '{$request->title}' uploaded.");
    }

    public function destroy($event_id, EventDocument $document)
    {
        $document->delete();
        return back()->with('success', 'Document deleted.');
    }
}
