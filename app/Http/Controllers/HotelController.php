<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class HotelController extends Controller
{
    public function getHotelNames(Request $request)
    {
        $event_id = $request->input('event_id');
        $hotels = DB::table('hotel')
            ->where('event_id', '=', $event_id)
            ->pluck('name');

        if ($hotels->isEmpty()) {
            $status = 0;
            $message = "No hotels found for the provided event ID.";
            $data = null;
        } else {
            $status = 1;
            $message = "Hotel names retrieved successfully.";
            $data = $hotels;
        }

        $response = [
            'status' => $status,
            'data' => $data,
            'msg' => $message
        ];

        return response()->json($response, Response::HTTP_OK);
    }

}
