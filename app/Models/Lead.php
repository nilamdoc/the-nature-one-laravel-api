<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $connection = 'mongodb';
    protected $collection = 'leads';

    protected $fillable = [
        'name',
        'email',
        'source',
        'date',
        'status', // New, Contacted, Converted, Closed
    ];

    protected $casts = [
        'date' => 'datetime',
    ];
}