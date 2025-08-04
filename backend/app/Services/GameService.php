<?php

namespace App\Services;

use App\Models\Game;
use App\Repositories\GameRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GameService
{
    protected $gameRepository;

    public function __construct(GameRepository $gameRepository)
    {
        $this->gameRepository = $gameRepository;
    }

    public function getAllGames($filters = [])
    {
        $cacheKey = "games_" . md5(serialize($filters));
        
        return Cache::remember($cacheKey, 3600, function () use ($filters) {
            return $this->gameRepository->getFiltered($filters);
        });
    }

    public function getFeaturedGames()
    {
        return Cache::remember("featured_games", 3600, function () {
            return $this->gameRepository->getFeatured();
        });
    }

    public function getFreeGames()
    {
        return Cache::remember("free_games", 3600, function () {
            return $this->gameRepository->getFree();
        });
    }

    public function getGamesOnSale()
    {
        return Cache::remember("games_on_sale", 3600, function () {
            return $this->gameRepository->getOnSale();
        });
    }

    public function createGame(array $data)
    {
        DB::beginTransaction();
        try {
            // Validar y procesar datos
            if ($data["is_free"]) {
                $data["price"] = 0;
                $data["discount_price"] = null;
                $data["is_on_sale"] = false;
            }

            $game = $this->gameRepository->create($data);
            
            // Limpiar caché relacionado
            $this->clearGameCache();
            
            DB::commit();
            return $game;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function updateGame($id, array $data)
    {
        DB::beginTransaction();
        try {
            if ($data["is_free"]) {
                $data["price"] = 0;
                $data["discount_price"] = null;
                $data["is_on_sale"] = false;
            }

            $game = $this->gameRepository->update($id, $data);
            
            // Limpiar caché relacionado
            $this->clearGameCache();
            
            DB::commit();
            return $game;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function deleteGame($id)
    {
        DB::beginTransaction();
        try {
            $this->gameRepository->delete($id);
            $this->clearGameCache();
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    private function clearGameCache()
    {
        Cache::forget("featured_games");
        Cache::forget("free_games");
        Cache::forget("games_on_sale");
        Cache::tags(["games"])->flush();
    }
}