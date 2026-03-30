<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'settings';

    protected $fillable = [
        'key',
        'value'
    ];

    protected $casts = [
        'value' => 'string', // you can change to 'array' if storing JSON
    ];

    public $timestamps = true;

    /**
     * ===============================
     * 🔹 GET SINGLE SETTING
     * ===============================
     */
    public static function get($key, $default = null)
    {
        return Cache::remember("setting_{$key}", 60 * 60, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * ===============================
     * 🔹 SET SINGLE VALUE
     * ===============================
     */
    public static function set($key, $value)
    {
        $setting = self::where('key', $key)->first();

        if ($setting) {
            $setting->update(['value' => $value]);
        } else {
            $setting = self::create([
                'key' => $key,
                'value' => $value
            ]);
        }

        Cache::forget("setting_{$key}");
        Cache::forget("all_settings");

        return $setting;
    }

    /**
     * ===============================
     * 🔹 SET MULTIPLE VALUES
     * ===============================
     */
    public static function setMany(array $data)
    {
        foreach ($data as $key => $value) {
            self::set($key, $value);
        }
    }

    /**
     * ===============================
     * 🔹 GET ALL SETTINGS (KEY-VALUE)
     * ===============================
     */
    public static function getAll()
    {
        return Cache::remember('all_settings', 60 * 60, function () {
            return self::all()->pluck('value', 'key')->toArray();
        });
    }

    /**
     * ===============================
     * 🔹 CLEAR CACHE
     * ===============================
     */
    public static function clearCache()
    {
        Cache::forget('all_settings');
    }
}