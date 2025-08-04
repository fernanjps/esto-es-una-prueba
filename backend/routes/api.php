<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Ruta de health check
Route::get("health", function () {
    return response()->json([
        "status" => "ok",
        "timestamp" => now(),
        "service" => "GameReviews API"
    ]);
});

// Rutas de autenticación
Route::group([
    "middleware" => "api",
    "prefix" => "auth"
], function () {
    Route::post("register", [AuthController::class, "register"]);
    Route::post("login", [AuthController::class, "login"]);
    Route::post("logout", [AuthController::class, "logout"])->middleware("auth:api");
    Route::get("me", [AuthController::class, "me"])->middleware("auth:api");
    Route::put("update-profile", [AuthController::class, "updateProfile"])->middleware("auth:api");
});

// Rutas de juegos (públicas)
Route::group([
    "prefix" => "games"
], function () {
    Route::get("/", [GameController::class, "index"]);
    Route::get("featured", [GameController::class, "featured"]);
    Route::get("free", [GameController::class, "free"]);
    Route::get("on-sale", [GameController::class, "onSale"]);
    Route::get("{id}", [GameController::class, "show"]);
});

// Rutas de reseñas
Route::group([
    "prefix" => "reviews"
], function () {
    Route::get("/", [ReviewController::class, "index"]);
    Route::get("recent", [ReviewController::class, "recent"]);
    Route::post("/", [ReviewController::class, "store"])->middleware("auth:api");
    Route::put("{id}", [ReviewController::class, "update"])->middleware("auth:api");
    Route::delete("{id}", [ReviewController::class, "destroy"])->middleware("auth:api");
});

// Rutas de usuario autenticado
Route::group([
    "middleware" => "auth:api",
    "prefix" => "user"
], function () {
    Route::get("reviews", [ReviewController::class, "userReviews"]);
});

// Rutas de administración
Route::group([
    "middleware" => ["auth:api", "admin"],
    "prefix" => "admin"
], function () {
    Route::get("stats", [AdminController::class, "getStats"]);
    Route::get("games", [AdminController::class, "getGames"]);
    Route::post("games", [AdminController::class, "storeGame"]);
    Route::put("games/{id}", [AdminController::class, "updateGame"]);
    Route::delete("games/{id}", [AdminController::class, "destroyGame"]);
});
Route::get("admin/users", [AdminController::class, "getUsers"])->middleware("auth:api");

