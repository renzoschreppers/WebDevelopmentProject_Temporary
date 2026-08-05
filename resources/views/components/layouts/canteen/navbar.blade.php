<flux:navlist variant="outline">
    <flux:navlist.item icon="home" :href="route('home')" :current="request()->routeIs('home')" wire:navigate>
        Home
    </flux:navlist.item>

    <flux:navlist.item icon="calendar-days" :href="route('menu')" :current="request()->routeIs('menu')" wire:navigate>
        This week
    </flux:navlist.item>

    <flux:navlist.item icon="squares-2x2" href="#" wire:navigate>
        Dishes
    </flux:navlist.item>

    @auth
        <flux:navlist.item icon="heart" href="#" wire:navigate>
            My favourites
        </flux:navlist.item>
    @endauth
</flux:navlist>

@auth
    @if (auth()->user()->admin)
        <flux:navlist variant="outline" class="mt-4">
            <flux:navlist.group heading="Admin">
                <flux:navlist.item icon="chart-bar" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate>
                    Dashboard
                </flux:navlist.item>
                <flux:navlist.item icon="cake" href="#" wire:navigate>Dishes</flux:navlist.item>
                <flux:navlist.item icon="tag" href="#" wire:navigate>Categories</flux:navlist.item>
                <flux:navlist.item icon="calendar-days" href="#" wire:navigate>Menus</flux:navlist.item>
                <flux:navlist.item icon="users" href="#" wire:navigate>Users</flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>
    @endif
@endauth
