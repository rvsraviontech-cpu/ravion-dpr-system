@props([
    'show' => null,
    'edit' => null,
    'toggle' => null,
    'active' => true,
])

<div x-data="{ open:false }" class="relative inline-block text-left">
    <button
        type="button"
        @click="open=!open"
        class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white hover:bg-gray-50"
    >
        <svg xmlns="http://www.w3.org/2000/svg"
             class="h-5 w-5 text-gray-500"
             fill="none"
             viewBox="0 0 24 24"
             stroke="currentColor">
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 5v.01M12 12v.01M12 19v.01"/>
        </svg>
    </button>

    <div
        x-show="open"
        x-cloak
        @click.away="open=false"
        @keydown.escape.window="open=false"
        x-transition
        class="absolute right-0 bottom-full z-[9999] mb-2 w-48 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl"
    >
        @if($show)
            <a href="{{ $show }}" class="block px-4 py-2.5 text-sm hover:bg-gray-50">
                👁 View
            </a>
        @endif

        @if($edit)
            <a href="{{ $edit }}" class="block px-4 py-2.5 text-sm hover:bg-gray-50">
                ✏ Edit
            </a>
        @endif

        @if($toggle)
            <form action="{{ $toggle }}" method="POST">
                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    onclick="return confirm('Change status?')"
                    class="block w-full px-4 py-2.5 text-left text-sm hover:bg-gray-50"
                >
                    {{ $active ? '🚫 Deactivate' : '✅ Activate' }}
                </button>
            </form>
        @endif
    </div>
</div>