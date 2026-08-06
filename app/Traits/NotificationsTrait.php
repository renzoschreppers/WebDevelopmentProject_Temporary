<?php

namespace App\Traits;

use Arr;
use Illuminate\Support\Js;

trait NotificationsTrait
{
    /**
     * Dispatch a toast notification (Livewire 3).
     *
     * @param string $variant The style variant ('default', 'info', 'success', 'warning', 'danger').
     * @param string $text The main message text for the toast.
     * @param array $options Additional options: heading, duration, position, icon.
     * @return void
     */
    protected function showToast(string $variant, string $text, array $options = []): void
    {
        $showIcon = Arr::get($options, 'icon');
        if (!is_bool($showIcon)) {
            $showIcon = in_array($variant, ['info', 'success', 'warning', 'danger']);
        }

        $payload = [ // Alpine component expects an array containing one object
            'text' => $text,
            'variant' => $variant,
            'heading' => Arr::get($options, 'heading'),
            'duration' => Arr::get($options, 'duration', 4000), // Default 4s
            'position' => Arr::get($options, 'position'), // Alpine component handles null/default
            'icon' => $showIcon,
        ];

        // Livewire 3 dispatch to browser event listener
        $this->dispatch('toast-show', $payload);
    }

    /** Show default toast. */
    public function toast(string $text, array $options = []): void
    {
        $this->showToast('default', $text, $options);
    }

    /** Show info toast. */
    public function toastInfo(string $text, array $options = []): void
    {
        $this->showToast('info', $text, $options);
    }

    /** Show success toast. */
    public function toastSuccess(string $text, array $options = []): void
    {
        $this->showToast('success', $text, $options);
    }

    /** Show warning toast. */
    public function toastWarning(string $text, array $options = []): void
    {
        // Default duration for warning might be longer
        $options['duration'] = Arr::get($options, 'duration', 5000);
        $this->showToast('warning', $text, $options);
    }

    /** Show danger/error toast. */
    public function toastDanger(string $text, array $options = []): void
    {
        // Default duration for danger might be longer
        $options['duration'] = Arr::get($options, 'duration', 6000);
        $this->showToast('danger', $text, $options);
    }

    /** Alias for danger toast. */
    public function toastError(string $text, array $options = []): void
    {
        $this->toastDanger($text, $options);
    }


    /**
     * Dispatch a confirmation modal dialog (Livewire 3).
     * Dispatches a Livewire event on confirm, suitable for Route Model Binding listeners.
     *
     * @param string $text The confirmation question/text.
     * @param array $options Configuration: heading, confirmText, cancelText, class, next => [onEvent, params...].
     *        'next' => [
     *            'onEvent' => (string) Name of the Livewire event to dispatch.
     *            'paramName1' => value1, // For Route Model Binding, use lowercase model name as key (e.g., 'genre' => $genre->id)
     *            'paramName2' => value2, // Additional parameters
     *            // ...
     *        ]
     * @return void
     */
    public function confirm(string $text, array $options = []): void
    {
        $nextAction = Arr::get($options, 'next', []);
        $onEvent = Arr::get($nextAction, 'onEvent');

        if (!$onEvent) {
            trigger_error('Confirmation modal requires a "next.onEvent" option.', E_USER_WARNING);
            return; // Stop if no event name provided
        }

        // Extract parameters for the event, excluding 'onEvent' itself
        $eventParams = Arr::except($nextAction, ['onEvent']);

        // Prepare the JavaScript parameters object using Js::from
        // Ensures proper encoding of different data types for JS
        $jsEventParams = Js::from($eventParams);

        // Construct the JavaScript string to dispatch the JavaScript event using $dispatch
        // $dispatch('event-name', { key1: value1, key2: value2 })
        // Note: $dispatch needs escaping in the PHP string to become literal $wire in JS

        $nextEventJs = "\$dispatch('{$onEvent}', {$jsEventParams})";

        // Prepare the payload for the Alpine confirm component
        $payload = [ // Alpine component expects an array containing one object
            'text' => $text,
            'heading' => Arr::get($options, 'heading', 'Are you sure?'), // Default heading
            'confirmText' => Arr::get($options, 'confirmText', 'Confirm'),
            'cancelText' => Arr::get($options, 'cancelText', 'Cancel'),
            'nextEvent' => $nextEventJs, // JS string: $wire.dispatch(...)
            'extraClasses' => Arr::get($options, 'class'), // Map 'class' option to 'extraClasses' for Alpine
        ];

        // Livewire 3 dispatch to browser event listener
        $this->dispatch('confirm-show', $payload);
    }
}
