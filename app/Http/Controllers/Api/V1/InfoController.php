<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use romanzipp\QueueMonitor\Models\Monitor;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Str;
use anlutro\LaravelSettings\Facade as Setting;
use AppHelper;

class InfoController extends Controller
{
    /**
     * Show Informations.
     */
    public function show()
    {
        $activitiesLimit = request()->input('limit', 500);

        $activities = Activity::orderBy('created_at', 'desc')->limit($activitiesLimit)->get();
        $lastJob = Monitor::query()->orderBy('started_at', 'desc')->first();
        $remainingJobs = DB::table('jobs')->count();

        new \App\Console\Kernel(app(), new Dispatcher());
        $schedule = app(Schedule::class);
        $scheduled = collect($schedule->events());
        $scheduledTcpos = $scheduled->filter(function ($item) {
            return Str::contains($item->command, 'import:tcpos');
        })->first()->nextRunDate();
        $scheduledWoo = $scheduled->filter(function ($item) {
            return Str::contains($item->command, 'import:woo');
        })->first()->nextRunDate();
        $scheduledSync = $scheduled->filter(function ($item) {
            return Str::contains($item->command, 'sync:tcpos_woo');
        })->first()->nextRunDate();

        $lastTcposUpdate = AppHelper::getLastTcposUpdate()->locale('fr_ch')->isoFormat('L LT');
        $needImportFromTcpos = AppHelper::needImportFromTcpos();
        $lastJobDatetime = $lastJob ? $lastJob->started_at->locale('fr_ch')->timezone('Europe/Zurich') : now();

        if (isset($lastJob) && $lastJob->started_at->diffInMinutes(now()) <= 1) {
            $jobsWorking = true;
        } else {
            $jobsWorking = false;
        }

        $products_where_minimal_quantity_under_six = Product::whereHas('stockRelation', function (Builder $query) {
            $query->where('value', '<', 6);
        })->count();

        $products_count = Product::all()->count();

        return view('welcome', [
            'activities' => $activities,
            'activitiesLimit' => $activitiesLimit,
            'lastJobDatetime' => $lastJobDatetime,
            'remainingJobs' => $remainingJobs,
            'lastTcposUpdate' => $lastTcposUpdate,
            'needImportFromTcpos' => $needImportFromTcpos,
            'jobsWorking' => $jobsWorking,
            'scheduledTcpos' => $scheduledTcpos,
            'scheduledWoo' => $scheduledWoo,
            'scheduledSync' => $scheduledSync,
            'products_count' => $products_count,
            'products_where_minimal_quantity_under_six' => $products_where_minimal_quantity_under_six,
            'count_wine' => Product::where('category', 'wine')->count(),
            'spirit' => Product::where('category', 'spirit')->count(),
            'cider' => Product::where('category', 'cider')->count(),
            'wineSet' => Product::where('category', 'wineSet')->count(),
            'mineralDrink' => Product::where('category', 'mineralDrink')->count(),
            'beer' => Product::where('category', 'beer')->count(),
            'book' => Product::where('category', 'book')->count(),
            'selection' => Product::where('category', 'selection')->count(),
            'none' => Product::where('category', 'none')->count(),
        ]);
    }
    /**
     * Show API Informations.
     */
    public function api()
    {
        
        return response()->json([
            'message' => 'Informations',
            'products_count' => Product::all()->count(),
            'products_where_minimal_quantity_<_6' => Product::whereHas('stockRelation', function (Builder $query) {
                $query->where('value', '<', 6);
            })->count(),
            'products_where_minimal_quantity_>=_6' => Product::whereHas('stockRelation', function (Builder $query) {
                $query->where('value', '>=', 6);
            })->count(),
            'count_by_category' => [
                'count_wine' => Product::where('category', 'wine')->count(),
                'spirit' => Product::where('category', 'spirit')->count(),
                'cider' => Product::where('category', 'cider')->count(),
                'wineSet' => Product::where('category', 'wineSet')->count(),
                'mineralDrink' => Product::where('category', 'mineralDrink')->count(),
                'beer' => Product::where('category', 'beer')->count(),
                'book' => Product::where('category', 'book')->count(),
                'selection' => Product::where('category', 'selection')->count(),
                'none' => Product::where('category', 'none')->count(),
            ],
        ]);
    }
}
