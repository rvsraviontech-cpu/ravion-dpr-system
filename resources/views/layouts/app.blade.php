<!DOCTYPE html>
<html>
<head>

    <title>Ravion DPR System</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="bg-gray-100">

    <div class="flex min-h-screen">

        <!-- Sidebar -->

        <div class="w-64 bg-gray-900 text-white p-5">

            <h1 class="text-2xl font-bold mb-8">
                Ravion DPR
            </h1>

            <ul class="space-y-4">
                @php
                 $role = auth()->user()->role->name;
                @endphp

    @if($role == 'Admin')

<li>
    <a href="/admin-dashboard"
       class="block py-2 px-4 hover:bg-gray-700 rounded">

        Dashboard

    </a>
</li>

<li>
    <a href="/projects"
       class="block py-2 px-4 rounded
{{ request()->is('projects*')
    ? 'bg-blue-600'
    : 'hover:bg-gray-700' }}">

        Projects

    </a>
</li>

<li>
    <a href="/activities"
       class="block py-2 px-4 hover:bg-gray-700 rounded">

        Activities

    </a>
</li>

<li>
    <a href="/contractors"
       class="block py-2 px-4 hover:bg-gray-700 rounded">

        Contractors

    </a>
</li>

<li>
    <a href="/users"
   class="block py-2 px-4 rounded
   {{ request()->is('users*')
       ? 'bg-blue-600'
       : 'hover:bg-gray-700' }}">

    Users

</a>
</li>

<li>
    <a href="/dprs"
   class="block py-2 px-4 rounded
   {{ request()->is('dprs*')
       ? 'bg-blue-600'
       : 'hover:bg-gray-700' }}">

    DPR Entries

</a>
</li>
<li>
    <a href="/project-progress"
       class="block py-2 px-4 hover:bg-gray-700 rounded">

        Project Progress

    </a>
</li>
<li>
    <a href="/engineer-productivity"
       class="block py-2 px-4 hover:bg-gray-700 rounded">

        Engineer Productivity

    </a>
</li>
<li>

    <a href="{{ route('labour-types.index') }}"
       class="block px-4 py-2 hover:bg-gray-700">

        Labour Types

    </a>

</li>

<li>

    <a href="{{ route('materials.index') }}"
       class="block px-4 py-2 hover:bg-gray-700">

        Materials

    </a>

</li>
<li>

    <a href="{{ route('vendors.index') }}"
       class="block px-4 py-2 hover:bg-gray-700">

        Vendors

    </a>

</li>
<li>

    <a href="{{ route('machinery-tools.index') }}"
       class="block px-4 py-2 hover:bg-gray-700">

        Machinery & Tools

    </a>

</li>
<li>

    <a href="{{ route('weekly-plans.index') }}"
       class="block px-4 py-2 hover:bg-gray-700">

        Weekly Plans

    </a>

</li>
<li>

    <a href="{{ route('weekly-plans.progress-dashboard') }}"
       class="block px-4 py-2 hover:bg-gray-700">

        Weekly Progress Dasboard

    </a>

</li>

@endif

    @if($role == 'Engineer')

<li>
    <a href="/engineer-dashboard"
       class="block py-2 px-4 hover:bg-gray-700 rounded">

        Engineer Dashboard

    </a>
</li>

<li>
    <a href="/dprs"
       class="block py-2 px-4 hover:bg-gray-700 rounded">

        DPR Entries

    </a>
</li>

@endif
@if($role == 'PMO')

<li>
    <a href="/pmo-dashboard"
       class="block py-2 px-4 hover:bg-gray-700 rounded">

        PMO Dashboard

    </a>
</li>

<li>
    <a href="/pmo/dprs"
       class="block py-2 px-4 hover:bg-gray-700 rounded">

        DPR Review Queue

    </a>
</li>
<li>
    <a href="/project-progress"
       class="block py-2 px-4 hover:bg-gray-700 rounded">

        Project Progress

    </a>
</li>
<li>
    <a href="/engineer-productivity"
       class="block py-2 px-4 hover:bg-gray-700 rounded">

        Engineer Productivity

    </a>
</li>
<li>

    <a href="{{ route('weekly-plans.index') }}"
       class="block px-4 py-2 hover:bg-gray-700">

        Weekly Plans

    </a>

</li>
<li>

    <a href="{{ route('weekly-plans.progress-dashboard') }}"
       class="block px-4 py-2 hover:bg-gray-700">

        Weekly Progress Dasboard

    </a>

</li>

@endif
@if($role == 'CEO')

<li>
    <a href="/ceo-dashboard"
       class="block py-2 px-4 hover:bg-gray-700 rounded">

        CEO Dashboard

    </a>
</li>
<li>
    <a href="/project-progress"
       class="block py-2 px-4 hover:bg-gray-700 rounded">

        Project Progress

    </a>
</li>
<li>
    <a href="/engineer-productivity"
       class="block py-2 px-4 hover:bg-gray-700 rounded">

        Engineer Productivity

    </a>
</li>
@endif
</ul>

        </div>

        <!-- Main Content -->

        <div class="flex-1">

            <!-- Top Navbar -->

            <div class="bg-white shadow p-4 flex justify-between">

                <div>
                    Welcome,
                    {{ auth()->user()->name }}
                </div>

                <div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button type="submit"
                            class="bg-red-500 text-white px-4 py-2 rounded">

                            Logout

                        </button>

                    </form>

                </div>

            </div>

            <!-- Page Content -->

            <div class="p-6">
                @if(session('success'))

    <div class="bg-green-100 border border-green-400
                text-green-700 px-4 py-3 rounded mb-6">

        {{ session('success') }}

    </div>

@endif
                @yield('content')

            </div>

        </div>

    </div>

</body>
</html>