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
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\TrustBadgeController;
use App\Http\Controllers\Api\CertificationController;
use App\Http\Controllers\Api\PressLogoController;
use App\Http\Controllers\Api\WhyUsController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\UserProfileController;
use App\Http\Controllers\Api\ProductCategoryController;
use App\Http\Controllers\Api\ProductController;

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
Route::prefix('announcements')->group(function () {
    Route::get('/', [AnnouncementController::class, 'index']);
    Route::post('/', [AnnouncementController::class, 'store']);
    Route::get('/{id}', [AnnouncementController::class, 'show']);
    Route::post('/{id}', [AnnouncementController::class, 'update']);
    Route::delete('/{id}', [AnnouncementController::class, 'destroy']);
});
Route::prefix('trust-badges')->group(function () {
    Route::get('/', [TrustBadgeController::class, 'index']);
    Route::post('/', [TrustBadgeController::class, 'store']);
    Route::get('/{id}', [TrustBadgeController::class, 'show']);
    Route::post('/{id}', [TrustBadgeController::class, 'update']);
    Route::delete('/{id}', [TrustBadgeController::class, 'destroy']);
});
Route::prefix('certifications')->group(function () {
    Route::get('/', [CertificationController::class, 'index']);
    Route::post('/', [CertificationController::class, 'store']);
    Route::get('/{id}', [CertificationController::class, 'show']);
    Route::post('/{id}', [CertificationController::class, 'update']); // using POST for update as per your reference
    Route::delete('/{id}', [CertificationController::class, 'destroy']);
});
Route::prefix('press-logos')->group(function () {
    Route::get('/', [PressLogoController::class, 'index']);
    Route::post('/', [PressLogoController::class, 'store']);
    Route::get('/{id}', [PressLogoController::class, 'show']);
    Route::post('/{id}', [PressLogoController::class, 'update']); // POST for update
    Route::delete('/{id}', [PressLogoController::class, 'destroy']);
});
Route::prefix('why-us')->group(function () {
    Route::get('/', [WhyUsController::class, 'index']);
    Route::post('/', [WhyUsController::class, 'store']);
    Route::get('/{id}', [WhyUsController::class, 'show']);
    Route::post('/{id}', [WhyUsController::class, 'update']); // POST for update
    Route::delete('/{id}', [WhyUsController::class, 'destroy']);
});
Route::prefix('banners')->group(function () {
    Route::get('/', [BannerController::class, 'index']);
    Route::post('/', [BannerController::class, 'store']);
    Route::get('/{id}', [BannerController::class, 'show']);
    Route::post('/{id}', [BannerController::class, 'update']); // POST for update
    Route::delete('/{id}', [BannerController::class, 'destroy']);
});
Route::prefix('users')->group(function () {
    Route::get('/', [UserProfileController::class, 'index']);
    Route::post('/', [UserProfileController::class, 'store']);
    Route::get('/{id}', [UserProfileController::class, 'show']);
    Route::post('/{id}', [UserProfileController::class, 'update']); // POST for update
    Route::delete('/{id}', [UserProfileController::class, 'destroy']);
});
Route::prefix('product-categories')->group(function () {
    Route::get('/', [ProductCategoryController::class, 'index']);
    Route::post('/', [ProductCategoryController::class, 'store']);
    Route::get('/{id}', [ProductCategoryController::class, 'show']);
    Route::post('/{id}', [ProductCategoryController::class, 'update']); // POST for update
    Route::delete('/{id}', [ProductCategoryController::class, 'destroy']);
});
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::post('/', [ProductController::class, 'store']);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::post('/{id}', [ProductController::class, 'update']); // POST for update
    Route::delete('/{id}', [ProductController::class, 'destroy']);
});