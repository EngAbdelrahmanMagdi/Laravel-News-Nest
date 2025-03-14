<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Author;
use App\Models\Source;
use App\Models\Category;
use Illuminate\Support\Facades\Log;

class NewsFormatter
{
    /**
     * Process and store an article in the database.
     */
    public static function storeArticle(array $article, string $apiSource)
    {
        // Safely get URL
        $articleUrl = $article['url'] ?? $article['webUrl'] ?? null;

        if (!$articleUrl) {
            Log::warning("Skipped Article (Missing URL)", ['article' => $article]);
            return;
        }

        if (Article::where('url', $articleUrl)->exists()) {
            Log::info("Skipped (Already Exists): " . $articleUrl);
            return;
        }

        // Safely get Publisher
        $publisher = data_get($article, 'source.name', $apiSource);

        // Cache authors, categories, sources.
        static $authorCache = [], $categoryCache = [], $sourceCache = [];

        // Get or Create Author
        $authorName = trim(str_replace('By ', '', $article['author'] ?? $article['byline'] ?? '')) ?: 'Unknown';
        if (!isset($authorCache[$authorName])) {
            $authorCache[$authorName] = Author::firstOrCreate(['name' => $authorName]);
        }

        // Get or Create Category
        $categoryName = $article['sectionName'] ?? $article['section'] ?? 'General';
        if (!isset($categoryCache[$categoryName])) {
            $categoryCache[$categoryName] = Category::firstOrCreate(['name' => $categoryName]);
        }

        if (!isset($sourceCache[$publisher])) {
            $sourceCache[$publisher] = Source::firstOrCreate(['name' => $publisher]);
        }

        $imageUrl = data_get($article, 'urlToImage') ?: data_get($article, 'multimedia.0.url');

        // Store the article
        try {
            Article::create([
                'title' => $article['title'] ?? $article['webTitle'] ?? 'No Title',
                'summary' => $article['description'] ?? $article['abstract'] ?? '',
                'api_source' => $apiSource,
                'url' => $articleUrl,
                'author_id' => $authorCache[$authorName]->id ?? null,
                'category_id' => $categoryCache[$categoryName]->id ?? null,
                'source_id' => $sourceCache[$publisher]->id ?? null,
                'published_at' => $article['publishedAt'] ?? $article['published_date'] ?? now(),
                'image_url' => $imageUrl,
            ]);

            Log::info("Inserted New Article: " . $articleUrl);
        } catch (\Exception $e) {
            Log::error("Failed to insert article", ['error' => $e->getMessage(), 'article' => $article]);
        }
    }
}
