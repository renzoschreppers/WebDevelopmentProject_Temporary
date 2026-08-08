<div class="flex flex-col gap-6">

    {{-- Filters --}}
    <div class="flex flex-col gap-4 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">

        <div class="flex flex-col gap-3 lg:flex-row lg:items-end">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                label="Search"
                placeholder="Search dishes or ingredients"
                clearable
                class="flex-1"
            />

            <flux:select wire:model.live="category" label="Category" class="lg:max-w-48">
                <flux:select.option value="">All categories</flux:select.option>
                @foreach ($this->categories as $option)
                    <flux:select.option value="{{ $option->slug }}">{{ $option->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="sort" label="Sort by" class="lg:max-w-44">
                <flux:select.option value="name">Name A–Z</flux:select.option>
                <flux:select.option value="price_asc">Price, low to high</flux:select.option>
                <flux:select.option value="price_desc">Price, high to low</flux:select.option>
            </flux:select>
        </div>

        {{-- Dietary tags --}}
        <div class="flex flex-col gap-2">
            <flux:label>Dietary preferences</flux:label>

            <div class="flex flex-wrap gap-2">
                @foreach ($this->dietaryTags as $tag)
                    <label wire:key="filter-tag-{{ $tag->id }}" class="cursor-pointer">
                        <input type="checkbox" value="{{ $tag->slug }}" wire:model.live="tags" class="peer sr-only">

                        <span class="inline-flex items-center gap-1.5 rounded-full border border-zinc-200 px-3 py-1.5 text-sm text-zinc-600 transition peer-checked:border-zinc-800 peer-checked:bg-zinc-800 peer-checked:text-white peer-focus-visible:ring-2 dark:border-zinc-700 dark:text-zinc-300 dark:peer-checked:border-white dark:peer-checked:bg-white dark:peer-checked:text-zinc-900">
                            <flux:icon :name="$tag->icon" variant="micro" />
                            {{ $tag->name }}
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Max price --}}
        <div
            wire:key="price-{{ $maxPrice ?? 'any' }}"
            x-data="{ value: {{ $maxPrice ?? $this->priceCeiling }}, ceiling: {{ $this->priceCeiling }} }"
            x-bind:style="`--fill: ${(value / ceiling) * 100}%`"
            class="flex flex-col gap-2 sm:max-w-sm"
        >
            <flux:label>
                Max price:
                <span
                    class="font-normal text-zinc-500"
                    x-text="value >= ceiling ? 'any' : '€ ' + Number(value).toFixed(2).replace('.', ',')"
                ></span>
            </flux:label>

            <input
                type="range"
                min="0"
                max="{{ $this->priceCeiling }}"
                step="0.5"
                x-model="value"
                wire:change="setMaxPrice($event.target.value)"
                class="cm-range"
            >
        </div>

        {{-- Result count --}}
        <div class="flex items-center justify-between gap-3 border-t border-zinc-100 pt-3 dark:border-zinc-800">
            <flux:text size="sm" class="text-zinc-500">
                <span wire:loading.remove wire:target="search, category, tags, sort, setMaxPrice">
                    {{ $dishes->total() }} {{ str('dish')->plural($dishes->total()) }} found
                </span>
                <span wire:loading wire:target="search, category, tags, sort, setMaxPrice">Searching…</span>
            </flux:text>

            @if ($this->hasFilters)
                <flux:button size="sm" variant="subtle" icon="x-mark" wire:click="clearFilters">
                    Clear filters
                </flux:button>
            @endif
        </div>
    </div>

    {{-- Results --}}
    @if ($dishes->isEmpty())
        <x-cm.empty-state
            icon="magnifying-glass"
            title="No dishes match your filters"
            description="Try removing a filter or searching for something else."
        >
            <flux:button size="sm" wire:click="clearFilters" class="mt-2">Clear filters</flux:button>
        </x-cm.empty-state>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($dishes as $dish)
                <div wire:key="dish-{{ $dish->id }}">
                    <x-cm.dish-card :dish="$dish" />
                </div>
            @endforeach
        </div>

        {{ $dishes->links() }}
    @endif

    <x-cm.tag-legend :tags="$this->dietaryTags" />
</div>
