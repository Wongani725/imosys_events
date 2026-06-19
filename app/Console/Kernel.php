<?php
namespace App\Console;
use Illuminate\Console\Command;
use App\Console\Commands\SendUpdatesCommand;
use App\Console\Commands\StartQueueProcessing;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('disposable:update')->weekly();
        $schedule->command('send:updates')->everyMinute();
//        $schedule->command('queue:start')->everyMinute();
        $schedule->command('queue:work --tries=100')->everyMinute()->withoutOverlapping();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        parent::commands();
        $this->command('send:updates', function () {
            $this->call(SendUpdatesCommand::class);
        });
        $this->command('queue:start', function () {
            $this->call(StartQueueProcessing::class);
        });
        require base_path('routes/console.php');


    }


    /**
     * Boot the application for artisan commands.
     *
     * @return void
     */
    public function boot()
    {
        $this->commands([
            SendUpdatesCommand::class,
        ]);
    }
}
?>
