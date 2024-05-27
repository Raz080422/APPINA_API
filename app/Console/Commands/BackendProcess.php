<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Jobs\Confluence\ConfluenceJob;

class BackendProcess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:PostConfluenceProject';

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
        ConfluenceJob::dispatch();
    }
}
