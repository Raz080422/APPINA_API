<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\Confluence\UpdatePageConfluenceJob;
class UpdatePageConfluence extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:UpdateConfluence';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        UpdatePageConfluenceJob::dispatch();
    }
}
