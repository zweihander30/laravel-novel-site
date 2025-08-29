<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    protected $fillable = [
        'novel_id',
        'parent_id',
        'user_name',
        'user_identifier',
        'content',
        'depth'
    ];

    protected $with = ['replies']; // 返信を自動的に読み込む

    public function novel(): BelongsTo
    {
        return $this->belongsTo(Novel::class);
    }

    public function parent(): BelongTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->orderBy('created_at', 'asc');
    }

    // ルートコメント（親がない）のみ取得
    public function scopeRootComments($query)
    {
        return $query->whereNull('parent_id');
    }
}