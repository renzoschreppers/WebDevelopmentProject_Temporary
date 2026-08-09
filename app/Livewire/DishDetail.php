<?php

namespace App\Livewire;

use App\Models\Dish;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.canteen', ['title' => 'Dish'])]
class DishDetail extends Component
{
    public Dish $dish;

    public function mount(Dish $dish): void
    {
        abort_unless($dish->is_available, 404);

        $this->dish = $dish->load(['category', 'dietaryTags']);
    }

    /* Published menus from today onwards that include this dish. */
    #[Computed]
    public function upcomingMenus()
    {
        return $this->dish
            ->menus()
            ->where('is_published', true)
            ->whereDate('date', '>=', Carbon::today())
            ->orderBy('date')
            ->limit(5)
            ->get();
    }

    /* Other dishes in the same category. */
    #[Computed]
    public function related()
    {
        return Dish::query()
            ->with(['category', 'dietaryTags'])
            ->where('is_available', true)
            ->where('category_id', $this->dish->category_id)
            ->whereKeyNot($this->dish->id)
            ->inRandomOrder()
            ->limit(3)
            ->get();
    }

    public function render()
    {
        return view('livewire.dish-detail');
    }
}
