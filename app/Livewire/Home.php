<?php

namespace App\Livewire;

use App\Models\DietaryTag;
use App\Models\Dish;
use App\Models\Menu;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.canteen', ['title' => 'Welcome'])]
class Home extends Component
{
    /* Today's published menu, or the next upcoming one if the canteen is closed today (weekends, holidays, or
    nothing planned). */
    #[Computed]
    public function menu(): ?Menu
    {
        return Menu::query()
            ->with(['dishes' => fn ($query) => $query
                ->where('is_available', true)
                ->with('dietaryTags')
                ->orderBy('dish_menu.sort_order')])
            ->where('is_published', true)
            ->whereDate('date', '>=', Carbon::today())
            ->orderBy('date')
            ->first();
    }

    #[Computed]
    public function isToday(): bool
    {
        return $this->menu?->date->isToday() ?? false;
    }

    // A few dishes to browse, favoring ones with a photo.
    #[Computed]
    public function featured()
    {
        return Dish::query()
            ->with(['category', 'dietaryTags'])
            ->where('is_available', true)
            ->whereNotNull('image')
            ->inRandomOrder()
            ->limit(3)
            ->get();
    }

    #[Computed]
    public function dietaryTags()
    {
        return DietaryTag::orderBy('name')->get();
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'dishes' => Dish::where('is_available', true)->count(),
            'tags' => DietaryTag::count(),
            'menus' => Menu::where('is_published', true)
                ->whereDate('date', '>=', Carbon::today())
                ->count(),
        ];
    }

    public function render()
    {
        return view('livewire.home');
    }
}
