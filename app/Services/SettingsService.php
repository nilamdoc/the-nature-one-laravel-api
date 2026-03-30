<?php

namespace App\Services;

use App\Models\Setting;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;

class SettingsService
{
    /**
     * 🔹 Update Settings
     */
    public function updateSettings(array $data): array
    {
        try {

            // 🔹 Get old data
            $oldData = Setting::getAll();

            // 🔹 Handle file upload
            if (isset($data['logo']) && is_object($data['logo'])) {
                $data['logo'] = $data['logo']->store('settings', 'public');
            }

            // 🔹 Save new data
            Setting::setMany($data);

            // 🔹 Get updated data
            $newData = Setting::getAll();

            // 🔥 Detect changes ONLY
            $changedFields = [];
            $oldValues = [];
            $newValues = [];

            foreach ($data as $key => $value) {

                $oldValue = $oldData[$key] ?? null;
                $newValue = $newData[$key] ?? null;

                // Compare values (important: strict check)
                if ($oldValue != $newValue) {
                    $changedFields[] = $key;
                    $oldValues[$key] = $oldValue;
                    $newValues[$key] = $newValue;
                }
            }

            // 🔥 Only log if something changed
            if (!empty($changedFields)) {

                // Create readable action text
                $actionText = 'Updated settings: ' . implode(', ', $changedFields);

                \App\Services\AuditService::log(
                    module: 'settings',
                    action: $actionText,
                    oldData: $oldValues,
                    newData: $newValues
                );
            }

            return $newData;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 🔹 Get All Settings
     */
    public function getSettings(): array
    {
        return Setting::getAll();
    }
}