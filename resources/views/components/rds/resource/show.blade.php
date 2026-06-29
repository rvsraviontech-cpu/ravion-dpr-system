@props([
    'title',
    'description' => null,
    'breadcrumbs' => [],
])

<x-rds.page.show
    :title="$title"
    :description="$description"
    :breadcrumbs="$breadcrumbs"
>
    @isset($actions)
        <x-slot name="actions">
            {{ $actions }}
        </x-slot>
    @endisset

    {{ $slot }}

</x-rds.page.show>