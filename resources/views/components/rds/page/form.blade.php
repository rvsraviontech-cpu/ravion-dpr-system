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

    <x-rds.card>

        {{ $slot }}

        @isset($footer)
            <div class="mt-6 flex justify-end gap-3 border-t border-gray-200 pt-4">
                {{ $footer }}
            </div>
        @endisset

    </x-rds.card>

</x-rds.page.base>