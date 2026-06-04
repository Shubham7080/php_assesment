<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookVersionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'book_id' => $this->book_id,
            'version_number' => $this->version_number,
            'created_by' => $this->created_by,
            'snapshot' => $this->snapshot,
            'created_at' => $this->created_at,
        ];
    }
}
