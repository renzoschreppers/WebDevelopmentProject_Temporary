<?php

namespace App\Livewire\Forms;

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Form;

class CategoryForm extends Form
{
    public ?int $id = null;

    public string $name = '';

    public ?string $description = null;

    public int $sort_order = 1;

    protected $validationAttributes = [
        'name' => 'category name',
        'sort_order' => 'sort order',
    ];

    protected $messages = [
        'name.regex' => 'The category name must contain at least one letter.',
    ];

    public function rules(): array
    {
        return [
            'name' => "required|string|min:2|max:255|regex:/\p{L}/u|unique:categories,name,{$this->id}",
            'description' => 'nullable|string|max:255',
            'sort_order' => 'required|integer|min:1|max:255',
        ];
    }

    /* Insert this category at the requested position and renumber the rest, so sort_order values stay unique and
    gap-free. */
    protected function resequence(Category $category): void
    {
        DB::transaction(function () use ($category) {
            $ordered = Category::orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->reject(fn (Category $item) => $item->id === $category->id)
                ->values();

            $position = max(0, min($this->sort_order - 1, $ordered->count()));

            $ordered->splice($position, 0, [$category]);

            $ordered->each(function (Category $item, int $index) {
                $item->updateQuietly(['sort_order' => $index + 1]);
            });
        });

        $this->sort_order = $category->fresh()->sort_order;
    }

    public function create(): Category
    {
        $this->validate();

        $category = Category::create([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'description' => $this->description,
            'sort_order' => $this->sort_order,
        ]);

        $this->resequence($category);

        return $category;
    }

    public function update(): Category
    {
        $this->validate();

        $category = Category::findOrFail($this->id);

        $category->update([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'description' => $this->description,
            'sort_order' => $this->sort_order,
        ]);

        $this->resequence($category);

        return $category;
    }
}
