<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Participant;

class RestrictParticipantAccess
{
    public function handle(Request $request, Closure $next)
    {
        $participantId = $request->route('id1');
        $eventId = $request->route('id2');

        $participant = Participant::where('reference_code', $participantId)
            ->where('event_id', $eventId)
            ->first();

        if (!$participant) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
