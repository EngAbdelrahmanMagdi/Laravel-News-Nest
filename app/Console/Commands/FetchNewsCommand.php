<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NewsService;

class FetchNewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch news articles from multiple APIs and store them in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $newsService = app(NewsService::class);
        $newsService->fetchAllNews();

        $this->info('News articles fetched successfully!');
    }
}
