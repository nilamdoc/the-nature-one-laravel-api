<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'category', // reference to product category ID
        'badge',
        'price',
        'mrp',
        'discount',
        'stock',
        'short_description',
        'long_description',
        'highlights', // comma-separated string
        'is_active',
        'is_featured',
    ];
}