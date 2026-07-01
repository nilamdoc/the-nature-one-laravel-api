<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Page extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'pages';

    protected $fillable = [
        'title',
        'slug',
        'content',
        'seo_title',
        'meta_description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}