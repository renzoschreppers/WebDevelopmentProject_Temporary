<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Menu;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $byCategory = Category::with(['dishes' => fn ($query) => $query->where('is_available', true)])
            ->get()
            ->keyBy('slug');

        $monday = Carbon::now()->startOfWeek(Carbon::MONDAY);

        // Six weeks of menus, starting from the Monday of the current week.
        foreach (range(0, 5) as $weekOffset) {
            foreach (range(0, 4) as $dayOffset) {
                $date = $monday->copy()->addWeeks($weekOffset)->addDays($dayOffset);

                $menu = Menu::create([
                    'date' => $date,
                    'note' => $date->isFriday() ? 'Friday special: main course at a reduced price.' : null,
                    'is_published' => $weekOffset <= 1,
                ]);

                $sortOrder = 1;
                $attach = [];

                $pick = function ($categorySlug, $count, $course) use ($byCategory, &$sortOrder, &$attach) {
                    foreach ($byCategory[$categorySlug]->dishes->random($count)->values() as $dish) {
                        $attach[$dish->id] = [
                            'course' => $course,
                            'sort_order' => $sortOrder++,
                            'price_override' => null,
                        ];
                    }
                };

                $pick('soups', 1, 'soup');
                $pick('starters', 1, 'starter');
                $pick('main-courses', 3, 'main');
                $pick('salads', 1, 'salad');
                $pick('sandwiches', 2, 'sandwich');
                $pick('desserts', 2, 'dessert');

                // Friday: discount the first main course by €2
                if ($date->isFriday()) {
                    $firstMain = collect($attach)->search(fn ($pivot) => $pivot['course'] === 'main');

                    if ($firstMain !== false) {
                        $attach[$firstMain]['price_override'] = round(
                            $byCategory['main-courses']->dishes->find($firstMain)->price - 2,
                            2
                        );
                    }
                }

                $menu->dishes()->attach($attach);
            }
        }
    }
}
