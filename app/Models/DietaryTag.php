<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DietaryTag extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'color',
        ];

    public function dishes(): BelongsToMany
    {
        return $this->belongsToMany(Dish::class);
    }
}
