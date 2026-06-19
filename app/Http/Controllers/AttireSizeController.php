<?php

namespace App\Http\Controllers;

use App\Models\AttireSize;
use App\Models\Event;
use Illuminate\Http\Request;

class AttireSizeController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::where('event_status', 'active')->orderBy('start_date', 'desc')->get();
        $selectedEventId = $request->event_id ?? $events->first()?->event_id;

        $query = AttireSize::with('event')->orderBy('name');
        if ($selectedEventId) {
            $query->where('event_id', $selectedEventId);
        }
        $sizes = $query->paginate(20);

        return view('admin.attire-sizes.index', compact('sizes', 'events', 'selectedEventId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'event_id' => 'required|exists:events,event_id',
        ]);

        AttireSize::create($request->only(['name', 'event_id']));

        return redirect()->route('admin.attire-sizes.index')
            ->with('success', "Attire size '{$request->name}' added.");
    }

    public function destroy(AttireSize $attireSize)
    {
        $attireSize->delete();
        return back()->with('success', 'Attire size deleted.');
    }
}
