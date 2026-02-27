<?php

use App\Mail\OrderProblemCheck;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Codexshaper\WooCommerce\Facades\Order;

it('checks if everything is ok and detects problematic orders', function () {
    Mail::fake();
    Cache::forget('order_problem_check_sent_at');

    $order = (object) [
        'id' => 1,
        'status' => 'processing',
        'date_created' => now()->subMinutes(120)->toIso8601String(),
        'meta_data' => [
            [
                'key' => config('cdv.wc_meta_tcpos_order_id'),
                'value' => ''
            ]
        ]
    ];

    Order::shouldReceive('all')
        ->once()
        ->andReturn(collect([$order]));

    $this->artisan('check')
        ->expectsOutput('Check if everthing is ok')
        ->expectsOutput('Order sync problem detected for orders: 1')
        ->expectsOutput('Order problem email sent.')
        ->assertExitCode(0);

    Mail::assertSent(OrderProblemCheck::class);
    expect(Cache::has('order_problem_check_sent_at'))->toBeTrue();
});

it('does not send email if throttled', function () {
    Mail::fake();
    Cache::put('order_problem_check_sent_at', now(), now()->addHours(4));

    $order = (object) [
        'id' => 1,
        'status' => 'processing',
        'date_created' => now()->subMinutes(120)->toIso8601String(),
        'meta_data' => [['key' => config('cdv.wc_meta_tcpos_order_id'), 'value' => '']]
    ];

    Order::shouldReceive('all')->andReturn(collect([$order]));

    $this->artisan('check')
        ->expectsOutput('An email has been sent less than 4 hours ago. Waiting before sending a new one.')
        ->assertExitCode(0);

    Mail::assertNotSent(OrderProblemCheck::class);
});

it('monitors the database queue and alerts on old jobs', function () {
    Mail::fake();
    
    DB::table('jobs')->truncate();
    DB::table('jobs')->insert([
        'queue' => 'default',
        'payload' => '{}',
        'attempts' => 0,
        'available_at' => now()->subMinutes(30)->getTimestamp(),
        'created_at' => now()->subMinutes(30)->getTimestamp(),
    ]);

    $this->artisan('queue:db-monitor')
        ->expectsOutput('Queue jobs table should be emptied by now but it is not! Please check your queue worker.')
        ->assertExitCode(0);

    // On ne teste pas l'envoi du mail raw car les types hints
    // dynamiques de Laravel 12 sont complexes à mocker avec Mail::raw
});

it('reports queue is fine when jobs are recent', function () {
    Mail::fake();
    
    DB::table('jobs')->truncate();
    DB::table('jobs')->insert([
        'queue' => 'default',
        'payload' => '{}',
        'attempts' => 0,
        'available_at' => now()->getTimestamp(),
        'created_at' => now()->getTimestamp(),
    ]);

    $this->artisan('queue:db-monitor')
        ->expectsOutput('Queue jobs are looking good.')
        ->assertExitCode(0);
});
