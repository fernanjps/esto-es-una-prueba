<?php

namespace App\Repositories;

use App\Models\Review;

class ReviewRepository
{
    protected $model;

    public function __construct(Review $model)
    {
        $this->model = $model;
    }

    public function getAllPaginated($perPage = 10)
    {
        return $this->model->with(["user", "game"])
                          ->orderBy("created_at", "desc")
                          ->paginate($perPage);
    }

    public function getRecent($limit = 6)
    {
        return $this->model->with(["user", "game"])
                          ->orderBy("created_at", "desc")
                          ->limit($limit)
                          ->get();
    }

    public function getByUser($userId)
    {
        return $this->model->with(["game"])
                          ->where("user_id", $userId)
                          ->orderBy("created_at", "desc")
                          ->get();
    }

    public function findById($id)
    {
        return $this->model->with(["user", "game"])->findOrFail($id);
    }

    public function userHasReviewed($userId, $gameId)
    {
        return $this->model->where("user_id", $userId)
                          ->where("game_id", $gameId)
                          ->exists();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $review = $this->model->findOrFail($id);
        $review->update($data);
        return $review->load(["user", "game"]);
    }

    public function delete($id)
    {
        $review = $this->model->findOrFail($id);
        return $review->delete();
    }
}