<?php
// app/Http/Resources/DailyLogResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DailyLogResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'flock_id' => $this->flock_id,
            'flock_number' => $this->flock->flock_number ?? null,
            'log_date' => $this->log_date ? $this->log_date->format('Y-m-d') : null,
            'mortality_count' => $this->mortality_count,
            'culling_count' => $this->culling_count,
            'total_loss' => $this->total_loss,
            'mortality_rate' => $this->mortality_rate,
            'feed_intake_kg' => $this->feed_intake_kg,
            'water_consumption_liters' => $this->water_consumption_liters,
            'average_weight_kg' => $this->average_weight_kg,
            'species_metrics' => $this->species_metrics,
            'environmental' => [
                'temperature' => [
                    'min' => $this->min_temperature_c,
                    'max' => $this->max_temperature_c,
                    'range' => $this->temperature_range
                ],
                'humidity' => [
                    'min' => $this->min_humidity,
                    'max' => $this->max_humidity
                ],
                'ammonia_ppm' => $this->ammonia_ppm
            ],
            'notes' => $this->notes,
            'created_by' => [
                'id' => $this->creator->id ?? null,
                'name' => $this->creator->name ?? null
            ],
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toISOString() : null,
        ];
    }
}