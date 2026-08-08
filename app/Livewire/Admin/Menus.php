<?php

namespace App\Livewire\Admin;

use App\Livewire\Forms\MenuForm;
use App\Models\Menu;
use App\Traits\NotificationsTrait;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.canteen', ['title' => 'Menus'])]
class Menus extends Component
{
    use NotificationsTrait;
    use WithPagination;

    public MenuForm $form;

    public bool $showModal = false;

    #[Url]
    public string $status = 'all';

    public string $sortDirection = 'desc';

    public int $perPage = 15;

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function toggleSort(): void
    {
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->resetPage();
    }

    public function newMenu(): void
    {
        $this->form->reset();
        $this->resetValidation();

        // Default to the next day that has no menu yet.
        $date = Carbon::today();

        while ($date->isWeekend() || Menu::whereDate('date', $date)->exists()) {
            $date->addDay();
        }

        $this->form->date = $date->toDateString();
        $this->showModal = true;
    }

    public function editMenu(Menu $menu): void
    {
        $this->resetValidation();
        $this->form->setMenu($menu);
        $this->showModal = true;
    }

    public function save(): void
    {
        if ($this->form->id) {
            $menu = $this->form->update();
            $this->toastSuccess('Menu has been updated.');
        } else {
            $menu = $this->form->create();
            $this->toastSuccess('Menu has been created. Add some dishes to it.');
        }

        $this->showModal = false;
        $this->form->reset();

        // Send the admin straight to the planner for a brand new menu.
        if (! request()->has('stay')) {
            $this->redirectRoute('admin.menus.edit', $menu, navigate: true);
        }
    }

    public function togglePublished(Menu $menu): void
    {
        $menu->update(['is_published' => ! $menu->is_published]);

        $state = $menu->is_published ? 'published' : 'set back to draft';
        $this->toastSuccess("Menu for {$menu->date->isoFormat('D MMM')} has been {$state}.");
    }

    public function deleteConfirm(Menu $menu): void
    {
        $count = $menu->dishes()->count();

        $warning = $count > 0
            ? " It contains <b>{$count}</b> ".str('dish')->plural($count).'.'
            : '';

        $this->confirm(
            "Delete the menu for <b>{$menu->date->isoFormat('dddd D MMMM')}</b>?{$warning}",
            [
                'heading' => 'Delete menu',
                'confirmText' => 'Yes, delete it',
                'next' => [
                    'onEvent' => 'delete-menu',
                    'menu' => $menu->id,
                ],
            ]
        );
    }

    #[On('delete-menu')]
    public function deleteMenu(Menu $menu): void
    {
        $date = $menu->date->isoFormat('D MMM');
        $menu->delete();

        $this->toastSuccess("Menu for {$date} has been deleted.");
    }

    public function render()
    {
        $menus = Menu::query()
            ->withCount('dishes')
            ->when($this->status === 'published', fn ($query) => $query->where('is_published', true))
            ->when($this->status === 'draft', fn ($query) => $query->where('is_published', false))
            ->when($this->status === 'upcoming', fn ($query) => $query->whereDate('date', '>=', Carbon::today()))
            ->orderBy('date', $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.menus', compact('menus'));
    }
}
