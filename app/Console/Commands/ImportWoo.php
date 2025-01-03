<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\V1\ImportController;
use Illuminate\Console\Command;

class ImportWoo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:woo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'import Woo database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->line('Importation lauched');
        $importController = new ImportController;
        $importController->importWooAll();
        $this->info('Importation done. There are maybe queued jobs launched.');

        return self::SUCCESS;
    }
}
