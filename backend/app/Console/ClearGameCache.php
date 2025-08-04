<?php

namespace App\Console\Commands;

use App\Services\CacheService;
use Illuminate\Console\Command;

class ClearGameCache extends Command
{
    protected $signature = "cache:clear-games {--all : Clear all game-related cache}";
    protected $description = "Clear game-related cache";

    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        parent::__construct();
        $this->cacheService = $cacheService;
    }

    public function handle()
    {
        if ($this->option("all")) {
            $this->cacheService->clearAllCache();
            $this->info("All cache cleared successfully!");
        } else {
            // Limpiar solo caché de juegos
            $this->cacheService->clearGameCache("*");
            $this->info("Game cache cleared successfully!");
        }

        return 0;
    }
}