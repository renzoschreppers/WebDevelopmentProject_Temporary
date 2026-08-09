<div class="flex flex-col gap-6">
    @if ($dishes->isEmpty())
        <x-cm.empty-state
            icon="heart"
            title="No favorites yet"
            description="Save dishes you like and they'll appear here."
        >
            <flux:button size="sm" :href="route('dishes')" wire:navigate class="mt-2">
                Browse dishes
            </flux:button>
        </x-cm.empty-state>
    @else
        <flux:text class="text-zinc-500">
            {{ $dishes->total() }} {{ str('dish')->plural($dishes->total()) }} saved
        </flux:text>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($dishes as $dish)
                <div wire:key="fav-dish-{{ $dish->id }}">
                    <x-cm.dish-card :dish="$dish" />
                </div>
            @endforeach
        </div>

        {{ $dishes->links() }}
    @endif
</div>
