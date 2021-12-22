<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('import:woo')->timezone('Europe/Zurich')->between('6:00', '20:00')->hourlyAt(1);
        $schedule->command('import:tcpos')->timezone('Europe/Zurich')->between('6:00', '20:00')->hourlyAt(2);
        $schedule->command('sync:tcpos_woo')->timezone('Europe/Zurich')->between('6:00', '20:00')->hourlyAt(25);
        $schedule->command('sync:tcpos_woo_order')->timezone('Europe/Zurich')->between('6:00', '20:00')->everyThirtyMinutes();
        $schedule->command('import:tcpos_articles')->timezone('Europe/Zurich')->daily();


        $schedule->command('activitylog:clean')->daily();

        $schedule->call(function () {
            DB::table('queue_monitor')->where('started_at', '<=', now()->subDay())->delete();
        })->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
