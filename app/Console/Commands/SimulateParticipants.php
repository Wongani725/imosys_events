<?php

namespace App\Console\Commands;

use App\Models\Participant;
use Faker\Factory as Faker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SimulateParticipants extends Command
{
    protected $signature = 'participants:simulate {event_id} {count=120}';
    protected $description = 'Simulate participants for name tag testing';

    public function handle()
    {
        $eventId = $this->argument('event_id');
        $count = (int)$this->argument('count');

        if (!DB::table('events')->where('event_id', $eventId)->exists()) {
            $this->error("Event '{$eventId}' not found.");
            return 1;
        }

        $faker = Faker::create();
        $existing = DB::table('event_participants')->where('event_id', $eventId)->count();
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        for ($i = 0; $i < $count; $i++) {
            $num = $existing + $i + 1;
            DB::table('event_participants')->insert([
                'event_id' => $eventId,
                'reference_code' => 'SIM-' . str_pad($num, 4, '0', STR_PAD_LEFT),
                'participant' => $faker->name(),
                'email_address' => $faker->safeEmail(),
                'phone_number' => '+265' . $faker->numerify('########'),
                'company_name' => $faker->company(),
                'status' => 'Confirmed',
                'accommodation' => $faker->boolean(),
                'event_selection' => $faker->randomElement(['governance', 'main']),
                'meals' => $faker->randomElement([2, 5]),
            ]);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Created {$count} participants for event '{$eventId}'.");
    }
}
