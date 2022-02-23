<?php

namespace App\Http\Controllers;

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
use Codexshaper\WooCommerce\Facades\Order;

class ViewController extends Controller
{
    /**
     * Show Informations.
     */
    public function show()
    {
        $activitiesLimit = request()->input('limit', 500);
        $showLogsLevelInfo = request()->input('show-info', false);

        $activities = Activity::orderBy('created_at', 'desc')->get();
        if (!$showLogsLevelInfo) {
            $filteredActivities = $activities->filter(function ($value, $key) {
                return data_get($value->properties, 'level') != 'info';
            });
            $activities = $filteredActivities->splice(0, $activitiesLimit)->all();
        } else {
            $activities = $activities->splice(0, $activitiesLimit);
        }

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
        $lastJobDatetime = $lastJob ? $lastJob->started_at->locale('fr_ch')->timezone('Europe/Zurich') : false;

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
            'showLogsLevelInfo' => $showLogsLevelInfo,
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
            'regionalProduct' => Product::where('category', 'regionalProduct')->count(),
            'beer' => Product::where('category', 'beer')->count(),
            'book' => Product::where('category', 'book')->count(),
            'selection' => Product::where('category', 'selection')->count(),
            'none' => Product::where('category', 'none')->count(),
        ]);
    }

    /**
     * Show Tables.
     */
    public function tables()
    {
        return view('tables', [
            'products' => \App\Models\Product::all(),
            'orders' => Order::all(['per_page' => 5, 'page' => 1]),
        ]);
    }

    /**
     * Force product resource to update state.
     */
    public function forceUpdateProduct(Request $request, $id)
    {
        $product = Product::where('id', $id)->first();
        $product->sync_action = 'update';
        $product->save();

        return redirect()->back();
    }
}
