@props([
    'title',
    'description' => null,
    'breadcrumbs' => [],
])

<x-rds.page.base
    :title="$title"
    :description="$description"
    :breadcrumbs="$breadcrumbs"
>

    @isset($actions)
        <x-slot name="actions">
            {{ $actions }}
        </x-slot>
    @endisset

    @isset($stats)
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {{ $stats }}
        </div>
    @endisset

    {{ $slot }}

</x-rds.page.base>