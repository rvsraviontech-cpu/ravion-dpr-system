@props([
    'method' => 'GET',
    'action' => url()->current(),
])

<form method="{{ $method }}" action="{{ $action }}"
    {{ $attributes->merge([
        'class' => 'mb-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm'
    ]) }}
>
    <div class="flex flex-wrap items-end gap-4">

        <div class="flex-1 min-w-[240px]">
            {{ $slot }}
        </div>

        @isset($actions)
            <div class="flex items-end gap-2">
                {{ $actions }}
            </div>
        @endisset

    </div>
</form>