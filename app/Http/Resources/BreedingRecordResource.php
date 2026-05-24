<?php
// app/Http/Resources/BreedingRecordResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BreedingRecordResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'female_flock' => [
                'id' => $this->female->id ?? null,
                'number' => $this->female->flock_number ?? null,
                'breed' => $this->female->breed_variety ?? null
            ],
            'male_flock' => [
                'id' => $this->male->id ?? null,
                'number' => $this->male->flock_number ?? null,
                'breed' => $this->male->breed_variety ?? null
            ],
            'breeding_date' => $this->breeding_date ? $this->breeding_date->format('Y-m-d') : null,
            'expected_delivery_date' => $this->expected_delivery_date ? $this->expected_delivery_date->format('Y-m-d') : null,
            'actual_delivery_date' => $this->actual_delivery_date ? $this->actual_delivery_date->format('Y-m-d') : null,
            'breeding_method' => $this->breeding_method,
            'is_successful' => $this->is_successful,
            'offspring_count' => $this->offspring_count,
            'stillborn_count' => $this->stillborn_count,
            'weaned_count' => $this->weaned_count,
            'conception_rate' => $this->conception_rate,
            'live_birth_rate' => $this->live_birth_rate,
            'weaning_rate' => $this->weaning_rate,
            'notes' => $this->notes,
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toISOString() : null,
        ];
    }
}