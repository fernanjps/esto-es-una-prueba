<?php

namespace App\Http\Controllers;

use App\Services\GameService;
use Illuminate\Http\Request;

class GameController extends Controller
{
    protected $gameService;

    public function __construct(GameService $gameService)
    {
        $this->gameService = $gameService;
    }

    public function index(Request $request)
    {
        $filters = [
            "search" => $request->get("search"),
            "is_free" => $request->get("filter") === "free",
            "is_on_sale" => $request->get("filter") === "sale",
            "is_featured" => $request->get("filter") === "featured",
            "sort_by" => $request->get("sort", "rating")
        ];

        $games = $this->gameService->getAllGames($filters);
        
        return response()->json([
            "success" => true,
            "data" => $games
        ]);
    }

    public function featured()
    {
        $games = $this->gameService->getFeaturedGames();
        
        return response()->json([
            "success" => true,
            "data" => $games
        ]);
    }

    public function free()
    {
        $games = $this->gameService->getFreeGames();
        
        return response()->json([
            "success" => true,
            "data" => $games
        ]);
    }

    public function onSale()
    {
        $games = $this->gameService->getGamesOnSale();
        
        return response()->json([
            "success" => true,
            "data" => $games
        ]);
    }

    public function show($id)
    {
        try {
            $game = $this->gameService->getGameById($id);
            
            return response()->json([
                "success" => true,
                "data" => $game
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Game not found"
            ], 404);
        }
    }
}