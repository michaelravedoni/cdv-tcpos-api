<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Api\V1\TcposController;

class ImportTcpos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:tcpos_articles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import tcpos articles in database';

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
     *
     * @return int
     */
    public function handle()
    {
        $this->line('Importation lauched');
        $importController = new TcposController;
        $importController->importArticles();
        $this->info('Importation done. There are maybe queued jobs launched.');
    }
}
