<?php

namespace App\Services\Fetchers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GuardianFetcher
{
    /**
     * Fetch articles from The Guardian API.
     */
    public function fetch()
    {
        try {
            $response = Http::get(config('services.news.guardian_url'), [
                'api-key' => config('services.news.guardian_key')
            ]);

            if ($response->failed()) {
                Log::error("Guardian API request failed: " . $response->body());
                return [];
            }

            return data_get($response->json(), 'response.results', []);
        } catch (\Exception $e) {
            Log::error("Guardian API Error: " . $e->getMessage());
            return [];
        }
    }
}
