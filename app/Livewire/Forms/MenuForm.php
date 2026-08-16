<?php

namespace App\Livewire\Forms;

use App\Models\Menu;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Form;

class MenuForm extends Form
{
    public ?int $id = null;

    public string $date = '';

    public ?string $note = null;

    public bool $is_published = false;

    protected $validationAttributes = [
        'is_published' => 'published state',
    ];

    protected $messages = [
        'date.unique' => 'There is already a menu for this date.',
    ];

    public function rules(): array
    {
        return [
            'date' => [
                'required',
                'date',
                /* Compare date-only: the column stores a datetime, so a plain unique rule would never match an
                existing row. */
                fn ($attribute, $value, $fail) => Menu::whereDate('date', $value)
                    ->when($this->id, fn ($query) => $query->whereKeyNot($this->id))
                    ->exists()
                    ? $fail('There is already a menu for this date.')
                    : null,
                fn ($attribute, $value, $fail) => Carbon::parse($value)->isWeekend()
                    ? $fail('The canteen is closed at weekends. Choose a weekday.')
                    : null,
            ],
            'note' => 'nullable|string|max:255',
            'is_published' => 'boolean',
        ];
    }

    public function setMenu(Menu $menu): void
    {
        $this->id = $menu->id;
        $this->date = $menu->date->toDateString();
        $this->note = $menu->note;
        $this->is_published = $menu->is_published;
    }

    public function create(): Menu
    {
        $this->validate();

        return Menu::create($this->payload());
    }

    public function update(): Menu
    {
        $this->validate();

        $menu = Menu::findOrFail($this->id);
        $menu->update($this->payload());

        return $menu;
    }

    protected function payload(): array
    {
        return [
            'date' => $this->date,
            'note' => $this->note,
            'is_published' => $this->is_published,
        ];
    }
}
