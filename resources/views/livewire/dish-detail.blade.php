<div class="flex flex-col gap-6">
    <flux:button icon="arrow-left" :href="route('dishes')" wire:navigate class="self-start">
        Back to dishes
    </flux:button>

    <flux:heading size="lg">{{ $dish->name }}</flux:heading>
    <flux:text class="text-zinc-500">Detail page coming next.</flux:text>
</div>
