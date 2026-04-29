<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'payments';

    protected $fillable = [
        'order_id',
        'user_id',
        'amount',
        'currency',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
        'status',
        'raw_payload',
    ];

    protected $casts = [
        'amount' => 'float',
        'raw_payload' => 'array',
    ];
}

