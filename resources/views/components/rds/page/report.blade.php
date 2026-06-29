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

    @isset($filters)
        {{ $filters }}
    @endisset

    <x-rds.card>
        {{ $slot }}
    </x-rds.card>

</x-rds.page.base>