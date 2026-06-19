# IIA Malawi Event Management System - Entities

## Admin Users

### User
- **File**: `app/Models/User.php` | **Table**: `users`
- Admin/staff users with Spatie role-based access control
- Key fields: `id`, `name`, `email`, `password`, `status` (active/inactive)
- Traits: `HasRoles`, `HasApiTokens`, `TwoFactorAuthenticatable`
- Roles: Super Admin, Admin, Finance

---

## Member Entities

### Member
- **File**: `app/Models/Member.php` | **Table**: `members`
- IIA members and non-members who can book events (reusable across events)
- Key fields:
  - `id`, `member_id` (unique, nullable for non-members)
  - `participant` (name), `email_address`, `phone_number`, `company_name`
  - `status` (Member/Non-Member), `is_executive` (boolean, default false)
  - `password`, `password_set` (boolean), `otp`, `otp_expires_at`
  - `reference_code`, `address`, `last_active_at`
- Authentication: member_id + email → OTP (first time) or password (returning)
- Relationships: Has many Bookers, Participants, NotificationRecipients, MasterMealTags

### MemberOtp
- **File**: `app/Models/MemberOtp.php` | **Table**: `member_otps`
- Stores OTPs for member authentication
- Key fields: `id`, `email`, `otp`, `reference_code`

---

## Core Event Entities

### Event
- **File**: `app/Models/Event.php` | **Table**: `events`
- Conference events: Governance Forum or Annual Conference
- Key fields:
  - `event_id` (string PK, e.g. "IIA-GF-2026"), `event_type` (governance/main)
  - `event_name`, `theme`, `start_date`, `end_date`
  - `event_venue`, `venue`, `event_status`, `event_gps_coordinates`
  - `background_image`, `certificate_background`, `program_pdf`
  - `total_sessions`, `booking_start_time`, `booking_end_time`
- Relationships: Has many EventSessions, EventPrices, Hotels, Bookers, Participants, Speakers, EventDocuments, MasterMealTags

### EventSession
- **File**: `app/Models/EventSession.php` | **Table**: `event_sessions`
- Sessions within an event (used for attendance tracking)
- Key fields: `session_id`, `event_id`, `session_date`, `start_time`, `end_time`, `description`

### EventPrices
- **File**: `app/Models/EventPrices.php` | **Table**: `event_prices`
- Pricing tiers per event (dynamic, not hardcoded)
- Key fields: `id`, `event_id`, `member_type`, `accommodation`, `hotel`, `spouse_included`, `event_type`, `status` (description), `price`, `extra_person_price`

### EventDocument
- **File**: `app/Models/EventDocument.php` | **Table**: `event_documents`
- Documents/brochures uploaded per event
- Key fields: `id`, `event_id`, `title`, `file_path`, `type`, `is_public`

---

## Hotel Entities

### Hotel
- **File**: `app/Models/Hotel.php` | **Table**: `hotel`
- Partner hotels: Sunbird Nkopola (Governance), Sun N Sand (Main/Both)
- Key fields: `id`, `event_id`, `name`, `venue_type` (governance/main/both)
- `quantity` (total rooms), `available_count`, `booked_count`
- `gps_coordinates`, `extra_price`
- Room types removed — simplified to quantity on hotel directly
- Relationships: Belongs to Event, Has many Bookers, Has many Participants

---

## Booking Entities

### Bookers
- **File**: `app/Models/Bookers.php` | **Table**: `bookers`
- Booking requests
- Key fields:
  - `bookingID` (PK), `event_id`, `memberID` (FK to member reference_code)
  - `event_selection` (governance/main/both), `accommodation`, `hotel_id`
  - `spouse_included`, `extras`, `attire_size_id`
  - `name`, `email`, `phone_number`, `company`
  - `total_cost`, `amount_paid`, `balance`
  - `booking_status`: Pending Payment, Confirmed, Declined, Cancelled
  - `invoice_status`: pending, sent, paid
  - `proof_of_payment`, `admin_note`, `cancellation_reason`, `restored_at`
  - `member_type`, `mode_of_attendance`
- Lifecycle: Pending Payment → Confirmed (or Declined/Cancelled)

### BookingInvoice
- **File**: `app/Models/BookingInvoice.php` | **Table**: `booking_invoices`
- Invoice records linked to bookings
- Key fields: `id`, `booking_id`, `invoice_number`, `amount`, `status`, `sent_at`, `paid_at`

### Participant
- **File**: `app/Models/Participant.php` | **Table**: `event_participants`
- Confirmed participants (created when booking is approved)
- Key fields:
  - `id`, `event_id`, `reference_code`, `booker_id`
  - `participant`, `email_address`, `phone_number`, `company_name`
  - `hotel_id`, `accommodation`, `event_selection`
  - `spouse_name`, `extras_count`
  - `is_walkin` (boolean), `walkin_added_by`
  - `meals`, `extra_meals`, `qrcode_path`, `status`
- Relationships: Belongs to Event, Hotel, Booker. Has many MealCoupons, AttendanceRegistrations, EventRegistrations

---

## Meal & Coupon Entities

### MealCoupon
- **File**: `app/Models/MealCoupon.php` | **Table**: `meal_coupon`
- Meal coupons for confirmed physical attendees
- Key fields: `id`, `event_id`, `participant_reference_code`, `unique_code`, `total_meals`, `qrcode_path`, `meals_redeemed`, `day`, `status`
- Unique code format: `{memberID}` for main, `{memberID}-SPOUSE` for spouse, `{memberID}-EXTRA-{N}` for extras

### MealCouponPrintQueue
- **File**: `app/Models/MealCouponPrintQueue.php` | **Table**: `meal_coupon_print_queues`
- Queue for meal coupon print jobs

### MasterMealTag
- **File**: `app/Models/MasterMealTag.php` | **Table**: `master_meal_tags`
- Secretariat/Staff meal allocation (not tied to a booking)
- Key fields: `id`, `event_id`, `member_id`, `total_meals`, `unique_code`, `qrcode_path`, `created_by`

---

## Attendance & Registration

### AttendanceRegistration
- **File**: `app/Models/AttendanceRegistration.php` | **Table**: `attendance_registration`
- Session attendance records
- Key fields: `id`, `reference_code`, `session_id`, `event_id`, `check_in_time`

### EventRegistration
- **File**: `app/Models/EventRegistration.php` | **Table**: `event_registrations`
- Event check-in/registration records (renamed from i_participant_event_registrations)
- Key fields: `id`, `reference_code`, `participant_name`, `event_id`, `registration_date_time`, `conference_pack_redeemed`

---

## Evaluation Entities

### EvaluationQuestion
- **File**: `app/Models/EvaluationQuestion.php` | **Table**: `participant_evaluation`
- Evaluation questions per event
- Key fields: `id`, `event_id`, `questions`, `type` (radio/open/options), `options` (JSON for radio/select)

### EvaluationSubmission
- **File**: `app/Models/EvaluationSubmission.php` | **Table**: `evaluation_submissions`
- Evaluation form submissions (renamed from To_form)
- Key fields: `id`, `reference_code`, `event_id`, `answers` (JSON)

### Speaker
- **File**: `app/Models/Speaker.php` | **Table**: `speakers`
- Speakers per event, rated by participants
- Key fields: `id`, `event_id`, `name`, `title`, `photo`, `bio`

### SpeakerRating
- **File**: `app/Models/SpeakerRating.php` | **Table**: `speaker_ratings`
- Individual speaker ratings from participants
- Key fields: `id`, `speaker_id`, `reference_code`, `event_id`, `rating` (1-5), `comment`

---

## Communication Entities

### Notification
- **File**: `app/Models/Notification.php` | **Table**: `notifications`
- Admin-created announcements
- Key fields: `id`, `title`, `message`, `audience_type`, `created_by`

### NotificationRecipient
- **File**: `app/Models/NotificationRecipient.php` | **Table**: `notification_recipients`
- Links notifications to member recipients
- Key fields: `id`, `notification_id`, `member_id`, `read_at`

---

## Other Entities

### SponsorAd
- **File**: `app/Models/SponsorAd.php` | **Table**: `sponsor_ads`
- Sponsor advertisements per event
- Key fields: `id`, `sponsor`, `event_id`, `image`, `start_date`, `end_date`

### Terms
- **File**: `app/Models/Terms.php` | **Table**: `terms`
- Terms and conditions per event
- Key fields: `id`, `terms`, `event_id`

### AttireSize
- **File**: `app/Models/AttireSize.php` | **Table**: `attire_sizes`
- Clothing sizes per event (S, M, L, XL, XXL)
- Key fields: `id`, `name`, `event_id`

### Country
- **File**: `app/Models/Country.php` | **Table**: `countries`
- Country list for registration

### UserEvent
- **File**: `app/Models/UserEvent.php` | **Table**: `user_events`
- Links admin users to events (renamed from i_user_event)
- Key fields: `id`, `event_id`, `user_id`, `status`
