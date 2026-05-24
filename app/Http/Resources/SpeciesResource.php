<?php
// app/Http/Resources/SpeciesResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SpeciesResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'icon' => $this->icon,
            'color_hex' => $this->color_hex,
            'description' => $this->description,
            'default_metrics' => $this->default_metrics,
            'growth_standards' => $this->growth_standards,
            'gestation_days' => $this->gestation_days,
            'weaning_days' => $this->weaning_days,
            'market_age_days' => $this->market_age_days,
            'market_weight_kg' => $this->market_weight_kg,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toISOString() : null,
        ];
    }
}