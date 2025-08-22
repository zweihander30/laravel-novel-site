<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chapter extends Model
{
    protected $fillable = [
        'novel_id',
        'chapter_number',
        'title',
        'content'
    ];

    public function novel(): BelongsTo
    {
        return $this->belongsTo(Novel::class);
    }
}
