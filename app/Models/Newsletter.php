<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Newsletter extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'newsletters';

    protected $fillable = [
        'email',
        'status', // active / unsubscribed
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}