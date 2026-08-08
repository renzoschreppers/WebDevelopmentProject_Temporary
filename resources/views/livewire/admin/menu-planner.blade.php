<div class="flex flex-col gap-6">

    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <flux:button icon="arrow-left" :href="route('admin.menus')" wire:navigate tooltip="Back to menus" />

            <div class="flex flex-col">
                <flux:heading size="lg">{{ $menu->date->isoFormat('dddd D MMMM YYYY') }}</flux:heading>
                <flux:text size="sm" class="text-zinc-500">
                    {{ $this->totalDishes }} {{ str('dish')->plural($this->totalDishes) }} planned
                </flux:text>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <flux:button icon="document-duplicate" wire:click="$set('showCopyModal', true)">
                Copy from
            </flux:button>

            <flux:switch
                :checked="$menu->is_published"
                wire:click="togglePublished"
                label="Published"
            />
        </div>
    </div>

    @if ($menu->note)
        <flux:callout icon="information-circle">
            <flux:callout.text>{{ $menu->note }}</flux:callout.text>
        </flux:callout>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- LEFT: the menu --}}
        <div class="flex flex-col gap-4">
            <flux:heading size="sm" class="uppercase tracking-wide text-zinc-400">On this menu</flux:heading>

            @if ($this->totalDishes === 0)
                <x-cm.empty-state
                    icon="calendar"
                    title="Nothing planned yet"
                    description="Add dishes from the list, or copy another day's menu."
                />
            @else
                @foreach (\App\Models\Menu::COURSES as $courseKey => $courseLabel)
                    @if ($this->planned->has($courseKey))
                        <div class="flex flex-col gap-2">
                            <flux:text size="sm" class="font-semibold uppercase tracking-wide text-zinc-400">
                                {{ $courseLabel }}
                            </flux:text>

                            <div class="flex flex-col gap-2">
                                @foreach ($this->planned[$courseKey] as $index => $dish)
                                    <div
                                        wire:key="planned-{{ $dish->id }}"
                                        class="flex items-start gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700"
                                    >
                                        {{-- Arrows --}}
                                        <div class="flex shrink-0 flex-col gap-0.5">
                                            <flux:button
                                                size="xs"
                                                icon="chevron-up"
                                                wire:click="move({{ $dish->id }}, 'up')"
                                                :disabled="$index === 0"
                                                tooltip="Move up"
                                            />
                                            <flux:button
                                                size="xs"
                                                icon="chevron-down"
                                                wire:click="move({{ $dish->id }}, 'down')"
                                                :disabled="$index === $this->planned[$courseKey]->count() - 1"
                                                tooltip="Move down"
                                            />
                                        </div>

                                        {{-- Details --}}
                                        <div class="flex min-w-0 flex-1 flex-col gap-1.5">
                                            <flux:text class="font-medium">{{ $dish->name }}</flux:text>

                                            @if ($dish->dietaryTags->isNotEmpty())
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach ($dish->dietaryTags as $tag)
                                                        <x-cm.dietary-badge :tag="$tag" compact />
                                                    @endforeach
                                                </div>
                                            @endif

                                            <div class="flex items-center gap-2">
                                                <flux:text size="sm" class="shrink-0 text-zinc-400">
                                                    {{ $dish->price_formatted }}
                                                </flux:text>

                                                <flux:input
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    size="sm"
                                                    class="max-w-28"
                                                    placeholder="Override"
                                                    :value="$dish->pivot->price_override"
                                                    wire:change="setPriceOverride({{ $dish->id }}, $event.target.value)"
                                                />
                                            </div>
                                        </div>

                                        <flux:button
                                            size="sm"
                                            variant="danger"
                                            icon="x-mark"
                                            wire:click="removeDish({{ $dish->id }})"
                                            tooltip="Remove from menu"
                                            class="shrink-0"
                                        />
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif
        </div>

        {{-- RIGHT: dish picker --}}
        <div class="flex flex-col gap-4">
            <flux:heading size="sm" class="uppercase tracking-wide text-zinc-400">Add dishes</flux:heading>

            <div class="flex flex-col gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                <flux:select wire:model.live="course" label="Add to course">
                    @foreach (\App\Models\Menu::COURSES as $courseKey => $courseLabel)
                        <flux:select.option value="{{ $courseKey }}">{{ $courseLabel }}</flux:select.option>
                    @endforeach
                </flux:select>

                <div class="flex flex-col gap-2 sm:flex-row">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        icon="magnifying-glass"
                        placeholder="Search dishes"
                        clearable
                        class="flex-1"
                    />

                    <flux:select wire:model.live="categoryFilter" class="sm:max-w-40">
                        <flux:select.option value="">All categories</flux:select.option>
                        @foreach ($categories as $category)
                            <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <flux:switch wire:model.live="hidePlanned" label="Hide dishes already on this menu" />
            </div>

            @if ($this->available->isEmpty())
                <x-cm.empty-state
                    icon="magnifying-glass"
                    title="No dishes found"
                    description="Try a different search or category."
                />
            @else
                <div class="flex max-h-[32rem] flex-col gap-2 overflow-y-auto pe-1">
                    @foreach ($this->available as $dish)
                        <div
                            wire:key="available-{{ $dish->id }}"
                            class="flex items-center gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700"
                        >
                            <div class="size-10 shrink-0 overflow-hidden rounded-md border border-zinc-200 dark:border-zinc-700">
                                @if ($dish->image_url)
                                    <img src="{{ $dish->image_url }}" alt="{{ $dish->name }}" class="size-full object-cover">
                                @else
                                    <div class="flex size-full items-center justify-center">
                                        <flux:icon.photo class="size-4 text-zinc-300 dark:text-zinc-600" />
                                    </div>
                                @endif
                            </div>

                            <div class="flex min-w-0 flex-1 flex-col">
                                <flux:text class="truncate font-medium">{{ $dish->name }}</flux:text>
                                <flux:text size="sm" class="text-zinc-500">
                                    {{ $dish->category->name }} — {{ $dish->price_formatted }}
                                </flux:text>
                            </div>

                            <flux:button
                                size="sm"
                                icon="plus"
                                wire:click="addDish({{ $dish->id }})"
                                tooltip="Add to {{ \App\Models\Menu::COURSES[$course] }}"
                                class="shrink-0"
                            />
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Copy modal --}}
    <flux:modal wire:model.self="showCopyModal" class="w-full max-w-md">
        <div class="flex flex-col gap-4">
            <flux:heading size="lg">Copy from another menu</flux:heading>

            <flux:text class="text-zinc-500">
                This replaces every dish currently on this menu, including courses and price overrides.
            </flux:text>

            <flux:select wire:model="copyFromMenuId" label="Source menu">
                <flux:select.option value="">Choose a menu</flux:select.option>
                @foreach ($this->copyableMenus as $option)
                    <flux:select.option value="{{ $option->id }}">
                        {{ $option->date->isoFormat('ddd D MMM YYYY') }} ({{ $option->dishes()->count() }} dishes)
                    </flux:select.option>
                @endforeach
            </flux:select>

            <div class="flex justify-end gap-2">
                <flux:button wire:click="$set('showCopyModal', false)">Cancel</flux:button>
                <flux:button variant="primary" wire:click="copyConfirm">Copy</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
