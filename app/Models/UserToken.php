<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class UserToken extends Model
{
    protected $connection = 'mongodb'; // ✅ VERY IMPORTANT
    protected $collection = 'user_tokens'; // ✅ collection name

    protected $fillable = [
        'user_id',
        'token'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', '_id');
    }
}