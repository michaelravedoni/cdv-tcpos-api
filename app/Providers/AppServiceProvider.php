<?php

namespace App\Providers;

use Opcodes\LogViewer\Facades\LogViewer;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();

        Queue::failing(function (JobFailed $event): void {
            activity()
                ->withProperties(['group' => 'jobs', 'level' => 'error', 'resource' => 'job'])
                ->log($event->exception->getMessage());
        });

        LogViewer::auth(fn ($request) => true);
    }
}
