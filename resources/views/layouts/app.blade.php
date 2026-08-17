<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>Ravion DPR</title>

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, viewport-fit=cover"
    >

    <meta name="theme-color" content="#0F2A52">

    <meta
        name="description"
        content="Ravion Daily Progress Report & Site Execution"
    >

    <meta name="mobile-web-app-capable" content="yes">

    <meta
        name="apple-mobile-web-app-capable"
        content="yes"
    >

    <meta
        name="apple-mobile-web-app-status-bar-style"
        content="default"
    >

    <meta
        name="apple-mobile-web-app-title"
        content="Ravion DPR"
    >

    <link
        rel="manifest"
        href="{{ asset('manifest.webmanifest') }}"
    >

    <link
        rel="apple-touch-icon"
        href="{{ asset('pwa/icons/icon-192.png') }}"
    >

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

    @stack('styles')
</head>

<body
    style="background: {{ config('ravion.theme.page_bg') }};"
    x-data="{ mobileMenuOpen: false }"
    @open-mobile-menu.window="mobileMenuOpen = true"
>

<div class="min-h-screen">

    {{-- Desktop Sidebar --}}
    <div class="hidden lg:block">
        <x-sidebar />
    </div>

    {{-- Mobile Header --}}
    <x-mobile-header />

    <main
        class="
            min-h-screen
            overflow-x-auto
            pb-24
            lg:ml-[272px]
            lg:p-6
            lg:pb-6
        "
    >

        {{-- Desktop Topbar --}}
        <div class="hidden lg:block">
            <x-topbar />
        </div>

        {{-- Mobile Content Padding --}}
        <div class="px-4 py-4 lg:p-0">
            @yield('content')
        </div>

    </main>

    {{-- Mobile Bottom Navigation --}}
    <x-mobile-bottom-nav />

    {{-- Mobile More Menu --}}
    <div
        x-show="mobileMenuOpen"
        x-cloak
        class="lg:hidden fixed inset-0 z-50"
    >

        <div
            class="absolute inset-0 bg-black/40"
            @click="mobileMenuOpen = false"
        ></div>

        <div
            class="
                absolute
                bottom-0
                left-0
                right-0
                bg-white
                rounded-t-2xl
                shadow-xl
                p-4
                pb-8
            "
        >

            <div class="flex items-center justify-between mb-4">

                <div>
                    <div class="text-lg font-bold text-[#0F2A52]">
                        Site Execution
                    </div>

                    <div class="text-xs text-gray-500">
                        Ravion DPR
                    </div>
                </div>

                <button
                    type="button"
                    @click="mobileMenuOpen = false"
                    class="
                        w-9
                        h-9
                        inline-flex
                        items-center
                        justify-center
                        rounded-lg
                        bg-gray-100
                        text-gray-600
                    "
                >
                    ✕
                </button>

            </div>

            <div class="grid grid-cols-2 gap-3">

                @if(Route::has('dprs.create'))
                    <a
                        href="{{ route('dprs.create') }}"
                        class="rounded-xl border border-gray-200 p-4"
                    >
                        <div class="font-semibold text-gray-900">
                            Create DPR
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            Daily progress entry
                        </div>
                    </a>
                @endif

                @if(Route::has('labour-attendances.index'))
                    <a
                        href="{{ route('labour-attendances.index') }}"
                        class="rounded-xl border border-gray-200 p-4"
                    >
                        <div class="font-semibold text-gray-900">
                            Labour Attendance
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            Daily attendance
                        </div>
                    </a>
                @endif

                @if(Route::has('tomorrow-plans.index'))
                    <a
                        href="{{ route('tomorrow-plans.index') }}"
                        class="rounded-xl border border-gray-200 p-4"
                    >
                        <div class="font-semibold text-gray-900">
                            Tomorrow Plan
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            Plan next-day work
                        </div>
                    </a>
                @endif

                @if(Route::has('site-issues.index'))
                    <a
                        href="{{ route('site-issues.index') }}"
                        class="rounded-xl border border-gray-200 p-4"
                    >
                        <div class="font-semibold text-gray-900">
                            Site Issues
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            Report site concerns
                        </div>
                    </a>
                @endif

                @if(Route::has('dpr-photos.index'))
                    <a
                        href="{{ route('dpr-photos.index') }}"
                        class="rounded-xl border border-gray-200 p-4"
                    >
                        <div class="font-semibold text-gray-900">
                            Site Photos
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            Capture site progress
                        </div>
                    </a>
                @endif

                @if(Route::has('machinery-tools.index'))
                    <a
                        href="{{ route('machinery-tools.index') }}"
                        class="rounded-xl border border-gray-200 p-4"
                    >
                        <div class="font-semibold text-gray-900">
                            Machinery
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            Equipment usage
                        </div>
                    </a>
                @endif

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@stack('scripts')

</body>
</html>