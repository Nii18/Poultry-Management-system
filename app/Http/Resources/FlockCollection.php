<?php
// app/Http/Resources/FlockCollection.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class FlockCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => FlockResource::collection($this->collection),
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