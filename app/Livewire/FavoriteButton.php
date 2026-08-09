<?php

namespace App\Livewire;

use App\Models\Dish;
use App\Traits\NotificationsTrait;
use Livewire\Component;

class FavoriteButton extends Component
{
    use NotificationsTrait;

    public Dish $dish;

    public bool $isFavorited = false;

    public bool $showLabel = false;

    public function mount(Dish $dish, bool $showLabel = false): void
    {
        $this->dish = $dish;
        $this->showLabel = $showLabel;

        $this->isFavorited = auth()->check()
            && auth()->user()->favorites()->whereKey($dish->id)->exists();
    }

    public function toggle(): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $user = auth()->user();

        /* Read the current state from the database rather than trusting
        $this->isFavorited, which can drift from rapid clicks or navigation. */
        if ($user->favorites()->whereKey($this->dish->id)->exists()) {
            $user->favorites()->detach($this->dish->id);
            $this->isFavorited = false;

            $this->toastSuccess("<b>{$this->dish->name}</b> removed from your favorites.");
        } else {
            $user->favorites()->syncWithoutDetaching([$this->dish->id]);
            $this->isFavorited = true;

            $this->toastSuccess("<b>{$this->dish->name}</b> added to your favorites.");
        }

        $this->dispatch('favorites-changed');
    }

    public function render()
    {
        return view('livewire.favorite-button');
    }
}
