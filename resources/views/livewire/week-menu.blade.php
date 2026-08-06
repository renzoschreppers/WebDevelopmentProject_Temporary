<div class="flex flex-col gap-6">

    {{-- Controls --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="flex items-center gap-2">
            <flux:button icon="chevron-left" wire:click="previousWeek" aria-label="Previous week" />

            <flux:button wire:click="goToToday" :disabled="$this->isCurrentWeek">
                Today
            </flux:button>

            <flux:button icon="chevron-right" wire:click="nextWeek" aria-label="Next week" />
        </div>

        <div class="w-full sm:w-56">
            <flux:input type="date" :value="$date" wire:change="jumpToDate($event.target.value)" label="Jump to a date" />
        </div>
    </div>

    {{-- Week label --}}
    <div class="flex items-center justify-between">
        <flux:heading size="lg">
            {{ $this->weekStart->isoFormat('D MMMM') }} —
            {{ $this->weekStart->copy()->addDays(4)->isoFormat('D MMMM YYYY') }}
        </flux:heading>

        <flux:text size="sm" class="text-zinc-500" wire:loading>Loading…</flux:text>
    </div>

    {{-- Days: accordion on mobile, five columns from lg --}}
    <div
        wire:key="week-{{ $this->weekStart->toDateString() }}"
        x-data="{ open: @js($this->defaultOpenDay) }"
        class="grid grid-cols-1 gap-4 lg:grid-cols-5"
    >
        @foreach ($this->weekDays as $day)
            @php
                $key = $day->toDateString();
                $menu = $this->menus[$key] ?? null;
                $isToday = $day->isToday();
            @endphp

            <div
                wire:key="day-{{ $key }}"
                @class([
                    'rounded-lg border',
                    'border-zinc-200 dark:border-zinc-700' => ! $isToday,
                    'border-accent ring-1 ring-accent' => $isToday,
                ])
            >
                {{-- Header (tappable on mobile only) --}}
                <button
                    type="button"
                    x-on:click="open = (open === @js($key) ? null : @js($key))"
                    class="flex w-full items-center justify-between gap-2 p-4 text-left lg:pointer-events-none"
                >
                    <div class="flex items-baseline gap-2">
                        <div>
                            <flux:heading size="sm">{{ $day->isoFormat('dddd') }}</flux:heading>
                            <flux:text size="sm" class="text-zinc-500">{{ $day->isoFormat('D MMM') }}</flux:text>
                        </div>

                        @if ($isToday)
                            <flux:badge size="sm" color="lime">Today</flux:badge>
                        @endif
                    </div>

                    <span class="inline-flex lg:hidden" x-bind:class="open === @js($key) ? 'rotate-180' : ''">
                    <flux:icon.chevron-down class="size-5 text-zinc-400 transition-transform" />
                </span>
                </button>

                {{-- Body --}}
                <div x-bind:class="{ 'hidden': open !== @js($key) }" class="lg:block!">
                    <div class="flex flex-col gap-4 px-4 pb-4">

                        @if ($menu && ! $menu->is_published)
                            <div>
                                <flux:badge size="sm" color="amber" icon="eye-slash">Draft</flux:badge>
                            </div>
                        @endif

                        @if ($menu?->note)
                            <flux:text size="sm" class="italic text-zinc-500">{{ $menu->note }}</flux:text>
                        @endif

                        @if ($menu && $menu->dishes->isNotEmpty())
                            @php $byCourse = $menu->dishes->groupBy(fn ($dish) => $dish->pivot->course); @endphp

                            <div class="flex flex-col gap-8">
                                @foreach (\App\Models\Menu::COURSES as $course => $label)
                                    @if ($byCourse->has($course))
                                        <div class="flex flex-col gap-2">
                                            <flux:text size="sm" class="font-semibold uppercase tracking-wide text-zinc-400">
                                                {{ $label }}
                                            </flux:text>

                                            <div class="flex flex-col gap-3.5">
                                                @foreach ($byCourse[$course] as $dish)
                                                    <div wire:key="dish-{{ $menu->id }}-{{ $dish->id }}" class="flex flex-col gap-1">
                                                        <div class="flex items-baseline justify-between gap-4">
                                                            <flux:text class="font-medium">{{ $dish->name }}</flux:text>

                                                            @if ($dish->hasPriceOverride())
                                                                <span class="flex shrink-0 items-baseline gap-1.5">
                                                                <flux:text size="sm" class="text-zinc-400 line-through tabular-nums">
                                                                    {{ $dish->price_formatted }}
                                                                </flux:text>
                                                                <flux:text class="font-semibold text-accent tabular-nums">
                                                                    {{ $dish->priceForMenu() }}
                                                                </flux:text>
                                                            </span>
                                                            @else
                                                                <flux:text class="shrink-0 tabular-nums">
                                                                    {{ $dish->priceForMenu() }}
                                                                </flux:text>
                                                            @endif
                                                        </div>

                                                        @if ($dish->dietaryTags->isNotEmpty())
                                                            <div class="flex flex-wrap gap-1">
                                                                @foreach ($dish->dietaryTags as $tag)
                                                                    <x-cm.dietary-badge :tag="$tag" compact />
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <x-cm.empty-state
                                icon="calendar"
                                title="No menu"
                                description="Nothing planned for this day."
                            />
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <x-cm.tag-legend :tags="$this->dietaryTags" />
</div>
