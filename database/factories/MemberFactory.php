<?php

namespace Database\Factories;

use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MemberFactory extends Factory
{
    protected $model = Member::class;

    public function definition()
    {
        $referenceCode = 'REF-' . strtoupper(Str::random(8));
        return [
            'member_id' => 'MEM-' . $this->faker->unique()->numberBetween(1000, 9999),
            'participant' => $this->faker->name(),
            'email_address' => $this->faker->unique()->safeEmail(),
            'phone_number' => '+265' . $this->faker->numerify('########'),
            'company_name' => $this->faker->company(),
            'status' => 'Member',
            'is_executive' => false,
            'address' => $this->faker->address(),
            'password' => bcrypt('password'),
            'password_set' => true,
            'reference_code' => $referenceCode,
            'datejoined' => now()->toDateString(),
        ];
    }

    public function unsetPassword()
    {
        return $this->state([
            'password' => null,
            'password_set' => false,
        ]);
    }

    public function executive()
    {
        return $this->state(['is_executive' => true]);
    }

    public function nonMember()
    {
        return $this->state(['status' => 'Non-Member']);
    }
}
