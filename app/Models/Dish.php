<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Dish extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'image',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_available' => 'boolean',
        ];
    }

    protected function priceFormatted(): Attribute
    {
        return Attribute::make(
            get: fn () => '€ ' . number_format($this->price, 2, ',', '.'),
        );
    }

    public function priceForMenu(): string
    {
        $price = $this->pivot?->price_override ?? $this->price;

        return '€ ' . number_format($price, 2, ',', '.');
    }

    public function hasPriceOverride(): bool
    {
        return $this->pivot?->price_override !== null;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withDefault([
            'name' => 'Uncategorised',
        ]);
    }

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class)
            ->withPivot(['price_override', 'course', 'sort_order'])
            ->withTimestamps();
    }

    public function dietaryTags(): BelongsToMany
    {
        return $this->belongsToMany(DietaryTag::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
