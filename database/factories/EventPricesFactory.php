<?php

namespace Database\Factories;

use App\Models\EventPrices;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventPricesFactory extends Factory
{
    protected $model = EventPrices::class;

    public function definition()
    {
        return [
            'event_id' => Event::factory(),
            'member_type' => $this->faker->randomElement(['Member', 'Non-Member']),
            'accommodation' => false,
            'event_type' => $this->faker->randomElement(['governance', 'main']),
            'price' => $this->faker->randomFloat(2, 50000, 500000),
            'extra_person_price' => 600000.00,
        ];
    }

    public function withAccommodation()
    {
        return $this->state([
            'accommodation' => true,
            'price' => $this->faker->randomFloat(2, 200000, 800000),
        ]);
    }

    public function forGovernance()
    {
        return $this->state(['event_type' => 'governance']);
    }

    public function forMain()
    {
        return $this->state(['event_type' => 'main']);
    }
}
