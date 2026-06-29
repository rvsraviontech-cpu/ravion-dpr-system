@props([
    'title',
    'description' => null,
    'breadcrumbs' => [],
])

<div {{ $attributes->merge(['class' => 'space-y-6']) }}>

    @if(!empty($breadcrumbs))
        <x-rds.breadcrumb :items="$breadcrumbs" />
    @endif

    <x-rds.page-header
        :title="$title"
        :description="$description"
    >
        @isset($actions)
            <x-slot name="actions">
                {{ $actions }}
            </x-slot>
        @endisset
    </x-rds.page-header>

    {{ $slot }}

</div>