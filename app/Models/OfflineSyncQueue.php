<?php
// app/Models/OfflineSyncQueue.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfflineSyncQueue extends Model
{
    protected $table = 'offline_sync_queue';
    
    protected $fillable = [
        'user_id', 'table_name', 'data', 'operation_type',
        'record_id', 'attempts', 'synced_at', 'error_log'
    ];
    
    protected $casts = [
        'data' => 'array',
        'error_log' => 'array',
        'synced_at' => 'datetime'
    ];
    
    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    // Scopes
    public function scopeUnsynced($query)
    {
        return $query->whereNull('synced_at');
    }
    
    public function scopeFailed($query)
    {
        return $query->where('attempts', '>=', 3);
    }
    
    public function scopeRetryable($query)
    {
        return $query->whereNull('synced_at')
                     ->where('attempts', '<', 3);
    }
}