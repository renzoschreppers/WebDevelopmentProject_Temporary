<?php

namespace App\Livewire\Admin;

use App\Livewire\Forms\DietaryTagForm;
use App\Models\DietaryTag;
use App\Traits\NotificationsTrait;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.canteen', ['title' => 'Dietary tags'])]
class DietaryTags extends Component
{
    use NotificationsTrait;
    use WithPagination;

    public DietaryTagForm $form;

    public bool $showModal = false;

    public bool $showDishesModal = false;

    public ?DietaryTag $viewingTag = null;

    #[Url]
    public string $search = '';

    public int $perPage = 10;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function newTag(): void
    {
        $this->form->reset();
        $this->resetValidation();
        $this->showModal = true;
    }

    public function editTag(DietaryTag $tag): void
    {
        $this->resetValidation();
        $this->form->fill($tag);
        $this->showModal = true;
    }

    public function save(): void
    {
        if ($this->form->id) {
            $tag = $this->form->update();
            $this->toastSuccess("Tag <b>{$tag->name}</b> has been updated.");
        } else {
            $tag = $this->form->create();
            $this->toastSuccess("Tag <b>{$tag->name}</b> has been created.");
        }

        $this->showModal = false;
        $this->form->reset();
    }

    public function showDishes(DietaryTag $tag): void
    {
        $this->viewingTag = $tag->load(['dishes' => fn ($query) => $query->orderBy('name')]);
        $this->showDishesModal = true;
    }

    /* Deleting a tag cascades: it is removed from every dish that uses it. The database will not stop this, so
    the warning has to be explicit. */
    public function deleteConfirm(DietaryTag $tag): void
    {
        $count = $tag->dishes()->count();

        $warning = $count > 0
            ? " It will be removed from <b>{$count}</b> ".str('dish')->plural($count).'.'
            : '';

        $this->confirm(
            "Are you sure you want to delete <b>{$tag->name}</b>?{$warning}",
            [
                'heading' => 'Delete dietary tag',
                'confirmText' => 'Yes, delete it',
                'next' => [
                    'onEvent' => 'delete-tag',
                    'tag' => $tag->id,
                ],
            ]
        );
    }

    #[On('delete-tag')]
    public function deleteTag(DietaryTag $tag): void
    {
        $name = $tag->name;
        $tag->delete();

        $this->toastSuccess("Tag <b>{$name}</b> has been deleted.");
    }

    public function render()
    {
        $tags = DietaryTag::query()
            ->withCount('dishes')
            ->when($this->search, fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.admin.dietary-tags', compact('tags'));
    }
}
