<?php

namespace App\Helpers;

use App\Models\AuditLog;

class AuditHelper
{
    public static function log(
        string $module,
        string $action,
        ?string $recordType = null,
        ?int $recordId = null,
        ?string $description = null,
        $oldValues = null,
        $newValues = null
    ) {

        AuditLog::create([

            'user_id' => auth()->id(),

            'module' => $module,

            'action' => $action,

            'record_type' => $recordType,

            'record_id' => $recordId,

            'description' => $description,

            'ip_address' => request()->ip(),

            'user_agent' => request()->userAgent(),

            'old_values' => $oldValues,

            'new_values' => $newValues,

        ]);
    }
}