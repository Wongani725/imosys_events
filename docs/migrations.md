# IIA Malawi Event Management System - Migration Plan (Refactored)

## Existing Migrations (Batch 1)

| Migration | Purpose |
|-----------|---------|
| `2019_12_14_000001_create_personal_access_tokens_table` | Sanctum tokens |
| `2025_03_25_160142_create_sessions_table` | Laravel sessions |
| `2026_05_24_105600_create_permission_tables` | Spatie permissions |
| `2026_05_24_105700_create_base_tables` | All base tables |
| `2026_05_24_105800_drop_unused_tables` | Drop occassions, meals_owners, etc. |
| `2026_05_24_105801_update_members_table` | Add member_id, password_set, otp_expires |
| `2026_05_24_105802_update_bookers_table` | Add event_selection, accommodation, etc. |
| `2026_05_24_105803_update_event_participants_table` | Add hotel_id, room_type_id, etc. |
| `2026_05_24_105804_update_events_table` | Add event_type, venue |
| `2026_05_24_105805_update_event_prices_table` | Add member_type, accommodation, hotel, etc. |
| `2026_05_24_105806_update_hotel_table` | Add venue_type |
| `2026_05_24_105807_create_room_types_table` | Create room_types (later dropped) |
| `2026_05_24_105808_create_booking_invoices_table` | Create booking_invoices |
| `2026_05_24_105809_add_foreign_keys` | Add FK constraints |

## New Refactoring Migrations (Batch 2+)

### Migration 1: `2026_05_27_000001_add_executive_to_members`
- Adds `is_executive` boolean (default false) to `members` table

### Migration 2: `2026_05_27_000002_add_event_fields`
- Adds to `events`: `background_image`, `certificate_background`, `program_pdf`, `total_sessions`

### Migration 3: `2026_05_27_000003_simplify_hotels_drop_room_types`
- Adds to `hotel`: `quantity`, `available_count`, `booked_count`
- Drops `room_types` table and all FK references
- Removes `room_type_id` from `bookers` and `event_participants`

### Migration 4: `2026_05_27_000004_update_bookers_table`
- Replaces `hotel_choice` string with `hotel_id` FK
- Removes `room_allocated`
- Adds `admin_note`, `cancellation_reason`, `restored_at`

### Migration 5: `2026_05_27_000005_update_event_participants_table`
- Removes `room_type_id`, `room_allocated`
- Adds `spouse_name`, `extras_count`, `booker_id` FK, `is_walkin`, `walkin_added_by` FK

### Migration 6: `2026_05_27_000006_create_event_documents_table`
- Creates `event_documents` (id, event_id FK, title, file_path, type, is_public)

### Migration 7: `2026_05_27_000007_create_speakers_and_ratings_tables`
- Creates `speakers` (id, event_id FK, name, title, photo, bio)
- Creates `speaker_ratings` (id, speaker_id FK, reference_code, event_id FK, rating, comment)

### Migration 8: `2026_05_27_000008_create_notifications_tables`
- Creates `notifications` (id, title, message, audience_type, created_by FK)
- Creates `notification_recipients` (id, notification_id FK, member_id FK, read_at)

### Migration 9: `2026_05_27_000009_create_master_meal_tags_table`
- Creates `master_meal_tags` (id, event_id FK, member_id FK, total_meals, unique_code, qrcode_path, created_by FK)

### Migration 10: `2026_05_27_000010_add_options_to_evaluation`
- Adds `options` text column to `participant_evaluation`

### Migration 11: `2026_05_27_000011_rename_tables_to_clean_conventions`
- Renames (safe tables with model-level access only):
  - `i_user_otp` → `member_otps`
  - `To_form` → `evaluation_submissions`
  - `i_meal_coupons_print_queue` → `meal_coupon_print_queues`
  - `i_user_event` → `user_events`
- Note: `i_participant_event_registrations` NOT renamed (too many raw SQL references in controllers — will rename in later controller refactoring phase)

### Migration 12: `2026_05_27_000012_cleanup_unused_tables`
- Drops: `occassions`, `meals_owners`, `event_attendants`, `file22s`, `files`, `attire_colors`, `attire_types`
- Drops `booking_forms` if empty

## Deleted Models
- `RoomType` — replaced by hotel.quantity/available_count/booked_count
- `Occassion`, `File`, `AttireColor`, `AttireType` — unused
- `BookingForm` — if empty

## New Models
- `Speaker`, `SpeakerRating`, `Notification`, `NotificationRecipient`
- `MasterMealTag`, `EventDocument`, `MemberOtp`
- `EventRegistration`, `EvaluationSubmission`, `MealCouponPrintQueue`, `UserEvent`

## Data Seeding
- **Admin User**: wongani087@gmail.com / password (Super Admin role)
- **Roles**: Super Admin, Admin, Finance (simplified from previous 4)
- **Events**: 2026 Governance Forum (IIA-GF-2026) + 2026 Annual Conference (IIA-AC-2026)
- **Hotels**: Sunbird Nkopola (qty 70), Sun N Sand Holiday Resort (qty 110)
- **Pricing Tiers**: 20 tiers (10 per event type)
- **Demo User**: jdoe@example.com / password (Member, 80% attendance, seeded booking + evaluation data)
- **Countries**: 195 countries seeded for registration dropdown
- **Sponsors**: 7 sponsors across events
- **Attire Sizes**: S, M, L, XL, XXL, XXXL for both events

## Seeders

| Seeder | Purpose |
|--------|---------|
| `CountrySeeder` | Seeds 195 countries |
| `AttireSponsorSeeder` | Seeds attire sizes + sponsor ads |
| `IIAInitialSeeder` | Creates admin user, events, hotels, pricing tiers |
| `DemoSeeder` | Seeds test member with booking, sessions, attendance, questions, speakers |
