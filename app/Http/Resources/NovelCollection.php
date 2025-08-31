<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class NovelCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(function ($novel) {
                return [
                    'id' => $novel->id,
                    'title' => $novel->title,
                    'description' => $novel->description,
                    'author_name' => $novel->author_name,
                    'created_at' => $novel->created_at->toIso8601String(),
                    'stats' => [
                        'chapters_count' => $novel->chapter_count ?? 0,
                        'likes_count' => $novel->likes_count ?? 0,
                        'favorites_count' => $novel->favorites_count ?? 0,
                    ],
                    'links' => [
                        'self' => "/api/novels/{$novel->id}",
                    ]
                ];
            }),
            'meta' => [
                'total' => $this->collection->count(),
                'generated_at' => now()->toIso8601String(),
            ]
        ];
    }
}
