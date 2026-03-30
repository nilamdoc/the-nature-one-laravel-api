<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\NewsletterController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\TestimonialController;

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