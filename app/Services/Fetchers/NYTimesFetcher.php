<?php

namespace App\Services\Fetchers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NYTimesFetcher
{
    /**
     * Fetch articles from The New York Times API.
     */
    public function fetch()
    {
        try {
            $response = Http::get(config('services.news.nytimes_url'), [
                'api-key' => config('services.news.nytimes_key'),
            ]);

            if ($response->failed()) {
                Log::error("NYTimes API request failed: " . $response->body());
                return [];
            }
            
            return data_get($response->json(), 'results', []);
        } catch (\Exception $e) {
            Log::error("NYTimes API Error: " . $e->getMessage());
            return [];
        }
    }
}
