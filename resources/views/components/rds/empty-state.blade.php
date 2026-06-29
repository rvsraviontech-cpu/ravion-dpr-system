@props([
    'title' => 'No records found',
    'message' => 'There is no data available to display.',
    'icon' => null,
])

<div {{ $attributes->merge([
    'class' => 'rounded-xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center'
]) }}>
    @if($icon)
        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-500">
            {!! $icon !!}
        </div>
    @endif

    <h3 class="text-sm font-semibold text-gray-900">
        {{ $title }}
    </h3>

    <p class="mt-2 text-sm text-gray-500">
        {{ $message }}
    </p>

    @isset($action)
        <div class="mt-6">
            {{ $action }}
        </div>
    @endisset
</div>