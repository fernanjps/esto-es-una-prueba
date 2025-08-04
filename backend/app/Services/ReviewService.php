<?php

namespace App\Services;

use App\Models\Review;
use App\Repositories\ReviewRepository;
use App\Jobs\UpdateGameStats;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    protected $reviewRepository;

    public function __construct(ReviewRepository $reviewRepository)
    {
        $this->reviewRepository = $reviewRepository;
    }

    public function getAllReviews($perPage = 10)
    {
        return $this->reviewRepository->getAllPaginated($perPage);
    }

    public function getRecentReviews($limit = 6)
    {
        return $this->reviewRepository->getRecent($limit);
    }

    public function getUserReviews($userId)
    {
        return $this->reviewRepository->getByUser($userId);
    }

    public function createReview(array $data, $userId)
    {
        DB::beginTransaction();
        try {
            // Verificar si ya existe una reseña del usuario para este juego
            if ($this->reviewRepository->userHasReviewed($userId, $data["game_id"])) {
                throw new \Exception("You have already reviewed this game");
            }

            $data["user_id"] = $userId;
            $review = $this->reviewRepository->create($data);

            // Actualizar estadísticas del juego en background
            UpdateGameStats::dispatch($data["game_id"]);

            DB::commit();
            return $review;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function updateReview($id, array $data, $userId)
    {
        DB::beginTransaction();
        try {
            $review = $this->reviewRepository->findById($id);

            if ($review->user_id !== $userId) {
                throw new \Exception("Unauthorized");
            }

            $updatedReview = $this->reviewRepository->update($id, $data);

            // Actualizar estadísticas del juego
            UpdateGameStats::dispatch($review->game_id);

            DB::commit();
            return $updatedReview;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function deleteReview($id, $userId)
    {
        DB::beginTransaction();
        try {
            $review = $this->reviewRepository->findById($id);

            if ($review->user_id !== $userId) {
                throw new \Exception("Unauthorized");
            }

            $gameId = $review->game_id;
            $this->reviewRepository->delete($id);

            // Actualizar estadísticas del juego
            UpdateGameStats::dispatch($gameId);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function adminDeleteReview($id)
    {
        DB::beginTransaction();
        try {
            $review = $this->reviewRepository->findById($id);
            $gameId = $review->game_id;
            
            $this->reviewRepository->delete($id);

            // Actualizar estadísticas del juego
            UpdateGameStats::dispatch($gameId);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}