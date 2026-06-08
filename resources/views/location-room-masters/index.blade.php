@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Location Room Masters
</h1>

@if(session('success'))
    <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<div class="bg-white p-6 rounded shadow mb-6">

    <h2 class="text-xl font-bold mb-4">
        Add Location Room Master
    </h2>

    <form method="POST"
          action="{{ route('location-room-masters.store') }}">

        @csrf

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

            <div>
                <label class="block font-semibold mb-1">Room Name</label>
                <input type="text"
                       name="name"
                       placeholder="Master Bedroom / Kitchen"
                       class="border p-2 rounded w-full"
                       required>
            </div>

            <div>
                <label class="block font-semibold mb-1">Room Type</label>
                <input type="text"
                       name="room_type"
                       placeholder="Bedroom / Kitchen / Toilet"
                       class="border p-2 rounded w-full">
            </div>

            <div>
                <label class="block font-semibold mb-1">Remarks</label>
                <input type="text"
                       name="remarks"
                       class="border p-2 rounded w-full">
            </div>

            <div class="flex items-end">
                <button type="submit"
                        class="bg-blue-600 text-white px-4 py-2 rounded w-full">
                    Add Master
                </button>
            </div>

        </div>

    </form>

</div>

<div class="bg-white rounded shadow overflow-x-auto">

    <table class="w-full text-sm">

        <thead class="bg-gray-100">
            <tr>
                <th class="p-3 text-left">#</th>
                <th class="p-3 text-left">Room Name</th>
                <th class="p-3 text-left">Room Type</th>
                <th class="p-3 text-left">Status</th>
                <th class="p-3 text-left">Actions</th>
            </tr>
        </thead>

        <tbody>

            @forelse($rooms as $index => $room)

                <tr class="border-t">

                    <td class="p-3">
                        {{ $rooms->firstItem() + $index }}
                    </td>

                    <td class="p-3">
                        {{ $room->name }}
                    </td>

                    <td class="p-3">
                        {{ $room->room_type ?? '-' }}
                    </td>

                    <td class="p-3">
                        @if($room->is_active)
                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded">
                                Active
                            </span>
                        @else
                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded">
                                Inactive
                            </span>
                        @endif
                    </td>

                    <td class="p-3 flex gap-2">

                        <a href="{{ route('location-room-masters.edit', $room) }}"
                           class="bg-yellow-500 text-white px-3 py-1 rounded">
                            Edit
                        </a>

                        <form method="POST"
                              action="{{ route('location-room-masters.toggle-status', $room) }}">
                            @csrf
                            @method('PATCH')

                            <button type="submit"
                                    class="{{ $room->is_active ? 'bg-red-600' : 'bg-green-600' }} text-white px-3 py-1 rounded">
                                {{ $room->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="5"
                        class="p-4 text-center text-gray-500">
                        No room masters added.
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

</div>

<div class="mt-4">
    {{ $rooms->links() }}
</div>

@endsection