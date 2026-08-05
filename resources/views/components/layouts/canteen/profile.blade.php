@guest
    <flux:navlist variant="outline">
        <flux:navlist.item icon="arrow-right-end-on-rectangle" :href="route('login')" wire:navigate>
            Log in
        </flux:navlist.item>
        <flux:navlist.item icon="user-plus" :href="route('register')" wire:navigate>
            Register
        </flux:navlist.item>
    </flux:navlist>
@endguest

@auth
    <flux:dropdown position="top" align="start">
        <flux:profile
            :name="auth()->user()->name"
            :initials="auth()->user()->initials()"
            icon:trailing="chevrons-up-down"
        />

        <flux:menu class="w-[220px]">
            <div class="px-2 py-1.5">
                <div class="text-sm font-semibold">{{ auth()->user()->name }}</div>
                <div class="text-xs opacity-70">{{ auth()->user()->email }}</div>
            </div>

            <flux:menu.separator />

            <flux:menu.item icon="cog-6-tooth" :href="route('settings.profile')" wire:navigate>
                Settings
            </flux:menu.item>

            <flux:menu.separator />

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                    Log out
                </flux:menu.item>
            </form>
        </flux:menu>
    </flux:dropdown>
@endauth
