@extends('layouts.app')

@section('content')

<h1 class="text-3xl font-bold mb-6">
    Edit Project Block / Building
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
          action="{{ route('project-locations.blocks.update', $projectBlock) }}">

        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div>
                <label class="block font-semibold mb-1">Block Master</label>
                <select name="name"
                        class="border p-2 rounded w-full"
                        required>
                    @foreach($blockMasters as $master)
                        <option value="{{ $master->name }}"
                            {{ $projectBlock->name == $master->name ? 'selected' : '' }}>
                            {{ $master->name }}
                            @if($master->type)
                                ({{ $master->type }})
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold mb-1">Code</label>
                <input type="text"
                       name="code"
                       value="{{ old('code', $projectBlock->code) }}"
                       class="border p-2 rounded w-full">
            </div>

            <div>
                <label class="block font-semibold mb-1">Type</label>
                <select name="type"
                        class="border p-2 rounded w-full"
                        required>
                    @foreach(['Building', 'Block', 'Tower', 'Villa', 'External Area', 'Not Applicable'] as $type)
                        <option value="{{ $type }}"
                            {{ $projectBlock->type == $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold mb-1">Status</label>
                <select name="is_active"
                        class="border p-2 rounded w-full">
                    <option value="1" {{ $projectBlock->is_active ? 'selected' : '' }}>
                        Active
                    </option>
                    <option value="0" {{ !$projectBlock->is_active ? 'selected' : '' }}>
                        Inactive
                    </option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block font-semibold mb-1">Remarks</label>
                <input type="text"
                       name="remarks"
                       value="{{ old('remarks', $projectBlock->remarks) }}"
                       class="border p-2 rounded w-full">
            </div>

        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded">
                Update
            </button>

            <a href="{{ route('project-locations.index', ['project_id' => $projectBlock->project_id]) }}"
               class="bg-gray-500 text-white px-4 py-2 rounded">
                Back
            </a>
        </div>

    </form>

</div>

@endsection