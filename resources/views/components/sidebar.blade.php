@php
    $theme = config('ravion.theme');

    $canSee = function ($permission) {
        return auth()->check()
            && auth()->user()->hasPermission($permission);
    };

    $icon = function ($name, $class = 'w-4 h-4') {
        $icons = [
            'home' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M4 10v10h6v-6h4v6h6V10"/></svg>',
            'user' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14c-4 0-7 2-7 4v1h14v-1c0-2-3-4-7-4z"/></svg>',
            'users' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M12 12a4 4 0 100-8 4 4 0 000 8z"/></svg>',
            'chart' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19V5m0 14h16M8 17V9m5 8V5m5 12v-6"/></svg>',
            'clipboard' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5h6m-7 4h8m-8 4h8m-8 4h5M9 3h6a2 2 0 012 2h1a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h1a2 2 0 012-2z"/></svg>',
            'document' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 3h7l5 5v13H7a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>',
            'building' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 21h16M6 21V4h12v17M9 8h1m4 0h1M9 12h1m4 0h1M9 16h1m4 0h1"/></svg>',
            'tree' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v18M12 7H7v4M12 11h5v4M7 11h10"/></svg>',
            'settings' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317a1 1 0 011.35-.936l1.514.606a1 1 0 00.93-.086l1.35-.9a1 1 0 011.39.26l1.2 2.078a1 1 0 00.75.493l1.56.195a1 1 0 01.875 1.137l-.24 1.548a1 1 0 00.27.86l1.13 1.13a1 1 0 010 1.414l-1.13 1.13a1 1 0 00-.27.86l.24 1.548a1 1 0 01-.875 1.137l-1.56.195a1 1 0 00-.75.493l-1.2 2.078a1 1 0 01-1.39.26l-1.35-.9a1 1 0 00-.93-.086l-1.514.606a1 1 0 01-1.35-.936l-.12-1.63a1 1 0 00-.49-.78l-1.38-.84a1 1 0 010-1.708l1.38-.84a1 1 0 00.49-.78l.12-1.63z"/><circle cx="12" cy="12" r="3" stroke-width="2"/></svg>',
            'grid' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z"/></svg>',
            'list' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>',
            'link' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M10 13a5 5 0 007.07 0l2-2a5 5 0 00-7.07-7.07l-1 1M14 11a5 5 0 00-7.07 0l-2 2A5 5 0 0012 20.07l1-1"/></svg>',
            'cube' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 2l9 5-9 5-9-5 9-5zM3 7v10l9 5 9-5V7M12 12v10"/></svg>',
            'folder' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>',
            'scale' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M5 6h14M6 6l-3 7h6L6 6zm12 0l-3 7h6l-3-7z"/></svg>',
            'truck' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M3 7h11v10H3zM14 11h4l3 3v3h-7zM7 20a2 2 0 100-4 2 2 0 000 4zm11 0a2 2 0 100-4 2 2 0 000 4z"/></svg>',
            'map' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M9 18l-6 3V6l6-3 6 3 6-3v15l-6 3-6-3zM9 3v15M15 6v15"/></svg>',
            'layers' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 2l9 5-9 5-9-5 9-5zm-9 10l9 5 9-5M3 17l9 5 9-5"/></svg>',
            'door' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M6 21V3h12v18M10 12h.01"/></svg>',
            'square' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="5" y="5" width="14" height="14" rx="2" stroke-width="2"/></svg>',
            'briefcase' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M10 6V5a2 2 0 012-2h0a2 2 0 012 2v1m-9 0h14v14H5z"/></svg>',
            'wrench' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M14.7 6.3a4 4 0 01-5 5L4 17v3h3l5.7-5.7a4 4 0 005-5z"/></svg>',
            'shield' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 4v5c0 5-3.5 8-7 9-3.5-1-7-4-7-9V7l7-4z"/></svg>',
            'check' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>',
            'queue' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h10M4 18h7"/></svg>',
            'review' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M5 4h14v16H5z"/></svg>',
            'cog' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 8a4 4 0 100 8 4 4 0 000-8zm8 4h2m-20 0h2m14.14-6.14l1.42-1.42M4.44 19.56l1.42-1.42m12.72 1.42l-1.42-1.42M4.44 4.44l1.42 1.42"/></svg>',
            'key' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M15 7a4 4 0 11-7 2.65L3 14v3h3l2-2h2l1.35-1.35A4 4 0 0015 7z"/></svg>',
            'lock' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M6 10V8a6 6 0 1112 0v2M5 10h14v11H5z"/></svg>',
            'history' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 109-9M3 3v6h6M12 7v5l4 2"/></svg>',
            'warning' => '<svg class="'.$class.'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>',
        ];

        return $icons[$name] ?? $icons['square'];
    };
@endphp

<aside class="fixed left-0 top-0 w-[272px] h-screen bg-white border-r border-gray-200 z-30 overflow-y-auto">

    <div class="px-5 py-5 border-b border-gray-200">
        <div class="text-xl font-bold tracking-tight text-[#0F2A52]">
            Ravion ERP
        </div>
        <div class="text-xs text-gray-500 mt-1">
            DPR & Construction Control
        </div>
    </div>

    <nav class="px-3 py-4 space-y-2" x-data="{ openSection: null }">

        @foreach(config('navigation') as $index => $section)

            @php
                $visibleItems = collect($section['items'])
                    ->filter(function ($item) use ($canSee) {
    $permission = $item['permission'] ?? null;
    $route = $item['route'] ?? null;

    return $route
        && Route::has($route)
        && $canSee($permission);
});

                $sectionActive = $visibleItems->contains(function ($item) {
                    return request()->routeIs($item['route'])
                        || request()->routeIs(str_replace('.index', '.*', $item['route']));
                });

                $sectionKey = 'section_' . $index;
            @endphp

            @if($visibleItems->count())

                <div x-init="@if($sectionActive) openSection = '{{ $sectionKey }}' @endif">

                    <button type="button"
                            @click="openSection = openSection === '{{ $sectionKey }}' ? null : '{{ $sectionKey }}'"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition
                                   {{ $sectionActive ? 'bg-[#E8F1FF] text-[#0F2A52]' : 'text-gray-700 hover:bg-gray-100' }}">

                        <span class="flex items-center gap-3 min-w-0">
                            <span class="{{ $sectionActive ? config('ravion.theme.sidebar_icon_active_color', 
                            'text-blue-700') : config('ravion.theme.sidebar_icon_color', 'text-sky-500') }}">

                                {!! $icon($section['icon'] ?? 'square', 'w-4 h-4') !!}
                            </span>

                            <span class="text-sm font-bold truncate">
                                {{ $section['title'] }}
                            </span>
                        </span>

                        <svg class="w-4 h-4 text-gray-400 transition-transform"
                             :class="openSection === '{{ $sectionKey }}' ? 'rotate-90' : ''"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>

                    <div x-show="openSection === '{{ $sectionKey }}'" x-cloak
                    class="mt-1 ml-7 pl-2 space-y-1">

                        @foreach($visibleItems as $item)

                            @php
                                $active = request()->routeIs($item['route'])
                                    || request()->routeIs(str_replace('.index', '.*', $item['route']));
                            @endphp

                            <a href="{{ route($item['route']) }}"
                               class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition duration-200 ease-in-out
                                      {{ $active
                                            ? 'bg-[#E8F1FF] text-[#0F2A52] font-semibold'
                                            : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">

                                <span class="{{ $active ? config('ravion.theme.sidebar_icon_active_color', 
                                'text-blue-700') : config('ravion.theme.sidebar_icon_color', 'text-sky-500') }}">
                                    {!! $icon($item['icon'] ?? 'square', 'w-3.5 h-3.5') !!}
                                </span>

                                <span class="truncate">
                                    {{ $item['title'] }}
                                </span>
                            </a>

                        @endforeach

                    </div>

                </div>

            @endif

        @endforeach

    </nav>

</aside>