@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Location Masters
</h1>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    <a href="{{ route('location-block-masters.index') }}"
       class="bg-white p-6 rounded shadow hover:shadow-lg block">
        <h2 class="text-xl font-bold mb-2">Block Masters</h2>
        <p class="text-gray-600">
            Manage standard blocks, buildings, towers and villas.
        </p>
    </a>

    <a href="{{ route('location-floor-masters.index') }}"
       class="bg-white p-6 rounded shadow hover:shadow-lg block">
        <h2 class="text-xl font-bold mb-2">Floor Masters</h2>
        <p class="text-gray-600">
            Manage standard floors like Ground, First, Terrace.
        </p>
    </a>

    <a href="{{ route('location-unit-masters.index') }}"
       class="bg-white p-6 rounded shadow hover:shadow-lg block">
        <h2 class="text-xl font-bold mb-2">Unit Masters</h2>
        <p class="text-gray-600">
            Manage standard flats, villas, offices and common areas.
        </p>
    </a>

    <a href="{{ route('location-room-masters.index') }}"
       class="bg-white p-6 rounded shadow hover:shadow-lg block">
        <h2 class="text-xl font-bold mb-2">Room Masters</h2>
        <p class="text-gray-600">
            Manage bedrooms, kitchens, toilets and common spaces.
        </p>
    </a>

    <a href="{{ route('location-subspace-masters.index') }}"
       class="bg-white p-6 rounded shadow hover:shadow-lg block">
        <h2 class="text-xl font-bold mb-2">Sub-space Masters</h2>
        <p class="text-gray-600">
            Manage walls, floors, ceilings and room elements.
        </p>
    </a>

</div>

@endsection