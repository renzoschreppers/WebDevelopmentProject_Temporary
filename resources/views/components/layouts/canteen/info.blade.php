@env('local')
    <div class="fixed bottom-2 end-2 z-50 rounded-md bg-zinc-900/80 px-3 py-2 text-xs text-white backdrop-blur">
        <div>Route: {{ request()->route()?->getName() ?? 'n/a' }}</div>
        <div>User: {{ auth()->user()?->name ?? 'guest' }}{{ auth()->user()?->admin ? ' (admin)' : '' }}</div>
    </div>
@endenv
