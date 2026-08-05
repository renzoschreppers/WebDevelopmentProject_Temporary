<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Menu extends Model
{
    public const COURSES = [
        'soup' => 'Soup',
        'starter' => 'Starter',
        'main' => 'Main course',
        'salad' => 'Salad',
        'sandwich' => 'Sandwich',
        'dessert' => 'Dessert',
    ];

    protected $fillable = [
        'date',
        'note',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_published' => 'boolean',
        ];
    }

    public function dishes(): BelongsToMany
    {
        return $this->belongsToMany(Dish::class)
            ->withPivot(['price_override', 'course', 'sort_order'])
            ->withTimestamps();
    }
}
