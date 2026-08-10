<div class="flex flex-col gap-10">

    {{-- Hero --}}
    <div class="flex flex-col gap-4">
        <flux:heading size="xl">Today at the canteen</flux:heading>

        <flux:text class="max-w-2xl text-zinc-500">
            Fresh meals prepared daily in our kitchen. Browse the full menu, check what's being served this week,
            and filter by your dietary preferences.
        </flux:text>

        <div class="flex flex-wrap gap-2">
            <flux:button variant="primary" icon="calendar-days" :href="route('menu')" wire:navigate>
                This week's menu
            </flux:button>

            <flux:button icon="squares-2x2" :href="route('dishes')" wire:navigate>
                Browse all dishes
            </flux:button>
        </div>
    </div>

    {{-- Today's menu --}}
    <div class="flex flex-col gap-4">
        @if ($this->menu)
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <flux:heading size="lg">
                        {{ $this->isToday ? 'On the menu today' : 'Coming up next' }}
                    </flux:heading>

                    <flux:badge :color="$this->isToday ? 'lime' : 'zinc'">
                        {{ $this->menu->date->isoFormat('dddd D MMMM') }}
                    </flux:badge>
                </div>

                <flux:button
                    size="sm"
                    variant="subtle"
                    icon:trailing="arrow-right"
                    :href="route('menu', ['week' => $this->menu->date->toDateString()])"
                    wire:navigate
                >
                    See the whole week
                </flux:button>
            </div>

            @if ($this->menu->note)
                <flux:callout icon="sparkles">
                    <flux:callout.text>{{ $this->menu->note }}</flux:callout.text>
                </flux:callout>
            @endif

            @if ($this->menu->dishes->isEmpty())
                <x-cm.empty-state
                    icon="calendar"
                    title="Nothing planned yet"
                    description="The kitchen hasn't published dishes for this day."
                />
            @else
                @php $byCourse = $this->menu->dishes->groupBy(fn ($dish) => $dish->pivot->course); @endphp

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach (\App\Models\Menu::COURSES as $course => $label)
                        @if ($byCourse->has($course))
                            <div wire:key="course-{{ $course }}" class="flex flex-col gap-3 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                                <flux:text size="sm" class="font-semibold uppercase tracking-wide text-zinc-400">
                                    {{ $label }}
                                </flux:text>

                                <div class="flex flex-col gap-3">
                                    @foreach ($byCourse[$course] as $dish)

                                        <a wire:key="home-dish-{{ $dish->id }}"
                                           href="{{ route('dishes.show', $dish) }}"
                                           wire:navigate
                                           class="flex flex-col gap-1 hover:opacity-70"
                                        >
                                            <div class="flex items-baseline justify-between gap-3">
                                                <flux:text class="font-medium">{{ $dish->name }}</flux:text>

                                                @if ($dish->hasPriceOverride())
                                                    <span class="flex shrink-0 items-baseline gap-1.5">
                                                        <flux:text size="sm"
                                                                   class="text-zinc-400 line-through tabular-nums">
                                                            {{ $dish->price_formatted }}
                                                        </flux:text>
                                                        <flux:text class="font-semibold tabular-nums">
                                                            {{ $dish->priceForMenu() }}
                                                        </flux:text>
                                                    </span>
                                                @else
                                                    <flux:text
                                                        class="shrink-0 tabular-nums">{{ $dish->priceForMenu() }}</flux:text>
                                                @endif
                                            </div>

                                            @if ($dish->dietaryTags->isNotEmpty())
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach ($dish->dietaryTags as $tag)
                                                        <x-cm.dietary-badge :tag="$tag" compact/>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        @else
            <x-cm.empty-state
                icon="calendar"
                title="No menus published"
                description="Check back soon to see what the kitchen is planning."
            />
        @endif
    </div>

    {{-- Featured dishes --}}
    @if ($this->featured->isNotEmpty())
        <div class="flex flex-col gap-4">
            <flux:separator variant="subtle" />

            <div class="flex flex-wrap items-center justify-between gap-3">
                <flux:heading size="lg">From our kitchen</flux:heading>

                <flux:button
                    size="sm"
                    variant="subtle"
                    icon:trailing="arrow-right"
                    :href="route('dishes')"
                    wire:navigate
                >
                    See all {{ $this->stats['dishes'] }} dishes
                </flux:button>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($this->featured as $dish)
                    <div wire:key="featured-{{ $dish->id }}">
                        <x-cm.dish-card :dish="$dish" />
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Dietary info --}}
    <div class="flex flex-col gap-4">
        <flux:separator variant="subtle" />

        <flux:heading size="lg">Eating with us</flux:heading>

        <flux:text class="max-w-2xl text-zinc-500">
            Every dish is labelled so you can see at a glance what suits you. Use the filters on the dishes page
            to find exactly what you're looking for.
        </flux:text>

        <x-cm.tag-legend :tags="$this->dietaryTags" />

        @guest
            <flux:callout icon="heart">
                <flux:callout.heading>Save your favourites</flux:callout.heading>
                <flux:callout.text>
                    Create an account to save the dishes you like and find them again in one click.
                </flux:callout.text>

                <div class="mt-3 flex flex-wrap gap-2">
                    <flux:button size="sm" variant="primary" :href="route('register')" wire:navigate>Register</flux:button>
                    <flux:button size="sm" :href="route('login')" wire:navigate>Log in</flux:button>
                </div>
            </flux:callout>
        @endguest
    </div>
</div>
