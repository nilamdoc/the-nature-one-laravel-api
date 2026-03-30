<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\NewsletterController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\TestimonialController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\HeroSlideController;

Route::get('/test', function () {
    return response()->json(['message' => 'API WORKING']);
});
Route::prefix('settings')->group(function () {
    Route::get('/', [SettingsController::class, 'index']);
    Route::post('/update', [SettingsController::class, 'update']);
});
Route::prefix('audit-logs')->group(function () {
    Route::get('/', [AuditLogController::class, 'index']);
});
Route::prefix('newsletter')->group(function () {
    Route::get('/', [NewsletterController::class, 'index']);
    Route::delete('/{id}', [NewsletterController::class, 'destroy']);
});
Route::get('/newsletter/export', [NewsletterController::class, 'export']);
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe']);
Route::get('/unsubscribe', [NewsletterController::class, 'unsubscribe']);
Route::prefix('faqs')->group(function () {
    Route::get('/', [FaqController::class, 'index']);
    Route::post('/', [FaqController::class, 'store']);
    Route::get('/{id}', [FaqController::class, 'show']);
    Route::post('/{id}', [FaqController::class, 'update']);
    Route::delete('/{id}', [FaqController::class, 'destroy']);
});
Route::prefix('testimonials')->group(function () {
    Route::get('/', [TestimonialController::class, 'index']);
    Route::post('/', [TestimonialController::class, 'store']);
    Route::get('/{id}', [TestimonialController::class, 'show']);
    Route::post('/{id}', [TestimonialController::class, 'update']); // use POST for form-data
    Route::delete('/{id}', [TestimonialController::class, 'destroy']);
});
Route::prefix('blogs')->group(function () {
    Route::get('/', [BlogController::class, 'index']);
    Route::post('/', [BlogController::class, 'store']);
    Route::get('/{id}', [BlogController::class, 'show']);
    Route::post('/{id}', [BlogController::class, 'update']); // form-data support
    Route::delete('/{id}', [BlogController::class, 'destroy']);
});
Route::prefix('pages')->group(function () {
    Route::get('/', [PageController::class, 'index']);
    Route::post('/', [PageController::class, 'store']);
    Route::get('/{id}', [PageController::class, 'show']);
    Route::post('/{id}', [PageController::class, 'update']); // form-data safe
    Route::delete('/{id}', [PageController::class, 'destroy']);
    // 🔥 Slug based (frontend)
    Route::get('/slug/{slug}', [PageController::class, 'getBySlug']);
});

Route::prefix('hero-slides')->group(function () {
    Route::get('/', [HeroSlideController::class, 'index']);
    Route::post('/', [HeroSlideController::class, 'store']);
    Route::get('/{id}', [HeroSlideController::class, 'show']);
    Route::post('/{id}', [HeroSlideController::class, 'update']); // form-data
    Route::delete('/{id}', [HeroSlideController::class, 'destroy']);
});