<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Tax extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'taxes';

    protected $fillable = [
        'name',
        'rate',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'float',
        'is_active' => 'boolean',
    ];
}