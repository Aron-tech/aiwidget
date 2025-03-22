<!-- resources/views/components/notification.blade.php -->
@props(['notifications' => []])
@php
    $notifications = [
        'success' => session('success'),
        'warning' => session('warning'),
        'danger' => session('danger'),
        'info' => session('info'),
    ];

    $notifications = array_filter($notifications, function ($message) {
        return is_string($message) && !empty($message);
    });
@endphp
@foreach ($notifications as $type => $message)
    <div wire:key="notification-{{ $type }}" class="fixed top-3 sm:top-5 w-2/3 sm:left-1/2 transform sm:-translate-x-1/2 sm:w-1/4 z-50">
        <flux:callout
            :icon="
                $type === 'success' ? 'check-circle' :
                ($type === 'warning' ? 'exclamation-circle' :
                ($type === 'danger' ? 'exclamation-triangle' :
                ($type === 'info' ? 'information-circle' : '')))
            "
            :variant="$type"
            inline
            x-data="{ visible: true }"
            x-show="visible">
            <flux:callout.heading class="flex gap-2 @max-md:flex-col items-start">
                {{ $message }}
            </flux:callout.heading>
            <x-slot name="controls">
                <flux:button icon="x-mark" variant="ghost" x-on:click="visible = false" />
            </x-slot>
        </flux:callout>
    </div>
@endforeach