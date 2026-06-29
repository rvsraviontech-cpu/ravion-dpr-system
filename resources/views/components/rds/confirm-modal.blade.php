@props([
    'id',
    'title' => 'Confirm Action',
    'message' => 'Are you sure you want to continue?',
    'confirmText' => 'Confirm',
    'cancelText' => 'Cancel',
    'confirmVariant' => 'danger',
])

<div
    x-data="{ open: false }"
    x-on:open-confirm-modal.window="if ($event.detail === '{{ $id }}') open = true"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
>
    <div
        x-show="open"
        x-transition
        @click.outside="open = false"
        class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl"
    >
        <h2 class="text-lg font-semibold text-gray-900">
            {{ $title }}
        </h2>

        <p class="mt-2 text-sm text-gray-600">
            {{ $message }}
        </p>

        <div class="mt-6 flex justify-end gap-3">
            <x-rds.button
                type="button"
                variant="secondary"
                @click="open = false"
            >
                {{ $cancelText }}
            </x-rds.button>

            {{ $slot }}
        </div>
    </div>
</div>