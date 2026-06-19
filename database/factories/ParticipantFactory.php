<?php

namespace Database\Factories;

use App\Models\Participant;
use App\Models\Event;
use App\Models\Bookers;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParticipantFactory extends Factory
{
    protected $model = Participant::class;

    public function definition()
    {
        $referenceCode = 'REF-' . strtoupper($this->faker->lexify('????????'));
        return [
            'event_id' => Event::factory(),
            'reference_code' => $referenceCode,
            'participant' => $this->faker->name(),
            'email_address' => $this->faker->safeEmail(),
            'phone_number' => '+265' . $this->faker->numerify('########'),
            'company_name' => $this->faker->company(),
            'accommodation' => false,
            'event_selection' => 'both',
            'meals' => 2,
            'status' => 'Confirmed',
            'is_walkin' => false,
        ];
    }

    public function walkin()
    {
        return $this->state([
            'is_walkin' => true,
        ]);
    }

    public function fromBooking(Bookers $booking)
    {
        return $this->state([
            'event_id' => $booking->event_id,
            'reference_code' => $booking->reference_code,
            'booker_id' => $booking->bookingID,
            'participant' => $booking->name,
            'email_address' => $booking->email,
            'phone_number' => $booking->phone_number,
            'company_name' => $booking->company,
            'accommodation' => $booking->accommodation,
            'event_selection' => $booking->event_selection,
            'hotel_id' => $booking->hotel_id,
        ]);
    }

    public function withAccommodation()
    {
        return $this->state([
            'accommodation' => true,
            'meals' => 5,
        ]);
    }
}
