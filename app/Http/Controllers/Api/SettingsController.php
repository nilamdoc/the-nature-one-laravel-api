<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateSettingsRequest;
use App\Http\Resources\ApiResponse;
use App\Services\SettingsService;
use Illuminate\Validation\ValidationException;
use Throwable;

class SettingsController extends Controller
{
    protected $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * 🔹 GET SETTINGS
     */
    public function index()
    {
        try {
            $data = $this->settingsService->getSettings();

            return ApiResponse::success($data, 'Settings fetched successfully');

        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return ApiResponse::error('Failed to fetch settings', [
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔹 UPDATE SETTINGS
     */
    public function update(UpdateSettingsRequest $request)
    {
        try {
            $data = $this->settingsService->updateSettings($request->validated());

            return ApiResponse::success($data, 'Settings updated successfully');

        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', $e->errors(), 422);
        } catch (Throwable $e) {
            return ApiResponse::error('Something went wrong', [
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

