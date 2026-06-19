<?php

namespace App\Http\Controllers;

use App\Mail\OTPMail;
use App\Models\Bookers;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class WebAuthController extends Controller
{
    private function generateOTP()
    {
        return rand(100000, 999999);
    }

    public function index()
    {
        return view('web_booking.web_auth.login');
    }

    public function showForgotPasswordForm()
    {
        return view('web_booking.web_auth.forgot_password');
    }

    public function sendForgotPasswordOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:members,email_address',
        ]);

        $member = Member::where('email_address', $request->email)->first();

        $otp = $this->generateOTP();
        DB::table('one_time_passwords')->insert([
            'code' => $otp,
            'purpose' => 'password_reset',
            'channel' => 'email',
            'status' => 'pending',
            'user_id' => $member ? $member->id : null,
            'payload' => json_encode(['email' => $request->email]),
            'created_at' => now(),
        ]);

        Mail::to($request->email)->send(new OTPMail($otp));

        session(['reset_email' => $request->email, 'identifier' => $request->email]);

        return redirect()->route('otp.verify.form')->with('status', 'Reset OTP sent to ' . $request->email);
    }

    public function showResetPasswordForm()
    {
        if (!session('reset_email')) {
            return redirect()->route('participant.login');
        }
        return view('web_booking.web_auth.reset_password', [
            'email' => session('reset_email'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $member = Member::where('email_address', $request->email)->first();
        if (!$member) {
            return back()->withErrors(['email' => 'Member not found.']);
        }

        $member->password = Hash::make($request->password);
        $member->password_set = true;
        $member->save();

        session()->forget(['reset_email', 'reset_member_id']);

        return redirect()->route('participant.login')->with('status', 'Password reset successfully. Please login.');
    }

    public function showProfileSetupForm()
    {
        if (!session('setup_email')) {
            return redirect()->route('participant.login');
        }
        return view('web_booking.web_auth.profile_setup', [
            'email' => session('setup_email'),
            'company_name' => session('setup_company', ''),
        ]);
    }

    public function saveProfileSetup(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'company_name' => 'required|string|max:255',
            'password' => 'required|min:6|confirmed',
        ]);

        $member = Member::where('email_address', $request->email)->first();
        if (!$member) {
            return back()->withErrors(['email' => 'Member not found.']);
        }

        $member->company_name = $request->company_name;
        $member->password = Hash::make($request->password);
        $member->password_set = true;
        $member->save();

        session()->forget(['setup_email', 'setup_company']);

        Auth::guard('member')->login($member);

        return redirect()->route('member-dashboard')->with('status', 'Profile setup complete!');
    }

    /**
     * Generate OTP for login.
     *
     * This function validates the input request, checks if the input is email or phone number,
     * generates an OTP, saves it to database, and sends it to the user via email or SMS.
     * If the user is not found, it returns an error response.
     *
     * @param Request $request - The request containing email or phone number
     * @return \Illuminate\Http\JsonResponse - API response containing user ID and success message
     */
    public function sendOTP(Request $request)
    {
        $request->validate([
            'member_status' => 'required|in:member,non-member',
            'email' => 'required|email',
            'member_id' => 'required_if:member_status,member',
        ]);

        $email = $request->input('email');
        $memberId = $request->input('member_id');
        $memberStatus = $request->input('member_status');

        $member = null;

        if ($memberStatus === 'member') {
            // Strict lookup by member_id and member_status
            $member = Member::where('member_id', $memberId)
                ->where('status', $memberStatus)
                ->first();

            if (!$member) {
                return redirect()->back()
                    ->withErrors(['email' => 'Invalid member ID or status.'])
                    ->withInput();
            }

            // Check if entered email matches database email
            if ($member->email_address !== $email) {
                return redirect()->back()
                    ->withErrors(['email' => 'Invalid email for the provided Member ID. Please contact ICAM if you changed your email.'])
                    ->withInput();
            }

            // Redirect to password form if password is set
            if (!empty($member->password)) {
                return view('web_booking.web_auth.password', [
                    'email' => $member->email_address
                ]);
            }

            // Always use registered email
            $email = $member->email_address;

        } else {
            // For non-members
            $member = Member::where('email_address', $email)->first();

            if ($member && !empty($member->password)) {
                return view('web_booking.web_auth.password', [
                    'email' => $member->email_address
                ]);
            }
        }

        // Proceed with OTP generation
        $otp = $this->generateOTP();
        $identifier = $email;
        $user_id = $member ? $member->id : null;

        DB::table('one_time_passwords')->insert([
            'code' => $otp,
            'purpose' => 'account creation',
            'channel' => 'email',
            'status' => 'pending',
            'user_id' => $user_id,
            'payload' => json_encode([
                'email' => $email,
                'member_id' => $memberId,
                'identifier' => $identifier
            ]),
            'created_at' => now(),
        ]);

        Mail::to($email)->send(new OTPMail($otp));

        session([
            'identifier' => $identifier,
            'member_status' => $memberStatus
        ]);

        return redirect()->route('otp.verify.form')->with('status', 'OTP sent to ' . $email);
    }

    public function sendOTP1(Request $request)
    {
        $request->validate([
            'member_status' => 'required|in:member,non-member',
            'email' => 'required|email',
            'member_id' => 'required_if:member_status,member',
        ]);

        $email = $request->input('email');
        $memberId = $request->input('member_id');
        $memberStatus = $request->input('member_status');

        $member = null;

        if ($memberStatus === 'member') {
            $member = Member::where(function ($query) use ($email, $memberId) {
                $query->where('email_address', $email)
                    ->orWhere('member_id', $memberId);
            })->first();

            if (!$member) {
                return redirect()->back()->withErrors(['email' => 'Invalid email or member ID.'])->withInput();
            }

            // If member has a password, redirect to password login form
            if (!empty($member->password)) {
                return view('web_booking.web_auth.password', [
                    'email' => $member->email_address
                ]);
            }

            // Always use the member's registered email for OTP
            $email = $member->email_address;
        } else {
            // Check if non-member exists and has password (unlikely, but optional)
            $member = Member::where('email_address', $email)->first();

            if ($member && !empty($member->password)) {
                return view('web_booking.web_auth.password', [
                    'email' => $member->email_address
                ]);
            }
        }

        // Proceed with OTP if no password is found
        $otp = $this->generateOTP();
        $identifier = $email;
        $user_id = $member ? $member->id : null;
        $channel = 'email';

        DB::table('one_time_passwords')->insert([
            'code' => $otp,
            'purpose' => 'account creation',
            'channel' => $channel,
            'status' => 'pending',
            'user_id' => $user_id,
            'payload' => json_encode([
                'email' => $email,
                'member_id' => $memberId,
                'identifier' => $identifier
            ]),
            'created_at' => now(),
        ]);

        // Send OTP to email
        Mail::to($email)->send(new OTPMail($otp));

        // Store session for verification step
        session([
            'identifier' => $identifier,
            'member_status' => $memberStatus
        ]);

        return redirect()->route('otp.verify.form')->with('status', 'OTP sent to ' . $email);
    }

    public function showEnterPasswordForm()
    {
        return view('web_booking.web_auth.password');
    }

    public function showVerifyOTPForm()
    {
        return view('web_booking.web_auth.otp');
    }


    /**
     * Verify the provided OTP for a user.
     *
     * This method validates the input OTP and user_id, checks if the OTP exists
     * and is pending in the database for the given user, and verifies whether
     * the OTP is correct. If the OTP is valid, it updates its status to 'used'
     * and returns a success response. Otherwise, it returns an error response.
     *
     * @param Request $request The HTTP request containing 'otp' and 'user_id'.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the result
     *                                      of the OTP verification.
     */
    public function submitPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
//        dd($request->input('email'));

        $member = Member::where('email_address', $request->email)->first();
//        dd($member);

        if (!$member || !$member->password) {
            return redirect()->route('login')->withErrors(['email' => 'Account does not support password login.']);
        }

        if (!Hash::check($request->password, $member->password)) {
            return view('web_booking.web_auth.password', [
                'email' => $request->email,
            ])->withErrors(['password' => 'Incorrect password.']);
        }
        $member->last_active_at = now();
        $member->save();


        Auth::guard('member')->login($member);

        return redirect()->route('member-dashboard');
    }

    public function getPassword()
    {
        return view('web_booking.web_auth.change_password');
    }

    /**
     * Handle the profile update and password change request.
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();

        // Validate profile fields
        $profileRules = [
            'participant' => 'required|string|max:255',
            'email_address' => 'required|email|max:255',
            'phone_number' => 'nullable|string|max:50',
            'company_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
        ];

        // Validate password fields only if new_password is present
        $hasPasswordRequest = $request->filled('new_password');
        if ($hasPasswordRequest) {
            $profileRules['new_password'] = ['required', 'string', 'min:8', 'confirmed'];
            if (!empty($user->password)) {
                $profileRules['current_password'] = ['required'];
            }
        }

        $validated = $request->validate($profileRules);

        // Check current password if provided
        if ($hasPasswordRequest && !empty($user->password)) {
            if (!Hash::check($request->input('current_password'), $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['Current password is incorrect.'],
                ]);
            }
        }

        // Update profile fields
        $user->participant = $request->input('participant');
        $user->email_address = $request->input('email_address');
        $user->phone_number = $request->input('phone_number');
        $user->company_name = $request->input('company_name');
        $user->address = $request->input('address');

        // Update password if provided
        if ($hasPasswordRequest) {
            $user->password = Hash::make($request->input('new_password'));
        }

        $user->save();

        $message = 'Profile updated successfully.';
        if ($hasPasswordRequest) {
            $message = 'Profile updated and password changed successfully.';
        }

        return redirect()->route('member-dashboard')->with('status', $message);
    }

    public function verifyOTP(Request $request)
    {

        $request->validate([
            'otp' => 'required|numeric|digits:6',
            'identifier' => 'required|email',
        ]);

        $identifier = $request->input('identifier');
        $otp = $request->input('otp');
        $firebaseToken = $request->input('token');
        $last_active_at = now();

        // Check if OTP exists and is pending
        $otpRecord = DB::table('one_time_passwords')
            ->where('status', 'pending')
            ->where('code', $otp)
            ->where('payload', 'like', '%' . $identifier . '%')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$otpRecord) {
            return redirect()->back()->withErrors(['otp' => 'Invalid or expired OTP.'])->withInput();
        }

        // Mark OTP as used
        DB::table('one_time_passwords')->where('id', $otpRecord->id)->update([
            'status' => 'used',
            'updated_at' => now()
        ]);

        // Check if the user is registered (member or registered non-member)
        $user = Member::where('email_address', $identifier)->first();

        if ($user) {
            $user->last_active_at = $last_active_at;
            $user->save();

            // First-time user: redirect to profile setup
            if (!$user->password_set) {
                session([
                    'setup_email' => $user->email_address,
                    'setup_company' => $user->company_name,
                ]);
                return redirect()->route('member.profile.setup.form');
            }

            // Existing user: log in
            Auth::guard('member')->login($user);

            return redirect()->route('member-dashboard');
        }

        // If not found in DB, it's a non-member who hasn’t registered yet
        session([
            'identifier' => $identifier,
            'firebase_token' => $firebaseToken,
            'member_status' => 'non-member'
        ]);

        return redirect()->route('register.form')->with('status', 'Please complete your registration to continue.');
    }

    public function showRegisterForm()
    {
        $countries = \App\Models\Country::where('status', true)->orderBy('name')->get();
        return view('web_booking.web_auth.register', compact('countries'));
    }

    public function registerNonMember(Request $request)
    {
        $request->validate([
            'identifier' => 'required|email',
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'gender' => 'nullable|in:male,female,other',
            'organisation' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
        ]);

        $otpUsed = DB::table('one_time_passwords')
            ->where('payload', 'like', '%' . $request->identifier . '%')
            ->where('status', 'used')
            ->orderBy('created_at', 'desc')
            ->exists();

        if (!$otpUsed) {
            return redirect()->back()->withErrors(['otp' => 'OTP verification required before registration.'])->withInput();
        }

        $existing = Member::where('email_address', $request->identifier)->first();
        if ($existing) {
            Auth::guard('member')->login($existing);
            return redirect()->route('member-dashboard')->with('status', 'Welcome back!');
        }

        do {
            $referenceCode = 'NM-' . strtoupper(Str::random(8));
        } while (DB::table('members')->where('member_id', $referenceCode)->exists());

        $nonMember = Member::create([
            'participant'    => $request->full_name,
            'email_address'  => $request->identifier,
            'phone_number'   => $request->phone_number,
            'company_name'   => $request->organisation,
            'status'         => 'Non-Member',
            'member_id'      => $referenceCode,
            'password_set'   => false,
        ]);

        $nonMember->save();

        session()->forget(['identifier', 'member_status']);

        session(['setup_email' => $nonMember->email_address, 'setup_company' => $nonMember->company_name]);
        return redirect()->route('member.profile.setup.form');
    }

    public function logout(Request $request)
    {
        Auth::guard('member')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('participant.login');
    }

    private function detectDeviceType(Request $request)
    {
        $userAgent = $request->header('User-Agent');

        if (preg_match('/android/i', $userAgent)) {
            return 'Android';
        } elseif (preg_match('/iPad|iPhone|iPod/i', $userAgent)) {
            return 'iOS';
        } else {
            return 'Other';
        }
    }

}
