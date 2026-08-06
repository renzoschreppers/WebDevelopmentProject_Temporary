<div class="flex flex-col gap-6">

    {{-- Toolbar --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <flux:input
            wire:model.live.debounce.300ms="search"
            icon="magnifying-glass"
            placeholder="Search tags"
            clearable
            class="sm:max-w-xs"
        />

        <flux:button variant="primary" icon="plus" wire:click="newTag">
            New tag
        </flux:button>
    </div>

    @if ($tags->isEmpty())
        <x-cm.empty-state
            icon="sparkles"
            title="No tags found"
            description="Try a different search, or create your first dietary tag."
        />
    @else
        {{-- Desktop table --}}
        <div class="hidden overflow-x-auto rounded-lg border border-zinc-200 md:block dark:border-zinc-700">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-zinc-200 dark:border-zinc-700">
                <tr>
                    <th class="p-3 font-semibold">Tag</th>
                    <th class="p-3 font-semibold">Slug</th>
                    <th class="p-3 font-semibold">Dishes</th>
                    <th class="p-3"></th>
                </tr>
                </thead>
                <tbody>
                @foreach ($tags as $tag)
                    <tr wire:key="tag-{{ $tag->id }}" class="border-b border-zinc-100 last:border-0 dark:border-zinc-800">
                        <td class="p-3"><x-cm.dietary-badge :tag="$tag" /></td>
                        <td class="p-3 text-zinc-500">{{ $tag->slug }}</td>
                        <td class="p-3">
                            <button wire:click="showDishes({{ $tag->id }})">
                                <flux:badge size="sm" class="hover:opacity-70">{{ $tag->dishes_count }}</flux:badge>
                            </button>
                        </td>
                        <td class="p-3">
                            <div class="flex justify-end gap-1">
                                <flux:button size="sm" icon="pencil-square" wire:click="editTag({{ $tag->id }})" tooltip="Edit {{ $tag->name }}" />
                                <flux:button size="sm" variant="danger" icon="trash" wire:click="deleteConfirm({{ $tag->id }})" tooltip="Delete {{ $tag->name }}" />
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="flex flex-col gap-3 md:hidden">
            @foreach ($tags as $tag)
                <div wire:key="tag-card-{{ $tag->id }}" class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                    <div class="flex flex-col items-start gap-2">
                        <x-cm.dietary-badge :tag="$tag" />

                        <button wire:click="showDishes({{ $tag->id }})">
                            <flux:text size="sm" class="text-zinc-500 underline">{{ $tag->dishes_count }} dishes</flux:text>
                        </button>
                    </div>

                    <div class="flex shrink-0 gap-1">
                        <flux:button size="sm" icon="pencil-square" wire:click="editTag({{ $tag->id }})" />
                        <flux:button size="sm" variant="danger" icon="trash" wire:click="deleteConfirm({{ $tag->id }})" />
                    </div>
                </div>
            @endforeach
        </div>

        {{ $tags->links() }}
    @endif

    {{-- Create / edit --}}
    <flux:modal wire:model.self="showModal" class="w-full max-w-md">
        <div class="flex flex-col gap-4">
            <flux:heading size="lg">{{ $form->id ? 'Edit tag' : 'New tag' }}</flux:heading>

            <flux:input wire:model.live.debounce.300ms="form.name" label="Name" placeholder="e.g. Vegetarian" />

            <flux:select wire:model.live="form.icon" label="Icon">
                @foreach (\App\Livewire\Forms\DietaryTagForm::ICONS as $icon)
                    <flux:select.option value="{{ $icon }}">{{ $icon }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="form.color" label="Colour">
                @foreach (\App\Livewire\Forms\DietaryTagForm::COLORS as $color)
                    <flux:select.option value="{{ $color }}">{{ ucfirst($color) }}</flux:select.option>
                @endforeach
            </flux:select>

            {{-- Live preview --}}
            <div class="flex flex-col gap-2 rounded-lg border border-dashed border-zinc-300 p-3 dark:border-zinc-700">
                <flux:text size="sm" class="text-zinc-400">Preview</flux:text>
                <div>
                    <flux:badge size="sm" :color="$form->color" :icon="$form->icon">
                        {{ $form->name !== '' ? $form->name : 'Tag name' }}
                    </flux:badge>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <flux:button wire:click="$set('showModal', false)">Cancel</flux:button>
                <flux:button variant="primary" wire:click="save">Save</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Dishes preview --}}
    <flux:modal wire:model.self="showDishesModal" class="w-full max-w-md">
        @if ($viewingTag)
            <div class="flex flex-col gap-4">
                <flux:heading size="lg">Dishes tagged {{ $viewingTag->name }}</flux:heading>

                @if ($viewingTag->dishes->isEmpty())
                    <flux:text class="text-zinc-500">No dishes use this tag yet.</flux:text>
                @else
                    <div class="flex max-h-80 flex-col divide-y divide-zinc-100 overflow-y-auto dark:divide-zinc-800">
                        @foreach ($viewingTag->dishes as $dish)
                            <div wire:key="tag-dish-{{ $dish->id }}" class="flex items-baseline justify-between gap-4 py-2">
                                <flux:text>{{ $dish->name }}</flux:text>
                                <flux:text size="sm" class="shrink-0 tabular-nums text-zinc-500">{{ $dish->price_formatted }}</flux:text>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="flex justify-end">
                    <flux:button wire:click="$set('showDishesModal', false)">Close</flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
