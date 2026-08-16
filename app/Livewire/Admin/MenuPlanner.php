<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Dish;
use App\Models\Menu;
use App\Traits\NotificationsTrait;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('components.layouts.canteen', ['title' => 'Plan menu'])]
class MenuPlanner extends Component
{
    use NotificationsTrait;

    public Menu $menu;

    public string $course = 'main';

    public string $search = '';

    public ?int $categoryFilter = null;

    public bool $hidePlanned = true;

    public bool $showCopyModal = false;

    public ?int $copyFromMenuId = null;

    public function mount(Menu $menu): void
    {
        $this->menu = $menu;
    }

    // Dishes on this menu, grouped by course and ordered within each course.
    #[Computed]
    public function planned()
    {
        return $this->menu
            ->dishes()
            ->with('dietaryTags')
            ->orderBy('dish_menu.sort_order')
            ->get()
            ->groupBy(fn (Dish $dish) => $dish->pivot->course);
    }

    #[Computed]
    public function plannedIds()
    {
        return $this->menu->dishes()->pluck('dishes.id')->all();
    }

    // Dishes available to add, filtered by the search panel.
    #[Computed]
    public function available()
    {
        return Dish::query()
            ->with('dietaryTags')
            ->where('is_available', true)
            ->when($this->search, fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
            ->when($this->categoryFilter, fn ($query) => $query->where('category_id', $this->categoryFilter))
            ->when($this->hidePlanned, fn ($query) => $query->whereNotIn('id', $this->plannedIds))
            ->orderBy('name')
            ->limit(50)
            ->get();
    }

    #[Computed]
    public function totalDishes(): int
    {
        return count($this->plannedIds);
    }

    // Other menus that have dishes, for the copy feature.
    #[Computed]
    public function copyableMenus()
    {
        return Menu::query()
            ->whereKeyNot($this->menu->id)
            ->has('dishes')
            ->orderByDesc('date')
            ->limit(20)
            ->get();
    }

    protected function refreshMenu(): void
    {
        $this->menu->refresh();

        unset($this->planned, $this->plannedIds, $this->available, $this->totalDishes);
    }

    public function addDish(Dish $dish): void
    {
        if (in_array($dish->id, $this->plannedIds)) {
            $this->toastWarning("<b>{$dish->name}</b> is already on this menu.");

            return;
        }

        $nextOrder = ($this->menu->dishes()
                ->wherePivot('course', $this->course)
                ->max('dish_menu.sort_order') ?? 0) + 1;

        $this->menu->dishes()->attach($dish->id, [
            'course' => $this->course,
            'sort_order' => $nextOrder,
            'price_override' => null,
        ]);

        $this->refreshMenu();

        $label = Menu::COURSES[$this->course];
        $this->toastSuccess("<b>{$dish->name}</b> added to {$label}.");
    }

    public function removeDish(Dish $dish): void
    {
        $this->menu->dishes()->detach($dish->id);
        $this->refreshMenu();

        $this->toastSuccess("<b>{$dish->name}</b> removed from this menu.");
    }

    // Swap a dish with its neighbor inside the same course.
    public function move(int $dishId, string $direction): void
    {
        $course = $this->menu->dishes()
            ->wherePivot('dish_id', $dishId)
            ->first()?->pivot->course;

        if (! $course) {
            return;
        }

        $ordered = $this->planned[$course] ?? collect();
        $index = $ordered->search(fn (Dish $dish) => $dish->id === $dishId);

        $target = $direction === 'up' ? $index - 1 : $index + 1;

        if ($index === false || $target < 0 || $target >= $ordered->count()) {
            return;
        }

        $current = $ordered[$index];
        $neighbour = $ordered[$target];

        // Swap the two sort_order values.
        $this->menu->dishes()->updateExistingPivot($current->id, [
            'sort_order' => $neighbour->pivot->sort_order,
        ]);

        $this->menu->dishes()->updateExistingPivot($neighbour->id, [
            'sort_order' => $current->pivot->sort_order,
        ]);

        $this->refreshMenu();
    }

    public function setPriceOverride(int $dishId, ?string $value): void
    {
        $price = $value === '' || $value === null ? null : round((float) $value, 2);

        if ($price !== null && ($price < 0 || $price > 9999.99)) {
            $this->toastDanger('That price is not valid.');

            return;
        }

        $this->menu->dishes()->updateExistingPivot($dishId, ['price_override' => $price]);
        $this->refreshMenu();

        $this->toastSuccess($price === null ? 'Price reset to the standard price.' : 'Price updated for this day.');
    }

    public function togglePublished(): void
    {
        $this->menu->update(['is_published' => ! $this->menu->is_published]);

        $state = $this->menu->is_published ? 'published' : 'set back to draft';
        $this->toastSuccess("This menu has been {$state}.");
    }

    public function copyConfirm(): void
    {
        if (! $this->copyFromMenuId) {
            $this->toastWarning('Choose a menu to copy from.');

            return;
        }

        $this->showCopyModal = false;

        $this->confirm(
            'Copying will replace every dish currently on this menu. Continue?',
            [
                'heading' => 'Copy menu',
                'confirmText' => 'Yes, copy it',
                'next' => [
                    'onEvent' => 'copy-menu',
                    'source' => $this->copyFromMenuId,
                ],
            ]
        );
    }

    #[On('copy-menu')]
    public function copyMenu(Menu $source): void
    {
        $rows = $source->dishes()
            ->get()
            ->mapWithKeys(fn (Dish $dish) => [
                $dish->id => [
                    'course' => $dish->pivot->course,
                    'sort_order' => $dish->pivot->sort_order,
                    'price_override' => $dish->pivot->price_override,
                ],
            ])
            ->all();

        $this->menu->dishes()->sync($rows);
        $this->refreshMenu();

        $this->copyFromMenuId = null;

        $this->toastSuccess("Copied {$source->dishes()->count()} dishes from {$source->date->isoFormat('D MMM')}.");
    }

    public function render()
    {
        return view('livewire.admin.menu-planner', [
            'categories' => Category::orderBy('sort_order')->get(),
        ]);
    }
}
