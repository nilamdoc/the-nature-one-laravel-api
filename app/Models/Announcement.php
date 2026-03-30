<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Announcement extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'announcements';

    protected $fillable = [
        'text',
        'order',
        'is_active',
    ];

    protected $casts = [
        'order' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
    ];
}