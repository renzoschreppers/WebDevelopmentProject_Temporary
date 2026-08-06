@props([
    'toastPosition' => 'top-right', // Default position for toasts if not specified in the event data
])

<div>
    {{-- Toast Container --}}
    <div
        x-data="toast()"
        x-init="init('{{ $toastPosition }}')"
        x-on:toast-show.window="show($event.detail)" {{-- Listen for Livewire 3 dispatch --}}
        class="fixed z-[5000] pointer-events-none inset-0 flex flex-col"
        aria-live="assertive"
    >
        <template x-for="(group, position) in groupedToasts" :key="position">
            <div class="fixed z-[5000]"
                 :class="{
                     'top-4 right-4': position === 'top-right',
                     'top-4 left-4': position === 'top-left',
                     'bottom-4 right-4': position === 'bottom-right',
                     'bottom-4 left-4': position === 'bottom-left',
                     'top-4 inset-x-4 flex justify-center': position === 'top',
                     'bottom-4 inset-x-4 flex justify-center': position === 'bottom'
                 }">
                <template x-for="(toast, index) in group" :key="index">
                    <div
                        x-show="toast.visible"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="pointer-events-auto mb-2 w-80 max-w-full transform rounded-lg shadow-lg overflow-hidden mx-4"
                        :class="{
                            'bg-zinc-50 dark:bg-zinc-700/50 border border-zinc-200 dark:border-zinc-900': toast.variant === 'default',
                            'bg-sky-50 dark:bg-sky-700/50 border border-sky-200 dark:border-sky-900': toast.variant === 'info',
                            'bg-green-50 dark:bg-green-700/50 border border-green-200 dark:border-green-900': toast.variant === 'success',
                            'bg-yellow-50 dark:bg-yellow-700/50 border border-yellow-200 dark:border-yellow-900': toast.variant === 'warning',
                            'bg-red-50 dark:bg-red-700/50 border border-red-200 dark:border-red-900': toast.variant === 'danger'
                        }"
                    >
                        <!-- Toast Content -->
                        <div class="p-4">
                            <div class="flex items-start">
                                <template x-if="toast.icon">
                                    <div class="flex-shrink-0 mr-3">
                                        {{-- Assuming flux:icons components exist --}}
                                        <template x-if="toast.variant === 'success'">
                                            <flux:icon.check-circle class="text-2xl text-green-700 dark:text-green-200"/>
                                        </template>
                                        <template x-if="toast.variant === 'danger'">
                                            <flux:icon.exclamation-circle class="text-2xl text-red-700 dark:text-red-200"/>
                                        </template>
                                        <template x-if="toast.variant === 'warning'">
                                            <flux:icon.exclamation-triangle class="text-2xl text-yellow-700 dark:text-yellow-200"/>
                                        </template>
                                        <template x-if="toast.variant === 'info'">
                                            <flux:icon.information-circle class="text-2xl text-sky-700 dark:text-sky-200"/>
                                        </template>
                                        <template x-if="toast.variant === 'default'">
                                            <flux:icon.bell class="text-2xl text-zinc-500 dark:text-zinc-400"/>
                                        </template>
                                    </div>
                                </template>
                                <div class="flex-1 pt-0.5">
                                    <template x-if="toast.heading">
                                        <h3
                                            class="text-sm font-medium mb-1"
                                            :class="{
                                                'text-zinc-800 dark:text-white': toast.variant === 'default',
                                                'text-sky-800 dark:text-sky-200': toast.variant === 'info',
                                                'text-green-800 dark:text-green-200': toast.variant === 'success',
                                                'text-yellow-800 dark:text-yellow-200': toast.variant === 'warning',
                                                'text-red-800 dark:text-red-200': toast.variant === 'danger',
                                            }"
                                            x-text="toast.heading"
                                        ></h3>
                                    </template>
                                    <div
                                        class="text-sm"
                                        :class="{
                                            'text-zinc-700 dark:text-zinc-300': toast.variant === 'default',
                                            'text-sky-700 dark:text-sky-300': toast.variant === 'info',
                                            'text-green-700 dark:text-green-300': toast.variant === 'success',
                                            'text-yellow-700 dark:text-yellow-300': toast.variant === 'warning',
                                            'text-red-700 dark:text-red-300': toast.variant === 'danger'
                                        }"
                                        x-html="toast.text"
                                    ></div>
                                </div>
                                <div class="ml-3 flex-shrink-0">
                                    <button
                                        type="button"
                                        class="inline-flex rounded-md focus:outline-none"
                                        :class="{
                                            'bg-zinc-50 text-zinc-700 hover:text-zinc-800 dark:bg-zinc-800/50 dark:text-zinc-500 dark:hover:text-zinc-400': toast.variant === 'default',
                                            'bg-sky-50 text-sky-700 hover:text-sky-800 dark:bg-sky-900/50 dark:text-sky-500 dark:hover:text-sky-400': toast.variant === 'info',
                                            'bg-green-50 text-green-700 hover:text-green-800 dark:bg-green-900/50 dark:text-green-500 dark:hover:text-green-400': toast.variant === 'success',
                                            'bg-yellow-50 text-yellow-700 hover:text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-500 dark:hover:text-yellow-400': toast.variant === 'warning',
                                            'bg-red-50 text-red-700 hover:text-red-800 dark:bg-red-900/50 dark:text-red-500 dark:hover:text-red-400': toast.variant === 'danger'
                                        }"
                                        @click="remove(position, index)"
                                    >
                                        <span class="sr-only">Close</span>
                                        {{-- Assuming flux:icons component exists --}}
                                        <flux:icon.x-mark class="h-5 w-5"/>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </div>
    {{-- /Toast Container --}}


    {{-- Confirm Container --}}
    <div
        style="display: none;"
        x-data="{ open: false, params: [{}] }"
        @keydown.escape.window="open = false"
        @confirm-show.window="open = true; params = $event.detail" {{-- Listen for Livewire 3 dispatch --}}
        role="dialog"
        aria-modal="true"
        x-show="open"
        class="confirm-container"
        {{-- Add aria-labelledby and aria-describedby if needed based on heading/text --}}
    >
        {{-- Overlay --}}
        <div x-show="open"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[6000] bg-zinc-500/50 backdrop-blur-xs"></div>

        {{-- Modal Panel --}}
        <div class="fixed inset-0 z-[6000] overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <div
                    x-show="open"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    @click.away="open = false"
                    class="relative transform overflow-hidden rounded-lg bg-white dark:bg-zinc-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg p-6"
                    :class="params[0].extraClasses">

                    {{-- Modal Content --}}
                    <h3 x-show="params[0].heading" x-text="params[0].heading"
                        class="text-lg font-semibold leading-6 text-zinc-900 dark:text-white mb-2" id="modal-title"></h3>
                    <div class="mt-2">
                        <p x-html="params[0].text" class="text-sm text-zinc-600 dark:text-zinc-300" id="modal-description"></p>
                    </div>

                    {{-- Modal Footer/Actions --}}
                    <div class="mt-5 sm:mt-6 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 space-y-2 sm:space-y-0">
                        {{-- Assuming flux:button component exists --}}
                        <flux:button type="button" @click.stop="open = false">
                            <span x-text="params[0].cancelText"></span>
                        </flux:button>
                        <flux:button type="button" variant="primary" @click.stop="eval(params[0].nextEvent); open = false">
                            <span x-text="params[0].confirmText"></span>
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- /Confirm Container --}}
</div>
