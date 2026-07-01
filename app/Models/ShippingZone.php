<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ShippingZone extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'shipping_zones';

    protected $fillable = [
        'name',
        'cities',
        'price',
        'is_active',
    ];

    protected $casts = [
        'cities' => 'array',
        'price' => 'float',
        'is_active' => 'boolean',
    ];
}
