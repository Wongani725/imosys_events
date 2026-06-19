<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Helpers\UserSession;
use App\Mail\MailSender;
use App\Mail\Pincode;
use App\Models\Event;
use App\Models\OTP;
use App\Models\Password;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderFee;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password as PasswordFacade;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class AuthController extends Controller
{

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
//            'current_password' => 'required|password',
            'password' => 'required|confirmed|min:8',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('dashboard')->with('success', 'Password changed successfully.');
    }

    public function showChangePasswordForm()
    {
        return view('auth.change-password');
    }

    //
	public function login(Request $request)
	{
	    $validationRules = [
            'username'  => 'required|email|exists:users,email',
            'device_date'  => 'required|string',
        ];

        $validator = Validator::make($request->all(), $validationRules);
        if($validator->fails()) {
            $validation_error = Helper::FirstValidationError($validator->errors()->toArray());
            return Helper::APIResponse(0, "Invalid data, {$validation_error}",HTTP_FAILED, ['errors'=> $validator->errors()]);
        }

        $error_code = HTTP_FAILED;

        DB::beginTransaction();
        try {
            $today = Helper::Today();
            if("{$request->device_date}" !== "{$today}") {
                throw new Exception("Device date is not accurate, Please change your device date");
            }

            $username = $request->username;
            $usernameColumn = "email";

            # fetch for user
            $user =  User::where([["{$usernameColumn}", $username], ["status", ACTIVE]])->first();

            # return an error
            if(empty($user)) {
                throw new Exception("Invalid Username");
            }

            # SEND PASSWORD TO USER EMAIL;
            $currentTime = Carbon::now();
            $code = Password::whereRaw("TIME_TO_SEC(TIMEDIFF('{$currentTime}', created_at)) < 600")
                ->where([['email', '=', $user->email], ['status', '=', PENDING]])->first();

            // Send another otp only when they no other pending otp
            if(empty($code)) {
                $code1 = Helper::GenerateUniqueNumber(100, 999);
                $code2 = Helper::GenerateUniqueNumber(100, 999);
                $otp = "{$code1}{$code2}";

                $mailSubject = "IIA Events Login Pin Code";
                $mailSalutation = "Dear ". empty($user->name) ? $user->email : $user->name;
                $mailMessage = "Your login pin code is {$otp}, note that this can only be used once and don't share it with anyone else.";
                $mailView = "emails.pincode";

                // Mail::to($user->email)->send(new MailSender("$mailSubject", "$mailSalutation", "$mailMessage", "$mailView"));

                # Cancel all pending OTPs
                Password::where([
                    ['email', '=', $user->email],
                    ['status', '=', PENDING]
                ])->update(['status' => EXPIRED]);

                // Create account creation otp record
                $code = Password::create([
                    "otp" => "{$otp}",
                    'status' => PENDING,
                    'email' => $user->email,
                ]);
            }

            $response_code = HTTP_SUCCESS;

            $message = "We have sent pin code to {$user->email}, please check the inbox.";
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            return Helper::APIResponse(0, $exception->getMessage() , $error_code, [
                'error' => $exception->getMessage()
            ]);
        }

        return Helper::APIResponse(1, $message, $response_code, ['code'=> $code]);
	}

    public function verifyOneTimePassword(Request $request) {
        $validationRules = [
            'code'  => 'required|string|min:6|max:6',
            'username' => 'required|email|exists:users,email',
            'token' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if($validator->fails()) {
            $validation_error = Helper::FirstValidationError($validator->errors()->toArray());
            return Helper::APIResponse(0, "Sorry; {$validation_error}",HTTP_UNPROCESSABLE, ['errors'=> $validator->errors()]);
        }

        $error_code = HTTP_FAILED;

        $error_code = HTTP_UNAUTHORIZED;

        DB::beginTransaction();
        try {
            $user = User::where([["email", $request->username], ["status", ACTIVE]])->first();

            if(empty($user)) {
                throw new Exception("Invalid username");
            }

            if(!UserSession::DemoAccount($user->email)) {
                $userOTP = Password::selectRaw("TIME_TO_SEC(TIMEDIFF(NOW(), created_at)) seconds, member_otps.*")
                    ->where([
                        ['otp', $request->code],
                        ['email', $user->email],
                        ['status', PENDING],
                    ])->first();

                if (empty($userOTP)) {
                    throw new Exception("The code you entered is invalid.");
                }

                // Check if token has not expired
                $otpAge = (int)$userOTP->seconds;

                if ($otpAge > 600) {
                    $userOTP->status = EXPIRED;
                    $userOTP->save();
                    $userOTP->refresh();

                    $error_code = HTTP_FAILED;
                    throw new Exception("The code you entered has expired");
                }

                # Update pin code record
                $userOTP->status = VERIFIED;
                $userOTP->date_verified = Helper::Timestamp();
                $userOTP->save();
                $userOTP->refresh();

                # update user email verification date
                $user->email_verified_at = Helper::Timestamp();
                $user->save();

//                $password = '12345678';
//                if (!Auth::attempt(["email" => $user->email, 'password' => $password])) {
//                    $error_code = HTTP_UNAUTHORIZED;
//                    throw new Exception("Failed to log you in, please try again");
//                }
            }

            # update push notification token
            $user->firebase_token = $request->token;
            $user->save();
            $user->refresh();

            # Get user role
            $role = $user->getRoleNames()[0];

            # create authentication access token for the user
            $access_token = $user->createToken('auth_token')->plainTextToken;
            $token_type = 'Bearer';

            $session_token = "{$token_type} {$access_token}";

            $headers = [
                "Accept"=> "application/json",
                "Authorization"=> $session_token
            ];

            # update user mobile app login counter
            $user->total_mobile_app_logins = (int) $user->total_mobile_app_logins + 1;
            $user->save();

            # Retrieve user assigned events if any
//            $events = $user->attendantEvents()->wherePivot("status", ACTIVE)->get();

            DB::commit();
        }
        catch (Exception $e) {
            DB::rollBack();
            return Helper::APIResponse(0, "request failed; {$e->getMessage()} ", $error_code);
        }

        $data = compact('headers',  'role', 'access_token', 'token_type', 'session_token');
        return Helper::APIResponse(1, "User Login Successful", HTTP_SUCCESS, $data);
    }

    public function resendOneTimePassword(Request $request) {
        $validationRules = [
            'phone' => 'required|string',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if($validator->fails()) {
            $validation_error = Helper::FirstValidationError($validator->errors()->toArray());
            return Helper::APIResponse(0, "Request Failed; {$validation_error}",HTTP_UNPROCESSABLE, ['errors'=> $validator->errors()]);
        }

        $validator = Validator::make($request->all(), [
            'phone' => 'required|email'
        ]);

        $usernameColumn = $validator->fails() ? "phone" : "email";

        try{
            $phone = $request->phone;
            $user = User::where("{$usernameColumn}", $phone)->first();

            if(empty($user)) {
                throw new Exception("Invalid user $usernameColumn");
            }

            $userOTP = OTP::where([
                ['user_id', $user->id],
                ['status', PENDING],
            ])->first();

            if(empty($userOTP)) {
                throw new Exception("Sorry, {$request->phone} did not request for otp code before.");
            }

            $response_code = HTTP_SUCCESS;

            if($usernameColumn === "email") {
                $newOTP = $this->sendSMS($phone, $user, true);
                $message = "We have resend the otp code to {$phone}, check your mailbox.";
            }
            else {
                $newOTP = $this->sendSMS($phone, $user);
                $message = "OTP SMS is sent to {$phone}";
            }


//            if ($newOTP !== true) {
////                $response_code = HTTP_NOT_COMPLETED;
//                $message = "You will receive otp soon. This can take up to 3 minutes";
//            }

            if ($newOTP !== true) {
                if( (int) $this->otpExceptionCode === 1) {
                    $message = "Please use the otp code that was sent to you a few minutes ago, if not received yet keep waiting.";
                }
                else {
                    $response_code = HTTP_NOT_COMPLETED;
                    $message = "Sorry your registration failed. Please try again later.";
                    throw new Exception($message);
                }
            }

            return Helper::APIResponse(1, $message, $response_code);
        }
        catch(Exception $e) {
            return Helper::APIResponse(0, Helper::LaravelException("Request Failed", $e),HTTP_UNPROCESSABLE, ['error'=> $e->getMessage()]);
        }
    }

	public function loginDefault(Request $request)
	{
	    $phone = $request->only('phone');
	    $password = '12345678';
		if (!Auth::attempt(['phone'=>$phone, 'password'=>$password])) {
			return response()->json([
			'message' => 'Invalid login details'
			           ], 401);
		}

		$user = User::where('email', $request['email'])->firstOrFail();

		$token = $user->createToken('auth_token')->plainTextToken;

		return response()->json([
		           'access_token' => $token,
		           'token_type' => 'Bearer',
		]);

	}

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $status = PasswordFacade::sendResetLink($request->only('email'));

        return $status === PasswordFacade::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = PasswordFacade::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => bcrypt($password)])->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );

        return $status === PasswordFacade::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    public function logout(Request $request) {
        try {
            $request->user()->tokens()->delete();
        }
        catch (Exception $exception) {
            return Helper::APIResponse(0, $exception->getMessage(), HTTP_FAILED);
        }

        return Helper::APIResponse(1, "Successfully signed out", HTTP_SUCCESS);
    }
}
