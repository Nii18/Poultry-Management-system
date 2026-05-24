<?php
// app/Models/SensorReading.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensorReading extends Model
{
    protected $fillable = [
        'sensor_device_id', 'house_id', 'value', 'unit',
        'reading_time', 'is_alert', 'notes'
    ];
    
    protected $casts = [
        'reading_time' => 'datetime',
        'is_alert' => 'boolean'
    ];
    
    // Relationships
    public function sensorDevice(): BelongsTo
    {
        return $this->belongsTo(SensorDevice::class);
    }
    
    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }
    
    // Scopes
    public function scopeAlerts($query)
    {
        return $query->where('is_alert', true);
    }
    
    public function scopeToday($query)
    {
        return $query->whereDate('reading_time', today());
    }
}