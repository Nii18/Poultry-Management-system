<?php
// app/Models/WorkerTaskAssignment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerTaskAssignment extends Model
{
    protected $table = 'worker_task_assignments';
    
    protected $fillable = [
        'task_id',
        'assigned_to',
        'assignment_date',
        'is_completed',
        'completed_at'
    ];
    
    protected $casts = [
        'assignment_date' => 'date',
        'completed_at' => 'datetime',
        'is_completed' => 'boolean'
    ];
    
    // Relationships
    public function task(): BelongsTo
    {
        return $this->belongsTo(WorkerTask::class, 'task_id');
    }
    
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}