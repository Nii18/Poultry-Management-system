<?php
// app/Http/Resources/HouseResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HouseResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'house_code' => $this->house_code,
            'species' => $this->species ? [
                'id' => $this->species->id,
                'name' => $this->species->name,
                'code' => $this->species->code
            ] : null,
            'capacity' => $this->capacity,
            'dimensions' => [
                'length_m' => $this->length_m,
                'width_m' => $this->width_m,
                'height_m' => $this->height_m,
                'area_m2' => $this->area_m2,
                'density' => $this->density
            ],
            'equipment' => [
                'feeders' => $this->feeders_count,
                'drinkers' => $this->drinkers_count,
                'fans' => $this->fans_count,
                'heaters' => $this->heaters_count
            ],
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toISOString() : null,
        ];
    }
}