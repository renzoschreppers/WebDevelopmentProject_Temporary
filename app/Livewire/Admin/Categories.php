<?php

namespace App\Livewire\Admin;

use App\Livewire\Forms\CategoryForm;
use App\Models\Category;
use App\Traits\NotificationsTrait;
use Illuminate\Database\QueryException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.canteen', ['title' => 'Categories'])]
class Categories extends Component
{
    use NotificationsTrait;
    use WithPagination;

    public CategoryForm $form;

    public bool $showModal = false;

    #[Url]
    public string $search = '';

    public string $sortColumn = 'sort_order';

    public string $sortDirection = 'asc';

    public int $perPage = 10;

    public bool $showDishesModal = false;

    public ?Category $viewingCategory = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
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

    public function newCategory(): void
    {
        $this->form->reset();
        $this->resetValidation();

        $this->form->sort_order = Category::count() + 1;
        $this->showModal = true;
    }

    public function editCategory(Category $category): void
    {
        $this->resetValidation();
        $this->form->fill($category);
        $this->showModal = true;
    }

    public function save(): void
    {
        if ($this->form->id) {
            $category = $this->form->update();
            $this->toastSuccess("Category <b>{$category->name}</b> has been updated.");
        } else {
            $category = $this->form->create();
            $this->toastSuccess("Category <b>{$category->name}</b> has been created.");
        }

        $this->showModal = false;
        $this->form->reset();
    }

    public function deleteConfirm(Category $category): void
    {
        $this->confirm(
            "Are you sure you want to delete <b>{$category->name}</b>?",
            [
                'heading' => 'Delete category',
                'confirmText' => 'Yes, delete it',
                'next' => [
                    'onEvent' => 'delete-category',
                    'category' => $category->id,
                ],
            ]
        );
    }

    #[On('delete-category')]
    public function deleteCategory(Category $category): void
    {
        try {
            $name = $category->name;
            $category->delete();

            $this->toastSuccess("Category <b>{$name}</b> has been deleted.");
        } catch (QueryException) {
            $this->toastDanger(
                "<b>{$category->name}</b> still has dishes. Move or delete those first."
            );
        }
    }

    public function showDishes(Category $category): void
    {
        $this->viewingCategory = $category->load(['dishes' => fn ($query) => $query->orderBy('name')]);
        $this->showDishesModal = true;
    }

    public function render()
    {
        $categories = Category::query()
            ->withCount('dishes')
            ->when($this->search, fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortColumn, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.categories', compact('categories'));
    }
}
