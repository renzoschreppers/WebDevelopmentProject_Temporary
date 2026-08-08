<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'sort_order',
    ];

    protected static function booted(): void
    {
        static::deleted(function () {
            DB::transaction(function () {
                Category::orderBy('sort_order')
                    ->orderBy('id')
                    ->get()
                    ->each(fn (Category $category, int $index) => $category->updateQuietly(['sort_order' => $index + 1]));
            });
        });
    }

    public function dishes(): HasMany
    {
        return $this->hasMany(Dish::class);
    }
}
