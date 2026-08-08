@props(['errors' => null])

@if ($errors && $errors->any())
    <flux:callout variant="danger" icon="exclamation-triangle">
        <flux:callout.heading>Please fix the following</flux:callout.heading>
        <flux:callout.text>
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </flux:callout.text>
    </flux:callout>
@endif
