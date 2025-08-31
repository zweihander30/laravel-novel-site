<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChapterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'novel_id' => $this->novel_id,
            'chapter_number' => $this->chapter_number,
            'title' => $this->title,
            'content' => $this->content,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),

            // 文字数などのメタ情報
            'meta' => [
                'content_length' => mb_strlen($this->content),
                'reading time minutes' => ceil(mb_strlen($this->content) / 400), // 400文字/分で計算
            ]
        ];
    }
}
