<?php
// app/Models/WorkerTaskAssignment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class WorkerTaskAssignment extends Model
{
    protected $table = 'worker_task_assignments';

    protected $fillable = [
        'task_id',
        'assigned_to',
        'assignment_date',
        'is_completed',
        'completed_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'assignment_date' => 'date',
        'completed_at'    => 'datetime',
        'is_completed'    => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function task(): BelongsTo
    {
        return $this->belongsTo(WorkerTask::class, 'task_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isMissed(): bool
{
    if ($this->status === 'completed') return false;
    if (!$this->task?->window)         return false;

    $windowEnds = [
        'morning'   => '12:00:00',
        'afternoon' => '17:00:00',
        'evening'   => '22:00:00',
    ];

    $windowEnd = $windowEnds[$this->task->window] ?? null;
    if (!$windowEnd) return false;

    $closes = Carbon::parse(
        $this->assignment_date->format('Y-m-d') . ' ' . $windowEnd
    );

    return Carbon::now()->gt($closes);
}

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForToday($query)
    {
        return $query->whereDate('assignment_date', today());
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeByWindow($query, string $window)
    {
        return $query->whereHas('task', fn($q) => $q->where('window', $window));
    }
}