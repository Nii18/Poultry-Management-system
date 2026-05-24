<?php
// app/Helpers/AuditHelper.php

namespace App\Helpers;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class AuditHelper
{
    public static function log(
        string $action,
        string $description,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ) {
        if (!auth()->check()) {
            return;
        }

        try {
            AuditLog::create([
                'user_id' => auth()->id(),
                'user_role' => auth()->user()->role,
                'action' => $action,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'description' => $description,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create audit log: ' . $e->getMessage());
        }
    }
}