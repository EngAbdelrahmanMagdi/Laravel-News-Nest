<?php

namespace App\Services;

use App\Services\Fetchers\NewsApiFetcher;
use App\Services\Fetchers\GuardianFetcher;
use App\Services\Fetchers\NYTimesFetcher;
use App\Services\NewsFormatter;
use Illuminate\Support\Facades\Log;

class NewsService
{
    protected $newsApiFetcher;
    protected $guardianFetcher;
    protected $nyTimesFetcher;

    public function __construct(NewsApiFetcher $newsApiFetcher, GuardianFetcher $guardianFetcher, NYTimesFetcher $nyTimesFetcher)
    {
        $this->newsApiFetcher = $newsApiFetcher;
        $this->guardianFetcher = $guardianFetcher;
        $this->nyTimesFetcher = $nyTimesFetcher;
    }

    /**
     * Fetch news from all sources.
     */
    public function fetchAllNews()
    {
        Log::info("Fetching news from all sources...");
        
        $this->fetchNews('NewsAPI', $this->newsApiFetcher);
        $this->fetchNews('The Guardian', $this->guardianFetcher);
        $this->fetchNews('New York Times', $this->nyTimesFetcher);
        
        Log::info("News fetching process completed.");
    }

    /**
     * Fetch news from an API fetcher.
     */
    private function fetchNews(string $source, $fetcher)
    {
        Log::info("Fetching news from $source...");
        
        try {
            $articles = $fetcher->fetch();
            if (empty($articles)) {
                Log::warning("$source returned no articles.");
                return;
            }

            foreach ($articles as $article) {
                NewsFormatter::storeArticle($article, $source);
            }

            Log::info("Fetching news from $source completed.");

        } catch (\Exception $e) {
            Log::error("Error fetching news from $source: " . $e->getMessage());
        }
    }
}
