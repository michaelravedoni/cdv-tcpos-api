<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('import:woo')->timezone('Europe/Zurich')->between('6:00', '20:00')->hourlyAt(1);
Schedule::command('import:tcpos')->timezone('Europe/Zurich')->between('6:00', '20:00')->hourlyAt(2);
Schedule::command('sync:tcpos_woo')->timezone('Europe/Zurich')->between('6:00', '20:00')->hourlyAt(25);
Schedule::command('sync:tcpos_woo_order')->timezone('Europe/Zurich')->between('6:00', '20:00')->everyThirtyMinutes();
Schedule::command('import:tcpos_articles')->timezone('Europe/Zurich')->daily();
Schedule::command('check')->timezone('Europe/Zurich')->between('6:00', '20:00')->everyThirtyMinutes();
Schedule::command(\Spatie\Health\Commands\RunHealthChecksCommand::class)->everyThirtyMinutes();
Schedule::command('check:woo')->timezone('Europe/Zurich')->between('6:00', '20:00')->hourly();

Schedule::command('queue:work --stop-when-empty')->everyMinute()->withoutOverlapping(30);
Schedule::command('queue:restart')->hourly();
Schedule::command('queue:db-monitor')->everyThirtyMinutes();

Schedule::command('activitylog:clean')->daily();

Schedule::call(function () {
    DB::table('queue_monitor')->where('started_at', '<=', now()->subDays(4))->delete();
})->daily();
