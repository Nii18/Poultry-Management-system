<?php

namespace App\Traits;

use App\Models\Notification;
use App\Models\User;

trait Notifiable
{
    /**
     * Send notification to a specific user
     */
    public function notifyUser($userId, $title, $message, $type = 'info', $entityType = null, $entityId = null, $severity = 'info')
    {
        return Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'severity' => $severity,
            'created_by' => auth()->id()
        ]);
    }

    /**
     * Send notification to multiple users by role
     */
    public function notifyByRole($roles, $title, $message, $type = 'info', $entityType = null, $entityId = null, $severity = 'info')
    {
        $users = User::whereIn('role', (array)$roles)->get();
        
        foreach ($users as $user) {
            $this->notifyUser($user->id, $title, $message, $type, $entityType, $entityId, $severity);
        }
    }

    /**
     * Send notification to all admins and managers
     */
    public function notifyManagement($title, $message, $type = 'info', $entityType = null, $entityId = null, $severity = 'info')
    {
        $this->notifyByRole(['admin', 'manager'], $title, $message, $type, $entityType, $entityId, $severity);
    }

    /**
     * Send notification to specific flock's assigned workers
     */
    public function notifyFlockWorkers($flockId, $title, $message, $type = 'info', $severity = 'info')
    {
        // Get workers assigned to this flock (you can adjust this logic)
        $workers = User::whereIn('role', ['worker', 'head_worker'])
            ->whereHas('assignedFlocks', function($q) use ($flockId) {
                $q->where('flock_id', $flockId);
            })->get();
        
        foreach ($workers as $worker) {
            $this->notifyUser($worker->id, $title, $message, $type, 'flock', $flockId, $severity);
        }
    }
}