<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\User;

class ArticleController extends Controller
{
    public function getArticles(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Retrieve user along with their preference relationships
        $user = User::whereHas('tokens', function ($query) use ($token) {
            $query->where('token', hash('sha256', $token));
        })->with(['preferredCategories', 'preferredSources', 'preferredAuthors'])->first();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Check if the user has any preferences
        $hasPreferences = $user->preferredCategories->isNotEmpty()
            || $user->preferredSources->isNotEmpty()
            || $user->preferredAuthors->isNotEmpty();

        if (!$hasPreferences) {
            // No preferences – return all articles
            $articles = Article::with(['category:id,name', 'source:id,name', 'author:id,name'])->get();
            return response()->json($articles);
        }

        // Build the query to filter articles based on any matching preference
        $query = Article::with(['category:id,name', 'source:id,name', 'author:id,name']);
    
        if ($user->preferredCategories->isNotEmpty()) {
            $categoryIds = $user->preferredCategories->pluck('id');
            $query->orWhereIn('category_id', $categoryIds);
        }

        if ($user->preferredSources->isNotEmpty()) {
            $sourceIds = $user->preferredSources->pluck('id');
            $query->orWhereIn('source_id', $sourceIds);
        }

        if ($user->preferredAuthors->isNotEmpty()) {
            $authorIds = $user->preferredAuthors->pluck('id');
            $query->orWhereIn('author_id', $authorIds);
        }

        $articles = $query->get();
        return response()->json($articles);
    }
}
