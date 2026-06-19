<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class StartQueueProcessing extends Command
{
    protected $signature = 'queue:start';

    protected $description = 'Start processing the queue';

    public function handle()
    {
        while (true) {
            $newJobsCount = DB::table('jobs')->count();

            if ($newJobsCount > 0) {
                Artisan::call('queue:work', ['--daemon' => true]);
            } else {
                $this->info('No new jobs to process.');
            }

            // Sleep for a while before checking again
            sleep(10); // You can adjust the interval as needed
        }
    }
}
