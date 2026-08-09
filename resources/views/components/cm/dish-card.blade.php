@props(['dish'])

<div class="group relative flex h-full flex-col overflow-hidden rounded-lg border border-zinc-200 transition hover:shadow-md dark:border-zinc-700">

    <div class="aspect-[4/3] w-full overflow-hidden bg-zinc-100 dark:bg-zinc-800">
        @if ($dish->image_url)
            <img src="{{ $dish->image_url }}" alt="{{ $dish->name }}" loading="lazy" class="size-full origin-center object-cover transition duration-300 will-change-transform group-hover:scale-105">
        @else
            <div class="flex size-full items-center justify-center">
                <flux:icon.photo class="size-10 text-zinc-300 dark:text-zinc-600" />
            </div>
        @endif
    </div>

    <div class="flex flex-1 flex-col gap-2 p-4">
        <div class="flex items-start justify-between gap-3">
            <a href="{{ route('dishes.show', $dish) }}" wire:navigate class="after:absolute after:inset-0 after:content-['']">
                <flux:heading size="sm">{{ $dish->name }}</flux:heading>
            </a>

            <flux:text class="shrink-0 font-medium tabular-nums">{{ $dish->price_formatted }}</flux:text>
        </div>

        <flux:text size="sm" class="text-zinc-500">{{ $dish->category->name }}</flux:text>

        @if ($dish->description)
            <flux:text size="sm" class="line-clamp-2 text-zinc-500">{{ $dish->description }}</flux:text>
        @endif

        @if ($dish->dietaryTags->isNotEmpty())
            <div class="relative z-10 mt-auto flex flex-wrap gap-1 pt-2">
                @foreach ($dish->dietaryTags as $tag)
                    <x-cm.dietary-badge :tag="$tag" compact />
                @endforeach
            </div>
        @endif
    </div>

    <div class="absolute end-2 top-2 z-20">
        <livewire:favorite-button :dish="$dish" :key="'fav-'.$dish->id" />
    </div>
</div>
