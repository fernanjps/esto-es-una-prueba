<?php

namespace App\Http\Controllers;

use App\Services\GameService;
use App\Services\ReviewService;
use App\Models\User;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    protected $gameService;
    protected $reviewService;

    public function __construct(GameService $gameService, ReviewService $reviewService)
    {
        $this->gameService = $gameService;
        $this->reviewService = $reviewService;
        $this->middleware(["auth:api", "admin"]);
    }

    public function getStats()
    {
        try {
            $stats = [
                "total_games" => \App\Models\Game::count(),
                "total_users" => User::count(),
                "total_reviews" => Review::count(),
                "average_rating" => Review::avg("rating") ?: 0
            ];

            return response()->json([
                "success" => true,
                "data" => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Error fetching stats"
            ], 500);
        }
    }

    public function getGames()
    {
        try {
            $games = $this->gameService->getAllGamesForAdmin();

            return response()->json([
                "success" => true,
                "data" => $games
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Error fetching games"
            ], 500);
        }
    }

    public function storeGame(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "title" => "required|string|max:255|unique:games",
            "description" => "required|string",
            "price" => "required|numeric|min:0",
            "discount_price" => "nullable|numeric|min:0|lt:price",
            "image_url" => "nullable|url",
            "steam_url" => "nullable|url",
            "epic_url" => "nullable|url",
            "is_free" => "boolean",
            "is_on_sale" => "boolean",
            "is_featured" => "boolean"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => "Validation errors",
                "errors" => $validator->errors()
            ], 422);
        }

        try {
            $game = $this->gameService->createGame($validator->validated());

            return response()->json([
                "success" => true,
                "message" => "Game created successfully",
                "data" => $game
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Error creating game"
            ], 500);
        }
    }

    public function updateGame(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            "title" => "required|string|max:255|unique:games,title," . $id,
            "description" => "required|string",
            "price" => "required|numeric|min:0",
            "discount_price" => "nullable|numeric|min:0|lt:price",
            "image_url" => "nullable|url",
            "steam_url" => "nullable|url",
            "epic_url" => "nullable|url",
            "is_free" => "boolean",
            "is_on_sale" => "boolean",
            "is_featured" => "boolean"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => "Validation errors",
                "errors" => $validator->errors()
            ], 422);
        }

        try {
            $game = $this->gameService->updateGame($id, $validator->validated());

            return response()->json([
                "success" => true,
                "message" => "Game updated successfully",
                "data" => $game
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Error updating game"
            ], 500);
        }
    }

    public function destroyGame($id)
    {
        try {
            $this->gameService->deleteGame($id);

            return response()->json([
                "success" => true,
                "message" => "Game deleted successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Error deleting game"
            ], 500);
        }
    }

    // Gestión de reseñas por admin
    public function getReviews()
    {
        try {
            $reviews = $this->reviewService->getAllReviews(50);

            return response()->json([
                "success" => true,
                "data" => $reviews
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Error fetching reviews"
            ], 500);
        }
    }

    public function destroyReview($id)
    {
        try {
            $this->reviewService->adminDeleteReview($id);

            return response()->json([
                "success" => true,
                "message" => "Review deleted successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Error deleting review"
            ], 500);
        }
    }
}