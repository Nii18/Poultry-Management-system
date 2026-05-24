<?php
// app/Models/DailyLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyLog extends Model
{
    protected $fillable = [
        'flock_id', 'log_date', 'mortality_count', 'culling_count',
        'feed_intake_kg', 'water_consumption_liters', 'average_weight_kg',
        'species_metrics', 'min_temperature_c', 'max_temperature_c',
        'min_humidity', 'max_humidity', 'ammonia_ppm', 'notes', 'created_by'
    ];
    
    protected $casts = [
        'log_date' => 'date',
        'species_metrics' => 'array'
    ];
    
    // Relationships
    public function flock(): BelongsTo
    {
        return $this->belongsTo(Flock::class);
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    // Accessors
    public function getTotalLossAttribute()
    {
        return $this->mortality_count + $this->culling_count;
    }
    
    public function getMortalityRateAttribute()
    {
        $currentCount = $this->flock->current_count + $this->total_loss;
        if ($currentCount == 0) return 0;
        return round(($this->total_loss / $currentCount) * 100, 2);
    }
    
    public function getTemperatureRangeAttribute()
    {
        if ($this->min_temperature_c && $this->max_temperature_c) {
            return "{$this->min_temperature_c}°C - {$this->max_temperature_c}°C";
        }
        return null;
    }
    
    // Helper methods for species-specific metrics
    public function getEggProduction()
    {
        return $this->species_metrics['egg_production'] ?? null;
    }
    
    public function getMilkYield()
    {
        return $this->species_metrics['milk_yield_liters'] ?? null;
    }
    
    public function getBackfatThickness()
    {
        return $this->species_metrics['backfat_thickness'] ?? null;
    }
}