<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NovelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'author_name' => $this->author_name,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),

            // 統計情報
            'stats' => [
                'chapter_count' => $this->when(isset($this->chapters_count), $this->chapters_count, 0),
                'likes_count' => $this->when(isset($this->likes_count), $this->likes_count, 0),
                'favorites_count' => $this->when(isset($this->favorites_count), $this->favorites_count, 0),
                'comments_count' => $this->whenLoaded('comments', fn() => $this->comments->count(), 0),
            ],

            // リレーション（読み込まれている場合のみ）
            'chapters' => ChapterResource::collection($this->whenLoaded('chapters')),
            'reaction_counts' => $this->whenLoaded(isset($this->reaction_counts), $this->reaction_counts),

            // メタ情報
            'links' => [
                'self' => route('novels.show', ['id' => $this->id]),
                'chapters' => "/api/novels/{$this->id}/cjapters",
                'comments' => "/api/novels/{$this->id}/comments",
                'reactions' => "/api/novels/{$this->id}/reactions",
            ]
        ];
    }
}