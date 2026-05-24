<?php
// app/Models/AuditLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id', 'user_role', 'action', 'entity_type', 'entity_id',
        'description', 'old_values', 'new_values', 'ip_address', 'user_agent'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForAdmin($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeForManager($query)
    {
        // Managers only see worker-related logs
        return $query->whereIn('user_role', ['worker', 'head_worker'])
            ->orWhere(function($q) {
                $q->whereIn('entity_type', ['worker_task', 'worker_attendance', 'daily_log'])
                  ->whereIn('action', ['complete', 'clock_in', 'clock_out']);
            })
            ->orderBy('created_at', 'desc');
    }
}