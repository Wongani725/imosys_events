<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Member;
use App\Models\Event;
use App\Models\EventPrices;
use App\Models\Bookers;
use App\Models\Participant;
use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_payment_booking_has_correct_status()
    {
        $member = Member::factory()->create();
        $event = Event::factory()->create();

        $booking = Bookers::factory()->create([
            'event_id' => $event->event_id,
            'reference_code' => $member->reference_code,
            'memberID' => $member->reference_code,
            'email' => $member->email_address,
            'booking_status' => 'Pending Payment',
        ]);

        $this->assertEquals('Pending Payment', $booking->booking_status);
        $this->assertEquals('warning', $booking->status_color);
    }

    public function test_admin_can_create_participant_from_booking()
    {
        $member = Member::factory()->create();
        $event = Event::factory()->create();
        $booking = Bookers::factory()->create([
            'event_id' => $event->event_id,
            'reference_code' => $member->reference_code,
            'booking_status' => 'Pending Payment',
        ]);

        $participant = Participant::factory()->fromBooking($booking)->create();

        $this->assertDatabaseHas('event_participants', [
            'booker_id' => $booking->bookingID,
            'reference_code' => $booking->reference_code,
            'event_id' => $event->event_id,
        ]);
    }

    public function test_booking_with_accommodation()
    {
        $member = Member::factory()->create();
        $event = Event::factory()->create();
        $hotel = Hotel::factory()->create(['event_id' => $event->event_id]);

        $booking = Bookers::factory()
            ->forEvent($event)
            ->forMember($member)
            ->create([
                'accommodation' => true,
                'hotel_id' => $hotel->id,
            ]);

        $this->assertTrue((bool) $booking->accommodation);
        $this->assertEquals($hotel->id, $booking->hotel_id);
    }

    public function test_booking_status_transition_to_confirmed()
    {
        $member = Member::factory()->create();
        $event = Event::factory()->create();

        $booking = Bookers::factory()->create([
            'event_id' => $event->event_id,
            'booking_status' => 'Pending Payment',
        ]);

        $booking->update(['booking_status' => 'Confirmed']);

        $this->assertEquals('Confirmed', $booking->fresh()->booking_status);
        $this->assertEquals('success', $booking->fresh()->status_color);
    }

    public function test_booking_can_be_cancelled()
    {
        $member = Member::factory()->create();
        $event = Event::factory()->create();
        $booking = Bookers::factory()->create([
            'event_id' => $event->event_id,
            'booking_status' => 'Pending Payment',
        ]);

        $booking->update(['booking_status' => 'Cancelled']);

        $this->assertEquals('Cancelled', $booking->fresh()->booking_status);
        $this->assertEquals('secondary', $booking->fresh()->status_color);
    }

    public function test_cancelled_booking_can_be_restored()
    {
        $member = Member::factory()->create();
        $event = Event::factory()->create();
        $booking = Bookers::factory()->create([
            'event_id' => $event->event_id,
            'booking_status' => 'Cancelled',
        ]);

        $booking->update([
            'booking_status' => 'Pending Payment',
            'restored_at' => now(),
        ]);

        $this->assertEquals('Pending Payment', $booking->fresh()->booking_status);
        $this->assertNotNull($booking->fresh()->restored_at);
    }

    public function test_participant_with_accommodation()
    {
        $event = Event::factory()->create();
        $hotel = Hotel::factory()->create(['event_id' => $event->event_id]);

        $participant = Participant::factory()
            ->withAccommodation()
            ->create([
                'event_id' => $event->event_id,
                'hotel_id' => $hotel->id,
            ]);

        $this->assertTrue((bool) $participant->accommodation);
        $this->assertEquals(5, $participant->meals);
    }

    public function test_walkin_participant()
    {
        $event = Event::factory()->create();

        $participant = Participant::factory()
            ->walkin()
            ->create(['event_id' => $event->event_id]);

        $this->assertTrue((bool) $participant->is_walkin);
    }
}
