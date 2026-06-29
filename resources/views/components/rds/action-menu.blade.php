@props([
    'show' => null,
    'edit' => null,
    'toggle' => null,
    'active' => true,
])

<div x-data="{ open:false }" class="relative inline-block text-left">

    <button
        @click="open=!open"
        class="w-9 h-9 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 flex items-center justify-center">

        <svg xmlns="http://www.w3.org/2000/svg"
             class="w-5 h-5 text-gray-500"
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
        @click.away="open=false"
        x-transition
        class="absolute right-0 mt-2 w-44 bg-white border rounded-lg shadow-lg z-50 overflow-hidden">

        @if($show)
            <a href="{{ $show }}"
               class="block px-4 py-2 text-sm hover:bg-gray-100">
                👁 View
            </a>
        @endif

        @if($edit)
            <a href="{{ $edit }}"
               class="block px-4 py-2 text-sm hover:bg-gray-100">
                ✏ Edit
            </a>
        @endif

        @if($toggle)

            <form
                action="{{ $toggle }}"
                method="POST">

                @csrf
                @method('DELETE')

                <button
                    onclick="return confirm('Change status?')"
                    class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100">

                    {{ $active ? '🚫 Deactivate' : '✅ Activate' }}

                </button>

            </form>

        @endif

    </div>

</div>