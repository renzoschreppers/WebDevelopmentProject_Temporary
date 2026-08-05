<?php

namespace Database\Seeders;

use App\Models\DietaryTag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DietaryTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            ['name' => 'Vegetarian',    'icon' => 'leaf',      'color' => 'green'],
            ['name' => 'Vegan',         'icon' => 'sprout',    'color' => 'emerald'],
            ['name' => 'Gluten-free',   'icon' => 'wheat-off', 'color' => 'amber'],
            ['name' => 'Lactose-free',  'icon' => 'milk-off',  'color' => 'sky'],
            ['name' => 'Contains nuts', 'icon' => 'nut',       'color' => 'orange'],
            ['name' => 'Spicy',         'icon' => 'flame',     'color' => 'red'],
        ];

        foreach ($tags as $tag) {
            DietaryTag::create([
                ...$tag,
                'slug' => Str::slug($tag['name']),
            ]);
        }
    }
}
