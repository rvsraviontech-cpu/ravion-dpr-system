@props([
    'selected' => 0,
])

<div
    x-show="{{ $selected }} > 0"
    x-transition
    class="mb-4 flex items-center justify-between rounded-lg border border-blue-200 bg-blue-50 px-4 py-3"
>

    <div class="text-sm font-medium text-blue-700">
        {{ $selected }} record(s) selected
    </div>

    <div class="flex items-center gap-2">
        {{ $slot }}
    </div>

</div>