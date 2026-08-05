<?php

namespace App\Livewire;

use App\Models\DietaryTag;
use App\Models\Menu;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.canteen', ['title' => 'This week'])]
class WeekMenu extends Component
{
    #[Url(as: 'week')]
    public string $date = '';

    public function mount(): void
    {
        $this->date = $this->parseDate($this->date)->toDateString();
    }

    /**
     * Parse a date string safely, falling back to today.
     */
    protected function parseDate(?string $value): Carbon
    {
        try {
            return $value ? Carbon::parse($value) : Carbon::today();
        } catch (\Exception) {
            return Carbon::today();
        }
    }

    #[Computed]
    public function weekStart(): Carbon
    {
        return $this->parseDate($this->date)->startOfWeek(Carbon::MONDAY);
    }

    /**
     * Monday to Friday of the selected week.
     */
    #[Computed]
    public function weekDays()
    {
        return collect(range(0, 4))
            ->map(fn ($offset) => $this->weekStart->copy()->addDays($offset));
    }

    #[Computed]
    public function canSeeDrafts(): bool
    {
        return auth()->check() && auth()->user()->admin;
    }

    #[Computed]
    public function isCurrentWeek(): bool
    {
        return $this->weekStart->isSameWeek(Carbon::today());
    }

    /**
     * Menus for this week, keyed by date so the view can look them up directly.
     */
    #[Computed]
    public function menus()
    {
        return Menu::query()
            ->with(['dishes' => fn ($query) => $query
                ->with('dietaryTags')
                ->orderBy('dish_menu.sort_order')])
            ->whereBetween('date', [
                $this->weekStart->copy()->startOfDay(),
                $this->weekStart->copy()->addDays(4)->endOfDay(),
            ])
            ->when(! $this->canSeeDrafts, fn ($query) => $query->where('is_published', true))
            ->get()
            ->keyBy(fn (Menu $menu) => $menu->date->toDateString());
    }

    #[Computed]
    public function dietaryTags()
    {
        return DietaryTag::orderBy('name')->get();
    }

    /**
     * The day that should be expanded on mobile: the same weekday as today,
     * clamped to Friday when today falls on a weekend.
     */
    #[Computed]
    public function defaultOpenDay(): string
    {
        $offset = min(Carbon::today()->dayOfWeekIso - 1, 4);

        return $this->weekStart->copy()->addDays($offset)->toDateString();
    }

    public function previousWeek(): void
    {
        $this->setDate($this->parseDate($this->date)->subWeek());
    }

    public function nextWeek(): void
    {
        $this->setDate($this->parseDate($this->date)->addWeek());
    }

    public function goToToday(): void
    {
        $this->setDate(Carbon::today());
    }

    /* Change the selected date and clear the cached computed properties,
    otherwise the view would render with the previous week's data. */
    protected function setDate(Carbon $date): void
    {
        $this->date = $date->toDateString();

        unset($this->weekStart, $this->weekDays, $this->menus, $this->isCurrentWeek, $this->defaultOpenDay);
    }

    public function jumpToDate(string $date): void
    {
        $this->setDate($this->parseDate($date));
    }

    public function render()
    {
        return view('livewire.week-menu');
    }
}
