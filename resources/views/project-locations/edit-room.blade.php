@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Edit Project Room / Space
</h1>

@if($errors->any())
    <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<div class="bg-white p-6 rounded shadow">

    <form method="POST"
          action="{{ route('project-locations.rooms.update', $projectRoom) }}">

        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div>
                <label class="block font-semibold mb-1">Block / Building</label>
                <select name="project_block_id"
                        class="border p-2 rounded w-full"
                        required>
                    @foreach($blocks as $block)
                        <option value="{{ $block->id }}"
                            {{ $projectRoom->project_block_id == $block->id ? 'selected' : '' }}>
                            {{ $block->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold mb-1">Floor</label>
                <select name="project_floor_id"
                        class="border p-2 rounded w-full"
                        required>
                    @foreach($floors as $floor)
                        <option value="{{ $floor->id }}"
                            {{ $projectRoom->project_floor_id == $floor->id ? 'selected' : '' }}>
                            {{ $floor->block?->name }} - {{ $floor->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold mb-1">Unit</label>
                <select name="project_unit_id"
                        class="border p-2 rounded w-full"
                        required>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}"
                            {{ $projectRoom->project_unit_id == $unit->id ? 'selected' : '' }}>
                            {{ $unit->floor?->name }} - {{ $unit->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold mb-1">Room Master</label>
                <select name="name"
                        class="border p-2 rounded w-full"
                        required>
                    @foreach($roomMasters as $master)
                        <option value="{{ $master->name }}"
                            {{ $projectRoom->name == $master->name ? 'selected' : '' }}>
                            {{ $master->name }}
                            @if($master->room_type)
                                ({{ $master->room_type }})
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold mb-1">Room Type</label>
                <select name="room_type"
                        class="border p-2 rounded w-full">
                    <option value="">Select Room Type</option>
                    @foreach($roomMasters as $master)
                        @if($master->room_type)
                            <option value="{{ $master->room_type }}"
                                {{ $projectRoom->room_type == $master->room_type ? 'selected' : '' }}>
                                {{ $master->room_type }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold mb-1">Status</label>
                <select name="is_active"
                        class="border p-2 rounded w-full">
                    <option value="1" {{ $projectRoom->is_active ? 'selected' : '' }}>
                        Active
                    </option>
                    <option value="0" {{ !$projectRoom->is_active ? 'selected' : '' }}>
                        Inactive
                    </option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block font-semibold mb-1">Remarks</label>
                <input type="text"
                       name="remarks"
                       value="{{ old('remarks', $projectRoom->remarks) }}"
                       class="border p-2 rounded w-full">
            </div>

        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded">
                Update
            </button>

            <a href="{{ route('project-locations.index', ['project_id' => $projectRoom->project_id]) }}"
               class="bg-gray-500 text-white px-4 py-2 rounded">
                Back
            </a>
        </div>

    </form>

</div>

@endsection