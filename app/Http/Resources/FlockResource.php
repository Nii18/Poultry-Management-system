<?php
// app/Http/Resources/FlockResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FlockResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'flock_number' => $this->flock_number,
            'species' => [
                'id' => $this->species->id ?? null,
                'name' => $this->species->name ?? null,
                'code' => $this->species->code ?? null,
                'icon' => $this->species->icon ?? null,
                'color' => $this->species->color_hex ?? null
            ],
            'house' => [
                'id' => $this->house->id ?? null,
                'name' => $this->house->name ?? null,
                'code' => $this->house->house_code ?? null
            ],
            'breed_variety' => $this->breed_variety,
            'start_date' => $this->start_date ? $this->start_date->format('Y-m-d') : null,
            'end_date' => $this->end_date ? $this->end_date->format('Y-m-d') : null,
            'age_days' => $this->age_in_days,
            'age_weeks' => $this->age_in_weeks,
            'initial_count' => $this->initial_count,
            'current_count' => $this->current_count,
            'mortality_count' => $this->total_mortality,
            'mortality_rate' => $this->mortality_rate,
            'production_type' => $this->production_type,
            'status' => $this->status,
            'feed_conversion_ratio' => $this->feed_conversion_ratio,
            'average_daily_gain' => $this->average_daily_gain,
            'total_feed_consumed' => $this->total_feed_consumed,
            'total_revenue' => $this->total_revenue,
            'is_breeding_stock' => $this->is_breeding_stock,
            'parity_number' => $this->parity_number,
            'notes' => $this->notes,
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toISOString() : null,
        ];
    }
}