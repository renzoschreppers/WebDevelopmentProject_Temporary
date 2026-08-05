@props(['tag', 'compact' => false])

<flux:badge size="sm" :color="$tag->color" :icon="$tag->icon">
    @unless ($compact) {{ $tag->name }} @endunless
</flux:badge>
