@props(['tags'])

<div class="flex flex-col gap-2 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
    <flux:text size="sm" class="font-semibold uppercase tracking-wide text-zinc-400">
        Dietary labels
    </flux:text>

    <div class="flex flex-wrap gap-2">
        @foreach ($tags as $tag)
            <x-cm.dietary-badge :tag="$tag" />
        @endforeach
    </div>
</div>
