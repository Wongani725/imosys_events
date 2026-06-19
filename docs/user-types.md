# IIA Malawi Event Management System - User Types & Roles

## Authentication Guards

| Guard | Provider | Model | Purpose |
|-------|----------|-------|---------|
| `web` | `users` | `App\Models\User` | Admin/staff web dashboard |
| `member` | `members` | `App\Models\Member` | Participant web portal & mobile app |

## Admin Roles (Spatie Permission)

| Role | Permissions & Responsibilities |
|------|--------------------------------|
| **Super Admin** | Full system access, manage admin users, all CRUD operations |
| **Admin** | Member import/management, event CRUD, booking management, notifications, reports, bulk import |
| **Finance** | View bookings, mark payments as received, enter amount paid, generate financial reports |

### Role Assignment
- Admin users are created by Super Admin
- New users receive an email with link + temporary credentials
- First login prompts password change

## Member/Participant Types

| Type | Identifier | Description |
|------|------------|-------------|
| **IIA Member** | `member_id` + email | Existing IIA members imported by admin via Excel. Has `is_executive` flag for future privileges. |
| **Non-Member** | email only | General public who self-register on the portal. No `member_id`. |

## Booking Statuses

| Status | Description |
|--------|-------------|
| `Pending Payment` | Booking submitted, invoice sent, awaiting payment |
| `Confirmed` | Payment verified by admin. QR codes, meal coupons, name tags generated |
| `Declined` | Booking rejected by admin (with reason, email sent) |
| `Cancelled` | Booking cancelled by user or admin (with reason, email sent) |

### Status Transitions
```
Pending Payment ──> Confirmed (approve + payment)
Pending Payment ──> Declined (with note)
Pending Payment ──> Cancelled (user or admin)
Declined ──> Pending Payment (user restores)
Cancelled ──> Pending Payment (user restores)
```
- No "Pending" or "Approved" intermediate states (simplified from previous version)
- Invoice sent immediately on submission (not after approval)

## Invoice Statuses

| Status | Description |
|--------|-------------|
| `pending` | Invoice created but not yet sent |
| `sent` | Invoice emailed to booker |
| `paid` | Payment confirmed by admin |
| `overdue` | Payment deadline passed |

## Attendance Modes

| Mode | Description |
|------|-------------|
| `Physical` | In-person attendance (gets meal coupons, QR code, name tag) |
| `Virtual` | Remote attendance (no meal coupons, QR code, or name tag) |

## Middleware

| Middleware | Purpose |
|------------|---------|
| `auth:sanctum` | API token authentication (mobile app, scanners) |
| `auth:member` | Web session auth for participant portal |
| `auth:web` | Web session auth for admin dashboard |
| `restrictParticipantAccess` | Restrict participant data access |
| `verified` | Email verification (Jetstream, admin only) |

## System Access

| Page/Action | Super Admin | Admin | Finance | Member |
|-------------|:-----------:|:-----:|:-------:|:------:|
| Dashboard | ✅ | ✅ | ✅ | ✅ |
| Member Management | ✅ | ✅ | ❌ | ❌ |
| Event CRUD | ✅ | ✅ | ❌ | ❌ |
| Hotel/Fees/Sessions | ✅ | ✅ | ❌ | ❌ |
| Booking Management | ✅ | ✅ | ✅ (view) | ❌ |
| Approve/Decline Bookings | ✅ | ✅ | ❌ | ❌ |
| Enter Payment | ✅ | ✅ | ✅ | ❌ |
| Reports & Exports | ✅ | ✅ | ✅ (financial) | ❌ |
| Notifications | ✅ | ✅ | ❌ | ❌ |
| Master Meal Tags | ✅ | ✅ | ❌ | ❌ |
| User Management | ✅ | ❌ | ❌ | ❌ |
| Book Events | ❌ | ❌ | ❌ | ✅ |
| View/Cancel Booking | ❌ | ❌ | ❌ | ✅ |
| Upload Proof of Payment | ❌ | ❌ | ❌ | ✅ |
| Submit Evaluation | ❌ | ❌ | ❌ | ✅ |
| Download Certificate | ❌ | ❌ | ❌ | ✅ |
