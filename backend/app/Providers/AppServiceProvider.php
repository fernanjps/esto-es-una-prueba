<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\GameService;
use App\Services\ReviewService;
use App\Services\UserService;
use App\Repositories\GameRepository;
use App\Repositories\ReviewRepository;
use App\Repositories\UserRepository;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Registrar repositorios
        $this->app->bind(GameRepository::class, GameRepository::class);
        $this->app->bind(ReviewRepository::class, ReviewRepository::class);
        $this->app->bind(UserRepository::class, UserRepository::class);

        // Registrar servicios
        $this->app->bind(GameService::class, function ($app) {
            return new GameService($app->make(GameRepository::class));
        });

        $this->app->bind(ReviewService::class, function ($app) {
            return new ReviewService($app->make(ReviewRepository::class));
        });

        $this->app->bind(UserService::class, function ($app) {
            return new UserService($app->make(UserRepository::class));
        });
    }

    public function boot()
    {
        //
    }
}