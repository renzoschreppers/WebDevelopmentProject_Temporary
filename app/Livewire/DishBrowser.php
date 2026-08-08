<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\DietaryTag;
use App\Models\Dish;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.canteen', ['title' => 'Dishes'])]
class DishBrowser extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $category = '';

    /** @var array<string> */
    #[Url]
    public array $tags = [];

    /** Null means no price limit. */
    #[Url]
    public ?string $maxPrice = null;

    #[Url]
    public string $sort = 'name';

    public int $perPage = 12;

    public function updated(string $property): void
    {
        if ($property !== 'page') {
            $this->resetPage();
        }
    }

    /* The slider reports its raw value; treat the ceiling as "no limit"
    so the URL stays clean when nothing is actually filtered. */
    public function setMaxPrice(string $value): void
    {
        $this->maxPrice = (float) $value >= $this->priceCeiling ? null : $value;

        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'category', 'tags', 'maxPrice', 'sort']);
        $this->resetPage();
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy('sort_order')->get();
    }

    #[Computed]
    public function dietaryTags()
    {
        return DietaryTag::orderBy('name')->get();
    }

    #[Computed]
    public function priceCeiling(): int
    {
        return (int) ceil(Dish::where('is_available', true)->max('price') ?? 20);
    }

    #[Computed]
    public function hasFilters(): bool
    {
        return $this->search !== ''
            || $this->category !== ''
            || $this->tags !== []
            || $this->maxPrice !== null
            || $this->sort !== 'name';
    }

    public function render()
    {
        $dishes = Dish::query()
            ->with(['category', 'dietaryTags'])
            ->where('is_available', true)
            ->when($this->search, fn ($query) => $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%");
            }))
            ->when($this->category, fn ($query) => $query->whereHas(
                'category',
                fn ($q) => $q->where('slug', $this->category)
            ))
            ->when($this->tags, function ($query) {
                // A dish must carry every selected tag, not just one of them.
                foreach ($this->tags as $slug) {
                    $query->whereHas('dietaryTags', fn ($q) => $q->where('slug', $slug));
                }
            })
            ->when($this->maxPrice !== null, fn ($query) => $query->where('price', '<=', (float) $this->maxPrice))
            ->when($this->sort === 'price_asc', fn ($query) => $query->orderBy('price'))
            ->when($this->sort === 'price_desc', fn ($query) => $query->orderByDesc('price'))
            ->when($this->sort === 'name', fn ($query) => $query->orderBy('name'))
            ->paginate($this->perPage);

        return view('livewire.dish-browser', compact('dishes'));
    }
}
