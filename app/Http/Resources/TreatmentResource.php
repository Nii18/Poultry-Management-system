<?php
// app/Http/Resources/TreatmentResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TreatmentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'flock_id' => $this->flock_id,
            'flock_number' => $this->flock->flock_number ?? null,
            'diagnosis' => $this->diagnosis,
            'product_name' => $this->product_name,
            'active_ingredient' => $this->active_ingredient,
            'dosage' => $this->dosage,
            'administration_route' => $this->administration_route,
            'start_date' => $this->start_date ? $this->start_date->format('Y-m-d') : null,
            'end_date' => $this->end_date ? $this->end_date->format('Y-m-d') : null,
            'duration_days' => $this->duration_days,
            'withdrawal' => [
                'days' => $this->withdrawal_days,
                'end_date' => $this->withdrawal_end_date ? $this->withdrawal_end_date->format('Y-m-d') : null,
                'is_active' => $this->is_withdrawal_active,
                'days_remaining' => $this->days_until_withdrawal_end
            ],
            'batch_number' => $this->batch_number,
            'animals_treated' => $this->animals_treated,
            'cost' => $this->cost,
            'notes' => $this->notes,
            'prescribed_by' => [
                'id' => $this->prescriber->id ?? null,
                'name' => $this->prescriber->name ?? null
            ],
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toISOString() : null,
        ];
    }
}