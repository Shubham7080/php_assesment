<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'author_id' => $this->author_id,
            'title' => $this->title,
            'description' => $this->description,
            'genre' => $this->genre,
            'status' => $this->status->value,
            'moderation_report' => $this->moderation_report,
            'published_at' => $this->published_at,
            'author' => new UserResource($this->whenLoaded('author')),
            'chapters' => ChapterResource::collection($this->whenLoaded('chapters')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
