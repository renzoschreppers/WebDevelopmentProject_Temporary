<flux:dropdown position="bottom" align="end">
    <flux:button variant="subtle" square aria-label="Toggle theme">
        <flux:icon.moon variant="mini" class="hidden dark:block" />
        <flux:icon.sun variant="mini" class="dark:hidden" />
    </flux:button>

    <flux:menu>
        <flux:menu.radio.group x-data x-model="$flux.appearance">
            <flux:menu.radio value="light" icon="sun">Light</flux:menu.radio>
            <flux:menu.radio value="dark" icon="moon">Dark</flux:menu.radio>
            <flux:menu.radio value="system" icon="computer-desktop">System</flux:menu.radio>
        </flux:menu.radio.group>
    </flux:menu>
</flux:dropdown>
