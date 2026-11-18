<?php

namespace App\Http\Controllers;

use anlutro\LaravelSettings\Facade as Setting;
use App\Models\Product;
use App\Models\Woo;
use Carbon\Carbon;
use Codexshaper\WooCommerce\Facades\Order;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;
use romanzipp\QueueMonitor\Models\Monitor;
use Spatie\Activitylog\Models\Activity;

class ViewController extends Controller
{
    /**
     * Show Dashboard.
     */
    public function show(Request $request): View
    {
        if ($request->boolean('refresh')) {
            Cache::forget('dashboard_tcpos_last_update');
            Cache::forget('dashboard_counts');
        }

        $activitiesLimit = (int) $request->input('limit', 100);
        $activitiesLimit = in_array($activitiesLimit, [50, 100, 250], true) ? $activitiesLimit : 100;
        $resourceFilter = $request->input('resource', 'all');
        $levelFilter = $request->input('level', $request->boolean('show-info') ? 'info' : 'default');

        // Read recent rows by primary key (id DESC) to avoid full-table JSON scans on large activity_log tables
        $recentActivities = Activity::query()
            ->orderByDesc('id')
            ->limit(1000)
            ->get();

        $activities = $recentActivities
            ->when($resourceFilter !== 'all', function ($collection) use ($resourceFilter) {
                return $collection->filter(fn ($item) => data_get($item->properties, 'resource') === $resourceFilter);
            })
            ->when($levelFilter === 'default', function ($collection) {
                return $collection->filter(fn ($item) => data_get($item->properties, 'level') !== 'info');
            })
            ->when($levelFilter === 'errors', function ($collection) {
                return $collection->filter(fn ($item) => in_array(data_get($item->properties, 'level'), ['error', 'warning'], true));
            })
            ->take($activitiesLimit)
            ->values();

        $lastJob = Monitor::query()->orderByDesc('id')->first();
        $remainingJobs = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();

        require base_path('routes/console.php');

        $schedule = app(Schedule::class);
        $scheduled = collect($schedule->events());

        $nextRunFor = function (string $needle) use ($scheduled) {
            $event = $scheduled->first(fn ($item) => Str::contains((string) $item->command, $needle));

            return $event ? $event->nextRunDate()->setTimezone('Europe/Zurich') : null;
        };

        $scheduledTcpos = $nextRunFor('import:tcpos');
        $scheduledWoo = $nextRunFor('import:woo');
        $scheduledSync = $nextRunFor('sync:tcpos_woo');
        $scheduledOrders = $nextRunFor('sync:tcpos_woo_order');

        $tcposStatus = Cache::remember('dashboard_tcpos_last_update', 10, function () {
            try {
                $response = Http::timeout(3)->get(config('cdv.tcpos.api_wond_url').'/getLastRefreshTimestamp');
                if ($response->failed()) {
                    return ['reachable' => false, 'timestamp' => null];
                }
                $ts = data_get($response->json(), 'getLastRefreshTimestamp.timestamp');

                return ['reachable' => (bool) $ts, 'timestamp' => $ts];
            } catch (\Throwable $e) {
                return ['reachable' => false, 'timestamp' => null];
            }
        });

        $tcposReachable = (bool) data_get($tcposStatus, 'reachable', false);
        $lastTcposUpdateDate = data_get($tcposStatus, 'timestamp') ? Carbon::parse($tcposStatus['timestamp']) : null;
        $lastTcposUpdate = $lastTcposUpdateDate ? $lastTcposUpdateDate->locale('fr_ch')->isoFormat('L LT') : 'Injoignable';

        $localTimestamp = Setting::get('lastTcposUpdate', null);
        $needImportFromTcpos = $lastTcposUpdateDate
            ? ($lastTcposUpdateDate->toDateTimeLocalString() > $localTimestamp)
            : false;

        $lastJobDatetime = $lastJob?->started_at?->locale('fr_ch')->timezone('Europe/Zurich') ?? false;
        $jobsWorking = $lastJob && $lastJob->started_at && $lastJob->started_at->diffInMinutes(now()) <= 1;

        $counts = Cache::remember('dashboard_counts', 10, function () {
            return [
                'products_under_six' => Product::whereHas('stockRelation', function (Builder $query) {
                    $query->where('value', '<', 6);
                })->count(),
                'products_count' => Product::count(),
                'products_pending_update' => Product::where('sync_action', 'update')->count(),
                'customers_with_card' => Woo::where('resource', 'customer')->count(),
                'woo_products_count' => Woo::where('resource', 'product')->count(),
                'category_counts' => Product::select('category', DB::raw('count(*) as total'))
                    ->groupBy('category')
                    ->pluck('total', 'category'),
            ];
        });

        $products_where_minimal_quantity_under_six = $counts['products_under_six'];
        $products_count = $counts['products_count'];
        $productsPendingUpdate = $counts['products_pending_update'];
        $customersWithCardCount = $counts['customers_with_card'];
        $wooProductsCount = $counts['woo_products_count'];
        $categoryCounts = $counts['category_counts'];

        return view('welcome', [
            'activities' => $activities,
            'activitiesLimit' => $activitiesLimit,
            'resourceFilter' => $resourceFilter,
            'levelFilter' => $levelFilter,
            'lastJobDatetime' => $lastJobDatetime,
            'remainingJobs' => $remainingJobs,
            'failedJobs' => $failedJobs,
            'tcposReachable' => $tcposReachable,
            'lastTcposUpdate' => $lastTcposUpdate,
            'lastTcposUpdateDate' => $lastTcposUpdateDate,
            'needImportFromTcpos' => $needImportFromTcpos,
            'jobsWorking' => $jobsWorking,
            'scheduledTcpos' => $scheduledTcpos,
            'scheduledWoo' => $scheduledWoo,
            'scheduledSync' => $scheduledSync,
            'scheduledOrders' => $scheduledOrders,
            'products_count' => $products_count,
            'productsPendingUpdate' => $productsPendingUpdate,
            'wooProductsCount' => $wooProductsCount,
            'customersWithCardCount' => $customersWithCardCount,
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
     * Show Tables with search & pagination.
     */
    public function tables(Request $request): View
    {
        $tab = $request->input('tab', 'orders');
        if (! in_array($tab, ['orders', 'products', 'customers'], true)) {
            $tab = 'orders';
        }

        $orderQuery = trim((string) $request->input('order_q', ''));
        $orderPage = max(1, (int) $request->input('order_page', 1));
        $orderPerPage = 20;

        $productQuery = trim((string) $request->input('q', ''));
        $productCategory = $request->input('category', 'all');
        $productSync = $request->input('sync_action', 'all');

        $customerQuery = trim((string) $request->input('customer_q', ''));

        $orders = collect();
        $ordersError = null;
        if ($tab === 'orders') {
            $cacheKey = 'tables_orders_'.md5($orderQuery.'_'.$orderPage);
            if ($request->boolean('refresh')) {
                Cache::forget($cacheKey);
            }

            $orderResult = Cache::remember($cacheKey, 10, function () use ($orderQuery, $orderPage, $orderPerPage) {
                try {
                    if ($orderQuery !== '' && ctype_digit($orderQuery)) {
                        try {
                            $single = Order::find((int) $orderQuery);
                            if ($single && isset($single->id)) {
                                return ['items' => collect([$single]), 'error' => null];
                            }
                        } catch (\Throwable $e) {
                            // Fall through to standard search
                        }
                    }

                    $params = ['per_page' => $orderPerPage, 'page' => $orderPage];
                    if ($orderQuery !== '') {
                        $params['search'] = $orderQuery;
                    }

                    return ['items' => collect(Order::all($params)), 'error' => null];
                } catch (\Throwable $e) {
                    return ['items' => collect(), 'error' => $e->getMessage()];
                }
            });

            $orders = $orderResult['items'];
            $ordersError = $orderResult['error'];
        }

        $products = null;
        if ($tab === 'products') {
            $products = Product::query()
                ->when($productQuery !== '', function (Builder $query) use ($productQuery) {
                    $query->where(function (Builder $q) use ($productQuery) {
                        $q->where('name', 'like', '%'.$productQuery.'%')
                            ->orWhere('_tcposCode', 'like', '%'.$productQuery.'%')
                            ->orWhere('_tcposId', 'like', '%'.$productQuery.'%');
                    });
                })
                ->when($productCategory !== 'all', fn (Builder $q) => $q->where('category', $productCategory))
                ->when($productSync !== 'all', fn (Builder $q) => $q->where('sync_action', $productSync))
                ->orderByDesc('updated_at')
                ->paginate(50)
                ->withQueryString();
        }

        $customers = null;
        if ($tab === 'customers') {
            $customers = Woo::where('resource', 'customer')
                ->when($customerQuery !== '', function (Builder $query) use ($customerQuery) {
                    $query->where(function (Builder $q) use ($customerQuery) {
                        $q->where('_wooId', 'like', '%'.$customerQuery.'%')
                            ->orWhere('data', 'like', '%'.$customerQuery.'%');
                    });
                })
                ->orderByDesc('updated_at')
                ->paginate(50)
                ->withQueryString();
        }

        return view('tables', [
            'tab' => $tab,
            'orders' => $orders,
            'ordersError' => $ordersError,
            'orderQuery' => $orderQuery,
            'orderPage' => $orderPage,
            'orderPerPage' => $orderPerPage,
            'products' => $products,
            'productQuery' => $productQuery,
            'productCategory' => $productCategory,
            'productSync' => $productSync,
            'customers' => $customers,
            'customerQuery' => $customerQuery,
            'productsCount' => Product::count(),
            'customersCount' => Woo::where('resource', 'customer')->count(),
            'categories' => array_keys(config('cdv.categories', [])),
            'wooStoreUrl' => rtrim((string) config('woocommerce.store_url', 'https://shop.chateaudevilla.ch'), '/'),
        ]);
    }

    /**
     * Force product resource to update state.
     */
    public function forceUpdateProduct(Request $request, $id): RedirectResponse
    {
        $product = Product::where('id', $id)->first();
        if ($product) {
            $product->sync_action = 'update';
            $product->save();
        }

        return redirect()->back();
    }
}
