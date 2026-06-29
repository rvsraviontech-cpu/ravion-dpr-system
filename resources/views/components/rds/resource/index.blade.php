@props([
    'title',
    'description' => null,
    'breadcrumbs' => [],
    'paginator' => null,
])

<x-rds.page.index
    :title="$title"
    :description="$description"
    :breadcrumbs="$breadcrumbs"
>

    @isset($actions)
        <x-slot name="actions">
            {{ $actions }}
        </x-slot>
    @endisset

    @isset($toolbar)
        {{ $toolbar }}
    @endisset

    <x-rds.card>

        {{ $slot }}

        @if($paginator)
            <x-rds.pagination :paginator="$paginator" />
        @endif

    </x-rds.card>

</x-rds.page.index>