<?php

namespace App\Services\Fetchers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsApiFetcher
{
    /**
     * Fetch articles from News API.
     */
    public function fetch()
    {
        try {
            $response = Http::get(config('services.news.newsapi_url'), [
                'apiKey' => config('services.news.newsapi_key'),
                'country' => 'us'
            ]);

            if ($response->failed()) {
                Log::error("NewsAPI request failed: " . $response->body());
                return [];
            }

            return data_get($response->json(), 'articles', []);
        } catch (\Exception $e) {
            Log::error("NewsAPI Error: " . $e->getMessage());
            return [];
        }
    }
}
