<div
    x-data="{ open: false }"
    class="relative inline-block text-left"
>
    <button
        type="button"
        @click="open = !open"
        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 transition hover:bg-gray-50"
    >
        ⋮
    </button>

    <div
        x-show="open"
        x-transition
        @click.outside="open = false"
        x-cloak
        class="absolute right-0 z-50 mt-2 w-52 rounded-lg border border-gray-200 bg-white shadow-lg"
    >
        <div class="py-1">
            {{ $slot }}
        </div>
    </div>
</div>