<div class="flex flex-col gap-8">

    {{-- Stats --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        <a href="{{ route('admin.dishes') }}" wire:navigate class="transition hover:opacity-80">
            <x-cm.stat-card
                label="Dishes"
                :value="$this->stats['dishes']"
                icon="cake"
                :hint="$this->stats['unavailable'].' unavailable'"
            />
        </a>

        <a href="{{ route('admin.categories') }}" wire:navigate class="transition hover:opacity-80">
            <x-cm.stat-card
                label="Categories"
                :value="$this->stats['categories']"
                icon="tag"
                hint="Dish groupings"
            />
        </a>

        <a href="{{ route('admin.dietary-tags') }}" wire:navigate class="transition hover:opacity-80">
            <x-cm.stat-card
                label="Dietary tags"
                :value="$this->stats['tags']"
                icon="sparkles"
                hint="Allergens and diets"
            />
        </a>

        <a href="{{ route('admin.users') }}" wire:navigate class="transition hover:opacity-80">
            <x-cm.stat-card
                label="Users"
                :value="$this->stats['users']"
                icon="users"
                :hint="$this->stats['admins'].' admins, '.$this->stats['inactive'].' inactive'"
            />
        </a>

        <x-cm.stat-card
            label="Favourites"
            :value="$this->stats['favorites']"
            icon="heart"
            hint="Saved by users"
        />
    </div>

    {{-- Needs attention --}}
    @if ($this->problems['missing'] || $this->problems['drafts'] || $this->problems['empty'])
        <flux:callout variant="warning" icon="exclamation-triangle">
            <flux:callout.heading>Needs your attention</flux:callout.heading>
            <flux:callout.text>
                <ul class="list-inside list-disc">
                    @if ($this->problems['missing'])
                        <li>{{ $this->problems['missing'] }} upcoming {{ str('weekday')->plural($this->problems['missing']) }} with no menu</li>
                    @endif
                    @if ($this->problems['drafts'])
                        <li>{{ $this->problems['drafts'] }} {{ str('menu')->plural($this->problems['drafts']) }} still in draft</li>
                    @endif
                    @if ($this->problems['empty'])
                        <li>{{ $this->problems['empty'] }} {{ str('menu')->plural($this->problems['empty']) }} with no dishes</li>
                    @endif
                </ul>
            </flux:callout.text>
        </flux:callout>
    @endif

    <div class="grid grid-cols-1 items-stretch gap-6 lg:grid-cols-2">

        {{-- Menu planning --}}
        <div class="flex flex-col gap-3">
            <div class="flex h-9 items-center justify-between gap-3">
                <flux:heading size="lg">Next ten weekdays</flux:heading>

                <flux:button size="sm" variant="subtle" icon:trailing="arrow-right" :href="route('admin.menus')" wire:navigate>
                    All menus
                </flux:button>
            </div>

            <div class="flex flex-col divide-y divide-zinc-100 rounded-lg border border-zinc-200 dark:divide-zinc-800 dark:border-zinc-700">
                @foreach ($this->upcoming as $day)
                    <div wire:key="day-{{ $day['date']->toDateString() }}" class="flex items-center justify-between gap-3 p-3">
                        <div class="flex items-center gap-2">
                            <flux:text class="font-medium">{{ $day['date']->isoFormat('ddd D MMM') }}</flux:text>

                            @if ($day['date']->isToday())
                                <flux:badge size="sm" color="lime">Today</flux:badge>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            @if (! $day['menu'])
                                <flux:badge size="sm" color="red">No menu</flux:badge>
                            @else
                                @if ($day['menu']->dishes_count === 0)
                                    <flux:badge size="sm" color="red">Empty</flux:badge>
                                @else
                                    <flux:badge size="sm">{{ $day['menu']->dishes_count }} dishes</flux:badge>
                                @endif

                                @unless ($day['menu']->is_published)
                                    <flux:badge size="sm" color="zinc" icon="eye-slash">Draft</flux:badge>
                                @endunless

                                <flux:button
                                    size="xs"
                                    icon="squares-plus"
                                    :href="route('admin.menus.edit', $day['menu'])"
                                    wire:navigate
                                    tooltip="Plan this day"
                                />
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Chart + recent --}}
        <div class="flex flex-col gap-6">
            <div class="flex flex-col gap-3">
                <div class="flex h-9 items-center">
                    <flux:heading size="lg">Dishes per category</flux:heading>
                </div>

                <div class="flex flex-col gap-4 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    @php $max = max($this->dishesPerCategory['values']) ?: 1; @endphp

                    @foreach ($this->dishesPerCategory['labels'] as $i => $label)
                        @php $value = $this->dishesPerCategory['values'][$i]; @endphp

                        <div wire:key="bar-{{ $i }}" class="flex flex-col gap-1.5">
                            <div class="flex items-baseline justify-between gap-3">
                                <flux:text size="sm" class="font-medium">{{ $label }}</flux:text>
                                <flux:text size="sm" class="tabular-nums text-zinc-500">{{ $value }}</flux:text>
                            </div>

                            <div class="h-2.5 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                <div
                                    class="h-full rounded-full bg-zinc-700 transition-all duration-500 dark:bg-zinc-300"
                                    style="width: {{ round($value / $max * 100) }}%"
                                ></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-col gap-3">
                <div class="flex h-9 items-center">
                    <flux:heading size="lg">Recently updated</flux:heading>
                </div>

                <div class="flex flex-col divide-y divide-zinc-100 rounded-lg border border-zinc-200 dark:divide-zinc-800 dark:border-zinc-700">
                    @foreach ($this->recentDishes as $dish)
                        <div wire:key="recent-{{ $dish->id }}" class="flex items-center justify-between gap-3 p-3">
                            <div class="flex flex-col">
                                <flux:text class="font-medium">{{ $dish->name }}</flux:text>
                                <flux:text size="sm" class="text-zinc-500">{{ $dish->category->name }}</flux:text>
                            </div>

                            <flux:text size="sm" class="shrink-0 text-zinc-400">
                                {{ $dish->updated_at->diffForHumans() }}
                            </flux:text>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
