<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ravion DPR</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100">

<div class="flex min-h-screen">

    {{-- SIDEBAR --}}
    <aside class="w-64 bg-gray-900 text-white min-h-screen">

        <div class="p-4 text-2xl font-bold border-b border-gray-700">
            Ravion ERP
        </div>

        <ul class="py-4 space-y-1">

            {{-- DASHBOARD --}}
            <div class="px-4 py-2 text-xs font-bold text-gray-400 uppercase">
                Dashboard
            </div>

            <li>
                <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"
                   class="block px-4 py-2 hover:bg-gray-700">
                    Dashboard
                </a>
            </li>


            {{-- MASTERS --}}
            @auth
                @if(in_array(auth()->user()->role->name, ['Admin', 'PMO', 'DGM']))

                    <div class="px-4 py-3 text-xs font-bold text-gray-400 uppercase">
                        Masters
                    </div>

                    <li>
                        <a href="{{ Route::has('projects.index') ? route('projects.index') : '#' }}"
                           class="block px-4 py-2 hover:bg-gray-700">
                            Projects
                        </a>
                    </li>

                    <li>
                        <a href="{{ Route::has('location-masters.index') ? route('location-masters.index') : '#' }}"
                           class="block px-4 py-2 hover:bg-gray-700">
                            Location Masters
                        </a>
                    </li>

                    <li>
                        <a href="{{ Route::has('project-locations.index') ? route('project-locations.index') : '#' }}"
                           class="block px-4 py-2 hover:bg-gray-700">
                            Project Locations
                        </a>
                    </li>

                    <li>
                        <a href="{{ Route::has('activity-mappings.index') ? route('activity-mappings.index') : '#' }}"
                           class="block px-4 py-2 hover:bg-gray-700">
                            Activity Mappings
                        </a>
                    </li>

                    <li>
                        <a href="{{ Route::has('materials.index') ? route('materials.index') : '#' }}"
                           class="block px-4 py-2 hover:bg-gray-700">
                            Materials
                        </a>
                    </li>

                    <li>
                        <a href="{{ Route::has('contractors.index') ? route('contractors.index') : '#' }}"
                           class="block px-4 py-2 hover:bg-gray-700">
                            Contractors
                        </a>
                    </li>

                    <li>
                        <a href="{{ Route::has('vendors.index') ? route('vendors.index') : '#' }}"
                           class="block px-4 py-2 hover:bg-gray-700">
                            Vendors
                        </a>
                    </li>

                    <li>
                        <a href="{{ Route::has('machinery-tools.index') ? route('machinery-tools.index') : '#' }}"
                           class="block px-4 py-2 hover:bg-gray-700">
                            Machinery / Tools
                        </a>
                    </li>

                @endif
            @endauth


            {{-- DAILY EXECUTION --}}
            <div class="px-4 py-3 text-xs font-bold text-gray-400 uppercase">
                Daily Execution
            </div>

            <li>
                <a href="{{ Route::has('dprs.index') ? route('dprs.index') : '#' }}"
                   class="block px-4 py-2 hover:bg-gray-700">
                    DPR Entries
                </a>
            </li>

            <li>
                <a href="{{ Route::has('labour-reports.index') ? route('labour-reports.index') : '#' }}"
                   class="block px-4 py-2 hover:bg-gray-700">
                    Labour Reporting
                </a>
            </li>

            <li>
                <a href="#"
                   class="block px-4 py-2 hover:bg-gray-700">
                    Material Received
                </a>
            </li>

            <li>
                <a href="#"
                   class="block px-4 py-2 hover:bg-gray-700">
                    Material Consumed
                </a>
            </li>

            <li>
                <a href="#"
                   class="block px-4 py-2 hover:bg-gray-700">
                    Material Required
                </a>
            </li>


            {{-- PLANNING --}}
            <div class="px-4 py-3 text-xs font-bold text-gray-400 uppercase">
                Planning & Controls
            </div>

            <li>
                <a href="{{ Route::has('weekly-plans.index') ? route('weekly-plans.index') : '#' }}"
                   class="block px-4 py-2 hover:bg-gray-700">
                    Weekly Plans
                </a>
            </li>

            <li>
                <a href="{{ Route::has('weekly-plans.progress-dashboard') ? route('weekly-plans.progress-dashboard') : '#' }}"
                   class="block px-4 py-2 hover:bg-gray-700">
                    Weekly Progress
                </a>
            </li>

            <li>
                <a href="#"
                   class="block px-4 py-2 hover:bg-gray-700">
                    Monthly Plans
                </a>
            </li>


            {{-- PMO --}}
            @auth
                @if(in_array(auth()->user()->role->name, ['Admin', 'PMO', 'DGM']))
                    <div class="px-4 py-3 text-xs font-bold text-gray-400 uppercase">
                        PMO & Verification
                    </div>

                    <li>
                        <a href="#"
                           class="block px-4 py-2 hover:bg-gray-700">
                            DPR Reviews
                        </a>
                    </li>

                    <li>
                        <a href="#"
                           class="block px-4 py-2 hover:bg-gray-700">
                            Material Verification
                        </a>
                    </li>

                    <li>
                        <a href="#"
                           class="block px-4 py-2 hover:bg-gray-700">
                            Mapping Pending Queue
                        </a>
                    </li>
                @endif
            @endauth


            {{-- REPORTS --}}
            <div class="px-4 py-3 text-xs font-bold text-gray-400 uppercase">
                Reports
            </div>

            <li>
                <a href="#"
                   class="block px-4 py-2 hover:bg-gray-700">
                    DPR Reports
                </a>
            </li>

            <li>
                <a href="#"
                   class="block px-4 py-2 hover:bg-gray-700">
                    Labour Reports
                </a>
            </li>

            <li>
                <a href="#"
                   class="block px-4 py-2 hover:bg-gray-700">
                    Material Reports
                </a>
            </li>


            {{-- ADMINISTRATION --}}
            @auth
                @if(auth()->user()->role->name === 'Admin')
                    <div class="px-4 py-3 text-xs font-bold text-gray-400 uppercase">
                        Administration
                    </div>

                    <li>
                        <a href="{{ Route::has('users.index') ? route('users.index') : '#' }}"
                           class="block px-4 py-2 hover:bg-gray-700">
                            Users
                        </a>
                    </li>

                    <li>
                        <a href="#"
                           class="block px-4 py-2 hover:bg-gray-700">
                            Audit Trail
                        </a>
                    </li>
                @endif
            @endauth

        </ul>
    </aside>


    {{-- MAIN CONTENT --}}
    <main class="flex-1 p-6">

        {{-- TOP BAR --}}
        <div class="bg-white rounded shadow p-4 mb-6 flex justify-between items-center">

            <div class="font-bold">
                Ravion DPR System
            </div>

            <div class="flex items-center gap-4">

                @auth
                    <span>
                        {{ auth()->user()->name }}
                        ({{ auth()->user()->role->name ?? '' }})
                    </span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button type="submit"
                                class="bg-red-600 text-white px-3 py-1 rounded">
                            Logout
                        </button>
                    </form>
                @endauth

            </div>

        </div>

        @yield('content')

    </main>

</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>