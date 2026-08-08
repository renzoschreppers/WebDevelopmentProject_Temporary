<?php

namespace App\Livewire;

use App\Models\Dish;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.canteen', ['title' => 'Dish'])]
class DishDetail extends Component
{
    public Dish $dish;

    public function mount(Dish $dish): void
    {
        $this->dish = $dish;
    }

    public function render()
    {
        return view('livewire.dish-detail');
    }
}
