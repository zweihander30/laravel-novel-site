<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isDeleted = $this->user_name === '[削除済み]';

        return [
            'id' => $this->id,
            'novel_id' => $this->novel_id,
            'parent_id' => $this->parent_id,
            'user_name' => $this->user_name,
            'content' => $this->content,
            'depth' => $this->depth,
            'is_deleted' => $isDeleted,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),

            // 返信（再帰的に変換）
            'replies' => CommentResource::collection($this->whenLoaded('replies')),
            'replies_count' => $this->when(isset($this->replies_count), $this->replies_count),

            // 現在のユーザーが編集可能か
            'can_delete' => !$isDeleted && request()->input('user_identifier') === $this->user_identifier,
        ];
    }
}
