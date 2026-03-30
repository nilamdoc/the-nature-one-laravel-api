<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    public static function log($module, $action, $oldData = [], $newData = [])
    {
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'module' => $module,
                'action' => $action,
                'old_data' => $oldData,
                'new_data' => $newData,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            // Optional: log error but don't break main flow
        }
    }
}