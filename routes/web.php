<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController1;
use App\Http\Controllers\FileUpload;
use App\Http\Controllers\OccassionController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\ParticipantEvaluationController;
use App\Http\Controllers\EvaluationFormController;
use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\WebBookingController;
use App\Models\SponsorAd;
use App\Models\Bookers;
use App\Models\AttireSize;
use App\Models\AttireType;
use App\Models\Hotel;
use App\Models\Event;
use App\Models\EventPrices;
use App\Models\AttireColor;
use Carbon\Carbon;

// Fortify password reset routes
Route::get('/forgot-password-admin', function () { return view('auth.forgot-password'); })->middleware('guest')->name('password.request');
Route::post('/forgot-password-admin', [\App\Http\Controllers\AuthController::class, 'sendResetLink'])->middleware('guest')->name('password.email');
Route::get('/reset-password-admin/{token}', function ($token) { return view('auth.reset-password', ['token' => $token]); })->middleware('guest')->name('password.reset.admin');
Route::post('/reset-password-admin', [\App\Http\Controllers\AuthController::class, 'resetPassword'])->middleware('guest')->name('password.update');

Route::redirect("/", 'dashboard');

Route::get('/qrcode/{code}', function ($code) {
    return response(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->generate($code), 200)
        ->header('Content-Type', 'image/svg+xml');
})->name('qrcode');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
])->group(function () {
    /// BY JONES
    Route::get('signout', function (\Illuminate\Http\Request $request) {
        auth("web")->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect("login");
    });

    // GET fallback for logout (handles direct URL access)
    Route::get('logout', function (\Illuminate\Http\Request $request) {
        auth("web")->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect("login");
    });


    Route::get('/doughnut-charts/{evaluationId?}', [App\Http\Controllers\UploadController::class, 'showDoughnutCharts'])->name('doughnut-charts.show');

    Route::get('/evaluation-data', [App\Http\Controllers\UploadController::class, 'showEvaluationData'])->name('evaluation-data');
    Route::get('/show-graph', 'GraphController@showGraph')->name('show-graph');
    Route::get('/evaluation-graph', 'EvaluationController@showGraph')->name('evaluation-graph');
    Route::get('/view-evaluation/{evaluationId}', 'EvaluationController@viewEvaluation')->name('view-evaluation');
    Route::get('/view-evaluation/{evaluationId}', [App\Http\Controllers\UploadController::class, 'viewEvaluation'])->name('view-evaluation');



    Route::get('/show-graph', [App\Http\Controllers\UploadController::class, 'showGraph'])->name('show-graph');
    // Evaluation Form
    Route::get("/evaluation/questions/index/{reference?}", [ParticipantEvaluationController::class, 'index'])->name("evaluation_questions_index");

    //user routes
    Route::get('user/index', [\App\Http\Controllers\UserController::class, 'index'])->name('user_index');

    //image upload

    Route::post('/upload', [\App\Http\Controllers\EventController::class, 'upload'])->name('upload');
    Route::post('/upload', [\App\Http\Controllers\EventController::class, 'upload'])->name('upload');
    Route::get('/events', [\App\Http\Controllers\UploadController::class, 'sendEvaluationForms'])->name('events');

//events routes
    //dashboard
    //Route::get('/report/{id}', 'ReportController@show');
    // Route::get('/report/{id}', [\App\Http\Controllers\ReportController::class, 'show'])->name('report');

    Route::get('/view-progress', [\App\Http\Controllers\AuthorizationController::class, 'viewProgress'] )->name("view-progress");


    Route::get('/authorization_logs/{id?}', [\App\Http\Controllers\AuthorizationController::class, 'logs'] )->name("auth.logs");
    Route::get('/pending_auth/{id?}', [\App\Http\Controllers\AuthorizationController::class, 'pending'] )->name("auth.pending");
    Route::get('/updateApprove_action/{reference_id?}', [\App\Http\Controllers\TempFileUploadController ::class, 'updateApproveParticipant'] )->name("auth.updateApprove");
    Route::get('/approve_action/{reference_id?}', [\App\Http\Controllers\TempFileUploadController ::class, 'authorizeParticipant'] )->name("auth.approve");
    Route::get('/approveBulk_action/{reference_id?}', [\App\Http\Controllers\TempFileUploadController ::class, 'authorizeBulkParticipant'] )->name("auth.bulk.approve");
    Route::get('/decline_action/{reference_id?}', [\App\Http\Controllers\TempFileUploadController ::class, 'declineParticipant'] )->name("auth.decline");
    Route::get('/preview_action/{reference_id?}', [\App\Http\Controllers\TempFileUploadController ::class, 'previewParticipant'] )->name("auth.preview");

    Route::get('onsite-registration-report', [App\Http\Controllers\ReportController::class, 'onsiteRegistrationReport'])->name('onsite-registration-report');
    Route::get('conference-hall-registration', [App\Http\Controllers\ReportController::class, 'conferenceHallRegistration'])->name('conference-hall-registration');

    Route::get('onsite-registration-report/{event}', [App\Http\Controllers\ReportController::class, 'onsiteRegistrationReport2'])->name('onsite-registration-report2');
    Route::get('event-report/{event}', [App\Http\Controllers\ReportController::class, 'eventReport'])->name('event-report');
    Route::get('event-report2/{event}', [App\Http\Controllers\ReportController::class, 'eventReport2'])->name('event-report2');
    Route::get('event-report3/{event}', [App\Http\Controllers\ReportController::class, 'eventReport3'])->name('event-report3');
    Route::get('event-report4/{event}', [App\Http\Controllers\ReportController::class, 'eventReport4'])->name('event-report4');

    Route::get('/hotel-participants/{hotel_name}', [App\Http\Controllers\ReportController::class, 'getParticipantsByHotel'])
        ->name('hotel-participants');

    Route::get('/hotel-participants-lunch/{hotel_name}', [App\Http\Controllers\ReportController::class, 'getParticipantsByHotelLunch'])
        ->name('hotel-participants-lunch');

    Route::get('/hotel-participants-supper/{hotel_name}', [App\Http\Controllers\ReportController::class, 'getParticipantsByHotelSupper'])
        ->name('hotel-participants-supper');

    Route::get('/report-dev/{event_id?}', [\App\Http\Controllers\ReportController::class, 'mealCouponsPerParticipant'])->name('report_dev');
    Route::get('/report', [\App\Http\Controllers\ReportController::class, 'show'])->name('report');
    Route::get('/hotel-meal-report', [\App\Http\Controllers\ReportController::class, 'hotelMealReport'])->name('hotel-meal-report');
    Route::get('/participation-report', [\App\Http\Controllers\ReportController::class, 'participationReport'])->name('participation_report');
    Route::get('export-participation-attires', [\App\Http\Controllers\ReportController::class, 'exportParticipationAttires'])->name('participation-attires.export');

    Route::get('/participant-meal-report', [\App\Http\Controllers\ReportController::class, 'participantMealReport'])->name('participant-meal-report');
    Route::get('/failed_emails/{id?}', [\App\Http\Controllers\FileUploadController::class, 'showFailedEmails'])->name('failed_emails');

    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard-two/{id?}', [\App\Http\Controllers\DashboardController::class, 'showDashboard'])->name('dashboard-two');

    //list of events
    Route::get('/events', [\App\Http\Controllers\EventController::class, 'index'] )->name("events");

    Route::get('/send_evaluation_form', [\App\Http\Controllers\UploadController::class, 'sendEvaluationForm'] )->name("send_evaluation_form");
    //delete event
    Route::get('/delete_event/{id}', [\App\Http\Controllers\EventController::class, 'delete_event'])->name('delete_event/{id}');
    //edit event
    Route::get('/edit_event/{id}',[\App\Http\Controllers\EventController::class, 'edit_event'])->name('edit_event');
    //update event
    Route::post('/update_event',[\App\Http\Controllers\EventController::class, 'update_event'])->name('update_event');
    //add event
    Route::get('/add_event', [\App\Http\Controllers\EventController::class, 'add_event'])->name('add_event');
    Route::post('/add_event2', [\App\Http\Controllers\EventController::class, 'add_event2'])->name('add_event2');
    //import event
    Route::get('/fileupload_evaluation/{id?}', [\App\Http\Controllers\UploadController::class, 'index'])->name('fileupload_evaluation');
    //mailtest
    Route::get('/mailTest', [\App\Http\Controllers\FileUploadController::class, 'sendMail'])->name('testEmail');

    Route::get('/fileupload', [\App\Http\Controllers\FileUploadController::class, 'index'])->name('fileupload');

//    Route::get('/fileupload/{id?}', [\App\Http\Controllers\FileUploadController::class, 'index'])->name('fileupload2');
//    Route::get('/email_certificates/{id?}', [\App\Http\Controllers\EmailCertificatesController::class, 'index'])->name('email_certificates');
    Route::post('/import_file', [\App\Http\Controllers\FileUploadController::class, 'importData'])->name('import_file');
    Route::post('/import_evaluation', [\App\Http\Controllers\UploadController::class, 'importData'])->name('import_evaluation');
    Route::get('download/apk', [App\Http\Controllers\UploadController::class, 'downloadApk'])->name('download.apk');
    Route::get('change-password', [App\Http\Controllers\AuthController::class, 'showChangePasswordForm'])->name('change.password');
    Route::post('update-password', [App\Http\Controllers\AuthController::class, 'updatePassword'])->name('update.password');

    Route::post('/email_certificates', [\App\Http\Controllers\EmailCertificatesController::class, 'importData'])->name('email_certificates');
    Route::post('/import_program/{id?}', [\App\Http\Controllers\EventController::class, 'importProgram'])->name('import_program');

//Create Event Sessions
    //view sessions and add
    Route::get('/view-sessions/{id?}', [\App\Http\Controllers\EventController::class, 'view_sessions'])->name('view_sessions');
    //add session

    Route::get('/add_programme/{id?}', [\App\Http\Controllers\EventController::class, 'add_programme'])->name('add_programme');

    Route::get('/create_evaluation_questions/{id?}', [\App\Http\Controllers\UploadController::class, 'createEvaluationQuestions'])->name('create_evaluation_questions');
    Route::get('/evaluate-here/{id?}', [UploadController::class, 'editEvaluationQuestions'])->name('evaluate-here');

//    delete reout
    Route::get('/delete_question/{id}', [\App\Http\Controllers\UploadController::class, 'delete_question'])->name('delete_question');


    Route::get('/edit_options/{id}', [\App\Http\Controllers\UploadController::class, 'edit_options'])->name('edit_options');
    Route::post('/update_options/{id}', [\App\Http\Controllers\UploadController::class, 'update_options'])->name('update_options');
    Route::get('/edit_speakers/{id}', [\App\Http\Controllers\UploadController::class, 'edit_speakers'])->name('edit_speakers');
    Route::post('/update_speakers/{id}', [\App\Http\Controllers\UploadController::class, 'update_speakers'])->name('update_speakers');


    //edit evaluation question
    Route::get('/edit_question/{id}',[\App\Http\Controllers\UploadController::class, 'edit_question'])->name('edit_question');
    //update evaluation question ((((((GET JUST ADDED))))))
    Route::post('/update_question2', [\App\Http\Controllers\UploadController::class, 'update_question'])->name('update_question');
//    Route::post('/update_question',[\App\Http\Controllers\UploadController::class, 'update_question'])->name('update_question');
    //Route for displaying database data into form
    Route::get('/display_evaluation_form', [UploadController::class, 'displayEvaluationForm'])->name('display_evaluation_form');


    Route::get('/add_session/{id?}', [\App\Http\Controllers\EventController::class, 'add_session'])->name('add_session');
    Route::post('/add_session2', [\App\Http\Controllers\EventController::class, 'add_session2'])->name('add_session2');
    //delete session
    Route::get('delete_session/{id}', [\App\Http\Controllers\EventController::class, 'delete_session'])->name('delete_session/{id}');
//edit session
    Route::get('edit_session/{id}',[\App\Http\Controllers\EventController::class, 'edit_session'])->name('edit_session');
    Route::post('/update_session',[\App\Http\Controllers\EventController::class, 'update_session'])->name('update_session');

    Route::get('/evaluation2/create', [\App\Http\Controllers\ParticipantEvaluationController::class, 'createForm'])->name('evaluation2.create');
    Route::post('/evaluation2/store', [\App\Http\Controllers\ParticipantEvaluationController::class, 'store'])->name('evaluation2.store');
// web.php
    Route::post('/evaluation_questions/{id?}', [\App\Http\Controllers\ParticipantEvaluationController::class, 'store'])->name('evaluation_questions');
    Route::get('/download_meal_couponss/{id?}', [\App\Http\Controllers\ParticipantController::class, 'download_meal_couponss'])->name('download_meal_couponss');

    Route::get('/download-name-tags', [\App\Http\Controllers\ParticipantController::class, 'nameTagsView'])->name('download-name-tags');
//event participants routes
    Route::get('/download_certificates/{id?}', [\App\Http\Controllers\EmailCertificatesController::class, 'download_certificates'])->name('download_certificates');
    Route::get('/download_name_tags/{id?}', [\App\Http\Controllers\ParticipantController::class, 'nameTagsView'])->name('download_name_tags');
    Route::get('/view_hotels/{id?}', [\App\Http\Controllers\EventController::class, 'view_hotels'])->name('view_hotels');
    Route::get('edit_hotel/{id}',[\App\Http\Controllers\EventController::class, 'edit_hotel'])->name('edit_hotel');
    Route::post('/update_hotel',[\App\Http\Controllers\EventController::class, 'update_hotel'])->name('update_hotel');
    Route::get('/add_hotel/{id?}', [\App\Http\Controllers\EventController::class, 'add_hotel'])->name('add_hotel');
    Route::post('/add_hotel2', [\App\Http\Controllers\EventController::class, 'add_hotel2'])->name('add_hotel2');
    Route::get('delete_hotel/{id}', [\App\Http\Controllers\EventController::class, 'delete_hotel'])->name('delete_hotel/{id}');
    Route::get('download_meal_coupons/{id}', [\App\Http\Controllers\FileUploadController::class, 'download_meal_coupons'])->name('download_meal_coupons');

//
//    Route::post('/events/{eventId}/assign-rooms', [AssignRoomsController::class, 'assignParticipantsToRooms']);
//    Route::post('/assign_participants/{id?}', [\App\Http\Controllers\AssignRoomsController::class, 'assignParticipantsToRooms'])->name('assign_participants');
    Route::post('/events/{eventId}/assign-rooms', [\App\Http\Controllers\AssignRoomsController::class, 'assignParticipantsToRooms'])
        ->name('assign.participants.to.rooms');

    //view participants
    Route::get('/view-participants/{id?}', [\App\Http\Controllers\ParticipantController::class, 'index'])->name('view_participants');
    //delete participant
    Route::get('delete_participant/{id}', [\App\Http\Controllers\ParticipantController::class, 'delete_participant'])->name('delete_participant/{id}');
    //add participant
    Route::get('/add_participant/{id?}', [\App\Http\Controllers\ParticipantController::class, 'add_participant'])->name('add_participant');
    Route::post('/add_participant2', [\App\Http\Controllers\ParticipantController::class, 'add_participant2'])->name('add_participant2');
    //edit participant
    Route::get('edit_participant/{id}',[\App\Http\Controllers\ParticipantController::class, 'edit_participant'])->name('edit_participant');
    Route::post('/update_participant',[\App\Http\Controllers\ParticipantController::class, 'auth_update_participant'])->name('update_participant');
    //Route::post('/update_participant',[\App\Http\Controllers\ParticipantController::class, 'update_participant'])->name('update_participant');

    //show participant details
    Route::get('/update_question/{id1?}/{id2?}', [\App\Http\Controllers\ParticipantController::class, 'show_participant'])->name('show_participant');
    Route::get('/show_participant/{id1}/{id2}', [\App\Http\Controllers\ParticipantController::class, 'show_participant'])->name('show_participant.web');
    Route::get('/view_certificate/{id1?}/{id2?}', [\App\Http\Controllers\ParticipantController::class, 'view_certificate'])->name('view_certificate');
    //event attendant routes
    //add attendant
//    Route::post('/evaluation/{reference_code}/{event_id}', [UploadController::class, 'storeEvaluationData'])->name('evaluation');

    //Route::post('/upload-avatar', [App\Http\Controllers\AvatarController::class, 'upload'])->name('upload-avatar');
    Route::post('/upload-event-location-image', [\App\Http\Controllers\AvatarController::class, 'uploadEventlocationImage'])->name('upload-event-location-image');
//    Route::post('/evaluation/{reference_code}/{event_id}', [UploadController::class, 'storeEvaluationData'])->name('evaluation');

    Route::get('/add_attendant/{id?}', [\App\Http\Controllers\ParticipantController::class, 'add_attendant'])->name('add_attendant');
    Route::post('/add_attendant2', [\App\Http\Controllers\ParticipantController::class, 'add_attendant2'])->name('add_attendant2');
//    Route::post('/participant/send/email/{reference?}', [\App\Http\Controllers\ParticipantController::class, 'sendEmail'])->name('participant_send_email');
//
//    Route::post('/participant/send/email/evaluation/{reference?}', [\App\Http\Controllers\ParticipantController::class, 'sendEmailEvaluation'])->name('participant_send_email_evaluation');

    Route::post('/participant/send/email/evaluation/{reference?}', [\App\Http\Controllers\ParticipantController::class, 'sendEmailEvaluation'])->name('participant_send_email_evaluation');
    Route::post('/participant/send/email/{reference?}', [\App\Http\Controllers\ParticipantController::class, 'sendEmail'])->name('participant_send_email');

    Route::get('/download-certificate-pdf/{reference_code}/{event_id}', [\App\Http\Controllers\ParticipantController::class, 'downloadCertificatePdf'])->name('download_certificate_pdf');

    Route::post('/add-member', [\App\Http\Controllers\SponsorController::class, 'add_member'])->name('add_member');

//    new routes
    Route::get('/get/bookers', [\App\Http\Controllers\BookingController::class, 'index'])->name('get-bookers');
    Route::get('/get/terms', [\App\Http\Controllers\WebBookingController::class, 'index'])->name('get-terms');
    Route::get('/fileupload', [\App\Http\Controllers\FileUploadController::class, 'index'])->name('fileupload');
    Route::put('/members/update', [\App\Http\Controllers\SponsorController::class, 'updateMember'])->name('update_member');
    Route::post('/bookers/send-reminders', [\App\Http\Controllers\BookingController::class, 'sendReminderEmails'])->name('bookers.sendReminderEmails');

    Route::get('/view_participant_fees/{id?}', [\App\Http\Controllers\EventController::class, 'view_participant_fees'])->name('view_participant_fees');
    Route::get('/add_fees/{id?}', [\App\Http\Controllers\EventController::class, 'add_fees'])->name('add_fees');
    Route::post('/add_fees2', [\App\Http\Controllers\EventController::class, 'add_fees2'])->name('add_fees2');
    Route::get('edit_fees/{id}',[\App\Http\Controllers\EventController::class, 'edit_fees'])->name('edit_fees');
    Route::post('/update_fees',[\App\Http\Controllers\EventController::class, 'update_fees'])->name('update_fees');
    Route::get('delete_fees/{id}', [\App\Http\Controllers\EventController::class, 'delete_fees'])->name('delete_fees/{id}');

    Route::get('/view_hotel_capacity/{id?}', [\App\Http\Controllers\EventController::class, 'viewHotelCapacity'])->name('view_hotel_capacity');
    Route::post('/hotels/{id}/update-quantity', [\App\Http\Controllers\EventController::class, 'updateHotelQuantity'])->name('hotels.updateQuantity');
//    Routes for additional provisions
    Route::get('/get/terms', [\App\Http\Controllers\WebBookingController::class, 'index'])->name('get-terms');
    Route::post('/terms', [\App\Http\Controllers\WebBookingController::class, 'store'])->name('terms.store');
    Route::put('/terms/{id}', [\App\Http\Controllers\WebBookingController::class, 'update'])->name('terms.update');

    Route::get('/get/extra_meal_price/{id?}', [\App\Http\Controllers\WebBookingController::class, 'getExtraMeal'])->name('get-extra_meal_price');
    // Route::post('/extra_meal_price', [\App\Http\Controllers\ExtraMealPriceController::class, 'store'])->name('extra_meal_price.store');
    // Route::put('/extra_meal_price/{id}', [\App\Http\Controllers\ExtraMealPriceController::class, 'update'])->name('extra_meal_price.update');

    Route::get('/get/sponsors/{id?}', [\App\Http\Controllers\SponsorController::class, 'getSponsors'])->name('get-sponsors');
    Route::post('/create-sponsors', [\App\Http\Controllers\SponsorController::class, 'store'])->name('sponsors.store');
    Route::put('/sponsors/{id}', [\App\Http\Controllers\SponsorController::class, 'update'])->name('sponsors.update');
    Route::delete('/sponsors/{id}', [\App\Http\Controllers\SponsorController::class, 'destroy'])->name('sponsors.destroy');

    Route::post('/bookers/send-reminders', [\App\Http\Controllers\BookingController::class, 'sendReminderEmails'])->name('bookers.sendReminderEmails');
    Route::put('/members/update', [\App\Http\Controllers\SponsorController::class, 'updateMember'])->name('update_member');
    Route::post('/admin/bookers/{id}/upload-pop', [\App\Http\Controllers\WebBookingController::class, 'adminUploadPoP'])->name('admin.uploadPoP');
    Route::get('/admin/view-pop/{bookingID}', [\App\Http\Controllers\BookingController::class, 'adminViewPoP'])->name('admin.viewPoP');
    Route::post('/admin/bookers/{id}/upload-receipt', [\App\Http\Controllers\SponsorController::class, 'adminUploadReceipt'])->name('admin.uploadReceipt');

    Route::get('/admin/invoice/{booking}', [\App\Http\Controllers\SponsorController::class, 'viewInvoice'])->name('admin.invoice');
    Route::post('/bookers/{id}/approve', [\App\Http\Controllers\BookingController::class, 'confirmPayment']);
    Route::post('/bookers/{id}/confirm-payment', [\App\Http\Controllers\BookingController::class, 'confirmPayment'])->name('bookings.confirm-payment');
    Route::post('/bookers/{id}/decline', [\App\Http\Controllers\BookingController::class, 'decline'])->name('bookings.decline');
    Route::post('/bookers/{id}/cancel', [\App\Http\Controllers\BookingController::class, 'adminCancelBooking'])->name('bookings.cancel');
    Route::post('/bookers/{id}/enter-payment', [\App\Http\Controllers\BookingController::class, 'enterPayment'])->name('bookings.enter-payment');
    Route::post('/bookers/{id}/edit-booking', [\App\Http\Controllers\BookingController::class, 'editBooking'])->name('bookings.edit-booking');
    Route::post('/bookers/{id}/allocate-room', [\App\Http\Controllers\BookingController::class, 'allocateRoom'])->name('bookings.allocate-room');



    Route::get('import_participants/{id}',[\App\Http\Controllers\SponsorController::class, 'import_participants'])->name('import_participants');
    Route::post('/import_members', [\App\Http\Controllers\SponsorController::class, 'importData'])->name('import_members');

    // Member Management Routes
    Route::prefix('admin/members')->name('admin.members.')->group(function () {
        Route::get('/', [\App\Http\Controllers\MemberController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\MemberController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\MemberController::class, 'store'])->name('store');
        Route::get('/import', [\App\Http\Controllers\MemberController::class, 'importForm'])->name('import.form');
        Route::post('/import', [\App\Http\Controllers\MemberController::class, 'import'])->name('import');
        Route::get('/template', [\App\Http\Controllers\MemberController::class, 'downloadTemplate'])->name('template');
        Route::get('/{member}/edit', [\App\Http\Controllers\MemberController::class, 'edit'])->name('edit');
        Route::put('/{member}', [\App\Http\Controllers\MemberController::class, 'update'])->name('update');
        Route::delete('/{member}', [\App\Http\Controllers\MemberController::class, 'destroy'])->name('destroy');
    });

    // Bulk Booking Routes
    Route::prefix('admin/bulk-booking')->name('admin.bulk-booking.')->group(function () {
        Route::get('/', [\App\Http\Controllers\BulkBookingController::class, 'index'])->name('index');
        Route::get('/template', [\App\Http\Controllers\BulkBookingController::class, 'downloadTemplate'])->name('template');
        Route::post('/preview', [\App\Http\Controllers\BulkBookingController::class, 'preview'])->name('preview');
        Route::post('/confirm', [\App\Http\Controllers\BulkBookingController::class, 'confirmImport'])->name('confirm');
        Route::post('/import', [\App\Http\Controllers\BulkBookingController::class, 'import'])->name('import');
        Route::post('/batches/{batchRef}/approve', [\App\Http\Controllers\BulkBookingController::class, 'approveBatch'])->name('batches.approve');
        Route::post('/batches/{batchRef}/notify', [\App\Http\Controllers\BulkBookingController::class, 'notifyBatch'])->name('batches.notify');
        Route::get('/batches/{batchRef}/invoice', [\App\Http\Controllers\BulkBookingController::class, 'viewInvoice'])->name('batches.invoice');
    });

    // Walk-in Registration Routes
    Route::prefix('admin/walkin')->name('admin.walkin.')->group(function () {
        Route::get('/', [\App\Http\Controllers\WalkInController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\WalkInController::class, 'store'])->name('store');
    });

    // Attire Sizes Routes
    Route::prefix('admin/attire-sizes')->name('admin.attire-sizes.')->group(function () {
        Route::get('/', [\App\Http\Controllers\AttireSizeController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\AttireSizeController::class, 'store'])->name('store');
        Route::delete('/{attireSize}', [\App\Http\Controllers\AttireSizeController::class, 'destroy'])->name('destroy');
    });

    // Admin Notifications Routes
    Route::prefix('admin/notifications')->name('admin.notifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\AdminNotificationController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\AdminNotificationController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\AdminNotificationController::class, 'store'])->name('store');
        Route::get('/{notification}', [\App\Http\Controllers\AdminNotificationController::class, 'show'])->name('show');
        Route::delete('/{notification}', [\App\Http\Controllers\AdminNotificationController::class, 'destroy'])->name('destroy');
    });

    // Admin Settings Routes
    Route::prefix('admin/settings')->name('admin.settings.')->group(function () {
        Route::get('/users', [\App\Http\Controllers\AdminUserController::class, 'index'])->name('users');
        Route::get('/users/create', [\App\Http\Controllers\AdminUserController::class, 'create'])->name('create-user');
        Route::post('/users', [\App\Http\Controllers\AdminUserController::class, 'store'])->name('store-user');
        Route::post('/users/{user}/toggle-status', [\App\Http\Controllers\AdminUserController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/users/{user}/reset-password', [\App\Http\Controllers\AdminUserController::class, 'resetPassword'])->name('reset-password');
        Route::delete('/users/{user}', [\App\Http\Controllers\AdminUserController::class, 'destroy'])->name('destroy-user');
    });

    // Event Documents Routes
    Route::prefix('admin/events/{event_id}/documents')->name('admin.documents.')->group(function () {
        Route::get('/', [\App\Http\Controllers\EventDocumentController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\EventDocumentController::class, 'store'])->name('store');
        Route::delete('/{document}', [\App\Http\Controllers\EventDocumentController::class, 'destroy'])->name('destroy');
    });

    // Report Routes
    Route::prefix('admin/reports')->name('admin.reports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ReportDashboardController::class, 'index'])->name('index');
        Route::get('/{type}', [\App\Http\Controllers\ReportDashboardController::class, 'show'])->name('show');
        Route::get('/export/{type}', [\App\Http\Controllers\ReportExportController::class, 'export'])->name('export.index');
    });

    // Master Meal Tags Routes
    Route::prefix('admin/master-meal-tags')->name('admin.master-meal-tags.')->group(function () {
        Route::get('/', [\App\Http\Controllers\MasterMealTagController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\MasterMealTagController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\MasterMealTagController::class, 'store'])->name('store');
        Route::delete('/{masterMealTag}', [\App\Http\Controllers\MasterMealTagController::class, 'destroy'])->name('destroy');
    });

    // Name Tags Routes
    Route::prefix('admin/name-tags')->name('admin.name-tags.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ParticipantController::class, 'nameTagsView'])->name('index');
        Route::get('/download/{event_id}', [\App\Http\Controllers\ParticipantController::class, 'downloadNameTagsPdf'])->name('download');
        Route::get('/master/single/{masterMealTag}', [\App\Http\Controllers\MasterMealTagController::class, 'downloadSingleNameTag'])->name('master.single');
        Route::get('/master/{event_id}', [\App\Http\Controllers\MasterMealTagController::class, 'downloadNameTags'])->name('master');
    });

});
//Route::post('/evaluation/{reference_code}/{event_id}', [UploadController::class, 'storeEvaluationData'])->name('evaluation');
Route::get('/show_certificate/{id1}/{id2}', [\App\Http\Controllers\EmailCertificatesController::class, 'show_certificate'])->name('show_certificate');
Route::get('/show_programme/{id1}', [\App\Http\Controllers\EventController::class, 'show_programme'])->name('show_programme');
Route::get('/show_participant2/{id1}/{id2}', [\App\Http\Controllers\ParticipantController::class, 'show_participant2'])->name('show_participant2');
Route::get('/show-participant/{reference_code}', [\App\Http\Controllers\ParticipantController::class, 'showParticipantConsolidated'])->name('show-participant');
Route::get('/my-event-resources/{reference_code}', [\App\Http\Controllers\ParticipantController::class, 'view_event_resources'])->name('member.event-resources');

Route::post('/upload-avatar', [\App\Http\Controllers\AvatarController::class, 'upload'])->name('upload-avatar');
Route::get('/download_meal_coupon/{id?}', [\App\Http\Controllers\MealCouponController::class, 'download_meal_coupon'])->name('download-meal_coupon');
Route::get("test", [\App\Http\Controllers\FileUploadController::class, 'sendParticipantEmail'])->name('test');
Route::post('/evaluation/{reference_code}/{event_id}', [\App\Http\Controllers\UploadController::class, 'storeEvaluationData'])->name('evaluation-form');




//Evaluation Routes
// Route for displaying the file upload form
Route::get('/upload', [UploadController::class, 'upload'])->name('upload');
Route::get('/show_evaluation_form/{id1}/{id2}', [\App\Http\Controllers\UploadController::class, 'show_evaluation'])->name('show_evaluation_form');
// Route for handling the file upload
Route::post('/upload', 'UploadController@upload')->name('upload.process');

// Route for displaying the evaluation form
//Route::get('/evaluation', 'EvaluationController@showEvaluationForm')->name('evaluation');

// Route for handling the evaluation submission
Route::post('/evaluation', 'EvaluationController@submitEvaluation')->name('evaluation.submit');
Route::post('/evaluation', 'UploadController@submitEvaluation')->name('evaluation.submit');


// Route for displaying the evaluations
Route::get('/evaluations', 'EvaluationController@showEvaluations')->name('evaluations');
//Route::get('/send-evaluation-forms', 'EvaluationFormController@sendEvaluationForms')->name('send.evaluation.forms');


Route::get('/send-evaluation-forms', 'EvaluationFormController@sendEvaluationForms')->name('send.evaluation.forms');


//web booking authentication
Route::get('participant-login', [WebAuthController::class, 'index'])->name('participant.login');
Route::get('forgot-password', [WebAuthController::class, 'showForgotPasswordForm'])->name('member.forgot.password.form');
Route::post('forgot-password', [WebAuthController::class, 'sendForgotPasswordOtp'])->name('member.forgot.password.send');
Route::get('reset-password', [WebAuthController::class, 'showResetPasswordForm'])->name('member.reset.password.form');
Route::post('reset-password', [WebAuthController::class, 'resetPassword'])->name('member.reset.password');
Route::get('profile-setup', [WebAuthController::class, 'showProfileSetupForm'])->name('member.profile.setup.form');
Route::post('profile-setup', [WebAuthController::class, 'saveProfileSetup'])->name('member.profile.setup');
Route::post('participant-login2', [WebAuthController::class, 'generateOTPForLogin']);
Route::post('participant-otp-verification', [WebAuthController::class, 'verifyOTP']);
Route::post('/register/non-member', [WebAuthController::class, 'registerNonMember']);

// Handle OTP generation form submission
Route::post('/login/send-otp', [WebAuthController::class, 'sendOTP'])->name('otp.request');

// Show OTP verification form
Route::get('/verify-otp', [WebAuthController::class, 'showVerifyOTPForm'])->name('otp.verify.form');

Route::post('/login/check', [WebAuthController::class, 'checkLogin'])->name('login.check');
Route::post('/login/with-password', [WebAuthController::class, 'submitPassword'])->name('login.password.submit');

// Handle OTP verification submission
Route::post('/verify-otp', [WebAuthController::class, 'verifyOTP'])->name('otp.verify');

// Show set password form (first-time login)
Route::get('/set-password', [WebAuthController::class, 'showSetPasswordForm'])->name('set-password.form');
Route::post('/set-password', [WebAuthController::class, 'setPassword'])->name('set-password');

// Show non-member registration form
Route::get('/register', [WebAuthController::class, 'showRegisterForm'])->name('register.form');

Route::post('/account-deletion', [\App\Http\Controllers\AuthenticationController::class, 'handleAccountDeletionRequest']);

Route::get('/policy', [\App\Http\Controllers\AuthenticationController::class, 'getPolicy']);


// Handle non-member registration
Route::post('/register', [WebAuthController::class, 'registerNonMember'])->name('register.nonmember');
// Route::get("/app-store-redirect", [\App\Http\Controllers\AppStoreRedirectController::class, "redirect"]);
Route::middleware(['auth:member'])->group(function () {
      Route::get('/member-certificate/{reference_code}/{event_id}', [\App\Http\Controllers\ParticipantController::class, 'view_certificate'])->name('member.certificate');

      Route::get('/member-dashboard', function () {

        $user = Auth::guard('member')->user();

        /*
        |--------------------------------------------------------------------------
        | NORMALIZE MEMBER TYPE
        |--------------------------------------------------------------------------
        */

        $memberType =
            strtolower(trim($user->status)) === 'member'
                ? 'Member'
                : 'Non-Member';

        /*
        |--------------------------------------------------------------------------
        | EVENTS
        |--------------------------------------------------------------------------
        */

        $events = Event::query()

            ->where('event_status', 'active')

            ->orderBy('start_date')

            ->get();

        /*
        |--------------------------------------------------------------------------
        | EVENT IDS
        |--------------------------------------------------------------------------
        */

        $eventIds =
            $events->pluck('event_id');

        /*
        |--------------------------------------------------------------------------
        | EVENT PRICES
        |--------------------------------------------------------------------------
        */

        $eventPrices = EventPrices::query()

            ->whereIn('event_id', $eventIds)

            ->get();

        /*
        |--------------------------------------------------------------------------
        | HOTELS
        |--------------------------------------------------------------------------
        */

        $hotels = Hotel::query()

            ->whereIn('event_id', $eventIds)

            ->orderBy('name')

            ->get();
  
        /*
        |--------------------------------------------------------------------------
        | ATTIRE SIZES
        |--------------------------------------------------------------------------
        */

        $attireSizes = AttireSize::query()

            ->orderBy('id')

            ->get();

        $attireSizesByEvent = $attireSizes->groupBy('event_id');

        /*
        |--------------------------------------------------------------------------
        | SPONSORS
        |--------------------------------------------------------------------------
        */

        $sponsors = SponsorAd::query()

            ->orderBy('priority')
            ->latest()

            ->get();

        /*
        |--------------------------------------------------------------------------
        | CURRENT USER BOOKING
        |--------------------------------------------------------------------------
        */

        $currentBooking = Bookers::query()

            ->where('email', $user->email_address)

            ->whereNotIn('booking_status', ['Cancelled', 'Declined'])

            ->latest()

            ->first();


       $bookings = Bookers::with('event')
        ->where('email', $user->email_address)
        ->whereNotIn('booking_status', ['Cancelled', 'Declined'])
        ->get();

                    $bookingsByEvent = $bookings->groupBy('event_id');


            $eventsWithBookings = $bookings
            ->groupBy('event_id')
            ->map(function ($items) {

                $event = $items->first()->event;

                return [
                    'event_id' => $event->event_id ?? null,
                    'event' => $event,
                    'bookings' => $items,
                    'count' => $items->count(),
                    'statuses' => $items->groupBy('booking_status'),
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | TERMS
        |--------------------------------------------------------------------------
        */

        $terms = DB::table('terms')->whereIn('event_id', $eventIds)->value('terms');

        /*
        |--------------------------------------------------------------------------
        | PRICE MAP FOR FRONTEND
        |--------------------------------------------------------------------------
        */

        $priceMap = $eventPrices->map(function ($price) {

            return [

                'event_id' =>
                    $price->event_id,

                'member_type' =>
                    $price->member_type,

                'accommodation' =>
                    $price->accommodation,

                'hotel' =>
                    $price->hotel,

                'spouse_included' =>
                    $price->spouse_included,

                'price' =>
                    $price->price,

                'extra_person_price' =>
                    $price->extra_person_price,
            ];

        });

        $unreadNotifications = \App\Models\NotificationRecipient::where('member_id', $user->id)
            ->whereNull('read_at')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | JSON DATA FOR FRONTEND (avoid closure in Blade @json)
        |--------------------------------------------------------------------------
        */

        $eventsJson = $events->map(function ($e) {
            return ['event_id' => $e->event_id, 'event_name' => $e->event_name];
        });

        $hotelsJson = $hotels->map(function ($h) {
            $priceCode = str_contains(strtolower($h->name), 'nkopola') ? 'nkopola' : 'sun_n_sand';
            return [
                'id' => $h->id,
                'name' => $h->name,
                'event_id' => $h->event_id,
                'available_count' => $h->available_count,
                'price_code' => $priceCode,
            ];
        });

        $attireSizesJson = $attireSizes->unique('name')->values()->map(function ($s) {
            return ['id' => $s->id, 'name' => $s->name, 'event_id' => $s->event_id];
        });

        return view('web_booking.dashboard', compact(
            'user',
            'memberType',
            'events',
            'eventPrices',
            'priceMap',
            'currentBooking',
            'sponsors',
            'hotels',
            'attireSizes',
            'attireSizesByEvent',
            'terms',
            'bookingsByEvent',
            'eventsWithBookings',
            'unreadNotifications',
            'eventsJson',
            'hotelsJson',
            'attireSizesJson'
        ));

    })->name('member-dashboard');

    // Member Bulk Booking Routes
    Route::prefix('member/bulk-booking')->name('member.bulk-booking.')->group(function () {
        Route::post('/preview', [\App\Http\Controllers\BulkBookingController::class, 'memberPreview'])->name('preview');
        Route::post('/confirm', [\App\Http\Controllers\BulkBookingController::class, 'memberConfirmImport'])->name('confirm');
    });

    Route::post('/book', [WebBookingController::class, 'submitBooking'])->name('book');
    Route::get('/my-booking/{event_id}', [WebBookingController::class, 'getBookingView'])->name('my-booking');
    Route::get('/get-invoice-pdf/{bookingID}', [WebBookingController::class, 'getInvoicePDF'])
    ->name('invoice.pdf');
    Route::get('/get-consolidated-invoice/{bookingID}', [WebBookingController::class, 'getConsolidatedInvoicePDF'])
    ->name('invoice.consolidated');
    Route::post('/upload-proof-of-payment/{bookingID}', [WebBookingController::class, 'updatePoP'])
    ->name('upload.pop');
    Route::get('/view-pop/{bookingID}', [WebBookingController::class, 'viewPoP'])
    ->name('view.pop');

    Route::match(['post', 'put'], '/updateBooking', [WebBookingController::class, 'updateBooking'])->name('updateBooking');
    Route::post('/cancel-booking', [WebBookingController::class, 'cancelBooking'])->name('cancel.booking');
    Route::post('/cancel-booking-web', [WebBookingController::class, 'cancelBookingWeb'])->name('web.cancel.booking');
    Route::post('/restore-booking', [WebBookingController::class, 'restoreBooking'])->name('web.restore.booking');

    Route::get('/evaluation/{event_id}', function ($event_id) {
        $user = Auth::guard('member')->user();
        $event = \App\Models\Event::where('event_id', $event_id)->firstOrFail();

        $refCode = $user->reference_code;
        if (!$refCode) {
            $booking = \App\Models\Bookers::where('email', $user->email_address)
                ->where('event_id', $event_id)
                ->where('booking_status', 'Confirmed')
                ->first();
            $refCode = $booking->reference_code ?? null;
        }

        $participant = \App\Models\Participant::where('reference_code', $refCode)
            ->where('event_id', $event_id)->first();

        if (!$participant) {
            return redirect()->route('member-dashboard')->with('error', 'You are not registered for this event.');
        }

        $eligibility = \App\Helpers\AttendanceService::getAttendanceBreakdown($refCode, $event_id);

        if ($eligibility['percentage'] < 70) {
            return redirect()->route('member-dashboard')
                ->with('error', "You need at least 70% attendance to evaluate. You have {$eligibility['percentage']}% ({$eligibility['attended']}/{$eligibility['total_sessions']} sessions).");
        }

        $alreadySubmitted = \App\Models\EvaluationSubmission::where('reference_code', $refCode)
            ->where('event_id', $event_id)->exists();

        if ($alreadySubmitted) {
            $certUrl = route('download_certificate_pdf', ['reference_code' => $refCode, 'event_id' => $event_id]);
            return view('web_booking.evaluation_done', compact('event', 'certUrl'));
        }

        $questions = \App\Models\EvaluationQuestion::where('event_id', $event_id)->get();
        $speakers = \App\Models\Speaker::where('event_id', $event_id)->get();

        return view('web_booking.evaluation', compact('event', 'questions', 'speakers', 'participant', 'eligibility'));
    })->name('member.evaluation');

    Route::post('/evaluation/{event_id}', function (\Illuminate\Http\Request $request, $event_id) {
        $user = Auth::guard('member')->user();

        $refCode = $user->reference_code;
        if (!$refCode) {
            $booking = \App\Models\Bookers::where('email', $user->email_address)
                ->where('event_id', $event_id)
                ->where('booking_status', 'Confirmed')
                ->first();
            $refCode = $booking->reference_code ?? null;
        }

        $request->validate(['answers' => 'required|array']);

        $alreadySubmitted = \App\Models\EvaluationSubmission::where('reference_code', $refCode)
            ->where('event_id', $event_id)->exists();

        if ($alreadySubmitted) {
            return back()->with('error', 'You have already submitted your evaluation.');
        }

        \App\Models\EvaluationSubmission::create([
            'reference_code' => $refCode,
            'event_id' => $event_id,
            'answers' => $request->answers,
        ]);

        // Save speaker ratings
        if ($request->has('speaker_ratings')) {
            foreach ($request->speaker_ratings as $speakerId => $rating) {
                \App\Models\SpeakerRating::create([
                    'speaker_id' => $speakerId,
                    'reference_code' => $refCode,
                    'event_id' => $event_id,
                    'rating' => $rating,
                    'comment' => $request->speaker_comments[$speakerId] ?? null,
                ]);
            }
        }

        $certUrl = route('download_certificate_pdf', ['reference_code' => $refCode, 'event_id' => $event_id]);
        return redirect()->route('member-dashboard')->with('status', 'Evaluation submitted! Your certificate is ready.');
    })->name('member.evaluation.submit');

    Route::get('/member-change-password', [WebAuthController::class, 'getPassword'])->name('password.view');
    Route::post('/post-change-password', [WebAuthController::class, 'changePassword'])->name('password.change');
    Route::post('/member-logout', [WebAuthController::class, 'logout'])->name('member.logout');

    Route::get('/notifications', function () {
        $user = Auth::guard('member')->user();
        $notifications = \App\Models\NotificationRecipient::with('notification')
            ->where('member_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        return view('web_booking.notifications', compact('notifications'));
    })->name('member.notifications');

    Route::post('/notifications/{notification}/read', function (\App\Models\NotificationRecipient $notification) {
        $notification->update(['read_at' => now()]);
        return back()->with('status', 'Marked as read.');
    })->name('member.notifications.read');

    Route::post('/notifications/read-all', function () {
        $user = Auth::guard('member')->user();
        \App\Models\NotificationRecipient::where('member_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        return back()->with('status', 'All notifications marked as read.');
    })->name('member.notifications.read-all');

});

Route::get('/terms/{event_id}', [\App\Http\Controllers\WebBookingController::class, 'memberTerms'])->name('terms.show');

Route::get('/member/bulk-template', [\App\Http\Controllers\BulkBookingController::class, 'downloadTemplate'])->name('member.bulk-template');