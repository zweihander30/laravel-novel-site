<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reaction extends Model
{
    protected $fillable = [
        'novel_id',
        'user_identifier',
        'type'
    ];

    public function novel(): BelongsTo
    {
        return $this->belongsTo(Novel::class);
    }
}
