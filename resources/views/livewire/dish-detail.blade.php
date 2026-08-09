<div class="flex flex-col gap-8">

    <flux:button icon="arrow-left" :href="route('dishes')" wire:navigate class="self-start">
        Back to dishes
    </flux:button>

    {{-- Main --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 lg:gap-10">

        {{-- Image --}}
        <div class="aspect-[4/3] w-full overflow-hidden rounded-xl bg-zinc-100 dark:bg-zinc-800">
            @if ($dish->image_url)
                <img src="{{ $dish->image_url }}" alt="{{ $dish->name }}" class="size-full object-cover">
            @else
                <div class="flex size-full flex-col items-center justify-center gap-2">
                    <flux:icon.photo class="size-12 text-zinc-300 dark:text-zinc-600" />
                    <flux:text size="sm" class="text-zinc-400">No photo yet</flux:text>
                </div>
            @endif
        </div>

        {{-- Details --}}
        <div class="flex flex-col gap-5">

            <div class="flex flex-col gap-2">
                <flux:text size="sm" class="font-semibold uppercase tracking-wide text-zinc-400">
                    {{ $dish->category->name }}
                </flux:text>

                <flux:heading size="xl">{{ $dish->name }}</flux:heading>

                <div class="flex items-center justify-between gap-4">
                    <flux:heading size="lg" class="tabular-nums">{{ $dish->price_formatted }}</flux:heading>

                    <livewire:favorite-button :dish="$dish" :show-label="true" :key="'fav-detail-'.$dish->id" />
                </div>
            </div>

            @if ($dish->description)
                <flux:text class="text-zinc-600 dark:text-zinc-300">{{ $dish->description }}</flux:text>
            @endif

            @if ($dish->dietaryTags->isNotEmpty())
                <div class="flex flex-col gap-2">
                    <flux:text size="sm" class="font-semibold uppercase tracking-wide text-zinc-400">
                        Dietary information
                    </flux:text>

                    <div class="flex flex-wrap gap-2">
                        @foreach ($dish->dietaryTags as $tag)
                            <x-cm.dietary-badge :tag="$tag" />
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- When it's served --}}
            <div class="flex flex-col gap-2 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:text size="sm" class="font-semibold uppercase tracking-wide text-zinc-400">
                    Coming up
                </flux:text>

                @if ($this->upcomingMenus->isEmpty())
                    <flux:text size="sm" class="text-zinc-500">
                        Not on any published menu at the moment.
                    </flux:text>
                @else
                    <div class="flex flex-col divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($this->upcomingMenus as $menu)

                            <a wire:key="menu-{{ $menu->id }}"
                               href="{{ route('menu', ['week' => $menu->date->toDateString()]) }}"
                               wire:navigate
                               class="flex items-center justify-between gap-3 py-2 hover:opacity-70"
                            >
                                <flux:text class="font-medium">
                                    {{ $menu->date->isoFormat('dddd D MMMM') }}
                                    @if ($menu->date->isToday())
                                        <flux:badge size="sm" color="lime" class="ms-1">Today</flux:badge>
                                    @endif
                                </flux:text>

                                <div class="flex shrink-0 items-center gap-2">
                                    @if ($menu->pivot->price_override !== null)
                                        <flux:text size="sm" class="text-zinc-400 line-through tabular-nums">
                                            {{ $dish->price_formatted }}
                                        </flux:text>
                                        <flux:text size="sm" class="font-semibold tabular-nums">
                                            € {{ number_format((float) $menu->pivot->price_override, 2, ',', '.') }}
                                        </flux:text>
                                    @endif

                                    <flux:icon.chevron-right class="size-4 text-zinc-400"/>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Related --}}
    @if ($this->related->isNotEmpty())
        <div class="flex flex-col gap-4">
            <flux:separator variant="subtle" />

            <div class="flex items-center justify-between gap-3">
                <flux:heading size="lg">More {{ $dish->category->name }}</flux:heading>

                <flux:button
                    size="sm"
                    variant="subtle"
                    icon:trailing="arrow-right"
                    :href="route('dishes', ['category' => $dish->category->slug])"
                    wire:navigate
                >
                    See all
                </flux:button>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($this->related as $relatedDish)
                    <div wire:key="related-{{ $relatedDish->id }}">
                        <x-cm.dish-card :dish="$relatedDish" />
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
