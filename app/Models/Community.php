<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use MongoDB\Laravel\Eloquent\Model as EloquentMongo;

class Community extends EloquentMongo
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mongodb1';
    protected $collection = 'communities';

    protected $fillable = [
        'category_id',
        'created_by',
        'name',
        'slug',
        'image',
        'main_image',
        'short_description',
        'long_description',
        'rules',
        'privacy_type',
        'join_type',
        'is_featured',
        'location',
        'language',
        'cover_video',
    ];

    /** 🔗 Relationships */
    public function category()
    {
        return $this->belongsTo(CommunityCategory::class, 'category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
