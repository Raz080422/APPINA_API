<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\Confluence\UpdateDataConfluence;

class UpdatePageDataConfluence extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:Mappingdata';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mapping Links and data project';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        UpdateDataConfluence::dispatch();
    }
}
