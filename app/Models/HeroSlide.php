<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class HeroSlide extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'hero_slides';

    protected $fillable = [
        'headline',
        'subtitle',
        'cta_text',
        'cta_link',
        'image',
        'display_order',
        'show_text_overlay',
        'status'
    ];

    protected $casts = [
        'display_order' => 'integer',
        'show_text_overlay' => 'boolean',
        'created_at' => 'datetime',
    ];
}