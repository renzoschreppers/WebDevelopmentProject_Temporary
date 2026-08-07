<?php

namespace App\Livewire\Forms;

use App\Models\Dish;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
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

    public $newImage = null;

    public ?string $image = null;

    protected $validationAttributes = [
        'category_id' => 'category',
        'tag_ids' => 'dietary tags',
        'is_available' => 'availability',
        'newImage' => 'image',
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
            'newImage' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:8192',
        ];
    }

    /* Fill the form from a model, including the many-to-many tag selection
    which fill() cannot handle on its own. */
    public function setDish(Dish $dish): void
    {
        $this->fill($dish);

        $this->price = (string) $dish->price;
        $this->tag_ids = $dish->dietaryTags->pluck('id')->all();
        $this->image = $dish->image;
        $this->newImage = null;
    }

    public function create(): Dish
    {
        $this->validate();

        $dish = Dish::create($this->payload());
        $dish->dietaryTags()->sync($this->tag_ids);
        $this->storeImage($dish);

        return $dish;
    }

    public function update(): Dish
    {
        $this->validate();

        $dish = Dish::findOrFail($this->id);
        $dish->update($this->payload());
        $dish->dietaryTags()->sync($this->tag_ids);
        $this->storeImage($dish);

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

    /* Resize the uploaded image to a square thumbnail and store it.
    Named after the dish id so each dish has at most one image file. */
    protected function storeImage(Dish $dish): void
    {
        if (! $this->newImage) {
            return;
        }

        $path = "dishes/{$dish->id}.jpg";

        $image = Image::read($this->newImage->getRealPath())
            ->cover(600, 600)
            ->toJpeg(75);

        Storage::disk('public')->put($path, (string) $image);

        $dish->update(['image' => $path]);
        $dish->touch();

        $this->image = $path;
        $this->newImage = null;
    }

    public function deleteImage(): void
    {
        if (! $this->id) {
            return;
        }

        $dish = Dish::findOrFail($this->id);

        if ($dish->image) {
            Storage::disk('public')->delete($dish->image);
            $dish->update(['image' => null]);
        }

        $this->image = null;
    }
}
