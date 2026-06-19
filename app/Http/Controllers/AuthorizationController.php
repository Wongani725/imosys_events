<?php

namespace App\Http\Controllers;


use Carbon\Carbon;
use DB;
use Exception;
use App\Models\User;
use App\Helpers\Helper;
use App\Models\Occassion;
use Illuminate\Http\Request;
use App\Models\AlertMessage;
use App\Models\Event;
use App\Models\Participant;

//use App\Models\Occassion;
use Illuminate\Support\Facades\Validator;

class AuthorizationController extends Controller
{
    public function viewProgress()
    {
        $jobs = DB::table('jobs')->get();

        return view('progress', compact('jobs'));
    }

    public function pending()
    {
        $pendingValues = DB::table('authorization_logs')->where('status','pending')->get();

        return view('authorization.pendingAuth', compact('pendingValues'));

    }

    public function logs()
    {
        $values = DB::table('authorization_logs')->orderBy('created_at','asc')->get();

        return view('authorization.logs', compact('values'));

    }

}
?>
