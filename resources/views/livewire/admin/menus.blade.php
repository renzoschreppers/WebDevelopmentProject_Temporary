<div class="flex flex-col gap-6">

    {{-- Toolbar --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2">
            <flux:select wire:model.live="status" class="max-w-44">
                <flux:select.option value="all">All menus</flux:select.option>
                <flux:select.option value="upcoming">Upcoming</flux:select.option>
                <flux:select.option value="published">Published</flux:select.option>
                <flux:select.option value="draft">Drafts</flux:select.option>
            </flux:select>

            <flux:button
                :icon="$sortDirection === 'asc' ? 'bars-arrow-up' : 'bars-arrow-down'"
                wire:click="toggleSort"
                tooltip="Sort by date"
            />
        </div>

        <flux:button variant="primary" icon="plus" wire:click="newMenu">
            New menu
        </flux:button>
    </div>

    @if ($menus->isEmpty())
        <x-cm.empty-state
            icon="calendar-days"
            title="No menus found"
            description="Create a menu for a date and start adding dishes."
        />
    @else
        {{-- Desktop table --}}
        <div class="hidden overflow-x-auto rounded-lg border border-zinc-200 md:block dark:border-zinc-700">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-zinc-200 dark:border-zinc-700">
                <tr>
                    <th class="p-3 font-semibold">Date</th>
                    <th class="p-3 font-semibold">Note</th>
                    <th class="p-3 font-semibold">Dishes</th>
                    <th class="p-3 font-semibold">Published</th>
                    <th class="p-3"></th>
                </tr>
                </thead>
                <tbody>
                @foreach ($menus as $menu)
                    <tr wire:key="menu-{{ $menu->id }}" @class([
                            'border-b border-zinc-100 last:border-0 dark:border-zinc-800',
                            'bg-zinc-50 dark:bg-zinc-800/40' => $menu->date->isToday(),
                        ])>
                        <td class="p-3">
                            <div class="flex items-center gap-2">
                                <flux:text class="font-medium">{{ $menu->date->isoFormat('ddd D MMM YYYY') }}</flux:text>
                                @if ($menu->date->isToday())
                                    <flux:badge size="sm" color="lime">Today</flux:badge>
                                @endif

                                @unless ($menu->is_published)
                                    <flux:badge size="sm" color="zinc" icon="eye-slash">Draft</flux:badge>
                                @endunless
                            </div>
                        </td>
                        <td class="p-3 text-zinc-500">{{ $menu->note }}</td>
                        <td class="p-3">
                            <flux:badge size="sm" :color="$menu->dishes_count === 0 ? 'amber' : 'zinc'">
                                {{ $menu->dishes_count }}
                            </flux:badge>
                        </td>
                        <td class="p-3">
                            <flux:switch
                                :checked="$menu->is_published"
                                wire:click="togglePublished({{ $menu->id }})"
                            />
                        </td>
                        <td class="p-3">
                            <div class="flex justify-end gap-1">
                                <flux:button
                                    size="sm"
                                    icon="squares-plus"
                                    :href="route('admin.menus.edit', $menu)"
                                    wire:navigate
                                    tooltip="Plan dishes"
                                />
                                <flux:button size="sm" icon="pencil-square" wire:click="editMenu({{ $menu->id }})" tooltip="Edit date and note" />
                                <flux:button size="sm" variant="danger" icon="trash" wire:click="deleteConfirm({{ $menu->id }})" tooltip="Delete menu" />
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="flex flex-col gap-3 md:hidden">
            @foreach ($menus as $menu)
                <div wire:key="menu-card-{{ $menu->id }}" class="flex flex-col gap-2 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-2">
                                <flux:heading size="sm">{{ $menu->date->isoFormat('ddd D MMM YYYY') }}</flux:heading>
                                @unless ($menu->is_published)
                                    <flux:badge size="sm" color="zinc" icon="eye-slash">Draft</flux:badge>
                                @endunless
                            </div>
                            @if ($menu->note)
                                <flux:text size="sm" class="text-zinc-500">{{ $menu->note }}</flux:text>
                            @endif
                        </div>

                        <flux:badge size="sm" :color="$menu->dishes_count === 0 ? 'amber' : 'zinc'" class="shrink-0">
                            {{ $menu->dishes_count }} dishes
                        </flux:badge>
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <flux:switch
                            :checked="$menu->is_published"
                            wire:click="togglePublished({{ $menu->id }})"
                            label="Published"
                        />

                        <div class="flex gap-1">
                            <flux:button size="sm" icon="squares-plus" :href="route('admin.menus.edit', $menu)" wire:navigate />
                            <flux:button size="sm" icon="pencil-square" wire:click="editMenu({{ $menu->id }})" />
                            <flux:button size="sm" variant="danger" icon="trash" wire:click="deleteConfirm({{ $menu->id }})" />
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{ $menus->links() }}
    @endif

    {{-- Create / edit --}}
    <flux:modal wire:model.self="showModal" class="w-full max-w-md">
        <div class="errors-in-summary flex flex-col gap-4">
            <flux:heading size="lg">{{ $form->id ? 'Edit menu' : 'New menu' }}</flux:heading>

            <x-cm.error-summary :errors="$errors" />

            <flux:input type="date" wire:model="form.date" label="Date" />

            <flux:input wire:model="form.note" label="Note" placeholder="e.g. Friday special" />

            <flux:switch wire:model="form.is_published" label="Published" />

            <div class="flex justify-end gap-2">
                <flux:button wire:click="$set('showModal', false)">Cancel</flux:button>
                <flux:button variant="primary" wire:click="save">Save</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
