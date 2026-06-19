<?php

namespace App\Http\Responses;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as ContractsLoginResponse;
use App\Models\User;



class LoginResponse implements ContractsLoginResponse
{
    //     public function toResponse($request)
    //     {
    //         // dd("Logging in...");
    //         try{
    //             // $user =  auth()->user();
    //             // dd("User is $user");
    //             $user = User::find(auth()->id());
    // 
    //             $user_session = [
    //                 'details'=> [
    //                     'id'=> $user->id,
    //                     'name'=> $user->name,
    //                     'email'=> $user->email,
    //                     'phone'=> $user->phone,
    //                     'roles'=> $user->getRoleNames(),
    //                     'primary_role'=> $user->getRoleNames()[0],
    //                 ],
    //                 "filters" => [],
    //             ];
    //             
    //             session()->put('user_session', $user_session);
    //             // dd(session()->get('user_session'));
    // 
    //             //return redirect()->to(route('change.password'));
    //             $user->total_web_logins = (int)$user->total_web_logins + 1;
    //             $user->save();
    // 
    //             if (auth()->user()->total_web_logins <= 1) {
    //                 return redirect()->route('change.password');
    //             }
    //         } catch (\Exception $e) {
    //             dd("Exception occurred: $e");
    //             Auth::logout();
    //             return redirect()->to('/login')->with('status', $e->getMessage());
    //         }
    // 
    //         return redirect()->intended('dashboard');
    //     } // end toResponse


    public function toResponse($request)
    {
        try {
            $user = User::find(auth()->id());

            $user_session = [
                'details' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'roles' => $user->getRoleNames(),
                    'primary_role' => $user->getRoleNames()[0],
                ],
                "filters" => [],
            ];

            // Regenerate session first, then store data to avoid conflicts
            session()->regenerate();
            session()->put('user_session', $user_session);

            // Track logins
            $user->total_web_logins = (int) $user->total_web_logins + 1;
            $user->save();

            if ($user->total_web_logins <= 1) {
                return redirect()->route('change.password');
            }
        } catch (\Exception $e) {
            Auth::logout();
            session()->invalidate(); // <--- Make sure to clear session on failure
            session()->regenerateToken();
            return redirect()->to('/login')->with('status', $e->getMessage());
        }

        return redirect()->intended('dashboard');
    } // end toResponse
} // end LoginResponse
