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

class SyncAttributeUpdate implements ShouldQueue
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
        // https://codexshaper.github.io/docs/laravel-woocommerce/#update-attribute
        Attribute::update($this->id, $this->data);

        return 'Sync: Attribute updated in Woocommerce : '.$this->id;
    }
}
