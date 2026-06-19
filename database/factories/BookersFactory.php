<?php

namespace Database\Factories;

use App\Models\Bookers;
use App\Models\Event;
use App\Models\Member;
use App\Models\Hotel;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookersFactory extends Factory
{
    protected $model = Bookers::class;

    public function definition()
    {
        $referenceCode = 'REF-' . strtoupper($this->faker->lexify('????????'));
        return [
            'event_id' => Event::factory(),
            'event_selection' => $this->faker->randomElement(['governance', 'main', 'both']),
            'accommodation' => false,
            'spouse_included' => false,
            'extras' => 0,
            'reference_code' => $referenceCode,
            'memberID' => $referenceCode,
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone_number' => '+265' . $this->faker->numerify('########'),
            'company' => $this->faker->company(),
            'total_cost' => $this->faker->randomFloat(2, 50000, 500000),
            'booking_status' => 'Pending Payment',
            'invoice_status' => 'pending',
            'member_type' => 'Member',
        ];
    }

    public function confirmed()
    {
        return $this->state([
            'booking_status' => 'Confirmed',
            'amount_paid' => function (array $attrs) {
                return $attrs['total_cost'] ?? 100000;
            },
            'balance' => 0,
            'invoice_status' => 'paid',
        ]);
    }

    public function cancelled()
    {
        return $this->state([
            'booking_status' => 'Cancelled',
            'cancellation_reason' => $this->faker->sentence(),
        ]);
    }

    public function declined()
    {
        return $this->state([
            'booking_status' => 'Declined',
            'admin_note' => $this->faker->sentence(),
        ]);
    }

    public function withAccommodation()
    {
        return $this->state([
            'accommodation' => true,
            'hotel_id' => Hotel::factory(),
        ]);
    }

    public function forMember(Member $member)
    {
        return $this->state([
            'reference_code' => $member->reference_code,
            'memberID' => $member->reference_code,
            'name' => $member->participant,
            'email' => $member->email_address,
            'company' => $member->company_name,
        ]);
    }

    public function forEvent(Event $event)
    {
        return $this->state(['event_id' => $event->event_id]);
    }
}
