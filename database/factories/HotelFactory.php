<?php

namespace Database\Factories;

use App\Models\Hotel;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class HotelFactory extends Factory
{
    protected $model = Hotel::class;

    public function definition()
    {
        return [
            'event_id' => Event::factory(),
            'venue_type' => $this->faker->randomElement(['governance', 'main', 'both']),
            'name' => $this->faker->company() . ' Hotel',
            'quantity' => 50,
            'available_count' => 50,
            'booked_count' => 0,
            'extra_price' => $this->faker->randomFloat(2, 50000, 200000),
        ];
    }

    public function full()
    {
        return $this->state([
            'available_count' => 0,
            'booked_count' => 50,
        ]);
    }
}
