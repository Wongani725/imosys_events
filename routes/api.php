<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ConferencePackRedeemController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\MasterMealTagController;
use App\Http\Controllers\MealCouponController;
use App\Http\Controllers\MobileAppRetrieveController;
use App\Http\Controllers\OfflineScanningController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\ParticipantEventRegistrationController;
use App\Http\Controllers\PrintJobController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;

// Sanctum authenticated user endpoint
Route::middleware('auth:sanctum')->get('/user', fn(Request $request) => $request->user());
// All authenticated routes (auth:sanctum)
Route::middleware('auth:sanctum')->group(function () {

    // JONES
    Route::post('update-user-firebase-token', [UserController::class, 'updateFirebaseTokenAPI']);
    Route::get('my-profile', [UserController::class, 'userProfileAPI']);
    Route::post('fn-qr-code', [ParticipantEventRegistrationController::class, 'scanQRCode']);
    Route::post('registration-confirm', [ParticipantEventRegistrationController::class, 'registerParticipant']);

    // SARAH
    Route::post('get-participants-by-hotel', [ParticipantController::class, 'getTotalParticipantsByHotel']);
    Route::post('get-redeemed-coupons-by-hotel', [ParticipantController::class, 'getRedeemedCouponsByHotel']);
    Route::get('events-list', [EventController::class, 'listAPI']);
    Route::get('all-initial-registrations', [EventController::class, 'allInitialRegistrations']);
    Route::get('all-meal-coupons', [EventController::class, 'allMealCoupons']);
    Route::get('get-upcoming-events', [EventController::class, 'upcomingEvents']);
    Route::post('/get-conference-hall-registrations', [ParticipantEventRegistrationController::class, 'getConferenceHallRegistrations']);
    Route::post('/get-initial-registrations', [ParticipantEventRegistrationController::class, 'getInitialRegistrations']);
    Route::post('/get-meal-coupons', [MealCouponController::class, 'getMealCoupons']);
    Route::post('/sync-initial-registrations', [ParticipantEventRegistrationController::class, 'syncInitialRegistrations'])->name('sync-initial-registrations');
    Route::post('/sync-conference-hall-registrations', [ParticipantEventRegistrationController::class, 'syncConferenceHallRegistrations'])->name('sync-conference-hall-registrations');
    Route::post('/sync-meal-coupon', [MealCouponController::class, 'syncMealCoupon'])->name('sync-meal-coupon');

    Route::post('/hotels', [HotelController::class, 'getHotelNames']);
    Route::post('/event-participants', [EventController::class, 'getParticipantsByEventID']);
    Route::get('event-sessions', [EventController::class, 'getSessions']);

    // Offline scanning
    Route::get('participants', [OfflineScanningController::class, 'getParticipants']);
    Route::get('meal-coupons', [OfflineScanningController::class, 'getMealCoupons']);
    Route::get('meal-scans', [OfflineScanningController::class, 'getMealScans']);
    Route::get('meal-selections', [OfflineScanningController::class, 'getMealSelections']);
    Route::get('registered-participants', [OfflineScanningController::class, 'getInitialRegistrations']);
    Route::get('conference-attendants', [OfflineScanningController::class, 'getConferenceAttendance']);

    Route::post('register-attendance', [EventController::class, 'registerAttendance']);
    Route::post('/redeem-meals', [MealCouponController::class, 'redeemMeals'])->name('redeem-meals');
    Route::post('/show-meal-information', [MealCouponController::class, 'showMealInformation'])->name('show-meal-information');
    Route::post('/scan-meal-coupon', [MealCouponController::class, 'ScanMealCoupon']);
    Route::post('print-participant-meal-coupons', [ParticipantController::class, 'PrintParticipantMealCoupons']);
    Route::post('update-participant-meal-coupon', [PrintJobController::class, 'updateMealCouponStatus']);

    Route::post('/master-tags', [MasterMealTagController::class, 'getMasterTags']);

    Route::get('/get-pending-participants', [PrintJobController::class, 'getPendingParticipants']);
    Route::post('/participants/update-status', [PrintJobController::class, 'updateStatus']);
    Route::post('redeem-conference-pack', [ConferencePackRedeemController::class, 'redeemConferencePack']);
    Route::post('/initial-registration', [ParticipantEventRegistrationController::class, 'initialRegistrations']);

    // WONGANI MSUMBA – ICAM Members Mobile App Routes
    Route::prefix('user')->group(function () {
        Route::get('/events-attended', [MobileAppRetrieveController::class, 'getEventsAttendedCount']);
        Route::get('/upcoming-event', [MobileAppRetrieveController::class, 'getUpcomingEvent']);
        Route::get('/lodging-details', [MobileAppRetrieveController::class, 'getUserLodgingDetails']);
        Route::post('/edit-details', [AuthenticationController::class, 'auth_update_participant']);
        Route::post('/registration', [MobileAppRetrieveController::class, 'initialRegistrations']);
        Route::get('/meal-count', [MobileAppRetrieveController::class, 'getMealCount']);
        Route::get('/session-attendance', [MobileAppRetrieveController::class, 'getSessionAttendance']);
        Route::post('/restaurant-choice', [MobileAppRetrieveController::class, 'chooseHotelAndRedeemMeal']);
        Route::get('/hotels', [MobileAppRetrieveController::class, 'getHotels']);
        Route::get('/name-tag', [MobileAppRetrieveController::class, 'getNameTag']);
        Route::get('/program', [MobileAppRetrieveController::class, 'getProgram']);
        Route::get('/evaluation', [MobileAppRetrieveController::class, 'getQuestionsForEvent']);
        Route::get('/certificate', [MobileAppRetrieveController::class, 'getCertificate']);
        Route::post('/submit-evaluation', [MobileAppRetrieveController::class, 'storeEvaluationData']);
        Route::get('/profile', [MobileAppRetrieveController::class, 'getUserProfile']);
        Route::post('/logout', [AuthenticationController::class, 'logout']);
        Route::post('/scan-session-attendance', [AttendanceController::class, 'registerAttendance']);
        Route::get('/extra-meal-coupons', [MobileAppRetrieveController::class, 'getExtraMealCoupons']);
        Route::get('/extra-meal-coupons-history', [MobileAppRetrieveController::class, 'getExtraMealCouponsHistory']);
        Route::post('/restaurant-choice1', [MobileAppRetrieveController::class, 'chooseHotelAndRedeemMeal']);
        Route::post('/update-member-details', [AuthenticationController::class, 'auth_update_participant']);
        Route::get('/terms', [BookingController::class, 'getTerms']);
        Route::get('/policy', [BookingController::class, 'getPrivacyPolicy']);
        Route::get('/status', [BookingController::class, 'getEventPrices']);
        Route::get('/booking-form', [BookingController::class, 'getQuestions']);
        Route::post('/submit-booking-form', [BookingController::class, 'submitBooking']);
        Route::post('/submit-pop', [BookingController::class, 'updatePoP']);
        Route::post('/edit-booking-form', [BookingController::class, 'updateBooking']);
        Route::get('/booking-details', [BookingController::class, 'trackBookingStatus']);
        Route::post('/cancel-booking', [BookingController::class, 'cancelBooking']);
    });

    Route::get('/sponsor-ads', [MobileAppRetrieveController::class, 'getAds']);
    Route::get('/booking/invoice', [BookingController::class, 'getInvoiceLink']);
    Route::post('/user/booking-details', [BookingController::class, 'trackBookingStatus1']);
    Route::post('participant-password-change', [AuthenticationController::class, 'changePassword']);
});

// Public / Guest Routes
Route::get('/account-deletion-link', [AuthenticationController::class, 'accountDeletionLink']);
Route::get('/account-deletion', [AuthenticationController::class, 'showAccountDeletionForm']);
Route::post('/account-deletion', [AuthenticationController::class, 'handleAccountDeletionRequest']);

Route::post('register-app-installation', [GeneralController::class, 'registerAppInstallation']);
Route::get('about', [GeneralController::class, 'aboutDataAPI']);
Route::get('available-countries', [GeneralController::class, 'activeCountries']);

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('new-auth-otp', [AuthController::class, 'resendOneTimePassword']);
Route::post('otp-verification', [AuthController::class, 'verifyOneTimePassword']);
Route::get('/user/policy', [AuthenticationController::class, 'getPolicy']);

Route::post('participant-login', [AuthenticationController::class, 'generateOTPForLogin']);
Route::post('participant-otp-verification', [AuthenticationController::class, 'verifyOTP']);
Route::post('participant-password-verification', [AuthenticationController::class, 'verifyPassword']);
Route::post('participant/set-password', [AuthenticationController::class, 'setMemberPassword']);
Route::post('/register/non-member', [AuthenticationController::class, 'registerNonMember']);