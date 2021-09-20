<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use romanzipp\QueueMonitor\Traits\IsMonitored;
use Codexshaper\WooCommerce\Facades\Term;
use Codexshaper\WooCommerce\Facades\Attribute;
use App\Models\Attribute as TcposAttribute;

class SyncAttributeTermUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use IsMonitored;

    public $id;
    public $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id, $data)
    {
        $this->id = $id;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // https://codexshaper.github.io/docs/laravel-woocommerce/#update-term
        Term::update(config('cdv.wc_attribute_ids.cellar'), $this->id, $this->data);

        activity()->withProperties(['group' => 'sync', 'level' => 'info', 'resource' => 'terms'])->log('Term updated in Woocommerce : '.$this->id);
    }
}
