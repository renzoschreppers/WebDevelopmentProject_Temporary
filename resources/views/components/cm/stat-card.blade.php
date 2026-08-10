@props(['label', 'value', 'icon' => null, 'hint' => null])

<div {{ $attributes->class('flex h-full flex-col gap-1 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700') }}>
    <div class="flex items-center justify-between gap-2">
        <flux:text size="sm" class="font-semibold uppercase tracking-wide text-zinc-400">{{ $label }}</flux:text>

        @if ($icon)
            <flux:icon :name="$icon" class="size-5 text-zinc-300 dark:text-zinc-600" />
        @endif
    </div>

    <flux:heading size="xl" class="tabular-nums">{{ $value }}</flux:heading>

    @if ($hint)
        <flux:text size="sm" class="text-zinc-500">{{ $hint }}</flux:text>
    @endif
</div>
