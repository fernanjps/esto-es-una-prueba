<?php

namespace App\Jobs;

use App\Models\Game;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class UpdateGameStats implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $gameId;

    public function __construct($gameId)
    {
        $this->gameId = $gameId;
    }

    public function handle()
    {
        $game = Game::find($this->gameId);
        
        if ($game) {
            // Actualizar rating promedio
            $averageRating = $game->reviews()->avg("rating") ?: 0;
            $game->update(["rating" => round($averageRating, 2)]);
            
            // Limpiar caché relacionado
            Cache::forget("game_stats_{$this->gameId}");
            Cache::forget("featured_games");
            Cache::forget("top_rated_games");
            
            \Log::info("Game stats updated for game ID: " . $this->gameId);
        }
    }
}