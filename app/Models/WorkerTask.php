<?php
// app/Models/WorkerTask.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkerTask extends Model
{
    protected $table = 'worker_tasks';

    protected $fillable = [
        'title',
        'description',
        'priority',
        'status',
        'due_date',
        'start_time',
        'end_time',
        'window',
        'assigned_to',
        'assigned_by',
        'completed_at',
        'is_recurring',
        'recurring_pattern',
    ];

    protected $casts = [
        'due_date'     => 'date',
        'completed_at' => 'datetime',
        'is_recurring' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(WorkerTaskAssignment::class, 'task_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForToday($query)
    {
        return $query->whereDate('due_date', today());
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getPriorityBadgeAttribute(): string
    {
        $colors = ['high' => 'danger', 'medium' => 'warning', 'low' => 'info'];
        $c = $colors[$this->priority] ?? 'secondary';
        return '<span class="badge bg-' . $c . '-soft text-' . $c . '">'
             . ucfirst($this->priority)
             . '</span>';
    }

    public function getStatusBadgeAttribute(): string
    {
        $colors = [
            'pending'     => 'warning',
            'in_progress' => 'primary',
            'completed'   => 'success',
        ];
        $c = $colors[$this->status] ?? 'secondary';
        return '<span class="badge bg-' . $c . '-soft text-' . $c . '">'
             . ucfirst(str_replace('_', ' ', $this->status))
             . '</span>';
    }
}