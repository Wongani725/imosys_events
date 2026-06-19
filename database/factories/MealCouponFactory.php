<?php

namespace Database\Factories;

use App\Models\MealCoupon;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class MealCouponFactory extends Factory
{
    protected $model = MealCoupon::class;

    public function definition()
    {
        return [
            'event_id' => Event::factory(),
            'participant_reference_code' => 'REF-' . strtoupper($this->faker->lexify('????????')),
            'unique_code' => strtoupper($this->faker->bothify('MC-####-????')),
            'total_meals' => 1,
            'status' => 'pending',
        ];
    }

    public function redeemed()
    {
        return $this->state(['status' => 'redeemed']);
    }
}
