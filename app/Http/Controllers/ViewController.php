<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;
use Illuminate\Support\Str;
use App\Utilities\AppHelper;
use Illuminate\Http\Request;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\Builder;
use Codexshaper\WooCommerce\Facades\Order;
use romanzipp\QueueMonitor\Models\Monitor;
use Illuminate\Console\Scheduling\Schedule;

class ViewController extends Controller
{
    /**
     * Show Informations.
     */
    public function show()
    {
        $activitiesLimit = request()->input('limit', 100);
        $showLogsLevelInfo = request()->input('show-info', false);

        $activities = Activity::orderBy('created_at', 'desc')
            ->limit($activitiesLimit)
            ->when(! $showLogsLevelInfo, function (Builder $query) {
                $query->whereNot('properties->level', 'info');
            })
            ->get();

        $lastJob = Monitor::query()->orderBy('started_at', 'desc')->first();
        $remainingJobs = DB::table('jobs')->count();

        // Manually load the console routes to populate the schedule for the web context
        require base_path('routes/console.php');

        $schedule = app(Schedule::class);
        $scheduled = collect($schedule->events());

        $scheduledTcpos = $scheduled->filter(fn ($item) => Str::contains($item->command, 'import:tcpos'))->first();
        $scheduledTcpos = $scheduledTcpos ? $scheduledTcpos->nextRunDate() : null;

        $scheduledWoo = $scheduled->filter(fn ($item) => Str::contains($item->command, 'import:woo'))->first();
        $scheduledWoo = $scheduledWoo ? $scheduledWoo->nextRunDate() : null;

        $scheduledSync = $scheduled->filter(fn ($item) => Str::contains($item->command, 'sync:tcpos_woo'))->first();
        $scheduledSync = $scheduledSync ? $scheduledSync->nextRunDate() : null;

        // Fetch TCPOS update once to avoid multiple HTTP calls
        $lastTcposUpdateDate = AppHelper::getLastTcposUpdate();
        $lastTcposUpdate = $lastTcposUpdateDate->locale('fr_ch')->isoFormat('L LT');
        
        // We can pass the object to AppHelper if we modify it, but let's just use it here
        $tcposTimestamp = $lastTcposUpdateDate->toDateTimeLocalString();
        $localTimestamp = \anlutro\LaravelSettings\Facade::get('lastTcposUpdate', null);
        $needImportFromTcpos = $tcposTimestamp > $localTimestamp;

        $lastJobDatetime = $lastJob?->started_at?->locale('fr_ch')->timezone('Europe/Zurich') ?? false;

        if ($lastJob && $lastJob->started_at && $lastJob->started_at->diffInMinutes(now()) <= 1) {
            $jobsWorking = true;
        } else {
            $jobsWorking = false;
        }

        $products_where_minimal_quantity_under_six = Product::whereHas('stockRelation', function (Builder $query) {
            $query->where('value', '<', 6);
        })->count();

        $products_count = Product::count();

        // Group category counts in one query
        $categoryCounts = Product::select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->pluck('total', 'category');

        return view('welcome', [
            'activities' => $activities,
            'activitiesLimit' => $activitiesLimit,
            'showLogsLevelInfo' => $showLogsLevelInfo,
            'lastJobDatetime' => $lastJobDatetime,
            'remainingJobs' => $remainingJobs,
            'lastTcposUpdate' => $lastTcposUpdate,
            'lastTcposUpdateDate' => $lastTcposUpdateDate, // Pass the date object too
            'needImportFromTcpos' => $needImportFromTcpos,
            'jobsWorking' => $jobsWorking,
            'scheduledTcpos' => $scheduledTcpos,
            'scheduledWoo' => $scheduledWoo,
            'scheduledSync' => $scheduledSync,
            'products_count' => $products_count,
            'products_where_minimal_quantity_under_six' => $products_where_minimal_quantity_under_six,
            'count_wine' => $categoryCounts->get('wine', 0),
            'spirit' => $categoryCounts->get('spirit', 0),
            'cider' => $categoryCounts->get('cider', 0),
            'wineSet' => $categoryCounts->get('wineSet', 0),
            'mineralDrink' => $categoryCounts->get('mineralDrink', 0),
            'regionalProduct' => $categoryCounts->get('regionalProduct', 0),
            'beer' => $categoryCounts->get('beer', 0),
            'book' => $categoryCounts->get('book', 0),
            'selection' => $categoryCounts->get('selection', 0),
            'none' => $categoryCounts->get('none', 0),
        ]);
    }

    /**
     * Show Tables.
     */
    public function tables(): View
    {
        return view('tables', [
            'products' => \App\Models\Product::all(),
            'orders' => Order::all(['per_page' => 5, 'page' => 1]),
        ]);
    }

    /**
     * Force product resource to update state.
     */
    public function forceUpdateProduct(Request $request, $id): RedirectResponse
    {
        $product = Product::where('id', $id)->first();
        $product->sync_action = 'update';
        $product->save();

        return redirect()->back();
    }
}
