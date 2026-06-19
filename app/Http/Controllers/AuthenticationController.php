<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\Mail\OTPMail;
use App\Models\OneTimePassword;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Participant;
use Illuminate\Support\Facades\Http;
use App\Helpers\Helper;
use App\Models\Member;
use App\Helpers\NotificationHelper;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class AuthenticationController extends Controller
{

    public function getPolicy()
    {
        return view('policy.index');
    }


    private function generateOTP()
    {
        return rand(100000, 999999);
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

    public function generateOTPForLogin(Request $request)
    {
        $request->validate([
            'email' => 'required_without:member_id|email',
            'member_id' => 'required_without:email|string',
        ]);

        $email = $request->input('email');
        $memberId = $request->input('member_id');

        $identifier = $email;
        $user_id = null;
        $otp = null;
        $password = null;
        $memberStatus = null;
        $passwordSet = false;

        // Detect user by email or member_id
        $query = Member::query();
        if ($memberId) {
            $query->where('member_id', $memberId);
        }
        if ($email) {
            $query->where('email_address', $email);
        }
        $user = $query->first();

        if ($user) {
            $user_id = $user->id;
            $memberStatus = $user->status;
            $identifier = $user->email_address;
            $passwordSet = $user->password_set;

            if (!empty($user->password)) {
                $password = $user->password;
            }
        } else {
            $memberStatus = 'non-member';
        }

        // --- SPECIAL TEST USER ---
        if ($identifier === 'testuser@gmail.com') {
            if ($password) {
                return Helper::APIResponse(1, 'Test user has password.', HTTP_SUCCESS, [
                    'identifier' => $identifier,
                    'code' => 0,
                    'member_status' => $memberStatus,
                    'password' => $password,
                    'password_set' => $passwordSet,
                ]);
            }
            $otp = 123456;
        }
        // --- USER HAS PASSWORD ---
        elseif ($password) {
            return Helper::APIResponse(1, 'User has password.', HTTP_SUCCESS, [
                'identifier' => $identifier,
                'code' => 0,
                'member_status' => $memberStatus,
                'password' => $password,
                'password_set' => $passwordSet,
            ]);
        }
        // --- USER DOES NOT HAVE PASSWORD -> GENERATE OTP ---
        else {
            $otp = $this->generateOTP();
        }

        // Store OTP in member record
        if ($user) {
            $user->otp = $otp;
            $user->otp_expires_at = now()->addMinutes(10);
            $user->save();
        } else {
            DB::table('one_time_passwords')->insert([
                'code' => $otp,
                'purpose' => 'account creation',
                'channel' => 'email',
                'status' => 'pending',
                'user_id' => $user_id,
                'payload' => json_encode([
                    'email' => $email,
                    'identifier' => $identifier,
                    'member_status' => $memberStatus,
                ]),
                'created_at' => now(),
            ]);
        }

        Mail::to($identifier)->send(new OTPMail($otp));

        return Helper::APIResponse(1, 'Enter the OTP code sent to ' . $identifier . '.', HTTP_SUCCESS, [
            'identifier' => $identifier,
            'code' => $otp,
            'member_status' => $memberStatus,
            'password' => null,
            'password_set' => $passwordSet,
        ]);
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

    public function verifyOTP(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric|digits:6',
            'identifier' => 'required', // email
            'token' => 'nullable|string',
        ]);

        $identifier = $request->input('identifier');
        $otp = $request->input('otp');
        $firebase_token = $request->input('token');
        $last_active_at = now();

        // Check member's OTP first
        $user = Member::where('email_address', $identifier)
            ->where('otp', $otp)
            ->where('otp_expires_at', '>', now())
            ->first();

        if (!$user) {
            // Fallback to one_time_passwords table
            $otpRecord = DB::table('one_time_passwords')
                ->where('status', 'pending')
                ->where('code', $otp)
                ->where('payload', 'like', '%' . $identifier . '%')
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$otpRecord) {
                return Helper::APIResponse(0, 'Invalid or expired OTP.', HTTP_BAD_REQUEST, []);
            }

            DB::table('one_time_passwords')
                ->where('id', $otpRecord->id)
                ->update([
                    'status' => 'used',
                    'updated_at' => now()
                ]);

            $payload = json_decode($otpRecord->payload, true);
            $memberStatus = isset($payload['member_status']) ? strtolower($payload['member_status']) : 'non-member';

            return Helper::APIResponse(1, 'OTP verification successful (user not yet registered).', HTTP_SUCCESS, [
                'identifier' => $identifier,
                'member_status' => $memberStatus,
            ]);
        }

        // Clear OTP
        $user->otp = null;
        $user->otp_expires_at = null;
        if ($firebase_token) {
            $user->firebase_token = $firebase_token;
        }
        $user->last_active_at = $last_active_at;
        $user->save();

        $access_token = $user->createToken('auth_token')->plainTextToken;
        $token_type = 'Bearer';

        $data = [
            'user_id' => $user->id,
            'access_token' => $access_token,
            'token_type' => $token_type,
            'password_set' => $user->password_set,
            'member_status' => $user->status === 'International' ? 'international' :
                ($user->status === 'Non-Member' ? 'non-member' : 'member'),
            'user_name' => $user->participant,
            'user_email' => $user->email_address,
            'user_phone_number' => $user->phone_number,
            'user_company' => $user->company_name,
            'user_position' => $user->position,
        ];

        return Helper::APIResponse(1, 'OTP verification successful.', HTTP_SUCCESS, $data);
    }

    public function verifyPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $member = Member::where('email_address', $request->email)->first();

        if (!$member || !$member->password) {
            return Helper::APIResponse(0, 'Account does not support password login.', 401, []);
        }

        if (!Hash::check($request->password, $member->password)) {
            return Helper::APIResponse(0, 'Incorrect password.', 401, []);
        }

        $member->last_active_at = now();
        $member->device_type = $this->detectDeviceType($request);
        $member->save();


        // Login and create token
//        Auth::guard('member')->login($member);
        $access_token = $member->createToken('auth_token')->plainTextToken;

        $data = [
            'user_id' => $member->id,
            'access_token' => $access_token,
            'token_type' => 'Bearer',
            'password_set' => $member->password_set,
            'user_name' => $member->participant,
            'user_email' => $member->email_address,
            'user_phone_number' => $member->phone_number,
            'user_company' => $member->company_name,
            'user_position' => $member->position,
            'member_status' => $member->status,
        ];

        return Helper::APIResponse(1, 'Login successful.', 200, $data);
    }

    public function registerNonMember(Request $request)
    {
        $request->validate([
            'identifier' => 'required|email|unique:members,email_address',
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20|unique:members,phone_number',
            'gender' => 'required|in:male,female,other',
            'organisation' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'firebase_token' => 'required|string',
        ]);

        // Get the last used OTP for this identifier
        $otpRecord = DB::table('one_time_passwords')
            ->where('payload', 'like', '%' . $request->identifier . '%')
            ->where('status', 'used')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$otpRecord) {
            return Helper::APIResponse(0, 'OTP verification required before registration.', HTTP_FORBIDDEN, []);
        }

        // Parse member_status from payload
        $payload = json_decode($otpRecord->payload, true);
        $memberStatusRaw = strtolower($payload['member_status'] ?? 'non-member');

        $statusMap = [
            'non-member' => 'Non-Member',
            'international' => 'International',
        ];
        $memberStatus = $statusMap[$memberStatusRaw] ?? 'Non-Member';

        // Generate a unique reference code
        do {
            $referenceCode = strtoupper(Str::random(10));
        } while (DB::table('members')->where('reference_code', $referenceCode)->exists());

        $nonMember = Member::create([
            'participant' => $request->full_name,
            'email_address' => $request->identifier,
            'phone_number' => $request->phone_number,
            'gender' => $request->gender,
            'company_name' => $request->organisation,
            'position' => $request->position,
            'status' => $memberStatus,
            'firebase_token' => $request->firebase_token,
            'reference_code' => $referenceCode,
        ]);

        $nonMember->last_active_at = now();
        $nonMember->device_type = $this->detectDeviceType($request);
        $nonMember->save();

        $accessToken = $nonMember->createToken('auth_token')->plainTextToken;

        return Helper::APIResponse(1, 'Registration successful.', HTTP_SUCCESS, [
            'non_member_id' => $nonMember->id,
            'full_name' => $nonMember->participant,
            'status' => $nonMember->status,
            'reference_code' => $referenceCode,
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
        ]);
    }

    public function auth_update_participant(Request $request)
    {
        $validatedData = $request->validate([
            'participant' => 'nullable|string|max:255',
            'email_address' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:15',
            'company_name' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:10',
            'position' => 'nullable|string|max:255',
        ]);


        $user = $request->user();
        dd($user);

        $updatedFields = [];

        if ($request->has('participant')) {
            $updatedFields['participant'] = $validatedData['participant'];
        }
        if ($request->has('email_address')) {
            $updatedFields['email_address'] = $validatedData['email_address'];
        }
        if ($request->has('phone_number')) {
            $updatedFields['phone_number'] = $validatedData['phone_number'];
        }
        if ($request->has('company_name')) {
            $updatedFields['company_name'] = $validatedData['company_name'];
        }
        if ($request->has('gender')) {
            $updatedFields['gender'] = $validatedData['gender'];
        }
        if ($request->has('position')) {
            $updatedFields['position'] = $validatedData['position'];
        }

        if (empty($updatedFields)) {
            return Helper::APIResponse(0, 'No fields to update.', HTTP_BAD_REQUEST, []);
        }

        $updateQuery = 'UPDATE members SET ';
        $params = [];

        foreach ($updatedFields as $field => $value) {
            $updateQuery .= "$field = ?, ";
            $params[] = $value;
        }

        $updateQuery = rtrim($updateQuery, ', ') . ' WHERE reference_code = ?';
        $params[] = $user->reference_code;

        DB::update($updateQuery, $params);

        // Update event_participants as well
        $eventParticipantsUpdateQuery = 'UPDATE event_participants SET ';
        $eventParticipantsParams = [];

        foreach ($updatedFields as $field => $value) {
            // Ensure the column exists in event_participants before updating it
            $eventParticipantsUpdateQuery .= "$field = ?, ";
            $eventParticipantsParams[] = $value;
        }

        $eventParticipantsUpdateQuery = rtrim($eventParticipantsUpdateQuery, ', ') . ' WHERE reference_code = ?';
        $eventParticipantsParams[] = $user->reference_code;

        DB::update($eventParticipantsUpdateQuery, $eventParticipantsParams);

        // Send Push Notification after update
        $member = Member::where('reference_code', $user->reference_code)->first();

        if ($member) {
            NotificationHelper::sendPushNotification(
                $member->id,
                $updatedFields['participant'] ?? $member->participant ?? 'Participant',
                'Profile Updated',
                'Your participant details have been successfully updated.',
                []
            );
        }

        return Helper::APIResponse(1, 'Member details updated successfully.', HTTP_SUCCESS, [$updatedFields]);
    }


    public function logout(Request $request)
    {
        $user = $request->user();
//        dd($user);

        $user->tokens->each(function ($token) {
            $token->delete();
        });

        return Helper::APIResponse(1, 'Logged out successfully.', HTTP_SUCCESS, []);

    }

    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        if (!empty($user->password)) {
            $rules['current_password'] = ['required'];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errorMessages = $validator->errors()->all();
            return Helper::APIResponse(0, $errorMessages[0] ?? 'Validation errors.', 422, $validator->errors()->toArray());
        }

        if (!empty($user->password)) {
            if (!Hash::check($request->input('current_password'), $user->password)) {
                return Helper::APIResponse(0, 'Current password is incorrect.', 422, []);
            }
        }

        $user->password = Hash::make($request->input('new_password'));
        $user->password_set = true;
        $user->save();

        return Helper::APIResponse(1, 'Password changed successfully.', 200, []);
    }

    public function setMemberPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $member = Member::where('email_address', $request->email)->first();

        if (!$member) {
            return Helper::APIResponse(0, 'Member not found.', 404, []);
        }

        $member->password = Hash::make($request->input('password'));
        $member->password_set = true;
        $member->save();

        $access_token = $member->createToken('auth_token')->plainTextToken;

        return Helper::APIResponse(1, 'Password set successfully.', 200, [
            'user_id' => $member->id,
            'access_token' => $access_token,
            'token_type' => 'Bearer',
        ]);
    }

    public function showAccountDeletionForm()
    {
        return view('account_deletion'); // A Blade view you'll create next
    }

    public function handleAccountDeletionRequest(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:members,email_address',
        ]);

        $email = $request->input('email');
        $member = Member::where('email_address', $email)->first();

        if ($member) {
            // Log or store the request
            DB::table('account_deletion_requests')->insert([
                'user_id' => $member->id,
                'email' => $email,
                'requested_at' => now()
            ]);

            // Optionally notify admin or send confirmation
//            Mail::to($email)->send(new AccountDeletionRequestMail($member));
        }

        return back()->with(1, 'Your deletion request has been submitted. We will process it shortly.');
    }

    public function accountDeletionLink(){
        $link = "https://mei.iot.mw/engineers/api/account-deletion";
        return Helper::APIResponse(1, 'Account Deletion Link retrieved', HTTP_SUCCESS, [$link]);
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
