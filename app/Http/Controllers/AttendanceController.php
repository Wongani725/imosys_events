<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function registerAttendance(Request $request)
    {
        return response()->json(['message' => 'Attendance registered']);
    }
}
