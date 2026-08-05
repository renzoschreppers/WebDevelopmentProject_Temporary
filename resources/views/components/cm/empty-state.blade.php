@props(['icon' => 'inbox', 'title' => 'Nothing here', 'description' => null])

<div class="flex flex-col items-center justify-center gap-2 rounded-lg border border-dashed border-zinc-300 p-6 text-center dark:border-zinc-700">
    <flux:icon :name="$icon" class="size-8 text-zinc-400" />
    <flux:heading size="sm">{{ $title }}</flux:heading>

    @if ($description)
        <flux:text size="sm" class="text-zinc-500">{{ $description }}</flux:text>
    @endif

    {{ $slot }}
</div>
