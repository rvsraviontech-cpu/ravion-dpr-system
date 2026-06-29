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

    <div class="grid gap-6 lg:grid-cols-4">

        @isset($sidebar)
            <div class="lg:col-span-1">
                {{ $sidebar }}
            </div>
        @endisset

        <div class="lg:col-span-3">
            <x-rds.card>
                {{ $slot }}
            </x-rds.card>
        </div>

    </div>

</x-rds.page.base>