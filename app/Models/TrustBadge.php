<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class TrustBadge extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'trust_badges';

    protected $fillable = [
        'title',
        'order',
        'is_active',
    ];

    protected $casts = [
        'order' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
    ];
}