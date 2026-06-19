# IIA Malawi Event Management System - API Contracts

**Base URL**: `/api/`
**Authentication**: Laravel Sanctum (Bearer token)

## Public/Guest Endpoints

| Method | Path | Controller | Purpose |
|--------|------|------------|---------|
| POST | `/api/login` | AuthController@login | Admin login (email + OTP) |
| POST | `/api/register` | AuthController@register | Admin registration |
| POST | `/api/otp-verification` | AuthController@verifyOneTimePassword | Verify OTP |
| POST | `/api/new-auth-otp` | AuthController@resendOneTimePassword | Resend OTP |
| POST | `/api/participant-login` | AuthenticationController@generateOTPForLogin | Member login OTP |
| POST | `/api/participant-otp-verification` | AuthenticationController@verifyOTP | Member OTP verify |
| POST | `/api/participant-password-verification` | AuthenticationController@verifyPassword | Member password login |
| POST | `/api/register/non-member` | AuthenticationController@registerNonMember | Register new non-member |
| POST | `/api/participant/set-password` | AuthenticationController@setMemberPassword | First-time password setup |
| GET | `/api/about` | GeneralController@aboutDataAPI | About data |
| GET | `/api/available-countries` | GeneralController@activeCountries | Country list |
| POST | `/api/register-app-installation` | GeneralController@registerAppInstallation | Track app installs |

## Authenticated Endpoints (Admin/Scanner)

| Method | Path | Controller | Purpose |
|--------|------|------------|---------|
| GET | `/api/user` | Sanctum | Get authenticated user |
| GET | `/api/events-list` | EventController@listAPI | List events |
| GET | `/api/get-upcoming-events` | EventController@upcomingEvents | Upcoming events |
| POST | `/api/get-initial-registrations` | ParticipantEventRegistrationController | Get registrations |
| POST | `/api/sync-initial-registrations` | ParticipantEventRegistrationController | Sync registrations |
| POST | `/api/registration-confirm` | ParticipantEventRegistrationController@registerParticipant | Confirm registration |
| POST | `/api/fn-qr-code` | ParticipantEventRegistrationController@scanQRCode | Scan QR code |
| POST | `/api/redeem-meals` | MealCouponController@redeemMeals | Redeem meal coupon |
| POST | `/api/scan-meal-coupon` | MealCouponController@ScanMealCoupon | Scan meal coupon |
| POST | `/api/sync-meal-coupon` | MealCouponController@syncMealCoupon | Sync meal data |
| GET | `/api/participants` | OfflineScanningController@getParticipants | Get participants (paginated) |
| GET | `/api/meal-coupons` | OfflineScanningController@getMealCoupons | Get meal coupons |
| GET | `/api/registered-participants` | OfflineScanningController@getInitialRegistrations | Get registrations |
| GET | `/api/conference-attendants` | OfflineScanningController@getConferenceAttendance | Get attendance |
| GET | `/api/meal-scans` | OfflineScanningController@getMealScans | Get meal scans |
| POST | `/api/redeem-conference-pack` | ConferencePackRedeemController@redeemConferencePack | Redeem pack |
| POST | `/api/print-participant-meal-coupons` | ParticipantController@PrintParticipantMealCoupons | Print coupons |

## Mobile App User Endpoints (`/api/user/*`)

| Method | Path | Purpose |
|--------|------|---------|
| GET | `/api/user/events-attended` | Count events attended + CPD hours |
| GET | `/api/user/upcoming-event` | Get upcoming event with booking status |
| GET | `/api/user/lodging-details` | User lodging details |
| POST | `/api/user/edit-details` | Update participant details |
| GET | `/api/user/meal-count` | Meal count |
| GET | `/api/user/session-attendance` | Session attendance |
| POST | `/api/user/restaurant-choice` | Choose hotel and redeem meal |
| GET | `/api/user/hotels` | List hotels |
| GET | `/api/user/name-tag` | Get name tag |
| GET | `/api/user/program` | Get event programme |
| GET | `/api/user/evaluation` | Get evaluation questions |
| POST | `/api/user/submit-evaluation` | Submit evaluation |
| GET | `/api/user/certificate` | Get certificate |
| GET | `/api/user/profile` | Get user profile |
| POST | `/api/user/logout` | Logout |
| GET | `/api/user/extra-meal-coupons` | Extra meal coupons |
| GET | `/api/user/terms` | Terms and conditions |
| GET | `/api/user/booking-form` | Get booking questions |
| POST | `/api/user/submit-booking-form` | Submit booking |
| POST | `/api/user/submit-pop` | Upload proof of payment |
| GET | `/api/user/booking-details` | Track booking status |
| POST | `/api/user/cancel-booking` | Cancel booking |

## New Endpoints (Post-Refactoring)

| Method | Path | Purpose |
|--------|------|---------|
| POST | `/api/user/restore-booking/{id}` | Restore cancelled/declined booking |
| GET | `/api/user/notifications` | Get user's notifications |
| POST | `/api/user/mark-notification-read/{id}` | Mark notification as read |
| GET | `/api/user/notification-count` | Get unread notification count |
| GET | `/api/user/evaluation-eligibility/{event_id}` | Check >=70% attendance threshold |
| POST | `/api/user/submit-speaker-rating` | Rate a speaker |
| GET | `/api/user/speakers/{event_id}` | Get speakers for an event |
| GET | `/api/user/event-documents/{event_id}` | Get accessible documents |
| GET | `/api/user/my-event-resources/{reference_code}` | Unified page: program + docs + cert |
| POST | `/api/admin/master-meal-tag` | Create master meal tag |
| GET | `/api/admin/master-meal-tags` | List master meal tags |
| DELETE | `/api/admin/master-meal-tag/{id}` | Delete master meal tag |
| POST | `/api/admin/send-notification` | Create and send notification |
| GET | `/api/admin/notifications-history` | View sent notifications |
| POST | `/api/admin/bulk-import-bookings` | Bulk import bookings via Excel |
| POST | `/api/admin/walkin-register` | Register walk-in participant |
| GET | `/api/admin/reports/{type}/export` | Export report (xlsx/csv/pdf param) |
| GET | `/api/admin/members/import-template` | Download member import template |

## Web AJAX Endpoints (Session-Based, No Sanctum)

| Method | Path | Purpose |
|--------|------|---------|
| GET | `/booking/pricing` | Dynamic pricing fetch |
| POST | `/booking/calculate` | Calculate total cost |
| GET | `/admin/participants/search` | AJAX search participants |
| POST | `/admin/bookings/{id}/approve` | Approve booking |
| POST | `/admin/bookings/{id}/decline` | Decline booking |
| POST | `/admin/bookings/{id}/cancel` | Cancel booking |
| POST | `/admin/invoices/{id}/send` | Send invoice |
| POST | `/admin/invoices/{id}/mark-paid` | Mark invoice paid |
| GET | `/admin/reports/export` | Export reports |
| POST | `/admin/import-members` | Import members Excel |
| POST | `/admin/bulk-booking` | Bulk booking via Excel |
| GET | `/my-event-resources/{reference_code}` | Unified event resources page |
