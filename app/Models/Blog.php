<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Blog extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'blogs';

    protected $fillable = [
        'title',
        'slug',
        'category',
        'excerpt',
        'body',
        'featured_image',
        'author',
        'is_published',
        'is_featured',
        'publish_date',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'publish_date' => 'datetime',
        'created_at' => 'datetime',
    ];
}