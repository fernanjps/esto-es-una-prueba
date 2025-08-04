<?php

namespace App\Repositories;

use App\Models\Game;
use Illuminate\Database\Eloquent\Collection;

class GameRepository
{
    protected $model;

    public function __construct(Game $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model->withCount("reviews")->orderBy("rating", "desc")->get();
    }

    public function getFiltered(array $filters)
    {
        $query = $this->model->withCount("reviews");

        if (isset($filters["search"])) {
            $query->where(function ($q) use ($filters) {
                $q->where("title", "ILIKE", "%" . $filters["search"] . "%")
                  ->orWhere("description", "ILIKE", "%" . $filters["search"] . "%");
            });
        }

        if (isset($filters["is_free"]) && $filters["is_free"]) {
            $query->where("is_free", true);
        }

        if (isset($filters["is_on_sale"]) && $filters["is_on_sale"]) {
            $query->where("is_on_sale", true);
        }

        if (isset($filters["is_featured"]) && $filters["is_featured"]) {
            $query->where("is_featured", true);
        }

        if (isset($filters["sort_by"])) {
            switch ($filters["sort_by"]) {
                case "rating":
                    $query->orderBy("rating", "desc");
                    break;
                case "price_low":
                    $query->orderByRaw("COALESCE(discount_price, price) ASC");
                    break;
                case "price_high":
                    $query->orderByRaw("COALESCE(discount_price, price) DESC");
                    break;
                case "name":
                    $query->orderBy("title", "asc");
                    break;
                case "reviews":
                    $query->orderBy("reviews_count", "desc");
                    break;
                default:
                    $query->orderBy("rating", "desc");
            }
        }

        return $query->get();
    }

    public function getFeatured()
    {
        return $this->model->where("is_featured", true)
                          ->withCount("reviews")
                          ->orderBy("rating", "desc")
                          ->get();
    }

    public function getFree()
    {
        return $this->model->where("is_free", true)
                          ->withCount("reviews")
                          ->orderBy("rating", "desc")
                          ->get();
    }

    public function getOnSale()
    {
        return $this->model->where("is_on_sale", true)
                          ->withCount("reviews")
                          ->orderBy("rating", "desc")
                          ->get();
    }

    public function findById($id)
    {
        return $this->model->with("reviews.user")->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $game = $this->model->findOrFail($id);
        $game->update($data);
        return $game;
    }

    public function delete($id)
    {
        $game = $this->model->findOrFail($id);
        
        // Eliminar reseñas asociadas
        $game->reviews()->delete();
        
        return $game->delete();
    }
}