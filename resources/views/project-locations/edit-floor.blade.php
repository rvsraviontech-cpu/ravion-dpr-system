@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Edit Project Floor
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
          action="{{ route('project-locations.floors.update', $projectFloor) }}">

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
                            {{ $projectFloor->project_block_id == $block->id ? 'selected' : '' }}>
                            {{ $block->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold mb-1">Floor Master</label>
                <select name="name"
                        class="border p-2 rounded w-full"
                        required>
                    @foreach($floorMasters as $master)
                        <option value="{{ $master->name }}"
                            {{ $projectFloor->name == $master->name ? 'selected' : '' }}>
                            {{ $master->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold mb-1">Sequence</label>
                <input type="number"
                       name="sequence"
                       value="{{ old('sequence', $projectFloor->sequence) }}"
                       class="border p-2 rounded w-full">
            </div>

            <div>
                <label class="block font-semibold mb-1">Status</label>
                <select name="is_active"
                        class="border p-2 rounded w-full">
                    <option value="1" {{ $projectFloor->is_active ? 'selected' : '' }}>
                        Active
                    </option>
                    <option value="0" {{ !$projectFloor->is_active ? 'selected' : '' }}>
                        Inactive
                    </option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block font-semibold mb-1">Remarks</label>
                <input type="text"
                       name="remarks"
                       value="{{ old('remarks', $projectFloor->remarks) }}"
                       class="border p-2 rounded w-full">
            </div>

        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded">
                Update
            </button>

            <a href="{{ route('project-locations.index', ['project_id' => $projectFloor->project_id]) }}"
               class="bg-gray-500 text-white px-4 py-2 rounded">
                Back
            </a>
        </div>

    </form>

</div>

@endsection