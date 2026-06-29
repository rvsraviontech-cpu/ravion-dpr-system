@props([
    'items' => [],
])

<nav aria-label="Breadcrumb" {{ $attributes }}>
    <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500">

        @foreach($items as $item)

            @if(!$loop->first)
                <li>
                    <svg class="h-4 w-4 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        stroke-width="2">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M9 5l7 7-7 7"/>
                    </svg>
                </li>
            @endif

            <li>
                @if(isset($item['url']) && !$loop->last)
                    <a href="{{ $item['url'] }}"
                       class="hover:text-primary-600 transition-colors">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="font-medium text-gray-900">
                        {{ $item['label'] }}
                    </span>
                @endif
            </li>

        @endforeach

    </ol>
</nav>