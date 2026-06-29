<button
    type="button"
    {{ $attributes->merge([
        'class' => 'hidden md:flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-500 shadow-sm hover:bg-gray-50'
    ]) }}
>
    <span>Search...</span>

    <span class="ml-8 rounded border border-gray-200 bg-gray-50 px-1.5 py-0.5 text-xs text-gray-400">
        Ctrl K
    </span>
</button>