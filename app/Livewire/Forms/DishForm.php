<?php

namespace App\Livewire\Forms;

use App\Models\Dish;
use Livewire\Attributes\Validate;
use Livewire\Form;

class DishForm extends Form
{
    public ?int $id = null;

    public ?int $category_id = null;

    public string $name = '';

    public ?string $description = null;

    public string $price = '';

    public bool $is_available = true;

    /** @var array<int> */
    public array $tag_ids = [];

    protected $validationAttributes = [
        'category_id' => 'category',
        'tag_ids' => 'dietary tags',
        'is_available' => 'availability',
    ];

    protected $messages = [
        'name.regex' => 'The dish name must contain at least one letter.',
    ];

    public function rules(): array
    {
        return [
            'category_id' => 'required|integer|exists:categories,id',
            'name' => "required|string|min:2|max:255|regex:/\p{L}/u|unique:dishes,name,{$this->id}",
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0|max:9999.99',
            'is_available' => 'boolean',
            'tag_ids' => 'array',
            'tag_ids.*' => 'integer|exists:dietary_tags,id',
        ];
    }

    /* Fill the form from a model, including the many-to-many tag selection
    which fill() cannot handle on its own. */
    public function setDish(Dish $dish): void
    {
        $this->fill($dish);

        $this->price = (string) $dish->price;
        $this->tag_ids = $dish->dietaryTags->pluck('id')->all();
    }

    public function create(): Dish
    {
        $this->validate();

        $dish = Dish::create($this->payload());
        $dish->dietaryTags()->sync($this->tag_ids);

        return $dish;
    }

    public function update(): Dish
    {
        $this->validate();

        $dish = Dish::findOrFail($this->id);
        $dish->update($this->payload());
        $dish->dietaryTags()->sync($this->tag_ids);

        return $dish;
    }

    protected function payload(): array
    {
        return [
            'category_id' => $this->category_id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'is_available' => $this->is_available,
        ];
    }
}
