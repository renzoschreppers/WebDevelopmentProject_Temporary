<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Soups',        'description' => 'Freshly made soup, served with bread.',      'sort_order' => 1],
            ['name' => 'Starters',     'description' => 'Small dishes to begin your meal.',           'sort_order' => 2],
            ['name' => 'Main Courses', 'description' => 'Hot meals prepared daily in our kitchen.',   'sort_order' => 3],
            ['name' => 'Salads',       'description' => 'Light and fresh, made to order.',            'sort_order' => 4],
            ['name' => 'Sandwiches',   'description' => 'Quick options to take away.',                'sort_order' => 5],
            ['name' => 'Desserts',     'description' => 'Something sweet to finish.',                 'sort_order' => 6],
        ];

        foreach ($categories as $category) {
            Category::create([
                ...$category,
                'slug' => Str::slug($category['name']),
            ]);
        }
    }
}
