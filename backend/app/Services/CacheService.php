<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    const CACHE_TTL = 3600; // 1 hora
    const LONG_CACHE_TTL = 86400; // 24 horas

    public function getFeaturedGames()
    {
        return Cache::remember("featured_games", self::CACHE_TTL, function () {
            return Game::where("is_featured", true)
                      ->with("reviews")
                      ->orderBy("rating", "desc")
                      ->get();
        });
    }

    public function getTopRatedGames($limit = 10)
    {
        return Cache::remember("top_rated_games_{$limit}", self::CACHE_TTL, function () use ($limit) {
            return Game::orderBy("rating", "desc")
                      ->limit($limit)
                      ->get();
        });
    }

    public function getFreeGames()
    {
        return Cache::remember("free_games", self::CACHE_TTL, function () {
            return Game::where("is_free", true)
                      ->orderBy("rating", "desc")
                      ->get();
        });
    }

    public function getGamesOnSale()
    {
        return Cache::remember("games_on_sale", self::CACHE_TTL, function () {
            return Game::where("is_on_sale", true)
                      ->orderBy("rating", "desc")
                      ->get();
        });
    }

    public function getRecentReviews($limit = 6)
    {
        return Cache::remember("recent_reviews_{$limit}", 1800, function () use ($limit) { // 30 min
            return Review::with(["user", "game"])
                         ->orderBy("created_at", "desc")
                         ->limit($limit)
                         ->get();
        });
    }

    public function getSystemStats()
    {
        return Cache::remember("system_stats", self::CACHE_TTL, function () {
            return [
                "total_games" => Game::count(),
                "total_users" => User::count(),
                "total_reviews" => Review::count(),
                "average_rating" => Review::avg("rating") ?: 0
            ];
        });
    }

    public function getUserStats($userId)
    {
        return Cache::remember("user_stats_{$userId}", self::CACHE_TTL, function () use ($userId) {
            $user = User::find($userId);
            if (!$user) return null;

            return [
                "reviews_count" => $user->reviews()->count(),
                "average_rating" => $user->reviews()->avg("rating") ?: 0
            ];
        });
    }

    public function clearGameCache($gameId)
    {
        Cache::forget("game_stats_{$gameId}");
        Cache::forget("featured_games");
        Cache::forget("top_rated_games_10");
        Cache::forget("free_games");
        Cache::forget("games_on_sale");
        Cache::forget("system_stats");
    }

    public function clearUserCache($userId)
    {
        Cache::forget("user_stats_{$userId}");
        Cache::forget("recent_reviews_6");
        Cache::forget("system_stats");
    }

    public function clearAllCache()
    {
        Cache::flush();
    }
}