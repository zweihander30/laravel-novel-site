<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB; 

class Novel extends Model
{
    protected $fillable = [
        'title',
        'description',
        'author_name'
    ];

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    public function getReactionCounts()
    {
        return $this->reactions()
            ->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type');
    }
}
