<flux:navlist variant="outline">
    <flux:navlist.item icon="home" :href="route('home')" :current="request()->routeIs('home')" wire:navigate>
        Home
    </flux:navlist.item>

    <flux:navlist.item icon="calendar-days" :href="route('menu')" :current="request()->routeIs('menu')" wire:navigate>
        This week
    </flux:navlist.item>

    <flux:navlist.item icon="squares-2x2" :href="route('dishes')" :current="request()->routeIs('dishes')" wire:navigate>
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
                <flux:navlist.item icon="cake" :href="route('admin.dishes')" :current="request()->routeIs('admin.dishes')" wire:navigate>
                    Dishes
                </flux:navlist.item>
                <flux:navlist.item icon="tag" :href="route('admin.categories')" :current="request()->routeIs('admin.categories')" wire:navigate>
                    Categories
                </flux:navlist.item>
                <flux:navlist.item icon="sparkles" :href="route('admin.dietary-tags')" :current="request()->routeIs('admin.dietary-tags')" wire:navigate>
                    Dietary tags
                </flux:navlist.item>
                <flux:navlist.item icon="calendar-days" :href="route('admin.menus')" :current="request()->routeIs('admin.menus')" wire:navigate>
                    Menus
                </flux:navlist.item>
                <flux:navlist.item icon="users" href="#" wire:navigate>Users</flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>
    @endif
@endauth
