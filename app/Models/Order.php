<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'orders';

    protected $fillable = [
        'order_id',
        'customer_name',
        'purchase_date',
        'items', // JSON array of items
        'total',
        'payment',
        'status', // pending, processing, shipped, delivered, cancelled
    ];

    protected $casts = [
        'items' => 'array', // automatically cast items to array
        'purchase_date' => 'datetime',
    ];
}