<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Testimonial extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'testimonials';

    protected $fillable = [
        'name',
        'designation',
        'message',
        'rating',
        'image',
        'status'
    ];

    protected $casts = [
        'rating' => 'integer',
        'created_at' => 'datetime',
    ];
}