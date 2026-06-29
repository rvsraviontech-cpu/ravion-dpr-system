<?php

namespace App\Framework\Traits;

use App\Helpers\AuditHelper;

trait MasterAuditTrait
{
    protected function auditCreated(
        string $module,
        string $entity,
        int|string $entityId,
        string $name,
        array $newValues
    ): void {
        AuditHelper::log(
            $module,
            'Created',
            $entity,
            $entityId,
            "{$entity} created: {$name}",
            null,
            $newValues
        );
    }

    protected function auditUpdated(
        string $module,
        string $entity,
        int|string $entityId,
        string $name,
        array $oldValues,
        array $newValues
    ): void {
        AuditHelper::log(
            $module,
            'Updated',
            $entity,
            $entityId,
            "{$entity} updated: {$name}",
            $oldValues,
            $newValues
        );
    }

    protected function auditStatusChanged(
        string $module,
        string $entity,
        int|string $entityId,
        string $name,
        bool $isActive,
        array $oldValues,
        array $newValues
    ): void {
        AuditHelper::log(
            $module,
            $isActive ? 'Activated' : 'Deactivated',
            $entity,
            $entityId,
            $isActive
                ? "{$entity} activated: {$name}"
                : "{$entity} deactivated: {$name}",
            $oldValues,
            $newValues
        );
    }
}