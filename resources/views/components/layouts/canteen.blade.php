<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>CanteenMenu{{ isset($title) ? ' — ' . $title : '' }}</title>
    <meta name="description" content="{{ $description ?? 'The daily menu of our canteen' }}">

    <x-layouts.canteen.favicons />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>
<body class="min-h-screen bg-white antialiased dark:bg-zinc-900 dark:text-white">

{{-- Sidebar --}}
<flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
    <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

    <a href="{{ route('home') }}" class="me-5 flex items-center space-x-2" wire:navigate>
        <x-app-logo />
    </a>

    <x-layouts.canteen.navbar />

    <flux:spacer />

    <x-layouts.canteen.profile />
</flux:sidebar>

{{-- Mobile header --}}
<flux:header class="lg:hidden">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
</flux:header>

{{-- Main --}}
<flux:main class="max-w-[1600px]">
    <div class="flex items-center justify-between gap-4">
        <flux:heading size="xl" level="1">{{ $title ?? 'Home' }}</flux:heading>
        <x-layouts.canteen.toggle_mode />
    </div>

    <flux:separator variant="subtle" class="my-6" />

    {{ $slot }}
</flux:main>

<x-layouts.canteen.info />
<x-itf.notifications />

@fluxScripts
@stack('scripts')
</body>
</html>
