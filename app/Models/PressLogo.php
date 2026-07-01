<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class PressLogo extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'link',
        'order',
        'is_active',
    ];
}