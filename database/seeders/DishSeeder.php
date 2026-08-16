<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\DietaryTag;
use App\Models\Dish;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DishSeeder extends Seeder
{
    public function run(): void
    {
        // Uploaded dish images no longer match any dish after a fresh seed.
        Storage::disk('public')->deleteDirectory('dishes');

        $categories = Category::pluck('id', 'slug');
        $tags = DietaryTag::pluck('id', 'slug');

        $dishes = [
            // Soups
            ['Tomato Soup with Meatballs', 'soups', 'Classic tomato soup with small beef meatballs.', 4.50, true, []],
            ['Creamy Pumpkin Soup', 'soups', 'Smooth pumpkin soup with a hint of ginger.', 4.50, true, ['vegetarian', 'gluten-free']],
            ['Chicken Noodle Soup', 'soups', 'Clear broth with chicken, vegetables and fine noodles.', 4.80, true, []],
            ['Leek and Potato Soup', 'soups', 'Hearty soup made with local leeks.', 4.20, true, ['vegetarian', 'vegan', 'gluten-free']],
            ['Spicy Lentil Soup', 'soups', 'Red lentils with cumin, chilli and coriander.', 4.50, true, ['vegetarian', 'vegan', 'gluten-free', 'lactose-free', 'spicy']],
            ['Thai Coconut Soup', 'soups', 'Coconut broth with lemongrass, ginger and chicken.', 5.20, true, ['gluten-free', 'lactose-free', 'spicy']],

            // Starters
            ['Shrimp Croquettes', 'starters', 'Two crispy croquettes with grey North Sea shrimp.', 7.50, true, []],
            ['Cheese Croquettes', 'starters', 'Two croquettes with aged cheese and fried parsley.', 6.50, true, ['vegetarian']],
            ['Garlic Bread', 'starters', 'Oven-baked baguette with garlic butter and herbs.', 3.50, true, ['vegetarian']],
            ['Bruschetta', 'starters', 'Toasted bread with tomato, basil and olive oil.', 4.50, true, ['vegetarian', 'vegan']],
            ['Goat Cheese and Walnut Toast', 'starters', 'Warm goat cheese on toast with honey and walnuts.', 6.80, true, ['vegetarian', 'contains-nuts']],
            ['Hummus with Flatbread', 'starters', 'Chickpea hummus with olive oil and warm flatbread.', 5.00, true, ['vegetarian', 'vegan', 'lactose-free']],

            // Main Courses
            ['Beef Stew with Fries', 'main-courses', 'Slow-cooked beef in dark beer sauce, served with fries.', 12.50, true, []],
            ['Vol-au-Vent with Fries', 'main-courses', 'Puff pastry filled with chicken and mushroom ragout.', 11.50, true, []],
            ['Grilled Salmon', 'main-courses', 'Salmon fillet with seasonal vegetables and boiled potatoes.', 14.00, true, ['gluten-free']],
            ['Spaghetti Bolognese', 'main-courses', 'Pasta with a rich beef and tomato sauce.', 9.50, true, []],
            ['Vegetable Curry with Rice', 'main-courses', 'Mixed vegetables in a coconut curry sauce.', 10.00, true, ['vegetarian', 'vegan', 'gluten-free', 'spicy']],
            ['Chicken Schnitzel', 'main-courses', 'Breaded chicken with mashed potatoes and gravy.', 11.00, true, []],
            ['Meatballs in Tomato Sauce', 'main-courses', 'Beef meatballs with tomato sauce and mashed potatoes.', 10.50, true, []],
            ['Quiche Lorraine', 'main-courses', 'Savoury tart with bacon and cheese, served with salad.', 9.00, true, []],
            ['Mushroom Risotto', 'main-courses', 'Creamy arborio rice with mixed mushrooms and parmesan.', 10.50, true, ['vegetarian', 'gluten-free']],
            ['Fish and Chips', 'main-courses', 'Battered cod with fries and tartar sauce.', 12.00, false, []],
            ['Chicken Tikka Masala', 'main-courses', 'Marinated chicken in a spiced tomato sauce with rice.', 11.80, true, ['gluten-free', 'spicy']],
            ['Chilli con Carne', 'main-courses', 'Beef and kidney beans with rice and sour cream.', 10.80, true, ['gluten-free', 'spicy']],
            ['Pad Thai with Chicken', 'main-courses', 'Rice noodles with peanuts, egg and tamarind sauce.', 11.20, true, ['contains-nuts', 'spicy']],
            ['Stuffed Peppers', 'main-courses', 'Bell peppers filled with rice, tomato and herbs.', 9.80, true, ['vegetarian', 'vegan', 'gluten-free', 'lactose-free']],
            ['Moroccan Vegetable Tagine', 'main-courses', 'Slow-cooked vegetables with apricot and couscous.', 10.20, true, ['vegetarian', 'vegan', 'lactose-free']],
            ['Roast Pork with Apple Sauce', 'main-courses', 'Oven-roasted pork with apple sauce and potatoes.', 11.50, true, ['gluten-free', 'lactose-free']],

            // Salads
            ['Caesar Salad', 'salads', 'Romaine lettuce, grilled chicken, croutons and parmesan.', 8.50, true, []],
            ['Greek Salad', 'salads', 'Tomato, cucumber, olives, red onion and feta.', 8.00, true, ['vegetarian', 'gluten-free']],
            ['Tuna Salad', 'salads', 'Mixed leaves with tuna, egg, beans and olives.', 9.00, true, ['gluten-free']],
            ['Falafel Bowl', 'salads', 'Chickpea falafel with hummus, couscous and fresh herbs.', 9.50, true, ['vegetarian', 'vegan']],
            ['Waldorf Salad', 'salads', 'Apple, celery and walnuts in a light yoghurt dressing.', 8.20, true, ['vegetarian', 'gluten-free', 'contains-nuts']],
            ['Quinoa and Almond Salad', 'salads', 'Quinoa with roasted almonds, cranberries and spinach.', 9.20, true, ['vegetarian', 'vegan', 'gluten-free', 'lactose-free', 'contains-nuts']],
            ['Fresh Fruit Salad Bowl', 'salads', 'Large bowl of seasonal fruit with mint.', 5.50, true, ['vegetarian', 'vegan', 'gluten-free', 'lactose-free']],

            // Sandwiches
            ['Chicken Curry Sandwich', 'sandwiches', 'Soft roll with mild chicken curry salad.', 4.50, true, []],
            ['Cheese and Ham Sandwich', 'sandwiches', 'Fresh roll with cheese, ham, lettuce and tomato.', 4.00, true, []],
            ['Hummus and Vegetable Wrap', 'sandwiches', 'Wrap with hummus, grilled vegetables and rocket.', 5.00, true, ['vegetarian', 'vegan']],
            ['Club Sandwich', 'sandwiches', 'Triple-decker with chicken, bacon, egg and lettuce.', 6.50, true, []],
            ['Spicy Chicken Wrap', 'sandwiches', 'Grilled chicken with harissa mayo and crisp lettuce.', 5.50, true, ['spicy']],
            ['Peanut Butter and Banana Sandwich', 'sandwiches', 'Wholegrain bread with peanut butter and banana.', 3.80, true, ['vegetarian', 'vegan', 'lactose-free', 'contains-nuts']],

            // Desserts
            ['Chocolate Mousse', 'desserts', 'Dark chocolate mousse with whipped cream.', 4.00, true, ['vegetarian', 'gluten-free']],
            ['Apple Pie', 'desserts', 'Warm apple pie with cinnamon, served with vanilla sauce.', 3.50, true, ['vegetarian']],
            ['Rice Pudding', 'desserts', 'Creamy rice pudding with brown sugar.', 3.50, true, ['vegetarian', 'gluten-free']],
            ['Fresh Fruit Salad', 'desserts', 'Seasonal fruit, no added sugar.', 3.00, true, ['vegetarian', 'vegan', 'gluten-free']],
            ['Pistachio Baklava', 'desserts', 'Layered filo pastry with pistachios and honey syrup.', 4.20, true, ['vegetarian', 'contains-nuts']],
            ['Walnut Brownie', 'desserts', 'Dark chocolate brownie with walnut pieces.', 4.00, true, ['vegetarian', 'contains-nuts']],
            ['Coconut Panna Cotta', 'desserts', 'Set coconut cream with mango coulis.', 4.20, true, ['vegetarian', 'gluten-free', 'lactose-free']],
            ['Sorbet Selection', 'desserts', 'Three scoops of seasonal fruit sorbet.', 3.80, true, ['vegetarian', 'vegan', 'gluten-free', 'lactose-free']],
        ];

        foreach ($dishes as [$name, $categorySlug, $description, $price, $isAvailable, $tagSlugs]) {
            $dish = Dish::create([
                'category_id' => $categories[$categorySlug],
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'is_available' => $isAvailable,
            ]);

            $dish->dietaryTags()->attach(
                collect($tagSlugs)->map(fn ($slug) => $tags[$slug])->all()
            );

            $this->attachImage($dish);
        }
    }

    // Copy a seed image into storage if one exists for this dish.
    protected function attachImage(Dish $dish): void
    {
        $source = database_path('seeders/images/'.Str::slug($dish->name).'.jpg');

        if (! file_exists($source)) {
            return;
        }

        $path = "dishes/{$dish->id}.jpg";

        Storage::disk('public')->put($path, file_get_contents($source));

        $dish->updateQuietly(['image' => $path]);
    }
}
