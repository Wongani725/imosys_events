<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition()
    {
        $eventId = 'IIA-' . strtoupper($this->faker->lexify('???') . '-' . $this->faker->numberBetween(2024, 2027));
        return [
            'event_id' => $eventId,
            'event_type' => $this->faker->randomElement(['governance', 'main']),
            'event_name' => $this->faker->sentence(3),
            'theme' => $this->faker->sentence(4),
            'start_date' => $this->faker->dateTimeBetween('+1 month', '+2 months')->format('Y-m-d'),
            'end_date' => function (array $attrs) {
                return date('Y-m-d', strtotime($attrs['start_date'] . ' +3 days'));
            },
            'event_venue' => $this->faker->company(),
            'venue' => $this->faker->city(),
            'event_status' => 'active',
            'total_sessions' => 5,
            'booking_start_time' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
            'booking_end_time' => function (array $attrs) {
                return date('Y-m-d', strtotime($attrs['start_date'] . ' -1 day'));
            },
        ];
    }

    public function governance()
    {
        return $this->state(['event_type' => 'governance']);
    }

    public function main()
    {
        return $this->state(['event_type' => 'main']);
    }

    public function inactive()
    {
        return $this->state(['event_status' => 'inactive']);
    }
}
