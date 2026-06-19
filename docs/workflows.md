# IIA Malawi Event Management System - Workflows

## 1. Admin User Management
1. Super Admin creates Admin/Finance user via "Add User" form
2. System sends email with link + temporary credentials
3. User is prompted to change password on first login
4. Roles: Super Admin (full access), Admin (operations), Finance (payments/reports)

## 2. Member Import & Management
**Controller**: `AdminImportController` / `MemberController`

1. Admin navigates to "Members" → "Import"
2. Downloads template Excel (member_id, name, email, phone, company, is_executive)
3. Uploads completed file → system validates each row
4. Creates/updates `Member` records with `status = 'Member'`, `password_set = false`
5. Admin can also add one member at a time via form
6. Admin can edit any member details (including `is_executive` toggle)
7. Members persist across all events (not event-specific)

## 3. Event Creation
**Controller**: `EventController`

1. Admin creates event with:
   - event_id (unique string, e.g. "IIA-GF-2026"), name, theme
   - event_type (governance/main), start/end dates, venue
   - booking window (start/end), background image
   - Certificate background image, program PDF
2. Sub-forms for related entities (all per-event):
   - **Hotels**: name, quantity (sets available_count = quantity, booked_count = 0), venue_type
   - **Sponsors**: name, image upload
   - **Terms & Conditions**: rich text
   - **Event Fees**: member_type, accommodation, hotel, spouse_included, event_type, price, extra_person_price, status/description
   - **Sessions**: date, start_time, end_time, description
   - **Attire Sizes**: size name
   - **Speakers**: name, title, photo, bio
   - **Documents**: title, file, type (brochure/program/report), is_public

## 4. Member Authentication
**Controller**: `WebAuthController` / `MemberAuthController`

1. Member visits login page, enters `member_id` + `email`
2. System checks if member exists:
   - **First time** (`password_set = false`): OTP sent to email → verify OTP → set password + company name → redirect to dashboard
   - **Returning** (`password_set = true`): enter password → dashboard
   - **Forgot password**: send OTP, reset password
3. Non-members: "Register as Non-Member" → fill name, email, phone → OTP → password → login

## 5. Member Booking Flow
**Controller**: `WebBookingController`

1. Member logs in → dashboard shows:
   - Sponsor carousel (auto-rotating)
   - Unread notifications badge
   - Profile setup alert (if company/password missing)
   - Event cards (name, dates, venue, Register/View Details button)
2. Click "Register" on an event card → modal opens:
   - Event pre-selected from card
   - Accommodation: Yes/No dropdown
     - If No: hide hotel/spouse/extras
     - If Yes: show hotel dropdown (hotels with available_count > 0 only; if ALL hotels at 0, hide accommodation option)
     - Spouse: Yes/No (only when accommodation=Yes)
     - Extras: integer (only when accommodation=Yes)
   - Attire Size dropdown
   - Live price calculation via JS (matches member_type + selections against event_prices)
   - Terms checkbox
3. Submit → `booking_status = 'Pending Payment'`:
   - Invoice generated as PDF (matching DOCX template format)
   - Invoice emailed to member
   - Booking appears under "My Bookings"

## 6. Booking Status Machine

```
Submit → Pending Payment (invoice emailed)
  ├── Admin approves + verifies payment → Confirmed
  │     (creates Participant, MealCoupons, QR codes, name tags, sends email)
  ├── Admin declines (with note) → Declined
  │     (sends email with cancellation reason)
  ├── Admin cancels (with note) → Cancelled
  │     (sends email with reason)
  ├── User cancels → Cancelled
  └── User restores (from Cancelled/Declined) → Pending Payment
      (new invoice generated, status back to Pending Payment)
```

## 7. Admin Booking Management
**Controller**: `BookingController`

1. Admin views bookings list (filtered by event, default = latest)
2. Actions per booking:
   - View Details (full booking + member info)
   - View Invoice (stream PDF)
   - Upload Proof of Payment (on behalf of member)
   - Enter Amount Paid → auto-calculates balance (total_cost - amount_paid)
   - Approve (with payment verification)
   - Decline (with note, triggers email)
   - Cancel (with note, triggers email)
3. On **Approve**:
   - Update booking_status = Confirmed
   - Create `event_participants` entry
   - Create meal coupons:
     - Main participant: unique_code = memberID
     - Spouse: unique_code = "{memberID}-SPOUSE"
     - Per extra: unique_code = "{memberID}-EXTRA-{N}"
     - `participant_reference_code` = memberID for all
   - Generate QR codes, send confirmation email

## 8. Meal Calculation (MealCalculator Service)
```
governance + no accommodation: 2 meals (lunch 8, lunch 9 Sep)
governance + accommodation:     5 meals (dinner 7, lunch+dinner 8, lunch+dinner 9 Sep)
main + no accommodation:        2 meals (lunch 11, lunch 12 Sep)
main + accommodation:           5 meals (dinner 10, lunch+dinner 11, lunch+dinner 12 Sep)
both + accommodation:          11 meals (gov 5 + main 5 + lunch+dinner 10 Sep)
```

## 9. Walk-in Registration (On-site)
1. Admin/scanner enters: name, email, phone, company, event, hotel
2. Creates `event_participants` with `is_walkin = true`, `walkin_added_by = admin_id`
3. Generates meal coupon + name tag on the spot
4. Counted separately in dashboard stats

## 10. Bulk Booking via Excel
**Controller**: `BulkBookingController`

1. Admin downloads template Excel
2. Columns: member_id/email, event_id, accommodation, hotel_name, spouse_included, extras, attire_size
3. Each row = one booking for one event
4. If member attends both events, they get 2 rows (one per event_id)
5. System creates bookings with `booking_status = 'Pending Payment'`, generates invoices, sends emails

## 11. Certificate Flow
1. Admin uploads certificate background per event
2. Scanning app tracks session attendance → `attendance_registration` records
3. System calculates: `attendance_% = (attended_sessions / total_sessions) × 100`
4. If ≥ 70%: "Submit Evaluation" button appears on member dashboard
5. After evaluation submitted → certificate generated (DOMPDF) with:
   - Background image, participant name, event name, dates
6. Certificate emailed & downloadable from dashboard

## 12. Name Tag Generation
- Layout: A4 portrait, 4 fronts + 4 backs per sheet
- **Front**: Participant name, company, event, member ID, QR (identity)
- **Back**: "Scan for Event Resources" + QR linking to `{url}/my-event-resources/{reference_code}`
- Access control: shows only programs/docs for events the participant is confirmed in
- Spouse/Extras: same design, labeled "-SPOUSE" / "-EXTRA-N"

## 13. Evaluation System
**Controllers**: `UploadController`, `EvaluationFormController`

1. Admin creates questions per event:
   - Types: radio (1-5 scale), open (text), options (dropdown/select)
   - Options stored as JSON in `options` column
2. Participants submit evaluation (after 70% attendance threshold)
3. After general evaluation, participants rate each speaker separately
4. Results displayed as doughnut/bar charts

## 14. Notifications Module
**Controller**: `NotificationController`

1. Admin creates notification: title + message + audience
2. Audience options: All Members, Non-Members, All, Pending Payment, Confirmed, Governance Attendants, Main Attendants
3. System inserts into `notification_recipients` for matching members
4. Members see notifications in dashboard inbox (unread badge)
5. API endpoint for mobile app

## 15. Master Meal Tags (Secretariat)
**Controller**: `MasterMealTagController`

1. Admin goes to "Master Meal Tags" → "Create New"
2. Selects event, searches/selects member, enters meal count
3. System generates unique QR code, stores in `master_meal_tags`
4. Redeemable via same scanning endpoints as regular meal coupons

## 16. Reporting & Dashboards
**Controllers**: `DashboardController`, `ReportController`

- All reports default to latest event with event filter
- Reports exportable to Excel, CSV, or PDF
- Available reports: Booking Summary, Revenue, Participants, Meal Redemption, Hotel Occupancy, Session Attendance, Daily Check-in, Walk-ins, Evaluation Results, Certificates, Member Demographics, Notification History

## 17. Event Documents
- Admin uploads documents per event (brochures, programs, reports)
- Visibility: public or private (confirmed participants only)
- Participants see documents for events they're confirmed in
- Back-of-name-tag QR links to unified resources page
