<?php
// app/Models/PerformanceMetric.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceMetric extends Model
{
    protected $fillable = [
        'flock_id', 'mortality_rate', 'feed_conversion_ratio',
        'average_daily_gain_kg', 'total_feed_consumed_kg',
        'total_weight_gained_kg', 'total_revenue', 'total_cost',
        'net_profit', 'roi_percentage', 'species_specific_metrics',
        'calculated_date'
    ];
    
    protected $casts = [
        'calculated_date' => 'date',
        'species_specific_metrics' => 'array'
    ];
    
    // Relationships
    public function flock(): BelongsTo
    {
        return $this->belongsTo(Flock::class);
    }
    
    // Accessors
    public function getProfitMarginAttribute()
    {
        if ($this->total_revenue == 0) return 0;
        return round(($this->net_profit / $this->total_revenue) * 100, 2);
    }
    
    // Scopes
    public function scopeTopPerformers($query, $limit = 10)
    {
        return $query->whereNotNull('feed_conversion_ratio')
                     ->orderBy('feed_conversion_ratio')
                     ->limit($limit);
    }
}