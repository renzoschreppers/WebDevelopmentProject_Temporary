<?php

namespace App\Livewire\Forms;

use App\Models\DietaryTag;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Form;

class DietaryTagForm extends Form
{
    public ?int $id = null;

    public string $name = '';

    public string $icon = 'leaf';

    public string $color = 'zinc';

    // Lucide/Heroicon names that have been imported with `php artisan flux:icon`.
    public const ICONS = [
        'leaf', 'sprout', 'wheat-off', 'milk-off', 'nut', 'flame',
        'fish', 'egg', 'star', 'heart', 'check-badge', 'exclamation-triangle',
    ];

    // Flux badge colours.
    public const COLORS = [
        'zinc', 'red', 'orange', 'amber', 'yellow', 'lime', 'green',
        'emerald', 'teal', 'cyan', 'sky', 'blue', 'indigo', 'violet',
        'purple', 'fuchsia', 'pink', 'rose',
    ];

    protected $validationAttributes = [
        'name' => 'tag name',
    ];

    protected $messages = [
        'name.regex' => 'The tag name must contain at least one letter.',
    ];

    public function rules(): array
    {
        return [
            'name' => "required|string|min:2|max:255|regex:/\p{L}/u|unique:dietary_tags,name,{$this->id}",
            'icon' => 'required|string|in:'.implode(',', self::ICONS),
            'color' => 'required|string|in:'.implode(',', self::COLORS),
        ];
    }

    public function create(): DietaryTag
    {
        $this->validate();

        return DietaryTag::create($this->payload());
    }

    public function update(): DietaryTag
    {
        $this->validate();

        $tag = DietaryTag::findOrFail($this->id);
        $tag->update($this->payload());

        return $tag;
    }

    protected function payload(): array
    {
        return [
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'icon' => $this->icon,
            'color' => $this->color,
        ];
    }
}
