<div {{ $attributes->merge([
    'class' => 'mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between'
]) }}>

    {{-- Left Section (Search / Filters) --}}
    <div class="flex-1">
        {{ $slot }}
    </div>

    {{-- Right Section (Buttons) --}}
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endisset

</div>