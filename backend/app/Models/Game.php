<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        "title",
        "description",
        "price",
        "discount_price",
        "image_url",
        "steam_url",
        "epic_url",
        "rating",
        "is_free",
        "is_on_sale",
        "is_featured",
    ];

    protected $casts = [
        "price" => "decimal:2",
        "discount_price" => "decimal:2",
        "rating" => "decimal:2",
        "is_free" => "boolean",
        "is_on_sale" => "boolean",
        "is_featured" => "boolean",
    ];

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function averageRating()
    {
        return $this->reviews()->avg("rating");
    }
}