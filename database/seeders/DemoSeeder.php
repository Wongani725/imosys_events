<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Member;
use App\Models\Bookers;
use App\Models\Participant;
use App\Models\EventSession;
use App\Models\EvaluationQuestion;
use App\Models\Speaker;
use App\Models\Event;
use App\Models\Hotel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DemoSeeder extends Seeder
{
    public function run()
    {
        $eventId = 'IIA-GF-2026';

        // 1. Create test member
        $member = Member::firstOrCreate(
            ['email_address' => 'jdoe@example.com'],
            [
                'member_id' => 'TEST-001',
                'participant' => 'John Doe',
                'phone_number' => '0999123456',
                'company_name' => 'ACME Corp Malawi',
                'status' => 'Member',
                'password' => Hash::make('password'),
                'password_set' => true,
                'is_executive' => false,
                'reference_code' => 'TEST-001',
            ]
        );

        // 2. Create event sessions if empty
        $existingSessions = EventSession::where('event_id', $eventId)->count();
        if ($existingSessions === 0) {
            $dates = [
                '2026-09-07', '2026-09-08', '2026-09-09', '2026-09-10', '2026-09-11',
            ];
            foreach ($dates as $date) {
                EventSession::create([
                    'event_id' => $eventId,
                    'session_date' => $date,
                    'start_time' => '08:00:00',
                    'end_time' => '12:00:00',
                    'description' => 'Morning',
                ]);
                EventSession::create([
                    'event_id' => $eventId,
                    'session_date' => $date,
                    'start_time' => '13:00:00',
                    'end_time' => '17:00:00',
                    'description' => 'Afternoon',
                ]);
            }
            // Update total sessions on event
            Event::where('event_id', $eventId)->update(['total_sessions' => 10]);
        }

        // 3. Create booking (Confirmed)
        $existingBooking = Bookers::where('email', 'jdoe@example.com')
            ->where('event_id', $eventId)->first();
        if (!$existingBooking) {
            Bookers::create([
                'event_id' => $eventId,
                'reference_code' => 'TEST-001',
                'event_selection' => 'governance',
                'accommodation' => true,
                'hotel_id' => Hotel::where('event_id', $eventId)->first()?->id,
                'spouse_included' => false,
                'extras' => 0,
                'name' => 'John Doe',
                'email' => 'jdoe@example.com',
                'phone_number' => '0999123456',
                'company' => 'ACME Corp Malawi',
                'member_type' => 'Member',
                'booking_status' => 'Confirmed',
                'invoice_status' => 'paid',
                'total_cost' => 2250000,
                'amount_paid' => 2250000,
                'balance' => 0,
                'memberID' => 'TEST-001',
            ]);
        }

        // 4. Create participant
        $existingParticipant = Participant::where('reference_code', 'TEST-001')
            ->where('event_id', $eventId)->first();
        if (!$existingParticipant) {
            $qrPath = 'qrcodes/TEST-001_demo.svg';
            $qrDir = public_path('qrcodes');
            if (!is_dir($qrDir)) mkdir($qrDir, 0755, true);
            if (!file_exists(public_path($qrPath))) {
                QrCode::generate('TEST-001', public_path($qrPath));
            }

            Participant::create([
                'event_id' => $eventId,
                'reference_code' => 'TEST-001',
                'participant' => 'John Doe',
                'email_address' => 'jdoe@example.com',
                'phone_number' => '0999123456',
                'company_name' => 'ACME Corp Malawi',
                'status' => 'Member',
                'accommodation' => true,
                'hotel_id' => Hotel::where('event_id', $eventId)->first()?->id,
                'event_selection' => 'governance',
                'meals' => 5,
                'qrcode_path' => $qrPath,
            ]);
        }

        // 5. Attendance records (8 out of 10 = 80%)
        $sessions = EventSession::where('event_id', $eventId)->get();
        $attendedCount = DB::table('attendance_registration')
            ->where('reference_code', 'TEST-001')
            ->where('event_id', $eventId)->count();

        if ($attendedCount < 8) {
            $targetSessions = $sessions->take(8);
            foreach ($targetSessions as $session) {
                $exists = DB::table('attendance_registration')
                    ->where('reference_code', 'TEST-001')
                    ->where('session_id', $session->session_id)
                    ->exists();
                if (!$exists) {
                    DB::table('attendance_registration')->insert([
                        'reference_code' => 'TEST-001',
                        'session_id' => $session->session_id,
                        'event_id' => $eventId,
                        'created_at' => now(),
                    ]);
                }
            }
        }

        // 6. Evaluation questions
        if (EvaluationQuestion::where('event_id', $eventId)->count() === 0) {
            $questions = [
                [
                    'questions' => 'How would you rate the overall organization of this event?',
                    'type' => 'radio',
                    'options' => null,
                ],
                [
                    'questions' => 'How did you hear about this event?',
                    'type' => 'options',
                    'options' => 'Email,Social Media,Colleague,Website,Other',
                ],
                [
                    'questions' => 'What did you find most valuable about the event?',
                    'type' => 'open',
                    'options' => null,
                ],
                [
                    'questions' => 'Would you recommend this event to others?',
                    'type' => 'radio',
                    'options' => null,
                ],
            ];

            foreach ($questions as $q) {
                EvaluationQuestion::create([
                    'event_id' => $eventId,
                    'questions' => $q['questions'],
                    'type' => $q['type'],
                    'options' => $q['options'],
                ]);
            }
        }

        // 7. Speakers
        if (Speaker::where('event_id', $eventId)->count() === 0) {
            $speakers = [
                ['name' => 'Dr. Grace Banda', 'title' => 'Chief Audit Executive, Reserve Bank of Malawi', 'bio' => 'Over 15 years in internal audit leadership.'],
                ['name' => 'Mr. Charles Nkhoma', 'title' => 'Director of Governance, Public Procurement Authority', 'bio' => 'Expert in public sector governance frameworks.'],
                ['name' => 'Ms. Linda Phiri', 'title' => 'Risk Management Consultant, Deloitte Malawi', 'bio' => 'Specializes in enterprise risk and compliance.'],
            ];

            foreach ($speakers as $s) {
                Speaker::create([
                    'event_id' => $eventId,
                    'name' => $s['name'],
                    'title' => $s['title'],
                    'bio' => $s['bio'],
                ]);
            }
        }

        $this->command->info('Demo data seeded successfully!');
        $this->command->info('Login as jdoe@example.com / password');
        $this->command->info('John has 80% attendance and can evaluate the Governance Forum.');
    }
}
