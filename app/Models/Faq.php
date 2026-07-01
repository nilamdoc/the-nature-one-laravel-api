<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Faq extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'faqs';

    protected $fillable = [
        'question',
        'answer',
        'category',
        'status', // active / inactive
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}