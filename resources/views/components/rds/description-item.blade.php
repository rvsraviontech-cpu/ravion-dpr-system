@props([
    'label',
])

<div class="grid grid-cols-1 gap-2 py-4 md:grid-cols-3">

    <dt class="text-sm font-medium text-gray-500">
        {{ $label }}
    </dt>

    <dd class="text-sm text-gray-900 md:col-span-2">
        {{ $slot }}
    </dd>

</div>