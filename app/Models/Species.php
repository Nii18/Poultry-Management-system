<?php
// app/Models/Species.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Species extends Model
{
    protected $fillable = [
        'name', 'code', 'icon', 'color_hex', 'description',
        'default_metrics', 'growth_standards', 'health_indicators',
        'gestation_days', 'weaning_days', 'market_age_days',
        'market_weight_kg', 'lifespan_years', 'sexual_maturity_days',
        'is_active'
    ];
    
    protected $casts = [
        'default_metrics' => 'array',
        'growth_standards' => 'array',
        'health_indicators' => 'array',
        'is_active' => 'boolean'
    ];
    
    // Relationships
    public function houses(): HasMany
    {
        return $this->hasMany(House::class);
    }
    
    public function flocks(): HasMany
    {
        return $this->hasMany(Flock::class);
    }
    
    public function feedTypes(): HasMany
    {
        return $this->hasMany(FeedType::class);
    }
    
    // Helper Methods
    public function getGrowthTarget($ageInDays)
    {
        $standards = $this->growth_standards;
        
        if ($this->code === 'CH') {
            $week = ceil($ageInDays / 7);
            return $standards["week{$week}"] ?? null;
        } elseif (in_array($this->code, ['PG', 'CT', 'GT'])) {
            $month = ceil($ageInDays / 30);
            return $standards["month{$month}"] ?? null;
        }
        
        return null;
    }
    
    public function getDefaultFCR()
    {
        return $this->default_metrics['fcr_target'] ?? null;
    }
}