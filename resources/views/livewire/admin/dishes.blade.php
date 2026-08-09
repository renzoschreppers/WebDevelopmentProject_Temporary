@use('Illuminate\Support\Facades\Storage')

<div class="flex flex-col gap-6">

    {{-- Toolbar --}}
    <div class="flex flex-col gap-3">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <flux:input
                wire:model.live.debounce.500ms="search"
                icon="magnifying-glass"
                placeholder="Search dishes"
                clearable
                class="sm:max-w-xs"
            />

            <flux:button variant="primary" icon="plus" wire:click="newDish">
                New dish
            </flux:button>
        </div>

        {{-- Filters --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
            <flux:select wire:model.live="category" class="sm:max-w-48">
                <flux:select.option value="">All categories</flux:select.option>
                @foreach ($categories as $categoryOption)
                    <flux:select.option value="{{ $categoryOption->id }}">{{ $categoryOption->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="availability" class="sm:max-w-40">
                <flux:select.option value="all">All dishes</flux:select.option>
                <flux:select.option value="available">Available</flux:select.option>
                <flux:select.option value="unavailable">Unavailable</flux:select.option>
            </flux:select>

            <flux:dropdown>
                <flux:button icon="funnel" icon:trailing="chevron-down">
                    Tags @if ($tags) ({{ count($tags) }}) @endif
                </flux:button>

                <flux:menu>
                    <flux:menu.checkbox.group wire:model.live="tags">
                        @foreach ($allTags as $tag)
                            <flux:menu.checkbox value="{{ $tag->id }}">{{ $tag->name }}</flux:menu.checkbox>
                        @endforeach
                    </flux:menu.checkbox.group>
                </flux:menu>
            </flux:dropdown>

            @if ($search || $category || $tags || $availability !== 'all')
                <flux:button variant="subtle" icon="x-mark" wire:click="clearFilters">
                    Clear filters
                </flux:button>
            @endif
        </div>
    </div>

    @if ($dishes->isEmpty())
        <x-cm.empty-state
            icon="cake"
            title="No dishes found"
            description="Try adjusting your filters, or create a new dish."
        />
    @else
        {{-- Desktop table --}}
        <div class="hidden overflow-x-auto rounded-lg border border-zinc-200 md:block dark:border-zinc-700">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-zinc-200 dark:border-zinc-700">
                <tr>
                    <th class="p-3 w-16"></th>
                    <th class="p-3"><button wire:click="resort('name')" class="font-semibold">Dish</button></th>
                    <th class="p-3"><button wire:click="resort('category')" class="font-semibold">Category</button></th>
                    <th class="hidden p-3 lg:table-cell font-semibold">Tags</th>
                    <th class="p-3"><button wire:click="resort('price')" class="font-semibold">Price</button></th>
                    <th class="p-3 font-semibold">Available</th>
                    <th class="p-3"></th>
                </tr>
                </thead>
                <tbody>
                @foreach ($dishes as $dish)
                    <tr wire:key="dish-{{ $dish->id }}" @class([
                            'border-b border-zinc-100 last:border-0 dark:border-zinc-800',
                            'opacity-60' => ! $dish->is_available,
                        ])>
                        <td class="p-3">
                            <div class="size-10 overflow-hidden rounded-md border border-zinc-200 dark:border-zinc-700">
                                @if ($dish->image_url)
                                    <flux:tooltip>
                                        <img src="{{ $dish->image_url }}" alt="{{ $dish->name }}" class="size-full object-cover">
                                        <flux:tooltip.content class="p-1">
                                            <img src="{{ $dish->image_url }}" alt="" class="size-40 rounded object-cover">
                                        </flux:tooltip.content>
                                    </flux:tooltip>
                                @else
                                    <div class="flex size-full items-center justify-center">
                                        <flux:icon.photo class="size-4 text-zinc-300 dark:text-zinc-600" />
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="p-3">
                            <flux:text class="font-medium">{{ $dish->name }}</flux:text>
                        </td>
                        <td class="p-3 text-zinc-500">{{ $dish->category->name }}</td>
                        <td class="hidden p-3 lg:table-cell">
                            <div class="flex flex-wrap gap-1">
                                @foreach ($dish->dietaryTags as $tag)
                                    <x-cm.dietary-badge :tag="$tag" compact />
                                @endforeach
                            </div>
                        </td>
                        <td class="p-3 tabular-nums">{{ $dish->price_formatted }}</td>
                        <td class="p-3">
                            <flux:switch
                                :checked="$dish->is_available"
                                wire:click="toggleAvailability({{ $dish->id }})"
                            />
                        </td>
                        <td class="p-3">
                            <div class="flex justify-end gap-1">
                                <flux:button size="sm" icon="pencil-square" wire:click="editDish({{ $dish->id }})" tooltip="Edit {{ $dish->name }}" />
                                <flux:button size="sm" variant="danger" icon="trash" wire:click="deleteConfirm({{ $dish->id }})" tooltip="Delete {{ $dish->name }}" />
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="flex flex-col gap-3 md:hidden">
            @foreach ($dishes as $dish)
                <div wire:key="dish-card-{{ $dish->id }}" @class([
                    'flex flex-col gap-2 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700',
                    'opacity-60' => ! $dish->is_available,
                ])>
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            <div class="size-12 shrink-0 overflow-hidden rounded-md border border-zinc-200 dark:border-zinc-700">
                                @if ($dish->image_url)
                                    <img src="{{ $dish->image_url }}" alt="{{ $dish->name }}" class="size-full object-cover">
                                @else
                                    <div class="flex size-full items-center justify-center">
                                        <flux:icon.photo class="size-4 text-zinc-300 dark:text-zinc-600" />
                                    </div>
                                @endif
                            </div>

                            <div class="flex flex-col">
                                <flux:heading size="sm">{{ $dish->name }}</flux:heading>
                                <flux:text size="sm" class="text-zinc-500">{{ $dish->category->name }}</flux:text>
                            </div>
                        </div>

                        <flux:text class="shrink-0 tabular-nums">{{ $dish->price_formatted }}</flux:text>
                    </div>

                    @if ($dish->dietaryTags->isNotEmpty())
                        <div class="flex flex-wrap gap-1">
                            @foreach ($dish->dietaryTags as $tag)
                                <x-cm.dietary-badge :tag="$tag" compact />
                            @endforeach
                        </div>
                    @endif

                    <div class="flex items-center justify-between gap-2">
                        <flux:switch
                            :checked="$dish->is_available"
                            wire:click="toggleAvailability({{ $dish->id }})"
                            label="Available"
                        />

                        <div class="flex gap-1">
                            <flux:button size="sm" icon="pencil-square" wire:click="editDish({{ $dish->id }})" />
                            <flux:button size="sm" variant="danger" icon="trash" wire:click="deleteConfirm({{ $dish->id }})" />
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{ $dishes->links() }}
    @endif

    {{-- Create / edit --}}
    <flux:modal wire:model.self="showModal" class="w-full max-w-2xl">
        <div class="errors-in-summary flex flex-col gap-4">
            <flux:heading size="lg">{{ $form->id ? 'Edit dish' : 'New dish' }}</flux:heading>

            <x-cm.error-summary :errors="$errors" />

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:input wire:model="form.name" label="Name" placeholder="e.g. Tomato Soup" />

                <flux:select wire:model="form.category_id" label="Category">
                    <flux:select.option value="">Choose a category</flux:select.option>
                    @foreach ($categories as $categoryOption)
                        <flux:select.option value="{{ $categoryOption->id }}">{{ $categoryOption->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <flux:textarea wire:model="form.description" label="Description" rows="3" placeholder="Optional" />

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:input type="number" step="0.01" min="0" wire:model="form.price" label="Price (€)" />

                <div class="flex items-end">
                    <flux:switch wire:model="form.is_available" label="Available" />
                </div>
            </div>

            {{-- Image --}}
            <div class="flex flex-col gap-2">
                <flux:label>Image</flux:label>

                <div class="flex items-start gap-4">
                    {{-- Preview --}}
                    <div class="size-24 shrink-0 overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                        @if ($form->newImage)
                            <img src="{{ $form->newImage->temporaryUrl() }}" alt="Preview" class="size-full object-cover">
                        @elseif ($form->image)
                            <img src="{{ Storage::disk('public')->url($form->image) }}?v={{ time() }}" alt="Current image" class="size-full object-cover">
                        @else
                            <div class="flex size-full items-center justify-center">
                                <flux:icon.photo class="size-8 text-zinc-300 dark:text-zinc-600" />
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-1 flex-col gap-2">
                        <flux:input type="file" wire:model="form.newImage" accept="image/*" />

                        <flux:text size="sm" class="text-zinc-500">
                            JPG, PNG or WebP. Max 8 MB. Cropped to a square.
                        </flux:text>

                        <div wire:loading wire:target="form.newImage">
                            <flux:text size="sm" class="text-zinc-500">Uploading…</flux:text>
                        </div>

                        @if ($form->image)
                            <div>
                                <flux:button size="sm" variant="danger" icon="trash" wire:click="removeImage">
                                    Remove image
                                </flux:button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <flux:checkbox.group wire:model="form.tag_ids" label="Dietary tags" class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                @foreach ($allTags as $tag)
                    <flux:checkbox value="{{ $tag->id }}" label="{{ $tag->name }}" />
                @endforeach
            </flux:checkbox.group>

            <div class="flex justify-end gap-2">
                <flux:button wire:click="$set('showModal', false)">Cancel</flux:button>
                <flux:button variant="primary" wire:click="save">Save</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
