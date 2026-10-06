@php
    $theme = config('ravion.theme');

    $userRole = auth()->user()?->role?->name;

    $mobileHomeRoute = match ($userRole) {
        'Engineer', 'Site Engineer' => 'engineer-dashboard',
        'Admin' => 'admin-dashboard',
        'PMO', 'DGM' => 'pmo-dashboard',
        'CEO' => 'ceo-dashboard',
        'Accountant' => 'accountant-dashboard',
        default => 'dashboard',
    };

    $mobileHomeUrl = route($mobileHomeRoute);
@endphp

<header class="lg:hidden sticky top-0 z-40 bg-white border-b border-gray-200">

    <div class="flex items-center justify-between gap-2 px-3 py-2">

        {{-- Back / Forward Navigation --}}
        <div class="flex items-center gap-1 shrink-0">

            {{-- Back --}}
            <button
                type="button"
                id="ravion-mobile-back"
                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 bg-white text-gray-700 active:bg-gray-100"
                aria-label="Go Back"
                title="Back"
            >
                <svg
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 19l-7-7 7-7"
                    />
                </svg>
            </button>

            {{-- Forward --}}
            <button
                type="button"
                id="ravion-mobile-forward"
                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 bg-white text-gray-700 active:bg-gray-100"
                aria-label="Go Forward"
                title="Forward"
            >
                <svg
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 5l7 7-7 7"
                    />
                </svg>
            </button>

        </div>

        {{-- App / User --}}
        <div class="min-w-0 flex-1 px-1">

            <div class="text-sm font-bold text-[#0F2A52] truncate">
                Ravion DPR
            </div>

            @auth
                <div class="text-[11px] text-gray-500 truncate">
                    {{ auth()->user()->name }}
                </div>
            @endauth

        </div>

        {{-- Home / Logout --}}
        <div class="flex items-center gap-1 shrink-0">

            {{-- Dashboard --}}
            <a
                href="{{ $mobileHomeUrl }}"
                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 bg-white text-gray-700 active:bg-gray-100"
                aria-label="Dashboard"
                title="Dashboard"
            >
                <svg
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10"
                    />
                </svg>
            </a>

            @auth
                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-white active:opacity-90"
                        style="background: {{ $theme['primary'] ?? '#0F2A52' }};"
                        aria-label="Logout"
                        title="Logout"
                    >
                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"
                            />
                        </svg>
                    </button>
                </form>
            @endauth

        </div>

    </div>

</header>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const backButton = document.getElementById('ravion-mobile-back');
        const forwardButton = document.getElementById('ravion-mobile-forward');

        const homeUrl = @json($mobileHomeUrl);

        if (backButton) {
            backButton.addEventListener('click', function () {
                /*
                 * In an installed PWA there may be no previous page,
                 * especially when the application was launched directly
                 * from the home-screen icon.
                 */
                if (window.history.length > 1) {
                    window.history.back();
                    return;
                }

                window.location.href = homeUrl;
            });
        }

        if (forwardButton) {
            forwardButton.addEventListener('click', function () {
                window.history.forward();
            });
        }
    });
</script>