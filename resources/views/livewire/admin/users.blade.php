<div class="flex flex-col gap-6">

    {{-- Toolbar --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="Search name or email"
                clearable
                class="sm:max-w-xs"
            />

            <flux:select wire:model.live="role" class="sm:max-w-40">
                <flux:select.option value="all">All users</flux:select.option>
                <flux:select.option value="admins">Administrators</flux:select.option>
                <flux:select.option value="users">Regular users</flux:select.option>
                <flux:select.option value="inactive">Inactive</flux:select.option>
            </flux:select>

            @if ($search || $role !== 'all')
                <flux:button variant="subtle" icon="x-mark" wire:click="clearFilters">Clear</flux:button>
            @endif
        </div>

        <flux:button variant="primary" icon="plus" wire:click="newUser">New user</flux:button>
    </div>

    @if ($users->isEmpty())
        <x-cm.empty-state
            icon="users"
            title="No users found"
            description="Try a different search or filter."
        />
    @else
        {{-- Desktop table --}}
        <div class="hidden overflow-x-auto rounded-lg border border-zinc-200 md:block dark:border-zinc-700">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-zinc-200 dark:border-zinc-700">
                <tr>
                    <th class="p-3"><button wire:click="resort('name')" class="font-semibold">Name</button></th>
                    <th class="p-3"><button wire:click="resort('email')" class="font-semibold">Email</button></th>
                    <th class="hidden p-3 lg:table-cell font-semibold">Favourites</th>
                    <th class="p-3 font-semibold">Admin</th>
                    <th class="p-3 font-semibold">Active</th>
                    <th class="p-3"></th>
                </tr>
                </thead>
                <tbody>
                @foreach ($users as $user)
                    <tr wire:key="user-{{ $user->id }}" @class([
                            'border-b border-zinc-100 last:border-0 dark:border-zinc-800',
                            'opacity-60' => ! $user->active,
                        ])>
                        <td class="p-3">
                            <div class="flex items-center gap-2">
                                <flux:text class="font-medium">{{ $user->name }}</flux:text>
                                @if ($user->id === auth()->id())
                                    <flux:badge size="sm" color="sky">You</flux:badge>
                                @endif
                            </div>
                        </td>
                        <td class="p-3 text-zinc-500">{{ $user->email }}</td>
                        <td class="hidden p-3 lg:table-cell">
                            <flux:badge size="sm">{{ $user->favorites_count }}</flux:badge>
                        </td>
                        <td class="p-3">
                            <flux:switch
                                :checked="$user->admin"
                                :disabled="$user->id === auth()->id()"
                                wire:click="toggleAdmin({{ $user->id }})"
                            />
                        </td>
                        <td class="p-3">
                            <flux:switch
                                :checked="$user->active"
                                :disabled="$user->id === auth()->id()"
                                wire:click="toggleActive({{ $user->id }})"
                            />
                        </td>
                        <td class="p-3">
                            <div class="flex justify-end gap-1">
                                <flux:button size="sm" icon="pencil-square" wire:click="editUser({{ $user->id }})" tooltip="Edit {{ $user->name }}" />
                                <flux:button
                                    size="sm"
                                    variant="danger"
                                    icon="trash"
                                    wire:click="deleteConfirm({{ $user->id }})"
                                    :disabled="$user->id === auth()->id()"
                                    tooltip="Delete {{ $user->name }}"
                                />
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="flex flex-col gap-3 md:hidden">
            @foreach ($users as $user)
                <div wire:key="user-card-{{ $user->id }}" @class([
                    'flex flex-col gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700',
                    'opacity-60' => ! $user->active,
                ])>
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex flex-col">
                            <div class="flex items-center gap-2">
                                <flux:heading size="sm">{{ $user->name }}</flux:heading>
                                @if ($user->id === auth()->id())
                                    <flux:badge size="sm" color="sky">You</flux:badge>
                                @endif
                            </div>
                            <flux:text size="sm" class="text-zinc-500">{{ $user->email }}</flux:text>
                        </div>

                        <flux:badge size="sm" class="shrink-0">{{ $user->favorites_count }} ♥</flux:badge>
                    </div>

                    <div class="flex flex-wrap items-center gap-4">
                        <flux:switch
                            :checked="$user->admin"
                            :disabled="$user->id === auth()->id()"
                            wire:click="toggleAdmin({{ $user->id }})"
                            label="Admin"
                        />
                        <flux:switch
                            :checked="$user->active"
                            :disabled="$user->id === auth()->id()"
                            wire:click="toggleActive({{ $user->id }})"
                            label="Active"
                        />
                    </div>

                    <div class="flex justify-end gap-1">
                        <flux:button size="sm" icon="pencil-square" wire:click="editUser({{ $user->id }})" />
                        <flux:button
                            size="sm"
                            variant="danger"
                            icon="trash"
                            wire:click="deleteConfirm({{ $user->id }})"
                            :disabled="$user->id === auth()->id()"
                        />
                    </div>
                </div>
            @endforeach
        </div>

        {{ $users->links() }}
    @endif

    {{-- Create / edit --}}
    <flux:modal wire:model.self="showModal" class="errors-in-summary w-full max-w-md">
        <div class="flex flex-col gap-4">
            <flux:heading size="lg">{{ $form->id ? 'Edit user' : 'New user' }}</flux:heading>

            <x-cm.error-summary :errors="$errors" />

            <flux:input wire:model="form.name" label="Name" placeholder="e.g. Anna Peeters" />

            <flux:input type="email" wire:model="form.email" label="Email" placeholder="name@canteen.test" />

            <flux:input
                type="password"
                wire:model="form.password"
                label="Password"
                :placeholder="$form->id ? 'Leave blank to keep the current password' : 'At least 8 characters'"
                viewable
            />

            <div class="flex flex-wrap items-center gap-6">
                <flux:switch wire:model="form.admin" label="Administrator" />
                <flux:switch wire:model="form.active" label="Active" />
            </div>

            <div class="flex justify-end gap-2">
                <flux:button wire:click="$set('showModal', false)">Cancel</flux:button>
                <flux:button variant="primary" wire:click="save">Save</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
