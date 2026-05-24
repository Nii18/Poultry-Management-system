<?php
// app/Models/SensorDevice.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SensorDevice extends Model
{
    protected $fillable = [
        'house_id', 'device_id', 'device_name', 'sensor_type',
        'api_key', 'status', 'last_reading_at', 'notes'
    ];
    
    protected $casts = [
        'last_reading_at' => 'datetime'
    ];
    
    // Relationships
    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }
    
    public function readings(): HasMany
    {
        return $this->hasMany(SensorReading::class);
    }
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    
    public function scopeByType($query, $type)
    {
        return $query->where('sensor_type', $type);
    }
}