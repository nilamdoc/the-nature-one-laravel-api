<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'emoji', // Can be an emoji character or image path
        'slug',
        'display_order',
        'parent_category',
        'seo_title',
        'seo_description',
        'is_active',
    ];
}