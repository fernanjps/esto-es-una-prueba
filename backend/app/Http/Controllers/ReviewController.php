<?php

namespace App\Http\Controllers;

use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    protected $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
        $this->middleware("auth:api")->except(["index", "recent"]);
    }

    public function index()
    {
        try {
            $reviews = $this->reviewService->getAllReviews(10);
            
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

    public function recent()
    {
        try {
            $reviews = $this->reviewService->getRecentReviews(6);
            
            return response()->json([
                "success" => true,
                "data" => $reviews
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Error fetching recent reviews"
            ], 500);
        }
    }

    public function userReviews()
    {
        try {
            $reviews = $this->reviewService->getUserReviews(Auth::id());
            
            return response()->json([
                "success" => true,
                "data" => $reviews
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Error fetching user reviews"
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "game_id" => "required|exists:games,id",
            "rating" => "required|integer|min:1|max:5",
            "comment" => "required|string|max:1000",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => "Validation errors",
                "errors" => $validator->errors()
            ], 422);
        }

        try {
            $review = $this->reviewService->createReview($validator->validated(), Auth::id());

            return response()->json([
                "success" => true,
                "message" => "Review created successfully",
                "data" => $review
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage()
            ], $e->getMessage() === "You have already reviewed this game" ? 409 : 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            "rating" => "required|integer|min:1|max:5",
            "comment" => "required|string|max:1000",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => "Validation errors",
                "errors" => $validator->errors()
            ], 422);
        }

        try {
            $review = $this->reviewService->updateReview($id, $validator->validated(), Auth::id());

            return response()->json([
                "success" => true,
                "message" => "Review updated successfully",
                "data" => $review
            ]);
        } catch (\Exception $e) {
            $statusCode = $e->getMessage() === "Unauthorized" ? 403 : 500;
            return response()->json([
                "success" => false,
                "message" => $e->getMessage()
            ], $statusCode);
        }
    }

    public function destroy($id)
    {
        try {
            $this->reviewService->deleteReview($id, Auth::id());

            return response()->json([
                "success" => true,
                "message" => "Review deleted successfully"
            ]);
        } catch (\Exception $e) {
            $statusCode = $e->getMessage() === "Unauthorized" ? 403 : 500;
            return response()->json([
                "success" => false,
                "message" => $e->getMessage()
            ], $statusCode);
        }
    }
}