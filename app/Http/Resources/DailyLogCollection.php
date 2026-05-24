<?php
// app/Http/Resources/DailyLogCollection.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DailyLogCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => DailyLogResource::collection($this->collection),
            'meta' => [
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'from' => $this->firstItem(),
                'to' => $this->lastItem()
            ]
        ];
    }
}