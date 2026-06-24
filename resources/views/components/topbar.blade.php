@php
    $theme = config('ravion.theme');
@endphp

<header class="bg-white rounded-lg shadow-sm p-4 mb-6 flex justify-between items-center border">

    <div>
        <div class="font-bold" style="color: {{ $theme['primary'] }};">
            Ravion DPR System
        </div>
        <div class="text-xs text-gray-500">
            Execution Intelligence Platform
        </div>
    </div>

    <div class="flex items-center gap-4">
        @auth
            <span class="text-sm text-gray-700">
                {{ auth()->user()->name }}
                <span class="text-gray-400">
                    ({{ auth()->user()->role->name ?? '' }})
                </span>
            </span>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit"
                        class="text-white px-4 py-2 rounded text-sm"
                        style="background: {{ $theme['primary'] }};">
                    Logout
                </button>
            </form>
        @endauth
    </div>

</header>