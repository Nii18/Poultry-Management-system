<?php
// app/Traits/LogsAudit.php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

trait LogsAudit
{
    /**
     * Log an audit event
     *
     * @param string $action The action performed (create, update, delete, login, etc.)
     * @param string $description Human readable description of the action
     * @param string|null $entityType The type of entity affected (flock, task, expense, etc.)
     * @param int|null $entityId The ID of the affected entity
     * @param array|null $oldValues Previous values before change
     * @param array|null $newValues New values after change
     */
    protected function logAudit(
        string $action,
        string $description,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ) {
        // Only log if user is authenticated
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
            // Silently fail - don't let audit logging break the main application
            Log::error('Failed to create audit log: ' . $e->getMessage());
        }
    }

    /**
     * Log a successful login
     */
    protected function logLogin()
    {
        $this->logAudit(
            'login',
            'User logged into the system',
            null,
            null
        );
    }

    /**
     * Log a successful logout
     */
    protected function logLogout()
    {
        $this->logAudit(
            'logout',
            'User logged out of the system',
            null,
            null
        );
    }

    /**
     * Log a create action
     */
    protected function logCreate($entityType, $entity, $description = null)
    {
        $this->logAudit(
            'create',
            $description ?? "Created new {$entityType} #{$entity->id}",
            $entityType,
            $entity->id,
            null,
            $entity->toArray()
        );
    }

    /**
     * Log an update action
     */
    protected function logUpdate($entityType, $entity, $oldValues, $description = null)
    {
        $this->logAudit(
            'update',
            $description ?? "Updated {$entityType} #{$entity->id}",
            $entityType,
            $entity->id,
            $oldValues,
            $entity->toArray()
        );
    }

    /**
     * Log a delete action
     */
    protected function logDelete($entityType, $entity, $description = null)
    {
        $this->logAudit(
            'delete',
            $description ?? "Deleted {$entityType} #{$entity->id}",
            $entityType,
            $entity->id,
            $entity->toArray(),
            null
        );
    }
}