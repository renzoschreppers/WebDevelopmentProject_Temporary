<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\DietaryTag;
use App\Models\Dish;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.canteen', ['title' => 'Admin dashboard'])]
class Dashboard extends Component
{
    #[Computed]
    public function stats(): array
    {
        return [
            'dishes' => Dish::count(),
            'unavailable' => Dish::where('is_available', false)->count(),
            'categories' => Category::count(),
            'tags' => DietaryTag::count(),
            'users' => User::count(),
            'admins' => User::where('admin', true)->count(),
            'inactive' => User::where('active', false)->count(),
            'favorites' => DB::table('dish_user')->count(),
        ];
    }

    /**
     * The next ten weekdays, each with its menu status.
     */
    #[Computed]
    public function upcoming()
    {
        $menus = Menu::query()
            ->withCount('dishes')
            ->whereDate('date', '>=', Carbon::today())
            ->whereDate('date', '<=', Carbon::today()->addDays(20))
            ->get()
            ->keyBy(fn (Menu $menu) => $menu->date->toDateString());

        $days = collect();
        $date = Carbon::today();

        while ($days->count() < 10) {
            if (! $date->isWeekend()) {
                $days->push([
                    'date' => $date->copy(),
                    'menu' => $menus[$date->toDateString()] ?? null,
                ]);
            }

            $date->addDay();
        }

        return $days;
    }

    #[Computed]
    public function problems(): array
    {
        $upcoming = $this->upcoming;

        return [
            'missing' => $upcoming->whereNull('menu')->count(),
            'drafts' => $upcoming->filter(fn ($day) => $day['menu'] && ! $day['menu']->is_published)->count(),
            'empty' => $upcoming->filter(fn ($day) => $day['menu'] && $day['menu']->dishes_count === 0)->count(),
        ];
    }

    /**
     * Dish counts per category, for the chart.
     */
    #[Computed]
    public function dishesPerCategory(): array
    {
        $categories = Category::withCount('dishes')->orderBy('sort_order')->get();

        return [
            'labels' => $categories->pluck('name')->all(),
            'values' => $categories->pluck('dishes_count')->all(),
        ];
    }

    #[Computed]
    public function recentDishes()
    {
        return Dish::with('category')->latest('updated_at')->limit(5)->get();
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
