@props([
    'title' => 'Dashboard',
    'description' => null,
])

<x-rds.page.base
    :title="$title"
    :description="$description"
>

    {{ $slot }}

</x-rds.page.base>