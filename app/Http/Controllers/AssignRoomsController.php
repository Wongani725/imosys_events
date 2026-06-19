<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Room;
use App\Models\Participant;
use App\Models\AssignRoom;
use DB; // Keep this import statement to use the DB class


class AssignRoomsController extends Controller
{
    public function assignParticipantsToRooms(Request $request, $eventId)
    {

        $event = Event::orderBy('created_at', 'desc')->first();
//        dd($event);

        $eventId = $event->event_id;
//
//        dd($event);


        // Ensure the event was found
        if (!$event) {
            return redirect()->back()->withErrors(['message' => 'Event not found.']);
        }

        // Fetch rooms and participants
        $rooms = Room::where('event_id', $eventId)->get();
//        dd($rooms);
        $participants = Participant::where('event_id', $eventId)->get();
//        dd($participants);
        $numberOfRooms = $rooms->count();
//        dd($numberOfRooms);


        if ($numberOfRooms === 0) {
            return redirect()->back()->withErrors(['message' => 'No rooms available for this event.']);
        }

        // Shuffle participants and assign to rooms
        $participants->shuffle();
        $assignments = [];
        foreach ($participants as $index => $participant) {
            $room = $rooms->get($index % $numberOfRooms);
            if ($room) {
                $assignments[] = [
                    'room_id' => $room->id,
                    'event_id' => $eventId,
                    'participant_code' => $participant->reference_code,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Bulk insert into the pivot table
        DB::table('assigned_rooms')->insert($assignments);
        // Redirect back with a success message
        return redirect()->back()->with('message', 'Participants have been assigned to rooms.');
    }}
