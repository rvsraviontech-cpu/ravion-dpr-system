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
@endphp

<header class="lg:hidden sticky top-0 z-40 bg-white border-b border-gray-200">

    <div class="flex items-center justify-between px-4 py-3">

        <div class="min-w-0">

            <div class="text-base font-bold text-[#0F2A52] truncate">
                Ravion DPR
            </div>

            @auth
                <div class="text-xs text-gray-500 truncate">
                    {{ auth()->user()->name }}
                </div>
            @endauth

        </div>

        <div class="flex items-center gap-2">

            <a
                href="{{ route($mobileHomeRoute) }}"
                class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 text-gray-600"
                aria-label="Dashboard"
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
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-white"
                        style="background: {{ $theme['primary'] ?? '#0F2A52' }};"
                        aria-label="Logout"
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