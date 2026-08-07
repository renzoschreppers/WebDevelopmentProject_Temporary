<?php

namespace App\Livewire\Admin;

use App\Livewire\Forms\DishForm;
use App\Models\Category;
use App\Models\DietaryTag;
use App\Models\Dish;
use App\Traits\NotificationsTrait;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.canteen', ['title' => 'Dishes'])]
class Dishes extends Component
{
    use NotificationsTrait;
    use WithFileUploads;
    use WithPagination;

    public DishForm $form;

    public bool $showModal = false;

    #[Url]
    public string $search = '';

    #[Url]
    public ?int $category = null;

    /** @var array<int> */
    #[Url]
    public array $tags = [];

    #[Url]
    public string $availability = 'all';

    public string $sortColumn = 'name';

    public string $sortDirection = 'asc';

    public int $perPage = 10;

    public function updated($property): void
    {
        // Any filter change should send the user back to page one.
        if (in_array($property, ['search', 'category', 'availability']) || str_starts_with($property, 'tags')) {
            $this->resetPage();
        }
    }

    public function resort(string $column): void
    {
        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'category', 'tags', 'availability']);
        $this->resetPage();
    }

    public function newDish(): void
    {
        $this->form->reset();
        $this->resetValidation();
        $this->showModal = true;
    }

    public function editDish(Dish $dish): void
    {
        $this->resetValidation();
        $this->form->setDish($dish->load('dietaryTags'));
        $this->showModal = true;
    }

    public function toggleAvailability(Dish $dish): void
    {
        $dish->update(['is_available' => ! $dish->is_available]);

        $state = $dish->is_available ? 'available' : 'unavailable';
        $this->toastSuccess("<b>{$dish->name}</b> is now {$state}.");
    }

    public function save(): void
    {
        if ($this->form->id) {
            $dish = $this->form->update();
            $this->toastSuccess("Dish <b>{$dish->name}</b> has been updated.");
        } else {
            $dish = $this->form->create();
            $this->toastSuccess("Dish <b>{$dish->name}</b> has been created.");
        }

        $this->showModal = false;
        $this->form->reset();
    }

    public function deleteConfirm(Dish $dish): void
    {
        $menuCount = $dish->menus()->count();

        $warning = $menuCount > 0
            ? " It is planned on <b>{$menuCount}</b> ".str('menu')->plural($menuCount).'.'
            : '';

        $this->confirm(
            "Are you sure you want to delete <b>{$dish->name}</b>?{$warning}",
            [
                'heading' => 'Delete dish',
                'confirmText' => 'Yes, delete it',
                'next' => [
                    'onEvent' => 'delete-dish',
                    'dish' => $dish->id,
                ],
            ]
        );
    }

    #[On('delete-dish')]
    public function deleteDish(Dish $dish): void
    {
        $name = $dish->name;
        $dish->delete();

        $this->toastSuccess("Dish <b>{$name}</b> has been deleted.");
    }

    public function removeImage(): void
    {
        $this->form->deleteImage();
        $this->toastSuccess('Image removed.');
    }

    public function render()
    {
        $dishes = Dish::query()
            ->with(['category', 'dietaryTags'])
            ->when($this->search, fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
            ->when($this->category, fn ($query) => $query->where('category_id', $this->category))
            ->when($this->availability !== 'all', fn ($query) => $query->where('is_available', $this->availability === 'available'))
            ->when($this->tags, function ($query) {
                // A dish must carry every selected tag, not just one of them.
                foreach ($this->tags as $tagId) {
                    $query->whereHas('dietaryTags', fn ($q) => $q->where('dietary_tags.id', $tagId));
                }
            })
            ->when(
                $this->sortColumn === 'category',
                fn ($query) => $query->orderBy(
                    Category::select('name')->whereColumn('categories.id', 'dishes.category_id'),
                    $this->sortDirection
                ),
                fn ($query) => $query->orderBy($this->sortColumn, $this->sortDirection)
            )
            ->paginate($this->perPage);

        return view('livewire.admin.dishes', [
            'dishes' => $dishes,
            'categories' => Category::orderBy('sort_order')->get(),
            'allTags' => DietaryTag::orderBy('name')->get(),
        ]);
    }
}
