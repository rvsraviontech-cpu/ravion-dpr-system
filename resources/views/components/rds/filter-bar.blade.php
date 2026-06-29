@props([
    'method' => 'GET',
    'action' => url()->current(),
])

<form method="{{ $method }}" action="{{ $action }}"
    {{ $attributes->merge([
        'class' => 'mb-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm'
    ]) }}
>
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div class="grid flex-1 grid-cols-1 gap-4 md:grid-cols-4">
            {{ $slot }}
        </div>

        @isset($actions)
            <div class="flex items-center gap-2">
                {{ $actions }}
            </div>
        @endisset
    </div>
</form>