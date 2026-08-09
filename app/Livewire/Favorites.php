<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.canteen', ['title' => 'My favorites'])]
class Favorites extends Component
{
    use WithPagination;

    public int $perPage = 12;

    #[On('favorites-changed')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $dishes = auth()->user()
            ->favorites()
            ->with(['category', 'dietaryTags'])
            ->where('is_available', true)
            ->orderByPivot('created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.favorites', compact('dishes'));
    }
}
