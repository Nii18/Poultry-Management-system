<?php
// app/Http/Resources/ExpenseResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'category' => $this->category,
            'description' => $this->description,
            'amount' => $this->amount,
            'expense_date' => $this->expense_date ? $this->expense_date->format('Y-m-d') : null,
            'payment_method' => $this->payment_method,
            'receipt_number' => $this->receipt_number,
            'vendor_name' => $this->vendor_name,
            'flock_id' => $this->flock_id,
            'flock_number' => $this->flock->flock_number ?? null,
            'house_id' => $this->house_id,
            'house_name' => $this->house->name ?? null,
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