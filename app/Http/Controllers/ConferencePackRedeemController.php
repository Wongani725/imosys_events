<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConferencePackRedeemController extends Controller
{
    public function redeemConferencePack(Request $request)
    {
        $referenceCode = $request->input('reference_code');

        $registration = DB::table('i_participant_event_registrations')
            ->where('participant_id', $referenceCode)
            ->first();

        if ($registration) {
            $currentStatus = $registration->conference_pack_redeemed;

            if ($currentStatus === 0) {
                DB::table('i_participant_event_registrations')
                    ->where('participant_id', $referenceCode)
                    ->update(['conference_pack_redeemed' => 1]);

                return response()->json(['msg' => 'Conference pack redeemed successfully'], 200);
            } else {
                return response()->json(['msg' => 'Conference pack already redeemed'], 400);
            }
        }

        return response()->json(['msg' => 'Cannot redeem pack, please register'], 404);
    }
}
