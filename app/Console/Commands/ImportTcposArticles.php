<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\V1\TcposController;
use Illuminate\Console\Command;

class ImportTcposArticles extends Command
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
    public function handle(): int
    {
        $this->line('Importation lauched');
        $importController = new TcposController;
        $importController->importArticles();
        $this->info('Importation done. There are maybe queued jobs launched.');
    }
}
