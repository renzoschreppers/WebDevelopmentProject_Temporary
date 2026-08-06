<div class="flex flex-col gap-6">

    {{-- Toolbar --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <flux:input
            wire:model.live.debounce.300ms="search"
            icon="magnifying-glass"
            placeholder="Search categories"
            clearable
            class="sm:max-w-xs"
        />

        <flux:button variant="primary" icon="plus" wire:click="newCategory">
            New category
        </flux:button>
    </div>

    {{-- Table --}}
    @if ($categories->isEmpty())
        <x-cm.empty-state
            icon="tag"
            title="No categories found"
            description="Try a different search, or create your first category."
        />
    @else
        <div class="hidden overflow-x-auto rounded-lg border border-zinc-200 md:block dark:border-zinc-700">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-zinc-200 dark:border-zinc-700">
                <tr>
                    <th class="p-3">
                        <button wire:click="resort('sort_order')" class="font-semibold">Order</button>
                    </th>
                    <th class="p-3">
                        <button wire:click="resort('name')" class="font-semibold">Name</button>
                    </th>
                    <th class="hidden p-3 md:table-cell">Description</th>
                    <th class="p-3">Dishes</th>
                    <th class="p-3"></th>
                </tr>
                </thead>
                <tbody>
                @foreach ($categories as $category)
                    <tr wire:key="category-{{ $category->id }}" class="border-b border-zinc-100 last:border-0 dark:border-zinc-800">
                        <td class="p-3 tabular-nums">{{ $category->sort_order }}</td>
                        <td class="p-3 font-medium">{{ $category->name }}</td>
                        <td class="hidden p-3 text-zinc-500 md:table-cell">{{ $category->description }}</td>
                        <td class="p-3">
                            <button wire:click="showDishes({{ $category->id }})">
                                <flux:badge size="sm" class="hover:opacity-70">{{ $category->dishes_count }}</flux:badge>
                            </button>
                        </td>
                        <td class="p-3">
                            <div class="flex justify-end gap-1">
                                <flux:button
                                    size="sm"
                                    icon="pencil-square"
                                    wire:click="editCategory({{ $category->id }})"
                                    tooltip="Edit {{ $category->name }}"
                                />
                                <flux:button
                                    size="sm"
                                    variant="danger"
                                    icon="trash"
                                    wire:click="deleteConfirm({{ $category->id }})"
                                    tooltip="Delete {{ $category->name }}"
                                />
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile: cards --}}
        <div class="flex flex-col gap-3 md:hidden">
            @foreach ($categories as $category)
                <div wire:key="category-card-{{ $category->id }}" class="flex flex-col gap-2 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex flex-col">
                            <flux:heading size="sm">{{ $category->name }}</flux:heading>
                            @if ($category->description)
                                <flux:text size="sm" class="text-zinc-500">{{ $category->description }}</flux:text>
                            @endif
                        </div>

                        <flux:badge size="sm" class="shrink-0">#{{ $category->sort_order }}</flux:badge>
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <button wire:click="showDishes({{ $category->id }})">
                            <flux:badge size="sm">{{ $category->dishes_count }} dishes</flux:badge>
                        </button>

                        <div class="flex gap-1">
                            <flux:button size="sm" icon="pencil-square" wire:click="editCategory({{ $category->id }})" />
                            <flux:button size="sm" variant="danger" icon="trash" wire:click="deleteConfirm({{ $category->id }})" />
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{ $categories->links() }}
    @endif

    {{-- Create / edit modal --}}
    <flux:modal wire:model.self="showModal" class="w-full max-w-md">
        <div class="flex flex-col gap-4">
            <flux:heading size="lg">
                {{ $form->id ? 'Edit category' : 'New category' }}
            </flux:heading>

            <flux:input wire:model="form.name" label="Name" placeholder="e.g. Main Courses" />

            <flux:input wire:model="form.description" label="Description" placeholder="Optional" />

            <flux:input type="number" wire:model="form.sort_order" label="Sort order" min="0" max="255" />

            <div class="flex justify-end gap-2">
                <flux:button wire:click="$set('showModal', false)">Cancel</flux:button>
                <flux:button variant="primary" wire:click="save">Save</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Show dishes --}}
    <flux:modal wire:model.self="showDishesModal" class="w-full max-w-md">
        @if ($viewingCategory)
            <div class="flex flex-col gap-4">
                <flux:heading size="lg">Dishes in {{ $viewingCategory->name }}</flux:heading>

                @if ($viewingCategory->dishes->isEmpty())
                    <flux:text class="text-zinc-500">This category has no dishes yet.</flux:text>
                @else
                    <div class="flex max-h-80 flex-col divide-y divide-zinc-100 overflow-y-auto dark:divide-zinc-800">
                        @foreach ($viewingCategory->dishes as $dish)
                            <div wire:key="cat-dish-{{ $dish->id }}" class="flex items-baseline justify-between gap-4 py-2">
                                <flux:text>{{ $dish->name }}</flux:text>
                                <flux:text size="sm" class="shrink-0 tabular-nums text-zinc-500">
                                    {{ $dish->price_formatted }}
                                </flux:text>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="flex justify-end gap-2">
                    <flux:button wire:click="$set('showDishesModal', false)">Close</flux:button>
                    <flux:button variant="primary" :href="route('admin.dishes', ['category' => $viewingCategory->id])" wire:navigate>
                        View in dishes
                    </flux:button>
                </div>
            </div>
        @endif
    </flux:modal>
</div>
