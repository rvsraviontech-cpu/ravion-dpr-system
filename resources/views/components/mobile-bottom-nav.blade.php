@php
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

<div
    class="lg:hidden"
    x-data="{
        labourMenuOpen: false,
        materialMenuOpen: false
    }"
>
    {{-- Bottom Navigation --}}
    <nav
        class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-gray-200"
        style="padding-bottom: env(safe-area-inset-bottom);"
    >
        <div class="grid grid-cols-5">

            {{-- Home --}}
            <a
    href="{{ route($mobileHomeRoute) }}"
                class="flex flex-col items-center justify-center py-2 text-xs
                {{ request()->routeIs($mobileHomeRoute)
                    ? 'text-[#0F2A52] font-semibold'
                    : 'text-gray-500' }}"
            >
                <svg
                    class="w-5 h-5 mb-1"
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

                Home
            </a>

            {{-- DPR --}}
            <a
                href="{{ Route::has('dprs.create') ? route('dprs.create') : '#' }}"
                class="flex flex-col items-center justify-center py-2 text-xs
                {{ request()->routeIs('dprs.*')
                    ? 'text-[#0F2A52] font-semibold'
                    : 'text-gray-500' }}"
            >
                <svg
                    class="w-5 h-5 mb-1"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 5h6m-7 4h8m-8 4h8m-8 4h5M7 3h10a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z"
                    />
                </svg>

                DPR
            </a>

            {{-- Labour --}}
            <button
                type="button"
                @click="
                    materialMenuOpen = false;
                    labourMenuOpen = true;
                "
                class="flex flex-col items-center justify-center py-2 text-xs
                {{ request()->routeIs('labour-attendances.*')
                    || request()->routeIs('labour-attendance-corrections.*')
                    || request()->routeIs('labour-attendance-register.*')
                        ? 'text-[#0F2A52] font-semibold'
                        : 'text-gray-500' }}"
            >
                <svg
                    class="w-5 h-5 mb-1"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M12 12a4 4 0 100-8 4 4 0 000 8z"
                    />
                </svg>

                Labour
            </button>

            {{-- Materials --}}
            <button
                type="button"
                @click="
                    labourMenuOpen = false;
                    materialMenuOpen = true;
                "
                class="flex flex-col items-center justify-center py-2 text-xs
                {{ request()->routeIs('material-received.*')
                    || request()->routeIs('material-consumed.*')
                    || request()->routeIs('material-requirements.*')
                        ? 'text-[#0F2A52] font-semibold'
                        : 'text-gray-500' }}"
            >
                <svg
                    class="w-5 h-5 mb-1"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3 7h11v10H3zM14 11h4l3 3v3h-7z"
                    />
                </svg>

                Materials
            </button>

            {{-- More --}}
            <button
                type="button"
                @click="
                    labourMenuOpen = false;
                    materialMenuOpen = false;
                    $dispatch('open-mobile-menu');
                "
                class="flex flex-col items-center justify-center py-2 text-xs text-gray-500"
            >
                <svg
                    class="w-5 h-5 mb-1"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16"
                    />
                </svg>

                More
            </button>

        </div>
    </nav>


    {{-- Labour Backdrop --}}
    <div
        x-show="labourMenuOpen"
        x-cloak
        x-transition.opacity
        @click="labourMenuOpen = false"
        class="fixed inset-0 z-40 bg-black/40"
    ></div>


    {{-- Labour Menu --}}
    <div
        x-show="labourMenuOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
        class="
            fixed
            bottom-0
            left-0
            right-0
            z-50
            bg-white
            rounded-t-2xl
            shadow-2xl
        "
        style="padding-bottom: env(safe-area-inset-bottom);"
    >
        <div class="p-4">

            <div class="w-10 h-1 bg-gray-300 rounded-full mx-auto mb-4"></div>

            <div class="flex items-center justify-between mb-4">

                <div>
                    <div class="text-lg font-bold text-[#0F2A52]">
                        Labour
                    </div>

                    <div class="text-xs text-gray-500">
                        Attendance & workforce tracking
                    </div>
                </div>

                <button
                    type="button"
                    @click="labourMenuOpen = false"
                    class="
                        inline-flex
                        items-center
                        justify-center
                        w-9
                        h-9
                        rounded-lg
                        bg-gray-100
                        text-gray-600
                    "
                    aria-label="Close"
                >
                    ✕
                </button>

            </div>

            <div class="space-y-2">

                @if(Route::has('labour-attendances.index'))
                    <a
                        href="{{ route('labour-attendances.index') }}"
                        class="
                            flex
                            items-center
                            justify-between
                            rounded-xl
                            border
                            border-gray-200
                            px-4
                            py-4
                        "
                    >
                        <div>
                            <div class="font-semibold text-gray-900">
                                Labour Attendance
                            </div>

                            <div class="text-xs text-gray-500 mt-1">
                                Record daily site attendance
                            </div>
                        </div>

                        <span class="text-gray-400">
                            ›
                        </span>
                    </a>
                @endif

                @if(Route::has('labour-attendance-corrections.index'))
                    <a
                        href="{{ route('labour-attendance-corrections.index') }}"
                        class="
                            flex
                            items-center
                            justify-between
                            rounded-xl
                            border
                            border-gray-200
                            px-4
                            py-4
                        "
                    >
                        <div>
                            <div class="font-semibold text-gray-900">
                                Attendance Corrections
                            </div>

                            <div class="text-xs text-gray-500 mt-1">
                                Review attendance corrections
                            </div>
                        </div>

                        <span class="text-gray-400">
                            ›
                        </span>
                    </a>
                @endif

                @if(Route::has('labour-attendance-register.index'))
                    <a
                        href="{{ route('labour-attendance-register.index') }}"
                        class="
                            flex
                            items-center
                            justify-between
                            rounded-xl
                            border
                            border-gray-200
                            px-4
                            py-4
                        "
                    >
                        <div>
                            <div class="font-semibold text-gray-900">
                                Attendance Register
                            </div>

                            <div class="text-xs text-gray-500 mt-1">
                                View recorded attendance
                            </div>
                        </div>

                        <span class="text-gray-400">
                            ›
                        </span>
                    </a>
                @endif

            </div>

        </div>
    </div>


    {{-- Materials Backdrop --}}
    <div
        x-show="materialMenuOpen"
        x-cloak
        x-transition.opacity
        @click="materialMenuOpen = false"
        class="fixed inset-0 z-40 bg-black/40"
    ></div>


    {{-- Materials Menu --}}
    <div
        x-show="materialMenuOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
        class="
            fixed
            bottom-0
            left-0
            right-0
            z-50
            bg-white
            rounded-t-2xl
            shadow-2xl
        "
        style="padding-bottom: env(safe-area-inset-bottom);"
    >
        <div class="p-4">

            <div class="w-10 h-1 bg-gray-300 rounded-full mx-auto mb-4"></div>

            <div class="flex items-center justify-between mb-4">

                <div>
                    <div class="text-lg font-bold text-[#0F2A52]">
                        Material Tracking
                    </div>

                    <div class="text-xs text-gray-500">
                        Site material transactions
                    </div>
                </div>

                <button
                    type="button"
                    @click="materialMenuOpen = false"
                    class="
                        inline-flex
                        items-center
                        justify-center
                        w-9
                        h-9
                        rounded-lg
                        bg-gray-100
                        text-gray-600
                    "
                    aria-label="Close"
                >
                    ✕
                </button>

            </div>

            <div class="space-y-2">

                @if(Route::has('material-received.index'))
                    <a
                        href="{{ route('material-received.index') }}"
                        class="
                            flex
                            items-center
                            justify-between
                            rounded-xl
                            border
                            border-gray-200
                            px-4
                            py-4
                        "
                    >
                        <div>
                            <div class="font-semibold text-gray-900">
                                Material Received
                            </div>

                            <div class="text-xs text-gray-500 mt-1">
                                Record materials received at site
                            </div>
                        </div>

                        <span class="text-gray-400">
                            ›
                        </span>
                    </a>
                @endif

                @if(Route::has('material-consumed.index'))
                    <a
                        href="{{ route('material-consumed.index') }}"
                        class="
                            flex
                            items-center
                            justify-between
                            rounded-xl
                            border
                            border-gray-200
                            px-4
                            py-4
                        "
                    >
                        <div>
                            <div class="font-semibold text-gray-900">
                                Material Consumed
                            </div>

                            <div class="text-xs text-gray-500 mt-1">
                                Record daily material consumption
                            </div>
                        </div>

                        <span class="text-gray-400">
                            ›
                        </span>
                    </a>
                @endif

                @if(Route::has('material-requirements.index'))
                    <a
                        href="{{ route('material-requirements.index') }}"
                        class="
                            flex
                            items-center
                            justify-between
                            rounded-xl
                            border
                            border-gray-200
                            px-4
                            py-4
                        "
                    >
                        <div>
                            <div class="font-semibold text-gray-900">
                                Material Required
                            </div>

                            <div class="text-xs text-gray-500 mt-1">
                                Submit upcoming site requirements
                            </div>
                        </div>

                        <span class="text-gray-400">
                            ›
                        </span>
                    </a>
                @endif

            </div>

        </div>
    </div>

</div>