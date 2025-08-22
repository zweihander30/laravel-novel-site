<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
